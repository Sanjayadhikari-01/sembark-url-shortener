<?php

namespace Tests\Feature;

use App\Mail\InvitationMail;
use App\Models\Company;
use App\Models\Invitation;
use App\Models\ShortUrl;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SembarkTest extends TestCase
{
    use RefreshDatabase;

    private function company(): Company
    {
        return Company::create(['name' => 'Acme']);
    }

    private function user(string $role = 'Admin', ?Company $company = null): User
    {
        $u = new User(['name' => 'User', 'email' => fake()->unique()->safeEmail(), 'password' => Hash::make('password')]);
        $u->role = $role;
        $u->company_id = $role === 'SuperAdmin' ? null : ($company ?? $this->company())->id;
        $u->save();

        return $u;
    }

    public function test_guest_is_redirected_and_registration_is_disabled(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/register')->assertNotFound();
    }

    public function test_login_and_logout_work(): void
    {
        $user = $this->user('Admin');
        $this->get('/login')->assertOk();
        $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);
        $this->post('/logout')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_revoked_invitation_cannot_be_accepted(): void
    {
        $admin = $this->user('Admin');
        $invitation = Invitation::create([
            'company_id' => $admin->company_id,
            'invited_by' => $admin->id,
            'email' => 'revoked@example.com',
            'active_email' => 'revoked@example.com',
            'role' => 'Member',
            'token_hash' => hash('sha256', 'revoked-token'),
            'expires_at' => now()->addDay(),
        ]);
        $this->actingAs($admin)->post('/invitations/'.$invitation->id.'/revoke')->assertRedirect('/invitations');
        auth()->logout();
        $this->post('/invite/revoked-token', [
            'name' => 'Revoked Person',
            'password' => 'safe-password',
            'password_confirmation' => 'safe-password',
        ])->assertSessionHasErrors('token');
    }

    public function test_expired_invitation_is_rejected(): void
    {
        $company = $this->company();
        $admin = $this->user('Admin', $company);
        $invitation = Invitation::create([
            'company_id' => $company->id,
            'invited_by' => $admin->id,
            'email' => 'expired@example.com',
            'role' => 'Member',
            'token_hash' => hash('sha256', 'expired-token'),
            'expires_at' => now()->subMinute(),
        ]);
        $this->post('/invite/expired-token', [
            'name' => 'Expired Person',
            'password' => 'safe-password',
            'password_confirmation' => 'safe-password',
        ])->assertSessionHasErrors('token');
        $this->assertDatabaseMissing('users', ['email' => $invitation->email]);
    }

    public function test_admin_and_member_can_create_urls_but_superadmin_cannot(): void
    {
        $c = $this->company();
        foreach (['Admin', 'Member'] as $role) {
            $u = $this->user($role, $c);
            $this->actingAs($u)->post('/urls', ['original_url' => 'https://example.com', 'company_id' => 999, 'user_id' => 999])->assertRedirect('/urls');
            $this->assertDatabaseHas('short_urls', ['company_id' => $c->id, 'user_id' => $u->id]);
        } $this->actingAs($this->user('SuperAdmin'))->post('/urls', ['original_url' => 'https://example.com'])->assertForbidden();
    }

    public function test_invalid_scheme_is_rejected(): void
    {
        $u = $this->user('Member');
        $this->actingAs($u)->from('/urls/create')->post('/urls', ['original_url' => 'javascript:alert(1)'])->assertSessionHasErrors('original_url');
    }

    public function test_listings_are_scoped_and_public_redirect_works(): void
    {
        $a = $this->company();
        $b = $this->company();
        $admin = $this->user('Admin', $a);
        $member = $this->user('Member', $a);
        $other = $this->user('Admin', $b);
        $urlA = new ShortUrl(['original_url' => 'https://a.example', 'code' => 'CodeA']);
        $urlA->company_id = $a->id;
        $urlA->user_id = $member->id;
        $urlA->save();
        $urlB = new ShortUrl(['original_url' => 'https://b.example', 'code' => 'CodeB']);
        $urlB->company_id = $b->id;
        $urlB->user_id = $other->id;
        $urlB->save();
        $this->actingAs($admin)->get('/urls')->assertSee('CodeA')->assertDontSee('CodeB');
        $this->actingAs($member)->get('/urls')->assertSee('CodeA')->assertDontSee('CodeB');
        $this->actingAs($this->user('SuperAdmin'))->get('/urls')->assertSee('CodeA')->assertSee('CodeB');
        $this->get('/r/CodeA')->assertRedirect('https://a.example');
        $this->get('/r/missing')->assertNotFound();
    }

    public function test_invitation_acceptance_assigns_role_and_company_once(): void
    {
        Mail::fake();
        $admin = $this->user('Admin');
        $this->actingAs($admin)->post('/invitations', ['email' => 'invite@example.com', 'role' => 'Member', 'company_name' => 'spoof'])->assertRedirect('/invitations');
        $this->actingAs($admin)->from('/invitations/create')->post('/invitations', ['email' => 'invite@example.com', 'role' => 'Admin'])->assertSessionHasErrors('email');
        $i = Invitation::firstOrFail();
        $token = 'plain-secret-token';
        $i->token_hash = hash('sha256', $token);
        $i->save();
        auth()->logout();
        $this->post('/invite/'.$token, ['name' => 'Invitee', 'password' => 'safe-password', 'password_confirmation' => 'safe-password'])->assertRedirect('/dashboard');
        $this->assertDatabaseHas('users', ['email' => 'invite@example.com', 'company_id' => $admin->company_id, 'role' => 'Member']);
        $this->post('/invite/'.$token, ['name' => 'Again', 'password' => 'safe-password', 'password_confirmation' => 'safe-password'])->assertSessionHasErrors('token');
    }

    public function test_member_cannot_invite_and_superadmin_invites_admin_with_new_company(): void
    {
        Mail::fake();
        $this->actingAs($this->user('Member'))->post('/invitations', ['email' => 'm@example.com', 'role' => 'Admin'])->assertForbidden();
        $root = $this->user('SuperAdmin');
        $this->actingAs($root)->post('/invitations', ['email' => 'admin@example.com', 'company_name' => 'New Co'])->assertRedirect('/invitations');
        $this->assertDatabaseHas('companies', ['name' => 'New Co']);
        $this->assertDatabaseHas('invitations', ['email' => 'admin@example.com', 'role' => 'Admin']);
        Mail::assertSent(InvitationMail::class);
    }

    public function test_invitation_link_is_shown_only_in_demo_mode(): void
    {
        Mail::fake();
        $admin = $this->user('Admin');
        $this->actingAs($admin)->post('/invitations', ['email' => 'hidden@example.com', 'role' => 'Member'])->assertSessionMissing('invitation_link');
        config(['app.show_invitation_link' => true]);
        $this->actingAs($admin)->post('/invitations', ['email' => 'shown@example.com', 'role' => 'Member'])->assertSessionHas('invitation_link');
        $this->get('/invitations')->assertSee('/invite/', false);
    }
}
