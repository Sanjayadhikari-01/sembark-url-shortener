<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#111c34">
    <title>@yield('title', 'Sembark') · Sembark</title>
    <style>
        :root{font-family:Inter,ui-sans-serif,system-ui,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;color:#182338;background:#f5f7fb;font-synthesis:none;text-rendering:optimizeLegibility;--navy:#111c34;--blue:#365ce8;--muted:#728097;--line:#e8edf4;--card:#fff}
        *{box-sizing:border-box}body{margin:0;min-height:100vh}a{color:#365ce8;text-decoration:none}a:hover{text-decoration:underline}button,input,select{font:inherit}.topbar{height:72px;background:var(--navy);color:white;display:flex;align-items:center;padding:0 max(24px,calc((100vw - 1180px)/2));gap:28px}.brand{display:flex;align-items:center;gap:10px;color:white;font-size:18px;font-weight:750;letter-spacing:-.03em}.brand-mark{width:32px;height:32px;border-radius:10px;background:linear-gradient(140deg,#718dff,#4561e8);display:grid;place-items:center;font-size:19px}.nav-links{display:flex;align-items:center;gap:8px}.nav-links a{color:#c1cbdd;padding:9px 12px;border-radius:8px;font-size:14px;font-weight:550}.nav-links a:hover,.nav-links a.active{background:#26334d;color:white;text-decoration:none}.account{margin-left:auto;display:flex;align-items:center;gap:12px}.avatar{width:34px;height:34px;border-radius:50%;background:#e4eaff;color:#3554cf;display:grid;place-items:center;font-weight:700}.account-name{font-size:13px;line-height:1.3}.account-name small{display:block;color:#aebbd0;font-size:11px}.logout-form{margin:0}.logout-button{background:transparent;color:#d4dced;border:1px solid #3b4861;padding:8px 11px;border-radius:8px;cursor:pointer;font-size:13px}.logout-button:hover{background:#26334d}.page-shell{max-width:1180px;margin:38px auto;padding:0 24px 56px}.flash{padding:14px 16px;border:1px solid #bde7d0;background:#effbf4;color:#17613c;border-radius:11px;margin-bottom:20px}.validation-errors{padding:12px 18px;border:1px solid #f1c1be;background:#fff4f2;color:#a53029;border-radius:10px;margin-bottom:20px}.welcome-panel{display:flex;justify-content:space-between;align-items:center;gap:24px;margin-bottom:28px}.eyebrow{font-size:11px;letter-spacing:.12em;font-weight:750;color:#6375aa;margin:0 0 10px;text-transform:uppercase}.welcome-panel h1{font-size:30px;letter-spacing:-.04em;margin:0 0 8px}.muted{color:var(--muted);margin:0;font-size:14px}.button-link{display:inline-flex;align-items:center;gap:12px;background:var(--blue);color:#fff;padding:11px 16px;border-radius:9px;font-size:14px;font-weight:650;box-shadow:0 4px 12px #365ce82b}.button-link:hover{color:white;background:#294bd0;text-decoration:none}.stat-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(205px,1fr));gap:16px;margin-bottom:24px}.stat-card,.content-card{background:var(--card);border:1px solid var(--line);border-radius:14px;box-shadow:0 4px 16px #1d2a3d08}.stat-card{position:relative;padding:20px 20px 18px;min-height:164px}.stat-icon{position:absolute;right:18px;top:18px;width:36px;height:36px;border-radius:11px;display:grid;place-items:center;font-size:19px}.blue{color:#365ce8;background:#edf1ff}.purple{color:#8052cc;background:#f4edff}.orange{color:#c6781a;background:#fff3e3}.green{color:#21845a;background:#e8f8ef}.stat-label{font-size:13px;color:#6c7890;margin:0 0 12px}.stat-value{font-size:30px;letter-spacing:-.04em;font-weight:750;margin:0 0 13px}.stat-card>a,.stat-note{font-size:12px;color:#77849a}.stat-note{margin:0}.content-card{padding:22px 24px}.section-heading{display:flex;justify-content:space-between;align-items:center;margin-bottom:18px}.section-heading h2{font-size:17px;letter-spacing:-.02em;margin:0 0 5px}.section-heading .muted{font-size:12px}.text-link{font-size:13px;font-weight:650}.empty-state{text-align:center;padding:38px 15px 32px;border:1px dashed #dce3ed;border-radius:12px;background:#fbfcfe}.empty-icon{width:46px;height:46px;border-radius:14px;background:#edf1ff;color:#4561e8;display:grid;place-items:center;font-size:24px;margin:0 auto 14px}.empty-state h3{margin:0 0 7px;font-size:16px}.empty-state p{color:var(--muted);font-size:13px;margin:0 auto 18px;max-width:390px;line-height:1.6}.empty-state .text-link{display:inline-block}.table-wrap{overflow-x:auto}table{width:100%;border-collapse:collapse;font-size:13px}th{text-align:left;color:#7a879b;font-size:11px;text-transform:uppercase;letter-spacing:.06em;font-weight:650;padding:11px 10px;border-bottom:1px solid var(--line)}td{padding:14px 10px;border-bottom:1px solid #eff2f6;vertical-align:middle}tbody tr:last-child td{border-bottom:0}.short-code{font-weight:650;white-space:nowrap}.destination-cell{max-width:350px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:#617087}main h1{font-size:26px;letter-spacing:-.035em}main h2{font-size:18px}main form:not(.logout-form){max-width:620px;background:white;border:1px solid var(--line);padding:24px;border-radius:14px;box-shadow:0 4px 16px #1d2a3d08}label{display:grid;gap:7px;color:#41516b;font-size:13px;font-weight:600}input,select{width:100%;border:1px solid #d6deea;background:white;border-radius:8px;padding:11px 12px;outline:none}input:focus,select:focus{border-color:#6581f2;box-shadow:0 0 0 3px #365ce81c}main button:not(.logout-button){background:var(--blue);color:white;border:0;border-radius:8px;padding:11px 15px;cursor:pointer;font-weight:650;width:max-content}main button:hover{filter:brightness(.94)}
        @media(max-width:720px){.topbar{height:auto;min-height:66px;flex-wrap:wrap;gap:10px;padding:14px 18px}.brand{margin-right:auto}.nav-links{order:3;width:100%;overflow:auto}.nav-links a{padding:8px 10px;white-space:nowrap}.account{margin-left:0}.account-name{display:none}.page-shell{margin:26px auto;padding:0 16px 40px}.welcome-panel{align-items:flex-start;flex-direction:column}.welcome-panel h1{font-size:25px}.content-card{padding:18px 14px}.stat-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.stat-card{padding:16px;min-height:150px}.stat-icon{width:30px;height:30px;right:13px;top:13px}}
        @media(max-width:390px){.stat-grid{grid-template-columns:1fr}.logout-button{padding:7px 9px}}
    </style>
</head>
<body>
<header class="topbar">
    <a class="brand" href="{{ auth()->check() ? route('dashboard') : route('login') }}"><span class="brand-mark" aria-hidden="true">↗</span><span>Sembark</span></a>
    @auth
        <nav class="nav-links" aria-label="Main navigation">
            <a class="{{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">Dashboard</a>
            <a class="{{ request()->routeIs('urls.*') ? 'active' : '' }}" href="{{ route('urls.index') }}">Short links</a>
            @if(auth()->user()->role !== 'Member')
                <a class="{{ request()->routeIs('invitations.*') ? 'active' : '' }}" href="{{ route('invitations.index') }}">Invitations</a>
            @endif
        </nav>
        <div class="account">
            <span class="avatar" aria-hidden="true">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
            <span class="account-name">{{ auth()->user()->name }}<small>{{ auth()->user()->role }}</small></span>
            <form class="logout-form" method="post" action="{{ route('logout') }}">
                @csrf
                <button class="logout-button">Log out</button>
            </form>
        </div>
    @endauth
</header>
<main class="page-shell">
    @if(session('status'))
        <div class="flash" role="status">{{ session('status') }}</div>
    @endif
    @if($errors->any())
        <div class="validation-errors" role="alert"><ul>
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul></div>
    @endif
    @yield('content')
</main>
</body>
</html>
