<?php

namespace App\Http\Controllers;

use App\Models\ClientProfile;
use App\Models\AppSetting;
use App\Models\InboundEmail;
use App\Models\User;
use App\Jobs\OptimizeInvoiceFile;
use App\Services\AuditLogger;
use App\Services\InvoiceFileService;
use App\Services\Notifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InboundEmailController extends Controller
{
    public function store(Request $request, InvoiceFileService $files, AuditLogger $audit, Notifier $notifier): JsonResponse
    {
        $token = AppSetting::getValue('inbound_email_token', config('services.inbound_email.token'));

        abort_unless(hash_equals((string) $token, (string) $request->bearerToken()), 403);

        $validated = $request->validate([
            'provider' => ['nullable', 'string', 'max:255'],
            'message_id' => ['nullable', 'string', 'max:255'],
            'from_email' => ['required', 'email', 'max:255'],
            'to_email' => ['nullable', 'email', 'max:255'],
            'subject' => ['nullable', 'string', 'max:255'],
            'attachment_count' => ['nullable', 'integer', 'min:0'],
            'attachments' => ['nullable', 'array'],
            'attachments.*' => ['file', 'max:20480', 'mimes:jpg,jpeg,png,webp,pdf,doc,docx,xls,xlsx,csv,txt'],
            'metadata' => ['nullable', 'array'],
        ]);

        $client = ClientProfile::query()
            ->whereHas('user', fn ($query) => $query->where('email', $validated['from_email']))
            ->orWhere('webmail_address', $validated['to_email'] ?? '')
            ->first();

        unset($validated['attachments']);

        $email = InboundEmail::create($validated + [
            'client_profile_id' => $client?->id,
            'status' => $client ? 'matched' : 'unmatched',
            'attachment_count' => count($request->file('attachments', [])) ?: ($validated['attachment_count'] ?? 0),
        ]);

        $createdInvoices = 0;

        if ($client) {
            foreach ($request->file('attachments', []) as $attachment) {
                $payload = $files->store($client, $attachment);

                $invoice = $client->invoices()->create($payload + [
                    'uploaded_by' => null,
                    'source' => 'email',
                    'title' => $validated['subject'] ?: $attachment->getClientOriginalName(),
                    'description' => 'Received from '.$validated['from_email'].' via email.',
                    'currency' => 'USD',
                ]);

                OptimizeInvoiceFile::dispatch($invoice->id);
                $audit->log('invoice.email_received', $invoice, ['from' => $validated['from_email']]);
                User::query()->where('role', \App\Enums\UserRole::Admin)->each(
                    fn (User $admin) => $notifier->notify($admin, 'Email invoice received', $client->business_name.' sent '.$invoice->title, route('admin.invoices.show', $invoice))
                );
                $createdInvoices++;
            }
        } else {
            foreach ($request->file('attachments', []) as $attachment) {
                $email->attachments()->create($files->storeUnmatched($attachment, $email->id));
            }
            $audit->log('inbound_email.unmatched', $email, ['from' => $validated['from_email']]);
        }

        return response()->json([
            'id' => $email->id,
            'status' => $email->status,
            'created_invoices' => $createdInvoices,
        ], 201);
    }
}
