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
        .stat-box .number { font-size: 28px; font-weight: bold; color: #667eea; }
        .stat-box .label { font-size: 10px; color: #666; text-transform: uppercase; margin-top: 5px; }
        .section { margin: 30px 0; page-break-inside: avoid; }
        .section h3 { background: #667eea; color: white; padding: 10px; margin: 0 0 15px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table th { background: #f8f9fa; padding: 10px; text-align: left; border: 1px solid #ddd; font-weight: bold; font-size: 11px; }
        table td { padding: 8px; border: 1px solid #ddd; font-size: 11px; }
        .badge { padding: 3px 8px; border-radius: 3px; font-size: 10px; font-weight: bold; }
        .badge-todo { background: #6c757d; color: white; }
        .badge-in-progress { background: #0d6efd; color: white; }
        .badge-review { background: #ffc107; color: #000; }
        .badge-done { background: #198754; color: white; }
        .badge-low { background: #d1ecf1; color: #0c5460; }
        .badge-medium { background: #fff3cd; color: #856404; }
        .badge-high { background: #f8d7da; color: #721c24; }
        .footer { margin-top: 40px; text-align: center; font-size: 10px; color: #999; border-top: 1px solid #ddd; padding-top: 10px; }
        .no-print { text-align: center; margin: 20px 0; }
        .assignees { font-size: 10px; color: #666; }
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
            <div class="number">{{ $totalCards }}</div>
            <div class="label">Total Cards</div>
        </div>
        <div class="stat-box">
            <div class="number">{{ $byStatus['todo'] ?? 0 }}</div>
            <div class="label">To Do</div>
        </div>
        <div class="stat-box">
            <div class="number">{{ $byStatus['in_progress'] ?? 0 }}</div>
            <div class="label">In Progress</div>
        </div>
        <div class="stat-box">
            <div class="number">{{ $byStatus['review'] ?? 0 }}</div>
            <div class="label">Review</div>
        </div>
        <div class="stat-box">
            <div class="number">{{ $byStatus['done'] ?? 0 }}</div>
            <div class="label">Done</div>
        </div>
    </div>

    <!-- Card List -->
    <div class="section">
        <h3>Daftar Cards/Tasks</h3>
        <table>
            <thead>
                <tr>
                    <th style="width: 4%;">No</th>
                    <th style="width: 25%;">Judul Card</th>
                    <th style="width: 15%;">Project</th>
                    <th style="width: 12%;">Creator</th>
                    <th style="width: 10%; text-align: center;">Status</th>
                    <th style="width: 9%; text-align: center;">Priority</th>
                    <th style="width: 10%;">Due Date</th>
                    <th style="width: 15%;">Assignees</th>
                </tr>
            </thead>
            <tbody>
                @forelse($cards as $index => $card)
                    <tr>
                        <td style="text-align: center;">{{ $index + 1 }}</td>
                        <td><strong>{{ $card->card_title }}</strong></td>
                        <td>{{ $card->project->project_name ?? '-' }}</td>
                        <td>{{ $card->creator->fullname ?? $card->creator->username }}</td>
                        <td style="text-align: center;">
                            @if($card->status === 'todo')
                                <span class="badge badge-todo">To Do</span>
                            @elseif($card->status === 'in_progress')
                                <span class="badge badge-in-progress">In Progress</span>
                            @elseif($card->status === 'review')
                                <span class="badge badge-review">Review</span>
                            @elseif($card->status === 'done')
                                <span class="badge badge-done">Done</span>
                            @endif
                        </td>
                        <td style="text-align: center;">
                            @if($card->priority === 'low')
                                <span class="badge badge-low">Low</span>
                            @elseif($card->priority === 'medium')
                                <span class="badge badge-medium">Medium</span>
                            @elseif($card->priority === 'high')
                                <span class="badge badge-high">High</span>
                            @endif
                        </td>
                        <td>{{ $card->due_date ? \Carbon\Carbon::parse($card->due_date)->format('d/m/Y') : '-' }}</td>
                        <td>
                            <div class="assignees">
                                @if($card->assignees && $card->assignees->count() > 0)
                                    @foreach($card->assignees as $assignee)
                                        • {{ $assignee->fullname ?? $assignee->username }}<br>
                                    @endforeach
                                @else
                                    <span style="color: #999;">Belum ada</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center; color: #999;">Tidak ada data card</td>
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
                    <td>To Do</td>
                    <td style="text-align: center;"><strong>{{ $byStatus['todo'] ?? 0 }}</strong></td>
                    <td style="text-align: center;">{{ $totalCards > 0 ? round((($byStatus['todo'] ?? 0) / $totalCards) * 100, 1) : 0 }}%</td>
                </tr>
                <tr>
                    <td>In Progress</td>
                    <td style="text-align: center;"><strong>{{ $byStatus['in_progress'] ?? 0 }}</strong></td>
                    <td style="text-align: center;">{{ $totalCards > 0 ? round((($byStatus['in_progress'] ?? 0) / $totalCards) * 100, 1) : 0 }}%</td>
                </tr>
                <tr>
                    <td>Review</td>
                    <td style="text-align: center;"><strong>{{ $byStatus['review'] ?? 0 }}</strong></td>
                    <td style="text-align: center;">{{ $totalCards > 0 ? round((($byStatus['review'] ?? 0) / $totalCards) * 100, 1) : 0 }}%</td>
                </tr>
                <tr>
                    <td>Done</td>
                    <td style="text-align: center;"><strong>{{ $byStatus['done'] ?? 0 }}</strong></td>
                    <td style="text-align: center;">{{ $totalCards > 0 ? round((($byStatus['done'] ?? 0) / $totalCards) * 100, 1) : 0 }}%</td>
                </tr>
                <tr style="background: #f8f9fa; font-weight: bold;">
                    <td>TOTAL</td>
                    <td style="text-align: center;">{{ $totalCards }}</td>
                    <td style="text-align: center;">100%</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Statistics by Priority -->
    <div class="section">
        <h3>Statistik Berdasarkan Priority</h3>
        <table>
            <thead>
                <tr>
                    <th>Priority</th>
                    <th style="text-align: center;">Jumlah</th>
                    <th style="text-align: center;">Persentase</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Low</td>
                    <td style="text-align: center;"><strong>{{ $byPriority['low'] ?? 0 }}</strong></td>
                    <td style="text-align: center;">{{ $totalCards > 0 ? round((($byPriority['low'] ?? 0) / $totalCards) * 100, 1) : 0 }}%</td>
                </tr>
                <tr>
                    <td>Medium</td>
                    <td style="text-align: center;"><strong>{{ $byPriority['medium'] ?? 0 }}</strong></td>
                    <td style="text-align: center;">{{ $totalCards > 0 ? round((($byPriority['medium'] ?? 0) / $totalCards) * 100, 1) : 0 }}%</td>
                </tr>
                <tr>
                    <td>High</td>
                    <td style="text-align: center;"><strong>{{ $byPriority['high'] ?? 0 }}</strong></td>
                    <td style="text-align: center;">{{ $totalCards > 0 ? round((($byPriority['high'] ?? 0) / $totalCards) * 100, 1) : 0 }}%</td>
                </tr>
                <tr style="background: #f8f9fa; font-weight: bold;">
                    <td>TOTAL</td>
                    <td style="text-align: center;">{{ $totalCards }}</td>
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
