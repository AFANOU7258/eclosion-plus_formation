@extends('layouts.admin')
@section('title', 'Tableau de bord')

@section('content')
<div class="p-6">
    <div class="flex items-center justify-between mb-6">
        <h1 class="font-display text-xl font-normal text-gray-800">Tableau de bord</h1>
        <span class="text-xs text-gray-400">{{ now()->format('d/m/Y H:i') }}</span>
    </div>

    {{-- STATS --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3 mb-6">
        <div class="bg-white rounded-xl border border-gray-200 p-4 hover:shadow transition">
            <div class="flex items-center justify-between mb-2"><span class="material-icons text-eclosion-600">school</span><span class="text-[10px] text-gray-400 uppercase font-bold">Formations</span></div>
            <p class="text-2xl font-display text-gray-800">{{ $stats['courses_total'] }}</p><p class="text-[10px] text-gray-400">{{ $stats['courses_published'] }} publiées</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 hover:shadow transition">
            <div class="flex items-center justify-between mb-2"><span class="material-icons text-ocean-600">people</span><span class="text-[10px] text-gray-400 uppercase font-bold">Apprenants</span></div>
            <p class="text-2xl font-display text-gray-800">{{ $stats['students_total'] }}</p><p class="text-[10px] text-gray-400">{{ $stats['instructors_total'] }} formateurs</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 hover:shadow transition">
            <div class="flex items-center justify-between mb-2"><span class="material-icons text-yellow-600">hourglass_empty</span><span class="text-[10px] text-gray-400 uppercase font-bold">En attente</span></div>
            <p class="text-2xl font-display text-gray-800">{{ $stats['enrollments_pending'] }}</p><p class="text-[10px] text-gray-400">à valider</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 hover:shadow transition">
            <div class="flex items-center justify-between mb-2"><span class="material-icons text-green-600">check_circle</span><span class="text-[10px] text-gray-400 uppercase font-bold">Approuvés</span></div>
            <p class="text-2xl font-display text-gray-800">{{ $stats['enrollments_approved'] }}</p><p class="text-[10px] text-gray-400">/ {{ $stats['enrollments_total'] }} total</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 hover:shadow transition">
            <div class="flex items-center justify-between mb-2"><span class="material-icons text-purple-600">smart_toy</span><span class="text-[10px] text-gray-400 uppercase font-bold">Chats IA</span></div>
            <p class="text-2xl font-display text-gray-800">{{ $stats['conversations_total'] }}</p><p class="text-[10px] text-gray-400">conversations</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 hover:shadow transition">
            <div class="flex items-center justify-between mb-2"><span class="material-icons text-gray-700">payments</span><span class="text-[10px] text-gray-400 uppercase font-bold">Revenus</span></div>
            <p class="text-2xl font-display text-gray-800">{{ number_format($stats['revenue_total'],0,',',' ') }}</p><p class="text-[10px] text-gray-400">FCFA</p>
        </div>
    </div>

    <div class="grid lg:grid-cols-3 gap-6">
        {{-- DEMANDES RÉCENTES --}}
        <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-100 flex items-center justify-between">
                <h2 class="text-sm font-medium text-gray-800">Demandes récentes</h2>
                <a href="{{ route('admin.enrollments.index') }}" class="text-ocean-600 text-xs font-medium hover:underline">Tout voir</a>
            </div>
            <div class="overflow-x-auto"><table class="w-full text-sm"><thead class="text-left text-gray-400 text-xs bg-gray-50"><tr><th class="px-5 py-2.5 font-medium">Étudiant</th><th class="px-5 py-2.5 font-medium">Formation</th><th class="px-5 py-2.5 font-medium">Paiement</th><th class="px-5 py-2.5 font-medium">Statut</th><th class="px-5 py-2.5 font-medium"></th></tr></thead><tbody class="divide-y divide-gray-50">
            @foreach($recentEnrollments as $e)
            <tr class="hover:bg-gray-50"><td class="px-5 py-2.5"><div class="flex items-center gap-2"><span class="w-6 h-6 rounded-full bg-ocean-600 text-white flex items-center justify-center text-[10px] font-bold">{{ strtoupper(substr($e->user->name,0,1)) }}</span><span class="font-medium text-xs">{{ $e->user->name }}</span></div></td><td class="px-5 py-2.5 text-xs text-gray-600">{{ $e->course->title }}</td><td class="px-5 py-2.5 text-xs">@if($e->payment_method)<span class="text-[10px] px-1.5 py-0.5 rounded-full {{ $e->payment_method==='orange_money'?'bg-orange-100 text-orange-700':'bg-blue-100 text-blue-700' }}">{{ $e->payment_method==='orange_money'?'OM':'Wave' }}</span>@else — @endif</td><td class="px-5 py-2.5"><span class="text-[10px] px-2 py-0.5 rounded-full font-medium {{ $e->status==='approuvé'?'bg-green-100 text-green-700':($e->status==='en_attente'?'bg-yellow-100 text-yellow-700':'bg-red-100 text-red-700') }}">{{ $e->status }}</span></td><td class="px-5 py-2.5">@if($e->isPending())<button onclick="act({{$e->id}},'approve')" class="text-green-600 text-[10px] font-bold hover:underline mr-2">Approuver</button><button onclick="act({{$e->id}},'reject')" class="text-red-400 text-[10px] font-bold hover:underline">Refuser</button>@endif</td></tr>
            @endforeach
            </tbody></table></div>
        </div>

        {{-- TOP FORMATIONS --}}
        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <div class="px-5 py-3 border-b border-gray-100"><h2 class="text-sm font-medium text-gray-800">Top formations</h2></div>
            <div class="divide-y divide-gray-50">
                @foreach($topCourses as $i => $c)
                <div class="flex items-center gap-3 px-5 py-3"><span class="text-xs font-bold {{ $i<3 ? 'text-eclosion-600' : 'text-gray-400' }} w-5">#{{$i+1}}</span><div class="flex-1 min-w-0"><p class="text-xs font-medium text-gray-800 truncate">{{$c->title}}</p><p class="text-[10px] text-gray-400">{{$c->enrollments_count}} inscrits · {{number_format($c->price,0,',',' ')}} FCFA</p></div></div>
                @endforeach
            </div>
        </div>
    </div>
</div>
<script>async function act(id,a){await fetch('/admin/enrollments/'+id+'/'+a,{method:'PATCH',headers:{'X-CSRF-TOKEN':'{{csrf_token()}}','Accept':'application/json'}});location.reload()}</script>
@endsection
