<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Invoice Portal') }}</title>
    @php
        $assetVersion = function (string $file) {
            foreach ([public_path($file), base_path($file), base_path('public/'.$file)] as $path) {
                if (is_file($path)) {
                    return filemtime($path);
                }
            }
            return config('app.asset_version', '1');
        };
    @endphp
    <link rel="stylesheet" href="/app.css?v={{ $assetVersion('app.css') }}">
    <script defer src="/app.js?v={{ $assetVersion('app.js') }}"></script>
</head>
<body>
@auth
    <div class="mobile-overlay" data-sidebar-close></div>
    <div class="shell" id="appShell">
        <aside class="sidebar">
            <div class="sidebar-head">
                <div class="brand">Invoice Portal</div>
                <button class="sidebar-toggle" type="button" data-sidebar-toggle aria-label="Toggle sidebar">☰</button>
                <button class="sidebar-close" type="button" data-sidebar-close aria-label="Close sidebar">×</button>
            </div>
            <nav class="nav">
                @if(auth()->user()->isAdmin())
                    <a data-short="D" @class(['active' => request()->routeIs('admin.dashboard')]) href="{{ route('admin.dashboard') }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                        Dashboard
                    </a>

                    <div class="nav-group">Accounting</div>
                    <a data-short="C" @class(['active' => request()->routeIs('admin.clients.*')]) href="{{ route('admin.clients.index') }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                        Clients
                    </a>
                    <a data-short="I" @class(['active' => request()->routeIs('admin.invoices.*')]) href="{{ route('admin.invoices.index') }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><line x1="10" y1="9" x2="8" y2="9"/></svg>
                        Invoices
                    </a>
                    <a data-short="E" @class(['active' => request()->routeIs('admin.unmatched-emails.*') || request()->routeIs('admin.unmatched-attachments.*')]) href="{{ route('admin.unmatched-emails.index') }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2Z"/><polyline points="22,6 12,13 2,6"/><path d="M12 17l-3 3"/><path d="M9 20l-3-3"/></svg>
                        Unmatched Email
                    </a>

                    <div class="nav-group">Services</div>
                    <a data-short="J" @class(['active' => request()->routeIs('admin.job-requests.*')]) href="{{ route('admin.job-requests.index') }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="8" y="2" width="8" height="4" rx="1" ry="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="M12 11h4"/><path d="M12 16h4"/><path d="M8 11h.01"/><path d="M8 16h.01"/></svg>
                        Job Requests
                    </a>
                    <a data-short="V" @class(['active' => request()->routeIs('admin.jobs.*')]) href="{{ route('admin.jobs.index') }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
                        Jobs
                    </a>

                    <div class="nav-group">System</div>
                    <a data-short="S" @class(['active' => request()->routeIs('admin.settings.*')]) href="{{ route('admin.settings.edit') }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1Z"/></svg>
                        Settings
                    </a>
                @else
                    <a data-short="D" @class(['active' => request()->routeIs('client.dashboard')]) href="{{ route('client.dashboard') }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                        Dashboard
                    </a>

                    <div class="nav-group">Invoices</div>
                    <a data-short="U" @class(['active' => request()->routeIs('client.invoices.create')]) href="{{ route('client.invoices.create') }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                        Upload Invoice
                    </a>
                    <a data-short="I" @class(['active' => request()->routeIs('client.invoices.*') && ! request()->routeIs('client.invoices.create')]) href="{{ route('client.invoices.index') }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><line x1="10" y1="9" x2="8" y2="9"/></svg>
                        My Invoices
                    </a>

                    <div class="nav-group">Services</div>
                    <a data-short="J" @class(['active' => request()->routeIs('client.job-requests.*')]) href="{{ route('client.job-requests.index') }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="8" y="2" width="8" height="4" rx="1" ry="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="M12 11h4"/><path d="M12 16h4"/><path d="M8 11h.01"/><path d="M8 16h.01"/></svg>
                        Job Requests
                    </a>

                    <div class="nav-group">System</div>
                    <a data-short="S" @class(['active' => request()->routeIs('client.settings.*')]) href="{{ route('client.settings.edit') }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1Z"/></svg>
                        Settings
                    </a>
                @endif
                <form method="post" action="{{ route('logout') }}">
                    @csrf
                    <button class="logout" type="submit" data-short="L">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                        Logout
                    </button>
                </form>
            </nav>
        </aside>
        <main class="main">
            <div class="mobile-topbar">
                <button class="icon-btn" type="button" data-sidebar-open aria-label="Open sidebar">☰</button>
                <span>Invoice Portal</span>
            </div>
            <div class="app-topbar">
                <div class="notif" data-notif>
                    <button class="icon-btn notif-btn" type="button" data-notif-toggle aria-label="Notifications">
                        <svg class="notif-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/>
                            <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                        </svg>
                        <span class="notif-badge" data-notif-badge hidden>0</span>
                    </button>
                    <div class="notif-panel" data-notif-panel hidden>
                        <div class="notif-panel-head">Notifications</div>
                        <div class="notif-list" data-notif-list>
                            <div class="notif-empty">No new notifications.</div>
                        </div>
                        <a class="notif-all" href="{{ route('notifications.index') }}">View all</a>
                    </div>
                </div>
            </div>
            @if(session('status'))
                <div class="alert ok">{{ session('status') }}</div>
            @endif
            @yield('content')
        </main>
    </div>
@else
    @yield('content')
@endauth
</body>
</html>
