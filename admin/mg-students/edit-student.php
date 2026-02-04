<?php
require_once __DIR__ . '/../../database/db-config.php';
$conn = getDbConnection();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$sql = "SELECT * FROM admissions WHERE id = $id";
$result = $conn->query($sql);
if ($result->num_rows == 0) die("Student not found.");
$student = $result->fetch_assoc();

// Fetch Courses
$courses = [];
$c_res = $conn->query("SELECT id, title FROM courses WHERE is_active = 1");
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

function uploadEditFile($file, $dir) {
    if (!isset($file['name']) || $file['error'] != 0) return null;
    $target_dir = "../../assets/uploads/students/" . $dir . "/";
    if (!file_exists($target_dir)) mkdir($target_dir, 0755, true);
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = "ADMIN_EDIT_" . uniqid() . "." . $ext;
    if (move_uploaded_file($file['tmp_name'], $target_dir . $filename)) {
        return "assets/uploads/students/" . $dir . "/" . $filename;
    }
    return null;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $course_id = intval($_POST['course_id']);
    $session_id = intval($_POST['session_id']);
    
    // Basic Details
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $father_name = mysqli_real_escape_string($conn, $_POST['father_name']);
    $mother_name = mysqli_real_escape_string($conn, $_POST['mother_name']);
    $dob = mysqli_real_escape_string($conn, $_POST['dob']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $admission_mode = mysqli_real_escape_string($conn, $_POST['admission_mode']);
    
    // Contact
    $mobile = mysqli_real_escape_string($conn, $_POST['mobile']);
    $alt_mobile = mysqli_real_escape_string($conn, $_POST['alt_mobile']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $city = mysqli_real_escape_string($conn, $_POST['city']);
    $state = mysqli_real_escape_string($conn, $_POST['state']);
    $pincode = mysqli_real_escape_string($conn, $_POST['pincode']);
    $country = mysqli_real_escape_string($conn, $_POST['country']);

    // Education
    $highest_qual = mysqli_real_escape_string($conn, $_POST['highest_qual']);
    $school_name = mysqli_real_escape_string($conn, $_POST['school_name']);
    $board = mysqli_real_escape_string($conn, $_POST['board']);
    $passing_year = intval($_POST['passing_year']);
    $percentage = mysqli_real_escape_string($conn, $_POST['percentage']);
    $computer_knowledge = mysqli_real_escape_string($conn, $_POST['computer_knowledge']);
    
    // Docs
    $aadhar_no = mysqli_real_escape_string($conn, $_POST['aadhar_no']);
    
    // Check uploads
    $photo = uploadEditFile($_FILES['student_photo'], 'photos');
    $sign = uploadEditFile($_FILES['student_sign'], 'signatures');
    $aadhar_file = uploadEditFile($_FILES['aadhar_file'], 'documents');
    $cert_file = uploadEditFile($_FILES['edu_cert_file'], 'documents');
    
    $photo_sql = $photo ? ", student_photo='$photo'" : "";
    $sign_sql = $sign ? ", student_sign='$sign'" : "";
    $aadhar_sql = $aadhar_file ? ", aadhar_file='$aadhar_file'" : "";
    $cert_sql = $cert_file ? ", edu_cert_file='$cert_file'" : "";
    
    // Password Reset
    $pass_sql = "";
    if (!empty($_POST['new_password'])) {
        $hashed = password_hash($_POST['new_password'], PASSWORD_BCRYPT);
        $pass_sql = ", password='$hashed'";
    }

    $update_sql = "UPDATE admissions SET 
        course_id = $course_id,
        session_id = $session_id,
        full_name = '$full_name',
        father_name = '$father_name',
        mother_name = '$mother_name',
        dob = '$dob',
        category = '$category',
        admission_mode = '$admission_mode',
        mobile = '$mobile',
        alt_mobile = '$alt_mobile',
        email = '$email',
        address = '$address',
        city = '$city',
        state = '$state',
        pincode = '$pincode',
        country = '$country',
        highest_qual = '$highest_qual',
        school_name = '$school_name',
        board_university = '$board',
        passing_year = $passing_year,
        percentage = '$percentage',
        computer_knowledge = '$computer_knowledge',
        aadhar_no = '$aadhar_no'
        $photo_sql
        $sign_sql
        $aadhar_sql
        $cert_sql
        $pass_sql
        WHERE id = $id";
        
    if ($conn->query($update_sql) === TRUE) {
        $success_message = "Student updated successfully!";
        if (!empty($pass_sql)) {
            $success_message .= " Password was also reset.";
        }
        // Refresh data
        $student = $conn->query($sql)->fetch_assoc();
    } else {
        $error_message = "Error: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Student - MG Skills</title>
    <style>
        :root{--active:#22c55e;--indigo:#6f75ff;--line:#e6e8ee;--text:#0b1020;--muted:#6f7787;--error:#ef4444;--success:#22c55e}
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:system-ui,-apple-system,sans-serif;background:#f8fafc;color:var(--text)}
        .admin-content{margin-left:260px;padding:20px}
        .admin-wrap{max-width:1000px;margin:0 auto}
        .page-header{margin-bottom:20px;display:flex;justify-content:space-between;align-items:center}
        .page-title{font-size:24px;font-weight:700}
        .card{background:#fff;padding:30px;border-radius:12px;box-shadow:0 2px 10px rgba(0,0,0,0.05);margin-bottom:20px}
        .section-title{font-size:18px;font-weight:700;margin-bottom:20px;color:var(--indigo);border-bottom:1px solid var(--line);padding-bottom:10px}
        .grid-2{display:grid;grid-template-columns:1fr 1fr;gap:20px}
        .form-group{margin-bottom:15px} label{display:block;margin-bottom:5px;font-weight:600;font-size:14px}
        input,select,textarea{width:100%;padding:10px;border:1px solid var(--line);border-radius:8px;font-size:14px}
        .btn{padding:12px 24px;background:var(--indigo);color:#fff;border:none;border-radius:8px;cursor:pointer;font-weight:600}
        .alert{padding:15px;border-radius:8px;margin-bottom:20px} .alert-success{background:#dcfce7;color:#166534} .alert-error{background:#fee2e2;color:#991b1b}
        .file-status {font-size:12px; color:var(--active); margin-top:4px; display:inline-block;}
        .file-missing {color:var(--error);}
    </style>
</head>
<body>
    <?php include __DIR__ . "/../sidebar.php"; ?>
    <main class="admin-content">
        <div class="admin-wrap">
            <div class="page-header">
                <h1 class="page-title">Edit Student: <?php echo htmlspecialchars($student['full_name']); ?></h1>
                <a href="index.php" style="text-decoration:none; color:var(--muted); font-size:14px;">&larr; Back to List</a>
            </div>
            
            <?php if($success_message): ?><div class="alert alert-success"><?php echo $success_message; ?></div><?php endif; ?>
            <?php if($error_message): ?><div class="alert alert-error"><?php echo $error_message; ?></div><?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data">
                
                <div class="card">
                    <h3 class="section-title">Course & Admission</h3>
                    <div class="grid-2">
                        <div class="form-group">
                            <label>Course</label>
                            <select name="course_id" required>
                                <option value="">Select Course</option>
                                <?php foreach($courses as $c): ?>
                                    <option value="<?php echo $c['id']; ?>" <?php if($c['id'] == $student['course_id']) echo 'selected'; ?>><?php echo htmlspecialchars($c['title']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Admission Mode</label>
                            <select name="admission_mode">
                                <option value="Online" <?php if($student['admission_mode'] == 'Online') echo 'selected'; ?>>Online</option>
                                <option value="Offline" <?php if($student['admission_mode'] == 'Offline') echo 'selected'; ?>>Offline</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Session</label>
                            <select name="session_id" id="session_id" required>
                                <option value="">Select Session</option>
                                <!-- Populated by JS -->
                            </select>
                        </div>
                        <div class="form-group">
                             <label>Status</label>
                             <input type="text" value="<?php echo htmlspecialchars($student['payment_status']); ?>" readonly style="background:#f1f5f9; color:#64748b;">
                        </div>
                    </div>
                </div>

                <div class="card">
                    <h3 class="section-title">Security Settings</h3>
                    <div class="form-group">
                        <label style="color:var(--error)">Reset Password</label>
                        <input type="text" name="new_password" placeholder="Enter new password to reset (Leave empty to keep current)" autocomplete="off">
                        <small style="color:var(--muted)">Only enter a value here if you want to change the student's password directly.</small>
                    </div>
                </div>

                <div class="card">
                    <h3 class="section-title">Basic Details</h3>
                    <div class="grid-2">
                        <div class="form-group"><label>Full Name</label><input type="text" name="full_name" value="<?php echo htmlspecialchars($student['full_name']); ?>" required></div>
                        <div class="form-group"><label>Date of Birth</label><input type="date" name="dob" value="<?php echo htmlspecialchars($student['dob']); ?>" required></div>
                        <div class="form-group"><label>Father's Name</label><input type="text" name="father_name" value="<?php echo htmlspecialchars($student['father_name']); ?>" required></div>
                        <div class="form-group"><label>Mother's Name</label><input type="text" name="mother_name" value="<?php echo htmlspecialchars($student['mother_name']); ?>" required></div>
                        <div class="form-group"><label>Category</label>
                            <select name="category">
                                <option value="General" <?php if($student['category'] == 'General') echo 'selected'; ?>>General</option>
                                <option value="OBC" <?php if($student['category'] == 'OBC') echo 'selected'; ?>>OBC</option>
                                <option value="SC" <?php if($student['category'] == 'SC') echo 'selected'; ?>>SC</option>
                                <option value="ST" <?php if($student['category'] == 'ST') echo 'selected'; ?>>ST</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <h3 class="section-title">Contact & Address</h3>
                    <div class="grid-2">
                        <div class="form-group"><label>Mobile</label><input type="text" name="mobile" value="<?php echo htmlspecialchars($student['mobile']); ?>" required></div>
                        <div class="form-group"><label>Email</label><input type="email" name="email" value="<?php echo htmlspecialchars($student['email']); ?>" required></div>
                        <div class="form-group"><label>Alt Mobile</label><input type="text" name="alt_mobile" value="<?php echo htmlspecialchars($student['alt_mobile']); ?>"></div>
                        <div class="form-group"><label>Pincode</label>
                            <input type="text" name="pincode" id="pincode" value="<?php echo htmlspecialchars($student['pincode']); ?>" required>
                            <small id="pincode-msg" style="color: var(--indigo); display:none">Fetching details...</small>
                        </div>
                        <div class="form-group"><label>City</label><input type="text" name="city" id="city" value="<?php echo htmlspecialchars($student['city']); ?>"></div>
                        <div class="form-group"><label>State</label><input type="text" name="state" id="state" value="<?php echo htmlspecialchars($student['state']); ?>"></div>
                        <div class="form-group"><label>Country</label><input type="text" name="country" id="country" value="<?php echo !empty($student['country']) ? htmlspecialchars($student['country']) : 'India'; ?>"></div>
                    </div>
                    <div class="form-group"><label>Full Address</label><textarea name="address" required><?php echo htmlspecialchars($student['address']); ?></textarea></div>
                </div>

                <div class="card">
                    <h3 class="section-title">Education & Skills</h3>
                    <div class="grid-2">
                        <div class="form-group"><label>Highest Qualification</label><input type="text" name="highest_qual" value="<?php echo htmlspecialchars($student['highest_qual']); ?>"></div>
                        <div class="form-group"><label>School/College</label><input type="text" name="school_name" value="<?php echo htmlspecialchars($student['school_name']); ?>"></div>
                        <div class="form-group"><label>Board/Univ</label><input type="text" name="board" value="<?php echo htmlspecialchars($student['board_university']); ?>"></div>
                        <div class="form-group"><label>Passing Year</label><input type="number" name="passing_year" value="<?php echo htmlspecialchars($student['passing_year']); ?>"></div>
                        <div class="form-group"><label>Percentage</label><input type="text" name="percentage" value="<?php echo htmlspecialchars($student['percentage']); ?>"></div>
                        <div class="form-group"><label>Computer Knowledge</label>
                            <select name="computer_knowledge">
                                <option value="Beginner" <?php if($student['computer_knowledge'] == 'Beginner') echo 'selected'; ?>>Beginner</option>
                                <option value="Intermediate" <?php if($student['computer_knowledge'] == 'Intermediate') echo 'selected'; ?>>Intermediate</option>
                                <option value="Advanced" <?php if($student['computer_knowledge'] == 'Advanced') echo 'selected'; ?>>Advanced</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <h3 class="section-title">Documents & Uploads</h3>
                    <div class="grid-2">
                        <div class="form-group"><label>Aadhar Number</label><input type="text" name="aadhar_no" value="<?php echo htmlspecialchars($student['aadhar_no']); ?>"></div>
                        
                        <div class="form-group">
                            <label>Student Photo</label>
                            <input type="file" name="student_photo">
                            <?php if(!empty($student['student_photo'])): ?>
                                <span class="file-status">✓ Current file exists</span>
                            <?php endif; ?>
                        </div>
                        <div class="form-group">
                            <label>Signature</label>
                            <input type="file" name="student_sign">
                             <?php if(!empty($student['student_sign'])): ?>
                                <span class="file-status">✓ Current file exists</span>
                            <?php endif; ?>
                        </div>
                        <div class="form-group">
                            <label>Aadhar File</label>
                            <input type="file" name="aadhar_file">
                             <?php if(!empty($student['aadhar_file'])): ?>
                                <span class="file-status">✓ Current file exists</span>
                            <?php endif; ?>
                        </div>
                        <div class="form-group">
                            <label>Cert File</label>
                            <input type="file" name="edu_cert_file">
                             <?php if(!empty($student['edu_cert_file'])): ?>
                                <span class="file-status">✓ Current file exists</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div style="margin-bottom: 50px;"><button type="submit" class="btn">Update Student</button></div>
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

        // Dynamic Sessions
        const allSessions = <?php echo json_encode($sessions); ?>;
        const currentSessionId = <?php echo intval($student['session_id']); ?>;
        
        const courseSelect = document.querySelector('select[name="course_id"]');
        const sessionSelect = document.getElementById('session_id');

        function loadSessions(cId, selectedId = 0) {
            sessionSelect.innerHTML = '<option value="">Select Session</option>';
            if (cId && allSessions[cId]) {
                allSessions[cId].forEach(sess => {
                    const opt = document.createElement('option');
                    opt.value = sess.id;
                    opt.textContent = sess.session_name;
                    if(sess.id == selectedId) opt.selected = true;
                    sessionSelect.appendChild(opt);
                });
            }
        }

        courseSelect.addEventListener('change', function() {
            loadSessions(this.value);
        });

        // Initial Load
        if(courseSelect.value) {
            loadSessions(courseSelect.value, currentSessionId);
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>
