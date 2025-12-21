<?php
// Include database and PHPMailer
require_once __DIR__ . '/../../database/db-config.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$conn = getDbConnection();

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$success_message = "";
$error_message = "";

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

    // Generate Center ID: MGI-YYYY-CTR-XX
    $currentYear = date("Y");
    $result_id = $conn->query("SELECT id FROM centers ORDER BY id DESC LIMIT 1");
    $last_id = 0;
    if ($result_id->num_rows > 0) {
        $row_id = $result_id->fetch_assoc();
        $last_id = $row_id['id'];
    }
    $next_sequence = str_pad($last_id + 1, 2, "0", STR_PAD_LEFT);
    $center_code = "MGI-{$currentYear}-CTR-{$next_sequence}";

    // Generate Password
    $raw_password = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ@#$!"), 0, 10);
    $hashed_password = password_hash($raw_password, PASSWORD_BCRYPT);

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

    // Handle Media Uploads
    $center_logo = uploadFile($_FILES['center_logo'], 'logos');
    $owner_image = uploadFile($_FILES['owner_image'], 'owners');
    $signatory = uploadFile($_FILES['authorized_signatory'], 'signatories');
    $stamp = uploadFile($_FILES['digital_stamp'], 'stamps');

    // Handle Legal Documents (Multiple)
    $legal_docs = [];
    if (isset($_POST['doc_names'])) {
        foreach ($_POST['doc_names'] as $index => $name) {
            $doc_number = $_POST['doc_numbers'][$index] ?? '';
            $file_path = '';
            
            // Handle file upload for this index
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

            if ($name && $doc_number) {
                $legal_docs[] = [
                    'name' => $name,
                    'number' => $doc_number,
                    'file' => $file_path
                ];
            }
        }
    }
    $legal_docs_json = mysqli_real_escape_string($conn, json_encode($legal_docs));

    // SQL Insert
    $sql = "INSERT INTO centers (
        center_code, password, center_name, email, mobile, owner_name,
        country, state, city, pincode, address,
        num_classrooms, num_computers, has_internet, has_power_backup, lab_type,
        working_hours_from, working_hours_to, total_staff, weekend_off,
        legal_documents, franchise_fee, royalty_percentage,
        social_links, center_logo, owner_image, authorized_signatory, digital_stamp
    ) VALUES (
        '$center_code', '$hashed_password', '$center_name', '$email', '$mobile', '$owner_name',
        '$country', '$state', '$city', '$pincode', '$address',
        $num_classrooms, $num_computers, $has_internet, $has_power_backup, '$lab_type',
        '$working_hours_from', '$working_hours_to', $total_staff, '$weekend_off',
        '$legal_docs_json', $franchise_fee, $royalty_percentage,
        '$social_links', '$center_logo', '$owner_image', '$signatory', '$stamp'
    )";

    if ($conn->query($sql) === TRUE) {
        $success_message = "Center added successfully! Center Code: <strong>$center_code</strong>";
        
        // Send Welcome Email
        // Fetch SMTP Settings
        $smtp_sql = "SELECT * FROM smtp_settings WHERE id = 1 AND is_active = 1";
        $smtp_result = $conn->query($smtp_sql);
        
        if ($smtp_result->num_rows > 0) {
            $smtp = $smtp_result->fetch_assoc();
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = $smtp['smtp_host'];
                $mail->SMTPAuth = true;
                $mail->Username = $smtp['smtp_username'];
                $mail->Password = $smtp['smtp_password'];
                $mail->SMTPSecure = $smtp['smtp_encryption'];
                $mail->Port = $smtp['smtp_port'];

                $mail->setFrom($smtp['from_email'], $smtp['from_name']);
                $mail->addAddress($email, $owner_name);

                $mail->isHTML(true);
                $mail->Subject = "Welcome to MG Skills - Your Center Credentials";
                $mail->Body = "
                    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e0e0e0; border-radius: 10px;'>
                        <div style='text-align: center; margin-bottom: 20px;'>
                            <h2 style='color: #6f75ff;'>Welcome to MG Skills Network!</h2>
                        </div>
                        <p>Dear <strong>$owner_name</strong>,</p>
                        <p>Congratulations! Your center <strong>$center_name</strong> has been successfully registered with us.</p>
                        
                        <div style='background-color: #f9fafb; padding: 15px; border-radius: 8px; margin: 20px 0;'>
                            <h3 style='margin-top: 0; color: #333;'>Your Login Credentials</h3>
                            <p style='margin-bottom: 5px;'><strong>Center Code (User ID):</strong> <span style='color: #2563eb; font-weight: bold;'>$center_code</span></p>
                            <p style='margin-bottom: 5px;'><strong>Password:</strong> <span style='color: #dc2626; font-weight: bold;'>$raw_password</span></p>
                            <p style='font-size: 12px; color: #666;'>Please change your password after your first login.</p>
                        </div>

                        <p><strong>Center Details:</strong><br>
                        Location: $city, $state</p>
                        
                        <a href='http://localhost/mg-skill/center/login.php' style='display: inline-block; background-color: #6f75ff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold;'>Login to Dashboard</a>
                        
                        <br><br>
                        <p>We look forward to a successful partnership.</p>
                        <p>Best Regards,<br>MG Skills Team</p>
                    </div>
                ";
                $mail->send();
            } catch (Exception $e) {
                // Email failed, silent fail or log it
            }
        }
    } else {
        $error_message = "Database Error: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Center - MG Skills</title>
    <style>
        :root{--active:#22c55e;--indigo:#6f75ff;--line:#e6e8ee;--text:#0b1020;--muted:#6f7787;--error:#ef4444;--success:#22c55e;--info:#3b82f6}
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;background:linear-gradient(180deg,#f8fafc 0%,#ffffff 100%);color:var(--text)}
        .admin-content{margin-left:260px;min-height:100vh;padding:20px;transition:margin-left .25s ease}
        body.sidebar-collapsed .admin-content{margin-left:88px}
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
        .section-title { font-size: 18px; font-weight: 700; margin-bottom: 20px; border-bottom: 1px solid var(--line); padding-bottom: 10px; color: var(--indigo); }
        .form-group{margin-bottom:20px}
        .form-label{display:block;font-weight:700;color:var(--text);margin-bottom:8px;font-size:14px}
        .form-input, .form-textarea, .form-select{width:100%;padding:12px 16px;border:1px solid var(--line);border-radius:10px;font-size:15px;transition:all .2s ease;font-family:inherit;background:#fff}
        .form-input:focus, .form-textarea:focus, .form-select:focus{outline:none;border-color:var(--indigo);box-shadow:0 0 0 3px rgba(111,117,255,.1)}
        .grid-2{display:grid;grid-template-columns:1fr 1fr;gap:20px}
        .grid-3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:20px}
        .checkbox-group { display: flex; gap: 15px; flex-wrap: wrap; }
        .checkbox-item { display: flex; align-items: center; gap: 5px; font-size: 14px; }
        .btn{display:inline-flex;align-items:center;gap:8px;padding:12px 24px;border-radius:12px;font-weight:700;font-size:15px;border:none;cursor:pointer;transition:all .2s ease;text-decoration:none}
        .btn-primary{background:linear-gradient(135deg,var(--indigo) 0%,#5a5fff 100%);color:#fff}
        .btn-outline{background:#fff;color:var(--indigo);border:2px solid var(--indigo)}
        .doc-row { display: flex; gap: 10px; margin-bottom: 10px; align-items: flex-end; }
        .doc-row .form-group { margin-bottom: 0; flex: 1; }
        .remove-btn { color: var(--error); background: #fee2e2; border: 1px solid #fca5a5; padding: 10px; border-radius: 8px; cursor: pointer; height: 45px; }
    </style>
</head>
<body>
    <?php include __DIR__ . "/../sidebar.php"; ?>
    <main class="admin-content">
        <div class="admin-wrap">
            <div class="page-header">
                <div class="breadcrumb"><a href="../index.php">Dashboard</a> › Centers</div>
                <h1 class="page-title">Add New Center</h1>
                <p class="page-subtitle">Register a new training center</p>
            </div>

            <?php if (!empty($success_message)): ?>
            <div class="alert alert-success"><span>✓ <?php echo $success_message; ?></span></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                
                <!-- Basic Details -->
                <div class="card">
                    <h3 class="section-title">Basic Details</h3>
                    <div class="grid-2">
                        <div class="form-group">
                            <label class="form-label">Center Name *</label>
                            <input type="text" name="center_name" class="form-input" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Owner Name *</label>
                            <input type="text" name="owner_name" class="form-input" required>
                        </div>
                    </div>
                    <div class="grid-2">
                        <div class="form-group">
                            <label class="form-label">Email *</label>
                            <input type="email" name="email" class="form-input" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Mobile * (with Country Code)</label>
                            <input type="text" name="mobile" class="form-input" value="+91 " required>
                        </div>
                    </div>
                </div>

                <!-- Location -->
                <div class="card">
                    <h3 class="section-title">Location Details</h3>
                    <div class="grid-3">
                        <div class="form-group">
                            <label class="form-label">Pincode *</label>
                            <input type="text" name="pincode" id="pincode" class="form-input" required placeholder="Enter Pincode">
                            <small id="pincode-msg" style="color: var(--indigo); display:none">Fetching details...</small>
                        </div>
                        <div class="form-group">
                            <label class="form-label">City *</label>
                            <input type="text" name="city" id="city" class="form-input" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">State *</label>
                            <input type="text" name="state" id="state" class="form-input" required>
                        </div>
                    </div>
                    <div class="grid-2">
                        <div class="form-group">
                            <label class="form-label">Country *</label>
                            <input type="text" name="country" id="country" class="form-input" value="India" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Full Address *</label>
                            <input type="text" name="address" class="form-input" required placeholder="Building, Street, Area">
                        </div>
                    </div>
                </div>

                <!-- Infrastructure -->
                <div class="card">
                    <h3 class="section-title">Infrastructure</h3>
                    <div class="grid-3">
                        <div class="form-group">
                            <label class="form-label">Classrooms</label>
                            <input type="number" name="num_classrooms" class="form-input" value="0">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Computers</label>
                            <input type="number" name="num_computers" class="form-input" value="0">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Total Staff</label>
                            <input type="number" name="total_staff" class="form-input" value="0">
                        </div>
                    </div>
                    <div class="grid-3">
                        <div class="form-group">
                            <label class="form-label">Lab Type</label>
                            <select name="lab_type" class="form-select">
                                <option value="Basic">Basic</option>
                                <option value="Advanced">Advanced</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Working Hours From</label>
                            <input type="time" name="working_hours_from" class="form-input">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Working Hours To</label>
                            <input type="time" name="working_hours_to" class="form-input">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Amenities</label>
                        <div class="checkbox-group">
                            <label class="checkbox-item"><input type="checkbox" name="has_internet" value="1"> Internet Availability</label>
                            <label class="checkbox-item"><input type="checkbox" name="has_power_backup" value="1"> Power Backup</label>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Weekend Off</label>
                        <div class="checkbox-group">
                            <?php $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                            foreach($days as $day): ?>
                                <label class="checkbox-item"><input type="checkbox" name="weekend_off[]" value="<?php echo $day; ?>"> <?php echo $day; ?></label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Documents -->
                <div class="card">
                    <h3 class="section-title">Legal & Documentation</h3>
                    <div id="docs-container">
                        <div class="doc-row">
                            <div class="form-group">
                                <label class="form-label">Document Name</label>
                                <input type="text" name="doc_names[]" class="form-input" placeholder="e.g., PAN Card">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Document Number</label>
                                <input type="text" name="doc_numbers[]" class="form-input" placeholder="e.g., ABCDE1234F">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Upload File</label>
                                <input type="file" name="doc_files[]" class="form-input">
                            </div>
                            <button type="button" class="remove-btn" onclick="this.parentElement.remove()">×</button>
                        </div>
                    </div>
                    <button type="button" class="btn btn-outline" onclick="addDoc()">+ Add Another Document</button>
                </div>

                <!-- Franchise & Social -->
                <div class="card">
                    <h3 class="section-title">Franchise & Social</h3>
                    <div class="grid-2">
                        <div class="form-group">
                            <label class="form-label">Franchise Fee (INR)</label>
                            <input type="number" name="franchise_fee" class="form-input" placeholder="0.00">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Royalty Percentage (%)</label>
                            <input type="number" name="royalty_percentage" class="form-input" placeholder="0.00" step="0.01">
                        </div>
                    </div>
                    <div class="grid-2">
                        <div class="form-group">
                            <label class="form-label">Facebook URL</label>
                            <input type="url" name="facebook" class="form-input">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Instagram URL</label>
                            <input type="url" name="instagram" class="form-input">
                        </div>
                        <div class="form-group">
                            <label class="form-label">YouTube URL</label>
                            <input type="url" name="youtube" class="form-input">
                        </div>
                        <div class="form-group">
                            <label class="form-label">LinkedIn URL</label>
                            <input type="url" name="linkedin" class="form-input">
                        </div>
                    </div>
                </div>

                <!-- Media Uploads -->
                <div class="card">
                    <h3 class="section-title">Media Uploads</h3>
                    <div class="grid-2">
                        <div class="form-group">
                            <label class="form-label">Center Logo</label>
                            <input type="file" name="center_logo" class="form-input" accept="image/*">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Owner Image</label>
                            <input type="file" name="owner_image" class="form-input" accept="image/*">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Authorized Signatory</label>
                            <input type="file" name="authorized_signatory" class="form-input" accept="image/*">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Digital Stamp</label>
                            <input type="file" name="digital_stamp" class="form-input" accept="image/*">
                        </div>
                    </div>
                </div>

                <div style="margin-bottom: 50px;">
                    <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 16px;">Register Center</button>
                </div>

            </form>
        </div>
    </main>

    <script>
        // IndiaPost API Integration
        const pincodeInput = document.getElementById('pincode');
        const cityInput = document.getElementById('city');
        const stateInput = document.getElementById('state');
        const countryInput = document.getElementById('country');
        const msgSpan = document.getElementById('pincode-msg');

        pincodeInput.addEventListener('blur', function() {
            const pincode = this.value.trim();
            if (pincode.length === 6) {
                msgSpan.style.display = 'block';
                msgSpan.textContent = 'Fetching details...';
                
                fetch(`https://api.postalpincode.in/pincode/${pincode}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data[0].Status === 'Success') {
                            const details = data[0].PostOffice[0];
                            cityInput.value = details.District; // Using District as City
                            stateInput.value = details.State;
                            countryInput.value = details.Country;
                            msgSpan.textContent = 'Details fetched!';
                            msgSpan.style.color = '#22c55e';
                        } else {
                            msgSpan.textContent = 'Invalid Pincode';
                            msgSpan.style.color = '#ef4444';
                        }
                    })
                    .catch(err => {
                        msgSpan.textContent = 'Error fetching details';
                    });
            }
        });

        // Add Document Row
        function addDoc() {
            const container = document.getElementById('docs-container');
            const div = document.createElement('div');
            div.className = 'doc-row';
            div.innerHTML = `
                <div class="form-group">
                    <input type="text" name="doc_names[]" class="form-input" placeholder="Document Name">
                </div>
                <div class="form-group">
                    <input type="text" name="doc_numbers[]" class="form-input" placeholder="Number (if any)">
                </div>
                <div class="form-group">
                    <input type="file" name="doc_files[]" class="form-input">
                </div>
                <button type="button" class="remove-btn" onclick="this.parentElement.remove()">×</button>
            `;
            container.appendChild(div);
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>
