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
        table th { background: #f8f9fa; padding: 10px; text-align: left; border: 1px solid #ddd; font-weight: bold; }
        table td { padding: 8px; border: 1px solid #ddd; }
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

    <!-- Overview Stats -->
    <div class="stats-grid">
        <div class="stat-box">
            <div class="number">{{ $users }}</div>
            <div class="label">Total Users</div>
        </div>
        <div class="stat-box">
            <div class="number">{{ $projects }}</div>
            <div class="label">Total Projects</div>
        </div>
        <div class="stat-box">
            <div class="number">{{ $cards }}</div>
            <div class="label">Total Cards</div>
        </div>
    </div>

    <!-- Users by Role -->
    <div class="section">
        <h3>Distribusi User Berdasarkan Role</h3>
        <table>
            <thead>
                <tr>
                    <th>Role</th>
                    <th style="text-align: center;">Jumlah</th>
                    <th style="text-align: center;">Persentase</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Admin</td>
                    <td style="text-align: center;"><strong>{{ $usersByRole['admin'] }}</strong></td>
                    <td style="text-align: center;">{{ $users > 0 ? round(($usersByRole['admin'] / $users) * 100, 1) : 0 }}%</td>
                </tr>
                <tr>
                    <td>Team Lead</td>
                    <td style="text-align: center;"><strong>{{ $usersByRole['team_lead'] }}</strong></td>
                    <td style="text-align: center;">{{ $users > 0 ? round(($usersByRole['team_lead'] / $users) * 100, 1) : 0 }}%</td>
                </tr>
                <tr>
                    <td>Developer</td>
                    <td style="text-align: center;"><strong>{{ $usersByRole['developer'] }}</strong></td>
                    <td style="text-align: center;">{{ $users > 0 ? round(($usersByRole['developer'] / $users) * 100, 1) : 0 }}%</td>
                </tr>
                <tr>
                    <td>Designer</td>
                    <td style="text-align: center;"><strong>{{ $usersByRole['designer'] }}</strong></td>
                    <td style="text-align: center;">{{ $users > 0 ? round(($usersByRole['designer'] / $users) * 100, 1) : 0 }}%</td>
                </tr>
                <tr style="background: #f8f9fa; font-weight: bold;">
                    <td>TOTAL</td>
                    <td style="text-align: center;">{{ $users }}</td>
                    <td style="text-align: center;">100%</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Cards by Status -->
    <div class="section">
        <h3>Distribusi Card Berdasarkan Status</h3>
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
                    <td style="text-align: center;"><strong>{{ $cardsByStatus['todo'] }}</strong></td>
                    <td style="text-align: center;">{{ $cards > 0 ? round(($cardsByStatus['todo'] / $cards) * 100, 1) : 0 }}%</td>
                </tr>
                <tr>
                    <td>In Progress</td>
                    <td style="text-align: center;"><strong>{{ $cardsByStatus['in_progress'] }}</strong></td>
                    <td style="text-align: center;">{{ $cards > 0 ? round(($cardsByStatus['in_progress'] / $cards) * 100, 1) : 0 }}%</td>
                </tr>
                <tr>
                    <td>Review</td>
                    <td style="text-align: center;"><strong>{{ $cardsByStatus['review'] }}</strong></td>
                    <td style="text-align: center;">{{ $cards > 0 ? round(($cardsByStatus['review'] / $cards) * 100, 1) : 0 }}%</td>
                </tr>
                <tr>
                    <td>Done</td>
                    <td style="text-align: center;"><strong>{{ $cardsByStatus['done'] }}</strong></td>
                    <td style="text-align: center;">{{ $cards > 0 ? round(($cardsByStatus['done'] / $cards) * 100, 1) : 0 }}%</td>
                </tr>
                <tr style="background: #f8f9fa; font-weight: bold;">
                    <td>TOTAL</td>
                    <td style="text-align: center;">{{ $cards }}</td>
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
