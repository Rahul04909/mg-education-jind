<?php
require_once __DIR__ . '/../../database/db-config.php';

$conn = getDbConnection();

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$center_id = intval($_GET['id']);
$success_message = "";
$error_message = "";

// Fetch existing data
$sql = "SELECT * FROM centers WHERE id = $center_id";
$result = $conn->query($sql);

if ($result->num_rows === 0) {
    die("Center not found");
}

$center = $result->fetch_assoc();

// Helper to handle file upload
function uploadFile($file, $dir) {
    if (!isset($file['name']) || $file['error'] != 0) return null;
    
    $target_dir = "../../assets/uploads/centers/" . $dir . "/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . "." . $ext;
    $target_file = $target_dir . $filename;
    
    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        return "assets/uploads/centers/" . $dir . "/" . $filename;
    }
    return null;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Basic
    $center_name = mysqli_real_escape_string($conn, $_POST['center_name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $mobile = mysqli_real_escape_string($conn, $_POST['mobile']);
    $owner_name = mysqli_real_escape_string($conn, $_POST['owner_name']);

    // Location
    $country = mysqli_real_escape_string($conn, $_POST['country']);
    $state = mysqli_real_escape_string($conn, $_POST['state']);
    $city = mysqli_real_escape_string($conn, $_POST['city']);
    $pincode = mysqli_real_escape_string($conn, $_POST['pincode']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);

    // Infrastructure
    $num_classrooms = intval($_POST['num_classrooms']);
    $num_computers = intval($_POST['num_computers']);
    $has_internet = isset($_POST['has_internet']) ? 1 : 0;
    $has_power_backup = isset($_POST['has_power_backup']) ? 1 : 0;
    $lab_type = mysqli_real_escape_string($conn, $_POST['lab_type']);
    $working_hours_from = mysqli_real_escape_string($conn, $_POST['working_hours_from']);
    $working_hours_to = mysqli_real_escape_string($conn, $_POST['working_hours_to']);
    $total_staff = intval($_POST['total_staff']);
    $weekend_off = isset($_POST['weekend_off']) ? json_encode($_POST['weekend_off']) : json_encode([]);

    // Franchise
    $franchise_fee = floatval($_POST['franchise_fee']);
    $royalty_percentage = floatval($_POST['royalty_percentage']);

    // Social
    $social_links = json_encode([
        'facebook' => $_POST['facebook'],
        'instagram' => $_POST['instagram'],
        'youtube' => $_POST['youtube'],
        'linkedin' => $_POST['linkedin']
    ]);
    
    // Status
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    // Handle Media Uploads (Keep old if new not uploaded)
    $center_logo = uploadFile($_FILES['center_logo'], 'logos') ?? $center['center_logo'];
    $owner_image = uploadFile($_FILES['owner_image'], 'owners') ?? $center['owner_image'];
    $signatory = uploadFile($_FILES['authorized_signatory'], 'signatories') ?? $center['authorized_signatory'];
    $stamp = uploadFile($_FILES['digital_stamp'], 'stamps') ?? $center['digital_stamp'];

    // Handle Legal Documents (Existing + New)
    // For simplicity in edit, we'll just handle new uploads and append, 
    // or replace if we implement a complex UI. Here we will keep existing logic simpler:
    // We will parse existing docs, and allow adding new ones. To delete, we need a separate mechanism or UI.
    // For this task, let's keep it simple: Re-save everything sent in the form.
    // Ideally, we should list existing docs and allow keeping/removing.
    // Let's assume the user re-enters docs or we rely on a JSON hidden field.
    // BETTER APPROACH: Only add new docs to the existing array.
    
    $current_docs = json_decode($center['legal_documents'], true) ?? [];
    $new_docs = [];

    if (isset($_POST['doc_names'])) {
        foreach ($_POST['doc_names'] as $index => $name) {
            if (empty($name)) continue;
            
            $doc_number = $_POST['doc_numbers'][$index] ?? '';
            $file_path = '';
            
            // Handle file upload
            if (isset($_FILES['doc_files']['name'][$index]) && $_FILES['doc_files']['error'][$index] == 0) {
                 $file = [
                    'name' => $_FILES['doc_files']['name'][$index],
                    'type' => $_FILES['doc_files']['type'][$index],
                    'tmp_name' => $_FILES['doc_files']['tmp_name'][$index],
                    'error' => $_FILES['doc_files']['error'][$index],
                    'size' => $_FILES['doc_files']['size'][$index]
                ];
                $file_path = uploadFile($file, 'documents');
            }
            
            if ($file_path) {
                $new_docs[] = ['name' => $name, 'number' => $doc_number, 'file' => $file_path];
            }
        }
    }
    
    // Merge: For now, if no mechanism to remove, we just Append. 
    // If you want to replace, we'd need to clear old ones. 
    // Constructive merge is safest for "Add New".
    $final_docs = array_merge($current_docs, $new_docs);
    $legal_docs_json = mysqli_real_escape_string($conn, json_encode($final_docs));

    // Update SQL
    $sql = "UPDATE centers SET 
        center_name='$center_name', email='$email', mobile='$mobile', owner_name='$owner_name',
        country='$country', state='$state', city='$city', pincode='$pincode', address='$address',
        num_classrooms=$num_classrooms, num_computers=$num_computers, has_internet=$has_internet, 
        has_power_backup=$has_power_backup, lab_type='$lab_type', working_hours_from='$working_hours_from', 
        working_hours_to='$working_hours_to', total_staff=$total_staff, weekend_off='$weekend_off',
        franchise_fee=$franchise_fee, royalty_percentage=$royalty_percentage,
        social_links='$social_links', center_logo='$center_logo', owner_image='$owner_image',
        authorized_signatory='$signatory', digital_stamp='$stamp', is_active=$is_active,
        legal_documents='$legal_docs_json'
        WHERE id=$center_id";

    if ($conn->query($sql) === TRUE) {
        $success_message = "Center updated successfully!";
        // Refresh data
        $result = $conn->query("SELECT * FROM centers WHERE id = $center_id");
        $center = $result->fetch_assoc();
    } else {
        $error_message = "Error: " . $conn->error;
    }
}

// Data Prep for View
$weekend_off_arr = json_decode($center['weekend_off'], true) ?? [];
$social_arr = json_decode($center['social_links'], true) ?? [];
$docs_arr = json_decode($center['legal_documents'], true) ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Center - MG Skills</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root{--active:#22c55e;--indigo:#6f75ff;--line:#e6e8ee;--text:#0b1020;--muted:#6f7787;--error:#ef4444;--success:#22c55e;}
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Outfit',sans-serif;background:linear-gradient(180deg,#f8fafc 0%,#ffffff 100%);color:var(--text)}
        .admin-content{margin-left:260px;min-height:100vh;padding:32px;transition:margin-left .25s ease}
        body.sidebar-collapsed .admin-content{margin-left:80px}
        .admin-wrap{max-width:1100px;margin:0 auto}
        
        .page-header{margin-bottom:32px}
        .breadcrumb{color:var(--muted);font-size:14px;margin-bottom:4px}
        .breadcrumb a{color:var(--indigo);text-decoration:none}
        .page-title{font-size:28px;font-weight:800;color:var(--text)}

        .card{background:#fff;border:1px solid var(--line);border-radius:16px;padding:32px;box-shadow:0 4px 12px rgba(0,0,0,.04);margin-bottom:24px}
        .section-title{font-size:18px;font-weight:700;color:var(--indigo);margin-bottom:24px;border-bottom:1px solid var(--line);padding-bottom:12px}
        
        .form-group{margin-bottom:24px}
        .form-label{display:block;font-weight:600;color:#334155;margin-bottom:8px;font-size:14px}
        .form-input,.form-select{width:100%;padding:12px 16px;border:1px solid var(--line);border-radius:10px;font-size:15px;transition:all .2s;font-family:inherit;background:#fff}
        .form-input:focus{border-color:var(--indigo);box-shadow:0 0 0 3px rgba(111,117,255,.1);outline:none}
        
        .grid-2{display:grid;grid-template-columns:1fr 1fr;gap:24px}
        .grid-3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:24px}
        
        .checkbox-group{display:flex;gap:16px;flex-wrap:wrap}
        .checkbox-item{display:flex;align-items:center;gap:8px;font-size:14px;cursor:pointer}
        
        .btn{display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:14px 28px;border-radius:10px;font-weight:600;font-size:15px;border:none;cursor:pointer;transition:all .2s}
        .btn-primary{background:var(--indigo);color:#fff}
        .btn-primary:hover{opacity:0.9;transform:translateY(-1px)}
        
        .current-file{margin-top:8px;font-size:13px;color:var(--muted);display:flex;align-items:center;gap:6px}
        .current-file img{width:40px;height:40px;border-radius:6px;object-fit:cover;border:1px solid var(--line)}
        
        .alert{padding:16px;border-radius:12px;margin-bottom:24px;border:1px solid;font-weight:500}
        .alert-success{background:#dcfce7;border-color:#bbf7d0;color:#166534}
        .alert-error{background:#fee2e2;border-color:#fecaca;color:#991b1b}
        
        @media(max-width:768px){.grid-2,.grid-3{grid-template-columns:1fr}.admin-content{margin-left:80px;padding:20px}}
    </style>
</head>
<body>
    <?php include __DIR__ . '/../sidebar.php'; ?>
    
    <main class="admin-content">
        <div class="admin-wrap">
            <div class="page-header">
                <div class="breadcrumb"><a href="index.php">Centers</a> › Edit</div>
                <h1 class="page-title">Edit Center: <?php echo htmlspecialchars($center['center_name']); ?></h1>
            </div>

            <?php if ($success_message): ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php endif; ?>
            <?php if ($error_message): ?>
                <div class="alert alert-error"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                
                <!-- Basic -->
                <div class="card">
                    <h3 class="section-title">Basic Details</h3>
                    <div class="grid-2">
                        <div class="form-group">
                            <label class="form-label">Center Name</label>
                            <input type="text" name="center_name" class="form-input" value="<?php echo htmlspecialchars($center['center_name']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Owner Name</label>
                            <input type="text" name="owner_name" class="form-input" value="<?php echo htmlspecialchars($center['owner_name']); ?>" required>
                        </div>
                    </div>
                    <div class="grid-2">
                        <div class="form-group">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-input" value="<?php echo htmlspecialchars($center['email']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Mobile</label>
                            <input type="text" name="mobile" class="form-input" value="<?php echo htmlspecialchars($center['mobile']); ?>" required>
                        </div>
                    </div>
                     <div class="form-group">
                        <label class="form-label">Status</label>
                        <div class="checkbox-group">
                            <label class="checkbox-item">
                                <input type="checkbox" name="is_active" value="1" <?php echo $center['is_active'] ? 'checked' : ''; ?>> 
                                Active Center
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Location -->
                <div class="card">
                    <h3 class="section-title">Location</h3>
                    <div class="grid-3">
                        <div class="form-group">
                            <label class="form-label">City</label>
                            <input type="text" name="city" class="form-input" value="<?php echo htmlspecialchars($center['city']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">State</label>
                            <input type="text" name="state" class="form-input" value="<?php echo htmlspecialchars($center['state']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Pincode</label>
                            <input type="text" name="pincode" class="form-input" value="<?php echo htmlspecialchars($center['pincode']); ?>" required>
                        </div>
                    </div>
                    <div class="grid-2">
                        <div class="form-group">
                            <label class="form-label">Country</label>
                            <input type="text" name="country" class="form-input" value="<?php echo htmlspecialchars($center['country']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Address</label>
                            <input type="text" name="address" class="form-input" value="<?php echo htmlspecialchars($center['address']); ?>" required>
                        </div>
                    </div>
                </div>

                <!-- Infrastructure -->
                <div class="card">
                    <h3 class="section-title">Infrastructure</h3>
                    <div class="grid-3">
                        <div class="form-group">
                            <label class="form-label">Classrooms</label>
                            <input type="number" name="num_classrooms" class="form-input" value="<?php echo $center['num_classrooms']; ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Computers</label>
                            <input type="number" name="num_computers" class="form-input" value="<?php echo $center['num_computers']; ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Total Staff</label>
                            <input type="number" name="total_staff" class="form-input" value="<?php echo $center['total_staff']; ?>">
                        </div>
                    </div>
                    <div class="grid-3">
                        <div class="form-group">
                            <label class="form-label">Lab Type</label>
                            <select name="lab_type" class="form-select">
                                <option value="Basic" <?php echo $center['lab_type'] == 'Basic' ? 'selected' : ''; ?>>Basic</option>
                                <option value="Advanced" <?php echo $center['lab_type'] == 'Advanced' ? 'selected' : ''; ?>>Advanced</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Hours From</label>
                            <input type="time" name="working_hours_from" class="form-input" value="<?php echo $center['working_hours_from']; ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Hours To</label>
                            <input type="time" name="working_hours_to" class="form-input" value="<?php echo $center['working_hours_to']; ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Amenities</label>
                        <div class="checkbox-group">
                            <label class="checkbox-item"><input type="checkbox" name="has_internet" value="1" <?php echo $center['has_internet'] ? 'checked' : ''; ?>> Internet</label>
                            <label class="checkbox-item"><input type="checkbox" name="has_power_backup" value="1" <?php echo $center['has_power_backup'] ? 'checked' : ''; ?>> Power Backup</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Weekend Off</label>
                        <div class="checkbox-group">
                            <?php $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                            foreach($days as $day): 
                                $checked = in_array($day, $weekend_off_arr) ? 'checked' : '';
                            ?>
                                <label class="checkbox-item"><input type="checkbox" name="weekend_off[]" value="<?php echo $day; ?>" <?php echo $checked; ?>> <?php echo $day; ?></label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Financials -->
                <div class="card">
                     <h3 class="section-title">Franchise Details</h3>
                     <div class="grid-2">
                        <div class="form-group">
                            <label class="form-label">Franchise Fee</label>
                            <input type="number" name="franchise_fee" class="form-input" value="<?php echo $center['franchise_fee']; ?>" step="0.01">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Royalty (%)</label>
                            <input type="number" name="royalty_percentage" class="form-input" value="<?php echo $center['royalty_percentage']; ?>" step="0.01">
                        </div>
                     </div>
                </div>

                <!-- Social -->
                 <div class="card">
                     <h3 class="section-title">Social Links</h3>
                     <div class="grid-2">
                        <div class="form-group">
                            <label class="form-label">Facebook</label>
                            <input type="url" name="facebook" class="form-input" value="<?php echo htmlspecialchars($social_arr['facebook'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Instagram</label>
                            <input type="url" name="instagram" class="form-input" value="<?php echo htmlspecialchars($social_arr['instagram'] ?? ''); ?>">
                        </div>
                         <div class="form-group">
                            <label class="form-label">YouTube</label>
                            <input type="url" name="youtube" class="form-input" value="<?php echo htmlspecialchars($social_arr['youtube'] ?? ''); ?>">
                        </div>
                         <div class="form-group">
                            <label class="form-label">LinkedIn</label>
                            <input type="url" name="linkedin" class="form-input" value="<?php echo htmlspecialchars($social_arr['linkedin'] ?? ''); ?>">
                        </div>
                     </div>
                 </div>

                <!-- Media -->
                <div class="card">
                    <h3 class="section-title">Media</h3>
                    <div class="grid-2">
                        <div class="form-group">
                            <label class="form-label">Center Logo</label>
                            <input type="file" name="center_logo" class="form-input">
                            <?php if($center['center_logo']): ?>
                                <div class="current-file">
                                    <img src="../../<?php echo $center['center_logo']; ?>">
                                    <span>Current: Logo</span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Owner Image</label>
                            <input type="file" name="owner_image" class="form-input">
                             <?php if($center['owner_image']): ?>
                                <div class="current-file">
                                    <img src="../../<?php echo $center['owner_image']; ?>">
                                    <span>Current: Owner</span>
                                </div>
                            <?php endif; ?>
                        </div>
                         <div class="form-group">
                            <label class="form-label">Authorized Signatory</label>
                            <input type="file" name="authorized_signatory" class="form-input">
                             <?php if($center['authorized_signatory']): ?>
                                <div class="current-file">
                                    <img src="../../<?php echo $center['authorized_signatory']; ?>">
                                    <span>Current: Signatory</span>
                                </div>
                            <?php endif; ?>
                        </div>
                         <div class="form-group">
                            <label class="form-label">Digital Stamp</label>
                            <input type="file" name="digital_stamp" class="form-input">
                             <?php if($center['digital_stamp']): ?>
                                <div class="current-file">
                                    <img src="../../<?php echo $center['digital_stamp']; ?>">
                                    <span>Current: Stamp</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Docs Upload (Append) -->
                <div class="card">
                    <h3 class="section-title">Add New Documents</h3>
                    <p style="margin-bottom:12px;color:var(--muted);font-size:13px">Existing documents are preserved. Upload new ones below.</p>
                     <div class="form-group">
                         <div class="doc-row" style="display: flex; gap: 10px; margin-bottom: 10px;">
                            <input type="text" name="doc_names[]" class="form-input" placeholder="Doc Name (e.g., GST)">
                            <input type="text" name="doc_numbers[]" class="form-input" placeholder="Doc Number">
                            <input type="file" name="doc_files[]" class="form-input">
                         </div>
                     </div>
                     <button type="button" class="btn btn-primary" onclick="alert('For multiple docs, please save and add again.')">Simple Add Mode</button>
                     
                     <div style="margin-top:20px;">
                        <label class="form-label">Existing Documents (read-only):</label>
                        <ul style="padding-left:20px;color:var(--text);font-size:14px;">
                            <?php foreach($docs_arr as $doc): ?>
                                <li><?php echo htmlspecialchars($doc['name']); ?> (<?php echo htmlspecialchars($doc['number']); ?>) <a href="../../<?php echo $doc['file']; ?>" target="_blank" style="color:var(--indigo)">View</a></li>
                            <?php endforeach; ?>
                        </ul>
                     </div>
                </div>

                <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;padding:16px;font-size:16px">Update Center Details</button>

            </form>
        </div>
    </main>
</body>
</html>
<?php $conn->close(); ?>
