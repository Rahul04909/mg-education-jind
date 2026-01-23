<?php
require_once __DIR__ . '/../../database/db-config.php';

$conn = getDbConnection();
$message = "";
$msg_type = "";

// Handle Delete
if (isset($_POST['delete_news'])) {
    $id = intval($_POST['delete_id']);
    $conn->query("DELETE FROM news WHERE id = $id");
    $message = "News item deleted successfully.";
    $msg_type = "success";
}

// Handle Status Toggle
if (isset($_POST['toggle_status'])) {
    $id = intval($_POST['news_id']);
    $current = intval($_POST['current_status']);
    $new = $current ? 0 : 1;
    $conn->query("UPDATE news SET is_active = $new WHERE id = $id");
    $message = "Status updated.";
    $msg_type = "success";
}

// Handle Add
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_news'])) {
    $title = $conn->real_escape_string($_POST['title']);
    $url = $conn->real_escape_string($_POST['url']);
    
    if(!empty($title)) {
        $sql = "INSERT INTO news (title, url, is_active) VALUES ('$title', '$url', 1)";
        if ($conn->query($sql)) {
            $message = "News item added successfully!";
            $msg_type = "success";
        } else {
            $message = "Database error: " . $conn->error;
            $msg_type = "error";
        }
    } else {
        $message = "Title is required.";
        $msg_type = "error";
    }
}

// Fetch News
$news_items = [];
$res = $conn->query("SELECT * FROM news ORDER BY created_at DESC");
while($row = $res->fetch_assoc()) {
    $news_items[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage News Ticker - MG Skills</title>
    <style>
        :root{--active:#22c55e;--indigo:#6f75ff;--line:#e6e8ee;--text:#0b1020;--muted:#6f7787;--error:#ef4444;--success:#22c55e}
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:system-ui,-apple-system,sans-serif;background:#f8fafc;color:var(--text)}
        .admin-content{margin-left:260px;padding:20px}
        .admin-wrap{max-width:1100px;margin:0 auto}
        .page-header{margin-bottom:20px;display:flex;justify-content:space-between;align-items:center}
        .page-title{font-size:24px;font-weight:700}
        
        .card{background:#fff;padding:25px;border-radius:12px;box-shadow:0 2px 10px rgba(0,0,0,0.05);margin-bottom:25px}
        .btn{padding:10px 20px;background:var(--indigo);color:#fff;border:none;border-radius:8px;cursor:pointer;font-weight:600}
        .btn-red{background:var(--error)}
        .btn-sm{padding:6px 12px; font-size:13px;}
        
        .alert{padding:15px;border-radius:8px;margin-bottom:20px}
        .alert-success{background:#dcfce7;color:#166534}
        .alert-error{background:#fee2e2;color:#991b1b}

        .input-control{padding:10px;border:1px solid var(--line);border-radius:8px;font-size:14px;width:100%}
        
        table{width:100%;border-collapse:collapse;margin-top:10px}
        th,td{text-align:left;padding:12px;border-bottom:1px solid var(--line)}
        th{color:var(--muted);font-weight:600;font-size:13px}
        
        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }
        .status-active { background: #dcfce7; color: #166534; }
        .status-inactive { background: #fee2e2; color: #991b1b; }
    </style>
</head>
<body>
    <?php include __DIR__ . "/../sidebar.php"; ?>
    <main class="admin-content">
        <div class="admin-wrap">
            <div class="page-header">
                <h1 class="page-title">Manage News Ticker</h1>
            </div>
            
            <?php if($message): ?>
                <div class="alert alert-<?php echo $msg_type; ?>"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <!-- Add News -->
            <div class="card">
                <h3>Add News Item</h3>
                <form method="POST" style="margin-top:15px; display:grid; grid-template-columns: 2fr 1fr auto; gap:15px; align-items:end;">
                    <div>
                        <label style="display:block; margin-bottom:5px; font-size:14px; font-weight:600;">Title *</label>
                        <input type="text" name="title" class="input-control" required placeholder="e.g. Admissions Open">
                    </div>
                    <div>
                        <label style="display:block; margin-bottom:5px; font-size:14px; font-weight:600;">Link URL (Optional)</label>
                        <input type="text" name="url" class="input-control" placeholder="e.g. https://example.com or #">
                    </div>
                    <div>
                        <button type="submit" name="add_news" class="btn">Add News</button>
                    </div>
                </form>
            </div>
            
            <!-- Existing News -->
            <div class="card">
                <h3>Existing News</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Link</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($news_items as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['title']); ?></td>
                            <td><a href="<?php echo htmlspecialchars($item['url']); ?>" target="_blank" style="color:var(--indigo);"><?php echo htmlspecialchars($item['url']); ?></a></td>
                            <td><?php echo date('d M Y', strtotime($item['created_at'])); ?></td>
                            <td>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="toggle_status" value="1">
                                    <input type="hidden" name="news_id" value="<?php echo $item['id']; ?>">
                                    <input type="hidden" name="current_status" value="<?php echo $item['is_active']; ?>">
                                    <button type="submit" style="background:none; border:none; cursor:pointer;">
                                        <span class="status-badge <?php echo $item['is_active'] ? 'status-active' : 'status-inactive'; ?>">
                                            <?php echo $item['is_active'] ? 'Active' : 'Inactive'; ?>
                                        </span>
                                    </button>
                                </form>
                            </td>
                            <td>
                                <form method="POST" onsubmit="return confirm('Are you sure you want to delete this?');">
                                    <input type="hidden" name="delete_id" value="<?php echo $item['id']; ?>">
                                    <button type="submit" name="delete_news" class="btn btn-red btn-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(empty($news_items)): ?>
                            <tr><td colspan="5" style="text-align:center; padding:20px; color:var(--muted);">No news items found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
        </div>
    </main>
</body>
</html>
<?php $conn->close(); ?>
