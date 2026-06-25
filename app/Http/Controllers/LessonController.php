<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use Illuminate\View\View;

class LessonController extends Controller
{
    /** Page de visionnage */
    public function show(Lesson $lesson): View
    {
        $lesson->load(["level.course.levels.lessons"]);

        return view("lessons.show", compact("lesson"));
    }
}
