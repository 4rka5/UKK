<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\User;
use App\Models\ManagementProjectCard;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index()
    {
        return view('admin.reports.index');
    }
    
    public function generate(Request $request)
    {
        $request->validate([
            'report_type' => 'required|in:users,projects,cards,summary',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'format' => 'required|in:pdf,excel'
        ]);
        
        $reportType = $request->report_type;
        $startDate = $request->start_date;
        $endDate = $request->end_date;
        $format = $request->format;
        
        switch ($reportType) {
            case 'users':
                return $this->generateUserReport($startDate, $endDate, $format);
            case 'projects':
                return $this->generateProjectReport($startDate, $endDate, $format);
            case 'cards':
                return $this->generateCardReport($startDate, $endDate, $format);
            case 'summary':
                return $this->generateSummaryReport($startDate, $endDate, $format);
            default:
                return back()->with('error', 'Tipe laporan tidak valid');
        }
    }
    
    private function generateUserReport($startDate, $endDate, $format)
    {
        $query = User::query();
        
        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }
        
        $users = $query->orderBy('role')->orderBy('fullname')->get();
        
        $data = [
            'title' => 'Laporan Data User',
            'date' => now()->format('d/m/Y'),
            'period' => $startDate && $endDate ? "$startDate sampai $endDate" : "Semua Data",
            'users' => $users,
            'totalUsers' => $users->count(),
            'byRole' => [
                'admin' => $users->where('role', 'admin')->count(),
                'team_lead' => $users->where('role', 'team_lead')->count(),
                'developer' => $users->where('role', 'developer')->count(),
                'designer' => $users->where('role', 'designer')->count(),
            ]
        ];
        
        if ($format === 'pdf') {
            // Return view yang bisa di-print sebagai PDF
            return view('admin.reports.users-pdf', $data);
        } else {
            return back()->with('error', 'Format Excel belum tersedia');
        }
    }
    
    private function generateProjectReport($startDate, $endDate, $format)
    {
        $query = Project::with(['owner', 'members', 'reviewer']);
        
        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }
        
        $projects = $query->orderBy('created_at', 'desc')->get();
        
        $data = [
            'title' => 'Laporan Data Project',
            'date' => now()->format('d/m/Y'),
            'period' => $startDate && $endDate ? "$startDate sampai $endDate" : "Semua Data",
            'projects' => $projects,
            'totalProjects' => $projects->count(),
            'projectsByStatus' => [
                'active' => $projects->where('status', 'active')->count(),
                'done' => $projects->where('status', 'done')->count(),
            ]
        ];
        
        if ($format === 'pdf') {
            return view('admin.reports.projects-pdf', $data);
        } else {
            return back()->with('error', 'Format Excel belum tersedia');
        }
    }
    
    private function generateCardReport($startDate, $endDate, $format)
    {
        $query = ManagementProjectCard::with(['project', 'creator', 'assignees']);
        
        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }
        
        $cards = $query->orderBy('created_at', 'desc')->get();
        
        $data = [
            'title' => 'Laporan Data Card/Tugas',
            'date' => now()->format('d/m/Y'),
            'period' => $startDate && $endDate ? "$startDate sampai $endDate" : "Semua Data",
            'cards' => $cards,
            'totalCards' => $cards->count(),
            'byStatus' => [
                'todo' => $cards->where('status', 'todo')->count(),
                'in_progress' => $cards->where('status', 'in_progress')->count(),
                'review' => $cards->where('status', 'review')->count(),
                'done' => $cards->where('status', 'done')->count(),
            ],
            'byPriority' => [
                'low' => $cards->where('priority', 'low')->count(),
                'medium' => $cards->where('priority', 'medium')->count(),
                'high' => $cards->where('priority', 'high')->count(),
            ]
        ];
        
        if ($format === 'pdf') {
            return view('admin.reports.cards-pdf', $data);
        } else {
            return back()->with('error', 'Format Excel belum tersedia');
        }
    }
    
    private function generateSummaryReport($startDate, $endDate, $format)
    {
        $data = [
            'title' => 'Laporan Summary Sistem',
            'date' => now()->format('d/m/Y'),
            'period' => $startDate && $endDate ? "$startDate sampai $endDate" : "Semua Data",
            'users' => User::count(),
            'projects' => Project::count(),
            'cards' => ManagementProjectCard::count(),
            'usersByRole' => [
                'admin' => User::where('role', 'admin')->count(),
                'team_lead' => User::where('role', 'team_lead')->count(),
                'developer' => User::where('role', 'developer')->count(),
                'designer' => User::where('role', 'designer')->count(),
            ],
            'cardsByStatus' => [
                'todo' => ManagementProjectCard::where('status', 'todo')->count(),
                'in_progress' => ManagementProjectCard::where('status', 'in_progress')->count(),
                'review' => ManagementProjectCard::where('status', 'review')->count(),
                'done' => ManagementProjectCard::where('status', 'done')->count(),
            ]
        ];
        
        if ($format === 'pdf') {
            // Return view yang bisa di-print sebagai PDF menggunakan browser print
            return view('admin.reports.summary-pdf', $data);
        }
        
        return back()->with('error', 'Format Excel belum tersedia untuk laporan summary');
    }
}
