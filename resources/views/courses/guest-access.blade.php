<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $course->title }} — Eclosion+</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config={theme:{extend:{colors:{eclosion:{600:'#0d6025',700:'#0b4f1e'},ocean:{600:'#06429a',700:'#05357d'}}}}}</script>
    <link href="https://fonts.googleapis.com/css2?family=Google+Sans:wght@400;500;700&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>*{font-family:'Roboto',sans-serif}.font-display{font-family:'Google Sans','Roboto',sans-serif}.material-icons{font-family:'Material Icons'!important;font-size:20px;vertical-align:middle;display:inline-block;line-height:1;text-transform:none;letter-spacing:normal;word-wrap:normal;white-space:nowrap;direction:ltr;-webkit-font-smoothing:antialiased}</style>
</head>
<body class="bg-gray-50 text-gray-800">

    <header class="bg-white border-b border-gray-200 h-14 flex items-center px-4">
        <span class="font-display text-lg font-medium text-gray-800">Eclosion<span class="text-eclosion-600">+</span></span>
        <div class="flex-1"></div>
        <span class="text-xs bg-ocean-100 text-ocean-700 px-3 py-1 rounded-full font-medium">Accès invité</span>
    </header>

    <main class="max-w-4xl mx-auto px-4 py-8">
        <div class="bg-ocean-50 border border-ocean-200 rounded-lg p-4 mb-6 text-sm text-ocean-800">
            <span class="material-icons align-middle mr-1">info</span>
            Vous consultez cette formation en accès partagé.
            @if($link->expires_at)
                Ce lien expire le {{ $link->expires_at->format('d/m/Y') }}.
            @endif
            <a href="{{ route('register') }}" class="ml-2 text-ocean-700 font-medium underline">Créez un compte</a> pour suivre votre progression.
        </div>

        <h1 class="font-display text-3xl text-gray-800 mb-3">{{ $course->title }}</h1>
        <p class="text-gray-600 mb-8">{{ $course->description }}</p>

        <h2 class="font-display text-xl text-gray-800 mb-4">Contenu de la formation</h2>
        <div class="space-y-3">
            @foreach($course->levels as $level)
            <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
                <button onclick="this.nextElementSibling.classList.toggle('hidden')" class="w-full flex items-center justify-between p-4 hover:bg-gray-50 text-left">
                    <div>
                        <span class="text-xs text-eclosion-600 font-medium uppercase">Niveau {{ $level->order }}</span>
                        <h3 class="font-medium text-gray-800 mt-0.5">{{ $level->title }}</h3>
                    </div>
                    <span class="material-icons text-gray-400">expand_more</span>
                </button>
                <div class="hidden border-t border-gray-200 bg-gray-50">
                    <p class="px-4 pt-3 text-gray-600 text-sm">{{ $level->description }}</p>
                    @if($level->level_image)
                        <div class="px-4 pt-2"><img src="{{ asset('storage/'.$level->level_image) }}" class="rounded-lg max-h-48 object-cover"></div>
                    @endif
                    @if($level->level_audio)
                        <div class="px-4 pt-2"><audio controls class="w-full"><source src="{{ asset('storage/'.$level->level_audio) }}" type="audio/mpeg"></audio></div>
                    @endif
                    <ul class="p-4 space-y-1.5">
                        @foreach($level->lessons as $lesson)
                        <li class="flex items-center gap-3 text-sm text-gray-700 py-2 px-3 rounded-lg hover:bg-white">
                            <span class="w-6 h-6 rounded-full bg-eclosion-100 text-eclosion-600 flex items-center justify-center text-xs font-bold">{{ $lesson->order }}</span>
                            <span class="flex-1">{{ $lesson->title }}</span>
                            <span class="text-xs text-gray-400">
                                @if($lesson->isVideo()) <span class="material-icons text-sm">videocam</span>
                                @elseif($lesson->isAudio()) <span class="material-icons text-sm">headphones</span>
                                @else <span class="material-icons text-sm">description</span> @endif
                            </span>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
            @endforeach
        </div>
    </main>

    <footer class="text-center text-sm text-gray-400 py-8 border-t border-gray-200 mt-12">
        Propulsé par Eclosion+ — <a href="{{ route('register') }}" class="text-ocean-600 hover:underline">Créez votre compte</a>
    </footer>
</body>
</html>
