<?php

namespace App\Http\Controllers;

use App\Models\AiConversation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class HelpdeskController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /** Liste des conversations */
    public function index(): View
    {
        return view('helpdesk.index', [
            'conversations' => AiConversation::with('lesson')
                ->where('user_id', Auth::id())
                ->latest()
                ->paginate(15),
        ]);
    }

    /** Une conversation */
    public function show(AiConversation $conversation): View
    {
        abort_if($conversation->user_id !== Auth::id(), 403);

        $conversation->load(['messages' => fn($q) => $q->oldest(), 'lesson']);

        return view('helpdesk.show', compact('conversation'));
    }
}
