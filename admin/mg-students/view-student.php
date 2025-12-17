<?php
require_once __DIR__ . '/../../database/db-config.php';
$conn = getDbConnection();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$sql = "SELECT a.*, c.title as course_title FROM admissions a LEFT JOIN courses c ON a.course_id = c.id WHERE a.id = $id";
$result = $conn->query($sql);
if ($result->num_rows == 0) die("Student not found.");
$student = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Student - <?php echo htmlspecialchars($student['full_name']); ?></title>
    <style>
        :root{--active:#22c55e;--indigo:#6f75ff;--line:#e6e8ee;--text:#0b1020;--muted:#6f7787}
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:system-ui,-apple-system,sans-serif;background:#f8fafc;color:var(--text)}
        .admin-content{margin-left:260px;padding:20px} .admin-wrap{max-width:1000px;margin:0 auto}
        .card{background:#fff;padding:30px;border-radius:12px;box-shadow:0 2px 10px rgba(0,0,0,0.05);margin-bottom:20px}
        .section-title{font-size:18px;font-weight:700;margin-bottom:20px;color:var(--indigo);border-bottom:1px solid var(--line);padding-bottom:10px}
        .info-grid{display:grid;grid-template-columns:repeat(auto-fill, minmax(250px, 1fr));gap:20px}
        .info-item label{display:block;font-size:13px;color:var(--muted);margin-bottom:4px}
        .info-item div{font-size:15px;font-weight:600}
        .img-preview{width:150px;height:150px;object-fit:cover;border-radius:8px;border:1px solid var(--line);background:#f1f5f9}
        .btn{padding:10px 20px;background:var(--indigo);color:#fff;text-decoration:none;border-radius:8px;font-weight:600}
    </style>
</head>
<body>
    <?php include __DIR__ . "/../sidebar.php"; ?>
    <main class="admin-content">
        <div class="admin-wrap">
            <div style="display:flex;justify-content:space-between;margin-bottom:20px;align-items:center">
                <h1>Student Details</h1>
                <div>
                   <a href="edit-student.php?id=<?php echo $student['id']; ?>" class="btn">Edit Student</a>
                   <a href="index.php" class="btn" style="background:#fff;color:var(--text);border:1px solid var(--line)">Back</a>
                </div>
            </div>

            <div class="card">
                <div style="display:flex;gap:30px;margin-bottom:30px">
                    <div>
                        <img src="../../<?php echo !empty($student['student_photo']) ? $student['student_photo'] : 'assets/placeholder.png'; ?>" class="img-preview" alt="Student Photo">
                    </div>
                    <div>
                        <h2 style="margin-bottom:5px"><?php echo htmlspecialchars($student['full_name']); ?></h2>
                        <div style="color:var(--muted);margin-bottom:10px"><?php echo htmlspecialchars($student['enrollment_no']); ?></div>
                        <div class="info-grid" style="grid-template-columns:1fr 1fr; gap:30px">
                            <div class="info-item"><label>Course</label><div><?php echo htmlspecialchars($student['course_title']); ?></div></div>
                            <div class="info-item"><label>Admission Mode</label><div><?php echo htmlspecialchars($student['admission_mode']); ?></div></div>
                        </div>
                    </div>
                </div>

                <h3 class="section-title">Personal Details</h3>
                <div class="info-grid">
                    <div class="info-item"><label>Father's Name</label><div><?php echo htmlspecialchars($student['father_name']); ?></div></div>
                    <div class="info-item"><label>Mother's Name</label><div><?php echo htmlspecialchars($student['mother_name']); ?></div></div>
                    <div class="info-item"><label>DOB</label><div><?php echo htmlspecialchars($student['dob']); ?></div></div>
                    <div class="info-item"><label>Category</label><div><?php echo htmlspecialchars($student['category']); ?></div></div>
                </div>
            </div>

            <div class="card">
                <h3 class="section-title">Contact & Address</h3>
                <div class="info-grid">
                    <div class="info-item"><label>Mobile</label><div><?php echo htmlspecialchars($student['mobile']); ?></div></div>
                    <div class="info-item"><label>Email</label><div><?php echo htmlspecialchars($student['email']); ?></div></div>
                    <div class="info-item"><label>Address</label><div><?php echo htmlspecialchars($student['address']); ?></div></div>
                    <div class="info-item"><label>City/State</label><div><?php echo htmlspecialchars($student['city'] . ', ' . $student['state']); ?></div></div>
                    <div class="info-item"><label>Pincode</label><div><?php echo htmlspecialchars($student['pincode']); ?></div></div>
                </div>
            </div>
            
            <div class="card">
                <h3 class="section-title">Documents</h3>
                <div class="info-grid">
                    <?php if($student['student_sign']): ?>
                        <div class="info-item"><label>Signature</label><div><a href="../../<?php echo $student['student_sign']; ?>" target="_blank">View Signature</a></div></div>
                    <?php endif; ?>
                    <?php if($student['aadhar_file']): ?>
                        <div class="info-item"><label>Aadhar Card</label><div><a href="../../<?php echo $student['aadhar_file']; ?>" target="_blank">View Aadhar</a></div></div>
                    <?php endif; ?>
                    <?php if($student['edu_cert_file']): ?>
                        <div class="info-item"><label>Certificate</label><div><a href="../../<?php echo $student['edu_cert_file']; ?>" target="_blank">View Certificate</a></div></div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
<?php $conn->close(); ?>
