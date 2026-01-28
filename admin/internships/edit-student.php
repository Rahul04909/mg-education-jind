<?php
// Include database configuration
require_once __DIR__ . '/../../database/db-config.php';
$conn = getDbConnection();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$student = null;

// Fetch Student Data
if($id > 0) {
    $stmt = $conn->prepare("SELECT * FROM internship_enrollments WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $student = $stmt->get_result()->fetch_assoc();
}

if(!$student) {
    die("Student not found.");
}

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

    // Inputs
    $internship_id = intval($_POST['internship_id']);
    $session_id = isset($_POST['session_id']) ? intval($_POST['session_id']) : NULL;
    
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $father_name = mysqli_real_escape_string($conn, $_POST['father_name']);
    $mother_name = mysqli_real_escape_string($conn, $_POST['mother_name']);
    $dob = mysqli_real_escape_string($conn, $_POST['dob']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    
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
    
    // Handle File Uploads (Keep old if not new)
    $photo_path = $student['student_photo'];
    if(isset($_FILES['student_photo']) && $_FILES['student_photo']['error'] == 0) {
        $new_path = uploadAdminFile($_FILES['student_photo'], 'photos');
        if($new_path) $photo_path = $new_path;
    }

    $sign_path = $student['student_sign'];
    if(isset($_FILES['student_sign']) && $_FILES['student_sign']['error'] == 0) {
        $new_path = uploadAdminFile($_FILES['student_sign'], 'signatures');
        if($new_path) $sign_path = $new_path;
    }

    $aadhar_path = $student['aadhar_file'];
    if(isset($_FILES['aadhar_file']) && $_FILES['aadhar_file']['error'] == 0) {
        $new_path = uploadAdminFile($_FILES['aadhar_file'], 'documents');
        if($new_path) $aadhar_path = $new_path;
    }

    $cert_path = $student['edu_cert_file'];
    if(isset($_FILES['edu_cert_file']) && $_FILES['edu_cert_file']['error'] == 0) {
        $new_path = uploadAdminFile($_FILES['edu_cert_file'], 'documents');
        if($new_path) $cert_path = $new_path;
    }
    
    // Update Query
    $sql = "UPDATE internship_enrollments SET 
            internship_id = $internship_id,
            session_id = " . ($session_id ? $session_id : "NULL") . ",
            full_name = '$full_name',
            father_name = '$father_name',
            mother_name = '$mother_name',
            dob = '$dob',
            category = '$category',
            mobile = '$mobile',
            alt_mobile = '$alt_mobile',
            email = '$email',
            pincode = '$pincode',
            country = '$country',
            state = '$state',
            city = '$city',
            address = '$address',
            highest_qual = '$highest_qual',
            school_name = '$school_name',
            board_university = '$board',
            passing_year = $passing_year,
            percentage = '$percentage',
            computer_knowledge = '$computer_knowledge',
            typing_speed = '$typing_speed',
            aadhar_no = '$aadhar_no',
            student_photo = '$photo_path',
            student_sign = '$sign_path',
            aadhar_file = '$aadhar_path',
            edu_cert_file = '$cert_path'
            WHERE id = $id";
    
    if ($conn->query($sql) === TRUE) {
        $success_message = "Student Details Updated Successfully!";
        // Refresh data
        $stmt = $conn->prepare("SELECT * FROM internship_enrollments WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $student = $stmt->get_result()->fetch_assoc();
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
    <title>Edit Student - MG Admin</title>
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
        
        .current-file{font-size:12px;color:var(--muted);margin-top:5px}
        .current-file a{color:var(--indigo)}
    </style>
</head>
<body>
    <main class="admin-content">
        <div class="admin-wrap">
            <div class="page-header">
                <div class="breadcrumb">
                    <a href="../index.php">Dashboard</a> › <a href="student-list.php">Internship Students</a> › Edit Student
                </div>
                <h1 class="page-title">Edit Student Details</h1>
                <p style="color:var(--muted)">Enrollment No: <?php echo htmlspecialchars($student['enrollment_no']); ?></p>
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
                        <select name="internship_id" id="internship_select" class="form-select" required onchange="fetchSessions()">
                            <option value="">-- Choose Internship --</option>
                            <?php foreach($internships as $i): ?>
                                <option value="<?php echo $i['id']; ?>" <?php echo ($student['internship_id'] == $i['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($i['title']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Select Session</label>
                        <select name="session_id" id="session_select" class="form-select">
                            <option value="">-- Select Session --</option>
                        </select>
                        <input type="hidden" id="current_session_id" value="<?php echo htmlspecialchars($student['session_id']); ?>">
                    </div>
                </div>

                <div class="card">
                    <h3 class="section-title">Student Details</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Full Name *</label>
                            <input type="text" name="full_name" class="form-input" value="<?php echo htmlspecialchars($student['full_name']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Father's Name *</label>
                            <input type="text" name="father_name" class="form-input" value="<?php echo htmlspecialchars($student['father_name']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Mother's Name *</label>
                            <input type="text" name="mother_name" class="form-input" value="<?php echo htmlspecialchars($student['mother_name']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Date of Birth *</label>
                            <input type="date" name="dob" class="form-input" value="<?php echo htmlspecialchars($student['dob']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Category *</label>
                            <select name="category" class="form-select" required>
                                <option value="General" <?php echo ($student['category']=='General')?'selected':''; ?>>General</option>
                                <option value="OBC" <?php echo ($student['category']=='OBC')?'selected':''; ?>>OBC</option>
                                <option value="SC" <?php echo ($student['category']=='SC')?'selected':''; ?>>SC</option>
                                <option value="ST" <?php echo ($student['category']=='ST')?'selected':''; ?>>ST</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <h3 class="section-title">Contact Information</h3>
                    <div class="form-grid">
                         <div class="form-group">
                            <label class="form-label">Mobile Number *</label>
                            <input type="text" name="mobile" class="form-input" pattern="[0-9]{10}" value="<?php echo htmlspecialchars($student['mobile']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Alt Mobile</label>
                            <input type="text" name="alt_mobile" class="form-input" pattern="[0-9]{10}" value="<?php echo htmlspecialchars($student['alt_mobile']); ?>">
                        </div>
                        <div class="form-group form-full">
                            <label class="form-label">Email ID *</label>
                            <input type="email" name="email" class="form-input" value="<?php echo htmlspecialchars($student['email']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Pincode *</label>
                            <input type="text" name="pincode" id="pincode" class="form-input" value="<?php echo htmlspecialchars($student['pincode']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">City *</label>
                            <input type="text" name="city" id="city" class="form-input" value="<?php echo htmlspecialchars($student['city']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">State *</label>
                            <input type="text" name="state" id="state" class="form-input" value="<?php echo htmlspecialchars($student['state']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Country</label>
                            <input type="text" name="country" id="country" class="form-input" value="<?php echo htmlspecialchars($student['country']); ?>" required>
                        </div>
                        <div class="form-group form-full">
                            <label class="form-label">Address *</label>
                            <textarea name="address" class="form-textarea" rows="2" required><?php echo htmlspecialchars($student['address']); ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <h3 class="section-title">Education & Skills</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label class="form-label">Highest Qualification *</label>
                            <input type="text" name="highest_qual" class="form-input" value="<?php echo htmlspecialchars($student['highest_qual']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">College/Institute *</label>
                            <input type="text" name="school_name" class="form-input" value="<?php echo htmlspecialchars($student['school_name']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Board/University *</label>
                            <input type="text" name="board" class="form-input" value="<?php echo htmlspecialchars($student['board_university']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Passing Year *</label>
                            <input type="number" name="passing_year" class="form-input" value="<?php echo htmlspecialchars($student['passing_year']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Percentage *</label>
                            <input type="text" name="percentage" class="form-input" value="<?php echo htmlspecialchars($student['percentage']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Computer Knowledge *</label>
                            <select name="computer_knowledge" class="form-select" required>
                                <option value="Beginner" <?php echo ($student['computer_knowledge']=='Beginner')?'selected':''; ?>>Beginner</option>
                                <option value="Intermediate" <?php echo ($student['computer_knowledge']=='Intermediate')?'selected':''; ?>>Intermediate</option>
                                <option value="Advanced" <?php echo ($student['computer_knowledge']=='Advanced')?'selected':''; ?>>Advanced</option>
                            </select>
                        </div>
                         <div class="form-group">
                            <label class="form-label">Typing Speed</label>
                            <input type="text" name="typing_speed" class="form-input" value="<?php echo htmlspecialchars($student['typing_speed']); ?>">
                        </div>
                    </div>
                </div>

                <div class="card">
                    <h3 class="section-title">Documents</h3>
                    <div class="form-grid">
                         <div class="form-group">
                            <label class="form-label">Aadhar No *</label>
                            <input type="text" name="aadhar_no" class="form-input" value="<?php echo htmlspecialchars($student['aadhar_no']); ?>" required>
                        </div>
                         <div class="form-group">
                            <label class="form-label">Upload Aadhar</label>
                            <input type="file" name="aadhar_file" class="form-input">
                            <?php if($student['aadhar_file']): ?>
                                <div class="current-file">Current: <a href="../../<?php echo $student['aadhar_file']; ?>" target="_blank">View</a></div>
                            <?php endif; ?>
                        </div>
                         <div class="form-group">
                            <label class="form-label">Student Photo</label>
                            <input type="file" name="student_photo" class="form-input">
                            <?php if($student['student_photo']): ?>
                                <div class="current-file">Current: <a href="../../<?php echo $student['student_photo']; ?>" target="_blank">View</a></div>
                            <?php endif; ?>
                        </div>
                         <div class="form-group">
                            <label class="form-label">Student Sign</label>
                            <input type="file" name="student_sign" class="form-input">
                            <?php if($student['student_sign']): ?>
                                <div class="current-file">Current: <a href="../../<?php echo $student['student_sign']; ?>" target="_blank">View</a></div>
                            <?php endif; ?>
                        </div>
                        <div class="form-group form-full">
                            <label class="form-label">Certificate/Marksheet</label>
                            <input type="file" name="edu_cert_file" class="form-input">
                            <?php if($student['edu_cert_file']): ?>
                                <div class="current-file">Current: <a href="../../<?php echo $student['edu_cert_file']; ?>" target="_blank">View</a></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div style="margin-bottom:50px">
                    <button type="submit" class="btn btn-primary" onclick="return confirm('Update student details?')">Update Details</button>
                    <a href="student-list.php" class="btn" style="background:#fff;border:1px solid #ccc;color:#333;margin-left:10px">Cancel</a>
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

    function fetchSessions() {
        let sel = document.getElementById('internship_select');
        let currentSess = document.getElementById('current_session_id').value;
        
        if(sel.value) {
            fetch('../../internship-enrollment/get-sessions.php?internship_id=' + sel.value)
            .then(res => res.json())
            .then(data => {
                let sessSelect = document.getElementById('session_select');
                sessSelect.innerHTML = '<option value="">-- Select Session --</option>';
                if(data.status === 'success' && data.data.length > 0) {
                    data.data.forEach(sess => {
                        let selected = (sess.id == currentSess) ? 'selected' : '';
                        sessSelect.innerHTML += `<option value="${sess.id}" ${selected}>${sess.session_name}</option>`;
                    });
                } else {
                    sessSelect.innerHTML = '<option value="">No Active Sessions</option>';
                }
            });
        } else {
            document.getElementById('session_select').innerHTML = '<option value="">-- Select Session --</option>';
        }
    }

    // Call on load
    document.addEventListener('DOMContentLoaded', fetchSessions);
    </script>
</body>
</html>
<?php $conn->close(); ?>
