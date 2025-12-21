<?php
require_once 'auth_check.php';
require_once '../database/db-config.php';

$conn = getDbConnection();
$center_id = $_SESSION['center_id'];
$bank_details = [];

// Fetch existing details
$sql = "SELECT * FROM center_bank_details WHERE center_id = $center_id";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $bank_details = $result->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bank Details - MG Skills</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root { --primary: #059669; --text: #0b1020; --muted: #64748b; --line: #e2e8f0; --bg: #f1f5f9; }
        body { font-family: 'Outfit', sans-serif; background: var(--bg); color: var(--text); margin: 0; padding: 0; }
        
        .main-content { margin-left: 280px; padding: 32px; min-height: 100vh; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; }
        .title { font-size: 24px; font-weight: 700; margin: 0; color: #1e293b; }
        
        .card { background: #fff; border-radius: 20px; border: 1px solid var(--line); padding: 32px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02); max-width: 900px; margin: 0 auto; }
        
        .section-title { font-size: 18px; font-weight: 700; color: var(--text); margin-bottom: 24px; padding-bottom: 12px; border-bottom: 1px solid var(--line); }
        
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }
        
        .form-group { margin-bottom: 20px; }
        .form-label { display: block; font-size: 13px; font-weight: 600; color: #475569; margin-bottom: 8px; }
        .form-input { 
            width: 100%; padding: 12px 16px; border: 1px solid #cbd5e1; border-radius: 10px; font-family: inherit; font-size: 15px; 
            transition: all 0.2s; box-sizing: border-box; background: #f8fafc;
        }
        .form-input:focus { outline: none; border-color: var(--primary); background: #fff; box-shadow: 0 0 0 4px rgba(5, 150, 105, 0.1); }
        
        .btn-primary { 
            background: var(--primary); color: white; border: none; padding: 14px 32px; border-radius: 12px; font-weight: 600; cursor: pointer; 
            font-size: 16px; transition: all 0.2s; display: inline-flex; align-items: center; gap: 8px;
        }
        .btn-primary:hover { background: #047857; transform: translateY(-1px); box-shadow: 0 10px 15px -3px rgba(5, 150, 105, 0.2); }
        
        /* QR Upload */
        .qr-upload-area {
            border: 2px dashed #cbd5e1;
            border-radius: 16px;
            padding: 2px;
            text-align: center;
            cursor: pointer;
            transition: all 0.2s;
            position: relative;
            background: #f8fafc;
            height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        .qr-upload-area:hover { border-color: var(--primary); background: #ecfdf5; }
        .qr-preview { width: 100%; height: 100%; object-fit: contain; display: none; }
        .qr-preview.active { display: block; }
        .qr-placeholder { pointer-events: none; }
        .file-input { display: none; }
        
        .qr-card { text-align: center; }
        .qr-label { font-size: 14px; font-weight: 600; margin-bottom: 8px; display: block; }

        @media (max-width: 900px) { .form-grid { grid-template-columns: 1fr; } .main-content { margin-left: 0; padding: 20px; } }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>
    
    <main class="main-content">
        <div class="header">
            <h1 class="title">Bank Details</h1>
        </div>
        
        <form action="../insert-db/insert-bank-details.php" method="POST" enctype="multipart/form-data">
            <div class="card">
                <h3 class="section-title">Account Information</h3>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Bank Name</label>
                        <input type="text" name="bank_name" class="form-input" placeholder="e.g. State Bank of India" value="<?php echo htmlspecialchars($bank_details['bank_name'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Account Holder Name</label>
                        <input type="text" name="account_holder" class="form-input" placeholder="e.g. MG Skill Center" value="<?php echo htmlspecialchars($bank_details['account_holder'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Account Number</label>
                        <input type="text" name="account_no" class="form-input" placeholder="Enter Account Number" value="<?php echo htmlspecialchars($bank_details['account_no'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">IFSC Code</label>
                        <input type="text" name="ifsc" class="form-input" placeholder="e.g. SBIN0001234" value="<?php echo htmlspecialchars($bank_details['ifsc'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Branch Name</label>
                        <input type="text" name="branch" class="form-input" placeholder="Enter Branch Name" value="<?php echo htmlspecialchars($bank_details['branch'] ?? ''); ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">UPI ID (Optional)</label>
                        <input type="text" name="upi_id" class="form-input" placeholder="e.g. center@upi" value="<?php echo htmlspecialchars($bank_details['upi_id'] ?? ''); ?>">
                    </div>
                </div>

                <h3 class="section-title" style="margin-top: 32px;">QR Codes (Payment Scanners)</h3>
                <div class="form-grid">
                    <!-- QR 1 -->
                    <div class="qr-card">
                        <span class="qr-label">Primary QR Code</span>
                        <div class="qr-upload-area" onclick="document.getElementById('qr1').click()">
                            <?php if(!empty($bank_details['qr_1'])): ?>
                                <img src="../<?php echo $bank_details['qr_1']; ?>" class="qr-preview active" id="preview1">
                                <div class="qr-placeholder" style="display:none" id="placeholder1">
                            <?php else: ?>
                                <img src="" class="qr-preview" id="preview1">
                                <div class="qr-placeholder" id="placeholder1">
                            <?php endif; ?>
                                <div style="color:var(--muted)">
                                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin:0 auto 8px"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                                    <div style="font-size:13px">Click to Upload QR 1</div>
                                </div>
                            </div>
                        </div>
                        <input type="file" name="qr_1" id="qr1" class="file-input" accept="image/*" onchange="previewImage(this, 'preview1', 'placeholder1')">
                    </div>

                    <!-- QR 2 -->
                    <div class="qr-card">
                        <span class="qr-label">Secondary QR Code (Optional)</span>
                        <div class="qr-upload-area" onclick="document.getElementById('qr2').click()">
                            <?php if(!empty($bank_details['qr_2'])): ?>
                                <img src="../<?php echo $bank_details['qr_2']; ?>" class="qr-preview active" id="preview2">
                                <div class="qr-placeholder" style="display:none" id="placeholder2">
                            <?php else: ?>
                                <img src="" class="qr-preview" id="preview2">
                                <div class="qr-placeholder" id="placeholder2">
                            <?php endif; ?>
                                <div style="color:var(--muted)">
                                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="margin:0 auto 8px"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                                    <div style="font-size:13px">Click to Upload QR 2</div>
                                </div>
                            </div>
                        </div>
                        <input type="file" name="qr_2" id="qr2" class="file-input" accept="image/*" onchange="previewImage(this, 'preview2', 'placeholder2')">
                    </div>
                </div>

                <div style="margin-top: 40px; text-align: right;">
                    <button type="submit" class="btn-primary">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
                        Save Bank Details
                    </button>
                </div>
            </div>
        </form>
    </main>
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function previewImage(input, previewId, placeholderId) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById(previewId).src = e.target.result;
                    document.getElementById(previewId).classList.add('active');
                    document.getElementById(placeholderId).style.display = 'none';
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
    <?php if(isset($_GET['status'])): ?>
    <script>
        Swal.fire({
            icon: '<?php echo $_GET['status'] == 'success' ? 'success' : 'error'; ?>',
            title: '<?php echo $_GET['status'] == 'success' ? 'Saved!' : 'Error'; ?>',
            text: '<?php echo isset($_GET['msg']) ? htmlspecialchars($_GET['msg']) : ""; ?>',
            confirmButtonColor: '#059669'
        });
    </script>
    <?php endif; ?>
</body>
</html>
<?php $conn->close(); ?>
