<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $perPage = min((int) $request->integer('per_page', 30), 100);

        return view('admin.audit-logs.index', [
            'logs' => AuditLog::query()
                ->when($request->filled('q'), function ($query) use ($request): void {
                    $q = $request->string('q');
                    $query->where('action', 'like', "%{$q}%")
                        ->orWhere('subject_type', 'like', "%{$q}%")
                        ->orWhere('metadata', 'like', "%{$q}%");
                })
                ->when($request->filled('action'), fn ($query) => $query->where('action', $request->string('action')))
                ->when($request->filled('date_from'), fn ($query) => $query->whereDate('created_at', '>=', $request->date('date_from')))
                ->when($request->filled('date_to'), fn ($query) => $query->whereDate('created_at', '<=', $request->date('date_to')))
                ->latest()
                ->paginate($perPage)
                ->withQueryString(),
            'actions' => AuditLog::query()->select('action')->distinct()->orderBy('action')->pluck('action'),
        ]);
    }
}
