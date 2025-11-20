<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; margin: 20px; }
        .header { text-align: center; margin-bottom: 30px; border-bottom: 3px solid #333; padding-bottom: 15px; }
        .header h1 { margin: 0; font-size: 24px; color: #333; }
        .header .info { margin-top: 8px; color: #666; }
        .stats-grid { display: table; width: 100%; margin: 20px 0; }
        .stat-box { display: table-cell; padding: 15px; text-align: center; border: 2px solid #ddd; }
        .stat-box .number { font-size: 32px; font-weight: bold; color: #667eea; }
        .stat-box .label { font-size: 11px; color: #666; text-transform: uppercase; margin-top: 5px; }
        .section { margin: 30px 0; page-break-inside: avoid; }
        .section h3 { background: #667eea; color: white; padding: 10px; margin: 0 0 15px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table th { background: #f8f9fa; padding: 10px; text-align: left; border: 1px solid #ddd; font-weight: bold; font-size: 11px; }
        table td { padding: 8px; border: 1px solid #ddd; font-size: 11px; }
        .badge { padding: 3px 8px; border-radius: 3px; font-size: 10px; font-weight: bold; }
        .badge-active { background: #0d6efd; color: white; }
        .badge-done { background: #198754; color: white; }
        .badge-pending { background: #ffc107; color: #000; }
        .footer { margin-top: 40px; text-align: center; font-size: 10px; color: #999; border-top: 1px solid #ddd; padding-top: 10px; }
        .no-print { text-align: center; margin: 20px 0; }
        @media print {
            .no-print { display: none; }
            body { margin: 0; }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <button onclick="window.print()" style="padding: 10px 20px; background: #667eea; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 14px; margin-bottom: 20px;">
            🖨️ Print / Save as PDF
        </button>
        <a href="{{ route('admin.reports.index') }}" style="padding: 10px 20px; background: #6c757d; color: white; border: none; border-radius: 5px; text-decoration: none; display: inline-block; font-size: 14px; margin-bottom: 20px; margin-left: 10px;">
            ❌ Kembali
        </a>
    </div>

    <div class="header">
        <h1>{{ $title }}</h1>
        <div class="info">
            <strong>Tanggal:</strong> {{ $date }}<br>
            <strong>Periode:</strong> {{ $period }}
        </div>
    </div>

    <!-- Summary Stats -->
    <div class="stats-grid">
        <div class="stat-box">
            <div class="number">{{ $totalProjects }}</div>
            <div class="label">Total Projects</div>
        </div>
        <div class="stat-box">
            <div class="number">{{ $projectsByStatus['active'] }}</div>
            <div class="label">Active</div>
        </div>
        <div class="stat-box">
            <div class="number">{{ $projectsByStatus['done'] }}</div>
            <div class="label">Done</div>
        </div>
    </div>

    <!-- Project List -->
    <div class="section">
        <h3>Daftar Project</h3>
        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 25%;">Nama Project</th>
                    <th style="width: 15%;">Team Lead</th>
                    <th style="width: 10%; text-align: center;">Members</th>
                    <th style="width: 12%; text-align: center;">Status</th>
                    <th style="width: 12%;">Deadline</th>
                    <th style="width: 12%;">Dibuat</th>
                    <th style="width: 9%;">Reviewed</th>
                </tr>
            </thead>
            <tbody>
                @forelse($projects as $index => $project)
                    <tr>
                        <td style="text-align: center;">{{ $index + 1 }}</td>
                        <td><strong>{{ $project->project_name }}</strong></td>
                        <td>{{ $project->owner->fullname ?? $project->owner->username }}</td>
                        <td style="text-align: center;">{{ $project->members->count() }}</td>
                        <td style="text-align: center;">
                            @if($project->status === 'active')
                                <span class="badge badge-active">Aktif</span>
                            @elseif($project->status === 'done')
                                @if($project->reviewed_by)
                                    <span class="badge badge-done">Disetujui</span>
                                @else
                                    <span class="badge badge-pending">Review</span>
                                @endif
                            @endif
                        </td>
                        <td>{{ $project->deadline ? $project->deadline->format('d/m/Y') : '-' }}</td>
                        <td>{{ $project->created_at->format('d/m/Y') }}</td>
                        <td>
                            @if($project->reviewed_by)
                                {{ $project->reviewer->fullname ?? 'Admin' }}
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center; color: #999;">Tidak ada data project</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Statistics by Status -->
    <div class="section">
        <h3>Statistik Berdasarkan Status</h3>
        <table>
            <thead>
                <tr>
                    <th>Status</th>
                    <th style="text-align: center;">Jumlah</th>
                    <th style="text-align: center;">Persentase</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Active</td>
                    <td style="text-align: center;"><strong>{{ $projectsByStatus['active'] }}</strong></td>
                    <td style="text-align: center;">{{ $totalProjects > 0 ? round(($projectsByStatus['active'] / $totalProjects) * 100, 1) : 0 }}%</td>
                </tr>
                <tr>
                    <td>Done</td>
                    <td style="text-align: center;"><strong>{{ $projectsByStatus['done'] }}</strong></td>
                    <td style="text-align: center;">{{ $totalProjects > 0 ? round(($projectsByStatus['done'] / $totalProjects) * 100, 1) : 0 }}%</td>
                </tr>
                <tr style="background: #f8f9fa; font-weight: bold;">
                    <td>TOTAL</td>
                    <td style="text-align: center;">{{ $totalProjects }}</td>
                    <td style="text-align: center;">100%</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="footer">
        Laporan ini dibuat secara otomatis oleh sistem<br>
        © {{ now()->year }} Project Management System
    </div>
</body>
</html>
