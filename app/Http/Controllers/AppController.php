<?php

namespace App\Http\Controllers;

use App\Mail\InvitationMail;
use App\Models\Company;
use App\Models\Invitation;
use App\Models\ShortUrl;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AppController extends Controller
{
    public function login(Request $r)
    {
        $data = $r->validate(['email' => 'required|email', 'password' => 'required|string']);
        if (! Auth::attempt($data, $r->boolean('remember'))) {
            return back()->withErrors(['email' => 'These credentials do not match our records.'])->onlyInput('email');
        } $r->session()->regenerate();

        return redirect()->route('dashboard');
    }

    public function dashboard()
    {
        $user = auth()->user();
        $links = ShortUrl::query();

        if ($user->role === 'Admin') {
            $links->where('company_id', $user->company_id);
        } elseif ($user->role === 'Member') {
            $links->where('user_id', $user->id);
        }

        $stats = [
            'short_urls' => (clone $links)->count(),
            'users' => $user->isSuperAdmin()
                ? User::count()
                : ($user->role === 'Admin' ? User::where('company_id', $user->company_id)->count() : null),
            'companies' => $user->isSuperAdmin() ? Company::count() : null,
            'pending_invitations' => $user->isSuperAdmin() || $user->role === 'Admin'
                ? Invitation::whereNull('accepted_at')
                    ->whereNull('revoked_at')
                    ->where('expires_at', '>', now())
                    ->when(! $user->isSuperAdmin(), fn ($query) => $query->where('company_id', $user->company_id))
                    ->count()
                : null,
        ];

        $recentUrls = (clone $links)->with('creator')->latest()->limit(5)->get();

        return view('dashboard', [
            'stats' => $stats,
            'recentUrls' => $recentUrls,
            'companyName' => $user->company?->name,
        ]);
    }

    public function urls()
    {
        $q = ShortUrl::with(['company', 'creator'])->latest();
        $u = auth()->user();
        if (! $u->isSuperAdmin()) {
            if ($u->role === 'Admin') {
                $q->where('company_id', $u->company_id);
            } else {
                $q->where('user_id', $u->id);
            }
        }

        return view('urls.index', ['urls' => $q->paginate(15)]);
    }

    public function createUrl()
    {
        abort_unless(in_array(auth()->user()->role, ['Admin', 'Member']), 403);

        return view('urls.create');
    }

    public function storeUrl(Request $r)
    {
        abort_unless(in_array(auth()->user()->role, ['Admin', 'Member']), 403);
        $data = $r->validate(['original_url' => ['required', 'url:http,https', 'max:2048']]);
        $user = auth()->user();
        abort_unless($user->company_id, 403);
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $code = Str::random(8);
            if (ShortUrl::where('code', $code)->exists()) {
                continue;
            } $url = new ShortUrl(['original_url' => $data['original_url'], 'code' => $code]);
            $url->company_id = $user->company_id;
            $url->user_id = $user->id;
            try {
                $url->save();

                return redirect()->route('urls.index')->with('status', 'Short URL created: '.route('redirect', $code));
            } catch (UniqueConstraintViolationException $e) {
                continue;
            }
        } throw new \RuntimeException('Could not allocate a unique short code. Please try again.');
    }

    public function redirectShort(string $code)
    {
        $url = ShortUrl::where('code', $code)->firstOrFail();

        return redirect()->away($url->original_url);
    }

    public function invitations()
    {
        $u = auth()->user();
        abort_unless($u->isSuperAdmin() || $u->role === 'Admin', 403);
        $q = Invitation::with('company', 'inviter')->latest();
        if (! $u->isSuperAdmin()) {
            $q->where('company_id', $u->company_id);
        }

        return view('invitations.index', ['invitations' => $q->paginate(15)]);
    }

    public function inviteForm()
    {
        $u = auth()->user();
        abort_unless($u->isSuperAdmin() || $u->role === 'Admin', 403);

        return view('invitations.create');
    }

    public function invite(Request $r)
    {
        $u = auth()->user();
        abort_unless($u->isSuperAdmin() || $u->role === 'Admin', 403);
        $d = $r->validate(['email' => 'required|email|max:255', 'role' => $u->isSuperAdmin() ? 'nullable' : 'required|in:Admin,Member', 'company_name' => $u->isSuperAdmin() ? 'required|string|max:255' : 'nullable|string|max:255']);
        $email = Str::lower(trim($d['email']));
        if (User::where('email', $email)->exists()) {
            throw ValidationException::withMessages(['email' => 'An account already exists for this email.']);
        }
        $role = $u->isSuperAdmin() ? 'Admin' : $d['role'];
        $token = Str::random(64);
        try {
            DB::transaction(function () use ($u, $d, $email, $role, $token) {
                Invitation::whereNotNull('active_email')->where('expires_at', '<=', now())->update(['active_email' => null]);
                if (Invitation::where('active_email', $email)->lockForUpdate()->exists()) {
                    throw ValidationException::withMessages(['email' => 'An active invitation already exists for this email.']);
                }
                $company = $u->isSuperAdmin() ? Company::create(['name' => $d['company_name']]) : $u->company;
                if (! $company) {
                    abort(403);
                }

                Invitation::create([
                    'company_id' => $company->id,
                    'invited_by' => $u->id,
                    'email' => $email,
                    'active_email' => $email,
                    'role' => $role,
                    'token_hash' => hash('sha256', $token),
                    'expires_at' => now()->addDays((int) config('app.invitation_expiry_days', 7)),
                ]);
            });
        } catch (UniqueConstraintViolationException $e) {
            throw ValidationException::withMessages(['email' => 'An active invitation already exists for this email.']);
        }
        $link = route('invitation.accept', ['token' => $token]);
        Mail::to($email)->send(new InvitationMail($link));

        return redirect()->route('invitations.index')->with('status', 'Invitation sent to '.$email)
            ->with('invitation_link', config('app.show_invitation_link') ? $link : null);
    }

    public function revokeInvitation(int $invitationId)
    {
        $user = auth()->user();
        abort_unless($user->isSuperAdmin() || $user->role === 'Admin', 403);
        $query = Invitation::query();
        if (! $user->isSuperAdmin()) {
            $query->where('company_id', $user->company_id);
        }
        $invitation = $query->findOrFail($invitationId);
        abort_if($invitation->accepted_at || $invitation->revoked_at, 422, 'This invitation is no longer pending.');
        $invitation->update(['revoked_at' => now(), 'active_email' => null]);

        return redirect()->route('invitations.index')->with('status', 'Invitation revoked.');
    }

    public function acceptForm(string $token)
    {
        $i = $this->findInvitation($token);

        return view('invitations.accept', ['token' => $token, 'invitation' => $i]);
    }

    public function accept(Request $r, string $token)
    {
        $d = $r->validate(['name' => 'required|string|max:255', 'password' => 'required|string|min:8|confirmed']);

        return DB::transaction(function () use ($token, $d) {
            $i = Invitation::where('token_hash', hash('sha256', $token))->lockForUpdate()->first();
            if (! $i || $i->accepted_at || $i->revoked_at || $i->expires_at->isPast()) {
                throw ValidationException::withMessages(['token' => 'This invitation is invalid, expired, or already used.']);
            } if (User::where('email', $i->email)->exists()) {
                throw ValidationException::withMessages(['token' => 'An account already exists for this email. Contact your administrator.']);
            } $user = new User(['name' => $d['name'], 'email' => $i->email, 'password' => Hash::make($d['password'])]);
            $user->company_id = $i->company_id;
            $user->role = $i->role;
            $user->save();
            $i->update(['accepted_at' => now(), 'active_email' => null]);
            Auth::login($user);
            request()->session()->regenerate();

            return redirect()->route('dashboard')->with('status', 'Welcome to Sembark.');
        });
    }

    private function findInvitation(string $token): Invitation
    {
        $i = Invitation::where('token_hash', hash('sha256', $token))->first();
        if (! $i || $i->accepted_at || $i->revoked_at || $i->expires_at->isPast()) {
            throw ValidationException::withMessages(['token' => 'This invitation is invalid, expired, or already used.']);
        }

        return $i;
    }

    public function logout(Request $r)
    {
        Auth::logout();
        $r->session()->invalidate();
        $r->session()->regenerateToken();

        return redirect()->route('login');
    }
}
