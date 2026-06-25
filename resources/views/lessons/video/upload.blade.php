@extends('layouts.app')
@section('title', 'Uploader vidéo - ' . $lesson->title)

@section('content')
<div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    {{-- FIL D'ARIANE --}}
    <nav class="text-sm text-cloud-400 mb-6">
        <a href="{{ url('/') }}" class="hover:text-eclosion-600">Accueil</a> &rsaquo;
        <a href="{{ route('courses.show', $lesson->level->course) }}" class="hover:text-eclosion-600">{{ $lesson->level->course->title }}</a> &rsaquo;
        <a href="{{ route('lessons.show', $lesson) }}" class="hover:text-eclosion-600">{{ $lesson->title }}</a> &rsaquo;
        <span class="text-cloud-700">Uploader vidéo</span>
    </nav>

    <div class="bg-white border border-cloud-200 rounded-2xl p-8">
        <h1 class="text-2xl font-bold text-cloud-900 mb-2">Uploader une vidéo</h1>
        <p class="text-cloud-600 mb-8">{{ $lesson->title }}</p>

        {{-- Zone de drop --}}
        <div id="drop-zone" class="border-2 border-dashed border-cloud-300 rounded-2xl p-12 text-center transition hover:border-ocean-500 cursor-pointer mb-8 bg-gradient-to-br from-cloud-50 to-white"
             ondrop="handleDrop(event)"
             ondragover="handleDragOver(event)"
             ondragleave="handleDragLeave(event)">
            
            <svg class="w-16 h-16 text-ocean-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"/>
            </svg>

            <h3 class="text-lg font-semibold text-cloud-900 mb-2">Glissez-déposez votre vidéo</h3>
            <p class="text-cloud-600 mb-4">ou cliquez pour sélectionner</p>
            <p class="text-xs text-cloud-500">MP4, WebM, OGG, AVI, MOV, MKV • Max 5 GB</p>

            <input type="file" id="video-input" accept="video/*" style="display: none;" onchange="handleFileSelect(event)">
        </div>

        {{-- Formulaire --}}
        <form id="upload-form" enctype="multipart/form-data" class="space-y-6">
            @csrf

            {{-- Input vidéo (masqué) --}}
            <div style="display: none;">
                <input type="file" name="video" id="form-video" accept="video/*" required>
            </div>

            {{-- Infos vidéo --}}
            <div id="video-info" style="display: none;" class="bg-ocean-50 border border-ocean-200 rounded-lg p-4">
                <p class="font-medium text-ocean-900 mb-2">
                    <span id="video-filename"></span>
                </p>
                <div class="flex justify-between text-sm text-ocean-700">
                    <span id="video-size"></span>
                    <span id="video-duration">Durée: --:--</span>
                </div>
            </div>

            {{-- Durée manuelle --}}
            <div>
                <label for="duration_minutes" class="block text-sm font-medium text-cloud-900 mb-2">
                    Durée (en minutes) <span class="text-cloud-500">(optionnel)</span>
                </label>
                <input type="number" name="duration_minutes" id="duration_minutes" min="0" step="0.5"
                       placeholder="ex: 15.5"
                       class="w-full border border-cloud-300 rounded-lg px-4 py-2 focus:outline-none focus:border-ocean-500">
            </div>

            {{-- Barre de progression --}}
            <div id="progress-container" style="display: none;" class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-sm font-medium text-cloud-900">Upload en cours...</span>
                    <span id="progress-percent" class="text-sm text-cloud-600">0%</span>
                </div>
                <div class="w-full bg-cloud-200 rounded-full h-3 overflow-hidden">
                    <div id="progress-bar" class="bg-gradient-to-r from-ocean-400 to-ocean-600 h-full transition-all" style="width: 0%"></div>
                </div>
                <div class="flex justify-between text-xs text-cloud-500">
                    <span id="progress-loaded">0 MB</span>
                    <span id="progress-total">0 MB</span>
                </div>
            </div>

            {{-- Messages --}}
            <div id="error-message" style="display: none;" class="bg-red-50 border border-red-200 text-red-700 p-4 rounded-lg">
                <p id="error-text"></p>
            </div>

            <div id="success-message" style="display: none;" class="bg-green-50 border border-green-200 text-green-700 p-4 rounded-lg flex items-center gap-3">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
                <span id="success-text">Vidéo uploadée avec succès!</span>
            </div>

            {{-- Boutons --}}
            <div class="flex gap-3 pt-6 border-t border-cloud-200">
                <button type="submit" id="submit-btn" style="display: none;" class="flex-1 bg-ocean-600 text-white font-medium py-2 rounded-lg hover:bg-ocean-700 transition disabled:opacity-50">
                    Uploader la vidéo
                </button>
                <button type="button" id="select-btn" class="flex-1 bg-ocean-600 text-white font-medium py-2 rounded-lg hover:bg-ocean-700 transition"
                        onclick="document.getElementById('video-input').click()">
                    Sélectionner une vidéo
                </button>
                <a href="{{ route('lessons.show', $lesson) }}" class="flex-1 text-center border border-cloud-300 text-cloud-700 font-medium py-2 rounded-lg hover:bg-cloud-50 transition">
                    Retour
                </a>
            </div>
        </form>
    </div>

    {{-- Infos utiles --}}
    <div class="mt-8 grid md:grid-cols-2 gap-6">
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
            <h3 class="font-semibold text-blue-900 mb-2">💡 Formats supportés</h3>
            <ul class="text-sm text-blue-800 space-y-1">
                <li>✓ MP4 (recommandé)</li>
                <li>✓ WebM</li>
                <li>✓ OGG</li>
                <li>✓ AVI, MOV, MKV</li>
            </ul>
        </div>

        <div class="bg-green-50 border border-green-200 rounded-lg p-6">
            <h3 class="font-semibold text-green-900 mb-2">📊 Limites</h3>
            <ul class="text-sm text-green-800 space-y-1">
                <li>Taille maximale: 5 GB</li>
                <li>Résolution: aucune limite</li>
                <li>Bit rate: aucune limite</li>
            </ul>
        </div>
    </div>
</div>

<script>
const dropZone = document.getElementById('drop-zone');
const videoInput = document.getElementById('video-input');
const formVideo = document.getElementById('form-video');
const uploadForm = document.getElementById('upload-form');
const submitBtn = document.getElementById('submit-btn');
const selectBtn = document.getElementById('select-btn');
const videoInfo = document.getElementById('video-info');
const progressContainer = document.getElementById('progress-container');
const errorMessage = document.getElementById('error-message');
const successMessage = document.getElementById('success-message');

let selectedFile = null;

// Drop zone
function handleDragOver(e) {
    e.preventDefault();
    dropZone.style.borderColor = '#0080a8';
    dropZone.style.backgroundColor = '#f0f9ff';
}

function handleDragLeave(e) {
    dropZone.style.borderColor = '#d0d7e0';
    dropZone.style.backgroundColor = 'transparent';
}

function handleDrop(e) {
    e.preventDefault();
    handleDragLeave();
    const files = e.dataTransfer.files;
    if (files.length > 0) {
        selectVideo(files[0]);
    }
}

function handleFileSelect(e) {
    const files = e.target.files;
    if (files.length > 0) {
        selectVideo(files[0]);
    }
}

function selectVideo(file) {
    // Validation
    const maxSize = 5 * 1024 * 1024 * 1024; // 5GB
    const validTypes = ['video/mp4', 'video/webm', 'video/ogg', 'video/avi', 'video/quicktime', 'video/x-matroska', 'video/mpeg'];
    
    if (file.size > maxSize) {
        showError('Fichier trop volumineux (max 5 GB)');
        return;
    }

    if (!validTypes.includes(file.type) && !file.name.match(/\.(mp4|webm|ogg|avi|mov|mkv)$/i)) {
        showError('Type de fichier non supporté');
        return;
    }

    selectedFile = file;
    formVideo.files = videoInput.files;
    
    // Afficher infos
    videoInfo.style.display = 'block';
    document.getElementById('video-filename').textContent = file.name;
    document.getElementById('video-size').textContent = formatBytes(file.size);
    
    // Obtenir la durée vidéo
    getVideoDuration(file);
    
    submitBtn.style.display = 'block';
    selectBtn.textContent = '✏️ Changer la vidéo';
}

function getVideoDuration(file) {
    const video = document.createElement('video');
    video.preload = 'metadata';
    video.onloadedmetadata = function() {
        const duration = Math.floor(video.duration / 60);
        document.getElementById('video-duration').textContent = 'Durée: ' + formatDuration(video.duration);
        document.getElementById('duration_minutes').value = duration;
    };
    video.onerror = () => {
        document.getElementById('video-duration').textContent = 'Durée: --:--';
    };
    video.src = URL.createObjectURL(file);
}

function formatBytes(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return Math.round((bytes / Math.pow(k, i)) * 100) / 100 + ' ' + sizes[i];
}

function formatDuration(seconds) {
    const hours = Math.floor(seconds / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);
    const secs = Math.floor(seconds % 60);
    
    if (hours > 0) {
        return hours + ':' + String(minutes).padStart(2, '0') + ':' + String(secs).padStart(2, '0');
    }
    return minutes + ':' + String(secs).padStart(2, '0');
}

uploadForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    if (!selectedFile) {
        showError('Sélectionnez une vidéo');
        return;
    }

    const formData = new FormData();
    formData.append('video', selectedFile);
    formData.append('duration_minutes', document.getElementById('duration_minutes').value);
    formData.append('_token', document.querySelector('input[name="_token"]').value);

    progressContainer.style.display = 'block';
    submitBtn.disabled = true;
    errorMessage.style.display = 'none';

    try {
        const xhr = new XMLHttpRequest();
        
        // Barre de progression
        xhr.upload.addEventListener('progress', (e) => {
            if (e.lengthComputable) {
                const percentComplete = (e.loaded / e.total) * 100;
                document.getElementById('progress-bar').style.width = percentComplete + '%';
                document.getElementById('progress-percent').textContent = Math.round(percentComplete) + '%';
                document.getElementById('progress-loaded').textContent = formatBytes(e.loaded);
                document.getElementById('progress-total').textContent = formatBytes(e.total);
            }
        });

        xhr.addEventListener('load', () => {
            if (xhr.status === 200) {
                const response = JSON.parse(xhr.responseText);
                if (response.success) {
                    successMessage.style.display = 'flex';
                    setTimeout(() => {
                        window.location.href = '{{ route("lessons.show", $lesson) }}';
                    }, 2000);
                } else {
                    showError(response.error || 'Erreur lors de l\'upload');
                    submitBtn.disabled = false;
                }
            } else {
                showError('Erreur: ' + xhr.statusText);
                submitBtn.disabled = false;
            }
        });

        xhr.addEventListener('error', () => {
            showError('Erreur de connexion');
            submitBtn.disabled = false;
        });

        xhr.open('POST', '{{ route("video.upload", $lesson) }}');
        xhr.send(formData);

    } catch (err) {
        showError(err.message);
        submitBtn.disabled = false;
    }
});

function showError(message) {
    errorMessage.style.display = 'block';
    document.getElementById('error-text').textContent = message;
}
</script>
@endsection
