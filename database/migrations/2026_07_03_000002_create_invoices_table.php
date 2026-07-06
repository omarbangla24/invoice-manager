<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('source')->default('portal')->index();
            $table->string('status')->default('pending')->index();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->date('expense_date')->nullable()->index();
            $table->decimal('amount', 12, 2)->nullable();
            $table->string('currency', 3)->default('AUD');
            $table->string('original_filename');
            $table->string('stored_path');
            $table->string('compressed_path')->nullable();
            $table->unsignedBigInteger('original_size')->default(0);
            $table->unsignedBigInteger('compressed_size')->nullable();
            $table->string('mime_type')->nullable();
            $table->timestamp('counted_at')->nullable();
            $table->timestamp('declined_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
