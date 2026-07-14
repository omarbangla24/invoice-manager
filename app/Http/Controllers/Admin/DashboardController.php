<?php

namespace App\Http\Controllers\Admin;

use App\Enums\InvoiceStatus;
use App\Enums\JobRequestStatus;
use App\Http\Controllers\Controller;
use App\Models\ClientProfile;
use App\Models\Invoice;
use App\Models\JobRequest;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $statusCounts = Invoice::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $jrStatusCounts = JobRequest::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('admin.dashboard', [
            'clientCount' => ClientProfile::count(),
            'invoiceCount' => Invoice::count(),
            'pendingCount' => $statusCounts[InvoiceStatus::Pending->value] ?? 0,
            'doneCount' => $statusCounts[InvoiceStatus::Done->value] ?? 0,
            'declinedCount' => $statusCounts[InvoiceStatus::Declined->value] ?? 0,
            'jobRequestCount' => JobRequest::count(),
            'jobRequestPendingCount' => $jrStatusCounts[JobRequestStatus::Pending->value] ?? 0,
            'jobRequestInProgressCount' => $jrStatusCounts[JobRequestStatus::InProgress->value] ?? 0,
            'jobRequestCompletedCount' => $jrStatusCounts[JobRequestStatus::Completed->value] ?? 0,
            'clients' => ClientProfile::withCount([
                'invoices',
                'invoices as pending_invoices_count' => fn ($query) => $query->where('status', InvoiceStatus::Pending),
                'invoices as done_invoices_count' => fn ($query) => $query->where('status', InvoiceStatus::Done),
                'invoices as declined_invoices_count' => fn ($query) => $query->where('status', InvoiceStatus::Declined),
            ])->latest()->limit(8)->get(),
        ]);
    }
}
