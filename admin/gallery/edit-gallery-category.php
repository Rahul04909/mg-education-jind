<?php
// Include database configuration
require_once __DIR__ . '/../../database/db-config.php';
$conn = getDbConnection();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$category = null;

if ($id) {
    $res = $conn->query("SELECT * FROM gallery_categories WHERE id = $id");
    if ($res->num_rows > 0) {
        $category = $res->fetch_assoc();
    }
}

if (!$category) {
    header("Location: manage-gallery-category.php");
    exit;
}

$success_message = "";
$error_message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $slug = mysqli_real_escape_string($conn, $_POST['slug']);
    if (empty($slug)) {
         $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name)));
    }
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    // Check slug uniqueness (excluding current id)
    $check = $conn->query("SELECT id FROM gallery_categories WHERE slug = '$slug' AND id != $id");
    if ($check->num_rows > 0) {
        $error_message = "Category with this slug already exists.";
    } else {
        $sql = "UPDATE gallery_categories SET name='$name', slug='$slug', is_active=$is_active WHERE id=$id";

        if ($conn->query($sql) === TRUE) {
            $success_message = "Gallery Category updated successfully!";
            // Update local object
            $category['name'] = $name;
            $category['slug'] = $slug;
            $category['is_active'] = $is_active;
        } else {
            $error_message = "Database Error: " . $conn->error;
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
    <title>Edit Gallery Category - MG Education</title>
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
    </style>
</head>
<body>
    <main class="admin-content">
        <div class="admin-wrap">
            <div class="page-header">
                <div class="breadcrumb">
                    <a href="../index.php">Dashboard</a> › <a href="manage-gallery-category.php">Gallery</a> › Edit Category
                </div>
                <h1 class="page-title">Edit Gallery Category</h1>
            </div>

            <?php if (!empty($success_message)): ?>
            <div class="alert alert-success"><span>✓ <?php echo $success_message; ?></span></div>
            <?php endif; ?>

            <?php if (!empty($error_message)): ?>
            <div class="alert alert-error"><span>⚠ <?php echo $error_message; ?></span></div>
            <?php endif; ?>

            <form method="POST">
                <div class="card">
                    <div class="form-group">
                        <label class="form-label">Category Name *</label>
                        <input type="text" name="name" id="name" class="form-input" required value="<?php echo htmlspecialchars($category['name']); ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Slug</label>
                        <input type="text" name="slug" id="slug" class="form-input" value="<?php echo htmlspecialchars($category['slug']); ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">
                            <input type="checkbox" name="is_active" <?php echo $category['is_active'] ? 'checked' : ''; ?>> Active
                        </label>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Update Category</button>
                </div>
            </form>
        </div>
    </main>
</body>
</html>
<?php $conn->close(); ?>
