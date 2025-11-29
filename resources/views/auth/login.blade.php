@extends('layouts.auth')

@section('title', 'Login')
@section('form_title', 'Masuk ke Akun')
@section('form_subtitle', 'Gunakan email dan password Anda untuk masuk')

@section('content')

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <label class="block text-sm font-medium text-gray-700">Email</label>
            <input type="email" name="email" required autofocus
                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-green-600 focus:border-green-600" />
        </div>

        <!-- Password -->
        <div>
            <label class="block text-sm font-medium text-gray-700">Password</label>
            <input type="password" name="password" required autocomplete="current-password"
                class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:ring-green-600 focus:border-green-600" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <button type="submit"
                class="w-full bg-[#064e3b] hover:bg-green-800 text-white py-2 rounded-lg font-semibold transition">
                Masuk
            </button>
        </div>
    </form>
@endsection
