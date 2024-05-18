<?php

use Illuminate\Support\Facades\Route;
use Laravel\Socialite\Facades\Socialite;

Route::get('/auth/redirect', function () {
    return Socialite::driver('google')->redirect();
});

Route::get('/auth/callback', function () {
    try {
        $googleData = Socialite::driver('google')->user();
    } catch (\Laravel\Socialite\Two\InvalidStateException) {
        return Socialite::driver('google')->redirect();
    }

    $user = \App\Models\User::where('email', $googleData->getEmail())->first();
    $avatar = file_get_contents($googleData->getAvatar());

    if (!$user) {
        $user = \App\Models\User::create([
            'avatar' => base64_encode($avatar),
            'email' => $googleData->getEmail(),
            'name' => $googleData->getName(),
        ]);
    }

    $token = $user->createToken('google')->plainTextToken;

    // return file_get_contents(public_path() . '/index.html');
    return redirect()->route('home', ['token' => $token]);
});

Route::middleware('auth:sanctum')->get('/me', function () {
    return \Illuminate\Support\Facades\Auth::user();
});

Route::get('/login', [\App\Http\Controllers\SpaController::class, 'index'])->name('login');

Route::get('/reset-password/{token}', function (string $token) {
    return view('auth.reset-password', ['token' => $token]);
})->middleware('guest')->name('password.reset');

Route::get('/{any_path?}', [\App\Http\Controllers\SpaController::class, 'index'])
    ->where('any_path', '(.*)')->name('home');
