<?php

namespace App\Jobs;

use App\Models\Invoice;
use App\Services\AuditLogger;
use App\Services\InvoiceFileService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class OptimizeInvoiceFile implements ShouldQueue
{
    use Queueable;

    public function __construct(public int $invoiceId)
    {
        //
    }

    public function handle(InvoiceFileService $files, AuditLogger $audit): void
    {
        $invoice = Invoice::find($this->invoiceId);

        if (! $invoice) {
            return;
        }

        $files->optimize($invoice);
        $audit->log('invoice.optimized', $invoice->refresh(), [
            'status' => $invoice->optimization_status,
            'compressed_size' => $invoice->compressed_size,
        ]);
    }
}
