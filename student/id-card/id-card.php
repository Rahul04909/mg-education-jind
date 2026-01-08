<?php
session_start();
require_once __DIR__ . '/../../database/db-config.php';
require_once __DIR__ . '/../../vendor/autoload.php';

if (!isset($_SESSION['student_id'])) {
    header("Location: ../login.php");
    exit;
}

$conn = getDbConnection();
$student_id = $_SESSION['student_id'];

// Fetch Student Details with Center Name and Mobile
$sql = "SELECT a.*, c.center_name, c.mobile as center_mobile 
        FROM admissions a 
        LEFT JOIN centers c ON a.center_id = c.id 
        WHERE a.id = $student_id";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    echo "Student not found.";
    exit;
}

$student = $result->fetch_assoc();

// Determine Center Name & Contact
$display_center_name = "MG Education & Social Development Organisation";
$emergency_contact = "+91 9813354588";

if (!empty($student['center_name'])) {
    $display_center_name = $student['center_name'];
}

if (!empty($student['center_mobile'])) {
    $emergency_contact = $student['center_mobile'];
}

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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <style>
        :root {
            --primary: #a855f7;
            --bg-body: #f8fafc;
        }
        body { margin: 0; font-family: 'Outfit', sans-serif; background: var(--bg-body); color: #1e293b; }
        .main-content { margin-left: 260px; padding: 40px; min-height: 100vh; }
        
        .page-header { margin-bottom: 40px; text-align: center; }
        .page-title { font-size: 28px; font-weight: 700; color: #1e293b; margin-bottom: 20px; }
        
        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
            flex-wrap: wrap;
        }

        .id-card-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 80px;
            margin-top: 50px;
        }

        .id-card-wrapper {
            position: relative;
            width: 600px;
            height: 378px; /* Standard ID Card Aspect Ratio */
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
            border-radius: 12px;
            overflow: hidden;
            background: white;
        }

        .card-img {
            display: block;
            width: 100%;
            height: 100%;
            object-fit: fill;
        }

        /* Overlay Text Positioning */
        .card-content {
            position: absolute;
            top: 175px; 
            left: 240px; 
            width: 320px; /* Increased width for center name */
            color: #0f172a;
            font-size: 14px;
            font-weight: 600;
            line-height: 1.6;
            text-align: left;
        }

        .data-row {
            display: flex;
            margin-bottom: 5px; /* Reduced spacing to fit Center Name */
        }
        .data-label {
            width: 70px; 
            font-weight: 700;
            color: #334155;
            text-transform: uppercase;
            font-size: 11px;
            flex-shrink: 0;
            padding-top: 2px;
        }
        .data-value {
            flex: 1;
            font-weight: 600;
            color: #0f172a;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            font-size: 13px;
        }

        .user-photo-box {
            position: absolute;
            top: 115px; 
            left: 0px; 
            width: 213px;
            height: 260px;
            background: #cbd5e1;
            border: 4px solid white;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            overflow: hidden;
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
            padding: 12px 25px;
            border-radius: 50px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 4px 15px rgba(168, 85, 247, 0.4);
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
            text-decoration: none;
        }
        .btn-download:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(168, 85, 247, 0.5); }
        .btn-secondary { background: #64748b; box-shadow: 0 4px 15px rgba(100, 116, 139, 0.4); }
        .btn-secondary:hover { box-shadow: 0 6px 20px rgba(100, 116, 139, 0.5); }

        @media print {
            .main-content { margin: 0; padding: 0; }
            .no-print { display: none; }
            .id-card-container { gap: 20px; page-break-inside: avoid; }
            .id-card-wrapper { box-shadow: none; border: 1px solid #ddd; }
        }
        
        @media (max-width: 768px) {
            .main-content { margin-left: 0; padding: 20px; }
            .id-card-wrapper { width: 100%; max-width: 400px; }
            .card-content { top: 40%; left: 60%; font-size: 2.5vw; } 
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
        <div class="action-buttons">
            <button onclick="downloadFront()" class="btn-download">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                Download Front Side
            </button>
            <button onclick="downloadBack()" class="btn-download btn-secondary">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                Download Back Side
            </button>
        </div>
    </div>

    <div class="id-card-container">
        <!-- Front Side -->
        <div class="id-card-wrapper" id="card-front">
            <img src="id-card-front-side.png" class="card-img" alt="ID Card Front">
            
            <!-- Photo (Optional/Placeholder based on design) -->
            <?php if (!empty($student['student_photo'])): ?>
                <div class="user-photo-box">
                    <img src="../../<?php echo htmlspecialchars($student['student_photo']); ?>" class="user-photo" alt="Student Photo">
                </div>
            <?php endif; ?>

            <div class="card-content">
                <div class="data-row">
                    <div class="data-label">NAME :</div>
                    <div class="data-value"><?php echo htmlspecialchars($student['full_name']); ?></div>
                </div>
                 <div class="data-row">
                    <div class="data-label">CENTER :</div>
                    <div class="data-value" style="font-size:12px; white-space:normal; line-height:1.2;">
                        <?php echo htmlspecialchars($display_center_name); ?>
                    </div>
                </div>
                <div class="data-row">
                    <div class="data-label">DOB :</div>
                    <div class="data-value"><?php 
                        echo (!empty($student['dob'])) ? date('d-m-Y', strtotime($student['dob'])) : 'N/A';
                    ?></div>
                </div>
                <div class="data-row">
                    <div class="data-label">ADDRESS :</div>
                    <div class="data-value" style="font-size:11px; line-height:1.3; white-space:normal;">
                        <?php echo htmlspecialchars($full_address); ?>
                    </div>
                </div>
                <div class="data-row" style="margin-top:2px;">
                    <div class="data-label">ID NO :</div>
                    <div class="data-value"><?php echo htmlspecialchars($student['enrollment_no']); ?></div>
                </div>
                <div class="data-row">
                    <div class="data-label">MOBILE :</div>
                    <div class="data-value"><?php echo htmlspecialchars($student['mobile']); ?></div>
                </div>
            </div>
            
            <!-- Authorized Signatory -->
            <div class="signatory" style="position: absolute; bottom: 25px; right: 30px; text-align: center;">
                 <img src="mg-sign.png" style="width: 100px; display: block; margin: 0 auto 2px;">
                <div style="font-size: 10px; font-weight: 700; color: #0f172a; text-transform: uppercase;">Authorized Signatory</div>
            </div>
        </div>

        <!-- Back Side -->
        <div class="id-card-wrapper" id="card-back">
            <img src="back-side.png" class="card-img" alt="ID Card Back">
            
            <!-- Barcode on Back Bottom Middle -->
            <div class="barcode-area" style="position: absolute; bottom: 50px; left: 50%; transform: translateX(-50%); background: white; padding: 5px; border-radius: 4px;">
                <?php
                    $generator = new Picqer\Barcode\BarcodeGeneratorPNG();
                    echo '<img src="data:image/png;base64,' . base64_encode($generator->getBarcode($student['enrollment_no'], $generator::TYPE_CODE_128, 2, 30)) . '">';
                ?>
            </div>
            
            <!-- Emergency Contact -->
            <div style="position: absolute; bottom: 25px; width: 100%; text-align: center; color: #0f172a; font-size: 13px; font-weight: 700;">
                Emergency Contact: <?php echo htmlspecialchars($emergency_contact); ?>
            </div>
        </div>
    </div>
</main>


<script>
    function downloadFront() {
        const frontCard = document.getElementById('card-front');
        const btn = document.querySelector('.btn-download');
        const originalText = btn.innerHTML;
        
        btn.innerHTML = 'Generating...';
        btn.style.opacity = '0.7';

        // Use html2canvas to capture the element
        html2canvas(frontCard, {
            scale: 2, // High resolution
            useCORS: true, // Enable cross-origin images
            backgroundColor: null, // Transparent bg if any
            logging: false
        }).then(canvas => {
            // Convert to link and click it
            const link = document.createElement('a');
            link.download = 'ID_Card_Front_<?php echo $student['enrollment_no']; ?>.png';
            link.href = canvas.toDataURL('image/png');
            link.click();
            
            btn.innerHTML = originalText;
            btn.style.opacity = '1';
        }).catch(err => {
            console.error(err);
            alert('Error generating image');
            btn.innerHTML = originalText;
            btn.style.opacity = '1';
        });
    }

    function downloadBack() {
        const backCard = document.getElementById('card-back');
        const btn = document.querySelector('.btn-secondary');
        const originalText = btn.innerHTML;
        
        btn.innerHTML = 'Generating...';
        btn.style.opacity = '0.7';

        html2canvas(backCard, {
            scale: 2,
            useCORS: true,
            backgroundColor: null,
            logging: false
        }).then(canvas => {
            const link = document.createElement('a');
            link.download = 'ID_Card_Back_<?php echo $student['enrollment_no']; ?>.png';
            link.href = canvas.toDataURL('image/png');
            link.click();
            
            btn.innerHTML = originalText;
            btn.style.opacity = '1';
        }).catch(err => {
            console.error(err);
            alert('Error generating image');
            btn.innerHTML = originalText;
            btn.style.opacity = '1';
        });
    }
</script>

</body>
</html>
