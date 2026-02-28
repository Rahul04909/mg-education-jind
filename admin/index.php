<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}

require_once __DIR__ . '/../database/db-config.php';
$conn = getDbConnection();

// Fetch Dashboard Statistics
$stats = [
    'students' => 0,
    'revenue' => 0,
    'courses' => 0,
    'internships' => 0,
    'enquiries' => 0,
    'volunteers' => 0
];

// Helper to get counts
function getCount($conn, $table) {
    // Check if table exists
    $check = $conn->query("SHOW TABLES LIKE '$table'");
    if (!$check || $check->num_rows == 0) return 0;
    
    $result = $conn->query("SELECT COUNT(*) as total FROM $table");
    return ($result && $result->num_rows > 0) ? $result->fetch_assoc()['total'] : 0;
}

$stats['students'] = getCount($conn, 'admissions');
$stats['courses'] = getCount($conn, 'courses');
$stats['internships'] = getCount($conn, 'internships');
$stats['volunteers'] = getCount($conn, 'volunteers');

// Enquiries count (Callback + Quick)
$callback_count = getCount($conn, 'callback_requests');
$quick_count = getCount($conn, 'quick_enquiries');
$stats['enquiries'] = $callback_count + $quick_count;

// Revenue Calculation
$check_payments = $conn->query("SHOW TABLES LIKE 'payment_logs'");
if ($check_payments && $check_payments->num_rows > 0) {
    $rev_res = $conn->query("SELECT SUM(amount) as total FROM payment_logs WHERE status = 'success'");
    $stats['revenue'] = ($rev_res) ? $rev_res->fetch_assoc()['total'] : 0;
} else {
    // Fallback: estimate based on admissions * average fee (e.g., 5000)
    $stats['revenue'] = $stats['students'] * 5000;
}

