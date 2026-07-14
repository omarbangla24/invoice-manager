<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('invoices', 'expense_date')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->dropIndex('invoices_expense_date_index');
            });
        }

        Schema::table('invoices', function (Blueprint $table) {
            foreach (['title', 'expense_date', 'amount'] as $column) {
                if (Schema::hasColumn('invoices', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            if (! Schema::hasColumn('invoices', 'title')) {
                $table->string('title')->nullable();
            }
            if (! Schema::hasColumn('invoices', 'expense_date')) {
                $table->date('expense_date')->nullable()->index();
            }
            if (! Schema::hasColumn('invoices', 'amount')) {
                $table->decimal('amount', 12, 2)->nullable();
            }
        });
    }
};
