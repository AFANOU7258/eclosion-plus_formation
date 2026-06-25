@extends('layouts.app')
@section('title', 'Connexion')

@section('content')
<div class="max-w-md mx-auto px-4 py-20">
    <h1 class="text-3xl font-bold text-cloud-900 text-center mb-8">Connexion</h1>

    <form method="POST" action="{{ url('/login') }}" class="bg-white border border-cloud-200 rounded-2xl p-8 space-y-5">
        @csrf

        <div>
            <label class="block text-sm font-medium text-cloud-700 mb-1">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required autofocus
                class="w-full border border-cloud-300 rounded-xl px-4 py-3 focus:outline-none focus:border-eclosion-500">
            @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-cloud-700 mb-1">Mot de passe</label>
            <input type="password" name="password" required
                class="w-full border border-cloud-300 rounded-xl px-4 py-3 focus:outline-none focus:border-eclosion-500">
            @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="flex items-center gap-2">
            <input type="checkbox" name="remember" id="remember" class="rounded">
            <label for="remember" class="text-sm text-cloud-500">Se souvenir de moi</label>
        </div>

        <button type="submit" class="w-full bg-eclosion-600 text-white font-semibold py-3 rounded-xl hover:bg-eclosion-700 transition">
            Se connecter
        </button>

        <p class="text-center text-sm text-cloud-500">
            Pas encore de compte ? <a href="{{ url('/register') }}" class="text-ocean-600 hover:underline">S'inscrire</a>
        </p>
    </form>
</div>
@endsection
