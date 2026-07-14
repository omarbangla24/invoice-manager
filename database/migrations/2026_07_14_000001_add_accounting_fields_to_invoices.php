<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('supplier_name')->nullable()->after('description');
            $table->string('abn')->nullable()->after('supplier_name');
            $table->string('category')->nullable()->index()->after('abn');
            $table->date('invoice_date')->nullable()->index()->after('category');
            $table->date('due_date')->nullable()->index()->after('invoice_date');
            $table->decimal('invoice_amount', 12, 2)->nullable()->after('due_date');
            $table->decimal('gst_amount', 12, 2)->nullable()->after('invoice_amount');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            foreach (['category', 'invoice_date', 'due_date'] as $indexed) {
                if (Schema::hasColumn('invoices', $indexed)) {
                    $table->dropIndex('invoices_'.$indexed.'_index');
                }
            }
            $table->dropColumn(['supplier_name', 'abn', 'category', 'invoice_date', 'due_date', 'invoice_amount', 'gst_amount']);
        });
    }
};
