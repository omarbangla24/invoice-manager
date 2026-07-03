<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\AppSetting;
use App\Models\ClientProfile;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin Accountant',
                'password' => Hash::make('password'),
                'role' => UserRole::Admin,
            ],
        );

        $clientUser = User::updateOrCreate(
            ['email' => 'client@example.com'],
            [
                'name' => 'Demo Client',
                'password' => Hash::make('password'),
                'role' => UserRole::Client,
            ],
        );

        ClientProfile::updateOrCreate(
            ['user_id' => $clientUser->id],
            [
                'business_name' => 'Demo Business LLC',
                'contact_name' => 'Demo Client',
                'webmail_address' => 'demo-business@example.com',
                'details' => 'Seed client for local testing.',
                'storage_folder' => 'demo-business-'.Str::lower(Str::random(6)),
            ],
        );

        AppSetting::setValue('inbound_email_address', 'invoices@example.com');
        AppSetting::setValue('inbound_provider', 'Webhook provider not connected');
        AppSetting::setValue('inbound_email_token', 'change-this-token');
    }
}
