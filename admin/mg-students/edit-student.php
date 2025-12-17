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

$success_message = "";
$error_message = "";

function uploadEditFile($file, $dir) {
    if (!isset($file['name']) || $file['error'] != 0) return null;
    $target_dir = "../../assets/uploads/students/" . $dir . "/";
    if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = "ADMIN_" . uniqid() . "." . $ext;
    if (move_uploaded_file($file['tmp_name'], $target_dir . $filename)) {
        return "assets/uploads/students/" . $dir . "/" . $filename;
    }
    return null;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $course_id = intval($_POST['course_id']);
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $father_name = mysqli_real_escape_string($conn, $_POST['father_name']);
    $mother_name = mysqli_real_escape_string($conn, $_POST['mother_name']);
    $dob = mysqli_real_escape_string($conn, $_POST['dob']);
    $category = mysqli_real_escape_string($conn, $_POST['category']);
    $admission_mode = mysqli_real_escape_string($conn, $_POST['admission_mode']);
    $mobile = mysqli_real_escape_string($conn, $_POST['mobile']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $city = mysqli_real_escape_string($conn, $_POST['city']);
    $state = mysqli_real_escape_string($conn, $_POST['state']);
    $pincode = mysqli_real_escape_string($conn, $_POST['pincode']);
    
    // Check uploads
    $photo = uploadEditFile($_FILES['student_photo'], 'photos');
    $sign = uploadEditFile($_FILES['student_sign'], 'signatures');
    
    $photo_sql = $photo ? ", student_photo='$photo'" : "";
    $sign_sql = $sign ? ", student_sign='$sign'" : "";
    
    $update_sql = "UPDATE admissions SET 
        course_id = $course_id,
        full_name = '$full_name',
        father_name = '$father_name',
        mother_name = '$mother_name',
        dob = '$dob',
        category = '$category',
        admission_mode = '$admission_mode',
        mobile = '$mobile',
        email = '$email',
        address = '$address',
        city = '$city',
        state = '$state',
        pincode = '$pincode'
        $photo_sql
        $sign_sql
        WHERE id = $id";
        
    if ($conn->query($update_sql) === TRUE) {
        $success_message = "Student updated successfully!";
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
        :root{--active:#22c55e;--indigo:#6f75ff;--line:#e6e8ee;--text:#0b1020}
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:system-ui,-apple-system,sans-serif;background:#f8fafc}
        .admin-content{margin-left:260px;padding:20px} .admin-wrap{max-width:1000px;margin:0 auto}
        .card{background:#fff;padding:30px;border-radius:12px;margin-bottom:20px}
        .form-group{margin-bottom:15px} label{display:block;margin-bottom:5px;font-weight:600}
        input,select,textarea{width:100%;padding:10px;border:1px solid var(--line);border-radius:8px}
        .btn{padding:12px 24px;background:var(--indigo);color:#fff;border:none;border-radius:8px;cursor:pointer}
        .alert{padding:15px;margin-bottom:20px;border-radius:8px} .alert-success{background:#dcfce7;color:#166534}
    </style>
</head>
<body>
    <?php include __DIR__ . "/../sidebar.php"; ?>
    <main class="admin-content">
        <div class="admin-wrap">
            <h1>Edit Student</h1>
            <?php if($success_message): ?><div class="alert alert-success"><?php echo $success_message; ?></div><?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data">
                <div class="card">
                    <div class="form-group"><label>Course</label>
                        <select name="course_id">
                            <?php foreach($courses as $c): ?>
                                <option value="<?php echo $c['id']; ?>" <?php if($c['id'] == $student['course_id']) echo 'selected'; ?>><?php echo $c['title']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group"><label>Admission Mode</label>
                        <select name="admission_mode">
                            <option value="Online" <?php if($student['admission_mode'] == 'Online') echo 'selected'; ?>>Online</option>
                            <option value="Offline" <?php if($student['admission_mode'] == 'Offline') echo 'selected'; ?>>Offline</option>
                        </select>
                    </div>
                    <div class="form-group"><label>Full Name</label><input type="text" name="full_name" value="<?php echo htmlspecialchars($student['full_name']); ?>"></div>
                    <div class="form-group"><label>Mobile</label><input type="text" name="mobile" value="<?php echo htmlspecialchars($student['mobile']); ?>"></div>
                    <div class="form-group"><label>Email</label><input type="email" name="email" value="<?php echo htmlspecialchars($student['email']); ?>"></div>
                    
                    <div class="form-group"><label>Father's Name</label><input type="text" name="father_name" value="<?php echo htmlspecialchars($student['father_name']); ?>"></div>
                    <div class="form-group"><label>Mother's Name</label><input type="text" name="mother_name" value="<?php echo htmlspecialchars($student['mother_name']); ?>"></div>
                <div class="form-group"><label>Date of Birth</label><input type="date" name="dob" value="<?php echo htmlspecialchars($student['dob']); ?>"></div>
                
                <div class="form-group"><label>Category</label>
                    <select name="category">
                        <option value="General" <?php if($student['category'] == 'General') echo 'selected'; ?>>General</option>
                        <option value="OBC" <?php if($student['category'] == 'OBC') echo 'selected'; ?>>OBC</option>
                        <option value="SC" <?php if($student['category'] == 'SC') echo 'selected'; ?>>SC</option>
                        <option value="ST" <?php if($student['category'] == 'ST') echo 'selected'; ?>>ST</option>
                    </select>
                </div>

                <div class="form-group"><label>Pincode</label><input type="text" name="pincode" value="<?php echo htmlspecialchars($student['pincode']); ?>"></div>
                <div class="form-group"><label>City</label><input type="text" name="city" value="<?php echo htmlspecialchars($student['city']); ?>"></div>
                <div class="form-group"><label>State</label><input type="text" name="state" value="<?php echo htmlspecialchars($student['state']); ?>"></div>
                <div class="form-group"><label>Address</label><textarea name="address"><?php echo htmlspecialchars($student['address']); ?></textarea></div>
                
                <div class="form-group"><label>Update Photo</label><input type="file" name="student_photo"></div>
                <div class="form-group"><label>Update Signature</label><input type="file" name="student_sign"></div>
                
                </div>
                <button type="submit" class="btn">Update Changes</button>
            </form>
        </div>
    </main>
</body>
</html>
<?php $conn->close(); ?>
