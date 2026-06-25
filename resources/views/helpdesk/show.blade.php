@extends('layouts.app')
@section('title', $conversation->title ?: 'Conversation')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <a href="{{ route('helpdesk.index') }}" class="text-ocean-600 hover:text-ocean-700 text-sm mb-6 inline-block">← Retour au support</a>

    <div class="bg-white border border-cloud-200 rounded-2xl overflow-hidden">
        <div class="p-4 bg-ocean-600 text-white">
            <h2 class="font-semibold">{{ $conversation->title ?: 'Conversation' }}</h2>
            @if($conversation->lesson)
                <p class="text-xs text-ocean-200 mt-1">{{ $conversation->lesson->title }}</p>
            @endif
        </div>

        <div id="chat-messages" class="p-6 space-y-4" style="min-height: 400px; max-height: 60vh; overflow-y: auto;">
            @foreach($conversation->messages as $msg)
                <div class="flex {{ $msg->role === 'user' ? 'justify-end' : 'justify-start' }}">
                    <div class="max-w-[75%] rounded-2xl px-5 py-3 {{ $msg->role === 'user' ? 'bg-eclosion-600 text-white' : ($msg->role === 'system' ? 'bg-yellow-50 text-yellow-800 text-xs' : 'bg-cloud-100 text-cloud-800') }}">
                        {{ $msg->content }}
                    </div>
                </div>
            @endforeach
        </div>

        <div class="p-4 border-t border-cloud-200">
            <form id="chat-form" onsubmit="sendMessage(event)" class="flex gap-3">
                <input type="text" id="chat-input" placeholder="Votre message..."
                    class="flex-1 border border-cloud-300 rounded-xl px-4 py-3 focus:outline-none focus:border-ocean-500">
                <button type="submit" class="bg-ocean-600 text-white px-5 py-3 rounded-xl hover:bg-ocean-700 transition font-medium">
                    Envoyer
                </button>
            </form>
        </div>
    </div>
</div>

<script>
async function sendMessage(e) {
    e.preventDefault();
    const input = document.getElementById('chat-input');
    const msg = input.value.trim();
    if (!msg) return;

    const container = document.getElementById('chat-messages');
    appendMsg('user', msg);
    input.value = '';

    const typing = appendMsg('assistant', '...');

    try {
        const res = await fetch('/helpdesk/chat', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            body: JSON.stringify({ message: msg, conversation_id: {{ $conversation->id }} })
        });
        const data = await res.json();
        typing.querySelector('div').textContent = data.reply;
    } catch(err) {
        typing.querySelector('div').textContent = 'Erreur. Veuillez réessayer.';
    }
    container.scrollTop = container.scrollHeight;
}

function appendMsg(role, text) {
    const container = document.getElementById('chat-messages');
    const div = document.createElement('div');
    div.className = 'flex ' + (role === 'user' ? 'justify-end' : 'justify-start');
    div.innerHTML = '<div class="max-w-[75%] rounded-2xl px-5 py-3 ' + (role === 'user' ? 'bg-eclosion-600 text-white' : 'bg-cloud-100 text-cloud-800') + '">' + text + '</div>';
    container.appendChild(div);
    container.scrollTop = container.scrollHeight;
    return div;
}
</script>
@endsection
