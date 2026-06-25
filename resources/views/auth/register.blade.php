@extends('layouts.app')
@section('title', 'Inscription')

@section('content')
<div class="max-w-md mx-auto px-4 py-20">
    <h1 class="text-3xl font-bold text-cloud-900 text-center mb-8">Créer un compte</h1>

    <form method="POST" action="{{ url('/register') }}" class="bg-white border border-cloud-200 rounded-2xl p-8 space-y-5">
        @csrf

        <div>
            <label class="block text-sm font-medium text-cloud-700 mb-1">Nom complet</label>
            <input type="text" name="name" value="{{ old('name') }}" required autofocus
                class="w-full border border-cloud-300 rounded-xl px-4 py-3 focus:outline-none focus:border-eclosion-500">
            @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-cloud-700 mb-1">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" required
                class="w-full border border-cloud-300 rounded-xl px-4 py-3 focus:outline-none focus:border-eclosion-500">
            @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-cloud-700 mb-1">Mot de passe</label>
            <input type="password" name="password" required
                class="w-full border border-cloud-300 rounded-xl px-4 py-3 focus:outline-none focus:border-eclosion-500">
            @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium text-cloud-700 mb-1">Confirmer le mot de passe</label>
            <input type="password" name="password_confirmation" required
                class="w-full border border-cloud-300 rounded-xl px-4 py-3 focus:outline-none focus:border-eclosion-500">
        </div>

        <button type="submit" class="w-full bg-eclosion-600 text-white font-semibold py-3 rounded-xl hover:bg-eclosion-700 transition">
            S'inscrire
        </button>

        <p class="text-center text-sm text-cloud-500">
            Déjà un compte ? <a href="{{ url('/login') }}" class="text-ocean-600 hover:underline">Se connecter</a>
        </p>
    </form>
</div>
@endsection
