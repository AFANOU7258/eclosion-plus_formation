@extends('layouts.app')
@section('title', 'Mes cours')

@php
    $active = $enrollments->where('status', 'approuvé')->count();
    $pending = $enrollments->where('status', 'en_attente')->count();
@endphp

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 py-8">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="font-display text-2xl font-normal text-gray-800">Mes cours</h1>
            <p class="text-sm text-gray-500 mt-1">{{ $active }} formation(s) active(s){{ $pending > 0 ? ' · ' . $pending . ' en attente' : '' }}</p>
        </div>
        <a href="{{ route('courses.index') }}" class="text-ocean-600 text-sm font-medium hover:bg-ocean-50 px-4 py-2 rounded-full transition flex items-center gap-1">
            <span class="material-icons text-sm">add</span> Catalogue
        </a>
    </div>

    @if($enrollments->isEmpty())
    <div class="text-center py-20">
        <span class="material-icons text-6xl text-gray-300 mb-4">school</span>
        <p class="text-gray-500 text-lg mb-2">Vous n'avez pas encore de cours.</p>
        <p class="text-gray-400 text-sm mb-6">Découvrez nos formations et commencez votre apprentissage.</p>
        <a href="{{ route('courses.index') }}" class="inline-flex items-center gap-2 bg-eclosion-600 text-white px-6 py-3 rounded-full font-medium text-sm hover:bg-eclosion-700 shadow-sm transition">
            <span class="material-icons text-sm">explore</span> Explorer le catalogue
        </a>
    </div>
    @else
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach($enrollments as $enrollment)
        @php $course = $enrollment->course; @endphp
        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden flex flex-col {{ !$enrollment->isApproved() ? 'opacity-80' : '' }} hover:shadow-md transition">
            {{-- Bannière avec statut --}}
            <div class="relative h-28 bg-gradient-to-br from-eclosion-600 to-ocean-600 flex items-start justify-between p-3">
                <span class="text-white text-xs font-medium bg-white/20 px-2.5 py-1 rounded-full">{{ $course->levels_count }} niveaux</span>
                @if($enrollment->isPending())
                    <span class="bg-yellow-100 text-yellow-800 text-xs px-2.5 py-1 rounded-full font-medium flex items-center gap-1">
                        <span class="material-icons text-sm">hourglass_empty</span> En attente
                    </span>
                @elseif($enrollment->isApproved())
                    <span class="bg-green-100 text-green-800 text-xs px-2.5 py-1 rounded-full font-medium flex items-center gap-1">
                        <span class="material-icons text-sm">check_circle</span> Actif
                    </span>
                @else
                    <span class="bg-red-100 text-red-800 text-xs px-2.5 py-1 rounded-full font-medium flex items-center gap-1">
                        <span class="material-icons text-sm">cancel</span> Refusé
                    </span>
                @endif
            </div>

            {{-- Contenu --}}
            <div class="p-4 flex-1 flex flex-col">
                <h3 class="font-display text-base font-medium text-gray-900 leading-tight mb-1">{{ $course->title }}</h3>
                <p class="text-xs text-gray-500 mb-3">{{ $course->total_lessons_count }} leçons</p>

                @if($enrollment->isApproved())
                    @php $progress = $course->progressFor(Auth::user()); @endphp
                    {{-- Barre de progression --}}
                    <div class="mb-3">
                        <div class="flex justify-between text-xs text-gray-500 mb-1">
                            <span>Progression</span>
                            <span class="font-medium">{{ $progress }}%</span>
                        </div>
                        <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                            <div class="h-full bg-eclosion-600 rounded-full transition-all duration-500" style="width: {{ $progress }}%"></div>
                        </div>
                    </div>

                    {{-- Dernière leçon --}}
                    @php
                        $lastLesson = null;
                        $allLessons = $course->lessons;
                        foreach ($allLessons as $l) {
                            if (!$l->isCompletedBy(Auth::user())) { $lastLesson = $l; break; }
                        }
                        $lastLesson = $lastLesson ?: $allLessons->first();
                    @endphp

                    <div class="mt-auto space-y-2">
                        @if($progress == 100)
                            <a href="{{ route('courses.complete', $course) }}" class="block w-full text-center bg-yellow-500 text-white font-medium py-2.5 rounded-full hover:bg-yellow-600 transition text-sm">
                                <span class="material-icons text-sm align-middle mr-1">emoji_events</span> Voir mon certificat
                            </a>
                        @elseif($progress > 0)
                            <p class="text-xs text-gray-400 text-center">Reprendre à : {{ $lastLesson ? $lastLesson->title : '...' }}</p>
                        @endif

                        <a href="{{ route('lessons.show', $lastLesson ?? $allLessons->first() ?? 1) }}"
                           class="block w-full text-center bg-eclosion-600 text-white font-medium py-2.5 rounded-full hover:bg-eclosion-700 transition text-sm">
                            {{ $progress > 0 ? 'Continuer' : 'Commencer' }}
                            <span class="material-icons text-sm align-middle ml-1">arrow_forward</span>
                        </a>
                    </div>
                @elseif($enrollment->isPending())
                    <div class="mt-auto bg-yellow-50 border border-yellow-200 rounded-lg p-3 text-center">
                        <p class="text-xs text-yellow-800 flex items-center justify-center gap-1">
                            <span class="material-icons text-sm">schedule</span>
                            En attente de validation
                        </p>
                        <p class="text-xs text-yellow-600 mt-1">L'admin vérifie votre paiement</p>
                    </div>
                @else
                    <div class="mt-auto bg-red-50 border border-red-200 rounded-lg p-3 text-center">
                        <p class="text-xs text-red-700">Demande refusée</p>
                    </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    <div class="mt-8">{{ $enrollments->links() }}</div>
    @endif
</div>
@endsection
