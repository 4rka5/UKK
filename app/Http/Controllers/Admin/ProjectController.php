<?php


namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index()
    {
        $projects = Project::with('owner')->orderBy('id','desc')->paginate(10);
        return view('admin.projects.index', compact('projects'));
    }

    public function create()
    {
        $owners = User::orderBy('fullname')->get();
        return view('admin.projects.create', compact('owners'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'project_name' => ['required','string','max:150'],
            'description'  => ['nullable','string'],
            'deadline'     => ['nullable','date'],
            'created_by'   => ['nullable','exists:users,id'],
        ]);
        if (empty($data['created_by'])) $data['created_by'] = auth()->id();
        Project::create($data);
        return redirect()->route('admin.projects.index')->with('status','Project dibuat.');
    }

    public function edit(Project $project)
    {
        $owners = User::orderBy('fullname')->get();
        return view('admin.projects.edit', compact('project','owners'));
    }

    public function update(Request $request, Project $project)
    {
        $data = $request->validate([
            'project_name' => ['required','string','max:150'],
            'description'  => ['nullable','string'],
            'deadline'     => ['nullable','date'],
            'created_by'   => ['nullable','exists:users,id'],
        ]);
        $project->update($data);
        return redirect()->route('admin.projects.index')->with('status','Project diperbarui.');
    }

    public function destroy(Project $project)
    {
        $project->delete();
        return back()->with('status','Project dihapus.');
    }
}
