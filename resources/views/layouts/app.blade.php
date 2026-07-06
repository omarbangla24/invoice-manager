<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Invoice Portal') }}</title>
    <link rel="stylesheet" href="/app.css?v={{ filemtime(public_path('app.css')) }}">
    <script defer src="/app.js?v={{ filemtime(public_path('app.js')) }}"></script>
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
                    <a data-short="D" href="{{ route('admin.dashboard') }}">Dashboard</a>
                    <a data-short="N" href="{{ route('notifications.index') }}">Notifications {{ auth()->user()->notifications()->whereNull('read_at')->count() ? '('.auth()->user()->notifications()->whereNull('read_at')->count().')' : '' }}</a>
                    <a data-short="C" href="{{ route('admin.clients.index') }}">Clients</a>
                    <a data-short="I" href="{{ route('admin.invoices.index') }}">Invoices</a>
                    <a data-short="E" href="{{ route('admin.unmatched-emails.index') }}">Unmatched Email</a>
                    <a data-short="A" href="{{ route('admin.audit-logs.index') }}">Audit Logs</a>
                    <a data-short="S" href="{{ route('admin.settings.edit') }}">Settings</a>
                @else
                    <a data-short="D" href="{{ route('client.dashboard') }}">Dashboard</a>
                    <a data-short="N" href="{{ route('notifications.index') }}">Notifications {{ auth()->user()->notifications()->whereNull('read_at')->count() ? '('.auth()->user()->notifications()->whereNull('read_at')->count().')' : '' }}</a>
                    <a data-short="U" href="{{ route('client.invoices.create') }}">Upload Invoice</a>
                    <a data-short="I" href="{{ route('client.invoices.index') }}">My Invoices</a>
                    <a data-short="S" href="{{ route('client.settings.edit') }}">Settings</a>
                @endif
                <form method="post" action="{{ route('logout') }}">
                    @csrf
                    <button class="logout" type="submit" data-short="L">Logout</button>
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
