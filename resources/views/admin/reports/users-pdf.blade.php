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
        .badge-admin { background: #dc3545; color: white; }
        .badge-lead { background: #0d6efd; color: white; }
        .badge-developer { background: #198754; color: white; }
        .badge-designer { background: #fd7e14; color: white; }
        .badge-idle { background: #6c757d; color: white; }
        .badge-working { background: #0dcaf0; color: white; }
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
            <div class="number">{{ $totalUsers }}</div>
            <div class="label">Total Users</div>
        </div>
        <div class="stat-box">
            <div class="number">{{ $byRole['admin'] }}</div>
            <div class="label">Admin</div>
        </div>
        <div class="stat-box">
            <div class="number">{{ $byRole['team_lead'] }}</div>
            <div class="label">Team Lead</div>
        </div>
        <div class="stat-box">
            <div class="number">{{ $byRole['developer'] }}</div>
            <div class="label">Developer</div>
        </div>
        <div class="stat-box">
            <div class="number">{{ $byRole['designer'] }}</div>
            <div class="label">Designer</div>
        </div>
    </div>

    <!-- User List -->
    <div class="section">
        <h3>Daftar User</h3>
        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 20%;">Nama Lengkap</th>
                    <th style="width: 15%;">Username</th>
                    <th style="width: 20%;">Email</th>
                    <th style="width: 12%; text-align: center;">Role</th>
                    <th style="width: 12%; text-align: center;">Status</th>
                    <th style="width: 16%;">Terdaftar</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $index => $user)
                    <tr>
                        <td style="text-align: center;">{{ $index + 1 }}</td>
                        <td>{{ $user->fullname ?: '-' }}</td>
                        <td>{{ $user->username }}</td>
                        <td>{{ $user->email ?: '-' }}</td>
                        <td style="text-align: center;">
                            <span class="badge badge-{{ $user->role }}">{{ ucfirst(str_replace('_', ' ', $user->role)) }}</span>
                        </td>
                        <td style="text-align: center;">
                            <span class="badge badge-{{ $user->status }}">{{ ucfirst($user->status) }}</span>
                        </td>
                        <td>{{ $user->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; color: #999;">Tidak ada data user</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Statistics by Role -->
    <div class="section">
        <h3>Statistik Berdasarkan Role</h3>
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
                    <td style="text-align: center;"><strong>{{ $byRole['admin'] }}</strong></td>
                    <td style="text-align: center;">{{ $totalUsers > 0 ? round(($byRole['admin'] / $totalUsers) * 100, 1) : 0 }}%</td>
                </tr>
                <tr>
                    <td>Team Lead</td>
                    <td style="text-align: center;"><strong>{{ $byRole['team_lead'] }}</strong></td>
                    <td style="text-align: center;">{{ $totalUsers > 0 ? round(($byRole['team_lead'] / $totalUsers) * 100, 1) : 0 }}%</td>
                </tr>
                <tr>
                    <td>Developer</td>
                    <td style="text-align: center;"><strong>{{ $byRole['developer'] }}</strong></td>
                    <td style="text-align: center;">{{ $totalUsers > 0 ? round(($byRole['developer'] / $totalUsers) * 100, 1) : 0 }}%</td>
                </tr>
                <tr>
                    <td>Designer</td>
                    <td style="text-align: center;"><strong>{{ $byRole['designer'] }}</strong></td>
                    <td style="text-align: center;">{{ $totalUsers > 0 ? round(($byRole['designer'] / $totalUsers) * 100, 1) : 0 }}%</td>
                </tr>
                <tr style="background: #f8f9fa; font-weight: bold;">
                    <td>TOTAL</td>
                    <td style="text-align: center;">{{ $totalUsers }}</td>
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
