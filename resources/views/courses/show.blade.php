@extends('layouts.app')
@section('title', $course->title)

@section('content')
<div class="max-w-5xl mx-auto px-4 sm:px-6 py-8">
    <nav class="text-sm text-gray-400 mb-6">
        <a href="{{ url('/') }}" class="hover:text-eclosion-600">Accueil</a> ›
        <a href="{{ route('courses.index') }}" class="hover:text-eclosion-600">Formations</a> ›
        <span class="text-gray-700">{{ $course->title }}</span>
    </nav>

    <div class="grid lg:grid-cols-3 gap-8">
        {{-- CONTENU --}}
        <div class="lg:col-span-2">
            <h1 class="font-display text-2xl md:text-3xl font-normal text-gray-800 mb-3">{{ $course->title }}</h1>
            <p class="text-gray-600 mb-6 leading-relaxed">{{ $course->description }}</p>

            <div class="flex items-center gap-3 mb-8 p-4 bg-white rounded-lg border border-gray-200">
                <span class="w-10 h-10 rounded-full bg-eclosion-600 text-white flex items-center justify-center font-bold text-sm">
                    {{ mb_substr($course->instructor->name ?? 'F', 0, 1) }}
                </span>
                <div>
                    <p class="font-medium text-gray-800">{{ $course->instructor->name ?? 'Formateur' }}</p>
                    <p class="text-xs text-gray-500">Formateur</p>
                </div>
            </div>

            <h2 class="font-display text-xl font-normal text-gray-800 mb-4">Programme</h2>
            <div class="space-y-3">
                @foreach($course->levels as $level)
                <div class="border border-gray-200 rounded-lg overflow-hidden">
                    <button onclick="this.nextElementSibling.classList.toggle('hidden'); this.querySelector('span.material-icons').classList.toggle('rotate-180')"
                        class="w-full flex items-center justify-between p-4 bg-white hover:bg-gray-50 transition text-left">
                        <div>
                            <span class="text-xs text-eclosion-600 font-medium uppercase">Niveau {{ $level->order }}</span>
                            <h3 class="font-medium text-gray-800 mt-0.5">{{ $level->title }}</h3>
                        </div>
                        <span class="material-icons text-gray-400 transition-transform">expand_more</span>
                    </button>
                    <div class="hidden border-t border-gray-200 bg-gray-50">
                        <p class="px-4 pt-3 text-gray-600 text-sm">{{ $level->description }}</p>
                        @if($level->level_image)
                            <div class="px-4 pt-2"><img src="{{ asset('storage/'.$level->level_image) }}" alt="{{ $level->title }}" class="rounded-lg max-h-48 object-cover"></div>
                        @endif
                        @if($level->level_audio)
                            <div class="px-4 pt-2"><audio controls class="w-full"><source src="{{ asset('storage/'.$level->level_audio) }}" type="audio/mpeg"></audio></div>
                        @endif
                        <ul class="p-4 space-y-1.5">
                            @foreach($level->lessons as $lesson)
                            <li class="flex items-center gap-3 text-sm text-gray-700 py-2 px-3 rounded-lg hover:bg-white transition">
                                <span class="w-6 h-6 rounded-full bg-eclosion-100 text-eclosion-600 flex items-center justify-center text-xs font-bold shrink-0">{{ $lesson->order }}</span>
                                <span class="flex-1">{{ $lesson->title }}</span>
                                <span class="text-gray-400 text-xs flex items-center gap-1">
                                    @if($lesson->isVideo()) <span class="material-icons text-sm">videocam</span>
                                    @elseif($lesson->isAudio()) <span class="material-icons text-sm">headphones</span>
                                    @else <span class="material-icons text-sm">description</span> @endif
                                    @if($lesson->duration_minutes) {{ $lesson->duration_minutes }} min @endif
                                </span>
                            </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- SIDEBAR --}}
        <div class="lg:col-span-1">
            <div class="bg-white border border-gray-200 rounded-lg p-6 sticky top-24">
                <div class="text-center mb-5">
                    <span class="font-display text-3xl font-medium text-eclosion-600">{{ number_format($course->price, 0, ',', ' ') }} FCFA</span>
                </div>

                @auth
                    @php $enrollment = \App\Models\Enrollment::where('user_id', Auth::id())->where('course_id', $course->id)->first(); @endphp

                    @if(!$enrollment)
                        <button onclick="openWaveModal()" class="w-full bg-eclosion-600 text-white font-medium py-3 rounded-full hover:bg-eclosion-700 transition mb-3">
                                                    <span class="material-icons text-sm align-middle">payment</span> Payer la formation
                                                </button>
                    @elseif($enrollment->isPending())
                        <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 rounded-lg p-4 text-sm text-center">
                            <span class="material-icons text-sm align-middle">hourglass_empty</span> Demande <strong>en attente</strong>
                        </div>
                    @elseif($enrollment->isApproved())
                        <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg p-4 text-sm text-center mb-4">
                            <span class="material-icons text-sm align-middle">check_circle</span> Accès <strong>approuvé</strong>
                        </div>
                        <a href="{{ route('lessons.show', $course->lessons->first() ?? 1) }}" class="block w-full text-center bg-ocean-600 text-white font-medium py-3 rounded-full hover:bg-ocean-700 transition">
                            Commencer la formation
                        </a>
                    @else
                        <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg p-4 text-sm text-center mb-3">
                            <span class="material-icons text-sm align-middle">cancel</span> Demande <strong>refusée</strong>
                        </div>
                        <button onclick="openWaveModal()" class="w-full bg-eclosion-600 text-white font-medium py-3 rounded-full hover:bg-eclosion-700 transition">
                            <span class="material-icons text-sm align-middle">refresh</span> Redemander l'accès
                        </button>
                    @endif
                @else
                    <a href="{{ route('login') }}" class="block w-full text-center bg-eclosion-600 text-white font-medium py-3 rounded-full hover:bg-eclosion-700 transition mb-3">
                        Se connecter pour s'inscrire
                    </a>
                @endauth

                <div class="mt-5 pt-5 border-t border-gray-100 space-y-2 text-sm text-gray-500">
                    <div class="flex items-center gap-1">
                        @for($i=1; $i<=5; $i++)
                            <span class="material-icons text-sm {{ $i <= $course->average_rating ? 'text-yellow-500' : 'text-gray-300' }}">star</span>
                        @endfor
                        <span class="ml-1">{{ $course->average_rating }} ({{ $course->reviews_count }} avis)</span>
                    </div>
                    <div class="flex items-center gap-2"><span class="material-icons text-sm">layers</span> {{ $course->levels_count }} niveaux</div>
                    <div class="flex items-center gap-2"><span class="material-icons text-sm">play_circle</span> {{ $course->total_lessons_count }} leçons</div>
                    <div class="flex items-center gap-2"><span class="material-icons text-sm">people</span> {{ $course->students_count }} apprenants</div>
                </div>
            </div>
        </div>
    </div>

    {{-- AVIS --}}
    <div class="max-w-5xl mx-auto mt-8 px-4 sm:px-6">
        <div class="bg-white rounded-lg border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="font-display text-lg text-gray-800">Avis des apprenants</h2>
                    <div class="flex items-center gap-1 mt-1">
                        @for($i=1; $i<=5; $i++)
                            <span class="material-icons {{ $i <= $course->average_rating ? 'text-yellow-500' : 'text-gray-300' }}">star</span>
                        @endfor
                        <span class="text-sm text-gray-500 ml-2">{{ $course->average_rating }} · {{ $course->reviews_count }} avis</span>
                    </div>
                </div>
                @auth
                <button onclick="document.getElementById('review-form').classList.toggle('hidden')" class="bg-eclosion-600 text-white px-5 py-2 rounded-full text-sm font-medium hover:bg-eclosion-700 transition">Donner mon avis</button>
                @endauth
            </div>

            <div id="review-form" class="hidden mb-6 p-5 bg-gray-50 rounded-lg">
                <div class="flex items-center gap-1 mb-3" id="stars">
                    @for($i=1; $i<=5; $i++)
                        <button onclick="setRating({{ $i }})" class="star-btn text-gray-300 hover:text-yellow-500 transition"><span class="material-icons text-2xl" id="star-{{ $i }}">star</span></button>
                    @endfor
                </div>
                <textarea id="review-comment" rows="2" placeholder="Partagez votre expérience..." class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm focus:outline-none focus:border-eclosion-500 mb-3"></textarea>
                <div class="flex gap-2">
                    <button onclick="submitReview({{ $course->id }})" class="bg-eclosion-600 text-white px-4 py-2 rounded-full text-sm font-medium hover:bg-eclosion-700">Publier</button>
                    <button onclick="document.getElementById('review-form').classList.add('hidden')" class="px-4 py-2 text-sm text-gray-500">Annuler</button>
                </div>
            </div>

            <div class="space-y-4">
                @forelse($course->reviews as $review)
                <div class="border-b border-gray-100 pb-4">
                    <div class="flex items-center gap-3 mb-1">
                        <span class="w-8 h-8 rounded-full bg-ocean-600 text-white flex items-center justify-center text-xs font-bold">{{ strtoupper(mb_substr($review->user->name, 0, 1)) }}</span>
                        <div>
                            <p class="text-sm font-medium">{{ $review->user->name }}</p>
                            <div class="flex">@for($i=1;$i<=5;$i++)<span class="material-icons text-sm {{ $i <= $review->rating ? 'text-yellow-500' : 'text-gray-300' }}">star</span>@endfor</div>
                        </div>
                        <span class="ml-auto text-xs text-gray-400">{{ $review->created_at->diffForHumans() }}</span>
                    </div>
                    @if($review->comment)<p class="text-sm text-gray-600 ml-11">{{ $review->comment }}</p>@endif
                </div>
                @empty
                <p class="text-gray-400 text-sm text-center py-4">Aucun avis. Soyez le premier !</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- MODAL WAVE --}}
{{-- MODAL PAIEMENT WAVE & ORANGE MONEY --}}
<div id="wave-modal" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-2xl max-w-md w-full mx-4 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-display text-lg text-gray-800">Payer la formation</h3>
            <button onclick="closeWaveModal()" class="p-1 rounded-full hover:bg-gray-100"><span class="material-icons">close</span></button>
        </div>

        <div class="bg-green-50 rounded-xl p-4 mb-4 text-center">
            <p class="text-sm font-medium text-green-800 mb-1">Montant à payer</p>
            <p class="text-3xl font-display font-bold text-green-700">{{ number_format($course->price, 0, ',', ' ') }} FCFA</p>
        </div>

        <div class="bg-orange-50 border border-orange-200 rounded-xl p-4 mb-4">
            <div class="flex items-center gap-2 mb-2">
                <span class="w-8 h-8 rounded-full bg-orange-500 text-white flex items-center justify-center text-xs font-bold">W</span>
                <span class="font-medium text-sm">Wave</span>
                <span class="w-8 h-8 rounded-full bg-orange-600 text-white flex items-center justify-center text-xs font-bold">OM</span>
                <span class="font-medium text-sm">Orange Money</span>
            </div>
            <p class="text-xs text-gray-600 mb-2">Envoyez le paiement au numéro :</p>
            <div class="bg-white rounded-lg px-4 py-3 text-center border-2 border-orange-300">
                <span class="text-2xl font-bold text-gray-800 tracking-widest">+223 72 58 54 79</span>
            </div>
            <p class="text-xs text-gray-500 mt-2">Disponible sur <strong>Wave</strong> et <strong>Orange Money</strong></p>
        </div>

        <div class="mb-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Méthode utilisée</label>
            <select id="pay-method" class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-eclosion-500">
                <option value="wave">Wave</option>
                <option value="orange_money">Orange Money</option>
            </select>
        </div>

        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Référence de la transaction *</label>
            <input type="text" id="wave-ref" placeholder="Ex: WAVE-123456 ou OM-789012"
                class="w-full border border-gray-300 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:border-eclosion-500">
            <p class="text-xs text-gray-400 mt-1">La référence se trouve dans l'historique de vos transactions</p>
        </div>

        <button onclick="submitPayment({{ $course->id }})" class="w-full bg-eclosion-600 text-white font-medium py-3 rounded-full hover:bg-eclosion-700 transition">
            <span class="material-icons text-sm align-middle">check_circle</span> Confirmer le paiement
        </button>
        <p class="text-xs text-gray-400 text-center mt-2">Accès activé après vérification du paiement</p>
    </div>
