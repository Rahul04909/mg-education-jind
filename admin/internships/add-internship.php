<?php
// Include database configuration
require_once __DIR__ . '/../../database/db-config.php';
$conn = getDbConnection();

$success_message = "";
$error_message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Basic Info
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    
    // Slug
    $slug = !empty($_POST['slug']) ? $_POST['slug'] : strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
    $slug = mysqli_real_escape_string($conn, $slug);

    // Details
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    
    // Duration
    $duration_value = isset($_POST['duration_value']) ? intval($_POST['duration_value']) : 0;
    $duration_type = mysqli_real_escape_string($conn, $_POST['duration_type']);

    // Fees (JSON)
    $fee_amount = isset($_POST['fee_amount']) ? $_POST['fee_amount'] : '';
    $fee_text = isset($_POST['fee_text']) ? $_POST['fee_text'] : '';
    $fees_data = [
        'amount' => $fee_amount,
        'description' => $fee_text
    ];
    $fees_json = mysqli_real_escape_string($conn, json_encode($fees_data));

    // SEO
    $meta_title = mysqli_real_escape_string($conn, $_POST['meta_title']);
    $meta_desc = mysqli_real_escape_string($conn, $_POST['meta_desc']);
    $meta_keywords = mysqli_real_escape_string($conn, $_POST['meta_keywords']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    // Image Upload
    $featured_image = "";
    if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] == 0) {
        $target_dir = "../../assets/uploads/internships/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $file_extension = pathinfo($_FILES["featured_image"]["name"], PATHINFO_EXTENSION);
        $new_filename = "internship_" . time() . "." . $file_extension;
        $target_file = $target_dir . $new_filename;
        
        if (move_uploaded_file($_FILES["featured_image"]["tmp_name"], $target_file)) {
            $featured_image = "assets/uploads/internships/" . $new_filename;
        } else {
            $error_message = "Error uploading image.";
        }
    }

    if (empty($error_message)) {
        $sql = "INSERT INTO internships (
            title, slug, description, 
            duration_value, duration_type,
            fees, featured_image,
            meta_title, meta_desc, meta_keywords, is_active
        ) VALUES (
            '$title', '$slug', '$description',
            $duration_value, '$duration_type',
            '$fees_json', '$featured_image',
            '$meta_title', '$meta_desc', '$meta_keywords', $is_active
        )";

        if ($conn->query($sql) === TRUE) {
            $success_message = "Internship added successfully!";
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
    <title>Add Internship - MG Education</title>
    
    <!-- TinyMCE JS (Cloud) -->
    <script src="https://cdn.tiny.cloud/1/zzf1ium270xgdjvayd6ocr6p0e7uej8ogum1kdm771lsz41d/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    
    <style>
        :root{--active:#22c55e;--indigo:#6f75ff;--line:#e6e8ee;--text:#0b1020;--muted:#6f7787;--error:#ef4444;--success:#22c55e;--info:#3b82f6}
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;background:linear-gradient(180deg,#f8fafc 0%,#ffffff 100%);color:var(--text)}
        .admin-content{margin-left:260px;min-height:100vh;padding:20px;transition:margin-left .25s ease}
        .admin-wrap{max-width:1200px;margin:0 auto}
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
        .form-input:focus, .form-textarea:focus, .form-select:focus{outline:none;border-color:var(--indigo);box-shadow:0 0 0 3px rgba(111,117,255,.1)}
        .btn{display:inline-flex;align-items:center;gap:8px;padding:12px 24px;border-radius:12px;font-weight:700;font-size:15px;border:none;cursor:pointer;transition:all .2s ease;text-decoration:none}
        .btn-primary{background:linear-gradient(135deg,var(--indigo) 0%,#5a5fff 100%);color:#fff}
        .grid-2{display:grid;grid-template-columns:1fr 1fr;gap:20px}
        .section-title { font-size: 18px; font-weight: 700; margin-bottom: 20px; border-bottom: 1px solid var(--line); padding-bottom: 10px; }
        .preview-image{max-width:200px;margin-top:10px;border-radius:8px;display:none}
    </style>
</head>
<body>
    <main class="admin-content">
        <div class="admin-wrap">
            <div class="page-header">
                <div class="breadcrumb">
                    <a href="../index.php">Dashboard</a> › <a href="index.php">Internships</a> › Add Internship
                </div>
                <h1 class="page-title">Add New Internship</h1>
                <p class="page-subtitle">Create a new internship opportunity</p>
            </div>

            <?php if (!empty($success_message)): ?>
            <div class="alert alert-success"><span>✓ <?php echo $success_message; ?></span></div>
            <?php endif; ?>

            <?php if (!empty($error_message)): ?>
            <div class="alert alert-error"><span>⚠ <?php echo $error_message; ?></span></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                
                <!-- Basic Info -->
                <div class="card">
                    <h3 class="section-title">Internship Information</h3>
                    <div class="grid-2">
                        <div class="form-group">
                            <label class="form-label">Internship Title *</label>
                            <input type="text" name="title" id="title" class="form-input" required placeholder="e.g., Web Development Internship">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Slug (Optional)</label>
                            <input type="text" name="slug" id="slug" class="form-input" placeholder="URL-friendly name">
                        </div>
                    </div>
                </div>

                <!-- Description (TinyMCE) -->
                <div class="card">
                    <h3 class="section-title">Description & Details</h3>
                    <div class="form-group">
                        <label class="form-label">Description</label>
                        <textarea name="description" id="description" rows="10"></textarea>
                    </div>
                </div>

                <!-- Duration & Fees -->
                <div class="card">
                    <h3 class="section-title">Duration & Fees</h3>
                    <div class="grid-2">
                        <div class="form-group">
                            <label class="form-label">Duration</label>
                            <div style="display: flex; gap: 10px;">
                                <input type="number" name="duration_value" class="form-input" placeholder="Value (e.g. 3)" required style="width: 120px;">
                                <select name="duration_type" class="form-select" required>
                                    <option value="Months">Months</option>
                                    <option value="Weeks">Weeks</option>
                                    <option value="Days">Days</option>
                                    <option value="Years">Years</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Fee Amount (INR)</label>
                            <input type="number" name="fee_amount" class="form-input" placeholder="0.00 (Leave empty for Free)">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Fee Note / Details</label>
                        <input type="text" name="fee_text" class="form-input" placeholder="e.g., Registration Fee Only">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Featured Image</label>
                        <input type="file" name="featured_image" class="form-input" accept="image/*" onchange="previewImage(this)">
                        <img id="image-preview" class="preview-image" src="#" alt="Preview">
                    </div>
                </div>

                <!-- SEO -->
                <div class="card">
                    <h3 class="section-title">SEO Configuration</h3>
                    <div class="grid-2">
                         <div class="form-group">
                            <label class="form-label">Meta Title</label>
                            <input type="text" name="meta_title" class="form-input" placeholder="SEO Title">
                        </div>
                         <div class="form-group">
                            <label class="form-label">Meta Keywords</label>
                            <input type="text" name="meta_keywords" class="form-input" placeholder="comma, separated, keywords">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Meta Description</label>
                        <textarea name="meta_desc" class="form-textarea" rows="2" placeholder="SEO Description"></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">
                            <input type="checkbox" name="is_active" checked> Active
                        </label>
                    </div>
                </div>

                <div style="margin-bottom: 50px;">
                    <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 16px; font-size: 16px;">Save Internship</button>
                </div>

            </form>
        </div>
    </main>

    <script>
        // Init TinyMCE
        document.addEventListener("DOMContentLoaded", function() {
            if (typeof tinymce !== 'undefined') {
                tinymce.init({
                    selector: '#description',
                    height: 300,
                    plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table help wordcount',
                    toolbar: 'undo redo | blocks | bold italic backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | removeformat | help'
                });
            } else {
                console.error("TinyMCE script not loaded.");
            }
        });

        // Slug Generation
        document.getElementById('title').addEventListener('input', function() {
            if(!document.getElementById('slug').value) {
                let slug = this.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
                // document.getElementById('slug').value = slug; // Optional: auto-fill visual field
            }
        });

        // Image Preview
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
