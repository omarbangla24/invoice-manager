<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_requests', function (Blueprint $table) {
            $table->string('attachment_path')->nullable()->after('remarks');
            $table->string('attachment_filename')->nullable()->after('attachment_path');
            $table->string('attachment_mime')->nullable()->after('attachment_filename');
        });
    }

    public function down(): void
    {
        Schema::table('job_requests', function (Blueprint $table) {
            $table->dropColumn(['attachment_path', 'attachment_filename', 'attachment_mime']);
        });
    }
};
