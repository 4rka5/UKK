<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\ProjectMember;

class CheckProjectMember
{
    /**
     * Handle an incoming request.
     * Middleware untuk memastikan user adalah member dari project
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        
        if (!$user) {
            return redirect('/login');
        }

        // Admin bisa akses semua
        if ($user->role === 'admin') {
            return $next($request);
        }

        // Ambil project_id dari route parameter
        $projectId = $request->route('project') ?? $request->route('projectId');
        
        if ($projectId) {
            // Cek apakah user adalah member dari project
            $isMember = ProjectMember::where('project_id', $projectId)
                ->where('user_id', $user->id)
                ->exists();
                
            if (!$isMember) {
                abort(403, 'Anda bukan anggota project ini');
            }
        }

        return $next($request);
    }
}
