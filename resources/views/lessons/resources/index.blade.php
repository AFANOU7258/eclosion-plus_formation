@extends('layouts.app')
@section('title', 'Ressources - ' . $lesson->title)

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    {{-- FIL D'ARIANE --}}
    <nav class="text-sm text-cloud-400 mb-6">
        <a href="{{ url('/') }}" class="hover:text-eclosion-600">Accueil</a> &rsaquo;
        <a href="{{ route('courses.show', $lesson->level->course) }}" class="hover:text-eclosion-600">{{ $lesson->level->course->title }}</a> &rsaquo;
        <a href="{{ route('lessons.show', $lesson) }}" class="hover:text-eclosion-600">{{ $lesson->title }}</a> &rsaquo;
        <span class="text-cloud-700">Ressources</span>
    </nav>

    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-cloud-900">Ressources</h1>
            <p class="text-cloud-600">{{ $resources->count() }} ressource(s)</p>
        </div>
        @auth
        @if(Auth::user()->isInstructor() || Auth::user()->isAdmin())
        <a href="{{ route('resources.create', $lesson) }}" class="bg-ocean-600 text-white px-6 py-2 rounded-lg hover:bg-ocean-700 transition font-medium">
            + Ajouter une ressource
        </a>
        @endif
        @endauth
    </div>

    @if($resources->count() > 0)
        <div class="space-y-3">
            @foreach($resources as $resource)
            <div class="bg-white border border-cloud-200 rounded-lg p-4 flex items-start justify-between hover:border-ocean-300 transition group">
                <div class="flex items-start gap-4 flex-1 min-w-0">
                    <span class="text-3xl shrink-0">{{ $resource->getIcon() }}</span>
                    <div class="flex-1 min-w-0">
                        <h3 class="font-semibold text-cloud-900 group-hover:text-ocean-600 transition">
                            {{ $resource->title }}
                        </h3>
                        @if($resource->description)
                        <p class="text-sm text-cloud-500 mt-1">{{ $resource->description }}</p>
                        @endif
                        <p class="text-xs text-cloud-400 mt-2">
                            @if($resource->type === 'link')
                                Lien externe
                            @else
                                {{ ucfirst($resource->type) }}
                            @endif
                        </p>
                    </div>
                </div>

                <div class="flex gap-2 shrink-0 ml-4">
                    <a href="{{ $resource->getDownloadUrl() }}" 
                       target="{{ $resource->type === 'link' ? '_blank' : '' }}"
                       class="text-ocean-600 hover:text-ocean-700 font-medium text-sm transition">
                        {{ $resource->type === 'link' ? 'Voir' : 'Télécharger' }}
                    </a>
                    @auth
                    @if(Auth::user()->isInstructor() || Auth::user()->isAdmin())
                    <a href="{{ route('resources.edit', [$lesson, $resource]) }}" class="text-cloud-600 hover:text-cloud-700 text-sm transition">
                        Éditer
                    </a>
                    @endif
                    @endauth
                </div>
            </div>
            @endforeach
        </div>
    @else
        <div class="bg-cloud-50 border border-cloud-200 rounded-lg p-12 text-center">
            <svg class="w-16 h-16 text-cloud-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
            </svg>
            <p class="text-cloud-600 font-medium">Aucune ressource pour cette leçon</p>
            @auth
            @if(Auth::user()->isInstructor() || Auth::user()->isAdmin())
            <a href="{{ route('resources.create', $lesson) }}" class="text-ocean-600 hover:text-ocean-700 font-medium mt-3 inline-block">
                Ajouter la première ressource
            </a>
            @endif
            @endauth
        </div>
    @endif

    <div class="mt-8">
        <a href="{{ route('lessons.show', $lesson) }}" class="text-ocean-600 hover:text-ocean-700 font-medium transition">
            ← Retour à la leçon
        </a>
    </div>
</div>
@endsection
