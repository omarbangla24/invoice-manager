<?php

namespace App\Http\Controllers\Client;

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
                ->when($request->filled('q'), function ($query) use ($request): void {
                    $q = $request->string('q');
                    $query->where(function ($query) use ($q): void {
                        $query->where('title', 'like', "%{$q}%")
                            ->orWhere('original_filename', 'like', "%{$q}%")
                            ->orWhere('description', 'like', "%{$q}%");
                    });
                })
                ->latest()
                ->paginate($perPage)
                ->withQueryString(),
            'statuses' => \App\Enums\InvoiceStatus::cases(),
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
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'expense_date' => ['nullable', 'date'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'size:3'],
            'invoice_file' => ['required', 'file', 'max:20480', 'mimes:jpg,jpeg,png,webp,pdf,doc,docx,xls,xlsx,csv,txt'],
        ]);

        $payload = $files->store($client, $validated['invoice_file']);

        $invoice = $client->invoices()->create($payload + [
            'uploaded_by' => $request->user()->id,
            'source' => 'portal',
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'expense_date' => $validated['expense_date'] ?? null,
            'amount' => $validated['amount'] ?? null,
            'currency' => strtoupper($validated['currency']),
        ]);

        OptimizeInvoiceFile::dispatch($invoice->id);
        $audit->log('invoice.uploaded', $invoice, ['source' => 'portal']);
        User::query()->where('role', \App\Enums\UserRole::Admin)->each(
            fn (User $admin) => $notifier->notify($admin, 'New invoice uploaded', $client->business_name.' uploaded '.$invoice->title, route('admin.invoices.show', $invoice))
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

        return Storage::disk($invoice->storage_disk ?: 'local')->download($path, $invoice->original_filename);
    }

    public function preview(Request $request, Invoice $invoice)
    {
        abort_unless($invoice->client_profile_id === $request->user()->clientProfile?->id, 403);

        $path = $invoice->compressed_path ?: $invoice->stored_path;
        $disk = $invoice->storage_disk ?: 'local';
        abort_unless(in_array($invoice->mime_type, ['application/pdf', 'image/jpeg', 'image/png', 'image/webp'], true), 415);

        return Storage::disk($disk)->response($path, $invoice->original_filename, [
            'Content-Type' => $invoice->mime_type,
            'Content-Disposition' => 'inline; filename="'.$invoice->original_filename.'"',
        ]);
    }
}
