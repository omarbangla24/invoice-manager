<?php

namespace App\Http\Controllers\Admin;

use App\Enums\InvoiceStatus;
use App\Http\Controllers\Controller;
use App\Models\ClientProfile;
use App\Models\Invoice;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $statusCounts = Invoice::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.dashboard', [
            'clientCount' => ClientProfile::count(),
            'invoiceCount' => Invoice::count(),
            'pendingCount' => $statusCounts[InvoiceStatus::Pending->value] ?? 0,
            'doneCount' => $statusCounts[InvoiceStatus::Done->value] ?? 0,
            'declinedCount' => $statusCounts[InvoiceStatus::Declined->value] ?? 0,
            'recentInvoices' => Invoice::with('clientProfile.user')->latest()->limit(8)->get(),
            'clients' => ClientProfile::withCount([
                'invoices',
                'invoices as pending_invoices_count' => fn ($query) => $query->where('status', InvoiceStatus::Pending),
                'invoices as done_invoices_count' => fn ($query) => $query->where('status', InvoiceStatus::Done),
                'invoices as declined_invoices_count' => fn ($query) => $query->where('status', InvoiceStatus::Declined),
            ])->latest()->limit(8)->get(),
        ]);
    }
}
