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
    <title>Admin Dashboard - MG Skill</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary: #4f46e5; /* Indigo 600 */
            --primary-dark: #3730a3; /* Indigo 800 */
            --primary-soft: #eef2ff; /* Indigo 50 */
            --text-main: #0f172a; /* Slate 900 */
            --text-light: #64748b; /* Slate 500 */
            --bg-body: #f1f5f9; /* Slate 100 */
            --surface: #ffffff;
            --border: #e2e8f0;
            --shadow-sm: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg-body);
            margin: 0;
            padding: 0;
            color: var(--text-main);
        }

        /* Layout */
        .admin-content {
            margin-left: 260px;
            min-height: 100vh;
            padding: 32px;
            transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        body.sidebar-collapsed .admin-content {
            margin-left: 80px;
        }

        @media (max-width: 900px) {
            .admin-content { margin-left: 80px; padding: 20px; }
        }

        /* Top Bar */
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 32px;
        }
        
        .page-title h1 {
            font-size: 24px;
            font-weight: 700;
            margin: 0;
            color: var(--text-main);
        }
        .page-title p {
            font-size: 14px;
            color: var(--text-light);
            margin: 4px 0 0 0;
        }

        .top-actions {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .search-box {
            position: relative;
        }
        .search-box input {
            padding: 10px 16px 10px 40px;
            border-radius: 99px;
            border: 1px solid var(--border);
            outline: none;
            font-family: inherit;
            font-size: 14px;
            width: 240px;
            transition: all 0.2s;
            background: var(--surface);
        }
        .search-box input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-soft);
            width: 280px;
        }
        .search-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            width: 18px;
            height: 18px;
            stroke: var(--text-light);
        }

        .action-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 1px solid var(--border);
            background: var(--surface);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s;
            color: var(--text-light);
            position: relative;
        }
        .action-btn:hover {
            background: var(--primary-soft);
            color: var(--primary);
            border-color: var(--primary-soft);
        }
        .notification-dot {
            position: absolute;
            top: 10px;
            right: 10px;
            width: 8px;
            height: 8px;
            background: #ef4444;
            border-radius: 50%;
            border: 2px solid var(--surface);
        }

        .profile-btn {
            display: flex;
            align-items: center;
            gap: 10px;
            background: var(--surface);
            padding: 6px 16px 6px 6px;
            border-radius: 99px;
            border: 1px solid var(--border);
            cursor: pointer;
            transition: all 0.2s;
        }
        .profile-btn:hover { border-color: var(--primary); }
        .profile-img {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            object-fit: cover;
        }
        .profile-info {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            line-height: 1.2;
        }
        .profile-name { font-size: 13px; font-weight: 600; color: var(--text-main); }
        .profile-role { font-size: 11px; color: var(--text-light); }

        /* Welcome Banner */
        .welcome-banner {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            border-radius: 20px;
            padding: 32px;
            color: white;
            position: relative;
            overflow: hidden;
            margin-bottom: 32px;
            box-shadow: var(--shadow-lg);
        }
        .welcome-content { position: relative; z-index: 2; max-width: 600px; }
        .welcome-title { font-size: 28px; font-weight: 800; margin: 0 0 8px 0; }
        .welcome-text { font-size: 15px; opacity: 0.9; margin: 0 0 24px 0; line-height: 1.5; }
        .welcome-btn {
            padding: 10px 20px;
            background: rgba(255,255,255,0.2);
            border: 1px solid rgba(255,255,255,0.3);
            border-radius: 10px;
            color: white;
            font-weight: 600;
            cursor: pointer;
            backdrop-filter: blur(4px);
            transition: all 0.2s;
        }
        .welcome-btn:hover { background: rgba(255,255,255,0.3); transform: translateY(-2px); }
        
        .welcome-decor {
            position: absolute;
            right: 0;
            top: 0;
            bottom: 0;
            width: 50%;
            background: radial-gradient(circle at 70% 50%, rgba(255,255,255,0.1) 0%, transparent 60%);
            z-index: 1;
        }
        .welcome-decor::after {
            content: '';
            position: absolute;
            right: -20px;
            bottom: -20px;
            width: 200px;
            height: 200px;
            border-radius: 50%;
            background: rgba(255,255,255,0.05);
        }

        /* Updated Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 24px;
            margin-bottom: 40px;
        }
        @media (max-width: 1000px) { .stats-grid { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 600px) { .stats-grid { grid-template-columns: 1fr; } }
        
        .stat-card.blue { background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); }
        .stat-card.green { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
        .stat-card.teal { background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%); }
        .stat-card.yellow { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); }
        .stat-card.purple { background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); }
        .stat-card.red { background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); }

        .stat-card {
            position: relative; 
            overflow: hidden; 
            display: flex; 
            align-items: center; 
            justify-content: space-between; 
            padding: 24px 28px; 
            min-height: 120px; 
            border-radius: 20px; 
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            border: none;
            transition: transform 0.2s;
        }
        .stat-card:hover { transform: translateY(-4px); box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.15); }
        
        .stat-content { z-index: 2; position: relative; display: flex; flex-direction: column; justify-content: center; }
        .stat-number { font-size: 36px; font-weight: 700; line-height: 1; margin-bottom: 6px; color:white; }
        .stat-label { font-size: 15px; font-weight: 500; opacity: 0.95; color:white; letter-spacing: 0.5px; }
        
        .stat-icon-bg { position: absolute; right: 15px; top: 50%; transform: translateY(-50%); opacity: 0.2; width: 80px; height: 80px; }
        .stat-icon-bg svg { width: 100%; height: 100%; fill: currentColor; color: white; }
        
        /* Main Grid */
        .content-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 24px;
        }
        @media (max-width: 1200px) { .content-grid { grid-template-columns: 1fr; } }

        .dashboard-card {
            background: var(--surface);
            border-radius: 16px;
            border: 1px solid var(--border);
            box-shadow: var(--shadow-sm);
            padding: 24px;
            height: 100%;
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
            color: var(--text-main);
            margin: 0;
        }
        .card-action {
            color: var(--primary);
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
        }

        /* Table */
        .table-container {
            width: 100%;
            overflow-x: auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th {
            text-align: left;
            font-size: 12px;
            font-weight: 600;
            color: var(--text-light);
            text-transform: uppercase;
            padding: 12px 0;
            border-bottom: 1px solid var(--border);
        }
        td {
            padding: 16px 0;
            font-size: 14px;
            color: var(--text-main);
            border-bottom: 1px solid #f1f5f9;
        }
        tr:last-child td { border-bottom: none; }
        
        .status-badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
        }
        .status-badge.completed { background: #dcfce7; color: #16a34a; }
        .status-badge.pending { background: #ffedd5; color: #ea580c; }
        .status-badge.failed { background: #fee2e2; color: #dc2626; }

        .user-cell {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .user-avatar {
            width: 32px; height: 32px;
            border-radius: 50%;
            background: #e2e8f0;
            object-fit: cover;
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
                <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                <input type="text" placeholder="Search...">
            </div>
            
            <button class="action-btn">
                <div class="notification-dot"></div>
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
            </button>
            
            <div class="profile-btn">
                <img src="https://ui-avatars.com/api/?name=Admin+User&background=4f46e5&color=fff" alt="Admin" class="profile-img">
                <div class="profile-info">
                    <span class="profile-name">Admin User</span>
                    <span class="profile-role">Super Admin</span>
                </div>
            </div>
        </div>
    </header>

    <div class="welcome-banner">
        <div class="welcome-decor"></div>
        <div class="welcome-content">
            <h2 class="welcome-title">Overview & Analytics</h2>
            <p class="welcome-text">Here's what's happening with your platform today. Check the new reports section for detailed insights.</p>
            <button class="welcome-btn">View Reports</button>
        </div>
    </div>

    <div class="stats-grid">
        <!-- Stat 1: Students (Blue) -->
        <div class="stat-card blue">
            <div class="stat-content">
                <div class="stat-number">2,543</div>
                <div class="stat-label">Total Students</div>
            </div>
            <div class="stat-icon-bg">
                <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
            </div>
        </div>

        <!-- Stat 2: Revenue (Green) -->
        <div class="stat-card green">
            <div class="stat-content">
                <div class="stat-number">₹45.2L</div>
                <div class="stat-label">Total Revenue</div>
            </div>
            <div class="stat-icon-bg">
                <svg viewBox="0 0 24 24"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>
            </div>
        </div>

        <!-- Stat 3: Batches (Teal) -->
        <div class="stat-card teal">
            <div class="stat-content">
                <div class="stat-number">128</div>
                <div class="stat-label">Active Batches</div>
            </div>
            <div class="stat-icon-bg">
                <svg viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22 6 12 13 2 6"></polyline></svg>
            </div>
        </div>

        <!-- Stat 4: New Courses (Yellow) -->
        <div class="stat-card yellow">
             <div class="stat-content">
                <div class="stat-number">48</div>
                <div class="stat-label">New Courses</div>
            </div>
            <div class="stat-icon-bg">
                <svg viewBox="0 0 24 24"><polygon points="12 2 2 7 12 12 22 7 12 2"></polygon><polyline points="2 17 12 22 22 17"></polyline><polyline points="2 12 12 17 22 12"></polyline></svg>
            </div>
        </div>
        
        <!-- Stat 5: Instructors (Purple) -->
         <div class="stat-card purple">
            <div class="stat-content">
                <div class="stat-number">32</div>
                <div class="stat-label">Instructors</div>
            </div>
            <div class="stat-icon-bg">
                <svg viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><line x1="20" y1="8" x2="20" y2="14"></line><line x1="23" y1="11" x2="17" y2="11"></line></svg>
            </div>
        </div>
        
        <!-- Stat 6: Tickets (Red) -->
         <div class="stat-card red">
            <div class="stat-content">
                <div class="stat-number">12</div>
                <div class="stat-label">Open Tickets</div>
            </div>
            <div class="stat-icon-bg">
                <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
            </div>
        </div>
    </div>

    <div class="content-grid">
        <!-- Chart Section -->
        <div class="dashboard-card">
            <div class="card-header">
                <h3 class="card-title">Revenue Analytics</h3>
                <div style="display:flex; gap:10px;">
                    <select style="border:1px solid #e2e8f0; padding:6px; border-radius:8px; outline:none;">
                        <option>This Year</option>
                        <option>Last Year</option>
                    </select>
                </div>
            </div>
            <div style="height: 300px;">
                <canvas id="revenueChart"></canvas>
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
                                    <span>Rahul K.</span>
                                </div>
                            </td>
                            <td style="font-weight:600;">₹4,500</td>
                            <td><span class="status-badge completed">Paid</span></td>
                        </tr>
                        <tr>
                            <td>
                                <div class="user-cell">
                                    <img src="https://i.pravatar.cc/150?img=4" class="user-avatar">
                                    <span>Sneha P.</span>
                                </div>
                            </td>
                            <td style="font-weight:600;">₹12,000</td>
                            <td><span class="status-badge pending">Pending</span></td>
                        </tr>
                        <tr>
                            <td>
                                <div class="user-cell">
                                    <img src="https://i.pravatar.cc/150?img=5" class="user-avatar">
                                    <span>Amit S.</span>
                                </div>
                            </td>
                            <td style="font-weight:600;">₹2,400</td>
                            <td><span class="status-badge completed">Paid</span></td>
                        </tr>
                         <tr>
                            <td>
                                <div class="user-cell">
                                    <img src="https://i.pravatar.cc/150?img=8" class="user-avatar">
                                    <span>Priya M.</span>
                                </div>
                            </td>
                            <td style="font-weight:600;">₹8,000</td>
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
    
    // Gradient
    const gradient = ctx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, 'rgba(79, 70, 229, 0.4)'); // Primary color with opacity
    gradient.addColorStop(1, 'rgba(79, 70, 229, 0)');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            datasets: [{
                label: 'Revenue (₹)',
                data: [12000, 19000, 15000, 25000, 22000, 30000, 35000, 28000, 42000, 45000, 48000, 55000],
                borderColor: '#4f46e5',
                backgroundColor: gradient,
                borderWidth: 2,
                pointBackgroundColor: '#ffffff',
                pointBorderColor: '#4f46e5',
                pointRadius: 4,
                pointHoverRadius: 6,
                fill: true,
                tension: 0.4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1e293b',
                    padding: 12,
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
                    grid: { borderDash: [4, 4], color: '#e2e8f0' },
                    ticks: { font: { family: 'Outfit', size: 11 }, color: '#64748b' }
                },
                x: {
                    grid: { display: false },
                    ticks: { font: { family: 'Outfit', size: 11 }, color: '#64748b' }
                }
            }
        }
    });
</script>
</body>
</html>
