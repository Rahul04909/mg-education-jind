<?php
require_once __DIR__ . '/../../database/db-config.php';

$conn = getDbConnection();
$message = "";
$msg_type = "";

// Handle Delete
if (isset($_POST['delete_uni'])) {
    $id = intval($_POST['delete_id']);
    // Box model fetching for file path
    $res = $conn->query("SELECT logo_path FROM universities WHERE id = $id");
    if ($res->num_rows > 0) {
        $row = $res->fetch_assoc();
        $file_path = __DIR__ . '/../../' . $row['logo_path'];
        
        if (file_exists($file_path)) {
            unlink($file_path);
        }
        
        $conn->query("DELETE FROM universities WHERE id = $id");
        $message = "University deleted successfully.";
        $msg_type = "success";
    }
}

// Handle Add
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_uni'])) {
    $name = $conn->real_escape_string($_POST['name']);
    
    if (isset($_FILES['logo_image']) && $_FILES['logo_image']['error'] == 0 && !empty($name)) {
        $target_dir = "../../assets/images/universities/";
        if (!file_exists(__DIR__ . '/' . $target_dir)) {
            mkdir(__DIR__ . '/' . $target_dir, 0777, true);
        }
        
        $ext = pathinfo($_FILES['logo_image']['name'], PATHINFO_EXTENSION);
        $filename = "uni_" . time() . "_" . uniqid() . "." . $ext;
        $target_file = $target_dir . $filename;
        $db_path = "assets/images/universities/" . $filename;
        
        // Simple validation
        $check = getimagesize($_FILES['logo_image']['tmp_name']);
        if($check !== false) {
             if (move_uploaded_file($_FILES['logo_image']['tmp_name'], __DIR__ . '/' . $target_file)) {
                 $stmt = $conn->prepare("INSERT INTO universities (name, logo_path) VALUES (?, ?)");
                 $stmt->bind_param("ss", $name, $db_path);
                 if ($stmt->execute()) {
                     $message = "University added successfully!";
                     $msg_type = "success";
                 } else {
                     $message = "Database error: " . $conn->error;
                     $msg_type = "error";
                 }
             } else {
                 $message = "Error uploading file.";
                 $msg_type = "error";
             }
        } else {
            $message = "File is not an image.";
            $msg_type = "error";
        }
    } else {
        $message = "Please provide name and logo.";
        $msg_type = "error";
    }
}

// Fetch Universities
$universities = [];
$res = $conn->query("SELECT * FROM universities ORDER BY created_at DESC");
while($row = $res->fetch_assoc()) {
    $universities[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Universities - MG Skills</title>
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
        
        .alert{padding:15px;border-radius:8px;margin-bottom:20px}
        .alert-success{background:#dcfce7;color:#166534}
        .alert-error{background:#fee2e2;color:#991b1b}

        .input-control{padding:10px;border:1px solid var(--line);border-radius:8px;font-size:14px;width:100%}

        .uni-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
        }
        .uni-item {
            border: 1px solid var(--line);
            border-radius: 8px;
            overflow: hidden;
            background: #fff;
            text-align: center;
            padding: 15px;
        }
        .uni-img {
            width: 100%;
            height: 100px;
            object-fit: contain;
            margin-bottom: 10px;
        }
        .uni-name {
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 10px;
             white-space: nowrap;
             overflow: hidden;
             text-overflow: ellipsis;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . "/../sidebar.php"; ?>
    <main class="admin-content">
        <div class="admin-wrap">
            <div class="page-header">
                <h1 class="page-title">Manage Universities</h1>
            </div>
            
            <?php if($message): ?>
                <div class="alert alert-<?php echo $msg_type; ?>"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <!-- Add University -->
            <div class="card">
                <h3>Add University</h3>
                 <form method="POST" enctype="multipart/form-data" style="margin-top:15px; display:grid; grid-template-columns: 2fr 1fr auto; gap:15px; align-items:end;">
                    <div>
                        <label style="display:block; margin-bottom:5px; font-size:14px; font-weight:600;">University Name *</label>
                        <input type="text" name="name" class="input-control" required placeholder="e.g. Delhi University">
                    </div>
                    <div>
                         <label style="display:block; margin-bottom:5px; font-size:14px; font-weight:600;">Logo *</label>
                        <input type="file" name="logo_image" class="input-control" required accept="image/*" style="padding:10px; width:100%;">
                    </div>
                    <div>
                        <button type="submit" name="add_uni" class="btn">Add University</button>
                    </div>
                </form>
            </div>
            
            <!-- Existing Universities -->
            <div class="card">
                <h3>Partner Universities</h3>
                <br>
                <div class="uni-grid">
                    <?php foreach($universities as $uni): ?>
                    <div class="uni-item">
                        <img src="../../<?php echo htmlspecialchars($uni['logo_path']); ?>" class="uni-img" alt="<?php echo htmlspecialchars($uni['name']); ?>">
                        <div class="uni-name" title="<?php echo htmlspecialchars($uni['name']); ?>"><?php echo htmlspecialchars($uni['name']); ?></div>
                        <form method="POST" onsubmit="return confirm('Are you sure you want to delete this?');">
                            <input type="hidden" name="delete_id" value="<?php echo $uni['id']; ?>">
                            <button type="submit" name="delete_uni" class="btn btn-red" style="padding:4px 10px; font-size:12px;">Delete</button>
                        </form>
                    </div>
                    <?php endforeach; ?>
                    <?php if(empty($universities)): ?>
                        <p style="grid-column:1/-1; color:var(--muted);">No universities found. Add one above.</p>
                    <?php endif; ?>
                </div>
            </div>
            
        </div>
    </main>
</body>
</html>
<?php $conn->close(); ?>
