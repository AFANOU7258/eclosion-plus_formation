@extends('layouts.admin')
@section('title', 'Demande #' . $enrollment->id)

@section('content')
<a href="{{ url()->previous() }}" class="inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700 mb-4">
    <span class="material-icons text-sm">arrow_back</span> Retour
</a>

<div class="grid lg:grid-cols-3 gap-6">
    {{-- INFOS DEMANDE --}}
    <div class="lg:col-span-2 space-y-5">
        <div class="bg-white rounded-lg border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-display text-lg text-gray-800">Demande d'accès</h2>
                <span class="px-3 py-1 rounded-full text-xs font-bold
                    {{ $enrollment->status === 'approuvé' ? 'bg-green-100 text-green-800' : '' }}
                    {{ $enrollment->status === 'en_attente' ? 'bg-yellow-100 text-yellow-800' : '' }}
                    {{ $enrollment->status === 'refusé' ? 'bg-red-100 text-red-800' : '' }}">
                    {{ strtoupper($enrollment->status) }}
                </span>
            </div>

            <div class="grid sm:grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-gray-500 text-xs mb-0.5">Étudiant</p>
                    <p class="font-medium">{{ $enrollment->user->name }}</p>
                    <p class="text-gray-500">{{ $enrollment->user->email }}</p>
                </div>
                <div>
                    <p class="text-gray-500 text-xs mb-0.5">Formation</p>
                    <p class="font-medium">{{ $enrollment->course->title }}</p>
                    <p class="text-gray-600">{{ number_format($enrollment->course->price , 0, ',', ' ')  }} FCFA</p>
                </div>
                <div>
                    <p class="text-gray-500 text-xs mb-0.5">Demandé le</p>
                    <p>{{ $enrollment->created_at->format('d/m/Y à H:i') }}</p>
                </div>
                <div>
                    <p class="text-gray-500 text-xs mb-0.5">Traité par</p>
                    <p>{{ $enrollment->approvedBy ? $enrollment->approvedBy->name : '—' }}</p>
                    <p class="text-xs text-gray-400">{{ $enrollment->approved_at ? $enrollment->approved_at->format('d/m/Y H:i') : '' }}</p>
                </div>
                @if($enrollment->payment_method)
                <div class="sm:col-span-2 bg-blue-50 rounded-lg p-3">
                    <p class="text-xs text-blue-800 mb-0.5 font-medium">💳 Paiement {{ strtoupper($enrollment->payment_method) }}</p>
                    <p class="text-sm font-medium text-blue-900">Réf: {{ $enrollment->payment_reference ?? 'N/A' }}</p>
                </div>
                @endif
            </div>

            @if($enrollment->isPending())
            <div class="flex gap-3 mt-6 pt-6 border-t border-gray-100">
                <button onclick="act('approve')" class="bg-green-600 text-white px-6 py-2.5 rounded-full text-sm font-medium hover:bg-green-700 transition flex items-center gap-1.5"><span class="material-icons text-sm">check</span> Approuver</button>
                                <button onclick="act('reject')" class="bg-red-50 text-red-700 border border-red-200 px-6 py-2.5 rounded-full text-sm font-medium hover:bg-red-100 transition flex items-center gap-1.5"><span class="material-icons text-sm">close</span> Refuser</button>
            </div>
            @endif
        </div>

        {{-- PROGRAMME DE LA FORMATION --}}
        <div class="bg-white rounded-lg border border-gray-200 p-6">
            <h3 class="font-medium text-gray-800 mb-3">Programme : {{ $enrollment->course->title }}</h3>
            <div class="space-y-2">
                @foreach($enrollment->course->levels as $level)
                <div class="border rounded-lg p-3">
                    <p class="text-sm font-medium text-gray-800">{{ $level->title }}</p>
                    <p class="text-xs text-gray-500 mb-2">{{ $level->description }}</p>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach($level->lessons as $lesson)
                        <span class="text-xs bg-gray-100 rounded-full px-2.5 py-1 text-gray-600">
                            {{ $lesson->media_type === 'video' ? '🎬' : ($lesson->media_type === 'audio' ? '🎧' : '📄') }}
                            {{ $lesson->title }}
                        </span>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- SIDEBAR --}}
    <div class="space-y-4">
        <div class="bg-white rounded-lg border border-gray-200 p-5">
            <h3 class="font-medium text-gray-800 text-sm mb-3">Étudiant</h3>
            <div class="flex items-center gap-3">
                <span class="w-12 h-12 rounded-full bg-ocean-600 text-white flex items-center justify-center text-lg font-bold">{{ strtoupper(substr($enrollment->user->name, 0, 1)) }}</span>
                <div>
                    <p class="font-medium">{{ $enrollment->user->name }}</p>
                    <p class="text-xs text-gray-500">{{ $enrollment->user->email }}</p>
                    <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-600 mt-1 inline-block">{{ $enrollment->user->role }}</span>
                </div>
            </div>
            <a href="{{ route('admin.users.show', $enrollment->user) }}" class="block text-center text-ocean-600 text-xs font-medium mt-3 hover:underline">Voir le profil</a>
        </div>

        <div class="bg-white rounded-lg border border-gray-200 p-5">
            <h3 class="font-medium text-gray-800 text-sm mb-2">Statistiques étudiant</h3>
            <p class="text-xs text-gray-500">Formations : {{ $enrollment->user->enrollments->count() }}</p>
            <p class="text-xs text-gray-500">Approuvées : {{ $enrollment->user->enrollments->where('status', 'approuvé')->count() }}</p>
        </div>
    </div>
</div>

<script>
async function act(action) {
    if (!confirm('Confirmer ' + action + ' ?')) return;
    await fetch('/admin/enrollments/{{ $enrollment->id }}/' + action, { method: 'PATCH', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' } });
    location.reload();
}
</script>
@endsection
