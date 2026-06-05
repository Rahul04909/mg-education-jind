<?php
// Include database configuration
require_once __DIR__ . '/../../database/db-config.php';
// Ensure schema is updated
require_once __DIR__ . '/../../database/update_internship_study_material_schema.php';

$conn = getDbConnection();
$success_message = '';
$error_message = '';

// Handle Actions
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $internship_id = intval($_POST['internship_id']);
        $title = mysqli_real_escape_string($conn, $_POST['title']);
        $description = mysqli_real_escape_string($conn, $_POST['description']);
        $file_path = '';

        // Handle File Upload
        if (isset($_FILES['study_file']) && $_FILES['study_file']['error'] === 0) {
            $target_dir = "../../assets/uploads/internship_study_materials/";
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            $ext = strtolower(pathinfo($_FILES["study_file"]["name"], PATHINFO_EXTENSION));
            if ($ext !== 'pdf') {
                $error_message = "Only PDF files are allowed for study materials.";
            } else {
                $filename = "MAT_" . time() . "_" . uniqid() . ".pdf";
                $target_file = $target_dir . $filename;
                if (move_uploaded_file($_FILES["study_file"]["tmp_name"], $target_file)) {
                    @chmod($target_file, 0644);
                    $file_path = "assets/uploads/internship_study_materials/" . $filename;
                } else {
                    $error_message = "Failed to move uploaded file.";
                }
            }
        } else {
            $error_message = "Please upload a valid PDF study material file.";
        }

        if (empty($error_message)) {
            $sql = "INSERT INTO internship_study_material (internship_id, title, description, file_path) 
                    VALUES ($internship_id, '$title', '$description', '$file_path')";
            
            if ($conn->query($sql) === TRUE) {
                $success_message = "Study material uploaded successfully!";
            } else {
                // Cleanup file if DB insert failed
                if (!empty($file_path) && file_exists('../../' . $file_path)) {
                    @unlink('../../' . $file_path);
                }
                $error_message = "Error saving to database: " . $conn->error;
            }
        }

    } elseif ($action === 'edit') {
        $id = intval($_POST['material_id']);
        $internship_id = intval($_POST['internship_id']);
        $title = mysqli_real_escape_string($conn, $_POST['title']);
        $description = mysqli_real_escape_string($conn, $_POST['description']);

        // Fetch current file path
        $stmt = $conn->prepare("SELECT file_path FROM internship_study_material WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $curr_mat = $stmt->get_result()->fetch_assoc();
        $file_path = $curr_mat['file_path'] ?? '';

        // Check if a new file is uploaded
        if (isset($_FILES['study_file']) && $_FILES['study_file']['error'] === 0) {
            $target_dir = "../../assets/uploads/internship_study_materials/";
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            $ext = strtolower(pathinfo($_FILES["study_file"]["name"], PATHINFO_EXTENSION));
            if ($ext !== 'pdf') {
                $error_message = "Only PDF files are allowed for study materials.";
            } else {
                $filename = "MAT_" . time() . "_" . uniqid() . ".pdf";
                $target_file = $target_dir . $filename;
                if (move_uploaded_file($_FILES["study_file"]["tmp_name"], $target_file)) {
                    @chmod($target_file, 0644);
                    
                    // Delete old file
                    if (!empty($curr_mat['file_path']) && file_exists('../../' . $curr_mat['file_path'])) {
                        @unlink('../../' . $curr_mat['file_path']);
                    }
                    $file_path = "assets/uploads/internship_study_materials/" . $filename;
                } else {
                    $error_message = "Failed to move new uploaded file.";
                }
            }
        }

        if (empty($error_message)) {
            $sql = "UPDATE internship_study_material SET 
                    internship_id=$internship_id, 
                    title='$title', 
                    description='$description', 
                    file_path='$file_path' 
                    WHERE id=$id";

            if ($conn->query($sql) === TRUE) {
                $success_message = "Study material updated successfully!";
            } else {
                $error_message = "Error updating database: " . $conn->error;
            }
        }

    } elseif ($action === 'delete') {
        $id = intval($_POST['material_id']);
        
        // Fetch current file path for cleanup
        $stmt = $conn->prepare("SELECT file_path FROM internship_study_material WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $curr_mat = $stmt->get_result()->fetch_assoc();

        $sql = "DELETE FROM internship_study_material WHERE id=$id";
        if ($conn->query($sql) === TRUE) {
            // Delete physical file
            if ($curr_mat && !empty($curr_mat['file_path']) && file_exists('../../' . $curr_mat['file_path'])) {
                @unlink('../../' . $curr_mat['file_path']);
            }
            $success_message = "Study material deleted successfully!";
        } else {
            $error_message = "Error deleting record: " . $conn->error;
        }
    }
}

// Fetch Internships for Dropdown and Filters
$internships_result = $conn->query("SELECT id, title FROM internships WHERE is_active = 1 ORDER BY title ASC");
$internships = [];
while ($row = $internships_result->fetch_assoc()) {
    $internships[] = $row;
}

// Filter Logic
$selected_internship_id = isset($_GET['internship_id']) ? intval($_GET['internship_id']) : 0;

// Fetch Study Materials List
$sql_materials = "SELECT mat.*, i.title as internship_title 
                  FROM internship_study_material mat 
                  JOIN internships i ON mat.internship_id = i.id";

if ($selected_internship_id > 0) {
    $sql_materials .= " WHERE mat.internship_id = $selected_internship_id";
}

$sql_materials .= " ORDER BY i.title ASC, mat.created_at DESC";
$result_materials = $conn->query($sql_materials);

include __DIR__ . "/../sidebar.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Study Materials - MG Education</title>
    
    <!-- CKEditor -->
    <script src="../../vendor/ckeditor/ckeditor/ckeditor.js"></script>

    <style>
        :root{--active:#22c55e;--indigo:#6f75ff;--line:#e6e8ee;--text:#0b1020;--muted:#6f7787;--error:#ef4444;--success:#22c55e}
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;background:linear-gradient(180deg,#f8fafc 0%,#ffffff 100%);color:var(--text)}
        .admin-content{margin-left:260px;min-height:100vh;padding:20px;transition:margin-left .25s ease}
        body.sidebar-collapsed .admin-content{margin-left:88px}
        .page-header{margin-bottom:30px; display:flex; justify-content:space-between; align-items:center}
        .page-title{font-size:32px;font-weight:800;color:var(--text);margin-bottom:8px}
        .card{background:#fff;border:1px solid var(--line);border-radius:18px;padding:30px;box-shadow:0 4px 12px rgba(0,0,0,.05);margin-bottom:20px}
        .form-group{margin-bottom:20px}
        .form-label{display:block;font-weight:700;color:var(--text);margin-bottom:8px;font-size:14px}
        .form-input, .form-select, .form-textarea{width:100%;padding:12px 16px;border:1px solid var(--line);border-radius:10px;font-size:15px;transition:all .2s ease;font-family:inherit;background:#fff}
        .form-input:focus, .form-select:focus, .form-textarea:focus{outline:none;border-color:var(--indigo);box-shadow:0 0 0 3px rgba(111,117,255,.1)}
        .btn{display:inline-flex;align-items:center;gap:8px;padding:12px 24px;border-radius:12px;font-weight:700;font-size:14px;border:none;cursor:pointer;transition:all .2s ease;text-decoration:none}
        .btn-primary{background:linear-gradient(135deg,var(--indigo) 0%,#5a5fff 100%);color:#fff}
        .btn-danger{background:var(--error);color:#fff}
        .btn-sm{padding:8px 16px; font-size:13px;}
        .alert{padding:16px 20px;border-radius:12px;margin-bottom:20px;display:flex;align-items:center;gap:12px;border:1px solid}
        .alert-success{background:#d1fae5;border-color:#86efac;color:#065f46}
        .alert-error{background:#fee2e2;border-color:#fca5a5;color:#991b1b}
        .table{width:100%;border-collapse:collapse;margin-top:20px}
        .table th{text-align:left;padding:16px;background:#f8fafc;font-size:13px;font-weight:700;border-bottom:1px solid var(--line)}
        .table td{padding:16px;border-bottom:1px solid var(--line);font-size:14px;vertical-align:middle}
        
        /* Modal Styles */
        .modal {display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.5);}
        .modal-content {background-color: #fefefe; margin: 5% auto; padding: 20px; border: 1px solid #888; width: 750px; border-radius: 12px; position: relative;}
        .close {color: #aaa; float: right; font-size: 28px; font-weight: bold; cursor: pointer;}
        .close:hover {color: black;}
        .cke_notifications_area { display: none !important; }
    </style>
</head>
<body>
    <main class="admin-content">
        <div class="page-header">
            <div>
                <div style="font-size:14px;color:var(--muted);margin-bottom:10px">
                    <a href="../index.php" style="text-decoration:none;color:var(--indigo)">Dashboard</a> › <a href="index.php" style="text-decoration:none;color:var(--indigo)">Internships</a> › Manage Study Materials
                </div>
                <h1 class="page-title">Manage Study Materials</h1>
                <p style="color:var(--muted)">Upload and manage PDF learning resources for interns</p>
            </div>
            <div>
                <button class="btn btn-primary" onclick="openAddModal()">+ Add Study Material</button>
            </div>
        </div>

        <?php if (!empty($success_message)): ?>
        <div class="alert alert-success"><span>✓ <?php echo $success_message; ?></span></div>
        <?php endif; ?>

        <?php if (!empty($error_message)): ?>
        <div class="alert alert-error"><span>⚠ <?php echo $error_message; ?></span></div>
        <?php endif; ?>

        <!-- Filter -->
        <div class="card" style="padding: 20px;">
            <form method="GET" style="display:flex; align-items:center; gap:20px;">
                <div style="flex:1">
                    <label class="form-label" style="margin-bottom: 5px;">Filter by Internship:</label>
                    <select name="internship_id" class="form-select" onchange="this.form.submit()">
                        <option value="">All Internships</option>
                        <?php foreach($internships as $intern): ?>
                            <option value="<?php echo $intern['id']; ?>" <?php echo $selected_internship_id == $intern['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($intern['title']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="padding-top:20px;">
                    <a href="manage-study-material.php" class="btn btn-sm" style="background:#f1f5f9; color:var(--text)">Reset</a>
                </div>
            </form>
        </div>

        <!-- Materials List -->
        <div class="card">
            <h3 style="font-size:18px; font-weight:700; margin-bottom:15px;">Uploaded Study Materials</h3>
            <table class="table">
                <thead>
                    <tr>
                        <th style="width: 25%;">Material Title</th>
                        <th style="width: 30%;">Internship</th>
                        <th style="width: 20%;">Attachment</th>
                        <th style="width: 15%;">Description</th>
                        <th style="text-align:right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result_materials && $result_materials->num_rows > 0): ?>
                        <?php while($row = $result_materials->fetch_assoc()): ?>
                        <tr>
                            <td><div style="font-weight:700"><?php echo htmlspecialchars($row['title']); ?></div></td>
                            <td><span style="font-weight:600; color:var(--indigo)"><?php echo htmlspecialchars($row['internship_title']); ?></span></td>
                            <td>
                                <?php if(!empty($row['file_path'])): ?>
                                    <a href="../../<?php echo $row['file_path']; ?>" target="_blank" style="display:inline-flex; align-items:center; gap:6px; color:var(--indigo); text-decoration:none; font-weight:600;">
                                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                                        View PDF
                                    </a>
                                <?php else: ?>
                                    <span style="color:var(--muted)">No file</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php echo mb_strimwidth(strip_tags($row['description']), 0, 50, "..."); ?>
                            </td>
                            <td style="text-align:right">
                                <button class="btn btn-sm btn-primary" style="background:none; color:var(--indigo); border:1px solid var(--line)" 
                                    onclick='openEditModal(<?php echo json_encode([
                                        "id" => $row["id"], 
                                        "internship_id" => $row["internship_id"], 
                                        "title" => $row["title"],
                                        "description" => $row["description"] 
                                    ]); ?>)'>Edit</button>
                                
                                <form method="POST" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this study material?');">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="material_id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-danger" style="background:none; color:var(--error); border:none">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="5" style="text-align:center;padding:40px;color:var(--muted)">No study materials found. Click Add to upload one.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>

    <!-- Add/Edit Modal -->
    <div id="materialModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2 id="modalTitle" style="margin-bottom:20px;">Add Study Material</h2>
            <form method="POST" id="materialForm" enctype="multipart/form-data">
                <input type="hidden" name="action" id="formAction" value="add">
                <input type="hidden" name="material_id" id="materialId">

                <div class="form-group">
                    <label class="form-label">Select Internship *</label>
                    <select name="internship_id" id="internship_id" class="form-select" required>
                        <option value="">-- Select Internship --</option>
                        <?php foreach($internships as $intern): ?>
                            <option value="<?php echo $intern['id']; ?>">
                                <?php echo htmlspecialchars($intern['title']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Material Title *</label>
                    <input type="text" name="title" id="title" class="form-input" required placeholder="e.g. Unit 1: Introduction to PHP Programming">
                </div>

                <div class="form-group">
                    <label class="form-label">Upload PDF File <span id="fileRequiredStar">*</span></label>
                    <input type="file" name="study_file" id="study_file" class="form-input" accept="application/pdf">
                    <p style="font-size:12px; color:var(--muted); margin-top:4px;">Only PDF format is supported. Max file size: 20MB.</p>
                </div>

                <div class="form-group">
                    <label class="form-label">Description / Details</label>
                    <textarea name="description" id="description" rows="10"></textarea>
                </div>

                <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center;">Save Study Material</button>
            </form>
        </div>
    </div>

    <script>
        const modal = document.getElementById("materialModal");
        
        // Initialize CKEditor
        let editorInstance = null;
        window.addEventListener('load', function() {
            editorInstance = CKEDITOR.replace('description', {
                versionCheck: false
            });
        });

        function openAddModal() {
            document.getElementById("modalTitle").innerText = "Add Study Material";
            document.getElementById("formAction").value = "add";
            document.getElementById("materialId").value = "";
            document.getElementById("internship_id").value = "";
            document.getElementById("title").value = "";
            document.getElementById("study_file").required = true;
            document.getElementById("fileRequiredStar").style.display = "inline";
            if (editorInstance) {
                editorInstance.setData("");
            }
            modal.style.display = "block";
        }

        function openEditModal(data) {
            document.getElementById("modalTitle").innerText = "Edit Study Material";
            document.getElementById("formAction").value = "edit";
            document.getElementById("materialId").value = data.id;
            document.getElementById("internship_id").value = data.internship_id;
            document.getElementById("title").value = data.title;
            document.getElementById("study_file").required = false;
            document.getElementById("fileRequiredStar").style.display = "none";
            if (editorInstance) {
                editorInstance.setData(data.description);
            } else {
                document.getElementById("description").value = data.description;
            }
            modal.style.display = "block";
        }

        function closeModal() {
            modal.style.display = "none";
        }

        // Close modal when clicking outside content
        window.onclick = function(event) {
            if (event.target == modal) {
                closeModal();
            }
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>
