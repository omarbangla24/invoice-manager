<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function edit(Request $request): View
    {
        $tab = in_array($request->query('tab'), ['account', 'email', 'storage'], true)
            ? $request->query('tab')
            : 'account';

        return view('admin.settings.edit', [
            'admin' => $request->user(),
            'tab' => $tab,
            'settings' => [
                'mail_mailer' => AppSetting::getValue('mail_mailer', config('mail.default')),
                'mail_host' => AppSetting::getValue('mail_host', config('mail.mailers.smtp.host')),
                'mail_port' => AppSetting::getValue('mail_port', (string) config('mail.mailers.smtp.port')),
                'mail_username' => AppSetting::getValue('mail_username', config('mail.mailers.smtp.username')),
                'mail_from_address' => AppSetting::getValue('mail_from_address', config('mail.from.address')),
                'mail_from_name' => AppSetting::getValue('mail_from_name', config('mail.from.name')),
                'inbound_email_address' => AppSetting::getValue('inbound_email_address', 'invoices@yourdomain.com'),
                'inbound_provider' => AppSetting::getValue('inbound_provider', 'Webhook provider not connected'),
                'inbound_email_token' => AppSetting::getValue('inbound_email_token', config('services.inbound_email.token')),
                'storage_disk' => AppSetting::getValue('storage_disk', config('filesystems.default', 'local')),
                's3_key' => AppSetting::getValue('s3_key', config('filesystems.disks.s3.key')),
                's3_secret' => AppSetting::getValue('s3_secret', config('filesystems.disks.s3.secret')),
                's3_region' => AppSetting::getValue('s3_region', config('filesystems.disks.s3.region')),
                's3_bucket' => AppSetting::getValue('s3_bucket', config('filesystems.disks.s3.bucket')),
                's3_endpoint' => AppSetting::getValue('s3_endpoint', config('filesystems.disks.s3.endpoint')),
                's3_url' => AppSetting::getValue('s3_url', config('filesystems.disks.s3.url')),
                's3_path_style' => AppSetting::getValue('s3_path_style', 'false'),
            ],
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $admin = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($admin->id)],
            'current_password' => ['required', 'current_password'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $admin->forceFill([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        if (! empty($validated['password'])) {
            $admin->password = Hash::make($validated['password']);
        }

        $admin->save();

        return redirect()->route('admin.settings.edit', ['tab' => 'account'])->with('status', 'Admin account updated.');
    }

    public function updateEmailSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'mail_mailer' => ['required', 'string', 'max:50'],
            'mail_host' => ['nullable', 'string', 'max:255'],
            'mail_port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'mail_username' => ['nullable', 'string', 'max:255'],
            'mail_password' => ['nullable', 'string', 'max:255'],
            'mail_from_address' => ['required', 'email', 'max:255'],
            'mail_from_name' => ['required', 'string', 'max:255'],
            'inbound_email_address' => ['required', 'email', 'max:255'],
            'inbound_provider' => ['required', 'string', 'max:255'],
            'inbound_email_token' => ['required', 'string', 'min:12', 'max:255'],
        ]);

        foreach ($validated as $key => $value) {
            if ($key === 'current_password' || ($key === 'mail_password' && empty($value))) {
                continue;
            }

            AppSetting::setValue($key, is_null($value) ? null : (string) $value);
        }

        return redirect()->route('admin.settings.edit', ['tab' => 'email'])->with('status', 'Email settings saved.');
    }

    public function updateStorageSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'storage_disk' => ['required', 'in:local,s3'],
            's3_key' => ['nullable', 'string', 'max:255'],
            's3_secret' => ['nullable', 'string', 'max:255'],
            's3_region' => ['nullable', 'string', 'max:100'],
            's3_bucket' => ['nullable', 'string', 'max:255'],
            's3_endpoint' => ['nullable', 'url', 'max:255'],
            's3_url' => ['nullable', 'url', 'max:255'],
            's3_path_style' => ['nullable', 'boolean'],
        ]);

        foreach ($validated as $key => $value) {
            if ($key === 'current_password' || ($key === 's3_secret' && empty($value))) {
                continue;
            }

            AppSetting::setValue($key, is_bool($value) ? ($value ? 'true' : 'false') : (string) ($value ?? ''));
        }

        return redirect()->route('admin.settings.edit', ['tab' => 'storage'])->with('status', 'Storage settings saved.');
    }
}
