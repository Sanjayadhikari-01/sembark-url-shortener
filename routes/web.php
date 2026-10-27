<?php

use App\Http\Controllers\AppController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => auth()->check() ? redirect()->route('dashboard') : redirect()->route('login'));
Route::middleware('guest')->group(function () {
    Route::get('/login', fn () => view('auth.login'))->name('login');
    Route::post('/login', [AppController::class, 'login'])->middleware('throttle:6,1');
});
Route::get('/invite/{token}', [AppController::class, 'acceptForm'])->name('invitation.accept');
Route::post('/invite/{token}', [AppController::class, 'accept'])->middleware('throttle:6,1')->name('invitation.accept.store');
Route::get('/r/{code}', [AppController::class, 'redirectShort'])->where('code', '[A-Za-z0-9]+')->middleware('throttle:120,1')->name('redirect');
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [AppController::class, 'dashboard'])->name('dashboard');
    Route::post('/logout', [AppController::class, 'logout'])->name('logout');
    Route::get('/urls', [AppController::class, 'urls'])->name('urls.index');
    Route::get('/urls/create', [AppController::class, 'createUrl'])->name('urls.create');
    Route::post('/urls', [AppController::class, 'storeUrl'])->middleware('throttle:30,1')->name('urls.store');
    Route::get('/invitations', [AppController::class, 'invitations'])->name('invitations.index');
    Route::get('/invitations/create', [AppController::class, 'inviteForm'])->name('invitations.create');
    Route::post('/invitations', [AppController::class, 'invite'])->middleware('throttle:10,1')->name('invitations.store');
    Route::post('/invitations/{invitationId}/revoke', [AppController::class, 'revokeInvitation'])->name('invitations.revoke');
});
