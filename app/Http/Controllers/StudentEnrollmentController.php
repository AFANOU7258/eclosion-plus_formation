<?php

namespace App\Http\Controllers;

use App\Models\Enrollment;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class StudentEnrollmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /** Mes inscriptions */
    public function index(): View
    {
        return view('enrollments.index', [
            'enrollments' => Enrollment::with('course')
                ->where('user_id', Auth::id())
                ->latest()
                ->paginate(10),
        ]);
    }
}
