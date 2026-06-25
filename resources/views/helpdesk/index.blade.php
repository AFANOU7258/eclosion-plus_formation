@extends('layouts.app')
@section('title', 'Support IA')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
    <h1 class="text-3xl font-bold text-cloud-900 mb-2">🤖 Support IA</h1>
    <p class="text-cloud-500 mb-8">Posez vos questions sur vos formations, l'assistant connaît le contexte de chaque leçon.</p>

    <a href="{{ url()->previous() }}" class="inline-block bg-ocean-600 text-white font-semibold px-6 py-3 rounded-xl hover:bg-ocean-700 transition mb-10">
        + Nouvelle conversation
    </a>

    <div class="space-y-4">
        @forelse($conversations as $conv)
        <a href="{{ route('helpdesk.show', $conv) }}" class="block bg-white border border-cloud-200 rounded-xl p-5 hover:border-ocean-300 hover:shadow transition">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="font-semibold text-cloud-900">{{ $conv->title ?: 'Conversation sans titre' }}</h3>
                    <p class="text-xs text-cloud-400 mt-1">
                        {{ $conv->created_at->diffForHumans() }}
                        @if($conv->lesson) · {{ $conv->lesson->title }} @endif
                    </p>
                </div>
                <span class="text-cloud-300">→</span>
            </div>
        </a>
        @empty
        <div class="text-center py-16 text-cloud-400">
            <p>Aucune conversation. Commencez par poser une question sur une leçon !</p>
        </div>
        @endforelse
    </div>

    {{ $conversations->links() }}
</div>
@endsection
