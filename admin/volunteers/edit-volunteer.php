<?php
require_once __DIR__ . '/../../database/db-config.php';
$conn = getDbConnection();

if (!isset($_GET['id'])) { header("Location: index.php"); exit; }
$id = intval($_GET['id']);
$sql = "SELECT * FROM volunteers WHERE id = $id";
$result = $conn->query($sql);

if ($result->num_rows == 0) { echo "Volunteer not found"; exit; }
$row = $result->fetch_assoc();

// Handle Submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    function uploadUpdateFile($file, $sub_dir, $old_path) {
        if (!isset($file['name']) || $file['size'] == 0) return $old_path; // detailed check
        if ($file['error'] != 0) return $old_path;
        
        $target_dir = "../../assets/uploads/volunteers/" . $sub_dir . "/";
        if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . "_" . time() . "." . $ext;
        if (move_uploaded_file($file['tmp_name'], $target_dir . $filename)) {
            return "assets/uploads/volunteers/" . $sub_dir . "/" . $filename;
        }
        return $old_path;
    }

    $full_name = mysqli_real_escape_string($conn, trim($_POST['full_name']));
    $email = mysqli_real_escape_string($conn, trim($_POST['email']));
    $mobile = mysqli_real_escape_string($conn, trim($_POST['mobile']));
    $dob = mysqli_real_escape_string($conn, $_POST['dob']);
    $gender = mysqli_real_escape_string($conn, $_POST['gender']);
    $pincode = mysqli_real_escape_string($conn, $_POST['pincode']);
    $country = mysqli_real_escape_string($conn, $_POST['country']);
    $state = mysqli_real_escape_string($conn, $_POST['state']);
    $city = mysqli_real_escape_string($conn, $_POST['city']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $role = mysqli_real_escape_string($conn, $_POST['role']);
    $hours_per_week = intval($_POST['hours_per_week']);
    $weekdays = isset($_POST['weekdays']) ? json_encode($_POST['weekdays']) : '[]';
    $preferred_mode = mysqli_real_escape_string($conn, $_POST['preferred_mode']);
    $aadhar_no = mysqli_real_escape_string($conn, $_POST['aadhar_no']);
    $is_student = isset($_POST['is_student']) ? 1 : 0;
    $student_id_no = $is_student ? mysqli_real_escape_string($conn, $_POST['student_id_no']) : NULL;
    $message = mysqli_real_escape_string($conn, $_POST['message']);

    $photo_path = uploadUpdateFile($_FILES['photo_file'], 'photos', $row['photo_file']);
    $resume_path = uploadUpdateFile($_FILES['resume_file'], 'resumes', $row['resume_file']);
    $aadhar_path = uploadUpdateFile($_FILES['aadhar_file'], 'documents', $row['aadhar_file']);
    $student_id_path = $is_student ? uploadUpdateFile($_FILES['student_id_file'], 'documents', $row['student_id_file']) : NULL;

    $weekdays_esc = mysqli_real_escape_string($conn, $weekdays);

    $update_sql = "UPDATE volunteers SET 
        full_name = '$full_name', email = '$email', mobile = '$mobile', dob = '$dob', gender = '$gender',
        pincode = '$pincode', country = '$country', state = '$state', city = '$city', address = '$address',
        role = '$role', hours_per_week = $hours_per_week, weekdays = '$weekdays_esc', preferred_mode = '$preferred_mode',
        aadhar_no = '$aadhar_no', aadhar_file = '$aadhar_path', photo_file = '$photo_path', resume_file = '$resume_path',
        is_student = $is_student, student_id_no = '$student_id_no', student_id_file = '$student_id_path',
        message = '$message'
        WHERE id = $id";

    if ($conn->query($update_sql) === TRUE) {
        header("Location: index.php?msg=updated");
        exit;
    } else {
        $error = "Error: " . $conn->error;
    }
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Volunteer - MG Skills</title>
    <style>
        :root{--active:#22c55e;--indigo:#4f46e5;--line:#e2e8f0;--text:#1e293b;--muted:#64748b;--bg:#f8fafc;--white:#fff;}
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Outfit',system-ui,sans-serif;background:var(--bg);color:var(--text)}
        .admin-content{margin-left:260px;min-height:100vh;padding:30px;transition:margin-left .3s ease}
        body.sidebar-collapsed .admin-content{margin-left:80px}
        
        .page-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:24px}
        .page-title{font-size:24px;font-weight:700;color:var(--text)}
        
        .card { background: white; border-radius: 12px; border: 1px solid var(--line); padding: 30px; max-width: 1000px; }
        
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px; }
        .form-full { grid-column: 1/-1; }
        .form-group { margin-bottom: 15px; }
        .form-label { display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px; color: var(--text); }
        .form-control { width: 100%; padding: 10px 12px; border: 1px solid var(--line); border-radius: 8px; font-size: 14px; background: #fff; }
        
        .section-title { font-size: 16px; font-weight: 700; color: var(--indigo); border-bottom: 1px solid var(--line); padding-bottom: 10px; margin: 30px 0 20px; }
        
        .btn-submit { padding: 12px 24px; background: var(--indigo); color: white; border: none; border-radius: 8px; font-weight: 600; cursor: pointer; }
        .btn-cancel { padding: 12px 24px; background: white; color: var(--muted); border: 1px solid var(--line); border-radius: 8px; font-weight: 600; text-decoration: none; margin-right: 10px; }
        
        .checkbox-group { display: flex; flex-wrap: wrap; gap: 10px; }
        .custom-check { display: inline-flex; align-items: center; gap: 8px; font-size: 14px; cursor: pointer; }

        .file-link { font-size: 12px; color: var(--indigo); text-decoration: none; margin-top: 4px; display: block; }
    </style>
</head>
<body class="<?php echo isset($_COOKIE['sidebar_collapsed']) && $_COOKIE['sidebar_collapsed'] == 'true' ? 'sidebar-collapsed' : ''; ?>">

    <?php include '../../admin/sidebar.php'; ?>

    <div class="admin-content">
        <div class="page-header">
            <h1 class="page-title">Edit Volunteer</h1>
        </div>

        <form method="POST" class="card" enctype="multipart/form-data">
            
            <?php if(isset($error)): ?>
                <div style="background:#fee2e2;color:#ef4444;padding:15px;border-radius:8px;margin-bottom:20px"><?php echo $error; ?></div>
            <?php endif; ?>

            <div class="section-title" style="margin-top:0">Personal Information</div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Full Name *</label>
                    <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($row['full_name']); ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Email Address *</label>
                    <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($row['email']); ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Mobile Number *</label>
                    <input type="text" name="mobile" class="form-control" value="<?php echo htmlspecialchars($row['mobile']); ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Date of Birth *</label>
                    <input type="date" name="dob" class="form-control" value="<?php echo htmlspecialchars($row['dob']); ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Gender *</label>
                    <select name="gender" class="form-control" required>
                        <option value="Male" <?php echo $row['gender'] == 'Male' ? 'selected' : ''; ?>>Male</option>
                        <option value="Female" <?php echo $row['gender'] == 'Female' ? 'selected' : ''; ?>>Female</option>
                        <option value="Other" <?php echo $row['gender'] == 'Other' ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>
            </div>

            <div class="section-title">Location Details</div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Pincode *</label>
                    <input type="text" name="pincode" id="pincode" class="form-control" value="<?php echo htmlspecialchars($row['pincode']); ?>" required maxlength="6">
                </div>
                <div class="form-group">
                    <label class="form-label">City *</label>
                    <input type="text" name="city" id="city" class="form-control" value="<?php echo htmlspecialchars($row['city']); ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">State *</label>
                    <input type="text" name="state" id="state" class="form-control" value="<?php echo htmlspecialchars($row['state']); ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Country *</label>
                    <input type="text" name="country" id="country" class="form-control" value="<?php echo htmlspecialchars($row['country']); ?>" required>
                </div>
                <div class="form-group form-full">
                    <label class="form-label">Full Address *</label>
                    <textarea name="address" class="form-control" rows="2" required><?php echo htmlspecialchars($row['address']); ?></textarea>
                </div>
            </div>

            <div class="section-title">Role & Availability</div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Role Preference *</label>
                    <select name="role" class="form-control" required>
                        <option value="">Select Role</option>
                        <option value="Teaching (Online/Offline)" <?php echo $row['role'] == 'Teaching (Online/Offline)' ? 'selected' : ''; ?>>Teaching (Online/Offline)</option>
                        <option value="Blood Donation" <?php echo $row['role'] == 'Blood Donation' ? 'selected' : ''; ?>>Blood Donation</option>
                        <option value="Tree Plantation" <?php echo $row['role'] == 'Tree Plantation' ? 'selected' : ''; ?>>Tree Plantation</option>
                        <option value="Event Management" <?php echo $row['role'] == 'Event Management' ? 'selected' : ''; ?>>Event Management</option>
                        <option value="Fundraising" <?php echo $row['role'] == 'Fundraising' ? 'selected' : ''; ?>>Fundraising</option>
                        <option value="Social Media / Marketing" <?php echo $row['role'] == 'Social Media / Marketing' ? 'selected' : ''; ?>>Social Media / Marketing</option>
                        <option value="Doubt Solver" <?php echo $row['role'] == 'Doubt Solver' ? 'selected' : ''; ?>>Doubt Solver</option>
                        <option value="Content Creation" <?php echo $row['role'] == 'Content Creation' ? 'selected' : ''; ?>>Content Creation</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Preferred Mode *</label>
                    <select name="preferred_mode" class="form-control" required>
                        <option value="Online" <?php echo $row['preferred_mode'] == 'Online' ? 'selected' : ''; ?>>Online</option>
                        <option value="Offline" <?php echo $row['preferred_mode'] == 'Offline' ? 'selected' : ''; ?>>Offline</option>
                        <option value="Hybrid" <?php echo $row['preferred_mode'] == 'Hybrid' ? 'selected' : ''; ?>>Hybrid</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Hours Per Week</label>
                    <input type="number" name="hours_per_week" class="form-control" value="<?php echo htmlspecialchars($row['hours_per_week']); ?>" min="1">
                </div>
                <div class="form-group form-full">
                    <label class="form-label">Available Days</label>
                    <?php $selected_days = json_decode($row['weekdays'], true) ?? []; ?>
                    <div class="checkbox-group">
                        <label class="custom-check"><input type="checkbox" name="weekdays[]" value="Monday" <?php echo in_array('Monday', $selected_days) ? 'checked' : ''; ?>> Mon</label>
                        <label class="custom-check"><input type="checkbox" name="weekdays[]" value="Tuesday" <?php echo in_array('Tuesday', $selected_days) ? 'checked' : ''; ?>> Tue</label>
                        <label class="custom-check"><input type="checkbox" name="weekdays[]" value="Wednesday" <?php echo in_array('Wednesday', $selected_days) ? 'checked' : ''; ?>> Wed</label>
                        <label class="custom-check"><input type="checkbox" name="weekdays[]" value="Thursday" <?php echo in_array('Thursday', $selected_days) ? 'checked' : ''; ?>> Thu</label>
                        <label class="custom-check"><input type="checkbox" name="weekdays[]" value="Friday" <?php echo in_array('Friday', $selected_days) ? 'checked' : ''; ?>> Fri</label>
                        <label class="custom-check"><input type="checkbox" name="weekdays[]" value="Saturday" <?php echo in_array('Saturday', $selected_days) ? 'checked' : ''; ?>> Sat</label>
                        <label class="custom-check"><input type="checkbox" name="weekdays[]" value="Sunday" <?php echo in_array('Sunday', $selected_days) ? 'checked' : ''; ?>> Sun</label>
                    </div>
                </div>
            </div>

            <div class="section-title">Documents</div>
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label">Aadhar Number *</label>
                    <input type="text" name="aadhar_no" class="form-control" value="<?php echo htmlspecialchars($row['aadhar_no']); ?>" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Update Aadhar (PDF)</label>
                    <input type="file" name="aadhar_file" class="form-control" accept="application/pdf">
                    <?php if($row['aadhar_file']): ?><a href="../../<?php echo $row['aadhar_file']; ?>" target="_blank" class="file-link">Current: View Aadhar</a><?php endif; ?>
                </div>
                <div class="form-group">
                    <label class="form-label">Update Photo</label>
                    <input type="file" name="photo_file" class="form-control" accept="image/*">
                    <?php if($row['photo_file']): ?><a href="../../<?php echo $row['photo_file']; ?>" target="_blank" class="file-link">Current: View Photo</a><?php endif; ?>
                </div>
                <div class="form-group">
                    <label class="form-label">Update Resume</label>
                    <input type="file" name="resume_file" class="form-control" accept=".pdf,.doc,.docx">
                    <?php if($row['resume_file']): ?><a href="../../<?php echo $row['resume_file']; ?>" target="_blank" class="file-link">Current: View Resume</a><?php endif; ?>
                </div>
                <div class="form-full">
                    <label class="custom-check">
                        <input type="checkbox" name="is_student" id="isStudent" <?php echo $row['is_student'] ? 'checked' : ''; ?>>
                        <span>Is Student?</span>
                    </label>
                </div>
                <div id="studentFields" class="form-group form-full" style="display:<?php echo $row['is_student'] ? 'block' : 'none'; ?>;background:#f1f5f9;padding:15px;border-radius:8px">
                    <label class="form-label">Student ID Number</label>
                    <input type="text" name="student_id_no" class="form-control" value="<?php echo htmlspecialchars($row['student_id_no']); ?>" style="margin-bottom:10px">
                    <label class="form-label">Update ID Card</label>
                    <input type="file" name="student_id_file" class="form-control">
                    <?php if($row['student_id_file']): ?><a href="../../<?php echo $row['student_id_file']; ?>" target="_blank" class="file-link">Current: View ID</a><?php endif; ?>
                </div>
            </div>

            <div class="section-title">Motivation</div>
            <div class="form-group">
                <label class="form-label">Message *</label>
                <textarea name="message" class="form-control" rows="4" required><?php echo htmlspecialchars($row['message']); ?></textarea>
            </div>

            <div style="margin-top:30px">
                <a href="index.php" class="btn-cancel">Cancel</a>
                <button type="submit" class="btn-submit">Update Volunteer</button>
            </div>

        </form>
    </div>

<script>
    // Pincode
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
    // Student Toggle
    document.getElementById('isStudent').addEventListener('change', function() {
        document.getElementById('studentFields').style.display = this.checked ? 'block' : 'none';
    });
</script>
</body>
</html>
