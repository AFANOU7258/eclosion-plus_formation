@extends('layouts.admin')
@section('title', 'Nouvelle formation')

@section('content')
<div class="max-w-5xl mx-auto">
    <h1 class="font-display text-2xl font-normal text-gray-800 mb-6">Créer une formation</h1>

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg px-5 py-3 mb-6 text-sm">
            @foreach($errors->all() as $error) <p>{{ $error }}</p> @endforeach
        </div>
    @endif

    <form action="{{ route('admin.courses.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="bg-white rounded-lg border border-gray-200 p-6 mb-6 space-y-5">
            <h2 class="font-medium text-gray-800">Informations</h2>
            <div class="grid md:grid-cols-2 gap-5">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Titre *</label>
                    <input type="text" name="title" value="{{ old('title') }}" required class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:border-eclosion-500">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Description *</label>
                    <textarea name="description" rows="4" required class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:border-eclosion-500">{{ old('description') }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Prix (€)</label>
                    <input type="number" name="price" step="0.01" value="{{ old('price', 0) }}" class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:border-eclosion-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Statut</label>
                    <select name="status" class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:border-eclosion-500">
                        <option value="draft">Brouillon</option>
                        <option value="published">Publié</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Image de couverture</label>
                    <input type="file" name="thumbnail" accept="image/*" class="w-full border border-gray-300 rounded-lg px-4 py-3 focus:outline-none text-sm">
                    <p class="text-xs text-gray-400 mt-1">JPG, PNG, WebP — max 5 Mo</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg border border-gray-200 p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-medium text-gray-800">Niveaux & Leçons</h2>
                <button type="button" onclick="addLevel()" class="text-eclosion-600 border border-eclosion-300 px-4 py-1.5 rounded-full text-sm font-medium hover:bg-eclosion-50 transition">+ Niveau</button>
            </div>
            <div id="levels-container" class="space-y-4"></div>
            <p id="no-levels" class="text-gray-400 text-sm text-center py-8">Aucun niveau. Cliquez sur "+ Niveau".</p>
        </div>

        <div class="flex items-center gap-4">
            <button type="submit" class="bg-eclosion-600 text-white px-8 py-3 rounded-full font-medium hover:bg-eclosion-700 transition">Enregistrer la formation</button>
            <a href="{{ route('admin.courses.index') }}" class="text-gray-500 hover:text-gray-700 text-sm">Annuler</a>
        </div>
    </form>
</div>

<script>
let levelIndex = 0;
let lessonCounters = {};

function addLevel() {
    document.getElementById('no-levels')?.remove();
    const idx = levelIndex++;
    document.getElementById('levels-container').insertAdjacentHTML('beforeend', `
    <div class="border border-gray-200 rounded-lg p-5 bg-gray-50" id="level-${idx}">
        <div class="flex items-center justify-between mb-3">
            <input type="text" name="levels[${idx}][title]" placeholder="Titre du niveau (ex: Niveau 1 — Débutant)"
                class="font-medium text-gray-800 bg-transparent border-b-2 border-transparent focus:border-eclosion-500 focus:outline-none flex-1 px-2 py-1">
            <button type="button" onclick="document.getElementById('level-${idx}').remove()" class="text-red-400 hover:text-red-600 text-xs">✕</button>
        </div>
        <textarea name="levels[${idx}][description]" rows="2" placeholder="Description du niveau..."
            class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-eclosion-300 mb-3"></textarea>
        <div class="lessons-list-${idx} space-y-2 mb-3"></div>
        <button type="button" onclick="addLesson(${idx})" class="text-ocean-600 text-xs font-medium hover:underline">+ Ajouter une leçon</button>
    </div>`);
}

function addLesson(levelIdx) {
    if (!lessonCounters[levelIdx]) lessonCounters[levelIdx] = 0;
    const li = lessonCounters[levelIdx]++;
    const list = document.querySelector('.lessons-list-' + levelIdx);
    if (!list) return;
    list.insertAdjacentHTML('beforeend', `
    <div class="flex flex-wrap items-start gap-3 p-3 bg-white rounded-lg border border-gray-100">
        <div class="flex-1 min-w-[200px]">
            <input type="text" name="levels[${levelIdx}][lessons][${li}][title]" placeholder="Titre de la leçon"
                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:border-eclosion-300">
        </div>
        <select name="levels[${levelIdx}][lessons][${li}][media_type]" class="border border-gray-200 rounded-lg px-2 py-2 text-xs">
            <option value="video">🎬 Vidéo</option><option value="audio">🎧 Audio</option><option value="pdf">📄 PDF</option>
        </select>
        <input type="file" name="levels[${levelIdx}][lessons][${li}][media_file]" accept="video/*,audio/*,.pdf" class="text-xs">
        <input type="number" name="levels[${levelIdx}][lessons][${li}][duration_minutes]" placeholder="Min" class="w-20 border border-gray-200 rounded-lg px-2 py-2 text-xs">
        <div class="w-full">
            <textarea name="levels[${levelIdx}][lessons][${li}][content]" rows="2" placeholder="Contenu de la leçon..."
                class="w-full border border-gray-200 rounded-lg px-3 py-2 text-xs focus:outline-none focus:border-eclosion-300"></textarea>
        </div>
        <div class="w-full">
            <label class="text-xs text-gray-500">Illustrations</label>
            <input type="file" name="levels[${levelIdx}][lessons][${li}][illustrations][]" accept="image/*" multiple class="text-xs">
        </div>
        <button type="button" onclick="this.closest('.flex').remove()" class="text-red-400 hover:text-red-600 text-xs mt-1">✕</button>
    </div>`);
}
</script>
@endsection
