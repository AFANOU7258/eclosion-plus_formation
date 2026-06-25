@extends('layouts.admin')
@section('title', 'Formations')

@section('content')
<div class="flex items-center justify-between mb-6">
    <h1 class="font-display text-2xl font-normal text-gray-800">Formations</h1>
    <a href="{{ route('admin.courses.create') }}" class="inline-flex items-center gap-2 bg-eclosion-600 text-white px-5 py-2.5 rounded-full text-sm font-medium hover:bg-eclosion-700 transition shadow-sm">
        <span class="material-icons text-sm">add</span> Nouvelle formation
    </a>
</div>

@if(session('success'))
    <div class="bg-green-50 text-green-800 rounded-lg px-4 py-3 mb-4 text-sm flex items-center gap-2">
        <span class="material-icons text-sm">check_circle</span> {{ session('success') }}
    </div>
@endif

<div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="text-left text-gray-500 border-b border-gray-200 bg-gray-50">
            <tr>
                <th class="px-5 py-3 font-medium">Formation</th>
                <th class="px-5 py-3 font-medium">Niveaux</th>
                <th class="px-5 py-3 font-medium">Leçons</th>
                <th class="px-5 py-3 font-medium">Prix</th>
                <th class="px-5 py-3 font-medium">Statut</th>
                <th class="px-5 py-3 font-medium"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @foreach($courses as $course)
            <tr class="hover:bg-gray-50">
                <td class="px-5 py-3">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded bg-eclosion-600 flex items-center justify-center text-white text-xs font-bold shrink-0">
                            {{ strtoupper(substr($course->title, 0, 1)) }}
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">{{ $course->title }}</p>
                            <p class="text-xs text-gray-400">{{ mb_strimwidth($course->description, 0, 50, '...') }}</p>
                        </div>
                    </div>
                </td>
                <td class="px-5 py-3 text-gray-600">{{ $course->levels_count }}</td>
                <td class="px-5 py-3 text-gray-600">{{ $course->lessons_count }}</td>
                <td class="px-5 py-3 font-medium">{{ number_format($course->price , 0, ',', ' ')  }} FCFA</td>
                <td class="px-5 py-3">
                    <span class="px-2.5 py-1 rounded-full text-xs font-medium {{ $course->status === 'published' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                        {{ $course->status === 'published' ? 'Publié' : 'Brouillon' }}
                    </span>
                </td>
                <td class="px-5 py-3">
                    <div class="flex items-center gap-2">
                        <a href="{{ route('admin.courses.edit', $course) }}" class="text-ocean-600 text-xs font-medium hover:underline">Modifier</a>
                        <button onclick="shareCourse({{ $course->id }}, '{{ $course->title }}')" class="text-purple-600 text-xs font-medium hover:underline">Partager</button>
                        <form action="{{ route('admin.courses.destroy', $course) }}" method="POST" onsubmit="return confirm('Supprimer ?')" class="inline">
                            @csrf @method('DELETE')
                            <button class="text-red-500 text-xs font-medium hover:underline">Supprimer</button>
                        </form>
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
<div class="mt-6">{{ $courses->links() }}</div>

<div id="share-modal" class="fixed inset-0 bg-black/50 z-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-xl max-w-md w-full mx-4 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-display text-lg text-gray-800">Partager la formation</h3>
            <button onclick="document.getElementById('share-modal').classList.add('hidden')" class="p-1 rounded-full hover:bg-gray-100"><span class="material-icons">close</span></button>
        </div>
        <p class="text-sm text-gray-600 mb-4">Générez un lien d'accès direct. Toute personne avec ce lien peut consulter la formation sans créer de compte.</p>
        <form id="share-form" method="POST" class="space-y-3">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Expiration (jours, vide = illimité)</label>
                <input type="number" name="expires_at" placeholder="Ex: 30" min="1" class="w-full border border-gray-300 rounded-lg px-4 py-2 text-sm">
            </div>
            <button type="submit" class="w-full bg-purple-600 text-white font-medium py-2.5 rounded-full hover:bg-purple-700 transition">Générer le lien</button>
        </form>
        <div id="share-result" class="hidden mt-4 p-3 bg-green-50 rounded-lg">
            <p class="text-xs text-green-800 font-medium mb-1">Lien créé :</p>
            <div class="flex gap-2">
                <input type="text" id="share-url" readonly class="flex-1 border border-green-200 rounded-lg px-3 py-1.5 text-xs bg-white">
                <button onclick="navigator.clipboard.writeText(document.getElementById('share-url').value); alert('Copié !')" class="bg-green-600 text-white px-3 py-1.5 rounded-lg text-xs font-medium">Copier</button>
            </div>
        </div>
    </div>
</div>

<script>
function shareCourse(id, title) {
    const modal = document.getElementById('share-modal');
    const form = document.getElementById('share-form');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    form.action = '/admin/courses/' + id + '/share';
    form.onsubmit = async function(e) {
        e.preventDefault();
        const fd = new FormData(form);
        try {
            const res = await fetch(form.action, { method:'POST', headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}','Accept':'application/json'}, body: fd });
            const data = await res.json();
            if (data.url) {
                document.getElementById('share-url').value = data.url;
                document.getElementById('share-result').classList.remove('hidden');
            }
        } catch(err) { alert('Erreur lors de la génération du lien.'); }
    };
}
</script>
@endsection
