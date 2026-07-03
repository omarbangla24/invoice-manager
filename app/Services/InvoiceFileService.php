<?php

namespace App\Services;

use App\Models\ClientProfile;
use App\Models\Invoice;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class InvoiceFileService
{
    /**
     * @return array{original_filename:string,stored_path:string,compressed_path:?string,original_size:int,compressed_size:?int,mime_type:?string}
     */
    public function store(ClientProfile $client, UploadedFile $file): array
    {
        $folder = $this->folderFor($client);
        $basename = now()->format('His').'-'.Str::uuid();
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'bin');
        $storedPath = "{$folder}/original/{$basename}.{$extension}";

        $disk = $this->disk();
        Storage::disk($disk)->put($storedPath, file_get_contents($file->getRealPath()));

        $compressedPath = $this->compressImageIfPossible($file, $folder, $basename, $disk);

        return [
            'original_filename' => $file->getClientOriginalName(),
            'stored_path' => $storedPath,
            'compressed_path' => $compressedPath,
            'original_size' => $file->getSize() ?: 0,
            'compressed_size' => $compressedPath ? Storage::disk($disk)->size($compressedPath) : null,
            'mime_type' => $file->getMimeType(),
            'storage_disk' => $disk,
            'optimization_status' => $compressedPath ? 'completed' : 'queued',
        ];
    }

    /**
     * @return array{original_filename:string,stored_path:string,size:int,mime_type:?string}
     */
    public function storeUnmatched(UploadedFile $file, int $emailId): array
    {
        $basename = now()->format('His').'-'.Str::uuid();
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'bin');
        $storedPath = "unmatched-emails/{$emailId}/original/{$basename}.{$extension}";

        $disk = $this->disk();
        Storage::disk($disk)->put($storedPath, file_get_contents($file->getRealPath()));

        return [
            'original_filename' => $file->getClientOriginalName(),
            'stored_path' => $storedPath,
            'size' => $file->getSize() ?: 0,
            'mime_type' => $file->getMimeType(),
            'storage_disk' => $disk,
        ];
    }

    /**
     * @return array{original_filename:string,stored_path:string,compressed_path:?string,original_size:int,compressed_size:?int,mime_type:?string}
     */
    public function copyExistingToClient(ClientProfile $client, string $sourcePath, string $originalFilename, ?string $mimeType, int $size, ?string $sourceDisk = null): array
    {
        $folder = $this->folderFor($client);
        $extension = strtolower(pathinfo($originalFilename, PATHINFO_EXTENSION) ?: 'bin');
        $basename = now()->format('His').'-'.Str::uuid();
        $storedPath = "{$folder}/original/{$basename}.{$extension}";

        $disk = $this->disk();
        Storage::disk($disk)->put($storedPath, Storage::disk($sourceDisk ?: $disk)->get($sourcePath));

        return [
            'original_filename' => $originalFilename,
            'stored_path' => $storedPath,
            'compressed_path' => null,
            'original_size' => $size,
            'compressed_size' => null,
            'mime_type' => $mimeType,
            'storage_disk' => $disk,
            'optimization_status' => 'queued',
        ];
    }

    public function optimize(Invoice $invoice): void
    {
        $disk = $invoice->storage_disk ?: 'local';

        if ($invoice->compressed_path || ! Storage::disk($disk)->exists($invoice->stored_path)) {
            $invoice->forceFill(['optimization_status' => 'completed'])->save();
            return;
        }

        if ($invoice->mime_type === 'application/pdf') {
            $this->optimizePdf($invoice, $disk);
            return;
        }

        $invoice->forceFill([
            'optimization_status' => 'skipped',
            'optimization_notes' => 'No deep optimizer available for '.$invoice->mime_type,
        ])->save();
    }

    private function folderFor(ClientProfile $client): string
    {
        return sprintf(
            'clients/%s/%s/%s',
            $client->storage_folder,
            now()->format('Y'),
            now()->format('m-F'),
        );
    }

    private function compressImageIfPossible(UploadedFile $file, string $folder, string $basename, string $disk): ?string
    {
        $mime = $file->getMimeType();

        if (! in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
            return null;
        }

        if (! function_exists('imagecreatetruecolor')) {
            return null;
        }

        $source = match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($file->getRealPath()),
            'image/png' => @imagecreatefrompng($file->getRealPath()),
            'image/webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($file->getRealPath()) : false,
            default => false,
        };

        if (! $source) {
            return null;
        }

        $width = imagesx($source);
        $height = imagesy($source);
        $maxWidth = 1800;
        $ratio = min(1, $maxWidth / max(1, $width));
        $targetWidth = (int) max(1, round($width * $ratio));
        $targetHeight = (int) max(1, round($height * $ratio));

        $canvas = imagecreatetruecolor($targetWidth, $targetHeight);
        imagecopyresampled($canvas, $source, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);

        $tmp = tempnam(sys_get_temp_dir(), 'invoice-');
        imagejpeg($canvas, $tmp, 78);

        imagedestroy($source);
        imagedestroy($canvas);

        $compressedPath = "{$folder}/compressed/{$basename}.jpg";
        Storage::disk($disk)->put($compressedPath, file_get_contents($tmp));
        @unlink($tmp);

        return $compressedPath;
    }

    private function optimizePdf(Invoice $invoice, string $disk): void
    {
        if (! $this->hasBinary('gs')) {
            $invoice->forceFill([
                'optimization_status' => 'skipped',
                'optimization_notes' => 'Ghostscript not installed on server.',
            ])->save();
            return;
        }

        $folder = dirname(dirname($invoice->stored_path));
        $target = $folder.'/compressed/'.pathinfo($invoice->stored_path, PATHINFO_FILENAME).'.pdf';
        $input = tempnam(sys_get_temp_dir(), 'pdf-in-');
        $output = tempnam(sys_get_temp_dir(), 'pdf-out-');

        File::put($input, Storage::disk($disk)->get($invoice->stored_path));

        $result = Process::timeout(60)->run([
            'gs', '-sDEVICE=pdfwrite', '-dCompatibilityLevel=1.4',
            '-dPDFSETTINGS=/ebook', '-dNOPAUSE', '-dQUIET', '-dBATCH',
            '-sOutputFile='.$output, $input,
        ]);

        if ($result->successful() && File::exists($output) && File::size($output) > 0) {
            Storage::disk($disk)->put($target, File::get($output));
            $invoice->forceFill([
                'compressed_path' => $target,
                'compressed_size' => Storage::disk($disk)->size($target),
                'optimization_status' => 'completed',
                'optimization_notes' => 'PDF compressed with Ghostscript.',
            ])->save();
        } else {
            $invoice->forceFill([
                'optimization_status' => 'failed',
                'optimization_notes' => trim($result->errorOutput()) ?: 'Ghostscript failed.',
            ])->save();
        }

        @unlink($input);
        @unlink($output);
    }

    private function hasBinary(string $binary): bool
    {
        return trim((string) shell_exec('command -v '.escapeshellarg($binary).' 2>/dev/null')) !== '';
    }

    private function disk(): string
    {
        $manager = app(StorageDiskManager::class);
        $manager->applyCloudConfig();

        return $manager->activeDisk();
    }
}
