<?php
session_start();
require_once __DIR__ . '/../database/db-config.php';

// Auth check
if(!isset($_SESSION['reception_id'])) {
    header("Location: login.php");
    exit;
} 

$conn = getDbConnection();

// 1. Total Enquiries
$sql_enq = "SELECT COUNT(*) as count FROM quick_enquiries";
$total_enquiries = $conn->query($sql_enq)->fetch_assoc()['count'];

// 2. Callback Requests
$sql_cb = "SELECT COUNT(*) as count FROM callback_requests";
$callback_requests = $conn->query($sql_cb)->fetch_assoc()['count'];

// 3. Total Students (Admitted by Admin - similar logic to search)
$sql_stu = "SELECT COUNT(*) as count FROM admissions WHERE center_id IS NULL OR center_id = 0";
$active_students = $conn->query($sql_stu)->fetch_assoc()['count'];

// 4. Today's Visitors
$today = date('Y-m-d');
$sql_vis = "SELECT COUNT(*) as count FROM visitors WHERE DATE(check_in_time) = '$today'";
$today_visitors = $conn->query($sql_vis)->fetch_assoc()['count'];

// 5. Total Visitors (Lifetime) - Requested by user to show "Total Visitors" stats
$sql_vis_total = "SELECT COUNT(*) as count FROM visitors";
$total_visitors = $conn->query($sql_vis_total)->fetch_assoc()['count'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reception Dashboard - MG Skills</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #ec4899; /* Pink 500 */
            --bg-body: #fdf2f8; /* Pink 50 */
            --text-main: #1e293b;
            --text-light: #64748b;
            --sidebar-w: 260px;
            --card-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.1);
        }
        
        * { box-sizing: border-box; }
        body { margin: 0; font-family: 'Outfit', sans-serif; background: var(--bg-body); color: var(--text-main); }
        .main-content { margin-left: var(--sidebar-w); padding: 30px; transition: all 0.3s; min-height: 100vh; }
        
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        .page-title { font-size: 24px; font-weight: 700; color: #831843; } /* Pink 900 */
        .page-subtitle { font-size: 14px; color: var(--text-light); }

        /* Stats Grid */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 24px; margin-bottom: 30px; }
        .stat-card {
            background: white;
            padding: 24px;
            border-radius: 16px;
            box-shadow: var(--card-shadow);
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-left: 5px solid transparent;
            transition: transform 0.2s;
        }
        .stat-card:hover { transform: translateY(-3px); }
        .stat-card.pink { border-left-color: #ec4899; }
        .stat-card.purple { border-left-color: #a855f7; }
        .stat-card.blue { border-left-color: #3b82f6; }
        .stat-card.orange { border-left-color: #f97316; }

        .stat-info h3 { font-size: 32px; font-weight: 700; margin: 0; color: var(--text-main); }
        .stat-info p { margin: 5px 0 0; color: var(--text-light); font-size: 14px; font-weight: 500; }
        
        .stat-icon {
            width: 50px; height: 50px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 24px;
        }
        .bg-pink-soft { background: #fce7f3; color: #db2777; }
        .bg-purple-soft { background: #f3e8ff; color: #9333ea; }
        .bg-blue-soft { background: #dbeafe; color: #2563eb; }
        .bg-orange-soft { background: #ffedd5; color: #ea580c; }

        /* Recent Activity / Quick Actions */
        .dashboard-grid-2 { display: grid; grid-template-columns: 2fr 1fr; gap: 24px; }
        
        .content-card {
            background: white;
            border-radius: 16px;
            box-shadow: var(--card-shadow);
            padding: 24px;
        }
        .card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .card-title { font-size: 18px; font-weight: 700; color: #831843; }
        
        .activity-list { list-style: none; padding: 0; margin: 0; }
        .activity-item {
            display: flex; gap: 16px; padding: 16px 0; border-bottom: 1px solid #f1f5f9;
        }
        .activity-item:last-child { border-bottom: none; }
        .activity-icon {
            width: 40px; height: 40px; border-radius: 50%; background: #f1f5f9; display: flex; align-items: center; justify-content: center; flex-shrink: 0;
        }
        .activity-details h4 { font-size: 14px; font-weight: 600; margin: 0 0 4px; }
        .activity-details p { font-size: 13px; color: var(--text-light); margin: 0; }
        .activity-time { font-size: 12px; color: #94a3b8; white-space: nowrap; margin-left: auto; }

        .action-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            padding: 16px;
            margin-top: 12px;
            border-radius: 14px;
            font-weight: 600;
            font-size: 15px;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #ec4899 0%, #be185d 100%);
            color: white;
            border: none;
            box-shadow: 0 4px 6px -1px rgba(236, 72, 153, 0.3), 0 2px 4px -1px rgba(236, 72, 153, 0.1);
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(236, 72, 153, 0.4);
        }
        
        .btn-outline {
            background: white;
            color: #ec4899;
            border: 2px solid #fce7f3;
        }
        .btn-outline:hover {
            border-color: #ec4899;
            background: #fff1f2;
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        @media (max-width: 768px) {
            .main-content { margin-left: 0; padding: 20px; }
            .dashboard-grid-2 { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<main class="main-content">
    <div class="header">
        <div>
            <div class="page-title">Good Morning, Reception</div>
            <div class="page-subtitle">Here's what's happening at the front desk today.</div>
        </div>
        <div style="font-weight: 600; color: #831843; background: white; padding: 8px 16px; border-radius: 30px; box-shadow: var(--card-shadow);">
            <?php echo date('l, d M Y'); ?>
        </div>
    </div>

    <div class="stats-grid">
        <!-- Total Enquiries -->
        <div class="stat-card pink">
            <div class="stat-info">
                <h3><?php echo $total_enquiries; ?></h3>
                <p>Total Enquiries</p>
            </div>
            <div class="stat-icon bg-pink-soft">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>
            </div>
        </div>

        <!-- Callback Requests -->
        <div class="stat-card purple">
            <div class="stat-info">
                <h3><?php echo $callback_requests; ?></h3>
                <p>Callback Requests</p>
            </div>
             <div class="stat-icon bg-purple-soft">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
            </div>
        </div>

        <!-- Active Students -->
        <div class="stat-card blue">
            <div class="stat-info">
                <h3><?php echo $active_students; ?></h3>
                <p>Total Students</p>
            </div>
             <div class="stat-icon bg-blue-soft">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
            </div>
        </div>

        <!-- Visitors (Today / Total) -->
        <div class="stat-card orange">
            <div class="stat-info">
                <h3 style="font-size:24px;"><?php echo $today_visitors; ?> <span style="font-size:14px; color:#94a3b8; font-weight:500;">/ <?php echo $total_visitors; ?></span></h3>
                <p>Visitors (Today / Total)</p>
            </div>
             <div class="stat-icon bg-orange-soft">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><line x1="20" y1="8" x2="20" y2="14"></line><line x1="23" y1="11" x2="17" y2="11"></line></svg>
            </div>
        </div>
    </div>

    <div class="dashboard-grid-2">
        <div class="content-card">
            <div class="card-header">
                <div class="card-title">Recent Activity</div>
                <a href="#" style="color:#ec4899; text-decoration:none; font-size:14px; font-weight:600;">View All</a>
            </div>
            <ul class="activity-list">
                <li class="activity-item">
                    <div class="activity-icon text-pink-600 bg-pink-50">
                        AB
                    </div>
                    <div class="activity-details">
                        <h4>New Enquiry: Amit Bhardwaj</h4>
                        <p>Interested in Frontend Development Course</p>
                    </div>
                    <div class="activity-time">10 mins ago</div>
                </li>
                <li class="activity-item">
                    <div class="activity-icon text-purple-600 bg-purple-50">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><line x1="20" y1="8" x2="20" y2="14"></line><line x1="23" y1="11" x2="17" y2="11"></line></svg>
                    </div>
                    <div class="activity-details">
                        <h4>Visitor Check-in: Rahul Sharma</h4>
                        <p>Meeting with Center Manager</p>
                    </div>
                    <div class="activity-time">35 mins ago</div>
                </li>
                 <li class="activity-item">
                    <div class="activity-icon text-green-600 bg-green-50">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                    </div>
                    <div class="activity-details">
                        <h4>Payment Received</h4>
                        <p>Student #MG202355 paid admission fees</p>
                    </div>
                    <div class="activity-time">1 hr ago</div>
                </li>
            </ul>
        </div>
        
        <div class="content-card">
            <div class="card-header">
                <div class="card-title">Quick Actions</div>
            </div>
            <a href="#" class="action-btn btn-primary">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                Add New Enquiry
            </a>
            <a href="#" class="action-btn btn-outline">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><line x1="20" y1="8" x2="20" y2="14"></line><line x1="23" y1="11" x2="17" y2="11"></line></svg>
                Register Visitor
            </a>
            <a href="student-search.php" class="action-btn btn-outline">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                Search Student
            </a>
            <div style="margin-top:20px; padding:15px; background:#fff1f2; border-radius:12px;">
                <h5 style="margin:0 0 5px 0; color:#9f1239;">Pending Tasks</h5>
                <p style="margin:0; font-size:13px; color:#be185d;">You have <strong>5 follow-ups</strong> scheduled for today.</p>
            </div>
        </div>
    </div>
</main>
</body>
</html>
