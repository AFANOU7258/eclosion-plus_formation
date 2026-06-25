@extends('layouts.admin')
@section('title', $user->name)

@section('content')
<a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700 mb-4">
    <span class="material-icons text-sm">arrow_back</span> Utilisateurs
</a>

<div class="grid lg:grid-cols-3 gap-6">
    {{-- PROFIL --}}
    <div class="lg:col-span-2 space-y-5">
        <div class="bg-white rounded-lg border border-gray-200 p-6">
            <div class="flex items-start justify-between mb-6">
                <div class="flex items-center gap-4">
                    <span class="w-16 h-16 rounded-full flex items-center justify-center text-white text-2xl font-bold
                        {{ $user->role === 'admin' ? 'bg-red-500' : ($user->role === 'instructor' ? 'bg-purple-500' : 'bg-ocean-600') }}">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </span>
                    <div>
                        <h2 class="font-display text-xl text-gray-800">{{ $user->name }}</h2>
                        <p class="text-gray-500 text-sm">{{ $user->email }}</p>
                        <p class="text-xs text-gray-400 mt-1">Inscrit le {{ $user->created_at->format('d/m/Y') }}</p>
                    </div>
                </div>
                <div>
                    <form action="{{ route('admin.users.updateRole', $user) }}" method="POST" class="flex items-center gap-2">
                        @csrf @method('PATCH')
                        <select name="role" class="border border-gray-300 rounded-lg text-sm px-3 py-2 focus:outline-none focus:border-eclosion-500">
                            <option value="student" {{ $user->role === 'student' ? 'selected' : '' }}>Étudiant</option>
                            <option value="instructor" {{ $user->role === 'instructor' ? 'selected' : '' }}>Formateur</option>
                            <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Admin</option>
                        </select>
                        <button type="submit" class="bg-eclosion-600 text-white px-3 py-2 rounded-lg text-xs font-medium hover:bg-eclosion-700 transition">Changer</button>
                    </form>
                </div>
            </div>

            @if($user->bio)
                <p class="text-gray-600 text-sm bg-gray-50 rounded-lg p-4">{{ $user->bio }}</p>
            @endif
        </div>

        {{-- INSCRIPTIONS --}}
        <div class="bg-white rounded-lg border border-gray-200 p-6">
            <h3 class="font-medium text-gray-800 mb-4">Formations ({{ $user->enrollments->count() }})</h3>
            @if($user->enrollments->isEmpty())
                <p class="text-gray-400 text-sm">Aucune inscription.</p>
            @else
            <div class="space-y-2">
                @foreach($user->enrollments as $e)
                <div class="flex items-center justify-between p-3 border rounded-lg">
                    <div>
                        <p class="text-sm font-medium">{{ $e->course->title ?? '—' }}</p>
                        <p class="text-xs text-gray-500">{{ $e->created_at->format('d/m/Y') }}</p>
                    </div>
                    <span class="text-xs px-2 py-1 rounded-full {{ $e->status === 'approuvé' ? 'bg-green-100 text-green-700' : ($e->status === 'en_attente' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') }}">{{ $e->status }}</span>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- COURS CRÉÉS (si formateur) --}}
        @if($user->createdCourses->isNotEmpty())
        <div class="bg-white rounded-lg border border-gray-200 p-6">
            <h3 class="font-medium text-gray-800 mb-4">Cours créés ({{ $user->createdCourses->count() }})</h3>
            <div class="space-y-2">
                @foreach($user->createdCourses as $c)
                <div class="flex items-center justify-between p-3 border rounded-lg">
                    <p class="text-sm font-medium">{{ $c->title }}</p>
                    <span class="text-xs px-2 py-1 rounded-full {{ $c->status === 'published' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">{{ $c->status }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    {{-- SIDEBAR --}}
    <div class="space-y-4">
        <div class="bg-white rounded-lg border border-gray-200 p-5">
            <h3 class="font-medium text-gray-800 text-sm mb-3">Résumé</h3>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between"><span class="text-gray-500">Inscriptions</span><span class="font-medium">{{ $user->enrollments->count() }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Approuvées</span><span class="font-medium text-green-600">{{ $user->enrollments->where('status', 'approuvé')->count() }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">En attente</span><span class="font-medium text-yellow-600">{{ $user->enrollments->where('status', 'en_attente')->count() }}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Cours créés</span><span class="font-medium">{{ $user->createdCourses->count() }}</span></div>
            </div>
        </div>
    </div>
</div>
@endsection
