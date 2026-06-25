@extends('layouts.admin')
@section('title', 'Éditer: ' . $course->title)

@section('content')
<div class="max-w-6xl mx-auto px-4 py-6">
    {{-- En-tête --}}
    <div class="mb-8 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-cloud-900 mb-2">Éditer la formation</h1>
            <p class="text-cloud-600">{{ $course->title }}</p>
        </div>
        <a href="{{ route('courses.show', $course) }}" class="text-ocean-600 hover:text-ocean-700 font-medium" target="_blank">
            👁️ Voir la formation
        </a>
    </div>

    @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-800 rounded-xl px-6 py-4 mb-6">
            <h3 class="font-semibold mb-2">Erreurs détectées:</h3>
            <ul class="space-y-1 text-sm">
                @foreach($errors->all() as $error)
                <li>• {{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.courses.update', $course) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')

        <!-- SECTION 1: INFOS FORMATION -->
        <div class="bg-white rounded-2xl border border-cloud-200 overflow-hidden">
            <div class="bg-gradient-to-r from-eclosion-600 to-ocean-600 px-8 py-6">
                <h2 class="text-white font-bold text-xl flex items-center gap-3">
                    <span class="text-2xl">📚</span> Informations de la formation
                </h2>
            </div>
            <div class="p-8 space-y-6">
                <!-- Titre -->
                <div>
                    <label class="block text-sm font-bold text-cloud-900 mb-2">Titre de la formation *</label>
                    <input type="text" name="title" value="{{ old('title', $course->title) }}" required
                        class="w-full border-2 border-cloud-300 rounded-xl px-4 py-3 focus:outline-none focus:border-ocean-500 focus:ring-2 focus:ring-ocean-100 transition">
                    @error('title')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-sm font-bold text-cloud-900 mb-2">Description *</label>
                    <textarea name="description" rows="5" required
                        class="w-full border-2 border-cloud-300 rounded-xl px-4 py-3 focus:outline-none focus:border-ocean-500 focus:ring-2 focus:ring-ocean-100 transition">{{ old('description', $course->description) }}</textarea>
                    @error('description')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Grille: Prix, Statut -->
                <div class="grid md:grid-cols-2 gap-6">
                    <!-- Prix -->
                    <div>
                        <label class="block text-sm font-bold text-cloud-900 mb-2">Prix (€)</label>
                        <input type="number" name="price" step="0.01" value="{{ old('price', $course->price) }}" min="0"
                            class="w-full border-2 border-cloud-300 rounded-xl px-4 py-3 focus:outline-none focus:border-ocean-500 focus:ring-2 focus:ring-ocean-100 transition">
                        @error('price')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Statut -->
                    <div>
                        <label class="block text-sm font-bold text-cloud-900 mb-2">Statut</label>
                        <select name="status" class="w-full border-2 border-cloud-300 rounded-xl px-4 py-3 focus:outline-none focus:border-ocean-500 focus:ring-2 focus:ring-ocean-100 transition">
                            <option value="draft" {{ old('status', $course->status) === 'draft' ? 'selected' : '' }}>🔒 Brouillon</option>
                            <option value="published" {{ old('status', $course->status) === 'published' ? 'selected' : '' }}>✅ Publiée</option>
                        </select>
                        @error('status')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Thumbnail -->
                <div>
                    <label class="block text-sm font-bold text-cloud-900 mb-3">Image de couverture</label>
                    <div class="flex gap-6">
                        <!-- Prévisualisation actuelle -->
                        @if($course->thumbnail)
                        <div class="flex-shrink-0">
                            <p class="text-xs text-cloud-600 mb-2">Image actuelle:</p>
                            <img src="{{ asset('storage/' . $course->thumbnail) }}" alt="Thumbnail" class="w-32 h-32 object-cover rounded-lg border border-cloud-200">
                        </div>
                        @endif

                        <!-- Upload nouvelle -->
                        <div class="flex-1">
                            <input type="file" name="thumbnail" id="thumbnail-input" accept="image/*"
                                class="hidden" onchange="previewThumbnail(this)">
                            
                            <label for="thumbnail-input" class="block cursor-pointer">
                                <div id="thumbnail-preview" class="border-2 border-dashed border-cloud-300 rounded-xl p-8 text-center hover:border-ocean-500 transition bg-gradient-to-br from-cloud-50 to-white h-32 flex items-center justify-center">
                                    <div>
                                        <svg class="w-12 h-12 text-cloud-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        <p class="text-sm text-cloud-700">Cliquez pour changer</p>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>
                    @error('thumbnail')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- SECTION 2: NIVEAUX ET LEÇONS -->
        <div class="bg-white rounded-2xl border border-cloud-200 overflow-hidden">
            <div class="bg-gradient-to-r from-ocean-600 to-eclosion-600 px-8 py-6">
                <h2 class="text-white font-bold text-xl flex items-center gap-3">
                    <span class="text-2xl">📖</span> Niveaux et Leçons
                </h2>
            </div>
            <div class="p-8">
                <div id="levels-container" class="space-y-6">
                    @foreach($course->levels as $levelIndex => $level)
                    <div class="level-block bg-cloud-50 border-2 border-cloud-200 rounded-xl p-6">
                        <div class="flex items-center justify-between mb-6">
                            <h3 class="font-bold text-cloud-900 text-lg flex items-center gap-2">
                                <span class="w-8 h-8 bg-ocean-600 text-white rounded-full flex items-center justify-center text-sm font-bold level-number">{{ $levelIndex + 1 }}</span>
                                Niveau
                            </h3>
                            <button type="button" class="text-red-600 hover:text-red-700 font-medium text-sm" onclick="removeLevel(this)">
                                ✕ Supprimer
                            </button>
                        </div>

                        <div class="space-y-4 mb-6">
                            <input type="text" name="levels[{{ $levelIndex }}][title]" value="{{ $level->title }}" placeholder="Titre du niveau" required
                                class="w-full border-2 border-cloud-300 rounded-lg px-4 py-2 focus:outline-none focus:border-ocean-500">
                            <textarea name="levels[{{ $levelIndex }}][description]" rows="2" placeholder="Description du niveau (optionnel)"
                                class="w-full border-2 border-cloud-300 rounded-lg px-4 py-2 focus:outline-none focus:border-ocean-500">{{ $level->description }}</textarea>
                        </div>

                        <h4 class="font-semibold text-cloud-900 mb-4 text-sm">Leçons</h4>
                        <div class="lessons-container space-y-4" data-level="{{ $levelIndex }}">
                            @foreach($level->lessons as $lessonIndex => $lesson)
                            <div class="lesson-block bg-white border-2 border-cloud-300 rounded-lg p-4">
                                <div class="grid md:grid-cols-2 gap-4 mb-4">
                                    <input type="text" name="levels[{{ $levelIndex }}][lessons][{{ $lessonIndex }}][title]" value="{{ $lesson->title }}" placeholder="Titre de la leçon" required
                                        class="border border-cloud-300 rounded-lg px-3 py-2 focus:outline-none focus:border-ocean-500 text-sm">
                                    <select name="levels[{{ $levelIndex }}][lessons][{{ $lessonIndex }}][media_type]" class="border border-cloud-300 rounded-lg px-3 py-2 focus:outline-none focus:border-ocean-500 text-sm media-type-select" onchange="updateMediaTypeUI(this)">
                                        <option value="video" {{ $lesson->media_type === 'video' ? 'selected' : '' }}>🎬 Vidéo</option>
                                        <option value="audio" {{ $lesson->media_type === 'audio' ? 'selected' : '' }}>🎧 Audio</option>
                                        <option value="pdf" {{ $lesson->media_type === 'pdf' ? 'selected' : '' }}>📄 PDF</option>
                                    </select>
                                </div>

                                <textarea name="levels[{{ $levelIndex }}][lessons][{{ $lessonIndex }}][content]" placeholder="Description / Contenu" rows="2"
                                    class="w-full border border-cloud-300 rounded-lg px-3 py-2 focus:outline-none focus:border-ocean-500 text-sm mb-4">{{ $lesson->content }}</textarea>

                                <div class="grid md:grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <label class="text-xs font-semibold text-cloud-700 mb-1 block">Fichier média</label>
                                        @if($lesson->media_path)
                                        <p class="text-xs text-cloud-600 mb-1">Fichier actuel: {{ basename($lesson->media_path) }}</p>
                                        @endif
                                        <input type="file" name="levels[{{ $levelIndex }}][lessons][{{ $lessonIndex }}][media_file]" 
                                            class="border border-cloud-300 rounded-lg px-3 py-2 text-sm w-full" accept="video/*,audio/*,.pdf">
                                        @if($lesson->media_path)
                                        <input type="hidden" name="levels[{{ $levelIndex }}][lessons][{{ $lessonIndex }}][existing_media_path]" value="{{ $lesson->media_path }}">
                                        @endif
                                    </div>
                                    <div>
                                        <label class="text-xs font-semibold text-cloud-700 mb-1 block">Durée (minutes)</label>
                                        <input type="number" name="levels[{{ $levelIndex }}][lessons][{{ $lessonIndex }}][duration_minutes]" value="{{ $lesson->duration_minutes }}" min="0" step="0.5"
                                            placeholder="15"
                                            class="border border-cloud-300 rounded-lg px-3 py-2 focus:outline-none focus:border-ocean-500 text-sm w-full">
                                    </div>
                                </div>

                                <button type="button" class="text-red-600 hover:text-red-700 text-sm font-medium" onclick="removeLesson(this)">
                                    ✕ Supprimer cette leçon
                                </button>
                            </div>
                            @endforeach
                        </div>

                        <button type="button" class="mt-4 text-ocean-600 hover:text-ocean-700 font-medium text-sm" onclick="addLesson(this)">
                            + Ajouter une leçon
                        </button>
                    </div>
                    @endforeach
                </div>

                <button type="button" class="mt-6 px-6 py-3 bg-ocean-100 text-ocean-700 rounded-lg font-medium hover:bg-ocean-200 transition" onclick="addLevel()">
                    + Ajouter un niveau
                </button>
            </div>
        </div>

        <!-- BOUTONS D'ACTION -->
        <div class="flex gap-4 pb-8">
            <button type="submit" class="flex-1 bg-gradient-to-r from-ocean-600 to-eclosion-600 text-white font-bold py-3 px-6 rounded-xl hover:shadow-lg transition">
                ✅ Enregistrer les modifications
            </button>
            <a href="{{ route('admin.courses.index') }}" class="flex-1 text-center border-2 border-cloud-300 text-cloud-700 font-bold py-3 px-6 rounded-xl hover:bg-cloud-50 transition">
                Annuler
            </a>
            <form method="POST" action="{{ route('admin.courses.destroy', $course) }}" class="flex-1" onsubmit="return confirm('Êtes-vous sûr?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="w-full bg-red-100 text-red-700 font-bold py-3 px-6 rounded-xl hover:bg-red-200 transition">
                    🗑️ Supprimer
                </button>
            </form>
        </div>
    </form>
</div>

<script>
let levelCount = {{ count($course->levels) }};

function addLevel() {
    const container = document.getElementById('levels-container');
    const newLevel = document.createElement('div');
    newLevel.className = 'level-block bg-cloud-50 border-2 border-cloud-200 rounded-xl p-6';
    newLevel.innerHTML = `
        <div class="flex items-center justify-between mb-6">
            <h3 class="font-bold text-cloud-900 text-lg flex items-center gap-2">
                <span class="w-8 h-8 bg-ocean-600 text-white rounded-full flex items-center justify-center text-sm font-bold level-number">${levelCount + 1}</span>
                Niveau
            </h3>
            <button type="button" class="text-red-600 hover:text-red-700 font-medium text-sm" onclick="removeLevel(this)">
                ✕ Supprimer
            </button>
        </div>
        <div class="space-y-4 mb-6">
            <input type="text" name="levels[${levelCount}][title]" placeholder="Titre du niveau" required
                class="w-full border-2 border-cloud-300 rounded-lg px-4 py-2 focus:outline-none focus:border-ocean-500">
            <textarea name="levels[${levelCount}][description]" rows="2" placeholder="Description du niveau (optionnel)"
                class="w-full border-2 border-cloud-300 rounded-lg px-4 py-2 focus:outline-none focus:border-ocean-500"></textarea>
        </div>
        <h4 class="font-semibold text-cloud-900 mb-4 text-sm">Leçons</h4>
        <div class="lessons-container space-y-4" data-level="${levelCount}">
            <div class="lesson-block bg-white border-2 border-cloud-300 rounded-lg p-4">
                <div class="grid md:grid-cols-2 gap-4 mb-4">
                    <input type="text" name="levels[${levelCount}][lessons][0][title]" placeholder="Titre de la leçon" required
                        class="border border-cloud-300 rounded-lg px-3 py-2 focus:outline-none focus:border-ocean-500 text-sm">
                    <select name="levels[${levelCount}][lessons][0][media_type]" class="border border-cloud-300 rounded-lg px-3 py-2 focus:outline-none focus:border-ocean-500 text-sm media-type-select" onchange="updateMediaTypeUI(this)">
                        <option value="video">🎬 Vidéo</option>
                        <option value="audio">🎧 Audio</option>
                        <option value="pdf">📄 PDF</option>
                    </select>
                </div>
                <textarea name="levels[${levelCount}][lessons][0][content]" placeholder="Description / Contenu" rows="2"
                    class="w-full border border-cloud-300 rounded-lg px-3 py-2 focus:outline-none focus:border-ocean-500 text-sm mb-4"></textarea>
                <div class="grid md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="text-xs font-semibold text-cloud-700 mb-1 block">Fichier média</label>
                        <input type="file" name="levels[${levelCount}][lessons][0][media_file]" 
                            class="border border-cloud-300 rounded-lg px-3 py-2 text-sm w-full" accept="video/*,audio/*,.pdf">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-cloud-700 mb-1 block">Durée (minutes)</label>
                        <input type="number" name="levels[${levelCount}][lessons][0][duration_minutes]" min="0" step="0.5"
                            placeholder="15"
                            class="border border-cloud-300 rounded-lg px-3 py-2 focus:outline-none focus:border-ocean-500 text-sm w-full">
                    </div>
                </div>
                <button type="button" class="text-red-600 hover:text-red-700 text-sm font-medium" onclick="removeLesson(this)">
                    ✕ Supprimer cette leçon
                </button>
            </div>
        </div>
        <button type="button" class="mt-4 text-ocean-600 hover:text-ocean-700 font-medium text-sm" onclick="addLesson(this)">
            + Ajouter une leçon
        </button>
    `;
    container.appendChild(newLevel);
    levelCount++;
    updateLevelNumbers();
}

function removeLevel(btn) {
    if (document.querySelectorAll('.level-block').length > 1) {
        btn.closest('.level-block').remove();
        updateLevelNumbers();
    } else {
        alert('Vous devez garder au moins un niveau');
    }
}

function addLesson(btn) {
    const levelBlock = btn.closest('.level-block');
    const levelIndex = Array.from(levelBlock.closest('#levels-container').children).indexOf(levelBlock);
    const lessonsContainer = levelBlock.querySelector('.lessons-container');
    const lessonCount = lessonsContainer.querySelectorAll('.lesson-block').length;

    const newLesson = document.createElement('div');
    newLesson.className = 'lesson-block bg-white border-2 border-cloud-300 rounded-lg p-4';
    newLesson.innerHTML = `
        <div class="grid md:grid-cols-2 gap-4 mb-4">
            <input type="text" name="levels[${levelIndex}][lessons][${lessonCount}][title]" placeholder="Titre de la leçon" required
                class="border border-cloud-300 rounded-lg px-3 py-2 focus:outline-none focus:border-ocean-500 text-sm">
            <select name="levels[${levelIndex}][lessons][${lessonCount}][media_type]" class="border border-cloud-300 rounded-lg px-3 py-2 focus:outline-none focus:border-ocean-500 text-sm media-type-select" onchange="updateMediaTypeUI(this)">
                <option value="video">🎬 Vidéo</option>
                <option value="audio">🎧 Audio</option>
                <option value="pdf">📄 PDF</option>
            </select>
        </div>
        <textarea name="levels[${levelIndex}][lessons][${lessonCount}][content]" placeholder="Description / Contenu" rows="2"
            class="w-full border border-cloud-300 rounded-lg px-3 py-2 focus:outline-none focus:border-ocean-500 text-sm mb-4"></textarea>
        <div class="grid md:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="text-xs font-semibold text-cloud-700 mb-1 block">Fichier média</label>
                <input type="file" name="levels[${levelIndex}][lessons][${lessonCount}][media_file]" 
                    class="border border-cloud-300 rounded-lg px-3 py-2 text-sm w-full" accept="video/*,audio/*,.pdf">
            </div>
            <div>
                <label class="text-xs font-semibold text-cloud-700 mb-1 block">Durée (minutes)</label>
                <input type="number" name="levels[${levelIndex}][lessons][${lessonCount}][duration_minutes]" min="0" step="0.5"
                    placeholder="15"
                    class="border border-cloud-300 rounded-lg px-3 py-2 focus:outline-none focus:border-ocean-500 text-sm w-full">
            </div>
        </div>
        <button type="button" class="text-red-600 hover:text-red-700 text-sm font-medium" onclick="removeLesson(this)">
            ✕ Supprimer cette leçon
        </button>
    `;
    lessonsContainer.appendChild(newLesson);
}

function removeLesson(btn) {
    const lessonsContainer = btn.closest('.lessons-container');
    if (lessonsContainer.querySelectorAll('.lesson-block').length > 1) {
        btn.closest('.lesson-block').remove();
    } else {
        alert('Vous devez garder au moins une leçon par niveau');
    }
}

function updateLevelNumbers() {
    document.querySelectorAll('.level-number').forEach((el, i) => {
        el.textContent = i + 1;
    });
}

function updateMediaTypeUI(select) {
    const mediaType = select.value;
    const fileInput = select.closest('.lesson-block').querySelector('input[name*="media_file"]');
    if (mediaType === 'audio') {
        fileInput.accept = 'audio/*';
    } else if (mediaType === 'pdf') {
        fileInput.accept = '.pdf';
    } else {
        fileInput.accept = 'video/*';
    }
}

function previewThumbnail(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('thumbnail-preview');
            preview.innerHTML = `
                <img src="${e.target.result}" alt="Preview" class="w-full h-32 object-cover rounded-lg">
            `;
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endsection
