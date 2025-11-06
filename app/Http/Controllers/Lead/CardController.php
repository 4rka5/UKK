<?php

namespace App\Http\Controllers\Lead;

use App\Http\Controllers\Controller;
use App\Models\ManagementProjectCard as Card;
use App\Models\ManagementProjectBoard as Board;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CardController extends Controller
{
    public function index(Request $request)
    {
        $leadId = Auth::id();
        $query = Card::with('board.project')
            ->whereHas('board.project', fn($q) => $q->where('created_by', $leadId));

        if ($request->filled('board_id')) {
            $query->where('board_id', $request->integer('board_id'));
        }

        $cards = $query->orderByDesc('id')->paginate(10)->withQueryString();

        $boards = Board::with('project')
            ->whereHas('project', fn($q) => $q->where('created_by', $leadId))
            ->orderBy('board_name')
            ->get();

        $statuses = ['backlog','todo','in_progress','code_review','testing','done'];

        return view('lead.cards.index', compact('cards','boards','statuses'));
    }

    public function create()
    {
        $boards = Board::with('project')
            ->whereHas('project', fn($q) => $q->where('created_by', Auth::id()))
            ->orderByDesc('id')->get();
        $priorities = ['low','medium','high'];
        $statuses = ['backlog','todo','in_progress','code_review','testing','done'];
        return view('lead.cards.create', compact('boards','priorities','statuses'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'board_id' => ['required','exists:management_project_boards,id'],
            'card_title' => ['required','string','max:200'],
            'description' => ['nullable','string'],
            'due_date' => ['nullable','date'],
            'status' => ['required','in:backlog,todo,in_progress,code_review,testing,done'],
            'priority' => ['required','in:low,medium,high'],
            'estimated_hours' => ['nullable','numeric','min:0'],
        ]);
        $data['created_by'] = Auth::id();
        Card::create($data);
        return redirect()->route('lead.cards.index')->with('status','Card dibuat.');
    }

    public function edit(Card $card)
    {
        $this->authorizeCard($card);
        $boards = Board::with('project')
            ->whereHas('project', fn($q) => $q->where('created_by', Auth::id()))
            ->orderByDesc('id')->get();
        $priorities = ['low','medium','high'];
        $statuses = ['backlog','todo','in_progress','code_review','testing','done'];
        return view('lead.cards.edit', compact('card','boards','priorities','statuses'));
    }

    public function update(Request $request, Card $card)
    {
        $this->authorizeCard($card);
        $data = $request->validate([
            'board_id' => ['required','exists:management_project_boards,id'],
            'card_title' => ['required','string','max:200'],
            'description' => ['nullable','string'],
            'due_date' => ['nullable','date'],
            'status' => ['required','in:backlog,todo,in_progress,code_review,testing,done'],
            'priority' => ['required','in:low,medium,high'],
            'estimated_hours' => ['nullable','numeric','min:0'],
            'actual_hours' => ['nullable','numeric','min:0'],
        ]);
        $card->update($data);
        return redirect()->route('lead.cards.index')->with('status','Card diperbarui.');
    }

    public function destroy(Card $card)
    {
        $this->authorizeCard($card);
        $card->delete();
        return back()->with('status','Card dihapus.');
    }

    public function move(Request $request, Card $card)
    {
        $this->authorizeCard($card);
        $request->validate([
            'status' => ['required','in:backlog,todo,in_progress,code_review,testing,done']
        ]);
        $card->update(['status' => $request->input('status')]);
        return back()->with('status','Status card diperbarui.');
    }

    protected function authorizeCard(Card $card): void
    {
        if (!$card->board || !$card->board->project || $card->board->project->created_by !== Auth::id()) {
            abort(403);
        }
    }
}

