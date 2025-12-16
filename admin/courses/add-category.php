<?php
// Include database configuration
require_once __DIR__ . '/../../database/db-config.php';

// Create database connection
$conn = getDbConnection();

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$success_message = "";
$error_message = "";

// Handle Form Submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    
    // Generate Slug if empty
    $slug = !empty($_POST['slug']) ? $_POST['slug'] : strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
    $slug = mysqli_real_escape_string($conn, $slug);
    
    $meta_title = mysqli_real_escape_string($conn, $_POST['meta_title']);
    $meta_desc = mysqli_real_escape_string($conn, $_POST['meta_desc']);
    $meta_keywords = mysqli_real_escape_string($conn, $_POST['meta_keywords']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    // Handle Image Upload
    $banner_image = "";
    if (isset($_FILES['banner_image']) && $_FILES['banner_image']['error'] == 0) {
        $target_dir = "../../assets/uploads/categories/";
        // Create dir if not exists
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_extension = pathinfo($_FILES["banner_image"]["name"], PATHINFO_EXTENSION);
        $new_filename = "cat_" . time() . "." . $file_extension;
        $target_file = $target_dir . $new_filename;
        
        if (move_uploaded_file($_FILES["banner_image"]["tmp_name"], $target_file)) {
            $banner_image = "assets/uploads/categories/" . $new_filename; // Store relative path for DB
        } else {
            $error_message = "Sorry, there was an error uploading your file.";
        }
    }

    if (empty($error_message)) {
        $sql = "INSERT INTO course_categories (name, slug, banner_image, meta_title, meta_desc, meta_keywords, is_active)
                VALUES ('$name', '$slug', '$banner_image', '$meta_title', '$meta_desc', '$meta_keywords', $is_active)";

        if ($conn->query($sql) === TRUE) {
            $success_message = "Category created successfully!";
        } else {
            $error_message = "Error: " . $sql . "<br>" . $conn->error;
        }
    }
}

include __DIR__ . "/../sidebar.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Course Category - MG Education</title>
    <style>
        :root{--active:#22c55e;--indigo:#6f75ff;--line:#e6e8ee;--text:#0b1020;--muted:#6f7787;--error:#ef4444;--success:#22c55e;--info:#3b82f6}
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;background:linear-gradient(180deg,#f8fafc 0%,#ffffff 100%);color:var(--text)}
        .admin-content{margin-left:260px;min-height:100vh;padding:20px;transition:margin-left .25s ease}
        body.sidebar-collapsed .admin-content{margin-left:88px}
        .admin-wrap{max-width:1000px;margin:0 auto}
        .page-header{margin-bottom:30px}
        .page-title{font-size:32px;font-weight:800;color:var(--text);margin-bottom:8px;display:flex;align-items:center;gap:12px}
        .page-subtitle{color:var(--muted);font-size:15px}
        .breadcrumb{color:var(--muted);margin-bottom:10px;font-size:14px}
        .breadcrumb a{color:var(--indigo);text-decoration:none}
        .alert{padding:16px 20px;border-radius:12px;margin-bottom:20px;display:flex;align-items:center;gap:12px;border:1px solid;animation:slideDown .3s ease}
        @keyframes slideDown{from{opacity:0;transform:translateY(-10px)}to{opacity:1;transform:translateY(0)}}
        .alert-success{background:#d1fae5;border-color:#86efac;color:#065f46}
        .alert-error{background:#fee2e2;border-color:#fca5a5;color:#991b1b}
        .card{background:#fff;border:1px solid var(--line);border-radius:18px;padding:30px;box-shadow:0 4px 12px rgba(0,0,0,.05)}
        .form-group{margin-bottom:20px}
        .form-label{display:block;font-weight:700;color:var(--text);margin-bottom:8px;font-size:14px}
        .form-input, .form-textarea{width:100%;padding:12px 16px;border:1px solid var(--line);border-radius:10px;font-size:15px;transition:all .2s ease;font-family:inherit}
        .form-input:focus, .form-textarea:focus{outline:none;border-color:var(--indigo);box-shadow:0 0 0 3px rgba(111,117,255,.1)}
        .form-help{font-size:13px;color:var(--muted);margin-top:6px}
        .btn{display:inline-flex;align-items:center;gap:8px;padding:12px 24px;border-radius:12px;font-weight:700;font-size:15px;border:none;cursor:pointer;transition:all .2s ease;text-decoration:none}
        .btn-primary{background:linear-gradient(135deg,var(--indigo) 0%,#5a5fff 100%);color:#fff}
        .btn-primary:hover{transform:translateY(-2px);box-shadow:0 8px 20px rgba(111,117,255,.3)}
        .grid-2{display:grid;grid-template-columns:1fr 1fr;gap:20px}
        .preview-image{max-width:200px;margin-top:10px;border-radius:8px;display:none}
    </style>
</head>
<body>
    <main class="admin-content">
        <div class="admin-wrap">
            <div class="page-header">
                <div class="breadcrumb">
                    <a href="../index.php">Dashboard</a> › <a href="#">Courses</a> › Add Category
                </div>
                <h1 class="page-title">Create Course Category</h1>
                <p class="page-subtitle">Add a new category to organize your courses</p>
            </div>

            <?php if (!empty($success_message)): ?>
            <div class="alert alert-success">
                <span>✓ <?php echo $success_message; ?></span>
            </div>
            <?php endif; ?>

            <?php if (!empty($error_message)): ?>
            <div class="alert alert-error">
                <span>⚠ <?php echo $error_message; ?></span>
            </div>
            <?php endif; ?>

            <div class="card">
                <form method="POST" enctype="multipart/form-data">
                    <div class="grid-2">
                        <div class="form-group">
                            <label class="form-label">Category Name *</label>
                            <input type="text" name="name" id="name" class="form-input" required placeholder="e.g., JEE Advanced">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Slug (Optional)</label>
                            <input type="text" name="slug" id="slug" class="form-input" placeholder="Leave empty to auto-generate">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Banner Image</label>
                        <input type="file" name="banner_image" class="form-input" accept="image/*" onchange="previewImage(this)">
                        <img id="image-preview" class="preview-image" src="#" alt="Preview">
                        <div class="form-help">Recommended size: 1200x400px</div>
                    </div>

                    <hr style="border: 0; border-top: 1px solid var(--line); margin: 30px 0;">
                    <h3 style="margin-bottom: 20px; color: var(--text);">SEO Configuration</h3>

                    <div class="form-group">
                        <label class="form-label">Meta Title</label>
                        <input type="text" name="meta_title" class="form-input" placeholder="SEO Title">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Meta Description</label>
                        <textarea name="meta_desc" class="form-textarea" rows="3" placeholder="SEO Description"></textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Meta Keywords</label>
                        <input type="text" name="meta_keywords" class="form-input" placeholder="comma, separated, keywords">
                    </div>

                    <div class="form-group">
                        <label class="form-label">
                            <input type="checkbox" name="is_active" checked> Active
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary">Create Category</button>
                    <a href="index.php" class="btn" style="background:transparent; color: var(--muted); border: 1px solid var(--line); margin-left: 10px;">Cancel</a>
                </form>
            </div>
        </div>
    </main>

    <script>
        // Auto-generate slug
        document.getElementById('name').addEventListener('input', function() {
            if(!document.getElementById('slug').value) {
                // strict slug generation could be added here if dynamic feeling is desired, 
                // but PHP fallback is safer. 
                // minimal JS slug preview:
                let slug = this.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
                // document.getElementById('slug').placeholder = slug; 
            }
        });

        function previewImage(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    var preview = document.getElementById('image-preview');
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>
