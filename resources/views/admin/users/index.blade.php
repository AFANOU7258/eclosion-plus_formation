@extends('layouts.admin')
@section('title', 'Utilisateurs')

@section('content')
<h1 class="font-display text-2xl font-normal text-gray-800 mb-4">Utilisateurs</h1>

<div class="flex flex-wrap gap-2 mb-4">
    <a href="?" class="px-4 py-2 rounded-full text-sm font-medium {{ !request('role') ? 'bg-eclosion-600 text-white' : 'bg-white border text-gray-600 hover:bg-gray-50' }}">
        Tous
    </a>
    <a href="?role=student" class="px-4 py-2 rounded-full text-sm font-medium {{ request('role') === 'student' ? 'bg-ocean-600 text-white' : 'bg-white border text-gray-600 hover:bg-gray-50' }}">
        Étudiants ({{ $roles['student'] }})
    </a>
    <a href="?role=instructor" class="px-4 py-2 rounded-full text-sm font-medium {{ request('role') === 'instructor' ? 'bg-purple-600 text-white' : 'bg-white border text-gray-600 hover:bg-gray-50' }}">
        Formateurs ({{ $roles['instructor'] }})
    </a>
    <a href="?role=admin" class="px-4 py-2 rounded-full text-sm font-medium {{ request('role') === 'admin' ? 'bg-red-600 text-white' : 'bg-white border text-gray-600 hover:bg-gray-50' }}">
        Admins ({{ $roles['admin'] }})
    </a>
</div>

<form class="mb-4 flex gap-2">
    <input type="hidden" name="role" value="{{ request('role') }}">
    <div class="relative flex-1 max-w-md">
        <span class="material-icons absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher par nom ou email..."
            class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-full text-sm focus:outline-none focus:border-eclosion-500">
    </div>
    <button type="submit" class="px-4 py-2 bg-gray-100 rounded-full text-sm font-medium hover:bg-gray-200">Rechercher</button>
</form>

<div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="text-left text-gray-500 border-b border-gray-200 bg-gray-50">
            <tr>
                <th class="px-5 py-3 font-medium">Utilisateur</th>
                <th class="px-5 py-3 font-medium">Rôle</th>
                <th class="px-5 py-3 font-medium">Inscriptions</th>
                <th class="px-5 py-3 font-medium">Cours créés</th>
                <th class="px-5 py-3 font-medium">Inscrit le</th>
                <th class="px-5 py-3 font-medium"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @foreach($users as $u)
            <tr class="hover:bg-gray-50">
                <td class="px-5 py-3">
                    <a href="{{ route('admin.users.show', $u) }}" class="flex items-center gap-3 hover:underline">
                        <span class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold shrink-0
                            {{ $u->role === 'admin' ? 'bg-red-500' : ($u->role === 'instructor' ? 'bg-purple-500' : 'bg-ocean-600') }}">
                            {{ strtoupper(substr($u->name, 0, 1)) }}
                        </span>
                        <div>
                            <p class="font-medium text-gray-800">{{ $u->name }}</p>
                            <p class="text-xs text-gray-400">{{ $u->email }}</p>
                        </div>
                    </a>
                </td>
                <td class="px-5 py-3">
                    <span class="px-2.5 py-1 rounded-full text-xs font-medium
                        {{ $u->role === 'admin' ? 'bg-red-100 text-red-700' : '' }}
                        {{ $u->role === 'instructor' ? 'bg-purple-100 text-purple-700' : '' }}
                        {{ $u->role === 'student' ? 'bg-ocean-100 text-ocean-700' : '' }}">
                        {{ $u->role }}
                    </span>
                </td>
                <td class="px-5 py-3">{{ $u->enrollments_count }}</td>
                <td class="px-5 py-3">{{ $u->created_courses_count }}</td>
                <td class="px-5 py-3 text-gray-500 text-xs">{{ $u->created_at->format('d/m/Y') }}</td>
                <td class="px-5 py-3">
                    <a href="{{ route('admin.users.show', $u) }}" class="text-ocean-600 text-xs hover:underline">Détail</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div class="mt-6">{{ $users->links() }}</div>
@endsection
