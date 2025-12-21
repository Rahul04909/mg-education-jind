<?php
// Include database and PHPMailer
require_once __DIR__ . '/../../database/db-config.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$conn = getDbConnection();

// Fetch Courses
$courses = [];
$c_sql = "SELECT id, title FROM courses WHERE is_active = 1";
$c_res = $conn->query($c_sql);
while($row = $c_res->fetch_assoc()) $courses[] = $row;

$success_message = "";
$error_message = "";

// Helper for uploads
function uploadAdminFile($file, $dir) {
    if (!isset($file['name']) || $file['error'] != 0) return null;
    $target_dir = "../../assets/uploads/students/" . $dir . "/";
    if (!file_exists($target_dir)) mkdir($target_dir, 0755, true);
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = "ADMIN_" . uniqid() . "." . $ext;
    if (move_uploaded_file($file['tmp_name'], $target_dir . $filename)) {
        return "assets/uploads/students/" . $dir . "/" . $filename;
    }
    return null;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    
    // Logic similar to process-admission but simplified for admin
    $course_id = intval($_POST['course_id']);
    
    // Generate Enrollment
    $year = date("Y");
    $last_sql = "SELECT enrollment_no FROM admissions WHERE enrollment_no LIKE 'MG{$year}%' ORDER BY id DESC LIMIT 1";
    $last_res = $conn->query($last_sql);
    $seq = 1;
    if ($last_res->num_rows > 0) {
        $last_seq = intval(substr($last_res->fetch_assoc()['enrollment_no'], -4));
        $seq = $last_seq + 1;
    }
    $enrollment_no = "MG" . $year . str_pad($seq, 4, "0", STR_PAD_LEFT);

    // Generate Password
    $raw_password = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ@#$!"), 0, 10);
    $hashed_password = password_hash($raw_password, PASSWORD_BCRYPT);

    // Fields
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $father_name = mysqli_real_escape_string($conn, $_POST['father_name']);
    $mother_name = mysqli_real_escape_string($conn, $_POST['mother_name']);
    $dob = mysqli_real_escape_string($conn, $_POST['dob']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $admission_mode = mysqli_real_escape_string($conn, $_POST['admission_mode']);
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
    $photo = uploadAdminFile($_FILES['student_photo'], 'photos');
    $sign = uploadAdminFile($_FILES['student_sign'], 'signatures');
    $aadhar = uploadAdminFile($_FILES['aadhar_file'], 'documents');
    $cert = uploadAdminFile($_FILES['edu_cert_file'], 'documents');

    // Payment
    $course_fee = 0; // Fetch from DB if needed, but admin entry usually overrides
    
    $sql = "INSERT INTO admissions (
        enrollment_no, password, course_id, full_name, father_name, mother_name, dob, category, admission_mode,
        student_photo, student_sign, mobile, alt_mobile, email,
        pincode, country, state, city, address,
        highest_qual, school_name, board_university, passing_year, percentage,
        computer_knowledge, typing_speed, prev_course_done,
        aadhar_no, aadhar_file, edu_cert_file,
        course_fee, payment_status
    ) VALUES (
        '$enrollment_no', '$hashed_password', $course_id, '$full_name', '$father_name', '$mother_name', '$dob', '$category', '$admission_mode',
        '$photo', '$sign', '$mobile', '$alt_mobile', '$email',
        '$pincode', '$country', '$state', '$city', '$address',
        '$highest_qual', '$school_name', '$board', $passing_year, '$percentage',
        '$computer_knowledge', '$typing_speed', $prev_course_done,
        '$aadhar_no', '$aadhar', '$cert',
        $course_fee, 'success'
    )";
    
    if ($conn->query($sql) === TRUE) {
        $success_message = "Student added successfully! Enrollment No: <strong>$enrollment_no</strong>";

        // Fetch SMTP Settings and Send Email
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
                        <p>Congratulations! Your admission has been confirmed.</p>
                        
                        <div style='background-color: #f9fafb; padding: 15px; border-radius: 8px; margin: 20px 0;'>
                            <h3 style='margin-top: 0; color: #333;'>Your Login Credentials</h3>
                            <p style='margin-bottom: 5px;'><strong>Enrollment No (User ID):</strong> <span style='color: #2563eb; font-weight: bold;'>$enrollment_no</span></p>
                            <p style='margin-bottom: 5px;'><strong>Password:</strong> <span style='color: #dc2626; font-weight: bold;'>$raw_password</span></p>
                            <p style='font-size: 12px; color: #666;'>Please change your password after your first login.</p>
                        </div>
                        
                        <a href='http://localhost/mg-skill/student/login.php' style='display: inline-block; background-color: #6f75ff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; font-weight: bold;'>Login to Student Dashboard</a>
                        
                        <br><br>
                        <p>Best Regards,<br>MG Skills Team</p>
                    </div>
                ";
                $mail->send();
            } catch (Exception $e) {
                // Email failed
            }
        }
    } else {
        $error_message = "Error: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Student - MG Skills</title>
    <style>
        :root{--active:#22c55e;--indigo:#6f75ff;--line:#e6e8ee;--text:#0b1020;--muted:#6f7787;--error:#ef4444;--success:#22c55e}
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:system-ui,-apple-system,sans-serif;background:#f8fafc;color:var(--text)}
        .admin-content{margin-left:260px;padding:20px}
        .admin-wrap{max-width:1000px;margin:0 auto}
        .page-header{margin-bottom:20px} .page-title{font-size:24px;font-weight:700}
        .card{background:#fff;padding:30px;border-radius:12px;box-shadow:0 2px 10px rgba(0,0,0,0.05);margin-bottom:20px}
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
        <div class="admin-wrap">
            <div class="page-header"><h1 class="page-title">Add New Student</h1></div>
            
            <?php if($success_message): ?><div class="alert alert-success"><?php echo $success_message; ?></div><?php endif; ?>
            <?php if($error_message): ?><div class="alert alert-error"><?php echo $error_message; ?></div><?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data">
                
                <div class="card">
                    <h3 class="section-title">Course & Admission Mode</h3>
                    <div class="grid-2">
                        <div class="form-group">
                            <label>Course *</label>
                            <select name="course_id" required>
                                <option value="">Select Course</option>
                                <?php foreach($courses as $c): ?>
                                    <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['title']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Admission Mode *</label>
                            <select name="admission_mode" required>
                                <option value="Online">Online</option>
                                <option value="Offline">Offline</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <h3 class="section-title">Basic Details</h3>
                    <div class="grid-2">
                        <div class="form-group"><label>Full Name *</label><input type="text" name="full_name" required></div>
                        <div class="form-group"><label>Date of Birth *</label><input type="date" name="dob" required></div>
                        <div class="form-group"><label>Father's Name *</label><input type="text" name="father_name" required></div>
                        <div class="form-group"><label>Mother's Name *</label><input type="text" name="mother_name" required></div>
                        <div class="form-group"><label>Category</label>
                            <select name="category"><option value="General">General</option><option value="OBC">OBC</option><option value="SC">SC</option><option value="ST">ST</option></select>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <h3 class="section-title">Contact & Address</h3>
                    <div class="grid-2">
                        <div class="form-group"><label>Mobile *</label><input type="text" name="mobile" required></div>
                        <div class="form-group"><label>Email *</label><input type="email" name="email" required></div>
                        <div class="form-group"><label>Alt Mobile</label><input type="text" name="alt_mobile"></div>
                        <div class="form-group"><label>Pincode *</label>
                            <input type="text" name="pincode" id="pincode" required>
                            <small id="pincode-msg" style="color: var(--indigo); display:none">Fetching details...</small>
                        </div>
                        <div class="form-group"><label>City</label><input type="text" name="city" id="city"></div>
                        <div class="form-group"><label>State</label><input type="text" name="state" id="state"></div>
                        <div class="form-group"><label>Country</label><input type="text" name="country" id="country" value="India"></div>
                    </div>
                    <div class="form-group"><label>Full Address</label><textarea name="address" required></textarea></div>
                </div>

                <div class="card">
                    <h3 class="section-title">Education & Skills</h3>
                    <div class="grid-2">
                        <div class="form-group"><label>Highest Qualification</label><input type="text" name="highest_qual" required></div>
                        <div class="form-group"><label>School/College</label><input type="text" name="school_name" required></div>
                        <div class="form-group"><label>Board/Univ</label><input type="text" name="board" required></div>
                        <div class="form-group"><label>Passing Year</label><input type="number" name="passing_year" required></div>
                        <div class="form-group"><label>Percentage</label><input type="text" name="percentage" required></div>
                        <div class="form-group"><label>Computer Knowledge</label>
                            <select name="computer_knowledge"><option value="Beginner">Beginner</option><option value="Intermediate">Intermediate</option><option value="Advanced">Advanced</option></select>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <h3 class="section-title">Documents</h3>
                    <div class="grid-2">
                        <div class="form-group"><label>Aadhar Number</label><input type="text" name="aadhar_no" required></div>
                        <div class="form-group"><label>Student Photo</label><input type="file" name="student_photo"></div>
                        <div class="form-group"><label>Signature</label><input type="file" name="student_sign"></div>
                        <div class="form-group"><label>Aadhar File</label><input type="file" name="aadhar_file"></div>
                        <div class="form-group"><label>Cert File</label><input type="file" name="edu_cert_file"></div>
                    </div>
                </div>

                <div style="margin-bottom: 50px;"><button type="submit" class="btn">Add Student</button></div>
            </form>
        </div>
    </main>
    <script>
        const pincodeInput = document.getElementById('pincode');
        const cityInput = document.getElementById('city');
        const stateInput = document.getElementById('state');
        const countryInput = document.getElementById('country');
        const msgSpan = document.getElementById('pincode-msg');

        pincodeInput.addEventListener('blur', function() {
            const pincode = this.value.trim();
            if(pincode.length == 6) {
                msgSpan.style.display = 'block';
                msgSpan.textContent = 'Fetching details...';

                fetch('https://api.postalpincode.in/pincode/' + pincode).then(r=>r.json()).then(d=>{
                    if(d[0].Status=='Success'){
                        let p = d[0].PostOffice[0];
                        cityInput.value = p.District;
                        stateInput.value = p.State;
                        countryInput.value = p.Country;
                        msgSpan.textContent = 'Details fetched!';
                        msgSpan.style.color = '#22c55e';
                    } else {
                        msgSpan.textContent = 'Invalid Pincode';
                        msgSpan.style.color = '#ef4444';
                    }
                }).catch(err => {
                    msgSpan.textContent = 'Error fetching details';
                });
            }
        });
    </script>
</body>
</html>
<?php $conn->close(); ?>
