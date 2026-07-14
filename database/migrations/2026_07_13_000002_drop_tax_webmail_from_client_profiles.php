<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('client_profiles', 'webmail_address')) {
            Schema::table('client_profiles', function (Blueprint $table) {
                $table->dropUnique('client_profiles_webmail_address_unique');
            });
        }

        Schema::table('client_profiles', function (Blueprint $table) {
            foreach (['tax_identifier', 'webmail_address'] as $column) {
                if (Schema::hasColumn('client_profiles', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('client_profiles', function (Blueprint $table) {
            if (! Schema::hasColumn('client_profiles', 'tax_identifier')) {
                $table->string('tax_identifier')->nullable();
            }
            if (! Schema::hasColumn('client_profiles', 'webmail_address')) {
                $table->string('webmail_address')->nullable()->unique();
            }
        });
    }
};
