<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CourseController extends Controller
{
    public function index(Request $request): View
    {
        $query = Course::published()->with("category");

        if ($search = $request->get("q")) {
            $query->search($search);
        }
        if ($cat = $request->get("categorie")) {
            $query->where("category_id", $cat);
        }

        return view("courses.index", [
            "courses" => $query
                ->latest()
                ->paginate(12)
                ->appends($request->query()),
            "categories" => Category::withCount("courses")->get(),
            "search" => $search ?? null,
        ]);
    }

    public function show(Course $course): View
    {
        $course->load([
            "instructor",
            "levels.lessons",
            "category",
            "reviews.user",
        ]);
        return view("courses.show", compact("course"));
    }
}
