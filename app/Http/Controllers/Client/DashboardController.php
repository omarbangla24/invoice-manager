<?php

namespace App\Http\Controllers\Client;

use App\Enums\InvoiceStatus;
use App\Enums\JobRequestStatus;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $client = $request->user()->clientProfile;
        abort_unless($client, 403);

        $counts = $client->invoices()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $jrCounts = $client->jobRequests()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return view('client.dashboard', [
            'client' => $client,
            'pendingCount' => $counts[InvoiceStatus::Pending->value] ?? 0,
            'doneCount' => $counts[InvoiceStatus::Done->value] ?? 0,
            'declinedCount' => $counts[InvoiceStatus::Declined->value] ?? 0,
            'jobRequestCount' => $client->jobRequests()->count(),
            'jobRequestPendingCount' => $jrCounts[JobRequestStatus::Pending->value] ?? 0,
            'jobRequestInProgressCount' => $jrCounts[JobRequestStatus::InProgress->value] ?? 0,
            'jobRequestCompletedCount' => $jrCounts[JobRequestStatus::Completed->value] ?? 0,
            'recentInvoices' => $client->invoices()->latest()->limit(10)->get(),
        ]);
    }
}
