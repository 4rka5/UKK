<?php
// app/Http/Controllers/ProjectController.php
namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ManagementProjectBoard;
use App\Models\ManagementProjectCard;
use App\Models\ProjectMember;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();

        // Projects where user is member or creator
        $projects = Project::whereHas('members', function($query) use ($user) {
            $query->where('user_id_0', $user->user_id_0);
        })->orWhere('created_by_#', $user->user_id_0)
          ->withCount(['boards', 'members'])
          ->get();

        // Recent activities
        $recentCards = ManagementProjectCard::whereHas('board.project.members', function($query) use ($user) {
            $query->where('user_id_0', $user->user_id_0);
        })->with(['board.project', 'createdBy'])
          ->orderBy('created_at', 'desc')
          ->limit(5)
          ->get();

        return view('projects.dashboard', compact('projects', 'recentCards'));
    }

    public function show($projectId)
    {
        $project = Project::with(['boards.cards' => function($query) {
            $query->with(['createdBy', 'assignments.user'])
                  ->orderBy('position');
        }])->findOrFail($projectId);

        // Check if user has access to this project
        $user = Auth::user();
        $isMember = $project->members()->where('user_id_0', $user->user_id_0)->exists();
        $isCreator = $project->created_by == $user->user_id_0;

        if (!$isMember && !$isCreator) {
            abort(403, 'Unauthorized access');
        }

        return view('projects.show', compact('project'));
    }

    public function storeBoard(Request $request, $projectId)
    {
        $request->validate([
            'board_name' => 'required|string|max:100',
            'description' => 'nullable|string',
        ]);

        $maxPosition = ManagementProjectBoard::where('project_id_#', $projectId)
                                 ->max('position') ?? 0;

        $board = ManagementProjectBoard::create([
            'project_id_#' => $projectId,
            'board_name' => $request->board_name,
            'description' => $request->description,
            'position' => $maxPosition + 1,
            'created_at' => now(),
        ]);

        return response()->json($board);
    }

    public function storeCard(Request $request, $boardId)
    {
        $request->validate([
            'card_title' => 'required|string|max:100',
            'description' => 'nullable|string',
            'priority' => 'required|in:low,medium,high',
            'estimated_hours' => 'nullable|numeric|min:0',
        ]);

        $maxPosition = ManagementProjectCard::where('board_id_#', $boardId)
                                ->max('position') ?? 0;

        $card = ManagementProjectCard::create([
            'board_id_#' => $boardId,
            'card_title' => $request->card_title,
            'description' => $request->description,
            'position' => $maxPosition + 1,
            'created_by_#' => Auth::id(),
            'priority' => $request->priority,
            'estimated_hours' => $request->estimated_hours,
            'status' => 'todo',
            'created_at' => now(),
        ]);

        return response()->json($card->load('createdBy'));
    }

    public function updateCardPosition(Request $request)
    {
        $request->validate([
            'cards' => 'required|array',
            'cards.*.card_id_0' => 'required|exists:management_project_cards,card_id_0',
            'cards.*.position' => 'required|integer',
            'cards.*.board_id_#' => 'required|exists:management_project_bounds,board_id_0',
        ]);

        foreach ($request->cards as $cardData) {
            ManagementProjectCard::where('card_id_0', $cardData['card_id_0'])
                      ->update([
                          'position' => $cardData['position'],
                          'board_id_#' => $cardData['board_id_#'],
                      ]);
        }

        return response()->json(['message' => 'Posisi kartu berhasil diupdate']);
    }
}
