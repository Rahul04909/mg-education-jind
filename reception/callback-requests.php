<?php
session_start();
require_once __DIR__ . '/../database/db-config.php';

// Auth check
if(!isset($_SESSION['reception_id'])) {
    header("Location: login.php");
    exit;
}

$conn = getDbConnection();

// Fetch Callback Requests
$sql = "SELECT c.*, co.title as course_title 
        FROM callback_requests c 
        LEFT JOIN courses co ON c.course_id = co.id 
        ORDER BY c.created_at DESC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Callback Requests - Reception Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #ec4899;
            --bg-body: #fdf2f8; 
            --text-main: #1e293b;
            --text-light: #64748b;
            --sidebar-w: 260px;
            --card-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.1);
        }
        
        * { box-sizing: border-box; }
        body { margin: 0; font-family: 'Outfit', sans-serif; background: var(--bg-body); color: var(--text-main); }
        .main-content { margin-left: var(--sidebar-w); padding: 30px; transition: all 0.3s; min-height: 100vh; }
        
        .page-header { margin-bottom: 30px; }
        .page-title { font-size: 24px; font-weight: 700; color: #831843; margin-bottom: 5px; }
        
        .card {
            background: white;
            border-radius: 16px;
            box-shadow: var(--card-shadow);
            overflow: hidden;
        }
        
        .table-container { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; padding: 16px 24px; background: #fce7f3; color: #be185d; font-weight: 600; font-size: 13px; text-transform: uppercase; }
        td { padding: 16px 24px; border-bottom: 1px solid #f1f5f9; font-size: 14px; }
        tr:last-child td { border-bottom: none; }
        tr:hover { background: #fff1f2; }
        
        .badge { padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; background: #fee2e2; color: #991b1b; }
        
        @media (max-width: 768px) {
            .main-content { margin-left: 0; padding: 20px; }
        }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<main class="main-content">
    <div class="page-header">
        <h1 class="page-title">Callback Requests</h1>
    </div>

    <div class="card">
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Name</th>
                        <th>Contact</th>
                        <th>Preferred Time</th>
                        <th>Course Interest</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?><br><span style="font-size:12px;color:var(--text-light)"><?php echo date('h:i A', strtotime($row['created_at'])); ?></span></td>
                                <td style="font-weight:600;"><?php echo htmlspecialchars($row['full_name']); ?></td>
                                <td>
                                    <div><?php echo htmlspecialchars($row['mobile']); ?></div>
                                    <div style="font-size:12px;color:var(--text-light)"><?php echo htmlspecialchars($row['email']); ?></div>
                                </td>
                                <td>
                                    <?php 
                                    $from = $row['schedule_from'] ? date('h:i A', strtotime($row['schedule_from'])) : 'Anytime';
                                    $to = $row['schedule_to'] ? date('h:i A', strtotime($row['schedule_to'])) : '';
                                    echo $from . ($to ? ' - ' . $to : '');
                                    ?>
                                </td>
                                <td><?php echo htmlspecialchars($row['course_title'] ?? 'General Enquiry'); ?></td>
                                <td>
                                    <a href="tel:<?php echo $row['mobile']; ?>" style="text-decoration:none; color:var(--primary); font-weight:600; border:1px solid var(--primary); padding:6px 12px; border-radius:8px; display:inline-block;">Call Now</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" style="text-align:center; padding:40px; color:var(--text-light);">No callback requests found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
</body>
</html>
