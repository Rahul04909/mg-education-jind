<?php
require_once __DIR__ . '/../../database/db-config.php';

$conn = getDbConnection();
$message = "";
$msg_type = "";

// Handle Delete
if (isset($_POST['delete_slide'])) {
    $id = intval($_POST['delete_id']);
    // Box model fetching for file path
    $res = $conn->query("SELECT image_path FROM hero_slides WHERE id = $id");
    if ($res->num_rows > 0) {
        $row = $res->fetch_assoc();
        $file_path = __DIR__ . '/../../' . $row['image_path'];
        
        if (file_exists($file_path)) {
            unlink($file_path);
        }
        
        $conn->query("DELETE FROM hero_slides WHERE id = $id");
        $message = "Slide deleted successfully.";
        $msg_type = "success";
    }
}

// Handle Add
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_slide'])) {
    if (isset($_FILES['slide_image']) && $_FILES['slide_image']['error'] == 0) {
        $target_dir = "../../assets/images/hero-slides/";
        if (!file_exists(__DIR__ . '/' . $target_dir)) {
            mkdir(__DIR__ . '/' . $target_dir, 0777, true);
        }
        
        $ext = pathinfo($_FILES['slide_image']['name'], PATHINFO_EXTENSION);
        $filename = "slide_" . time() . "_" . uniqid() . "." . $ext;
        $target_file = $target_dir . $filename;
        $db_path = "assets/images/hero-slides/" . $filename;
        
        // Simple validation
        $check = getimagesize($_FILES['slide_image']['tmp_name']);
        if($check !== false) {
             if (move_uploaded_file($_FILES['slide_image']['tmp_name'], __DIR__ . '/' . $target_file)) {
                 $stmt = $conn->prepare("INSERT INTO hero_slides (image_path) VALUES (?)");
                 $stmt->bind_param("s", $db_path);
                 if ($stmt->execute()) {
                     $message = "Slide added successfully!";
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
        $message = "Please select an image.";
        $msg_type = "error";
    }
}

// Fetch Slides
$slides = [];
$res = $conn->query("SELECT * FROM hero_slides ORDER BY created_at DESC");
while($row = $res->fetch_assoc()) {
    $slides[] = $row;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Hero Slides - MG Skills</title>
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

        .slide-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        .slide-item {
            border: 1px solid var(--line);
            border-radius: 8px;
            overflow: hidden;
            background: #fff;
        }
        .slide-img {
            width: 100%;
            height: 180px;
            object-fit: cover;
        }
        .slide-actions {
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
    </style>
</head>
<body>
    <?php include __DIR__ . "/../sidebar.php"; ?>
    <main class="admin-content">
        <div class="admin-wrap">
            <div class="page-header">
                <h1 class="page-title">Manage Hero Slides</h1>
            </div>
            
            <?php if($message): ?>
                <div class="alert alert-<?php echo $msg_type; ?>"><?php echo $message; ?></div>
            <?php endif; ?>
            
            <!-- Add Slide -->
            <div class="card">
                <h3>Add New Slide</h3>
                <p style="color:var(--muted); font-size:14px; margin-bottom:15px;">Recommended Size: 1920x600 px. Ensure the image is optimized for web.</p>
                <form method="POST" enctype="multipart/form-data" style="display:flex; gap:15px; align-items:flex-end;">
                    <div style="flex:1;">
                        <input type="file" name="slide_image" class="input-control" required accept="image/*" style="padding:10px; border:1px solid var(--line); width:100%; border-radius:8px;">
                    </div>
                    <button type="submit" name="add_slide" class="btn">Upload Slide</button>
                </form>
            </div>
            
            <!-- Existing Slides -->
            <div class="card">
                <h3>Existing Slides</h3>
                <br>
                <div class="slide-grid">
                    <?php foreach($slides as $slide): ?>
                    <div class="slide-item">
                        <img src="../../<?php echo htmlspecialchars($slide['image_path']); ?>" class="slide-img" alt="Slide">
                        <div class="slide-actions">
                            <span style="font-size:12px; color:var(--muted);"><?php echo date('d M Y', strtotime($slide['created_at'])); ?></span>
                            <form method="POST" onsubmit="return confirm('Are you sure you want to delete this slide?');">
                                <input type="hidden" name="delete_id" value="<?php echo $slide['id']; ?>">
                                <button type="submit" name="delete_slide" class="btn btn-red" style="padding:6px 12px; font-size:13px;">Delete</button>
                            </form>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php if(empty($slides)): ?>
                        <p style="grid-column:1/-1; color:var(--muted);">No slides found. Add one above.</p>
                    <?php endif; ?>
                </div>
            </div>
            
        </div>
    </main>
</body>
</html>
<?php $conn->close(); ?>
