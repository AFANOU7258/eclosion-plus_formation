<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\Level;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class AdminCourseController extends Controller
{
    public function __construct()
    {
        $this->middleware("auth");
    }

    // =================================================================
    // LISTE
    // =================================================================
    public function index(): View
    {
        return view("admin.courses.index", [
            "courses" => Course::withCount(["levels", "lessons", "enrollments"])
                ->latest()
                ->paginate(15),
        ]);
    }

    // =================================================================
    // CRÉATION
    // =================================================================
    public function create(): View
    {
        return view("admin.courses.create");
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            "title" => "required|string|max:255",
            "description" => "required|string",
            "price" => "nullable|numeric|min:0",
            "status" => "required|in:draft,published",
            "thumbnail" => "nullable|image|mimes:jpg,jpeg,png,webp|max:5120",
        ]);

        $thumbnailPath = null;
        if ($request->hasFile("thumbnail")) {
            $thumbnailPath = $request
                ->file("thumbnail")
                ->store("courses/thumbnails", "public");
        }

        $course = Course::create([
            "instructor_id" => Auth::id(),
            "title" => $validated["title"],
            "slug" => Str::slug($validated["title"]),
            "description" => $validated["description"],
            "price" => $validated["price"] ?? 0,
            "status" => $validated["status"],
            "thumbnail" => $thumbnailPath,
        ]);

        $this->saveLevelsAndLessons($request, $course);

        return redirect()
            ->route("admin.courses.index")
            ->with("success", "Formation créée !");
    }

    // =================================================================
    // ÉDITION
    // =================================================================
    public function edit(Course $course): View
    {
        $course->load(["levels.lessons"]);
        return view("admin.courses.edit", compact("course"));
    }

    public function update(Request $request, Course $course): RedirectResponse
    {
        $validated = $request->validate([
            "title" => "required|string|max:255",
            "description" => "required|string",
            "price" => "nullable|numeric|min:0",
            "status" => "required|in:draft,published",
            "thumbnail" => "nullable|image|mimes:jpg,jpeg,png,webp|max:5120",
        ]);

        if ($request->hasFile("thumbnail")) {
            if ($course->thumbnail) {
                Storage::disk("public")->delete($course->thumbnail);
            }
            $validated["thumbnail"] = $request
                ->file("thumbnail")
                ->store("courses/thumbnails", "public");
        }

        $validated["slug"] = Str::slug($validated["title"]);
        $course->update($validated);

        // Supprimer anciens niveaux/leçons
        foreach ($course->levels as $level) {
            foreach ($level->lessons as $lesson) {
                if ($lesson->media_path) {
                    Storage::disk("public")->delete($lesson->media_path);
                }
            }
        }
        $course->levels()->delete();

        $this->saveLevelsAndLessons($request, $course);

        return redirect()
            ->route("admin.courses.index")
            ->with("success", "Formation mise à jour !");
    }

    // =================================================================
    // MÉTHODE PARTAGÉE : sauvegarder niveaux + leçons + illustrations
    // =================================================================
    private function saveLevelsAndLessons(
        Request $request,
        Course $course,
    ): void {
        if (!$request->has("levels")) {
            return;
        }

        foreach ($request->input("levels", []) as $index => $levelData) {
            if (empty($levelData["title"])) {
                continue;
            }

            $level = $course->levels()->create([
                "title" => $levelData["title"],
                "description" => $levelData["description"] ?? "",
                "order" => $index + 1,
            ]);

            // Upload image du niveau
            if ($request->hasFile("levels.{$index}.level_image")) {
                $level->update([
                    "level_image" => $request
                        ->file("levels.{$index}.level_image")
                        ->store("levels/images", "public"),
                ]);
            }
            // Upload audio du niveau
            if ($request->hasFile("levels.{$index}.level_audio")) {
                $level->update([
                    "level_audio" => $request
                        ->file("levels.{$index}.level_audio")
                        ->store("levels/audio", "public"),
                ]);
            }

            if (!empty($levelData["lessons"])) {
                foreach ($levelData["lessons"] as $li => $lessonData) {
                    if (empty($lessonData["title"])) {
                        continue;
                    }

                    // Média principal (vidéo/audio/pdf)
                    $mediaPath = $lessonData["existing_media_path"] ?? null;
                    $mediaKey = "levels.{$index}.lessons.{$li}.media_file";
                    if ($request->hasFile($mediaKey)) {
                        if ($mediaPath) {
                            Storage::disk("public")->delete($mediaPath);
                        }
                        $mediaPath = $request
                            ->file($mediaKey)
                            ->store("courses/lessons", "public");
                    }

                    // Illustrations (images multiples)
                    $illustrations = [];
                    $illusKey = "levels.{$index}.lessons.{$li}.illustrations";
                    if ($request->hasFile($illusKey)) {
                        foreach ($request->file($illusKey) as $img) {
                            if ($img->isValid()) {
                                $illustrations[] = $img->store(
                                    "courses/illustrations",
                                    "public",
                                );
                            }
                        }
                    }

                    $level->lessons()->create([
                        "title" => $lessonData["title"],
                        "content" => $lessonData["content"] ?? "",
                        "media_type" => $lessonData["media_type"] ?? "video",
                        "media_path" => $mediaPath,
                        "duration_minutes" =>
                            $lessonData["duration_minutes"] ?? null,
                        "order" => $li + 1,
                        "illustrations" => !empty($illustrations)
                            ? $illustrations
                            : null,
                    ]);
                }
            }
        }
    }

    // =================================================================
    // SUPPRESSION
    // =================================================================
    public function destroy(Course $course): RedirectResponse
    {
        if ($course->thumbnail) {
            Storage::disk("public")->delete($course->thumbnail);
        }
        foreach ($course->lessons as $lesson) {
            if ($lesson->media_path) {
                Storage::disk("public")->delete($lesson->media_path);
            }
            if ($lesson->illustrations) {
                foreach ($lesson->illustrations as $img) {
                    Storage::disk("public")->delete($img);
                }
            }
        }
        $course->delete();
        return redirect()
            ->route("admin.courses.index")
            ->with("success", "Formation supprimée.");
    }
}
