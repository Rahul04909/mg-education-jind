<?php
require_once __DIR__ . '/../../database/db-config.php';
$conn = getDbConnection();

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$student = null;

if($id > 0) {
    $stmt = $conn->prepare("SELECT e.*, i.title as internship_title FROM internship_enrollments e LEFT JOIN internships i ON e.internship_id = i.id WHERE e.id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $student = $stmt->get_result()->fetch_assoc();
}

if(!$student) {
    die("Student not found.");
}

include __DIR__ . '/../sidebar.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Student - <?php echo htmlspecialchars($student['full_name']); ?></title>
    <style>
        :root{--indigo:#4f46e5;--line:#e2e8f0;--text:#1e293b;--muted:#64748b;--bg:#f8fafc}
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Outfit',sans-serif;background:var(--bg);color:var(--text)}
        .admin-content{margin-left:260px;min-height:100vh;padding:30px;transition:margin-left .3s ease}
        .admin-wrap{max-width:1000px;margin:0 auto}
        
        .page-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:30px}
        .page-title{font-size:28px;font-weight:800}
        .breadcrumb{color:var(--muted);margin-bottom:10px;font-size:14px}
        .breadcrumb a{color:var(--indigo);text-decoration:none}

        .card{background:#fff;border:1px solid var(--line);border-radius:18px;padding:30px;box-shadow:0 4px 12px rgba(0,0,0,.05);margin-bottom:20px}
        .section-title{font-size:18px;font-weight:700;margin-bottom:20px;border-bottom:1px solid var(--line);padding-bottom:10px;color:var(--indigo);display:flex;align-items:center;gap:10px}
        
        .info-grid{display:grid;grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));gap:25px}
        .info-item{display:flex;flex-direction:column;gap:6px}
        .info-label{font-size:12px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:0.5px}
        .info-value{font-size:15px;font-weight:500;color:var(--text)}
        
        .profile-section{display:flex;gap:30px;align-items:start;margin-bottom:30px}
        .profile-img{width:150px;height:150px;border-radius:12px;object-fit:cover;border:4px solid #fff;box-shadow:0 4px 12px rgba(0,0,0,0.1)}
        .profile-details{flex:1}
        
        .badge{padding:4px 12px;border-radius:99px;font-size:12px;font-weight:600;display:inline-block;background:var(--indigo);color:#fff}
        
        .doc-link{display:inline-flex;align-items:center;gap:8px;color:var(--indigo);text-decoration:none;font-weight:600;font-size:14px;padding:8px 16px;background:var(--bg);border-radius:8px;transition:all 0.2s}
        .doc-link:hover{background:#eef2ff;transform:translateY(-2px)}

        .btn{padding:10px 20px;border-radius:8px;font-weight:600;text-decoration:none;display:inline-flex;align-items:center;gap:8px;font-size:14px;border:none;cursor:pointer;transition:all 0.2s}
        .btn-primary{background:var(--indigo);color:#fff}
        .btn-secondary{background:#fff;border:1px solid var(--line);color:var(--text)}
    </style>
</head>
<body>
    <main class="admin-content">
        <div class="admin-wrap">
            <div class="page-header">
                <div>
                    <div class="breadcrumb">
                        <a href="index.php">Dashboard</a> › <a href="student-list.php">Internship Students</a> › View Student
                    </div>
                    <h1 class="page-title"><?php echo htmlspecialchars($student['full_name']); ?></h1>
                    <p style="color:var(--muted)">Enrollment No: <span style="font-family:monospace;font-weight:700;color:var(--indigo)"><?php echo htmlspecialchars($student['enrollment_no']); ?></span></p>
                </div>
                <div style="display:flex; gap:10px">
                    <a href="edit-student.php?id=<?php echo $student['id']; ?>" class="btn btn-primary">Edit Student</a>
                    <a href="student-list.php" class="btn btn-secondary">Back to List</a>
                </div>
            </div>

            <div class="card">
                <div class="profile-section">
                    <img src="../../<?php echo !empty($student['student_photo']) ? htmlspecialchars($student['student_photo']) : 'assets/images/avatar-placeholder.png'; ?>" class="profile-img" alt="Student Photo">
                    <div class="profile-details">
                        <div class="section-title">Personal Details</div>
                        <div class="info-grid">
                            <div class="info-item">
                                <span class="info-label">Full Name</span>
                                <span class="info-value"><?php echo htmlspecialchars($student['full_name']); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Father's Name</span>
                                <span class="info-value"><?php echo htmlspecialchars($student['father_name']); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Mother's Name</span>
                                <span class="info-value"><?php echo htmlspecialchars($student['mother_name']); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Date of Birth</span>
                                <span class="info-value"><?php echo date('d M, Y', strtotime($student['dob'])); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Category</span>
                                <span class="info-value"><?php echo htmlspecialchars($student['category']); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Gender</span>
                                <span class="info-value"><?php echo htmlspecialchars($student['gender'] ?? 'N/A'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="section-title">Internship Information</div>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Enrolled Internship</span>
                        <span class="info-value"><span class="badge"><?php echo htmlspecialchars($student['internship_title'] ?? 'N/A'); ?></span></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Enrollment Date</span>
                        <span class="info-value"><?php echo date('d M, Y', strtotime($student['created_at'])); ?></span>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="section-title">Contact Information</div>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Mobile Number</span>
                        <span class="info-value"><?php echo htmlspecialchars($student['mobile']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Alt Mobile</span>
                        <span class="info-value"><?php echo htmlspecialchars($student['alt_mobile'] ?: 'N/A'); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Email Address</span>
                        <span class="info-value"><?php echo htmlspecialchars($student['email']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Address</span>
                        <span class="info-value"><?php echo htmlspecialchars($student['address']); ?>, <?php echo htmlspecialchars($student['city']); ?>, <?php echo htmlspecialchars($student['state']); ?> - <?php echo htmlspecialchars($student['pincode']); ?></span>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="section-title">Education & Skills</div>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Highest Qualification</span>
                        <span class="info-value"><?php echo htmlspecialchars($student['highest_qual']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">School/College</span>
                        <span class="info-value"><?php echo htmlspecialchars($student['school_name']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Board/University</span>
                        <span class="info-value"><?php echo htmlspecialchars($student['board_university']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Passing Year</span>
                        <span class="info-value"><?php echo htmlspecialchars($student['passing_year']); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Percentage</span>
                        <span class="info-value"><?php echo htmlspecialchars($student['percentage']); ?>%</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Computer Knowledge</span>
                        <span class="info-value"><?php echo htmlspecialchars($student['computer_knowledge']); ?></span>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="section-title">Documents</div>
                <div style="display:flex; gap:15px; flex-wrap:wrap">
                    <?php if(!empty($student['aadhar_file'])): ?>
                    <a href="../../<?php echo htmlspecialchars($student['aadhar_file']); ?>" target="_blank" class="doc-link">
                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                        Aadhar Card
                    </a>
                    <?php endif; ?>

                    <?php if(!empty($student['edu_cert_file'])): ?>
                    <a href="../../<?php echo htmlspecialchars($student['edu_cert_file']); ?>" target="_blank" class="doc-link">
                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                        Education Certificate
                    </a>
                    <?php endif; ?>

                    <?php if(!empty($student['student_sign'])): ?>
                    <a href="../../<?php echo htmlspecialchars($student['student_sign']); ?>" target="_blank" class="doc-link">
                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                        Student Signature
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
<?php $conn->close(); ?>
