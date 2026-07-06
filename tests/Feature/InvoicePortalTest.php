<?php

namespace Tests\Feature;

use App\Enums\InvoiceStatus;
use App\Enums\UserRole;
use App\Models\AppSetting;
use App\Models\ClientProfile;
use App\Models\InboundEmail;
use App\Models\InboundEmailAttachment;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class InvoicePortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_can_upload_invoice_to_own_folder(): void
    {
        Storage::fake('local');
        $client = $this->clientProfile('Acme Studio');

        $response = $this->actingAs($client->user)->post(route('client.invoices.store'), [
            'title' => 'Taxi receipt',
            'invoice_file' => UploadedFile::fake()->create('receipt.txt', 8, 'text/plain'),
        ]);

        $response->assertRedirect(route('client.invoices.index'));
        $invoice = Invoice::firstOrFail();

        $this->assertSame($client->id, $invoice->client_profile_id);
        $this->assertSame('AUD', $invoice->currency);
        $this->assertStringContainsString($client->storage_folder, $invoice->stored_path);
        Storage::disk('local')->assertExists($invoice->stored_path);
    }

    public function test_client_can_upload_invoice_without_title_defaults_to_filename(): void
    {
        Storage::fake('local');
        $client = $this->clientProfile('Studio B');

        $response = $this->actingAs($client->user)->post(route('client.invoices.store'), [
            'invoice_file' => UploadedFile::fake()->create('payment-receipt.pdf', 12, 'application/pdf'),
        ]);

        $response->assertRedirect(route('client.invoices.index'));
        $invoice = Invoice::firstOrFail();

        $this->assertSame('payment-receipt.pdf', $invoice->title);
        $this->assertSame($client->id, $invoice->client_profile_id);
        Storage::disk('local')->assertExists($invoice->stored_path);
    }

    public function test_client_cannot_view_another_clients_invoice(): void
    {
        $first = $this->clientProfile('First Client');
        $second = $this->clientProfile('Second Client');

        $invoice = Invoice::create([
            'client_profile_id' => $first->id,
            'uploaded_by' => $first->user_id,
            'title' => 'Private receipt',
            'original_filename' => 'receipt.pdf',
            'stored_path' => 'clients/first/receipt.pdf',
            'original_size' => 100,
            'status' => InvoiceStatus::Pending,
        ]);

        $this->actingAs($second->user)
            ->get(route('client.invoices.show', $invoice))
            ->assertForbidden();
    }

    public function test_admin_can_mark_invoice_done_and_comment(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $client = $this->clientProfile('Review Client');
        $invoice = Invoice::create([
            'client_profile_id' => $client->id,
            'uploaded_by' => $client->user_id,
            'title' => 'Hotel receipt',
            'original_filename' => 'hotel.pdf',
            'stored_path' => 'clients/review/hotel.pdf',
            'original_size' => 100,
            'status' => InvoiceStatus::Pending,
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.invoices.update', $invoice), [
                'status' => InvoiceStatus::Done->value,
                'comment' => 'Counted in July expenses.',
            ])
            ->assertRedirect();

        $invoice->refresh();
        $this->assertSame(InvoiceStatus::Done, $invoice->status);
        $this->assertNotNull($invoice->counted_at);
        $this->assertSame('Counted in July expenses.', $invoice->comments()->first()->body);
    }

    public function test_inbound_email_creates_invoice_for_matched_client_attachment(): void
    {
        Storage::fake('local');
        config(['services.inbound_email.token' => 'secret-token']);
        $client = $this->clientProfile('Email Client');

        $this->post(route('inbound.email.store'), [
            'from_email' => $client->user->email,
            'to_email' => 'invoices@example.com',
            'subject' => 'Fuel receipt',
            'attachments' => [
                UploadedFile::fake()->create('fuel.pdf', 12, 'application/pdf'),
            ],
        ], [
            'Authorization' => 'Bearer secret-token',
        ])->assertCreated()
            ->assertJsonPath('created_invoices', 1);

        $invoice = Invoice::firstOrFail();
        $this->assertSame('email', $invoice->source);
        $this->assertSame($client->id, $invoice->client_profile_id);
        Storage::disk('local')->assertExists($invoice->stored_path);
    }

    public function test_admin_can_update_own_email_and_password_from_settings(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Admin,
            'password' => Hash::make('old-password'),
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.settings.profile.update'), [
                'name' => 'Lead Accountant',
                'email' => 'lead@example.com',
                'current_password' => 'old-password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ])
            ->assertRedirect();

        $admin->refresh();
        $this->assertSame('Lead Accountant', $admin->name);
        $this->assertSame('lead@example.com', $admin->email);
        $this->assertTrue(Hash::check('new-password', $admin->password));
    }

    public function test_client_can_update_own_email_and_password_from_portal_settings(): void
    {
        $client = $this->clientProfile('Portal Settings Client');
        $client->user->forceFill([
            'password' => Hash::make('old-password'),
        ])->save();

        $this->actingAs($client->user)
            ->patch(route('client.settings.update'), [
                'name' => 'Updated Client',
                'email' => 'updated-client@example.com',
                'current_password' => 'old-password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ])
            ->assertRedirect();

        $client->user->refresh();
        $this->assertSame('Updated Client', $client->user->name);
        $this->assertSame('updated-client@example.com', $client->user->email);
        $this->assertTrue(Hash::check('new-password', $client->user->password));
    }

    public function test_admin_can_save_email_settings_and_inbound_token_is_used(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $client = $this->clientProfile('Settings Email Client');

        $this->actingAs($admin)
            ->patch(route('admin.settings.email.update'), [
                'mail_mailer' => 'smtp',
                'mail_host' => 'smtp.example.com',
                'mail_port' => 587,
                'mail_username' => 'invoices@example.com',
                'mail_password' => 'smtp-secret',
                'mail_from_address' => 'invoices@example.com',
                'mail_from_name' => 'Invoice Portal',
                'inbound_email_address' => 'invoices@example.com',
                'inbound_provider' => 'Mailgun',
                'inbound_email_token' => 'database-token-123',
            ])
            ->assertRedirect();

        $this->assertSame('smtp.example.com', AppSetting::getValue('mail_host'));
        $this->assertSame('database-token-123', AppSetting::getValue('inbound_email_token'));

        $this->post(route('inbound.email.store'), [
            'from_email' => $client->user->email,
            'to_email' => 'invoices@example.com',
            'subject' => 'Database token receipt',
            'attachments' => [
                UploadedFile::fake()->create('receipt.pdf', 12, 'application/pdf'),
            ],
        ], [
            'Authorization' => 'Bearer database-token-123',
        ])->assertCreated()
            ->assertJsonPath('created_invoices', 1);
    }

    public function test_unmatched_email_attachments_are_queued_for_later_transfer(): void
    {
        Storage::fake('local');
        config(['services.inbound_email.token' => 'secret-token']);

        $this->post(route('inbound.email.store'), [
            'from_email' => 'unknown@example.com',
            'to_email' => 'invoices@example.com',
            'subject' => 'Unknown receipt',
            'attachments' => [
                UploadedFile::fake()->create('unknown.pdf', 12, 'application/pdf'),
            ],
        ], [
            'Authorization' => 'Bearer secret-token',
        ])->assertCreated()
            ->assertJsonPath('status', 'unmatched')
            ->assertJsonPath('created_invoices', 0);

        $this->assertDatabaseCount('invoices', 0);
        $email = InboundEmail::with('attachments')->firstOrFail();
        $attachment = $email->attachments->first();

        $this->assertSame('unmatched', $email->status);
        $this->assertSame('unmatched', $attachment->status);
        $this->assertStringContainsString('unmatched-emails/'.$email->id, $attachment->stored_path);
        Storage::disk('local')->assertExists($attachment->stored_path);
    }

    public function test_admin_can_transfer_unmatched_attachment_to_client_invoice(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $client = $this->clientProfile('Transfer Client');
        $email = InboundEmail::create([
            'from_email' => 'unknown@example.com',
            'to_email' => 'invoices@example.com',
            'subject' => 'Transfer receipt',
            'attachment_count' => 1,
            'status' => 'unmatched',
        ]);
        Storage::disk('local')->put('unmatched-emails/'.$email->id.'/original/file.pdf', 'pdf-content');
        $attachment = InboundEmailAttachment::create([
            'inbound_email_id' => $email->id,
            'status' => 'unmatched',
            'original_filename' => 'file.pdf',
            'stored_path' => 'unmatched-emails/'.$email->id.'/original/file.pdf',
            'size' => 11,
            'mime_type' => 'application/pdf',
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.unmatched-attachments.transfer', $attachment), [
                'client_profile_id' => $client->id,
                'title' => 'Transferred receipt',
            ])
            ->assertRedirect();

        $invoice = Invoice::firstOrFail();
        $attachment->refresh();
        $email->refresh();

        $this->assertSame($client->id, $invoice->client_profile_id);
        $this->assertSame('email', $invoice->source);
        $this->assertStringContainsString($client->storage_folder, $invoice->stored_path);
        $this->assertSame('transferred', $attachment->status);
        $this->assertSame($invoice->id, $attachment->invoice_id);
        $this->assertSame('transferred', $email->status);
        Storage::disk('local')->assertExists($invoice->stored_path);
    }

    private function clientProfile(string $businessName): ClientProfile
    {
        $user = User::factory()->create([
            'name' => $businessName,
            'email' => Str::slug($businessName).'-client@example.com',
            'role' => UserRole::Client,
        ]);

        return ClientProfile::create([
            'user_id' => $user->id,
            'business_name' => $businessName,
            'storage_folder' => Str::slug($businessName).'-abc123',
        ]);
    }
}
