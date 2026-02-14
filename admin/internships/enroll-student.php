<?php
// Include database configuration
require_once __DIR__ . '/../../database/db-config.php';
// Ensure schema
require_once __DIR__ . '/../../database/update_enrollment_password_schema.php';
// Composer Autoload
require_once __DIR__ . '/../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$conn = getDbConnection();

// Fetch Active Internships
$internships = [];
$sql = "SELECT id, title, fees FROM internships WHERE is_active = 1 ORDER BY title ASC";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $fees = json_decode($row['fees'], true);
        $row['amount'] = isset($fees['amount']) ? $fees['amount'] : 0;
        $internships[] = $row;
    }
}

$success_message = "";
$error_message = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Helpers
    function uploadAdminFile($file, $dir) {
        if (!isset($file['name']) || $file['error'] != 0) return null;
        $target_dir = "../../assets/uploads/internship_docs/" . $dir . "/";
        if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = "INT" . date("Ymd") . "_" . uniqid() . "." . $ext; 
        $target_file = $target_dir . $filename;
        if (move_uploaded_file($file['tmp_name'], $target_file)) {
            return "assets/uploads/internship_docs/" . $dir . "/" . $filename;
        }
        return null;
    }
    
    // Generate Enrollment No
    $year = date("Y");
    $last_sql = "SELECT enrollment_no FROM internship_enrollments WHERE enrollment_no LIKE 'INT{$year}%' ORDER BY id DESC LIMIT 1";
    $last_res = $conn->query($last_sql);
    $seq = 1;
    if ($last_res->num_rows > 0) {
        $last_row = $last_res->fetch_assoc();
        $last_seq = intval(substr($last_row['enrollment_no'], -4));
        $seq = $last_seq + 1;
    }
    $enrollment_no = "INT" . $year . str_pad($seq, 4, "0", STR_PAD_LEFT);

    // Inputs
    $internship_id = intval($_POST['internship_id']);
    $session_id = isset($_POST['session_id']) ? intval($_POST['session_id']) : NULL;
    
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $father_name = mysqli_real_escape_string($conn, $_POST['father_name']);
    $mother_name = mysqli_real_escape_string($conn, $_POST['mother_name']);
    $dob = mysqli_real_escape_string($conn, $_POST['dob']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $admission_mode = 'Offline'; // Admin enrollment assumes manually done unless specified otherwise
    
    $mobile = mysqli_real_escape_string($conn, $_POST['mobile']);
    $alt_mobile = mysqli_real_escape_string($conn, $_POST['alt_mobile']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    
    $pincode = mysqli_real_escape_string($conn, $_POST['pincode']);
    $country = mysqli_real_escape_string($conn, $_POST['country']);
    $state = mysqli_real_escape_string($conn, $_POST['state']);
    $city = mysqli_real_escape_string($conn, $_POST['city']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    
    $highest_qual = mysqli_real_escape_string($conn, $_POST['highest_qual']);
    $school_name = mysqli_real_escape_string($conn, $_POST['school_name']);
    $board = mysqli_real_escape_string($conn, $_POST['board']);
    $passing_year = intval($_POST['passing_year']);
    $percentage = mysqli_real_escape_string($conn, $_POST['percentage']);
    
    $computer_knowledge = mysqli_real_escape_string($conn, $_POST['computer_knowledge']);
    $typing_speed = mysqli_real_escape_string($conn, $_POST['typing_speed']);
    
    $prev_course_done = isset($_POST['prev_course_done']) && $_POST['prev_course_done'] == 'yes' ? 1 : 0;
    $prev_enroll_no = mysqli_real_escape_string($conn, $_POST['prev_enroll_no']);
    $prev_course_name = mysqli_real_escape_string($conn, $_POST['prev_course_name']);
    $prev_course_session = mysqli_real_escape_string($conn, $_POST['prev_course_session']);
    
    $aadhar_no = mysqli_real_escape_string($conn, $_POST['aadhar_no']);
    
    // Fee Logic
    $base_fee = floatval($_POST['base_fee']);
    $discount_percent = floatval($_POST['discount_percent']);
    $final_fee = floatval($_POST['final_fee']);
    $is_free = isset($_POST['is_free']) ? 1 : 0;
    
    if($is_free) {
        $final_fee = 0;
    }
    
    // Uploads
    $photo_path = uploadAdminFile($_FILES['student_photo'], 'photos');
    $sign_path = uploadAdminFile($_FILES['student_sign'], 'signatures');
    $aadhar_path = uploadAdminFile($_FILES['aadhar_file'], 'documents');
    $cert_path = uploadAdminFile($_FILES['edu_cert_file'], 'documents');
    
    $payment_status = 'success'; // Admin enrollment implies confirmed receipt or waiver
    
    // Generate Password
    $password_plain = substr(str_shuffle("abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ23456789"), 0, 8);
    $password_hash = password_hash($password_plain, PASSWORD_DEFAULT);

    $sql = "INSERT INTO internship_enrollments (
        enrollment_no, internship_id, session_id, full_name, father_name, mother_name, dob, category, admission_mode,
        student_photo, student_sign, mobile, alt_mobile, email,
        pincode, country, state, city, address,
        highest_qual, school_name, board_university, passing_year, percentage,
        computer_knowledge, typing_speed, prev_course_done, prev_enroll_no, prev_course_name, prev_course_session,
        aadhar_no, aadhar_file, edu_cert_file,
        course_fee, payment_status, razorpay_payment_id, password
    ) VALUES (
        '$enrollment_no', $internship_id, " . ($session_id ? $session_id : "NULL") . ", '$full_name', '$father_name', '$mother_name', '$dob', '$category', '$admission_mode',
        '$photo_path', '$sign_path', '$mobile', '$alt_mobile', '$email',
        '$pincode', '$country', '$state', '$city', '$address',
        '$highest_qual', '$school_name', '$board', $passing_year, '$percentage',
        '$computer_knowledge', '$typing_speed', $prev_course_done, '$prev_enroll_no', '$prev_course_name', '$prev_course_session',
        '$aadhar_no', '$aadhar_path', '$cert_path',
        $final_fee, '$payment_status', 'ADMIN_MANUAL', '$password_hash'
    )";
    
    if ($conn->query($sql) === TRUE) {
        $success_message = "Student Enrolled Successfully! Enrollment No: " . $enrollment_no;
        
        // Log transaction if fee > 0
        if($final_fee > 0) {
            $txn_sql = "INSERT INTO student_transactions (enrollment_no, transaction_id, amount, payment_mode, status, remarks) 
                        VALUES ('$enrollment_no', 'ADMIN-$enrollment_no', $final_fee, 'Cash/Manual', 'success', 'Admin Manual Enrollment (Disc: $discount_percent%)')";
            $conn->query($txn_sql);
        }
        
        // Send Email
        if (!empty($email)) {
             $smtp_sql = "SELECT * FROM smtp_settings WHERE id = 1 AND is_active = 1";
             $smtp_res = $conn->query($smtp_sql);
             if ($smtp_res && $smtp_res->num_rows > 0) {
                 $smtp = $smtp_res->fetch_assoc();
                 $mail = new PHPMailer(true);
                 try {
                     // Server settings
                     $mail->isSMTP();
                     $mail->Host       = $smtp['smtp_host'];
                     $mail->SMTPAuth   = true;
                     $mail->Username   = $smtp['smtp_username'];
                     $mail->Password   = $smtp['smtp_password'];
                     $mail->SMTPSecure = $smtp['smtp_encryption']; // tls or ssl
                     $mail->Port       = $smtp['smtp_port'];
 
                     // Recipients
                     $mail->setFrom($smtp['from_email'], $smtp['from_name']);
                     $mail->addAddress($email, $full_name);
 
                     // Content
                     $mail->isHTML(true);
                     $mail->Subject = 'Internship Enrollment Successful - MG Education';
                     $mail->Body    = "
                         <h2>Welcome to MG Education!</h2>
                         <p>Dear $full_name,</p>
                         <p>You have been successfully enrolled in the internship program.</p>
                         <p><strong>Enrollment No:</strong> $enrollment_no</p>
                         
                         <div style='background:#f3f4f6; padding:15px; border-radius:8px; margin:20px 0; border:1px solid #e5e7eb;'>
                            <h3 style='margin-top:0; color:#1358db;'>Exam Portal Credentials</h3>
                            <p>You can login to the internship exam dashboard using the following credentials:</p>
                            <p><strong>URL:</strong> <a href='https://mg-skills.com/internship-exam/login.php'>Login Here</a></p>
                            <p><strong>User ID:</strong> $enrollment_no (or your email)</p>
                            <p><strong>Password:</strong> <span style='font-family:monospace; background:#fff; padding:2px 6px; border-radius:4px;'>$password_plain</span></p>
                        </div>
                         <p>Please use these credentials to login to your student dashboard.</p>
                         <p>Regards,<br>MG Education Team</p>
                     ";
 
                     $mail->send();
                     
                     // Log Success
                     $safe_msg = mysqli_real_escape_string($conn, $mail->Body); // Basic log
                     $conn->query("INSERT INTO email_logs (to_email, subject, message, status) VALUES ('$email', 'Internship Enrollment', 'Sent successfully', 'success')");
                     
                 } catch (Exception $e) {
                     $error_message .= " <br>Warning: Email could not be sent. Mailer Error: {$mail->ErrorInfo}";
                     $conn->query("INSERT INTO email_logs (to_email, subject, message, status, error_message) VALUES ('$email', 'Internship Enrollment', 'Failed', 'failed', '" . mysqli_real_escape_string($conn, $mail->ErrorInfo) . "')");
                 }
             }
        }
    } else {
        $error_message = "Database Error: " . $conn->error;
    }
}

include __DIR__ . '/../sidebar.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enroll Student - MG Admin</title>
    <style>
        :root{--active:#22c55e;--indigo:#6f75ff;--line:#e6e8ee;--text:#0b1020;--muted:#6f7787;--error:#ef4444;--success:#22c55e;--info:#3b82f6}
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Outfit',sans-serif;background:#f8fafc;color:var(--text)}
        .admin-content{margin-left:260px;min-height:100vh;padding:20px;transition:margin-left .25s ease}
        .admin-wrap{max-width:1000px;margin:0 auto}
        
        .page-header{margin-bottom:30px}
        .page-title{font-size:28px;font-weight:800;color:var(--text);margin-bottom:8px}
        .breadcrumb{color:var(--muted);margin-bottom:10px;font-size:14px}
        .breadcrumb a{color:var(--indigo);text-decoration:none}

        .alert{padding:16px 20px;border-radius:12px;margin-bottom:20px;display:flex;align-items:center;gap:12px;border:1px solid}
        .alert-success{background:#d1fae5;border-color:#86efac;color:#065f46}
        .alert-error{background:#fee2e2;border-color:#fca5a5;color:#991b1b}

        .card{background:#fff;border:1px solid var(--line);border-radius:18px;padding:30px;box-shadow:0 4px 12px rgba(0,0,0,.05);margin-bottom:20px}
        .section-title{font-size:18px;font-weight:700;margin-bottom:20px;border-bottom:1px solid var(--line);padding-bottom:10px;color:var(--admin-primary)}
        
        .form-grid{display:grid;grid-template-columns:1fr 1fr;gap:20px}
        .form-group{margin-bottom:15px}
        .form-full{grid-column:1/-1}
        
        .form-label{display:block;font-weight:600;color:var(--text);margin-bottom:8px;font-size:14px}
        .form-input, .form-select, .form-textarea{width:100%;padding:10px 14px;border:1px solid var(--line);border-radius:10px;font-size:15px;transition:all .2s;font-family:inherit;background:#fff}
        .form-input:focus, .form-select:focus{outline:none;border-color:var(--indigo);box-shadow:0 0 0 3px rgba(111,117,255,.1)}
        
        .btn{display:inline-flex;align-items:center;justify-content:center;padding:12px 24px;border-radius:12px;font-weight:700;font-size:15px;border:none;cursor:pointer;transition:all .2s ease;text-decoration:none;width:100%}
        .btn-primary{background:var(--indigo);color:#fff}
        .btn-primary:hover{background:#5a5fff}
        
        /* Fee Card */
        .fee-card{background:#f0f9ff;border:1px solid #bae6fd;padding:20px;border-radius:12px;margin-bottom:20px}
        .fee-row{display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;font-weight:600;font-size:14px}
        .fee-total{border-top:1px dashed #0284c7;padding-top:10px;margin-top:10px;font-size:18px;color:#0369a1}
        .discount-group{display:flex;align-items:center;gap:10px}
    </style>
</head>
<body>
    <main class="admin-content">
        <div class="admin-wrap">
            <div class="page-header">
                <div class="breadcrumb">
                    <a href="../index.php">Dashboard</a> › <a href="index.php">Internships</a> › Enroll Student
                </div>
                <h1 class="page-title">Enroll New Student</h1>
            </div>

            <?php if (!empty($success_message)): ?>
            <div class="alert alert-success"><span>✓ <?php echo $success_message; ?></span></div>
            <?php endif; ?>

            <?php if (!empty($error_message)): ?>
            <div class="alert alert-error"><span>⚠ <?php echo $error_message; ?></span></div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">
                
                <div class="card">
                    <h3 class="section-title">Internship Selection</h3>
                    <div class="form-group">
                        <label class="form-label">Select Internship *</label>
                        <select name="internship_id" id="internship_select" class="form-select" required onchange="calculateFee()">
                            <option value="">-- Choose Internship --</option>
                            <?php foreach($internships as $i): ?>
                                <option value="<?php echo $i['id']; ?>" data-fee="<?php echo $i['amount']; ?>">
                                    <?php echo htmlspecialchars($i['title']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Select Session *</label>
                        <select name="session_id" id="session_select" class="form-select" required>
                            <option value="">-- First Select Internship --</option>
                        </select>
                    </div>

                    <div id="feeBox" class="fee-card" style="display:none">
                        <div class="fee-row">
                            <span>Base Fee:</span>
                            <span id="baseFeeDisplay">₹0</span>
                            <input type="hidden" name="base_fee" id="base_fee">
                        </div>
                        
                        <div class="fee-row">
                            <span>Discount (%):</span>
                            <div class="discount-group">
                                <input type="number" name="discount_percent" id="discount_percent" value="0" min="0" max="100" class="form-input" style="width:80px" oninput="calculateFee()">
                            </div>
                        </div>

                        <div class="fee-row">
                            <span>Make it Free?</span>
                            <div class="discount-group">
                                <label><input type="checkbox" name="is_free" id="is_free" onchange="calculateFee()"> Yes (Zero Fee)</label>
                            </div>
                        </div>

                        <div class="fee-row fee-total">
                            <span>Final Fee To Collect:</span>
                            <span id="finalFeeDisplay">₹0</span>
                            <input type="hidden" name="final_fee" id="final_fee">
                        </div>
                    </div>
                </div>

                <div class="card">
                    <h3 class="section-title">Student Details</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Full Name *</label>
                            <input type="text" name="full_name" class="form-input" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Father's Name *</label>
                            <input type="text" name="father_name" class="form-input" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Mother's Name *</label>
                            <input type="text" name="mother_name" class="form-input" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Date of Birth *</label>
                            <input type="date" name="dob" class="form-input" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Category *</label>
                            <select name="category" class="form-select" required>
                                <option value="General">General</option>
                                <option value="OBC">OBC</option>
                                <option value="SC">SC</option>
                                <option value="ST">ST</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <h3 class="section-title">Contact Information</h3>
                    <div class="form-grid">
                         <div class="form-group">
                            <label class="form-label">Mobile Number *</label>
                            <input type="text" name="mobile" class="form-input" pattern="[0-9]{10}" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Alt Mobile</label>
                            <input type="text" name="alt_mobile" class="form-input" pattern="[0-9]{10}">
                        </div>
                        <div class="form-group form-full">
                            <label class="form-label">Email ID *</label>
                            <input type="email" name="email" class="form-input" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Pincode *</label>
                            <input type="text" name="pincode" id="pincode" class="form-input" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">City *</label>
                            <input type="text" name="city" id="city" class="form-input" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">State *</label>
                            <input type="text" name="state" id="state" class="form-input" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Country</label>
                            <input type="text" name="country" id="country" class="form-input" value="India" required>
                        </div>
                        <div class="form-group form-full">
                            <label class="form-label">Address *</label>
                            <textarea name="address" class="form-textarea" rows="2" required></textarea>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <h3 class="section-title">Education & Skills</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Highest Qualification *</label>
                            <input type="text" name="highest_qual" class="form-input" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">College/Institute *</label>
                            <input type="text" name="school_name" class="form-input" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Board/University *</label>
                            <input type="text" name="board" class="form-input" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Passing Year *</label>
                            <input type="number" name="passing_year" class="form-input" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Percentage *</label>
                            <input type="text" name="percentage" class="form-input" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Computer Knowledge *</label>
                            <select name="computer_knowledge" class="form-select" required>
                                <option value="Beginner">Beginner</option>
                                <option value="Intermediate">Intermediate</option>
                                <option value="Advanced">Advanced</option>
                            </select>
                        </div>
                         <div class="form-group">
                            <label class="form-label">Typing Speed</label>
                            <input type="text" name="typing_speed" class="form-input">
                        </div>
                    </div>
                </div>

                <div class="card">
                    <h3 class="section-title">Documents</h3>
                    <div class="form-grid">
                         <div class="form-group">
                            <label class="form-label">Aadhar No *</label>
                            <input type="text" name="aadhar_no" class="form-input" required>
                        </div>
                         <div class="form-group">
                            <label class="form-label">Upload Aadhar *</label>
                            <input type="file" name="aadhar_file" class="form-input" required>
                        </div>
                         <div class="form-group">
                            <label class="form-label">Student Photo *</label>
                            <input type="file" name="student_photo" class="form-input" required>
                        </div>
                         <div class="form-group">
                            <label class="form-label">Student Sign *</label>
                            <input type="file" name="student_sign" class="form-input" required>
                        </div>
                        <div class="form-group form-full">
                            <label class="form-label">Certificate/Marksheet *</label>
                            <input type="file" name="edu_cert_file" class="form-input" required>
                        </div>
                    </div>
                </div>

                <div style="margin-bottom:50px">
                    <button type="submit" class="btn btn-primary" onclick="return confirm('Confirm enrollment details?')">Register Student</button>
                </div>

            </form>
        </div>
    </main>

    <script>
    // Pincode Logic
    document.getElementById('pincode').addEventListener('blur', function() {
        let pin = this.value;
        if(pin.length === 6) {
            fetch('https://api.postalpincode.in/pincode/' + pin)
            .then(res => res.json())
            .then(data => {
                if(data[0].Status === 'Success') {
                    let po = data[0].PostOffice[0];
                    document.getElementById('city').value = po.District;
                    document.getElementById('state').value = po.State;
                    document.getElementById('country').value = po.Country;
                }
            });
        }
    });

    function calculateFee() {
        let sel = document.getElementById('internship_select');
        let opt = sel.options[sel.selectedIndex];
        let base = opt.dataset.fee ? parseFloat(opt.dataset.fee) : 0;
        
        document.getElementById('baseFeeDisplay').innerText = '₹' + base;
        document.getElementById('base_fee').value = base;
        
        if(base > 0) {
            document.getElementById('feeBox').style.display = 'block';
        } else {
            document.getElementById('feeBox').style.display = 'none'; 
        }

        let isFree = document.getElementById('is_free').checked;
        let discount = parseFloat(document.getElementById('discount_percent').value) || 0;
        
        let final = base;

        if(isFree) {
            final = 0;
            document.getElementById('discount_percent').disabled = true;
        } else {
            document.getElementById('discount_percent').disabled = false;
            if(discount > 0) {
                let discAmount = (base * discount) / 100;
                final = base - discAmount;
            }
        }
        
        // Round to 2 decimals
        final = Math.round(final * 100) / 100;

        document.getElementById('finalFeeDisplay').innerText = '₹' + final;
        document.getElementById('final_fee').value = final;

        // Fetch Sessions
        if(sel.value) {
            // Adjust path to point to frontend folder
            fetch('../../internship-enrollment/get-sessions.php?internship_id=' + sel.value)
            .then(res => res.json())
            .then(data => {
                let sessSelect = document.getElementById('session_select');
                sessSelect.innerHTML = '<option value="">-- Select Session --</option>';
                if(data.status === 'success' && data.data.length > 0) {
                    data.data.forEach(sess => {
                        sessSelect.innerHTML += `<option value="${sess.id}">${sess.session_name}</option>`;
                    });
                } else {
                    sessSelect.innerHTML = '<option value="">No Active Sessions</option>';
                }
            });
        } else {
            document.getElementById('session_select').innerHTML = '<option value="">-- First Select Internship --</option>';
        }
    }
    </script>
</body>
</html>
<?php $conn->close(); ?>
