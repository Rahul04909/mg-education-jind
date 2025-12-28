<?php
session_start();
require_once __DIR__ . '/../../database/db-config.php';

if (!isset($_SESSION['student_id'])) {
    header("Location: ../login.php");
    exit;
}

$conn = getDbConnection();
$student_id = $_SESSION['student_id'];

// Fetch Student Details
$sql = "SELECT * FROM admissions WHERE id = $student_id";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    echo "Student not found.";
    exit;
}

$student = $result->fetch_assoc();

// Format address
$address_parts = [
    $student['city'] ?? '',
    $student['state'] ?? '',
    $student['pincode'] ?? ''
];
$address = implode(', ', array_filter($address_parts));
$full_address = ($student['address'] ?? '') . ' ' . $address;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My ID Card - MG Skills</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #a855f7;
            --bg-body: #f8fafc;
        }
        body { margin: 0; font-family: 'Outfit', sans-serif; background: var(--bg-body); color: #1e293b; }
        .main-content { margin-left: 260px; padding: 40px; min-height: 100vh; }
        
        .page-header { margin-bottom: 40px; text-align: center; }
        .page-title { font-size: 28px; font-weight: 700; color: #1e293b; margin-bottom: 10px; }
        
        .id-card-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 40px;
        }

        .id-card-wrapper {
            position: relative;
            width: 600px; /* Assuming standard ID card width or image width */
            /* height will be determined by image aspect ratio */
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
            border-radius: 12px;
            overflow: hidden;
            background: white;
        }

        .card-img {
            display: block;
            width: 100%;
            height: auto;
        }

        /* Overlay Text Positioning - Tweaked based on visual reference */
        .card-content {
            position: absolute;
            top: 240px; /* Approximate based on image */
            left: 380px; /* Moving to the right white area */
            width: 250px;
            color: #0f172a;
            font-size: 14px;
            font-weight: 600;
            line-height: 1.8;
            text-align: left;
        }

        .data-row {
            display: flex;
            margin-bottom: 8px;
        }
        .data-label {
            width: 70px; /* Fixed width for labels like NAME : */
            font-weight: 700;
            color: #334155;
            text-transform: uppercase;
            font-size: 12px;
            flex-shrink: 0;
        }
        .data-value {
            flex: 1;
            font-weight: 600;
            color: #0f172a;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .user-photo-box {
            position: absolute;
            top: 130px; /* Adjust according to blue shape */
            left: 50px; /* Adjust left position */
            width: 120px;
            height: 150px;
            background: #cbd5e1;
            border: 4px solid white;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .user-photo {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .btn-download {
            background: var(--primary);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 50px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(168, 85, 247, 0.4);
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.2s;
            text-decoration: none;
        }
        .btn-download:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(168, 85, 247, 0.5); }

        @media print {
            .main-content { margin: 0; padding: 0; }
            .no-print { display: none; }
            .id-card-container { gap: 20px; page-break-inside: avoid; }
            .id-card-wrapper { box-shadow: none; border: 1px solid #ddd; }
        }
        
        @media (max-width: 768px) {
            .main-content { margin-left: 0; padding: 20px; }
            .id-card-wrapper { width: 100%; max-width: 400px; }
            /* Scaling logic for mobile would be needed for exact pixels, simple scaling here */
            .card-content { top: 40%; left: 60%; font-size: 2.5vw; } 
            /* Note: Pixel perfection on responsive ID cards is tricky without fixed container */
        }
    </style>
</head>
<body>

<div class="no-print">
    <?php include '../sidebar.php'; ?>
</div>

<main class="main-content">
    <div class="page-header no-print">
        <h1 class="page-title">Identity Card</h1>
        <button onclick="window.print()" class="btn-download">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
            Print / Download PDF
        </button>
    </div>

    <div class="id-card-container">
        <!-- Front Side -->
        <div class="id-card-wrapper">
            <img src="id-card-front-side.png" class="card-img" alt="ID Card Front">
            
            <!-- Photo (Optional/Placeholder based on design) -->
            <?php if (!empty($student['photo'])): ?>
                <div class="user-photo-box">
                    <img src="../../uploads/<?php echo htmlspecialchars($student['photo']); ?>" class="user-photo" alt="Student Object">
                </div>
            <?php endif; ?>

            <div class="card-content">
                <div class="data-row">
                    <div class="data-label">NAME :</div>
                    <div class="data-value"><?php echo htmlspecialchars($student['full_name']); ?></div>
                </div>
                <div class="data-row">
                    <div class="data-label">DOB :</div>
                    <div class="data-value"><?php 
                        echo (!empty($student['dob'])) ? date('d-m-Y', strtotime($student['dob'])) : 'N/A';
                    ?></div>
                </div>
                <div class="data-row">
                    <div class="data-label">ADDRESS :</div>
                    <div class="data-value" style="font-size:12px; line-height:1.4; white-space:normal;">
                        <?php echo htmlspecialchars($full_address); ?>
                    </div>
                </div>
                <div class="data-row" style="margin-top:5px;">
                    <div class="data-label">ID NO :</div>
                    <div class="data-value"><?php echo htmlspecialchars($student['enrollment_no']); ?></div>
                </div>
                <div class="data-row">
                    <div class="data-label">MOBILE :</div>
                    <div class="data-value"><?php echo htmlspecialchars($student['mobile']); ?></div>
                </div>
            </div>
        </div>

        <!-- Back Side -->
        <div class="id-card-wrapper">
            <img src="id-card-back.png" class="card-img" alt="ID Card Back">
        </div>
    </div>
</main>

</body>
</html>
