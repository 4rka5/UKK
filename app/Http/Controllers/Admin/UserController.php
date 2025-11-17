<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        // Search by name, username, or email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('fullname', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by role
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Filter by task status
        if ($request->filled('has_tasks')) {
            if ($request->has_tasks === '1') {
                // Users with tasks
                $query->where(function($q) {
                    $q->whereHas('assignedCards', function($cardQuery) {
                        $cardQuery->where('status', '!=', 'done');
                    })
                    ->orWhereHas('projectMemberships')
                    ->orWhere('role', 'admin');
                });
            } else {
                // Users without tasks (available)
                $query->whereDoesntHave('assignedCards', function($cardQuery) {
                    $cardQuery->where('status', '!=', 'done');
                })
                ->whereDoesntHave('projectMemberships')
                ->where('role', '!=', 'admin');
            }
        }

        $users = $query->orderBy('id', 'desc')->paginate(10)->withQueryString();
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $roles = ['admin','team_lead','designer','developer'];
        return view('admin.users.create', compact('roles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'fullname' => ['nullable','string','max:100'],
            'username' => ['required','alpha_dash','min:3','max:30','unique:users,username'],
            'email'    => ['required','email','max:190','unique:users,email'],
            'password' => ['required','string','min:6'],
            'role'     => ['required','in:admin,team_lead,designer,developer'],
            'status'   => ['nullable','string','max:20'],
        ]);
        $data['password'] = Hash::make($data['password']);
        User::create($data);
        return redirect()->route('admin.users.index')->with('status','User dibuat.');
    }

    public function edit(User $user)
    {
        $roles = ['admin','team_lead','designer','developer'];
        return view('admin.users.edit', compact('user','roles'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'fullname' => ['nullable','string','max:100'],
            'username' => ['required','alpha_dash','min:3','max:30','unique:users,username,'.$user->id],
            'email'    => ['required','email','max:190','unique:users,email,'.$user->id],
            'password' => ['nullable','string','min:6'],
            'role'     => ['required','in:admin,team_lead,designer,developer'],
            'status'   => ['nullable','string','max:20'],
        ]);
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }
        $user->update($data);
        return redirect()->route('admin.users.index')->with('status','User diperbarui.');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return back()->with('status','User dihapus.');
    }
}
