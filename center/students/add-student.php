<?php
session_start();
require_once __DIR__ . '/../../database/db-config.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!isset($_SESSION['center_id'])) {
    header("Location: ../login.php");
    exit;
}

$center_id = $_SESSION['center_id'];

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

$conn = getDbConnection();

// Fetch Courses
$courses = [];
$c_sql = "SELECT id, title FROM courses WHERE is_active = 1";
$c_res = $conn->query($c_sql);
while($row = $c_res->fetch_assoc()) $courses[] = $row;

// Fetch Sessions
$sessions = [];
$s_sql = "SELECT id, course_id, session_name FROM course_sessions WHERE is_active = 1 ORDER BY id DESC";
$s_res = $conn->query($s_sql);
while($row = $s_res->fetch_assoc()) {
    $sessions[$row['course_id']][] = $row;
}

$success_message = "";
$error_message = "";

// Helper for uploads
function uploadCenterFile($file, $dir) {
    if (!isset($file['name']) || $file['error'] != 0) return null;
    $target_dir = "../../assets/uploads/students/" . $dir . "/";
    if (!file_exists($target_dir)) mkdir($target_dir, 0755, true);
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = "CTR_" . uniqid() . "." . $ext;
    if (move_uploaded_file($file['tmp_name'], $target_dir . $filename)) {
        return "assets/uploads/students/" . $dir . "/" . $filename;
    }
    return null;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    
    $course_id = intval($_POST['course_id']);
    $session_id = isset($_POST['session_id']) && !empty($_POST['session_id']) ? intval($_POST['session_id']) : 0;

    if($session_id == 0) {
        $error_message = "Please select a valid academic session.";
    } else {
    
    // Generate Enrollment ID for Center: MGCTR-2025-01
    $year = date("Y");
    // Find last center student enrollment
    $last_sql = "SELECT enrollment_no FROM admissions WHERE enrollment_no LIKE 'MGCTR{$year}%' ORDER BY id DESC LIMIT 1";
    $last_res = $conn->query($last_sql);
    $seq = 1;
    if ($last_res->num_rows > 0) {
        // MGCTR202501 -> last 2 chars? No, assume format length.
        // Format: MGCTR202501 (MGCTR + Year + 2 digit)
        // Let's use 2 digit sequence for now per request "ID format - MGCTR202501"
        $last_id_str = $last_res->fetch_assoc()['enrollment_no'];
        // $last_id_str = MGCTR202501
        // Year is 4 chars, MGCTR is 5 chars. Total 9. Remaining digits.
        $last_seq = intval(substr($last_id_str, 9)); 
        $seq = $last_seq + 1;
    }
    $enrollment_no = "MGCTR" . $year . str_pad($seq, 2, "0", STR_PAD_LEFT);

    // Generate Password
    $raw_password = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ@#$!"), 0, 10);
    $hashed_password = password_hash($raw_password, PASSWORD_BCRYPT);

    // Fields
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $father_name = mysqli_real_escape_string($conn, $_POST['father_name']);
    $mother_name = mysqli_real_escape_string($conn, $_POST['mother_name']);
    $dob = mysqli_real_escape_string($conn, $_POST['dob']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $admission_mode = 'Center'; // Fixed
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
    $aadhar_no = mysqli_real_escape_string($conn, $_POST['aadhar_no']);
    
    $prev_course_done = isset($_POST['prev_course_done']) ? 1 : 0;
    
    // Uploads
    $photo = uploadCenterFile($_FILES['student_photo'], 'photos');
    $sign = uploadCenterFile($_FILES['student_sign'], 'signatures');
    $aadhar = uploadCenterFile($_FILES['aadhar_file'], 'documents');
    $cert = uploadCenterFile($_FILES['edu_cert_file'], 'documents');

    // Payment (Center collects fees, so status pending until they collect)
    $course_fee = 0; 
    
    $sql = "INSERT INTO admissions (
        enrollment_no, password, course_id, session_id, center_id, added_by, full_name, father_name, mother_name, dob, category, admission_mode,
        student_photo, student_sign, mobile, alt_mobile, email,
        pincode, country, state, city, address,
        highest_qual, school_name, board_university, passing_year, percentage,
        computer_knowledge, typing_speed, prev_course_done,
        aadhar_no, aadhar_file, edu_cert_file,
        course_fee, payment_status
    ) VALUES (
        '$enrollment_no', '$hashed_password', $course_id, $session_id, $center_id, 'center', '$full_name', '$father_name', '$mother_name', '$dob', '$category', '$admission_mode',
        '$photo', '$sign', '$mobile', '$alt_mobile', '$email',
        '$pincode', '$country', '$state', '$city', '$address',
        '$highest_qual', '$school_name', '$board', $passing_year, '$percentage',
        '$computer_knowledge', '$typing_speed', $prev_course_done,
        '$aadhar_no', '$aadhar', '$cert',
        $course_fee, 'pending'
    )";
    
    if ($conn->query($sql) === TRUE) {
        $success_message = "Student enrolled successfully!<br>Enrollment ID: <strong>$enrollment_no</strong><br>Password: <strong>$raw_password</strong>";

        // Send Email
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
                $mail->addAddress($email, $full_name);

                $mail->isHTML(true);
                $mail->Subject = "Welcome to MG Skills - Your Student Credentials";
                $mail->Body = "
                    <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #e0e0e0; border-radius: 10px;'>
                        <div style='text-align: center; margin-bottom: 20px;'>
                            <h2 style='color: #6f75ff;'>Welcome to MG Education!</h2>
                        </div>
                        <p>Dear <strong>$full_name</strong>,</p>
                        <p>Your enrollment has been confirmed by your center.</p>
                        
                        <div style='background-color: #f9fafb; padding: 15px; border-radius: 8px; margin: 20px 0;'>
                            <h3 style='margin-top: 0; color: #333;'>Your Login Credentials</h3>
                            <p style='margin-bottom: 5px;'><strong>Enrollment No (User ID):</strong> <span style='color: #2563eb; font-weight: bold;'>$enrollment_no</span></p>
                            <p style='margin-bottom: 5px;'><strong>Password:</strong> <span style='color: #dc2626; font-weight: bold;'>$raw_password</span></p>
                        </div>
                        
                        <a href='http://localhost/mg-skill/student/login.php' style='display: inline-block; background-color: #6f75ff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold;'>Login to Student Dashboard</a>
                        <br><br>
                        <p>Best Regards,<br>MG Skills Team</p>
                    </div>
                ";
                $mail->send();
            } catch (Exception $e) { }
        }
    } else {
        $error_message = "Error: " . $conn->error;
    }

  } // End else valid session
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Enroll Student - Center Dashboard</title>
    <style>
        :root{--active:#22c55e;--indigo:#6f75ff;--line:#e6e8ee;--text:#0b1020;--muted:#6f7787;--error:#ef4444;--success:#22c55e}
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Outfit',sans-serif;background:#f8fafc;color:var(--text)}
        .admin-content{margin-left:260px;padding:30px}
        .page-header{margin-bottom:20px} .page-title{font-size:24px;font-weight:700}
        .card{background:#fff;padding:30px;border-radius:12px;margin-bottom:20px;box-shadow:0 2px 10px rgba(0,0,0,0.05)}
        .section-title{font-size:18px;font-weight:700;margin-bottom:20px;color:var(--indigo);border-bottom:1px solid var(--line);padding-bottom:10px}
        .grid-2{display:grid;grid-template-columns:1fr 1fr;gap:20px}
        .form-group{margin-bottom:15px} label{display:block;margin-bottom:5px;font-weight:600;font-size:14px}
        input,select,textarea{width:100%;padding:10px;border:1px solid var(--line);border-radius:8px;font-size:14px}
        .btn{padding:12px 24px;background:var(--indigo);color:#fff;border:none;border-radius:8px;cursor:pointer;font-weight:600}
        .alert{padding:15px;border-radius:8px;margin-bottom:20px} .alert-success{background:#dcfce7;color:#166534} .alert-error{background:#fee2e2;color:#991b1b}
    </style>
</head>
<body>
    <?php include __DIR__ . "/../sidebar.php"; ?>
    <main class="admin-content">
        <div class="page-header"><h1 class="page-title">Enroll New Student</h1></div>
        
        <?php if($success_message): ?><div class="alert alert-success"><?php echo $success_message; ?></div><?php endif; ?>
        <?php if($error_message): ?><div class="alert alert-error"><?php echo $error_message; ?></div><?php endif; ?>
        
        <form method="POST" enctype="multipart/form-data">
            <div class="card">
                <h3 class="section-title">Course Selection</h3>
                <div class="form-group">
                    <label>Select Course *</label>
                    <select name="course_id" required>
                        <option value="">Choose Course</option>
                        <?php foreach($courses as $c): ?>
                            <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['title']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Select Session *</label>
                    <select name="session_id" id="session_id" required>
                        <option value="">Select Session</option>
                        <!-- Populated by JS -->
                    </select>
                </div>
            </div>

            <div class="card">
                <h3 class="section-title">Personal Details</h3>
                <div class="grid-2">
                    <div class="form-group"><label>Full Name *</label><input type="text" name="full_name" required></div>
                    <div class="form-group"><label>Date of Birth *</label><input type="date" name="dob" required></div>
                    <div class="form-group"><label>Father's Name *</label><input type="text" name="father_name" required></div>
                    <div class="form-group"><label>Mother's Name *</label><input type="text" name="mother_name" required></div>
                    <div class="form-group"><label>Category</label>
                        <select name="category"><option value="General">General</option><option value="OBC">OBC</option><option value="SC">SC</option><option value="ST">ST</option></select>
                    </div>
                    <div class="form-group"><label>Aadhar No *</label><input type="text" name="aadhar_no" required></div>
                </div>
            </div>

            <div class="card">
                <h3 class="section-title">Contact Information</h3>
                <div class="grid-2">
                    <div class="form-group"><label>Mobile *</label><input type="text" name="mobile" required></div>
                    <div class="form-group"><label>Email *</label><input type="email" name="email" required></div>
                    <div class="form-group"><label>Pincode</label><input type="text" name="pincode" id="pincode"></div>
                    <div class="form-group"><label>City</label><input type="text" name="city" id="city"></div>
                    <div class="form-group"><label>State</label><input type="text" name="state" id="state"></div>
                    <div class="form-group"><label>Country</label><input type="text" name="country" id="country" value="India"></div>
                </div>
                <div class="form-group"><label>Full Address</label><textarea name="address" required></textarea></div>
            </div>

            <div class="card">
                <h3 class="section-title">Education & Uploads</h3>
                <div class="grid-2">
                    <div class="form-group"><label>Highest Qualification</label><input type="text" name="highest_qual" required></div>
                    <div class="form-group"><label>School/College</label><input type="text" name="school_name" required></div>
                    <div class="form-group"><label>Board/Univ</label><input type="text" name="board" required></div>
                    <div class="form-group"><label>Passing Year</label><input type="number" name="passing_year" required></div>
                    <div class="form-group"><label>Percentage</label><input type="text" name="percentage" required></div>
                    <div class="form-group"><label>Computer Knowledge</label>
                             <select name="computer_knowledge"><option value="Beginner">Beginner</option><option value="Intermediate">Intermediate</option><option value="Advanced">Advanced</option></select>
                    </div>
                    <div class="form-group"><label>Typing Speed</label><input type="text" name="typing_speed"></div>
                </div>
                <div class="grid-2">
                    <div class="form-group"><label>Student Photo</label><input type="file" name="student_photo"></div>
                    <div class="form-group"><label>Signature</label><input type="file" name="student_sign"></div>
                    <div class="form-group"><label>Aadhar File</label><input type="file" name="aadhar_file"></div>
                    <div class="form-group"><label>Certificate</label><input type="file" name="edu_cert_file"></div>
                </div>
            </div>

            <div style="margin-bottom: 50px;"><button type="submit" class="btn">Enroll Student</button></div>
        </form>
    </main>
    <script>
        // Pincode fetcher
        const pincodeInput = document.getElementById('pincode');
        const cityInput = document.getElementById('city');
        const stateInput = document.getElementById('state');
        const countryInput = document.getElementById('country');

        pincodeInput.addEventListener('blur', function() {
            if(this.value.length == 6) {
                fetch('https://api.postalpincode.in/pincode/' + this.value).then(r=>r.json()).then(d=>{
                    if(d[0].Status=='Success'){
                        let p = d[0].PostOffice[0];
                        cityInput.value = p.District;
                        stateInput.value = p.State;
                        countryInput.value = p.Country;
                    }
                });
            }
            }
        });

        // Dynamic Sessions
        // Debug Information
        window.allSessions = <?php echo json_encode($sessions); ?>;
        console.log("Initializing Sessions...", window.allSessions);

        // Create debug element
        const sessionDebugMsg = document.createElement('div');
        sessionDebugMsg.style.fontSize = '12px';
        sessionDebugMsg.style.marginTop = '5px';
        
        const sessionSelect = document.getElementById('session_id');
        if(sessionSelect) {
            sessionSelect.parentNode.appendChild(sessionDebugMsg);
        }

        const courseSelect = document.querySelector('select[name="course_id"]');

        function updateSessions() {
            if(!courseSelect || !sessionSelect) return;
            
            const cId = courseSelect.value;
            console.log("Selected Course ID:", cId);
            
            // Clear existing
            sessionSelect.innerHTML = '<option value="">Select Session</option>';
            sessionDebugMsg.textContent = '';
            
            if (!cId) return;

            if (window.allSessions && window.allSessions[cId]) {
                console.log("Found sessions:", window.allSessions[cId]);
                window.allSessions[cId].forEach(sess => {
                    const opt = document.createElement('option');
                    opt.value = sess.id;
                    opt.textContent = sess.session_name;
                    sessionSelect.appendChild(opt);
                });
                
                // Success message
                sessionDebugMsg.style.color = 'green';
                sessionDebugMsg.textContent = 'Loaded ' + window.allSessions[cId].length + ' active sessions.';
            } else {
                console.warn("No sessions found for Course ID: " + cId);
                // Error message
                sessionDebugMsg.style.color = 'red';
                sessionDebugMsg.textContent = 'No sessions found for this course. Please contact Admin.';
            }
        }

        if (courseSelect) {
            courseSelect.addEventListener('change', updateSessions);
            
            // Run immediately in case of auto-fill
            setTimeout(updateSessions, 500); 
        }
    </script>
</body>
</html>
