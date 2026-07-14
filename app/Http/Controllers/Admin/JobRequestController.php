<?php

namespace App\Http\Controllers\Admin;

use App\Enums\JobRequestStatus;
use App\Http\Controllers\Controller;
use App\Models\ClientProfile;
use App\Models\JobRequest;
use App\Services\AuditLogger;
use App\Services\Notifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JobRequestController extends Controller
{
    public function index(Request $request): View
    {
        $perPage = min((int) $request->integer('per_page', 20), 100);

        return view('admin.job-requests.index', [
            'jobRequests' => JobRequest::with('clientProfile.user')
                ->withCount('items')
                ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
                ->when($request->filled('client'), fn ($query) => $query->where('client_profile_id', $request->integer('client')))
                ->when($request->filled('date_from'), fn ($query) => $query->whereDate('created_at', '>=', $request->date('date_from')))
                ->when($request->filled('date_to'), fn ($query) => $query->whereDate('created_at', '<=', $request->date('date_to')))
                ->when($request->filled('q'), function ($query) use ($request): void {
                    $q = $request->string('q');
                    $query->where(function ($query) use ($q): void {
                        $query->where('remarks', 'like', "%{$q}%")
                            ->orWhereHas('clientProfile', fn ($query) => $query->where('business_name', 'like', "%{$q}%"));
                    });
                })
                ->latest()
                ->paginate($perPage)
                ->withQueryString(),
            'statuses' => JobRequestStatus::cases(),
            'clients' => ClientProfile::orderBy('business_name')->get(['id', 'business_name']),
        ]);
    }

    public function show(JobRequest $jobRequest): View
    {
        return view('admin.job-requests.show', [
            'jobRequest' => $jobRequest->load('clientProfile.user', 'items.job'),
            'statuses' => JobRequestStatus::cases(),
        ]);
    }

    public function updateStatus(Request $request, JobRequest $jobRequest, AuditLogger $audit, Notifier $notifier): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,in_progress,completed,cancelled'],
        ]);

        $status = JobRequestStatus::from($validated['status']);
        $jobRequest->forceFill(['status' => $status])->save();
        $audit->log('job_request.status_updated', $jobRequest, ['status' => $validated['status']]);

        $notifier->notify(
            $jobRequest->clientProfile->user,
            'Job request status updated',
            'Your job request #'.$jobRequest->id.' is now '.$status->label(),
            route('client.job-requests.show', $jobRequest),
        );

        return back()->with('status', 'Job request status updated.');
    }

    public function downloadAttachment(JobRequest $jobRequest): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        abort_unless($jobRequest->attachment_path && \Illuminate\Support\Facades\Storage::disk('local')->exists($jobRequest->attachment_path), 404, 'Attachment not found.');

        return \Illuminate\Support\Facades\Storage::disk('local')->download($jobRequest->attachment_path, $jobRequest->attachment_filename);
    }
}
