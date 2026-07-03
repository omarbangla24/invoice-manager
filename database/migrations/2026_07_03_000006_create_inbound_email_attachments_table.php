<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inbound_email_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inbound_email_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status')->default('unmatched')->index();
            $table->string('original_filename');
            $table->string('stored_path');
            $table->unsignedBigInteger('size')->default(0);
            $table->string('mime_type')->nullable();
            $table->timestamp('transferred_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inbound_email_attachments');
    }
};
