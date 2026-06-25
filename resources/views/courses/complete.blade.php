@extends('layouts.app')
@section('title', 'Félicitations !')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-12 text-center">
    {{-- FÉLICITATIONS --}}
    <div class="mb-8">
        <span class="material-icons text-7xl text-yellow-500 mb-4 block">emoji_events</span>
        <h1 class="font-display text-3xl md:text-4xl text-gray-800 mb-3">Félicitations !</h1>
        <p class="text-lg text-gray-600">Vous avez terminé la formation</p>
        <p class="text-2xl font-display font-medium text-eclosion-600 mt-2">{{ $course->title }}</p>
    </div>

    {{-- STATS --}}
    <div class="grid grid-cols-3 gap-4 mb-10 max-w-md mx-auto">
        <div class="bg-white rounded-xl border border-gray-200 p-4"><p class="text-2xl font-display text-eclosion-600">{{ $course->levels_count }}</p><p class="text-xs text-gray-500">Niveaux</p></div>
        <div class="bg-white rounded-xl border border-gray-200 p-4"><p class="text-2xl font-display text-ocean-600">{{ $course->total_lessons_count }}</p><p class="text-xs text-gray-500">Leçons</p></div>
        <div class="bg-white rounded-xl border border-gray-200 p-4"><p class="text-2xl font-display text-green-600">100%</p><p class="text-xs text-gray-500">Complété</p></div>
    </div>

    {{-- QUIZ IA --}}
    <div class="bg-white rounded-2xl border border-gray-200 p-6 mb-8 text-left">
        <div class="flex items-center gap-3 mb-6">
            <span class="material-icons text-purple-600 text-3xl">psychology</span>
            <div>
                <h2 class="font-display text-xl text-gray-800">Quiz de validation</h2>
                <p class="text-sm text-gray-500">Testez vos connaissances sur cette formation</p>
            </div>
        </div>

        @if($quiz && $quiz->completed)
            {{-- RÉSULTATS --}}
            <div class="text-center py-6">
                <p class="text-lg text-gray-600 mb-2">Votre score</p>
                <p class="text-5xl font-display font-bold {{ $quiz->score >= $quiz->total/2 ? 'text-green-600' : 'text-yellow-600' }}">{{ $quiz->score }}/{{ $quiz->total }}</p>
                <p class="text-sm text-gray-500 mt-1">{{ round(($quiz->score/max($quiz->total,1))*100) }}% de bonnes réponses</p>
                @if($quiz->score == $quiz->total)
                    <p class="text-green-600 font-medium mt-3 flex items-center justify-center gap-1"><span class="material-icons">star</span> Score parfait !</p>
                @endif
            </div>
        @elseif($quiz && $quiz->questions)
            {{-- QUESTIONS --}}
            <form id="quiz-form" class="space-y-5">
                @foreach($quiz->questions as $i => $q)
                <div class="bg-gray-50 rounded-xl p-4">
                    <p class="text-sm font-medium text-gray-800 mb-3"><span class="text-eclosion-600 font-bold">Q{{ $i+1 }}.</span> {{ $q['question'] }}</p>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach($q['options'] as $opt)
                        <label class="flex items-center gap-2 p-2.5 rounded-lg border border-gray-200 cursor-pointer hover:border-eclosion-300 transition text-sm">
                            <input type="radio" name="answers[{{ $i }}]" value="{{ $opt }}" class="text-eclosion-600"> {{ $opt }}
                        </label>
                        @endforeach
                    </div>
                </div>
                @endforeach
                <button type="submit" class="w-full bg-eclosion-600 text-white font-medium py-3 rounded-full hover:bg-eclosion-700 transition">Valider mes réponses</button>
            </form>
        @else
            {{-- GÉNÉRER LE QUIZ --}}
            <div id="quiz-loader" class="text-center py-8">
                <p class="text-gray-500 mb-4">Générez un quiz personnalisé par l'IA</p>
                <button onclick="generateQuiz({{ $course->id }})" id="gen-btn" class="bg-purple-600 text-white px-6 py-3 rounded-full font-medium hover:bg-purple-700 transition flex items-center gap-2 mx-auto">
                    <span class="material-icons">auto_awesome</span> Générer le quiz
                </button>
            </div>
        @endif
    </div>

    <div class="flex gap-3 justify-center">
        <a href="{{ route('courses.index') }}" class="bg-white border border-gray-300 text-gray-700 px-6 py-3 rounded-full font-medium hover:bg-gray-50 transition">Explorer d'autres formations</a>
        <a href="{{ route('enrollments.index') }}" class="bg-eclosion-600 text-white px-6 py-3 rounded-full font-medium hover:bg-eclosion-700 transition">Mes cours</a>
    </div>
</div>

<script>
async function generateQuiz(courseId) {
    const btn = document.getElementById('gen-btn');
    btn.disabled = true;
    btn.innerHTML = '<span class="material-icons animate-spin">sync</span> Génération...';
    try {
        const res = await fetch('/quiz/' + courseId + '/generate', {
            headers: { 'Accept': 'application/json' }
        });
        if (res.ok) { location.reload(); }
    } catch(e) { btn.disabled = false; btn.innerHTML = 'Réessayer'; }
}

const form = document.getElementById('quiz-form');
if (form) {
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        const data = new FormData(form);
        const answers = {};
        for (let [k, v] of data.entries()) answers[k] = v;
        try {
            const res = await fetch('/quiz/{{ $quiz->id ?? 0 }}/submit', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                body: JSON.stringify({ answers })
            });
            if (res.ok) { location.reload(); }
        } catch(e) {}
    });
}
</script>
@endsection
