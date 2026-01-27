<?php
// Include database configuration
require_once __DIR__ . '/../../database/db-config.php';
$conn = getDbConnection();

$success_message = "";
$error_message = "";

// Fetch logic for Categories
$categories = [];
$cat_res = $conn->query("SELECT * FROM gallery_categories WHERE is_active = 1 ORDER BY name ASC");
if($cat_res){
    while($row = $cat_res->fetch_assoc()){
        $categories[] = $row;
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $category_id = intval($_POST['category_id']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    
    // File Upload
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $target_dir = "../../assets/images/gallery/";
        if (!file_exists(__DIR__ . '/' . $target_dir)) {
            mkdir(__DIR__ . '/' . $target_dir, 0777, true);
        }
        
        $file_ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];
        
        if (in_array($file_ext, $allowed_ext)) {
            // Generate unique filename
            $new_filename = "gallery_" . time() . "_" . uniqid() . "." . $file_ext;
            $target_file = $target_dir . $new_filename;
            $db_path = "assets/images/gallery/" . $new_filename;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], __DIR__ . '/' . $target_file)) {
                $sql = "INSERT INTO gallery_images (category_id, title, image_path, is_active) VALUES ($category_id, '$title', '$db_path', $is_active)";
                
                if ($conn->query($sql) === TRUE) {
                    $success_message = "Gallery Image added successfully!";
                } else {
                    $error_message = "Database Error: " . $conn->error;
                    // Optional: remove uploaded file if db insert fails
                    unlink(__DIR__ . '/' . $target_file);
                }
            } else {
                $error_message = "Failed to upload image.";
            }
        } else {
            $error_message = "Invalid file type. Only JPG, JPEG, PNG, and WEBP are allowed.";
        }
    } else {
        $error_message = "Please select an image.";
    }
}

include __DIR__ . "/../sidebar.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Gallery Image - MG Education</title>
    <style>
        :root{--active:#22c55e;--indigo:#6f75ff;--line:#e6e8ee;--text:#0b1020;--muted:#6f7787;--error:#ef4444;--success:#22c55e;--info:#3b82f6}
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;background:linear-gradient(180deg,#f8fafc 0%,#ffffff 100%);color:var(--text)}
        .admin-content{margin-left:260px;min-height:100vh;padding:20px;transition:margin-left .25s ease}
        .admin-wrap{max-width:800px;margin:0 auto}
        .page-header{margin-bottom:30px}
        .page-title{font-size:32px;font-weight:800;color:var(--text);margin-bottom:8px;display:flex;align-items:center;gap:12px}
        .page-subtitle{color:var(--muted);font-size:15px}
        .breadcrumb{color:var(--muted);margin-bottom:10px;font-size:14px}
        .breadcrumb a{color:var(--indigo);text-decoration:none}
        .alert{padding:16px 20px;border-radius:12px;margin-bottom:20px;display:flex;align-items:center;gap:12px;border:1px solid;animation:slideDown .3s ease}
        .alert-success{background:#d1fae5;border-color:#86efac;color:#065f46}
        .alert-error{background:#fee2e2;border-color:#fca5a5;color:#991b1b}
        .card{background:#fff;border:1px solid var(--line);border-radius:18px;padding:30px;box-shadow:0 4px 12px rgba(0,0,0,.05);margin-bottom: 20px;}
        .form-group{margin-bottom:20px}
        .form-label{display:block;font-weight:700;color:var(--text);margin-bottom:8px;font-size:14px}
        .form-input, .form-textarea, .form-select{width:100%;padding:12px 16px;border:1px solid var(--line);border-radius:10px;font-size:15px;transition:all .2s ease;font-family:inherit;background:#fff}
        .btn{display:inline-flex;align-items:center;gap:8px;padding:12px 24px;border-radius:12px;font-weight:700;font-size:15px;border:none;cursor:pointer;transition:all .2s ease;text-decoration:none}
        .btn-primary{background:linear-gradient(135deg,var(--indigo) 0%,#5a5fff 100%);color:#fff}
        .preview-image{max-width: 100%; height: auto; max-height: 200px; margin-top: 10px; border-radius: 8px; display: none;}
    </style>
</head>
<body>
    <main class="admin-content">
        <div class="admin-wrap">
            <div class="page-header">
                <div class="breadcrumb">
                    <a href="../index.php">Dashboard</a> › <a href="manage-gallery-image.php">Gallery Images</a> › Add Image
                </div>
                <h1 class="page-title">Add Gallery Image</h1>
                <p class="page-subtitle">Upload a new image to the gallery</p>
            </div>

            <?php if (!empty($success_message)): ?>
            <div class="alert alert-success"><span>✓ <?php echo $success_message; ?></span></div>
            <?php endif; ?>

            <?php if (!empty($error_message)): ?>
            <div class="alert alert-error"><span>⚠ <?php echo $error_message; ?></span></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                <div class="card">
                    <div class="form-group">
                        <label class="form-label">Image Title *</label>
                        <input type="text" name="title" class="form-input" required placeholder="e.g., Annual Sports Day">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Category *</label>
                        <select name="category_id" class="form-select" required>
                            <option value="">Select Category</option>
                            <?php foreach($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php if(empty($categories)): ?>
                            <small style="color:var(--error);">No active categories found. Please add a category first.</small>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Upload Image *</label>
                        <input type="file" name="image" id="imageInput" class="form-input" required accept="image/png, image/jpeg, image/webp">
                        <img id="imagePreview" class="preview-image" alt="Preview">
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <input type="checkbox" name="is_active" checked> Active
                        </label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Save Image</button>
                </div>
            </form>
        </div>
    </main>

    <script>
        // Image Preview
        document.getElementById('imageInput').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('imagePreview');
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>
