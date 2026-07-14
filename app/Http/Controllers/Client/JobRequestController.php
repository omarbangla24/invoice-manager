<?php

namespace App\Http\Controllers\Client;

use App\Enums\JobRequestStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\JobRequest;
use App\Models\User;
use App\Services\AuditLogger;
use App\Services\Notifier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class JobRequestController extends Controller
{
    public function index(Request $request): View
    {
        $client = $request->user()->clientProfile;
        abort_unless($client, 403);
        $perPage = min((int) $request->integer('per_page', 20), 100);

        return view('client.job-requests.index', [
            'jobRequests' => $client->jobRequests()
                ->withCount('items')
                ->when($request->filled('status'), fn ($query) => $query->where('status', $request->string('status')))
                ->when($request->filled('date_from'), fn ($query) => $query->whereDate('created_at', '>=', $request->date('date_from')))
                ->when($request->filled('date_to'), fn ($query) => $query->whereDate('created_at', '<=', $request->date('date_to')))
                ->when($request->filled('q'), function ($query) use ($request): void {
                    $q = $request->string('q');
                    $query->where('remarks', 'like', "%{$q}%");
                })
                ->latest()
                ->paginate($perPage)
                ->withQueryString(),
            'statuses' => JobRequestStatus::cases(),
        ]);
    }

    public function create(Request $request): View
    {
        $perPage = min((int) $request->integer('per_page', 10), 50);

        return view('client.job-requests.create', [
            'jobs' => Job::where('is_active', true)
                ->when($request->filled('q'), function ($query) use ($request): void {
                    $q = $request->string('q');
                    $query->where(function ($query) use ($q): void {
                        $query->where('type_of_service', 'like', "%{$q}%")
                            ->orWhere('description', 'like', "%{$q}%")
                            ->orWhere('category', 'like', "%{$q}%");
                    });
                })
                ->when($request->filled('category'), fn ($query) => $query->where('category', $request->string('category')))
                ->orderBy('category')
                ->orderBy('type_of_service')
                ->paginate($perPage)
                ->withQueryString(),
            'categories' => Job::where('is_active', true)->whereNotNull('category')->distinct()->orderBy('category')->pluck('category'),
        ]);
    }

    public function store(Request $request, AuditLogger $audit, Notifier $notifier): RedirectResponse
    {
        $client = $request->user()->clientProfile;
        abort_unless($client, 403);

        $validated = $request->validate([
            'jobs' => ['required', 'array', 'min:1'],
            'jobs.*.job_id' => ['required', 'exists:services,id'],
            'jobs.*.qty' => ['required', 'integer', 'min:1'],
            'jobs.*.selected' => ['nullable'],
            'remarks' => ['nullable', 'string', 'max:2000'],
            'attachment' => ['nullable', 'file', 'max:10240', 'mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx'],
        ]);

        $selectedJobs = collect($validated['jobs'])->filter(fn ($item) => ! empty($item['selected']));

        if ($selectedJobs->isEmpty()) {
            return back()->withErrors(['jobs' => 'Please select at least one job.'])->withInput();
        }

        $jobRequest = DB::transaction(function () use ($request, $client, $selectedJobs, $validated): JobRequest {
            $data = [
                'status' => JobRequestStatus::Pending,
                'remarks' => $validated['remarks'] ?? null,
            ];

            if ($request->hasFile('attachment')) {
                $file = $request->file('attachment');
                $basename = now()->format('His') . '-' . \Illuminate\Support\Str::uuid();
                $extension = strtolower($file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'bin');
                $storedPath = "job-requests/{$basename}.{$extension}";

                \Illuminate\Support\Facades\Storage::disk('local')->put($storedPath, file_get_contents($file->getRealPath()));

                $data['attachment_path'] = $storedPath;
                $data['attachment_filename'] = $file->getClientOriginalName();
                $data['attachment_mime'] = $file->getMimeType();
            }

            $jobRequest = $client->jobRequests()->create($data);

            foreach ($selectedJobs as $item) {
                $job = Job::findOrFail($item['job_id']);
                $jobRequest->items()->create([
                    'service_id' => $job->id,
                    'qty' => $item['qty'],
                    'unit_price' => $job->fees,
                ]);
            }

            return $jobRequest;
        });

        $audit->log('job_request.created', $jobRequest);
        User::query()->where('role', UserRole::Admin)->each(
            fn (User $admin) => $notifier->notify($admin, 'New job request', $client->business_name.' submitted a job request', route('admin.job-requests.show', $jobRequest))
        );

        return redirect()->route('client.job-requests.index')->with('status', 'Job request submitted.');
    }

    public function show(Request $request, JobRequest $jobRequest): View
    {
        abort_unless($jobRequest->client_profile_id === $request->user()->clientProfile?->id, 403);

        return view('client.job-requests.show', [
            'jobRequest' => $jobRequest->load('items.job'),
        ]);
    }

    public function downloadAttachment(Request $request, JobRequest $jobRequest): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        abort_unless($jobRequest->client_profile_id === $request->user()->clientProfile?->id, 403);
        abort_unless($jobRequest->attachment_path && \Illuminate\Support\Facades\Storage::disk('local')->exists($jobRequest->attachment_path), 404, 'Attachment not found.');

        return \Illuminate\Support\Facades\Storage::disk('local')->download($jobRequest->attachment_path, $jobRequest->attachment_filename);
    }
}
