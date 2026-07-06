<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        $perPage = min((int) $request->integer('per_page', 20), 100);
        $notifications = $request->user()->notifications()
            ->when($request->filled('status'), fn ($query) => $request->string('status') === 'unread' ? $query->whereNull('read_at') : $query->whereNotNull('read_at'))
            ->when($request->filled('q'), function ($query) use ($request): void {
                $q = $request->string('q');
                $query->where(fn ($query) => $query->where('title', 'like', "%{$q}%")->orWhere('body', 'like', "%{$q}%"));
            })
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
        $request->user()->notifications()->whereNull('read_at')->update(['read_at' => now()]);

        return view('notifications.index', ['notifications' => $notifications]);
    }

    public function feed(Request $request): JsonResponse
    {
        $items = $request->user()->notifications()
            ->whereNull('read_at')
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn ($notification) => [
                'id' => $notification->id,
                'title' => $notification->title,
                'body' => $notification->body,
                'url' => $notification->url,
                'time' => $notification->created_at->diffForHumans(),
            ]);

        return response()->json([
            'count' => $request->user()->notifications()->whereNull('read_at')->count(),
            'items' => $items,
        ]);
    }
}
