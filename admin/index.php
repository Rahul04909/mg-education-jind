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
        :root {
            /* WP Admin Colors */
            --wp-bg: #f0f0f1;
            --wp-text: #3c434a;
            --wp-text-light: #646970;
            --wp-border: #c3c4c7;
            --wp-primary: #2271b1;
            --wp-primary-hover: #135e96;
            --wp-alert-red: #d63638;
            --admin-bar-height: 32px;
            --sidebar-width: 160px;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
            background-color: var(--wp-bg);
            margin: 0;
            padding: 0;
            color: var(--wp-text);
            font-size: 13px;
            line-height: 1.4;
        }

        /* Layout */
        .admin-content {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            padding: 10px 20px 20px 20px;
            box-sizing: border-box;
        }
        
        body.sidebar-collapsed .admin-content {
            margin-left: 36px;
        }

        /* Top Header - WP Style (Simple, just Title + Screen Options usually) */
        .top-bar {
            padding: 10px 0 20px 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .page-title h1 {
            font-size: 23px;
            font-weight: 400;
            margin: 0;
            color: #1d2327;
            padding: 9px 0 4px 0;
            line-height: 1.3;
            display: inline-block;
        }
        
        /* Dashboard Widgets Container */
        .dashboard-widgets {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -8px;
        }
        
        .postbox-container {
            width: 50%;
            padding: 0 8px;
            box-sizing: border-box;
        }
        @media (max-width: 800px) { .postbox-container { width: 100%; } }

        /* Generic Postbox (Widget) */
        .postbox {
            background: #fff;
            border: 1px solid #c3c4c7;
            box-shadow: 0 1px 1px rgba(0,0,0,.04);
            margin-bottom: 20px;
            position: relative;
            min-width: 255px;
        }
        
        .postbox-header {
            border-bottom: 1px solid #c3c4c7;
            padding: 8px 12px;
            margin: 0;
            background: #fff;
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer; /* Toggleable usually */
        }
        .postbox-header h2 {
            font-size: 14px;
            font-weight: 600;
            line-height: 1.4;
            color: #1d2327;
            margin: 0;
        }
        
        .postbox-content {
            padding: 0 12px 12px;
            margin-top: 10px; /* Spacing from header */
        }
        
        /* Welcome Panel */
        .welcome-panel {
            background: #fff;
            border: 1px solid #c3c4c7;
            padding: 24px;
            margin-bottom: 20px;
            position: relative;
            display: flex;
            justify-content: space-between; /* To push button to right if needed, or structured */
        }
        .welcome-panel-content h2 {
            margin: 0 0 10px;
            font-size: 21px;
            font-weight: 400;
            line-height: 1.2;
        }
        .welcome-panel-content p {
            font-size: 14px;
            margin-bottom: 20px;
            color: #646970;
        }

        /* Quick Button */
        .button {
            display: inline-block;
            text-decoration: none;
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
        }
        .button-primary {
            background: var(--wp-primary);
            border-color: var(--wp-primary);
            color: #fff;
        }
        .button-primary:hover {
            background: var(--wp-primary-hover);
            border-color: var(--wp-primary-hover);
            color: #fff;
        }

        /* At a Glance */
        ul.glance-list {
            list-style: none;
            margin: 0;
            padding: 0;
        }
        ul.glance-list li {
            margin-bottom: 10px;
            color: #646970;
            font-size: 14px;
            display: flex;
            align-items: center;
        }
        ul.glance-list li a {
            text-decoration: none;
            color: var(--wp-primary);
            font-weight: 600;
        }
        ul.glance-list li span.count {
            margin-right: 5px;
        }
        ul.glance-list li .dashicons {
            margin-right: 5px;
            color: #8c8f94;
        }

        /* Activity / Recent Comments style */
        .activity-block {
            border-bottom: 1px solid #f0f0f1;
            padding: 10px 0;
        }
        .activity-block:last-child { border-bottom: none; }
        .activity-header {
            font-size: 12px;
            color: #646970;
            margin-bottom: 4px;
        }
        .activity-title a {
            font-weight: 600;
            color: var(--wp-primary);
            text-decoration: none;
        }
        .activity-description {
            color: #3c434a;
        }
        
    </style>
</head>
<body>

<main class="admin-content">
    <header class="top-bar">
        <div class="page-title">
            <h1>Dashboard</h1>
        </div>
        <!-- Screen Options / Help usually here -->
    </header>

    <div class="welcome-panel">
        <div class="welcome-panel-content">
            <h2>Welcome to MG Skill</h2>
            <p>We’ve assembled some links to get you started:</p>
            <a href="#" class="button button-primary">Customize Your Site</a>
        </div>
    </div>

    <div class="dashboard-widgets">
        <!-- Column 1 -->
        <div class="postbox-container">
            <!-- At a Glance -->
            <div class="postbox">
                <div class="postbox-header"><h2>At a Glance</h2></div>
                <div class="postbox-content">
                    <ul class="glance-list">
                        <li>
                            <svg class="dashicons" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                            <a href="#"><span class="count">48</span> Courses</a>
                        </li>
                        <li>
                            <svg class="dashicons" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            <a href="#"><span class="count">2,543</span> Students</a>
                        </li>
                         <li>
                            <svg class="dashicons" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                            <a href="#"><span class="count">128</span> Batches</a>
                        </li>
                    </ul>
                    <p style="margin-top:20px; font-size:12px; color:#646970;">WordPress 6.4.2 running MG Theme.</p>
                </div>
            </div>

            <!-- Activity -->
            <div class="postbox">
                <div class="postbox-header"><h2>Activity</h2></div>
                <div class="postbox-content">
                    <div style="margin-bottom:15px; font-size:12px; color:#646970; text-transform:uppercase; font-weight:600;">Recently Published</div>
                    
                    <div class="activity-block">
                        <div class="activity-header">Feb 4th, 10:30 AM</div>
                        <div class="activity-title"><a href="#">Introduction to PHP Programming</a></div>
                    </div>
                    
                    <div class="activity-block">
                         <div class="activity-header">Feb 3rd, 2:15 PM</div>
                        <div class="activity-title"><a href="#">Graphic Design Masterclass</a></div>
                    </div>
                    
                    <div class="activity-block">
                         <div class="activity-header">Feb 1st, 09:00 AM</div>
                        <div class="activity-title"><a href="#">Web Development Bootcamp</a></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Column 2 -->
        <div class="postbox-container">
            <!-- Quick Draft / Revenue (Simulation) -->
            <div class="postbox">
                <div class="postbox-header"><h2>Quick Stats</h2></div>
                <div class="postbox-content">
                     <canvas id="revenueChart" style="height:200px; width:100%;"></canvas>
                </div>
            </div>
            
             <!-- MG Events -->
            <div class="postbox">
                <div class="postbox-header"><h2>Upcoming Events</h2></div>
                <div class="postbox-content">
                     <div class="activity-block">
                        <div class="activity-title"><a href="#">Mega Recruitment Drive</a></div>
                         <div class="activity-description">Scheduled for 15th Feb, 2026.</div>
                    </div>
                    <div class="activity-block">
                        <div class="activity-title"><a href="#">Coding Hackathon</a></div>
                         <div class="activity-description">Scheduled for 20th Feb, 2026.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
    // Chart Configuration for Quick Stats (Simplified)
    const ctx = document.getElementById('revenueChart').getContext('2d');
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['M', 'T', 'W', 'T', 'F', 'S', 'S'],
            datasets: [{
                label: 'Signups',
                data: [12, 19, 3, 5, 2, 3, 10],
                backgroundColor: '#2271b1',
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { display: false },
                x: { grid: { display: false } }
            }
        }
    });

    // Sidebar Toggle connection
    // We need to ensure sidebar toggle works with body class
    // Since sidebar.php has the script, we can just ensure body has the class if needed
</script>
</body>
</html>
