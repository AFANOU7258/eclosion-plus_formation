@extends('layouts.admin')
@section('title', 'Demandes d\'accès')

@section('content')
<h1 class="font-display text-2xl font-normal text-gray-800 mb-4">Demandes d'accès</h1>

{{-- ONGLETS STATUT --}}
<div class="flex flex-wrap gap-2 mb-4">
    <a href="?" class="px-4 py-2 rounded-full text-sm font-medium {{ !request('status') ? 'bg-eclosion-600 text-white' : 'bg-white border text-gray-600 hover:bg-gray-50' }}">
        Toutes <span class="ml-1 text-xs opacity-75">({{ $counts['all'] }})</span>
    </a>
    <a href="?status=en_attente" class="px-4 py-2 rounded-full text-sm font-medium {{ request('status') === 'en_attente' ? 'bg-yellow-500 text-white' : 'bg-white border text-gray-600 hover:bg-gray-50' }}">
        En attente <span class="ml-1 text-xs opacity-75">({{ $counts['pending'] }})</span>
    </a>
    <a href="?status=approuvé" class="px-4 py-2 rounded-full text-sm font-medium {{ request('status') === 'approuvé' ? 'bg-green-600 text-white' : 'bg-white border text-gray-600 hover:bg-gray-50' }}">
        Approuvées <span class="ml-1 text-xs opacity-75">({{ $counts['approved'] }})</span>
    </a>
    <a href="?status=refusé" class="px-4 py-2 rounded-full text-sm font-medium {{ request('status') === 'refusé' ? 'bg-red-500 text-white' : 'bg-white border text-gray-600 hover:bg-gray-50' }}">
        Refusées <span class="ml-1 text-xs opacity-75">({{ $counts['rejected'] }})</span>
    </a>
</div>

{{-- RECHERCHE --}}
<form class="mb-4 flex gap-2">
    <input type="hidden" name="status" value="{{ request('status') }}">
    <div class="relative flex-1 max-w-md">
        <span class="material-icons absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-lg">search</span>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher un étudiant ou une formation..."
            class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-full text-sm focus:outline-none focus:border-eclosion-500">
    </div>
    <button type="submit" class="px-4 py-2 bg-gray-100 rounded-full text-sm font-medium hover:bg-gray-200 transition">Rechercher</button>
    @if(request('search'))
        <a href="?status={{ request('status') }}" class="px-3 py-2 text-sm text-red-500 hover:underline">Effacer</a>
    @endif
</form>

<div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="text-left text-gray-500 border-b border-gray-200 bg-gray-50">
                <tr>
                    <th class="px-5 py-3 font-medium">Étudiant</th>
                    <th class="px-5 py-3 font-medium">Formation</th>
                    <th class="px-5 py-3 font-medium">Prix</th>
                    <th class="px-5 py-3 font-medium">Paiement</th>
                    <th class="px-5 py-3 font-medium">Statut</th>
                    <th class="px-5 py-3 font-medium">Date</th>
                    <th class="px-5 py-3 font-medium">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($enrollments as $e)
                <tr class="hover:bg-gray-50">
                    <td class="px-5 py-3">
                        <a href="{{ route('admin.users.show', $e->user) }}" class="flex items-center gap-2 hover:underline">
                            <span class="w-7 h-7 rounded-full bg-ocean-600 text-white flex items-center justify-center text-xs font-bold">{{ strtoupper(substr($e->user->name, 0, 1)) }}</span>
                            <span class="font-medium">{{ $e->user->name }}</span>
                        </a>
                    </td>
                    <td class="px-5 py-3 text-gray-700">{{ $e->course->title }}</td>
                    <td class="px-5 py-3 font-medium">{{ number_format($e->course->price, 0, ',', ' ') }} FCFA</td>
                    <td class="px-5 py-3">
                        @if($e->payment_method)
                            <span class="text-xs px-2 py-1 rounded-full font-medium
                                {{ $e->payment_method === 'orange_money' ? 'bg-orange-100 text-orange-700' : 'bg-blue-100 text-blue-700' }}">
                                {{ $e->payment_method === 'orange_money' ? 'Orange Money' : strtoupper($e->payment_method) }}
                            </span>
                            @if($e->payment_reference)
                                <p class="text-xs text-gray-400 mt-0.5">{{ $e->payment_reference }}</p>
                            @endif
                        @else
                            <span class="text-xs text-gray-400">—</span>
                        @endif
                    </td>
                    <td class="px-5 py-3">
                        <span class="px-2.5 py-1 rounded-full text-xs font-medium
                            {{ $e->status === 'approuvé' ? 'bg-green-100 text-green-800' : '' }}
                            {{ $e->status === 'en_attente' ? 'bg-yellow-100 text-yellow-800' : '' }}
                            {{ $e->status === 'refusé' ? 'bg-red-100 text-red-800' : '' }}">
                            {{ $e->status }}
                        </span>
                        @if($e->approvedBy)
                            <p class="text-xs text-gray-400 mt-0.5">{{ $e->approvedBy->name }}</p>
                        @endif
                    </td>
                    <td class="px-5 py-3 text-gray-500 text-xs">{{ $e->created_at->format('d/m/Y H:i') }}</td>
                    <td class="px-5 py-3">
                        <div class="flex items-center gap-2">
                            <a href="{{ route('admin.enrollments.show', $e) }}" class="text-ocean-600 text-xs hover:underline">Détail</a>
                            @if($e->isPending())
                                <button onclick="act({{ $e->id }},'approve')" class="bg-green-600 text-white w-7 h-7 rounded-full text-xs font-medium hover:bg-green-700 flex items-center justify-center"><span class="material-icons text-sm">check</span></button>
                                                                <button onclick="act({{ $e->id }},'reject')" class="bg-red-100 text-red-700 w-7 h-7 rounded-full text-xs font-medium hover:bg-red-200 flex items-center justify-center"><span class="material-icons text-sm">close</span></button>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
<div class="mt-6">{{ $enrollments->links() }}</div>

<script>
async function act(id, action) {
    if (!confirm(action === 'approve' ? 'Approuver ?' : 'Refuser ?')) return;
    await fetch('/admin/enrollments/' + id + '/' + action, { method: 'PATCH', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' } });
    location.reload();
}
</script>
@endsection
