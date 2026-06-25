@extends('layouts.app')
@section('title', 'Formations')

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 py-8">
    <h1 class="font-display text-2xl font-normal text-gray-800 mb-4">Catalogue</h1>

    {{-- RECHERCHE --}}
    <form class="mb-4 flex gap-2">
        <div class="relative flex-1">
            <span class="material-icons absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">search</span>
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Rechercher une formation..."
                class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-full text-sm focus:outline-none focus:border-eclosion-500">
        </div>
        <button type="submit" class="px-5 py-2.5 bg-gray-100 rounded-full text-sm font-medium hover:bg-gray-200">Rechercher</button>
    </form>

    {{-- CATÉGORIES --}}
    <div class="flex flex-wrap gap-2 mb-6">
        <a href="?q={{ request('q') }}" class="px-4 py-1.5 rounded-full text-xs font-medium {{ !request('categorie') ? 'bg-eclosion-600 text-white' : 'bg-white border text-gray-600 hover:bg-gray-50' }}">
            Toutes
        </a>
        @foreach($categories as $cat)
        <a href="?categorie={{ $cat->id }}&q={{ request('q') }}" class="px-4 py-1.5 rounded-full text-xs font-medium {{ request('categorie') == $cat->id ? 'bg-eclosion-600 text-white' : 'bg-white border text-gray-600 hover:bg-gray-50' }}">
            {{ $cat->name }} ({{ $cat->courses_count }})
        </a>
        @endforeach
    </div>

    {{-- RÉSULTATS --}}
    @if($search)
        <p class="text-sm text-gray-500 mb-4">Résultats pour "{{ $search }}" — {{ $courses->total() }} formation(s)</p>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @forelse($courses as $course)
        <a href="{{ route('courses.show', $course) }}" class="course-card bg-white rounded-lg border border-gray-200 overflow-hidden flex flex-col">
            <div class="relative h-28 bg-gradient-to-br from-eclosion-600 to-ocean-600 flex items-end p-3">
                @if($course->category)
                    <span class="text-white text-xs font-medium bg-black/30 px-2 py-0.5 rounded">{{ $course->category->name }}</span>
                @endif
            </div>
            <div class="p-4 flex-1 flex flex-col">
                <h3 class="font-display text-base font-medium text-gray-900 leading-tight mb-1">{{ $course->title }}</h3>
                <div class="flex items-center gap-0.5 mb-2">
                    @for($i=1; $i<=5; $i++)
                        <span class="material-icons text-sm {{ $i <= $course->average_rating ? 'text-yellow-500' : 'text-gray-300' }}">star</span>
                    @endfor
                    <span class="text-xs text-gray-400 ml-1">({{ $course->reviews_count }})</span>
                </div>
                <div class="mt-auto flex items-center justify-between pt-3 border-t border-gray-100">
                    <span class="text-xs text-gray-400">{{ $course->levels_count }} niveaux</span>
                    <span class="text-sm font-semibold text-eclosion-600">{{ number_format($course->price, 0, ',', ' ') }} FCFA</span>
                </div>
            </div>
        </a>
        @empty
        <div class="col-span-full text-center py-16">
            <span class="material-icons text-5xl text-gray-300 mb-3">search_off</span>
            <p class="text-gray-400">Aucune formation trouvée.</p>
            <a href="{{ route('courses.index') }}" class="text-ocean-600 text-sm mt-2 inline-block hover:underline">Voir tout le catalogue</a>
        </div>
        @endforelse
    </div>
    <div class="mt-8">{{ $courses->links() }}</div>
</div>
@endsection
