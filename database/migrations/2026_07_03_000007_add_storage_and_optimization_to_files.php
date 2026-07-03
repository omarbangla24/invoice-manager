<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('storage_disk')->default('local')->after('mime_type');
            $table->string('optimization_status')->default('queued')->after('storage_disk');
            $table->text('optimization_notes')->nullable()->after('optimization_status');
        });

        Schema::table('inbound_email_attachments', function (Blueprint $table) {
            $table->string('storage_disk')->default('local')->after('mime_type');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['storage_disk', 'optimization_status', 'optimization_notes']);
        });

        Schema::table('inbound_email_attachments', function (Blueprint $table) {
            $table->dropColumn('storage_disk');
        });
    }
};
