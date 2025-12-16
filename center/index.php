<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - MG Skill Center</title>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary: #059669;
            --primary-dark: #047857;
            --bg-body: #f1f5f9;
            --text-main: #0f172a;
            --text-light: #64748b;
            --white: #ffffff;
            --sidebar-width: 280px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --card-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05), 0 4px 6px -2px rgba(0, 0, 0, 0.025);
            --card-hover: 0 20px 25px -5px rgba(0, 0, 0, 0.05), 0 10px 10px -5px rgba(0, 0, 0, 0.02);
        }
        
        body { font-family: 'Outfit', sans-serif; background-color: var(--bg-body); color: var(--text-main); margin: 0; }

        .main-content {
            margin-left: var(--sidebar-width);
            padding: 30px 40px;
            min-height: 100vh;
            transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .main-content.expanded { margin-left: 80px; }

        /* Welcome Banner */
        .welcome-banner {
            background: linear-gradient(135deg, #059669 0%, #064e3b 100%);
            border-radius: 24px;
            padding: 40px;
            color: white;
            position: relative;
            overflow: hidden;
            margin-bottom: 40px;
            box-shadow: 0 20px 25px -5px rgba(5, 150, 105, 0.15);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .welcome-banner::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -10%;
            width: 500px;
            height: 500px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
            filter: blur(60px);
        }
        .welcome-text { position: relative; z-index: 2; }
        .welcome-text h1 { font-size: 32px; margin: 0 0 10px 0; font-weight: 700; letter-spacing: -1px; }
        .welcome-text p { font-size: 16px; margin: 0; opacity: 0.9; font-weight: 400; }
        
        .date-badge {
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(10px);
            padding: 10px 20px;
            border-radius: 12px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
            border: 1px solid rgba(255,255,255,0.2);
        }

        /* Top Bar */
        .top-bar {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 20px;
            gap: 20px;
        }
        .icon-btn {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: white;
            border: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: var(--transition);
            color: var(--text-light);
        }
        .icon-btn:hover { background: var(--bg-body); color: var(--primary); border-color: var(--primary); }
        .icon-btn.has-dot::after {
            content: '';
            position: absolute;
            top: 10px;
            right: 12px;
            width: 8px;
            height: 8px;
            background: #ef4444;
            border-radius: 50%;
            border: 2px solid white;
        }

        .user-pill {
            display: flex;
            align-items: center;
            gap: 12px;
            background: white;
            padding: 6px 6px 6px 16px;
            border-radius: 40px;
            border: 1px solid #e2e8f0;
            cursor: pointer;
            transition: var(--transition);
        }
        .user-pill:hover { border-color: var(--primary); box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .avatar {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 14px;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 24px;
            margin-bottom: 40px;
        }
        
        .stat-card {
            background: var(--white);
            border-radius: 32px; /* Very rounded corners as per image */
            padding: 32px 28px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02), 0 2px 4px -2px rgba(0, 0, 0, 0.02);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 200px;
            border: 1px solid #f8fafc;
        }
        
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.01);
        }

        .card-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
        }
        
        .card-title {
            font-size: 18px;
            font-weight: 500;
            color: #1e293b;
            letter-spacing: -0.5px;
        }
        
        .card-arrow {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            border: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #1e293b;
            transition: all 0.2s;
            cursor: pointer;
        }
        .card-arrow:hover { background: #f8fafc; border-color: #cbd5e1; }
        .card-arrow svg { width: 18px; height: 18px; }

        .card-value-row {
            display: flex;
            align-items: center;
            gap: 16px;
            margin-bottom: 12px;
        }
        
        .stat-card-value {
            font-size: 48px;
            font-weight: 500; /* Lighter weight as per image for large numbers */
            color: #0f172a;
            line-height: 1;
            letter-spacing: -2px;
        }

        .impact-pill {
            background: #a3e635; /* Bright lime green */
            color: #1a2e05;
            font-size: 12px;
            font-weight: 600;
            padding: 6px 14px;
            border-radius: 20px;
            letter-spacing: 0.3px;
        }
        
        .card-desc {
            font-size: 14px;
            color: #64748b;
            line-height: 1.5;
            margin: 0;
            font-weight: 400;
        }

        /* Charts Section */
        .content-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 24px;
            margin-bottom: 24px;
        }
        .card {
            background: var(--white);
            border-radius: 24px;
            padding: 24px;
            box-shadow: var(--card-shadow);
            border: 1px solid #f1f5f9;
        }
        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
        }
        .card-title { font-size: 18px; font-weight: 700; color: var(--text-main); }
        
        /* Activity List */
        .activity-item {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 16px 0;
            border-bottom: 1px dashed #e2e8f0;
        }
        .activity-item:last-child { border-bottom: none; }
        .activity-icon-sm {
            width: 40px;
            height: 40px;
            border-radius: 12px;
            background: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<main class="main-content">
    
    <div class="top-bar">
        <div class="icon-btn position-relative has-dot">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
        </div>
        <div class="user-pill">
            <div style="text-align: right;">
                <div style="font-size: 14px; font-weight: 700;">Alexander</div>
                <div style="font-size: 11px; color: var(--text-light);">Center Manager</div>
            </div>
            <div class="avatar">A</div>
        </div>
    </div>

    <div class="welcome-banner">
        <div class="welcome-text">
            <h1>Welcome back, Alexander!</h1>
            <p>Here's what's happening at your center today.</p>
        </div>
        <div class="date-badge">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
            <span><?php echo date('d M, Y'); ?></span>
        </div>
    </div>

    <div class="stats-grid">
        <!-- Card 1 -->
        <div class="stat-card">
            <div class="card-top">
                <div class="card-title">Student Retention</div>
                <div class="card-arrow">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="7" y1="17" x2="17" y2="7"></line><polyline points="7 7 17 7 17 17"></polyline></svg>
                </div>
            </div>
            <div>
                <div class="card-value-row">
                    <span class="stat-card-value">98%</span>
                    <span class="impact-pill">High Impact</span>
                </div>
                <p class="card-desc">Percentage of students continuing to next semester</p>
            </div>
        </div>

        <!-- Card 2 -->
        <div class="stat-card">
            <div class="card-top">
                <div class="card-title">Fee Collection</div>
                <div class="card-arrow">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="7" y1="17" x2="17" y2="7"></line><polyline points="7 7 17 7 17 17"></polyline></svg>
                </div>
            </div>
            <div>
                <div class="card-value-row">
                    <span class="stat-card-value">12%</span>
                    <span class="impact-pill" style="background:#dbeafe; color:#1e40af">Stable</span>
                </div>
                <p class="card-desc">Increase in monthly revenue compared to last year</p>
            </div>
        </div>

        <!-- Card 3 -->
        <div class="stat-card">
            <div class="card-top">
                <div class="card-title">Active Batches</div>
                <div class="card-arrow">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="7" y1="17" x2="17" y2="7"></line><polyline points="7 7 17 7 17 17"></polyline></svg>
                </div>
            </div>
            <div>
                <div class="card-value-row">
                    <span class="stat-card-value">24</span>
                    <span class="impact-pill">High Impact</span>
                </div>
                <p class="card-desc">Total number of batches running currently</p>
            </div>
        </div>

        <!-- Card 4 -->
        <div class="stat-card">
            <div class="card-top">
                <div class="card-title">Placement Rate</div>
                <div class="card-arrow">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="7" y1="17" x2="17" y2="7"></line><polyline points="7 7 17 7 17 17"></polyline></svg>
                </div>
            </div>
            <div>
                <div class="card-value-row">
                    <span class="stat-card-value">85%</span>
                    <span class="impact-pill" style="background:#fee2e2; color:#991b1b">Needs Focus</span>
                </div>
                <p class="card-desc">Students placed in jobs after course completion</p>
            </div>
        </div>
    </div>

    <div class="content-grid">
        <div class="card">
            <div class="card-header">
                <div class="card-title">Admission Analytics</div>
                <select style="border:1px solid #e2e8f0; padding:6px 12px; border-radius:8px; outline:none; color:var(--text-light);">
                    <option>Last 7 Days</option>
                    <option>Last Month</option>
                </select>
            </div>
            <canvas id="mainChart" height="120"></canvas>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="card-title">Recent Activity</div>
            </div>
            <div class="activity-list">
                <div class="activity-item">
                    <div class="activity-icon-sm icon-green">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle></svg>
                    </div>
                    <div style="flex:1">
                        <div style="font-weight:600; font-size:14px;">Rahul Kumar</div>
                        <div style="font-size:12px; color:var(--text-light);">ADCA Course Admission</div>
                    </div>
                    <div style="font-size:12px; color:var(--text-light);">2m</div>
                </div>
                
                <div class="activity-item">
                    <div class="activity-icon-sm icon-purple">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 1v22M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                    </div>
                    <div style="flex:1">
                        <div style="font-weight:600; font-size:14px;">Fees Payment</div>
                        <div style="font-size:12px; color:var(--text-light);">₹5000 Received</div>
                    </div>
                    <div style="font-size:12px; color:var(--text-light);">1h</div>
                </div>
                <div class="activity-item">
                    <div class="activity-icon-sm icon-blue">
                         <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>
                    </div>
                    <div style="flex:1">
                        <div style="font-weight:600; font-size:14px;">New Batch</div>
                        <div style="font-size:12px; color:var(--text-light);">Java Full Stack</div>
                    </div>
                    <div style="font-size:12px; color:var(--text-light);">3h</div>
                </div>
            </div>
            <button style="width:100%; padding:12px; border:1px dashed #e2e8f0; background:none; margin-top:16px; border-radius:12px; font-weight:600; color:var(--primary); cursor:pointer;">View All Activity</button>
        </div>
    </div>
</main>

<script>
    // Global Sidebar Toggle
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.querySelector('.main-content');
        if(sidebar) sidebar.classList.toggle('collapsed');
        if(mainContent) mainContent.classList.toggle('expanded');
    }

    const ctx = document.getElementById('mainChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
            datasets: [{
                label: 'Admissions',
                data: [4, 12, 8, 15, 10, 22, 18],
                borderColor: '#059669',
                backgroundColor: 'rgba(5, 150, 105, 0.1)',
                fill: true,
                tension: 0.4,
                borderWidth: 3,
                pointRadius: 4,
                pointBackgroundColor: '#fff',
                pointBorderColor: '#059669',
                pointBorderWidth: 2
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: { display: false },
                x: { grid: { display: false }, ticks: { color: '#94a3b8' } }
            }
        }
    });
</script>

</body>
</html>
