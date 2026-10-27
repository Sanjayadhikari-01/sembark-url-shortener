<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = strtolower(trim((string) env('SUPERADMIN_EMAIL')));
        $password = (string) env('SUPERADMIN_PASSWORD');
        if (! $email || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('Set a valid SUPERADMIN_EMAIL before seeding.');
        } if (! $password || app()->environment('production') && strlen($password) < 16) {
            throw new RuntimeException('Set SUPERADMIN_PASSWORD (at least 16 characters in production).');
        } $exists = DB::table('users')->where('email', $email)->exists();
        if ($exists) {
            return;
        } DB::insert('INSERT INTO users (company_id, name, email, email_verified_at, password, role, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?)', [null, 'SuperAdmin', $email, now(), Hash::make($password), 'SuperAdmin', now(), now()]);
    }
}
