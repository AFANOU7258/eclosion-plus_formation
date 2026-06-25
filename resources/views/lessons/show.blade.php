@extends('layouts.app')
@section('title', $lesson->title)

@php
    $course = $lesson->level->course;
    $allLessons = $course->lessons;
    $currentIdx = $allLessons->search(fn($l) => $l->id === $lesson->id);
    $prev = $currentIdx > 0 ? $allLessons[$currentIdx - 1] : null;
    $next = $currentIdx < $allLessons->count() - 1 ? $allLessons[$currentIdx + 1] : null;
    $totalLessons = $allLessons->count();
@endphp

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 py-4">
    {{-- FIL D'ARIANE --}}
    <nav class="text-sm text-gray-400 mb-3">
        <a href="{{ url('/') }}" class="hover:text-eclosion-600">Accueil</a> ›
        <a href="{{ route('courses.show', $course) }}" class="hover:text-eclosion-600">{{ $course->title }}</a> ›
        <span class="text-gray-700">{{ $lesson->title }}</span>
    </nav>

    {{-- BARRE DE PROGRESSION --}}
    <div class="bg-white rounded-lg border border-gray-200 p-3 mb-4 flex items-center gap-4">
        <span class="text-xs text-gray-500">Leçon {{ $currentIdx + 1 }}/{{ $totalLessons }}</span>
        <div class="flex-1 bg-gray-200 rounded-full h-1.5">
            <div class="bg-eclosion-600 h-1.5 rounded-full transition-all" style="width: {{ ($currentIdx + 1) / $totalLessons * 100 }}%"></div>
        </div>
        @auth
        <button onclick="markComplete({{ $lesson->id }})" id="complete-btn" class="px-4 py-1.5 rounded-full text-xs font-medium transition shrink-0
                    {{ $lesson->isCompletedBy(Auth::user()) ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600 hover:bg-eclosion-100 hover:text-eclosion-600' }}">
                    @if($lesson->isCompletedBy(Auth::user()))
                        <span class="material-icons text-sm align-middle">check_circle</span> Terminée
                    @else
                        Marquer
                    @endif
                </button>
        @endauth
    </div>

    <div class="grid lg:grid-cols-4 gap-5">
        {{-- SIDEBAR : PLAN DU COURS --}}
        <div class="lg:col-span-1 order-2 lg:order-1">
            <div class="bg-white rounded-lg border border-gray-200 sticky top-20">
                <div class="p-3 border-b border-gray-200 font-medium text-sm text-gray-800">Plan du cours</div>
                <div class="divide-y divide-gray-100 max-h-[70vh] overflow-y-auto">
                    @foreach($course->levels as $level)
                    <div class="p-2">
                        <p class="text-xs font-medium text-eclosion-600 uppercase px-2 py-1">{{ $level->title }}</p>
                        @foreach($level->lessons as $l)
                        <a href="{{ route('lessons.show', $l) }}" class="flex items-center gap-2 px-2 py-2 rounded-lg text-sm transition
                            {{ $l->id === $lesson->id ? 'bg-eclosion-50 text-eclosion-700 font-medium' : 'text-gray-600 hover:bg-gray-50' }}">
                            <span class="w-5 h-5 rounded-full flex items-center justify-center text-xs shrink-0
                                {{ $l->id === $lesson->id ? 'bg-eclosion-600 text-white' : 'bg-gray-200 text-gray-500' }}">
                                {{ $l->order }}
                            </span>
                            <span class="truncate">{{ $l->title }}</span>
                            @if($l->isCompletedBy(Auth::user() ?? \App\User::first()))
                                <span class="material-icons text-green-500 text-sm ml-auto shrink-0">check_circle</span>
                            @endif
                        </a>
                        @endforeach
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- LECTEUR --}}
        <div class="lg:col-span-2 order-1 lg:order-2 space-y-4">
            {{-- VIDÉO / MEDIA --}}
            <div class="bg-black rounded-lg overflow-hidden">
                @if($lesson->isVideo() && $lesson->media_path)
                    <video controls class="w-full aspect-video" src="{{ $lesson->media_url }}"></video>
                @elseif($lesson->isAudio() && $lesson->media_path)
                    <div class="p-12 flex flex-col items-center justify-center text-white aspect-video bg-gradient-to-br from-eclosion-800 to-ocean-900">
                        <span class="material-icons large text-eclosion-300 mb-4">headphones</span>
                        <audio controls class="w-full max-w-md"><source src="{{ $lesson->media_url }}" type="audio/mpeg"></audio>
                    </div>
                @elseif($lesson->isPdf() && $lesson->media_path)
                    <div class="p-12 flex flex-col items-center justify-center text-white aspect-video bg-gradient-to-br from-ocean-800 to-eclosion-900">
                        <span class="material-icons large text-ocean-300 mb-4">picture_as_pdf</span>
                        <a href="{{ $lesson->media_url }}" target="_blank" class="bg-white text-ocean-800 font-medium px-6 py-3 rounded-full hover:bg-gray-100 transition">📄 Ouvrir le PDF</a>
                    </div>
                @else
                    <div class="p-12 flex flex-col items-center justify-center text-white aspect-video bg-gradient-to-br from-eclosion-800 to-eclosion-900">
                        <span class="material-icons large text-eclosion-300 mb-4">videocam_off</span>
                        <p class="text-white font-medium">Média en cours de production</p>
                    </div>
                @endif
            </div>

            {{-- INFOS LEÇON --}}
            <div class="bg-white rounded-lg border border-gray-200 p-5">
                <span class="text-xs text-eclosion-600 font-medium uppercase">{{ $lesson->level->title }}</span>
                <h1 class="font-display text-xl font-normal text-gray-800 mt-1 mb-3">{{ $lesson->title }}</h1>
                @if($lesson->content)
                    <p class="text-gray-600 text-sm leading-relaxed mb-4">{{ $lesson->content }}</p>
                @endif

                {{-- ILLUSTRATIONS --}}
                    @if($lesson->illustrations && count($lesson->illustrations) > 0)
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-3 mt-4 pt-4 border-t border-gray-100">
                        @foreach($lesson->illustrations as $img)
                            <a href="{{ asset('storage/'.$img) }}" target="_blank" class="block rounded-lg overflow-hidden border border-gray-200 hover:shadow transition">
                                <img src="{{ asset('storage/'.$img) }}" alt="Illustration" class="w-full h-32 object-cover">
                            </a>
                        @endforeach
                    </div>
                    @endif
                </div>

                {{-- COMMENTAIRES --}}
                <div class="bg-white rounded-lg border border-gray-200 p-5">
                    <h3 class="font-medium text-gray-800 mb-4 flex items-center gap-2"><span class="material-icons">forum</span> Questions & Réponses</h3>
                    <div id="comments-list" class="space-y-4 mb-4 max-h-96 overflow-y-auto">
                        <p class="text-gray-400 text-sm text-center py-4">Chargement...</p>
                    </div>
                    @auth
                    <form onsubmit="postComment(event)" class="flex gap-2">
                        <input type="text" id="comment-input" placeholder="Posez votre question..."
                            class="flex-1 border border-gray-300 rounded-full px-4 py-2.5 text-sm focus:outline-none focus:border-eclosion-500">
                        <button type="submit" class="bg-eclosion-600 text-white px-4 py-2 rounded-full text-sm font-medium hover:bg-eclosion-700 transition">
                            <span class="material-icons text-sm">send</span>
                        </button>
                    </form>
                    @endauth
                </div>

            {{-- NAVIGATION PRÉCÉDENT / SUIVANT --}}
            <div class="flex gap-3">
                @if($prev)
                    <a href="{{ route('lessons.show', $prev) }}" class="flex-1 bg-white border border-gray-200 rounded-lg p-4 hover:border-eclosion-300 hover:shadow transition group">
                        <span class="text-xs text-gray-400">← Leçon précédente</span>
                        <p class="text-sm font-medium text-gray-800 group-hover:text-eclosion-600">{{ $prev->title }}</p>
                    </a>
                @else
                    <div class="flex-1"></div>
                @endif
                @if($next)
                    <a href="{{ route('lessons.show', $next) }}" class="flex-1 bg-white border border-gray-200 rounded-lg p-4 text-right hover:border-eclosion-300 hover:shadow transition group">
                        <span class="text-xs text-gray-400">Leçon suivante →</span>
                        <p class="text-sm font-medium text-gray-800 group-hover:text-eclosion-600">{{ $next->title }}</p>
                    </a>
                @else
                    <div class="flex-1"></div>
                @endif
            </div>
        </div>

        {{-- CHAT IA --}}
        <div class="lg:col-span-1 order-3">
            <div class="bg-white rounded-lg border border-gray-200 sticky top-20 flex flex-col" style="max-height: calc(100vh - 6rem)">
                <div class="p-3 bg-ocean-600 text-white rounded-t-lg">
                    <h3 class="text-sm font-medium flex items-center gap-1.5"><span class="material-icons text-sm">smart_toy</span> Assistant IA</h3>
                </div>
                <div id="chat-messages" class="flex-1 overflow-y-auto p-3 space-y-2 text-xs" style="min-height: 200px;">
                    <div class="text-center text-gray-400 py-6">Posez votre question</div>
                </div>
                <div class="p-2 border-t border-gray-200">
                    <form onsubmit="sendMessage(event)" class="flex gap-1.5">
                        <input type="text" id="chat-input" placeholder="Question..."
                            class="flex-1 border border-gray-300 rounded-full px-3 py-1.5 text-xs focus:outline-none focus:border-ocean-500">
                        <button type="submit" class="bg-ocean-600 text-white px-3 py-1.5 rounded-full hover:bg-ocean-700 text-xs">➤</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@auth
<script>
let conversationId = null;

// === COMMENTAIRES ===
async function loadComments() {
    try {
        const res = await fetch('/lecons/{{ $lesson->id }}/comments');
        const comments = await res.json();
        const list = document.getElementById('comments-list');
        if (!comments.length) { list.innerHTML = '<p class="text-gray-400 text-sm text-center py-4">Aucune question. Posez la première !</p>'; return; }
        list.innerHTML = comments.map(c => `
            <div class="border-b border-gray-100 pb-3">
                <div class="flex items-start gap-3">
                    <span class="w-7 h-7 rounded-full bg-eclosion-600 text-white flex items-center justify-center text-xs font-bold shrink-0 mt-0.5">${c.user.name.charAt(0)}</span>
                    <div class="flex-1">
                        <div class="flex items-center gap-2"><span class="text-sm font-medium">${c.user.name}</span><span class="text-xs text-gray-400">${new Date(c.created_at).toLocaleDateString('fr')}</span></div>
                        <p class="text-sm text-gray-700 mt-1">${c.content}</p>
                    </div>
                </div>
                ${(c.replies || []).map(r => `
                <div class="flex items-start gap-3 ml-10 mt-2 p-3 bg-ocean-50 rounded-lg">
                    <span class="material-icons text-ocean-600">smart_toy</span>
                    <div class="flex-1"><span class="text-xs font-medium text-ocean-700">Assistant IA</span><p class="text-sm text-gray-700">${r.content}</p></div>
                </div>`).join('')}
            </div>
        `).join('');
    } catch(e) {}
}
async function postComment(e) {
    e.preventDefault();
    const input = document.getElementById('comment-input');
    const c = input.value.trim(); if(!c) return;
    input.value = ''; input.disabled = true;
    try {
        await fetch('/lecons/{{ $lesson->id }}/comments', { method:'POST', headers:{ 'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json' }, body: JSON.stringify({ content: c }) });
        loadComments();
    } catch(e) {}
    input.disabled = false;
}
loadComments();

// === CHAT IA ===
async function sendMessage(e) {
    e.preventDefault();
    const input = document.getElementById('chat-input');
    const msg = input.value.trim();
    if (!msg) return;
    addMsg('user', msg); input.value = '';
    const typing = addMsg('assistant', '...');
    try {
        const res = await fetch('/helpdesk/chat', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            body: JSON.stringify({ message: msg, lesson_id: {{ $lesson->id }}, conversation_id: conversationId })
        });
        const data = await res.json();
        typing.querySelector('div').textContent = data.reply;
        conversationId = data.conversation_id;
    } catch(err) { typing.querySelector('div').textContent = 'Indisponible.'; }
}
function addMsg(role, text) {
    const c = document.getElementById('chat-messages');
    const d = document.createElement('div');
    d.className = 'flex ' + (role === 'user' ? 'justify-end' : 'justify-start');
    d.innerHTML = '<div class="max-w-[85%] rounded-xl px-3 py-1.5 ' + (role === 'user' ? 'bg-eclosion-600 text-white' : 'bg-gray-100 text-gray-800') + '">' + text + '</div>';
    c.appendChild(d); c.scrollTop = c.scrollHeight; return d;
}
async function markComplete(id) {
    try {
        await fetch('/progress/' + id + '/toggle', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
        });
        location.reload();
    } catch(err) {}
}
</script>
@endauth
@endsection
