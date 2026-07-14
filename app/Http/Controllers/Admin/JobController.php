<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Services\AuditLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class JobController extends Controller
{
    public function index(Request $request): View
    {
        $perPage = min((int) $request->integer('per_page', 20), 100);

        return view('admin.jobs.index', [
            'jobs' => Job::query()
                ->when($request->filled('q'), function ($query) use ($request): void {
                    $q = $request->string('q');
                    $query->where(function ($query) use ($q): void {
                        $query->where('type_of_service', 'like', "%{$q}%")
                            ->orWhere('description', 'like', "%{$q}%")
                            ->orWhere('category', 'like', "%{$q}%");
                    });
                })
                ->when($request->filled('category'), fn ($query) => $query->where('category', $request->string('category')))
                ->latest()
                ->paginate($perPage)
                ->withQueryString(),
            'categories' => Job::whereNotNull('category')->distinct()->orderBy('category')->pluck('category'),
        ]);
    }

    public function create(): View
    {
        return view('admin.jobs.create');
    }

    public function store(Request $request, AuditLogger $audit): RedirectResponse
    {
        $validated = $request->validate([
            'type_of_service' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'qty' => ['required', 'integer', 'min:1'],
            'fees' => ['required', 'numeric', 'min:0'],
            'attachment' => ['nullable', 'file', 'max:10240', 'mimes:jpg,jpeg,png,pdf'],
        ]);

        $data = [
            'type_of_service' => $validated['type_of_service'],
            'category' => $validated['category'] ?? null,
            'description' => $validated['description'] ?? null,
            'qty' => $validated['qty'],
            'fees' => $validated['fees'],
        ];

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $basename = now()->format('His').'-'.Str::uuid();
            $extension = strtolower($file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'bin');
            $storedPath = "jobs/{$basename}.{$extension}";

            Storage::disk('local')->put($storedPath, file_get_contents($file->getRealPath()));

            $data['attachment_path'] = $storedPath;
            $data['attachment_filename'] = $file->getClientOriginalName();
            $data['attachment_mime'] = $file->getMimeType();
        }

        $job = Job::create($data);
        $audit->log('job.created', $job);

        return redirect()->route('admin.jobs.index')->with('status', 'Job created.');
    }

    public function edit(Job $job): View
    {
        return view('admin.jobs.edit', ['job' => $job]);
    }

    public function update(Request $request, Job $job, AuditLogger $audit): RedirectResponse
    {
        $validated = $request->validate([
            'type_of_service' => ['required', 'string', 'max:255'],
            'category' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'qty' => ['required', 'integer', 'min:1'],
            'fees' => ['required', 'numeric', 'min:0'],
            'is_active' => ['nullable'],
            'attachment' => ['nullable', 'file', 'max:10240', 'mimes:jpg,jpeg,png,pdf'],
            'remove_attachment' => ['nullable'],
        ]);

        $data = [
            'type_of_service' => $validated['type_of_service'],
            'category' => $validated['category'] ?? null,
            'description' => $validated['description'] ?? null,
            'qty' => $validated['qty'],
            'fees' => $validated['fees'],
            'is_active' => $request->boolean('is_active'),
        ];

        if ($request->hasFile('attachment')) {
            if ($job->attachment_path && Storage::disk('local')->exists($job->attachment_path)) {
                Storage::disk('local')->delete($job->attachment_path);
            }

            $file = $request->file('attachment');
            $basename = now()->format('His').'-'.Str::uuid();
            $extension = strtolower($file->getClientOriginalExtension() ?: $file->guessExtension() ?: 'bin');
            $storedPath = "jobs/{$basename}.{$extension}";

            Storage::disk('local')->put($storedPath, file_get_contents($file->getRealPath()));

            $data['attachment_path'] = $storedPath;
            $data['attachment_filename'] = $file->getClientOriginalName();
            $data['attachment_mime'] = $file->getMimeType();
        } elseif ($request->boolean('remove_attachment')) {
            if ($job->attachment_path && Storage::disk('local')->exists($job->attachment_path)) {
                Storage::disk('local')->delete($job->attachment_path);
            }
            $data['attachment_path'] = null;
            $data['attachment_filename'] = null;
            $data['attachment_mime'] = null;
        }

        $job->update($data);
        $audit->log('job.updated', $job);

        return redirect()->route('admin.jobs.index')->with('status', 'Job updated.');
    }

    public function destroy(Job $job, AuditLogger $audit): RedirectResponse
    {
        if ($job->attachment_path && Storage::disk('local')->exists($job->attachment_path)) {
            Storage::disk('local')->delete($job->attachment_path);
        }

        $audit->log('job.deleted', $job, ['type_of_service' => $job->type_of_service]);
        $job->delete();

        return redirect()->route('admin.jobs.index')->with('status', 'Job deleted.');
    }

    public function downloadAttachment(Job $job): StreamedResponse
    {
        abort_unless($job->attachment_path && Storage::disk('local')->exists($job->attachment_path), 404, 'Attachment not found.');

        return Storage::disk('local')->download($job->attachment_path, $job->attachment_filename);
    }
}
