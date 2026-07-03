<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Invoice Portal') }}</title>
    <link rel="stylesheet" href="/app.css">
    <script defer src="/app.js"></script>
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
