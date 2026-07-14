<?php

namespace App\Http\Controllers\Client;

use App\Enums\InvoiceStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Jobs\OptimizeInvoiceFile;
use App\Models\Invoice;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\InvoiceFileService;
use App\Services\Notifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InvoiceController extends Controller
{
    public function index(Request $request): View
    {
        $client = $request->user()->clientProfile;
        abort_unless($client, 403);
        $perPage = min((int) $request->integer('per_page', 20), 100);

        return view('client.invoices.index', [
            'invoices' => $client->invoices()
                ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
                ->when($request->filled('source'), fn ($query) => $query->where('source', $request->string('source')))
                ->when($request->filled('date_from'), fn ($query) => $query->whereDate('created_at', '>=', $request->date('date_from')))
                ->when($request->filled('date_to'), fn ($query) => $query->whereDate('created_at', '<=', $request->date('date_to')))
                ->when($request->filled('supplier'), fn ($query) => $query->where('supplier_name', 'like', '%'.$request->string('supplier').'%'))
                ->when($request->filled('abn'), fn ($query) => $query->where('abn', 'like', '%'.$request->string('abn').'%'))
                ->when($request->filled('category'), fn ($query) => $query->where('category', $request->string('category')))
                ->when($request->filled('invoice_date_from'), fn ($query) => $query->whereDate('invoice_date', '>=', $request->date('invoice_date_from')))
                ->when($request->filled('invoice_date_to'), fn ($query) => $query->whereDate('invoice_date', '<=', $request->date('invoice_date_to')))
                ->when($request->filled('due_date_from'), fn ($query) => $query->whereDate('due_date', '>=', $request->date('due_date_from')))
                ->when($request->filled('due_date_to'), fn ($query) => $query->whereDate('due_date', '<=', $request->date('due_date_to')))
                ->when($request->filled('amount_min'), fn ($query) => $query->where('invoice_amount', '>=', $request->float('amount_min')))
                ->when($request->filled('amount_max'), fn ($query) => $query->where('invoice_amount', '<=', $request->float('amount_max')))
                ->when($request->filled('gst_min'), fn ($query) => $query->where('gst_amount', '>=', $request->float('gst_min')))
                ->when($request->filled('gst_max'), fn ($query) => $query->where('gst_amount', '<=', $request->float('gst_max')))
                ->when($request->filled('q'), function ($query) use ($request): void {
                    $q = $request->string('q');
                    $query->where(function ($query) use ($q): void {
                        $query->where('original_filename', 'like', "%{$q}%")
                            ->orWhere('description', 'like', "%{$q}%")
                            ->orWhere('supplier_name', 'like', "%{$q}%")
                            ->orWhere('abn', 'like', "%{$q}%");
                    });
                })
                ->latest()
                ->paginate($perPage)
                ->withQueryString(),
            'statuses' => InvoiceStatus::cases(),
            'categories' => $client->invoices()
                ->whereNotNull('category')
                ->distinct()
                ->orderBy('category')
                ->pluck('category'),
        ]);
    }

    public function create(): View
    {
        return view('client.invoices.create');
    }

    public function store(Request $request, InvoiceFileService $files, AuditLogger $audit, Notifier $notifier): RedirectResponse
    {
        $client = $request->user()->clientProfile;
        abort_unless($client, 403);

        $validated = $request->validate([
            'description' => ['nullable', 'string', 'max:2000'],
            'invoice_file' => ['required', 'file', 'max:20480', 'mimes:jpg,jpeg,png,webp,pdf,doc,docx,xls,xlsx,csv,txt'],
        ]);

        $payload = $files->store($client, $validated['invoice_file']);

        $invoice = $client->invoices()->create($payload + [
            'uploaded_by' => $request->user()->id,
            'source' => 'portal',
            'description' => $validated['description'] ?? null,
            'currency' => 'AUD',
        ]);

        OptimizeInvoiceFile::dispatch($invoice->id);
        $audit->log('invoice.uploaded', $invoice, ['source' => 'portal']);
        User::query()->where('role', UserRole::Admin)->each(
            fn (User $admin) => $notifier->notify($admin, 'New invoice uploaded', $client->business_name.' uploaded '.$invoice->original_filename, route('admin.invoices.show', $invoice))
        );

        return redirect()->route('client.invoices.index')->with('status', 'Invoice uploaded and queued for review.');
    }

    public function show(Request $request, Invoice $invoice): View
    {
        abort_unless($invoice->client_profile_id === $request->user()->clientProfile?->id, 403);

        return view('client.invoices.show', [
            'invoice' => $invoice->load('comments.user'),
        ]);
    }

    public function download(Request $request, Invoice $invoice): StreamedResponse
    {
        abort_unless($invoice->client_profile_id === $request->user()->clientProfile?->id, 403);

        $path = $invoice->compressed_path ?: $invoice->stored_path;
        $disk = $invoice->storage_disk ?: 'local';

        abort_unless(Storage::disk($disk)->exists($path), 404, 'File not found on storage.');

        return Storage::disk($disk)->download($path, $invoice->original_filename);
    }

    public function preview(Request $request, Invoice $invoice)
    {
        abort_unless($invoice->client_profile_id === $request->user()->clientProfile?->id, 403);

        $path = $invoice->compressed_path ?: $invoice->stored_path;
        $disk = $invoice->storage_disk ?: 'local';
        abort_unless(in_array($invoice->mime_type, ['application/pdf', 'image/jpeg', 'image/png', 'image/webp'], true), 415);
        abort_unless(Storage::disk($disk)->exists($path), 404, 'File not found on storage.');

        return Storage::disk($disk)->response($path, $invoice->original_filename, [
            'Content-Type' => $invoice->mime_type,
            'Content-Disposition' => 'inline; filename="'.$invoice->original_filename.'"',
        ]);
    }
}
