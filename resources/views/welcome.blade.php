@extends('layouts.app')
@section('title', 'Eclosion+')

@section('content')
{{-- HERO à la Classroom --}}
<div class="bg-white border-b border-gray-200">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 py-12 md:py-20">
        <h1 class="font-display text-3xl md:text-4xl font-normal text-gray-800 mb-3">Bienvenue sur Eclosion+</h1>
        <p class="text-lg text-gray-500 mb-8">Apprenez à votre rythme avec des formations structurées et un assistant IA à vos côtés.</p>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('courses.index') }}" class="inline-flex items-center gap-2 bg-eclosion-600 text-white px-6 py-3 rounded-full font-medium text-sm hover:bg-eclosion-700 transition shadow-sm">
                <span class="material-icons">school</span> Explorer les formations
            </a>
        </div>
    </div>
</div>

{{-- GRILLE DE COURS à la Classroom --}}
<div class="max-w-5xl mx-auto px-4 sm:px-6 py-10">
    <h2 class="font-display text-xl font-normal text-gray-800 mb-6">Formations disponibles</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach(\App\Models\Course::published()->latest()->take(6)->get() as $course)
        <a href="{{ route('courses.show', $course) }}" class="course-card bg-white rounded-lg border border-gray-200 overflow-hidden flex flex-col">
            <div class="relative h-28 bg-gradient-to-br from-eclosion-600 to-ocean-600 flex items-end p-3">
            </div>
            <div class="p-4 flex-1 flex flex-col">
                <h3 class="font-display text-base font-medium text-gray-900 leading-tight mb-1">{{ $course->title }}</h3>
                <p class="text-xs text-gray-500 mb-3">{{ $course->instructor->name ?? 'Formateur' }}</p>
                <div class="mt-auto flex items-center justify-between">
                    <span class="text-xs text-gray-400">{{ $course->levels_count }} niveaux</span>
                    <span class="text-sm font-semibold text-eclosion-600">{{ number_format($course->price, 0, ',', ' ') }} FCFA</span>
                </div>
            </div>
        </a>
        @endforeach
    </div>
    @if(\App\Models\Course::published()->count() > 6)
    <div class="text-center mt-8">
        <a href="{{ route('courses.index') }}" class="inline-flex items-center gap-1.5 text-ocean-600 font-medium text-sm hover:bg-ocean-50 px-4 py-2 rounded-full transition">
            Voir toutes les formations <span class="material-icons text-sm">arrow_forward</span>
        </a>
    </div>
    @endif
</div>

{{-- COMMENT ÇA MARCHE --}}
<div class="bg-white border-t border-gray-200 py-12">
    <div class="max-w-5xl mx-auto px-4 sm:px-6">
        <h2 class="font-display text-xl font-normal text-gray-800 text-center mb-8">Comment ça marche</h2>
        <div class="grid sm:grid-cols-3 gap-8 text-center">
            <div>
                <span class="material-icons large text-eclosion-600 mb-3">search</span>
                <h3 class="font-medium text-gray-800 mb-1">Choisissez</h3>
                <p class="text-sm text-gray-500">Parcourez le catalogue et trouvez la formation idéale.</p>
            </div>
            <div>
                <span class="material-icons large text-ocean-600 mb-3">how_to_reg</span>
                <h3 class="font-medium text-gray-800 mb-1">Inscrivez-vous</h3>
                <p class="text-sm text-gray-500">Demandez l'accès, il sera validé rapidement.</p>
            </div>
            <div>
                <span class="material-icons large text-eclosion-600 mb-3">auto_awesome</span>
                <h3 class="font-medium text-gray-800 mb-1">Apprenez</h3>
                <p class="text-sm text-gray-500">Vidéos, audio, PDF. L'IA vous assiste à chaque étape.</p>
            </div>
        </div>
    </div>
</div>
@endsection