include __DIR__ . '/sidebar.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | MG Skill Administration</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary: #4f46e5;
            --primary-light: #818cf8;
            --primary-dark: #3730a3;
            --secondary: #0ea5e9;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --bg-main: #f8fafc;
            --surface: #ffffff;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --border: #e2e8f0;
            --glass: rgba(255, 255, 255, 0.7);
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
        }

        * { box-sizing: border-box; }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg-main);
            color: var(--text-main);
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        .admin-content {
            margin-left: 260px;
            padding: 32px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            min-height: 100vh;
        }

        body.sidebar-collapsed .admin-content {
            margin-left: 80px;
        }

        @media (max-width: 1024px) {
            .admin-content { margin-left: 80px; padding: 20px; }
        }

        /* Header Section */
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
        }

        .header-title h1 {
            font-size: 28px;
            font-weight: 700;
            margin: 0;
            color: var(--text-main);
            letter-spacing: -0.025em;
        }

        .header-title p {
            margin: 4px 0 0;
            color: var(--text-muted);
            font-size: 15px;
        }

        .header-actions {
            display: flex;
            gap: 12px;
            align-items: center;
        }

        .btn-quick-add {
            background: var(--primary);
            color: white;
            padding: 10px 24px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.2);
        }

        .btn-quick-add:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(79, 70, 229, 0.3);
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 24px;
            margin-bottom: 32px;
        }

        .stat-card {
            background: var(--surface);
            padding: 24px;
            border-radius: 20px;
            border: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            box-shadow: var(--shadow-sm);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
            border-color: var(--primary-light);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; width: 4px; height: 100%;
            background: var(--accent-color, var(--primary));
        }

        .stat-info h3 {
            font-size: 14px;
            font-weight: 600;
            color: var(--text-muted);
            margin: 0 0 8px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: var(--text-main);
            margin: 0;
            letter-spacing: -0.05em;
        }

        .stat-trend {
            font-size: 12px;
            font-weight: 600;
            margin-top: 8px;
            display: flex;
            align-items: center;
            gap: 4px;
        }

        .trend-up { color: var(--success); }
        .trend-down { color: var(--danger); }

        .stat-icon {
            width: 56px;
            height: 56px;
            border-radius: 16px;
            background: var(--accent-bg, rgba(79, 70, 229, 0.1));
            color: var(--accent-color, var(--primary));
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .stat-icon svg { width: 28px; height: 28px; }

        /* Main Grid */
        .main-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 24px;
        }

        @media (max-width: 1200px) {
            .main-grid { grid-template-columns: 1fr; }
        }

        .card {
            background: var(--surface);
            border-radius: 24px;
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
            padding: 24px;
            margin-bottom: 24px;
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }

        .card-title {
            font-size: 18px;
            font-weight: 700;
            margin: 0;
        }

        /* Table Styles */
        .table-responsive {
            width: 100%;
            overflow-x: auto;
        }

        .custom-table {
            width: 100%;
            border-collapse: collapse;
        }

        .custom-table th {
            text-align: left;
            padding: 12px 16px;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-muted);
            border-bottom: 1px solid var(--border);
            background: #fcfcfd;
        }

        .custom-table td {
            padding: 16px;
            font-size: 14px;
            border-bottom: 1px solid var(--border);
            vertical-align: middle;
        }

        .custom-table tr:hover { background: #f8fafc; }

        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .avatar {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            object-fit: cover;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-active { background: #ecfdf5; color: #059669; }
        .status-pending { background: #fffbeb; color: #d97706; }

        /* Chart Container */
        .chart-container {
            height: 350px;
            margin-top: 20px;
        }

        /* Quick Shortcuts */
        .shortcuts-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }

        .shortcut-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
            border-radius: 16px;
            background: #f8fafc;
            border: 1px solid var(--border);
            text-decoration: none;
            color: var(--text-main);
            transition: all 0.2s;
        }

        .shortcut-item:hover {
            background: var(--primary-light);
            color: white;
            border-color: var(--primary);
            transform: translateY(-2px);
        }

        .shortcut-item svg { width:24px; height:24px; margin-bottom: 8px; }
        .shortcut-item span { font-size: 13px; font-weight: 600; }

        .side-content {
            display: flex;
            flex-direction: column;
            gap: 24px;
        }

    </style>
</head>
<body>

<main class="admin-content">
    <header class="dashboard-header">
        <div class="header-title">
            <h1>Command Center</h1>
            <p>Welcome back, <?= htmlspecialchars($_SESSION['admin_name'] ?? 'Administrator') ?>. Here's what's happening today.</p>
        </div>
        
        <div class="header-actions">
            <a href="mg-students/add-student.php" class="btn-quick-add">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                New Admission
            </a>
        </div>
    </header>

    <div class="stats-grid">
        <!-- Students -->
        <div class="stat-card" style="--accent-color: var(--primary); --accent-bg: rgba(79, 70, 229, 0.1);">
            <div class="stat-info">
                <h3>Total Students</h3>
                <p class="stat-value"><?= number_format($stats['students']) ?></p>
                <div class="stat-trend trend-up">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline><polyline points="17 6 23 6 23 12"></polyline></svg>
                    Real-time
                </div>
            </div>
            <div class="stat-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
            </div>
        </div>

        <!-- Revenue -->
        <div class="stat-card" style="--accent-color: var(--success); --accent-bg: rgba(16, 185, 129, 0.1);">
            <div class="stat-info">
                <h3>Est. Revenue</h3>
                <p class="stat-value">₹<?= number_format($stats['revenue']/1000, 1) ?>K</p>
                <div class="stat-trend trend-up">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"></polyline><polyline points="17 6 23 6 23 12"></polyline></svg>
                    Live Data
                </div>
            </div>
            <div class="stat-icon" style="color: var(--success); background:rgba(16, 185, 129, 0.1);">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
            </div>
        </div>

        <!-- Courses -->
        <div class="stat-card" style="--accent-color: var(--secondary); --accent-bg: rgba(14, 165, 233, 0.1);">
            <div class="stat-info">
                <h3>Active Courses</h3>
                <p class="stat-value"><?= number_format($stats['courses']) ?></p>
                <div class="stat-trend" style="color:var(--secondary)">
                    Curriculum items
                </div>
            </div>
            <div class="stat-icon" style="color: var(--secondary); background:rgba(14, 165, 233, 0.1);">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path></svg>
            </div>
        </div>

        <!-- Enquiries -->
        <div class="stat-card" style="--accent-color: var(--warning); --accent-bg: rgba(245, 158, 11, 0.1);">
            <div class="stat-info">
                <h3>Enquiries</h3>
                <p class="stat-value"><?= number_format($stats['enquiries']) ?></p>
                <div class="stat-trend trend-down">
                    Pending response
                </div>
            </div>
            <div class="stat-icon" style="color: var(--warning); background:rgba(245, 158, 11, 0.1);">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
            </div>
        </div>
    </div>

    <div class="main-grid">
        <!-- Analytics Chart -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Enrollment Analytics</h2>
                <div class="card-actions">
                    <select style="padding: 8px 12px; border-radius: 8px; border: 1px solid var(--border); font-size: 13px;">
                        <option>This Year</option>
                        <option>Last Year</option>
                    </select>
                </div>
            </div>
            <div class="chart-container">
                <canvas id="analyticsChart"></canvas>
            </div>
        </div>

        <!-- Side Content -->
        <div class="side-content">
            <!-- Quick Shortcuts -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Quick Actions</h2>
                </div>
                <div class="shortcuts-grid">
                    <a href="courses/add-course.php" class="shortcut-item">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
                        <span>Add Course</span>
                    </a>
                    <a href="blogs/add-blog.php" class="shortcut-item">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 1 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                        <span>Write Blog</span>
                    </a>
                    <a href="centers/add-center.php" class="shortcut-item">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h18M5 21V7l8-4 8 4v14M8 21v-4h8v4" /></svg>
                        <span>New Center</span>
                    </a>
                    <a href="settings/smtp-settings.php" class="shortcut-item">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                        <span>Settings</span>
                    </a>
                </div>
            </div>

            <!-- Recent Activity Mini -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Live Updates</h2>
                </div>
                <div style="display:flex; flex-direction:column; gap:16px;">
                    <?php
                    // Fetch last 3 admissions
                    $recent = $conn->query("SELECT student_name, created_at FROM admissions ORDER BY id DESC LIMIT 3");
                    if ($recent && $recent->num_rows > 0):
                        while($row = $recent->fetch_assoc()):
                    ?>
                    <div style="display:flex; gap:12px; align-items:flex-start;">
                        <div style="width:8px; height:8px; border-radius:50%; background:var(--primary); margin-top:6px;"></div>
                        <div>
                            <p style="margin:0; font-size:14px; font-weight:600;"><?= htmlspecialchars($row['student_name']) ?></p>
                            <p style="margin:2px 0 0; font-size:12px; color:var(--text-muted);">Joined <?= date('M d, H:i', strtotime($row['created_at'])) ?></p>
                        </div>
                    </div>
                    <?php endwhile; else: ?>
                    <p style="color:var(--text-muted); font-size:13px; text-align:center;">No recent activity</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Admissions Detailed -->
    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Recent Student Admissions</h2>
            <a href="mg-students/" style="color:var(--primary); text-decoration:none; font-size:14px; font-weight:600;">View All Students</a>
        </div>
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Student Details</th>
                        <th>Course / Category</th>
                        <th>Center</th>
                        <th>Registration Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $full_recent = $conn->query("SELECT * FROM admissions ORDER BY id DESC LIMIT 5");
                    if ($full_recent && $full_recent->num_rows > 0):
                        while($row = $full_recent->fetch_assoc()):
                    ?>
                    <tr>
                        <td>
                            <div class="user-info">
                                <img src="https://ui-avatars.com/api/?name=<?= urlencode($row['student_name']) ?>&background=random" class="avatar">
                                <div>
                                    <div style="font-weight:600;"><?= htmlspecialchars($row['student_name']) ?></div>
                                    <div style="font-size:12px; color:var(--text-muted);"><?= htmlspecialchars($row['email'] ?? 'N/A') ?></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div style="font-weight:500;"><?= htmlspecialchars($row['course_name'] ?? 'N/A') ?></div>
                            <div style="font-size:12px; color:var(--text-muted);"><?= htmlspecialchars($row['category'] ?? 'N/A') ?></div>
                        </td>
                        <td><?= htmlspecialchars($row['center_name'] ?? 'Main Center') ?></td>
                        <td><?= date('d M, Y', strtotime($row['created_at'])) ?></td>
                        <td><span class="status-badge status-active">Enrolled</span></td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr><td colspan="5" style="text-align:center; padding:30px; color:var(--text-muted);">No admissions found in the system yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<script>
    // Premium Area Chart for Analytics
    const ctx = document.getElementById('analyticsChart').getContext('2d');
    
    // Create Gradient
    const gradient = ctx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, 'rgba(79, 70, 229, 0.2)');
    gradient.addColorStop(1, 'rgba(79, 70, 229, 0)');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            datasets: [{
                label: 'Admissions',
                data: [45, 59, 80, 81, 56, 55, 40, 72, 93, 120, 105, 140],
                borderColor: '#4f46e5',
                backgroundColor: gradient,
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#fff',
                pointBorderColor: '#4f46e5',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6,
                pointHoverBackgroundColor: '#4f46e5',
                pointHoverBorderColor: '#fff',
                pointHoverBorderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1e293b',
                    titleFont: { family: 'Outfit', size: 14, weight: '600' },
                    bodyFont: { family: 'Outfit', size: 13 },
                    padding: 12,
                    cornerRadius: 12,
                    displayColors: false,
                    callbacks: {
                        label: function(context) {
                            return ' ' + context.parsed.y + ' New Students';
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { font: { family: 'Outfit', size: 12 }, color: '#64748b' }
                },
                y: {
                    grid: { color: '#f1f5f9', borderDash: [5, 5] },
                    ticks: { font: { family: 'Outfit', size: 12 }, color: '#64748b' }
                }
            }
        }
    });
</script>

</body>
</html>
