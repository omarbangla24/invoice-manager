<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inbound_emails', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_profile_id')->nullable()->constrained()->nullOnDelete();
            $table->string('provider')->nullable();
            $table->string('message_id')->nullable()->unique();
            $table->string('from_email')->index();
            $table->string('to_email')->nullable()->index();
            $table->string('subject')->nullable();
            $table->unsignedInteger('attachment_count')->default(0);
            $table->string('status')->default('received')->index();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inbound_emails');
    }
};
