<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClientProfile;
use App\Models\InboundEmail;
use App\Models\InboundEmailAttachment;
use App\Jobs\OptimizeInvoiceFile;
use App\Services\AuditLogger;
use App\Services\InvoiceFileService;
use App\Services\Notifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UnmatchedEmailController extends Controller
{
    public function index(Request $request): View
    {
        $perPage = min((int) $request->integer('per_page', 20), 100);

        return view('admin.unmatched-emails.index', [
            'emails' => InboundEmail::withCount('attachments')
                ->where('status', 'unmatched')
                ->when($request->filled('q'), function ($query) use ($request): void {
                    $q = $request->string('q');
                    $query->where(function ($query) use ($q): void {
                        $query->where('from_email', 'like', "%{$q}%")
                            ->orWhere('to_email', 'like', "%{$q}%")
                            ->orWhere('subject', 'like', "%{$q}%");
                    });
                })
                ->when($request->filled('date_from'), fn ($query) => $query->whereDate('created_at', '>=', $request->date('date_from')))
                ->when($request->filled('date_to'), fn ($query) => $query->whereDate('created_at', '<=', $request->date('date_to')))
                ->latest()
                ->paginate($perPage)
                ->withQueryString(),
        ]);
    }

    public function show(InboundEmail $email): View
    {
        return view('admin.unmatched-emails.show', [
            'email' => $email->load('attachments'),
            'clients' => ClientProfile::orderBy('business_name')->get(),
        ]);
    }

    public function transfer(Request $request, InboundEmailAttachment $attachment, InvoiceFileService $files, AuditLogger $audit, Notifier $notifier): RedirectResponse
    {
        $validated = $request->validate([
            'client_profile_id' => ['required', 'exists:client_profiles,id'],
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        abort_unless($attachment->status === 'unmatched', 422, 'This attachment was already transferred.');

        $client = ClientProfile::findOrFail($validated['client_profile_id']);
        $email = $attachment->inboundEmail;
        $payload = $files->copyExistingToClient(
            $client,
            $attachment->stored_path,
            $attachment->original_filename,
            $attachment->mime_type,
            $attachment->size,
            $attachment->storage_disk,
        );

        $invoice = $client->invoices()->create($payload + [
            'uploaded_by' => null,
            'source' => 'email',
            'title' => $validated['title'] ?: ($email->subject ?: $attachment->original_filename),
            'description' => ($validated['description'] ?? null) ?: 'Transferred from unmatched email sent by '.$email->from_email.'.',
            'currency' => 'USD',
        ]);

        $attachment->forceFill([
            'invoice_id' => $invoice->id,
            'status' => 'transferred',
            'transferred_at' => now(),
        ])->save();

        OptimizeInvoiceFile::dispatch($invoice->id);
        $audit->log('unmatched_attachment.transferred', $invoice, [
            'attachment_id' => $attachment->id,
            'client_profile_id' => $client->id,
        ]);
        $notifier->notify($client->user, 'Email invoice assigned', $invoice->title.' was added to your portal.', route('client.invoices.show', $invoice));

        if (! $email->attachments()->where('status', 'unmatched')->exists()) {
            $email->forceFill([
                'client_profile_id' => $client->id,
                'status' => 'transferred',
            ])->save();
        }

        return redirect()->route('admin.invoices.show', $invoice)->with('status', 'Unmatched attachment transferred to client invoice.');
    }

    public function download(InboundEmailAttachment $attachment): StreamedResponse
    {
        return Storage::disk($attachment->storage_disk ?: 'local')->download($attachment->stored_path, $attachment->original_filename);
    }

    public function destroyEmail(InboundEmail $email, AuditLogger $audit): RedirectResponse
    {
        foreach ($email->attachments as $attachment) {
            $disk = $attachment->storage_disk ?: 'local';
            if ($attachment->stored_path && Storage::disk($disk)->exists($attachment->stored_path)) {
                Storage::disk($disk)->delete($attachment->stored_path);
            }
            $attachment->delete();
        }

        $audit->log('unmatched_email.deleted', $email, ['from' => $email->from_email, 'subject' => $email->subject]);
        $email->delete();

        return redirect()->route('admin.unmatched-emails.index')->with('status', 'Unmatched email and its attachments deleted.');
    }

    public function destroyAttachment(InboundEmailAttachment $attachment, AuditLogger $audit): RedirectResponse
    {
        $disk = $attachment->storage_disk ?: 'local';
        if ($attachment->stored_path && Storage::disk($disk)->exists($attachment->stored_path)) {
            Storage::disk($disk)->delete($attachment->stored_path);
        }

        $email = $attachment->inboundEmail;
        $audit->log('unmatched_attachment.deleted', $attachment, ['filename' => $attachment->original_filename]);
        $attachment->delete();

        if ($email && ! $email->attachments()->where('status', 'unmatched')->exists()) {
            if ($email->attachments()->count() === 0) {
                $email->delete();
            }
        }

        return back()->with('status', 'Attachment deleted.');
    }
}
