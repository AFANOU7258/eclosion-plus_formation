<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\LessonResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LessonResourceController extends Controller
{
    /**
     * Afficher les ressources d'une leçon
     */
    public function index(Lesson $lesson)
    {
        $this->authorize('view', $lesson);
        
        $resources = $lesson->resources()->get();
        
        return view('lessons.resources.index', compact('lesson', 'resources'));
    }

    /**
     * Créer une nouvelle ressource (formulaire)
     */
    public function create(Lesson $lesson)
    {
        $this->authorize('createResource', [LessonResource::class, $lesson]);
        
        return view('lessons.resources.create', compact('lesson'));
    }

    /**
     * Stocker une nouvelle ressource
     */
    public function store(Request $request, Lesson $lesson)
    {
        $this->authorize('createResource', [LessonResource::class, $lesson]);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'type' => 'required|in:pdf,image,document,link,video',
            'description' => 'nullable|string',
            'file' => 'nullable|file|max:102400', // 100MB max
            'url' => 'nullable|url|required_if:type,link',
            'order' => 'nullable|integer|min:0',
        ]);

        if ($validated['type'] === 'link') {
            // Ressource externe (URL)
            LessonResource::create([
                'lesson_id' => $lesson->id,
                'title' => $validated['title'],
                'type' => $validated['type'],
                'url' => $validated['url'],
                'description' => $validated['description'] ?? null,
                'order' => $validated['order'] ?? 0,
            ]);
        } else {
            // Fichier à uploader
            if (!$request->hasFile('file')) {
                return back()->withErrors(['file' => 'Un fichier est requis pour ce type de ressource']);
            }

            $file = $request->file('file');
            $path = $file->store('lesson-resources/' . $lesson->id, 'public');

            LessonResource::create([
                'lesson_id' => $lesson->id,
                'title' => $validated['title'],
                'type' => $validated['type'],
                'file_path' => $path,
                'description' => $validated['description'] ?? null,
                'order' => $validated['order'] ?? 0,
            ]);
        }

        return redirect()->route('lessons.show', $lesson)
            ->with('success', 'Ressource ajoutée avec succès');
    }

    /**
     * Éditer une ressource
     */
    public function edit(Lesson $lesson, LessonResource $resource)
    {
        $this->authorize('updateResource', [$lesson, $resource]);
        
        return view('lessons.resources.edit', compact('lesson', 'resource'));
    }

    /**
     * Mettre à jour une ressource
     */
    public function update(Request $request, Lesson $lesson, LessonResource $resource)
    {
        $this->authorize('updateResource', [$lesson, $resource]);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'file' => 'nullable|file|max:102400',
            'url' => 'nullable|url',
            'order' => 'nullable|integer|min:0',
        ]);

        if ($request->hasFile('file') && $resource->type !== 'link') {
            // Supprimer ancien fichier
            if ($resource->file_path) {
                Storage::disk('public')->delete($resource->file_path);
            }
            
            $file = $request->file('file');
            $path = $file->store('lesson-resources/' . $lesson->id, 'public');
            $validated['file_path'] = $path;
        }

        $resource->update($validated);

        return redirect()->route('lessons.show', $lesson)
            ->with('success', 'Ressource mise à jour');
    }

    /**
     * Télécharger une ressource
     */
    public function download(LessonResource $resource)
    {
        if ($resource->type === 'link') {
            return redirect($resource->url);
        }

        if (!$resource->file_path) {
            return back()->withErrors(['error' => 'Fichier non disponible']);
        }

        return Storage::disk('public')->download($resource->file_path, $resource->title);
    }

    /**
     * Supprimer une ressource
     */
    public function destroy(Lesson $lesson, LessonResource $resource)
    {
        $this->authorize('deleteResource', [$lesson, $resource]);

        // Supprimer le fichier s'il existe
        if ($resource->file_path) {
            Storage::disk('public')->delete($resource->file_path);
        }

        $resource->delete();

        return redirect()->route('lessons.show', $lesson)
            ->with('success', 'Ressource supprimée');
    }

    /**
     * Réordonner les ressources (drag & drop)
     */
    public function reorder(Request $request, Lesson $lesson)
    {
        $this->authorize('updateResource', [LessonResource::class, $lesson]);

        $order = $request->input('order', []);
        
        foreach ($order as $index => $resourceId) {
            LessonResource::where('id', $resourceId)
                ->where('lesson_id', $lesson->id)
                ->update(['order' => $index]);
        }

        return response()->json(['success' => true]);
    }
}
