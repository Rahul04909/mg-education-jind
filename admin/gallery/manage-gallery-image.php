<?php
// Include database configuration
require_once __DIR__ . '/../../database/db-config.php';
$conn = getDbConnection();

// Delete logic
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    
    // Get image path to delete file
    $res = $conn->query("SELECT image_path FROM gallery_images WHERE id = $id");
    if ($res && $res->num_rows > 0) {
        $row = $res->fetch_assoc();
        $file_path = __DIR__ . '/../../' . $row['image_path'];
        if (file_exists($file_path)) {
            unlink($file_path);
        }
    }

    $conn->query("DELETE FROM gallery_images WHERE id = $id");
    header("Location: manage-gallery-image.php?msg=deleted");
    exit;
}

// Fetch Images with Category Name
$sql = "SELECT g.*, c.name as category_name 
        FROM gallery_images g 
        LEFT JOIN gallery_categories c ON g.category_id = c.id 
        ORDER BY g.created_at DESC";
$result = $conn->query($sql);

include __DIR__ . "/../sidebar.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Gallery Images - MG Education</title>
    <style>
        :root{--active:#22c55e;--indigo:#6f75ff;--line:#e6e8ee;--text:#0b1020;--muted:#6f7787}
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;background:linear-gradient(180deg,#f8fafc 0%,#ffffff 100%);color:var(--text)}
        .admin-content{margin-left:260px;min-height:100vh;padding:20px}
        .page-header{margin-bottom:30px; display: flex; justify-content: space-between; align-items: center;}
        .page-title{font-size:32px;font-weight:800;color:var(--text);margin-bottom:8px;}
        .btn{padding:10px 20px;border-radius:10px;text-decoration:none;font-weight:700;display:inline-flex;align-items:center;gap:8px;font-size:14px;transition:0.2s;}
        .btn-primary{background:var(--indigo);color:#fff;}
        .card{background:#fff;border:1px solid var(--line);border-radius:18px;overflow:hidden;}
        .table{width:100%;border-collapse:collapse;}
        .table th{text-align:left;padding:16px;background:#f8fafc;font-size:13px;font-weight:700;border-bottom:1px solid var(--line);}
        .table td{padding:16px;border-bottom:1px solid var(--line);font-size:14px;vertical-align: middle;}
        .badge{padding:4px 10px;border-radius:999px;font-size:12px;font-weight:700;}
        .badge.active{background:#d1fae5;color:#065f46;}
        .badge.inactive{background:#fee2e2;color:#991b1b;}
        .thumb-img{width: 80px; height: 60px; object-fit: cover; border-radius: 6px; border: 1px solid var(--line);}
    </style>
</head>
<body>
    <main class="admin-content">
        <div class="page-header">
            <div>
                <h1 class="page-title">Gallery Images</h1>
                <p style="color:var(--muted)">Manage images and categories</p>
            </div>
            <div style="display:flex; gap:10px">
                <a href="add-gallery-images.php" class="btn btn-primary">+ Add Image</a>
            </div>
        </div>

        <?php if (isset($_GET['msg']) && $_GET['msg'] == 'deleted'): ?>
            <div style="padding: 15px; background: #fee2e2; color: #991b1b; border-radius: 8px; margin-bottom: 20px;">Image deleted successfully.</div>
        <?php endif; ?>

        <div class="card">
            <table class="table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Title</th>
                        <th>Category</th>
                        <th>Created</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td>
                                <img src="../../<?php echo htmlspecialchars($row['image_path']); ?>" class="thumb-img" alt="Thumbnail">
                            </td>
                            <td>
                                <div style="font-weight:700"><?php echo htmlspecialchars($row['title']); ?></div>
                            </td>
                            <td>
                                <span style="background:#f1f5f9; padding:4px 8px; border-radius:4px; font-size:12px; font-weight:600;">
                                    <?php echo htmlspecialchars($row['category_name'] ?? 'Uncategorized'); ?>
                                </span>
                            </td>
                            <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                            <td>
                                <span class="badge <?php echo $row['is_active'] ? 'active' : 'inactive'; ?>">
                                    <?php echo $row['is_active'] ? 'Active' : 'Inactive'; ?>
                                </span>
                            </td>
                            <td>
                                <a href="edit-gallery-image.php?id=<?php echo $row['id']; ?>" style="color:var(--indigo);font-weight:600;text-decoration:none; margin-right: 10px;">Edit</a>
                                <a href="manage-gallery-image.php?delete=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure you want to delete this image?')" style="color:#ef4444;font-weight:600;text-decoration:none">Delete</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" style="text-align:center;padding:40px;color:var(--muted)">No images found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>
<?php $conn->close(); ?>
