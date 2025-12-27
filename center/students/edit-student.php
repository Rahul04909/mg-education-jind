<?php
session_start();
require_once __DIR__ . '/../../database/db-config.php';

if (!isset($_SESSION['center_id'])) {
    header("Location: ../login.php");
    exit;
}

$center_id = $_SESSION['center_id'];
$conn = getDbConnection();
$id = intval($_GET['id']);
$success_message = "";
$error_message = "";

// Check permission
$check_sql = "SELECT * FROM admissions WHERE id = $id AND center_id = $center_id";
$check_res = $conn->query($check_sql);
if ($check_res->num_rows == 0) {
    die("Access Denied or Student Not Found.");
}
$student = $check_res->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $mobile = mysqli_real_escape_string($conn, $_POST['mobile']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    
    // Update logic (simplified for key fields)
    $sql = "UPDATE admissions SET 
            full_name = '$full_name',
            mobile = '$mobile',
            email = '$email'
            WHERE id = $id AND center_id = $center_id";
            
    if ($conn->query($sql) === TRUE) {
        $success_message = "Student updated successfully.";
        // Refresh data
        $student = $conn->query($check_sql)->fetch_assoc();
    } else {
        $error_message = "Error updating: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Student</title>
    <style>
        :root{--active:#22c55e;--indigo:#6f75ff;--line:#e6e8ee;--text:#0b1020;}
        body{font-family:'Outfit',sans-serif;background:#f8fafc;color:var(--text)}
        .admin-content{margin-left:260px;padding:30px}
        .form-group{margin-bottom:15px} label{display:block;margin-bottom:5px;font-weight:600}
        input{width:100%;padding:10px;border:1px solid var(--line);border-radius:8px}
        .btn{padding:10px 20px;background:var(--indigo);color:#fff;border:none;border-radius:8px;cursor:pointer}
        .alert{padding:15px;margin-bottom:20px;border-radius:8px;background:#dcfce7;color:#166534}
        .card{background:#fff;padding:30px;border-radius:12px;max-width:600px}
    </style>
</head>
<body>
    <?php include __DIR__ . '/../sidebar.php'; ?>
    <main class="admin-content">
        <h1>Edit Student</h1>
        <?php if($success_message) echo "<div class='alert'>$success_message</div>"; ?>
        
        <form method="POST">
            <div class="card">
                <div class="form-group"><label>Full Name</label><input type="text" name="full_name" value="<?php echo htmlspecialchars($student['full_name']); ?>"></div>
                <div class="form-group"><label>Mobile</label><input type="text" name="mobile" value="<?php echo htmlspecialchars($student['mobile']); ?>"></div>
                <div class="form-group"><label>Email</label><input type="email" name="email" value="<?php echo htmlspecialchars($student['email']); ?>"></div>
                <button type="submit" class="btn">Update Details</button>
            </div>
        </form>
    </main>
</body>
</html>
