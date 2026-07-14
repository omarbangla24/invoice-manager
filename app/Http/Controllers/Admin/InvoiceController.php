<?php

namespace App\Http\Controllers\Admin;

use App\Enums\InvoiceStatus;
use App\Http\Controllers\Controller;
use App\Models\ClientProfile;
use App\Models\Invoice;
use App\Services\AuditLogger;
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
        $perPage = min((int) $request->integer('per_page', 20), 100);

        $invoices = Invoice::with('clientProfile.user')
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
            ->when($request->filled('source'), fn ($query) => $query->where('source', $request->string('source')))
            ->when($request->filled('client'), fn ($query) => $query->where('client_profile_id', $request->integer('client')))
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
                        ->orWhere('abn', 'like', "%{$q}%")
                        ->orWhereHas('clientProfile', fn ($query) => $query->where('business_name', 'like', "%{$q}%"));
                });
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        return view('admin.invoices.index', [
            'invoices' => $invoices,
            'statuses' => InvoiceStatus::cases(),
            'clients' => ClientProfile::orderBy('business_name')->get(['id', 'business_name']),
            'categories' => Invoice::query()->whereNotNull('category')->distinct()->orderBy('category')->pluck('category'),
        ]);
    }

    public function show(Invoice $invoice): View
    {
        return view('admin.invoices.show', [
            'invoice' => $invoice->load('clientProfile.user', 'comments.user'),
            'statuses' => InvoiceStatus::cases(),
        ]);
    }

    public function update(Request $request, Invoice $invoice, AuditLogger $audit, Notifier $notifier): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,done,declined'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ]);

        $status = InvoiceStatus::from($validated['status']);
        $invoice->forceFill([
            'status' => $status,
            'counted_at' => $status === InvoiceStatus::Done ? now() : null,
            'declined_at' => $status === InvoiceStatus::Declined ? now() : null,
        ])->save();

        if (! empty($validated['comment'])) {
            $invoice->comments()->create([
                'user_id' => $request->user()->id,
                'body' => $validated['comment'],
            ]);
        }

        $audit->log('invoice.status_updated', $invoice, ['status' => $status->value]);
        $notifier->notify(
            $invoice->clientProfile->user,
            'Invoice status updated',
            $invoice->original_filename.' marked '.$status->label(),
            route('client.invoices.show', $invoice),
        );

        return back()->with('status', 'Invoice status updated.');
    }

    public function updateDetails(Request $request, Invoice $invoice, AuditLogger $audit): RedirectResponse
    {
        $validated = $request->validate([
            'supplier_name' => ['nullable', 'string', 'max:255'],
            'abn' => ['nullable', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
            'invoice_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date'],
            'invoice_amount' => ['nullable', 'numeric', 'min:0'],
            'gst_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $invoice->forceFill([
            'supplier_name' => $validated['supplier_name'] ?? null,
            'abn' => $validated['abn'] ?? null,
            'category' => $validated['category'] ?? null,
            'invoice_date' => $validated['invoice_date'] ?? null,
            'due_date' => $validated['due_date'] ?? null,
            'invoice_amount' => $validated['invoice_amount'] ?? null,
            'gst_amount' => $validated['gst_amount'] ?? null,
        ])->save();

        $audit->log('invoice.details_updated', $invoice);

        return back()->with('status', 'Invoice details saved.');
    }

    public function destroy(Invoice $invoice, AuditLogger $audit): RedirectResponse
    {
        $disk = $invoice->storage_disk ?: 'local';
        if ($invoice->stored_path && Storage::disk($disk)->exists($invoice->stored_path)) {
            Storage::disk($disk)->delete($invoice->stored_path);
        }
        if ($invoice->compressed_path && $invoice->compressed_path !== $invoice->stored_path && Storage::disk($disk)->exists($invoice->compressed_path)) {
            Storage::disk($disk)->delete($invoice->compressed_path);
        }

        $audit->log('invoice.deleted', $invoice, ['filename' => $invoice->original_filename]);
        $clientId = $invoice->client_profile_id;
        $invoice->comments()->delete();
        $invoice->delete();

        return redirect()->route('admin.clients.show', $clientId)->with('status', 'Invoice deleted.');
    }

    public function download(Invoice $invoice): StreamedResponse
    {
        $path = $invoice->compressed_path ?: $invoice->stored_path;
        $disk = $invoice->storage_disk ?: 'local';

        abort_unless(Storage::disk($disk)->exists($path), 404, 'File not found on storage.');

        return Storage::disk($disk)->download($path, $invoice->original_filename);
    }

    public function preview(Invoice $invoice)
    {
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
