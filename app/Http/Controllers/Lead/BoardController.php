<?php

namespace App\Http\Controllers\Lead;

use App\Http\Controllers\Controller;
use App\Models\ManagementProjectBoard as Board;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BoardController extends Controller
{
    public function index()
    {
        $leadId = Auth::id();
        $boards = Board::with('project')
            ->whereHas('project', fn($q) => $q->where('created_by', $leadId))
            ->orderByDesc('id')
            ->paginate(10);
        return view('lead.boards.index', compact('boards'));
    }

    public function create()
    {
        $projects = Project::where('created_by', Auth::id())->orderBy('project_name')->get();
        return view('lead.boards.create', compact('projects'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'project_id' => ['required','exists:projects,id'],
            'board_name' => ['required','string','max:150'],
            'description' => ['nullable','string'],
        ]);
        Board::create($data);
        return redirect()->to('/lead')->with('status','Board dibuat.');
    }

    public function edit(Board $board)
    {
        $this->authorizeBoard($board);
        $projects = Project::where('created_by', Auth::id())->orderBy('project_name')->get();
        return view('lead.boards.edit', compact('board','projects'));
    }

    public function update(Request $request, Board $board)
    {
        $this->authorizeBoard($board);
        $data = $request->validate([
            'project_id' => ['required','exists:projects,id'],
            'board_name' => ['required','string','max:150'],
            'description' => ['nullable','string'],
        ]);
        $board->update($data);
        return redirect()->route('lead.boards.index')->with('status','Board diperbarui.');
    }

    public function destroy(Board $board)
    {
        $this->authorizeBoard($board);
        $board->delete();
        return back()->with('status','Board dihapus.');
    }

    protected function authorizeBoard(Board $board): void
    {
        if (!$board->project || $board->project->created_by !== Auth::id()) {
            abort(403);
        }
    }
}

