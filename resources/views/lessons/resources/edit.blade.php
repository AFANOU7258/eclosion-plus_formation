@extends('layouts.app')
@section('title', 'Éditer ressource - ' . $lesson->title)

@section('content')
<div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    {{-- FIL D'ARIANE --}}
    <nav class="text-sm text-cloud-400 mb-6">
        <a href="{{ url('/') }}" class="hover:text-eclosion-600">Accueil</a> &rsaquo;
        <a href="{{ route('courses.show', $lesson->level->course) }}" class="hover:text-eclosion-600">{{ $lesson->level->course->title }}</a> &rsaquo;
        <a href="{{ route('lessons.show', $lesson) }}" class="hover:text-eclosion-600">{{ $lesson->title }}</a> &rsaquo;
        <span class="text-cloud-700">Éditer ressource</span>
    </nav>

    <div class="bg-white border border-cloud-200 rounded-2xl p-8">
        <h1 class="text-2xl font-bold text-cloud-900 mb-2">Éditer la ressource</h1>
        <p class="text-cloud-600 mb-6">{{ $resource->title }}</p>

        <form action="{{ route('resources.update', [$lesson, $resource]) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')

            {{-- Titre --}}
            <div>
                <label for="title" class="block text-sm font-medium text-cloud-900 mb-2">
                    Titre *
                </label>
                <input type="text" name="title" id="title" required value="{{ old('title', $resource->title) }}"
                       class="w-full border border-cloud-300 rounded-lg px-4 py-2 focus:outline-none focus:border-ocean-500 @error('title') border-red-500 @enderror">
                @error('title')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Description --}}
            <div>
                <label for="description" class="block text-sm font-medium text-cloud-900 mb-2">
                    Description
                </label>
                <textarea name="description" id="description" rows="3"
                          class="w-full border border-cloud-300 rounded-lg px-4 py-2 focus:outline-none focus:border-ocean-500 @error('description') border-red-500 @enderror">{{ old('description', $resource->description) }}</textarea>
                @error('description')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Fichier ou URL --}}
            @if($resource->type === 'link')
                <div>
                    <label for="url" class="block text-sm font-medium text-cloud-900 mb-2">
                        URL *
                    </label>
                    <input type="url" name="url" id="url" value="{{ old('url', $resource->url) }}"
                           placeholder="https://example.com"
                           class="w-full border border-cloud-300 rounded-lg px-4 py-2 focus:outline-none focus:border-ocean-500 @error('url') border-red-500 @enderror">
                    @error('url')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            @else
                <div>
                    <label for="file" class="block text-sm font-medium text-cloud-900 mb-2">
                        Fichier (Laisser vide pour garder le fichier actuel)
                    </label>
                    <div class="border-2 border-dashed border-cloud-300 rounded-lg p-6 text-center hover:border-ocean-500 transition cursor-pointer"
                         onclick="document.getElementById('file').click()">
                        <svg class="w-12 h-12 text-cloud-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v16m8-8H4"/>
                        </svg>
                        <p class="text-cloud-600 text-sm font-medium mb-1" id="file-name">Cliquez pour changer le fichier</p>
                        <p class="text-cloud-400 text-xs">Fichier actuel: {{ basename($resource->file_path) }}</p>
                    </div>
                    <input type="file" name="file" id="file" class="hidden"
                           onchange="updateFileName(this)">
                    @error('file')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            @endif

            {{-- Ordre --}}
            <div>
                <label for="order" class="block text-sm font-medium text-cloud-900 mb-2">
                    Ordre d'affichage
                </label>
                <input type="number" name="order" id="order" min="0" value="{{ old('order', $resource->order) }}"
                       class="w-full border border-cloud-300 rounded-lg px-4 py-2 focus:outline-none focus:border-ocean-500 @error('order') border-red-500 @enderror">
                @error('order')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Boutons --}}
            <div class="flex gap-3 pt-6 border-t border-cloud-200">
                <button type="submit" class="flex-1 bg-ocean-600 text-white font-medium py-2 rounded-lg hover:bg-ocean-700 transition">
                    Enregistrer les modifications
                </button>
                <a href="{{ route('lessons.show', $lesson) }}" class="flex-1 text-center border border-cloud-300 text-cloud-700 font-medium py-2 rounded-lg hover:bg-cloud-50 transition">
                    Annuler
                </a>
                <form method="POST" action="{{ route('resources.destroy', [$lesson, $resource]) }}" class="flex-1" onsubmit="return confirm('Êtes-vous sûr?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full bg-red-100 text-red-700 font-medium py-2 rounded-lg hover:bg-red-200 transition">
                        Supprimer
                    </button>
                </form>
            </div>
        </form>
    </div>
</div>

<script>
function updateFileName(input) {
    if (input.files && input.files[0]) {
        document.getElementById('file-name').textContent = input.files[0].name;
    }
}
</script>
@endsection