</div>

@auth
<script>
let selectedRating = 0;
function setRating(r) {
    selectedRating = r;
    for(let i=1;i<=5;i++) {
        document.getElementById('star-'+i).parentElement.className = i<=r ? 'star-btn text-yellow-500 transition' : 'star-btn text-gray-300 hover:text-yellow-500 transition';
    }
}
async function submitReview(courseId) {
    if(!selectedRating){ alert('Donnez une note'); return; }
    try {
        const res = await fetch('/formations/'+courseId+'/review', {
            method:'POST',
            headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'},
            body: JSON.stringify({ rating: selectedRating, comment: document.getElementById('review-comment').value })
        });
        if(res.ok) location.reload();
    } catch(e){}
}

function openWaveModal() { document.getElementById('wave-modal').classList.remove('hidden'); document.getElementById('wave-modal').classList.add('flex'); }
function closeWaveModal() { document.getElementById('wave-modal').classList.add('hidden'); document.getElementById('wave-modal').classList.remove('flex'); }

async function submitPayment(courseId) {
    const ref = document.getElementById('wave-ref').value.trim();
    const method = document.getElementById('pay-method').value;
    if (!ref) { alert('Veuillez saisir la référence de la transaction.'); return; }
    try {
        const res = await fetch('/enrollments/' + courseId + '/request', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
            body: JSON.stringify({ payment_method: method, payment_reference: ref })
        });
        const data = await res.json();
        if (res.ok) { closeWaveModal(); location.reload(); }
        else { alert(data.message || 'Erreur lors de la demande.'); }
    } catch(err) { alert('Erreur réseau. Veuillez réessayer.'); }
}
</script>
@endauth
@endsection
