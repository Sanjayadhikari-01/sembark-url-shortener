@extends('layout')

@section('content')
    <section class="welcome-panel">
        <div>
            <p class="eyebrow">{{ $companyName ?? 'SEMBARK WORKSPACE' }}</p>
            <h1>Good to see you, {{ auth()->user()->name }}</h1>
            <p class="muted">Here’s what’s happening with your short links.</p>
        </div>
        @if(in_array(auth()->user()->role, ['Admin', 'Member']))
            <a class="button-link" href="{{ route('urls.create') }}">Create a short link <span aria-hidden="true">→</span></a>
        @endif
    </section>

    <section class="stat-grid" aria-label="Workspace summary">
        <article class="stat-card">
            <span class="stat-icon blue" aria-hidden="true">↗</span>
            <p class="stat-label">{{ auth()->user()->role === 'Member' ? 'Your short links' : 'Short links' }}</p>
            <p class="stat-value">{{ number_format($stats['short_urls']) }}</p>
            <a href="{{ route('urls.index') }}">View links <span aria-hidden="true">→</span></a>
        </article>

        @if($stats['users'] !== null)
            <article class="stat-card">
                <span class="stat-icon purple" aria-hidden="true">♙</span>
                <p class="stat-label">{{ auth()->user()->isSuperAdmin() ? 'Users' : 'Company users' }}</p>
                <p class="stat-value">{{ number_format($stats['users']) }}</p>
                <p class="stat-note">Active accounts in your {{ auth()->user()->isSuperAdmin() ? 'workspace' : 'company' }}</p>
            </article>
        @endif

        @if($stats['companies'] !== null)
            <article class="stat-card">
                <span class="stat-icon orange" aria-hidden="true">▦</span>
                <p class="stat-label">Companies</p>
                <p class="stat-value">{{ number_format($stats['companies']) }}</p>
                <p class="stat-note">Across the Sembark workspace</p>
            </article>
        @endif

        @if($stats['pending_invitations'] !== null)
            <article class="stat-card">
                <span class="stat-icon green" aria-hidden="true">✉</span>
                <p class="stat-label">Pending invitations</p>
                <p class="stat-value">{{ number_format($stats['pending_invitations']) }}</p>
                <a href="{{ route('invitations.index') }}">Manage invitations <span aria-hidden="true">→</span></a>
            </article>
        @endif
    </section>

    <section class="content-card">
        <div class="section-heading">
            <div>
                <h2>Recent short links</h2>
                <p class="muted">{{ auth()->user()->role === 'Member' ? 'Links you created' : 'The latest links in your view' }}</p>
            </div>
            <a class="text-link" href="{{ route('urls.index') }}">View all</a>
        </div>

        @if($recentUrls->isEmpty())
            <div class="empty-state">
                <span class="empty-icon" aria-hidden="true">↗</span>
                <h3>No short links yet</h3>
                <p>Your dashboard is ready. Create your first short link and it will appear here.</p>
                @if(in_array(auth()->user()->role, ['Admin', 'Member']))
                    <a class="button-link" href="{{ route('urls.create') }}">Create your first link</a>
                @else
                    <a class="text-link" href="{{ route('urls.index') }}">Browse short links</a>
                @endif
            </div>
        @else
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr><th>Short link</th><th>Destination</th>@if(auth()->user()->role !== 'Member')<th>Creator</th>@endif<th>Created</th></tr>
                    </thead>
                    <tbody>
                        @foreach($recentUrls as $url)
                            <tr>
                                <td><a class="short-code" href="{{ route('redirect', $url->code) }}">{{ route('redirect', $url->code) }}</a></td>
                                <td class="destination-cell">{{ $url->original_url }}</td>
                                @if(auth()->user()->role !== 'Member')<td>{{ $url->creator->name }}</td>@endif
                                <td>{{ $url->created_at->format('M j, Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
@endsection
