<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /** Poster ou mettre à jour un avis */
    public function store(Request $request, Course $course): JsonResponse
    {
        $validated = $request->validate([
            'rating'  => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $review = Review::updateOrCreate(
            ['user_id' => Auth::id(), 'course_id' => $course->id],
            ['rating' => $validated['rating'], 'comment' => $validated['comment']]
        );

        return response()->json([
            'review' => $review->load('user:id,name'),
            'average'=> $course->average_rating,
            'count'  => $course->reviews_count,
        ]);
    }
}
