<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}
include __DIR__ . '/sidebar.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard &lsaquo; MG Skill &#8212; WordPress</title>
    <!-- Use system fonts like WP -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* :root and body are handled globally in sidebar.php for Dark Theme */

        /* Layout */
        .admin-content {
            margin-left: var(--sidebar-w);
            min-height: 100vh;
            padding: 10px 20px 0 20px;
            box-sizing: border-box;
            transition: margin-left 0.3s ease;
        }
        
        body.sidebar-collapsed .admin-content {
            margin-left: var(--sidebar-collapsed-w);
        }

        @media (max-width: 900px) {
            .admin-content { margin-left: var(--sidebar-collapsed-w); padding: 10px; }
        }

        /* Top Header */
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-top: 10px;
        }
        
        .page-title h1 {
            font-size: 23px;
            font-weight: 400;
            margin: 0;
            color: var(--text-main);
            padding: 9px 0 4px 0;
            line-height: 1.3;
        }
        .page-title p { display: none; }

        .top-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Screen Options / Search */
        .search-box input {
            padding: 0 8px;
            line-height: 2;
            min-height: 30px;
            box-shadow: none;
            border-radius: 4px;
            border: 1px solid var(--border-color);
            background-color: var(--bg-input);
            color: var(--text-main);
            font-size: 13px;
            width: 200px;
            transition: all 0.2s;
        }
        .search-box input:focus {
            border-color: var(--active-bg);
            box-shadow: 0 0 0 1px var(--active-bg);
            outline: none;
        }
        .search-icon { display: none; } 

        .action-btn {
            background: transparent;
            border: none;
            cursor: pointer;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            padding: 4px;
        }
        .action-btn:hover { color: var(--text-main); }
        .notification-dot {
            width: 8px; height: 8px; background: #ef4444; border-radius: 50%; display: inline-block; margin-right: -8px; margin-top: -8px;
        }

        .profile-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            background: transparent;
            border: none;
            cursor: pointer;
            font-size: 13px;
            color: var(--text-main);
        }
        .profile-btn:hover { color: var(--text-accent); }
        .profile-img {
            width: 26px; height: 26px;
            border-radius: 50%;
            background: #334155;
        }
        .profile-name { font-weight: 600; }
        .profile-role { display: none; }

        /* Welcome Panel */
        .welcome-banner {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            padding: 0;
            margin-bottom: 20px;
            position: relative;
            box-sizing: border-box;
            display: flex;
        }
        .welcome-content {
            padding: 30px;
            max-width: 100%;
        }
        .welcome-title {
            font-size: 23px;
            font-weight: 400;
            margin: 0 0 10px 0;
            color: #1d2327;
        }
        .welcome-text {
            font-size: 15px;
            line-height: 1.5;
            margin: 0 0 20px 0;
            color: var(--wp-text);
        }
        .welcome-btn {
            background: var(--wp-primary);
            border-color: var(--wp-primary);
            color: #fff;
            text-decoration: none;
            text-shadow: none;
            display: inline-block;
            font-size: 13px;
            line-height: 2.15384615;
            min-height: 30px;
            margin: 0;
            padding: 0 10px;
            cursor: pointer;
            border-width: 1px;
            border-style: solid;
            -webkit-appearance: none;
            border-radius: 3px;
            white-space: nowrap;
            box-sizing: border-box;
            font-weight: 400;
        }
        .welcome-btn:hover {
            background: var(--wp-primary-hover);
            border-color: var(--wp-primary-hover);
            color: #fff;
        }
        .welcome-decor { display: none; } /* Remove the decor gradient */

        /* Dashboard Widgets (Stats) */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr); /* WP usually dense */
            gap: 20px;
            margin-bottom: 20px;
        }
        @media (max-width: 1200px) { .stats-grid { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 600px) { .stats-grid { grid-template-columns: 1fr; } }
        
        .stat-card {
            background: #fff;
            border: 1px solid #c3c4c7;
            /* box-shadow: 0 1px 1px rgba(0,0,0,.04); */
            padding: 0;
            min-height: auto;
            border-radius: 0;
            position: relative;
            display: flex;
            flex-direction: column;
            box-sizing: border-box;
            border-top: 4px solid #fff; /* Placeholder for color accent */
        }
        .stat-card:hover { border-color: #c3c4c7; box-shadow: 0 1px 1px rgba(0,0,0,.04); transform: none; }

        /* Color accents on top border to differentiate, keeping the 'color scheme' rule in a WP way */
        .stat-card.blue { border-top-color: #3b82f6; }
        .stat-card.green { border-top-color: #10b981; }
        .stat-card.teal { border-top-color: #0ea5e9; }
        .stat-card.yellow { border-top-color: #f59e0b; }
        .stat-card.purple { border-top-color: #8b5cf6; }
        .stat-card.red { border-top-color: #ef4444; }

        .stat-content {
            padding: 12px 12px 24px;
            display: block;
        }
        .stat-number {
            font-size: 24px; /* WP numbers aren't huge */
            font-weight: 600;
            color: #1d2327;
            margin-bottom: 4px;
            line-height: 1;
        }
        .stat-label {
            font-size: 13px;
            color: var(--wp-text-light);
            font-weight: 400;
        }
        .stat-icon-bg {
            display: none; /* WP widgets rarely have big background icons */
        }
        
        /* Postbox (Generic Container for Charts/Tables) */
        .content-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 20px;
        }
        @media (max-width: 1000px) { .content-grid { grid-template-columns: 1fr; } }

        .dashboard-card {
            background: #fff;
            border: 1px solid #c3c4c7;
            box-shadow: 0 1px 1px rgba(0,0,0,.04);
            margin-bottom: 20px;
            position: relative;
            min-width: 255px;
            border-radius: 0; /* Boxy */
            padding: 0;
        }

        .card-header {
            border-bottom: 1px solid #c3c4c7;
            padding: 8px 12px;
            margin: 0;
            background: #fff; /* Sometimes WP uses #fcfcfc but straight white is cleaner */
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .card-title {
            font-size: 14px;
            font-weight: 600;
            line-height: 1.4;
            color: #1d2327;
            margin: 0;
        }
        .card-action {
            font-size: 12px;
            text-decoration: none;
            color: var(--wp-primary);
        }
        .card-action:hover { color: var(--wp-primary-hover); }

        .card-body {
            padding: 0 12px 12px;
        }

        /* Tables (WP List Table Style) */
        .table-container {
            width: 100%;
            overflow-x: auto;
            border-top: 1px solid #c3c4c7; /* Match WP list table border */
        }
        table {
            width: 100%;
            border-collapse: collapse;
            border-spacing: 0;
            background: #fff;
        }
        th, td {
            text-align: left;
            padding: 8px 10px;
            font-size: 13px;
            line-height: 1.5;
            vertical-align: top;
            color: #2c3338;
        }
        th {
            border-bottom: 1px solid #c3c4c7;
            font-weight: 600;
            color: #1d2327;
        }
        td {
            /* box-shadow: inset 0 -1px 0 rgba(0,0,0,0.1); */
            border-bottom: 1px solid #f0f0f1;
        }
        tr:nth-child(odd) { background-color: #f9f9f9; } /* Striped rows */
        tr:last-child td { border-bottom: none; }

        .user-cell {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .user-avatar {
            width: 32px; height: 32px;
            border-radius: 50%;
        }

        .status-badge {
            font-weight: 700;
            /* WP uses text status primarily, but we'll keep badges minimal */
            padding: 0;
            background: transparent !important;
            font-size: 13px;
        }
        .status-badge.completed { color: #007017; }
        .status-badge.pending { color: #b32d56; } /* Orange-ish/Red-ish */
        .status-badge.failed { color: #d63638; }

        select {
            font-size: 13px;
            color: #2c3338;
            border: 1px solid #8c8f94;
            border-radius: 3px;
            padding: 0 24px 0 8px;
            min-height: 30px;
            max-width: 25rem;
            -webkit-appearance: none;
            background: #fff url("data:image/svg+xml;charset=US-ASCII,%3Csvg%20width%3D%2220%22%20height%3D%2220%22%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%3E%3Cpath%20d%3D%22M5%206l5%205%205-5%202%201-7%207-7-7%202-1z%22%20fill%3D%22%23555%22%2F%3E%3C%2Fsvg%3E") no-repeat right 5px top 55%;
            background-size: 16px 16px;
            cursor: pointer;
            vertical-align: middle;
        }
        select:focus {
            color: #2c3338;
            border-color: var(--wp-primary);
            box-shadow: 0 0 0 1px var(--wp-primary);
            outline: 0;
        }

    </style>
</head>
<body>

<main class="admin-content">
    <header class="top-bar">
        <div class="page-title">
            <h1>Dashboard</h1>
            <p>Welcome back, Admin</p>
        </div>
        
        <div class="top-actions">
            <div class="search-box">
                <input type="text" placeholder="Search...">
            </div>
            
            <button class="action-btn">
                <span class="notification-dot"></span>
                <span style="font-size: 16px; font-weight: 500;">Notifications</span>
            </button>
            
            <div class="profile-btn">
                <img src="https://ui-avatars.com/api/?name=Admin+User&background=4f46e5&color=fff" alt="Admin" class="profile-img">
                <span class="profile-name">Howdy, Admin User</span>
            </div>
        </div>
    </header>

    <div class="welcome-banner">
        <!-- Removed decor -->
        <div class="welcome-content">
            <h2 class="welcome-title">Welcome to your MG Skill Dashboard</h2>
            <p class="welcome-text">We’ve assembled some links to get you started:</p>
            <div style="display:flex; gap:10px;">
                <button class="welcome-btn">Customize Your Site</button>
                <button class="welcome-btn" style="background:#fff; color:var(--wp-primary); border-color:var(--wp-primary);">View Reports</button>
            </div>
        </div>
    </div>

    <div class="stats-grid">
        <!-- Stat 1: Students (Blue) -->
        <div class="stat-card blue">
            <div class="stat-content">
                <div class="stat-number">2,543</div>
                <div class="stat-label">Total Students</div>
            </div>
        </div>

        <!-- Stat 2: Revenue (Green) -->
        <div class="stat-card green">
            <div class="stat-content">
                <div class="stat-number">₹45.2L</div>
                <div class="stat-label">Total Revenue</div>
            </div>
        </div>

        <!-- Stat 3: Batches (Teal) -->
        <div class="stat-card teal">
            <div class="stat-content">
                <div class="stat-number">128</div>
                <div class="stat-label">Active Batches</div>
            </div>
        </div>

        <!-- Stat 4: New Courses (Yellow) -->
        <div class="stat-card yellow">
             <div class="stat-content">
                <div class="stat-number">48</div>
                <div class="stat-label">New Courses</div>
            </div>
        </div>
        
        <!-- Stat 5: Instructors (Purple) -->
         <div class="stat-card purple">
            <div class="stat-content">
                <div class="stat-number">32</div>
                <div class="stat-label">Instructors</div>
            </div>
        </div>
        
        <!-- Stat 6: Tickets (Red) -->
         <div class="stat-card red">
            <div class="stat-content">
                <div class="stat-number">12</div>
                <div class="stat-label">Open Tickets</div>
            </div>
        </div>
    </div>

    <div class="content-grid">
        <!-- Chart Section -->
        <div class="dashboard-card">
            <div class="card-header">
                <h3 class="card-title">Revenue Analytics</h3>
                <div>
                    <select>
                        <option>This Year</option>
                        <option>Last Year</option>
                    </select>
                </div>
            </div>
            <div class="card-body">
                <div style="height: 300px; padding-top: 20px;">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="dashboard-card">
            <div class="card-header">
                <h3 class="card-title">Recent Transactions</h3>
                <a href="#" class="card-action">View All</a>
            </div>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <div class="user-cell">
                                    <img src="https://i.pravatar.cc/150?img=3" class="user-avatar">
                                    <span style="font-weight:600; color:#2271b1;">Rahul K.</span>
                                </div>
                            </td>
                            <td><span style="font-family:monospace;">₹4,500</span></td>
                            <td><span class="status-badge completed">Paid</span></td>
                        </tr>
                        <tr>
                            <td>
                                <div class="user-cell">
                                    <img src="https://i.pravatar.cc/150?img=4" class="user-avatar">
                                    <span style="font-weight:600; color:#2271b1;">Sneha P.</span>
                                </div>
                            </td>
                            <td><span style="font-family:monospace;">₹12,000</span></td>
                            <td><span class="status-badge pending">Pending</span></td>
                        </tr>
                        <tr>
                            <td>
                                <div class="user-cell">
                                    <img src="https://i.pravatar.cc/150?img=5" class="user-avatar">
                                    <span style="font-weight:600; color:#2271b1;">Amit S.</span>
                                </div>
                            </td>
                            <td><span style="font-family:monospace;">₹2,400</span></td>
                            <td><span class="status-badge completed">Paid</span></td>
                        </tr>
                         <tr>
                            <td>
                                <div class="user-cell">
                                    <img src="https://i.pravatar.cc/150?img=8" class="user-avatar">
                                    <span style="font-weight:600; color:#2271b1;">Priya M.</span>
                                </div>
                            </td>
                            <td><span style="font-family:monospace;">₹8,000</span></td>
                            <td><span class="status-badge failed">Failed</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<script>
    // Chart Configuration
    const ctx = document.getElementById('revenueChart').getContext('2d');
    
    // Removed Gradient - WP is simpler
    // const gradient = ... 

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            datasets: [{
                label: 'Revenue (₹)',
                data: [12000, 19000, 15000, 25000, 22000, 30000, 35000, 28000, 42000, 45000, 48000, 55000],
                // WP Blue
                borderColor: '#2271b1',
                backgroundColor: 'rgba(34, 113, 177, 0.1)',
                borderWidth: 2,
                pointBackgroundColor: '#ffffff',
                pointBorderColor: '#2271b1',
                pointRadius: 3,
                pointHoverRadius: 5,
                fill: true,
                tension: 0 // Straighter lines often look more like business data, or keep slightly curved
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1d2327',
                    padding: 8,
                    displayColors: false,
                    callbacks: {
                        label: function(context) {
                            return '₹ ' + context.parsed.y.toLocaleString();
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { borderDash: [2, 2], color: '#f0f0f1' },
                    ticks: { font: { family: '-apple-system, BlinkMacSystemFont, "Segoe UI"', size: 11 }, color: '#646970' }
                },
                x: {
                    grid: { display: false },
                    ticks: { font: { family: '-apple-system, BlinkMacSystemFont, "Segoe UI"', size: 11 }, color: '#646970' }
                }
            }
        }
    });
</script>
</body>
</html>
