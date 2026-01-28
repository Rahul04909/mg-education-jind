<?php
require_once __DIR__ . '/db-setup.php'; 
require_once __DIR__ . '/../database/db-config.php';
$conn = getDbConnection();

// Fetch Active Internships
$internships = [];
$sql = "SELECT id, title, fees, duration_value, duration_type FROM internships WHERE is_active = 1 ORDER BY title ASC";
$result = $conn->query($sql);
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $fees = json_decode($row['fees'], true);
        $row['amount'] = isset($fees['amount']) ? $fees['amount'] : 0;
        $internships[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Internship Enrollment - MG Education</title>
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <style>
        :root{--primary:#1358db;--secondary:#0f1419;--light:#f3f4f6;--border:#e6e8ee;--white:#fff;}
        *{box-sizing:border-box;margin:0;padding:0;font-family:system-ui,-apple-system,sans-serif}
        body{background:var(--light);color:var(--secondary);display:flex;min-height:100vh}
        
        /* Sidebar */
        .sidebar{width:300px;background:var(--primary);color:var(--white);padding:40px;position:fixed;height:100vh;left:0;top:0;display:flex;flex-direction:column;justify-content:space-between;z-index: 10;}
        .sidebar h1{font-size:32px;line-height:1.2;margin-bottom:20px}
        .sidebar p{opacity:0.9;line-height:1.6}
        .steps{margin-top:40px;list-style:none}
        .steps li{margin-bottom:20px;display:flex;align-items:center;gap:12px;opacity:0.7}
        .steps li.active{opacity:1;font-weight:700}
        .steps li .num{width:28px;height:28px;border-radius:50%;border:2px solid rgba(255,255,255,0.5);display:flex;align-items:center;justify-content:center;font-size:14px}
        .steps li.active .num{background:var(--white);color:var(--primary);border-color:var(--white)}

        /* Main Content */
        .main{margin-left:300px;flex:1;padding:40px;background:#fff;border-top-left-radius:30px;border-bottom-left-radius:30px;min-height:100vh;margin-top: 10px; margin-bottom: 10px; margin-right: 10px; box-shadow: -10px 0 30px rgba(0,0,0,0.05);}
        .container{max-width:800px;margin:0 auto}
        
        .section-title{font-size:20px;font-weight:700;margin:30px 0 20px;padding-bottom:10px;border-bottom:1px solid var(--border);color:var(--primary)}
        .form-grid{display:grid;grid-template-columns:1fr 1fr;gap:20px}
        .form-group{margin-bottom:15px}
        .form-full{grid-column:1/-1}
        label{display:block;margin-bottom:8px;font-weight:600;font-size:14px}
        input,select,textarea{width:100%;padding:12px;border:1px solid var(--border);border-radius:8px;font-size:15px;transition:border .2s}
        input:focus,select:focus,textarea:focus{outline:none;border-color:var(--primary)}
        
        .preview-box{width:120px;height:120px;border:2px dashed var(--border);border-radius:8px;display:flex;align-items:center;justify-content:center;overflow:hidden;margin-top:10px;background:#f9fafb}
        .preview-box img{width:100%;height:100%;object-fit:cover}
        
        .btn{padding:14px 28px;background:var(--primary);color:var(--white);border:none;border-radius:8px;font-weight:700;cursor:pointer;font-size:16px;width:100%;margin-top:20px}
        .btn:hover{opacity:0.9}
        .btn:disabled{background:#ccc;cursor:not-allowed}
        
        .fee-card{background:#f0f9ff;border:1px solid #bae6fd;padding:20px;border-radius:10px;margin:20px 0}
        .fee-row{display:flex;justify-content:space-between;margin-bottom:5px;font-weight:600}
        .total-row{border-top:1px dashed #0284c7;padding-top:10px;margin-top:10px;font-size:18px;color:#0369a1}
        
        @media(max-width:900px){
            .sidebar{position:relative;width:100%;height:auto;padding:30px}
            .main{margin:0;border-radius:0}
            .form-grid{grid-template-columns:1fr}
        }

        /* Course Row Grid */
        .internship-row {
            display: grid;
            grid-template-columns: 1fr; /* Single column since no session */
            gap: 20px;
        }

        /* Custom Select Design */
        select {
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23374151' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 16px center;
            background-size: 16px;
            padding-right: 40px;
        }
    </style>
</head>
<body>

<div class="sidebar">
    <div>
        <h1>MG Education<br>Internship Enrollment</h1>
        <p>Enroll in our premium internship programs and gain hands-on experience.</p>
        <ul class="steps">
            <li class="active"><span class="num">1</span> Select Program</li>
            <li class="active"><span class="num">2</span> Personal Info</li>
            <li class="active"><span class="num">3</span> Uploads</li>
            <li class="active"><span class="num">4</span> Payment (If applicable)</li>
        </ul>
    </div>
    <div style="font-size:12px;opacity:0.6">&copy; <?php echo date('Y'); ?> MG Skill. All rights reserved.<br>
      A Website Designed By Rahul Dhiman <br> +91-8059982049 </div>
</div>

<div class="main">
    <div class="container">
        <form id="enrollmentForm" enctype="multipart/form-data">
            
            <!-- Internship Selection -->
            <div class="internship-row">
                <div class="form-group">
                    <label>Select Internship *</label>
                    <select name="internship_id" id="internship_select" required onchange="onInternshipChange()">
                        <option value="">-- Choose an Internship --</option>
                        <?php 
                        $pre_select_id = isset($_GET['internship_id']) ? intval($_GET['internship_id']) : 0;
                        foreach($internships as $i): 
                            $selected = ($i['id'] == $pre_select_id) ? 'selected' : '';
                        ?>
                            <option value="<?php echo $i['id']; ?>" data-fee="<?php echo $i['amount']; ?>" data-dur="<?php echo $i['duration_value'] . ' ' . $i['duration_type']; ?>" <?php echo $selected; ?>>
                                <?php echo htmlspecialchars($i['title']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            
            <div id="feeBox" class="fee-card" style="display:none">
                <div class="fee-row"><span>Duration:</span> <span id="durDisplay"></span></div>
                <div class="fee-row total-row"><span>Enrollment Fees:</span> <span id="feeDisplay"></span></div>
                <input type="hidden" name="course_fee" id="course_fee">
            </div>

            <!-- Auto Trigger Change if Pre-selected -->
            <?php if($pre_select_id > 0): ?>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    onInternshipChange();
                });
            </script>
            <?php endif; ?>

            <!-- Basic Details -->
            <h3 class="section-title">Basic Details</h3>
            <div class="form-grid">
                <div class="form-group">
                    <label>Full Name *</label>
                    <input type="text" name="full_name" required>
                </div>
                <div class="form-group">
                    <label>Father's/Guardian Name *</label>
                    <input type="text" name="father_name" required>
                </div>
                <div class="form-group">
                    <label>Mother's Name *</label>
                    <input type="text" name="mother_name" required>
                </div>
                <div class="form-group">
                    <label>Date of Birth *</label>
                    <input type="date" name="dob" required>
                </div>
                <div class="form-group">
                    <label>Category *</label>
                    <select name="category" required>
                        <option value="General">General</option>
                        <option value="OBC">OBC</option>
                        <option value="SC">SC</option>
                        <option value="ST">ST</option>
                    </select>
                </div>
            </div>
            
            <div class="form-grid">
                <div class="form-group">
                    <label>Applicant Photo *</label>
                    <input type="file" name="student_photo" accept="image/*" required onchange="preview(this, 'photoPreview')">
                    <div class="preview-box" id="photoPreview"><span>Preview</span></div>
                </div>
                <div class="form-group">
                    <label>Applicant Signature *</label>
                    <input type="file" name="student_sign" accept="image/*" required onchange="preview(this, 'signPreview')">
                    <div class="preview-box" id="signPreview"><span>Preview</span></div>
                </div>
            </div>

            <!-- Contact Details -->
            <h3 class="section-title">Contact Details</h3>
            <div class="form-grid">
                <div class="form-group">
                    <label>Mobile Number *</label>
                    <input type="text" name="mobile" pattern="[0-9]{10}" maxlength="10" placeholder="10 Digit Mobile No" required>
                </div>
                <div class="form-group">
                    <label>Alternate Mobile (Optional)</label>
                    <input type="text" name="alt_mobile" pattern="[0-9]{10}" maxlength="10">
                </div>
                <div class="form-group form-full">
                    <label>Email ID *</label>
                    <input type="email" name="email" required>
                </div>
            </div>

            <!-- Address Details -->
            <h3 class="section-title">Address Details</h3>
            <div class="form-grid">
                <div class="form-group">
                    <label>Pincode *</label>
                    <input type="text" name="pincode" id="pincode" maxlength="6" required placeholder="Enter Pincode">
                    <small id="pinMsg" style="color:var(--primary);display:none">Fetching...</small>
                </div>
                <div class="form-group">
                    <label>City *</label>
                    <input type="text" name="city" id="city" required>
                </div>
                <div class="form-group">
                    <label>State *</label>
                    <input type="text" name="state" id="state" required>
                </div>
                <div class="form-group">
                    <label>Country *</label>
                    <input type="text" name="country" id="country" value="India" required>
                </div>
                <div class="form-group form-full">
                    <label>Full Address *</label>
                    <textarea name="address" rows="2" required placeholder="House No, Street, Area"></textarea>
                </div>
            </div>

            <!-- Educational Details -->
            <h3 class="section-title">Educational & Professional</h3>
            <div class="form-grid">
                <div class="form-group">
                    <label>Highest Qualification *</label>
                    <input type="text" name="highest_qual" required>
                </div>
                <div class="form-group">
                    <label>College/Institution Name *</label>
                    <input type="text" name="school_name" required>
                </div>
                <div class="form-group">
                    <label>University *</label>
                    <input type="text" name="board" required>
                </div>
                <div class="form-group">
                    <label>Passing Year *</label>
                    <input type="number" name="passing_year" min="1990" max="2099" required>
                </div>
                <div class="form-group">
                    <label>Percentage/CGPA *</label>
                    <input type="text" name="percentage" required>
                </div>
            </div>

            <!-- Skill Background -->
            <h3 class="section-title">Skill Assessment</h3>
            <div class="form-grid">
                <div class="form-group">
                    <label>Computer Knowledge *</label>
                    <select name="computer_knowledge" required>
                        <option value="Beginner">Beginner</option>
                        <option value="Intermediate">Intermediate</option>
                        <option value="Advanced">Advanced</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Typing Speed (if any)</label>
                    <input type="text" name="typing_speed">
                </div>
            </div>
            
            <div class="form-group">
                <label>Have you done any previous course with MG Skill? *</label>
                <select name="prev_course_done" id="prevCourseToggle" onchange="togglePrev(this.value)">
                    <option value="no">No</option>
                    <option value="yes">Yes</option>
                </select>
            </div>
            
            <div id="prevCourseBox" class="form-grid" style="display:none">
                <div class="form-group"><label>Previous Enrollment No</label><input type="text" name="prev_enroll_no"></div>
                <div class="form-group"><label>Course Name</label><input type="text" name="prev_course_name"></div>
                <div class="form-group"><label>Session/Year</label><input type="text" name="prev_course_session"></div>
            </div>

            <!-- Documents -->
            <h3 class="section-title">Documents</h3>
            <div class="form-grid">
                <div class="form-group">
                    <label>Aadhar Number *</label>
                    <input type="text" name="aadhar_no" required>
                </div>
                <div class="form-group">
                    <label>Upload Aadhar *</label>
                    <input type="file" name="aadhar_file" required>
                </div>
                <div class="form-group form-full">
                    <label>College ID / Degree / Marksheet *</label>
                    <input type="file" name="edu_cert_file" required>
                </div>
            </div>

            <button type="button" id="payBtn" class="btn" onclick="processEnrollment()">Proceed to Enrollment</button>
        </form>
    </div>
</div>

<script>
    // Pincode Logic
    document.getElementById('pincode').addEventListener('blur', function() {
        let pin = this.value;
        if(pin.length === 6) {
            let msg = document.getElementById('pinMsg');
            msg.style.display='block';
            msg.innerText='Fetching...';
            fetch('https://api.postalpincode.in/pincode/' + pin)
            .then(res => res.json())
            .then(data => {
                if(data[0].Status === 'Success') {
                    let po = data[0].PostOffice[0];
                    document.getElementById('city').value = po.District;
                    document.getElementById('state').value = po.State;
                    document.getElementById('country').value = po.Country;
                    msg.style.color = 'green';
                    msg.innerText = 'Found!';
                } else {
                    msg.style.color = 'red';
                    msg.innerText = 'Invalid Pincode';
                }
            });
        }
    });

    // Preview Logic
    function preview(input, id) {
        if(input.files && input.files[0]) {
            let reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById(id).innerHTML = '<img src="'+e.target.result+'">';
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Previous Course Toggle
    function togglePrev(val) {
        document.getElementById('prevCourseBox').style.display = (val === 'yes') ? 'grid' : 'none';
    }

    // Internship Update Logic
    function onInternshipChange() {
        let sel = document.getElementById('internship_select');
        let opt = sel.options[sel.selectedIndex];
        let fee = opt.dataset.fee;
        let dur = opt.dataset.dur;
        
        if(fee && fee !== '0') {
            document.getElementById('feeBox').style.display = 'block';
            document.getElementById('feeDisplay').innerText = '₹' + fee;
            document.getElementById('durDisplay').innerText = dur;
            document.getElementById('course_fee').value = fee;
            document.getElementById('payBtn').innerText = 'Make Payment';
        } else if (fee === '0') {
            document.getElementById('feeBox').style.display = 'block';
            document.getElementById('feeDisplay').innerText = 'Free';
            document.getElementById('durDisplay').innerText = dur;
            document.getElementById('course_fee').value = 0;
            document.getElementById('payBtn').innerText = 'Complete Enrollment';
        } else {
            document.getElementById('feeBox').style.display = 'none';
            document.getElementById('course_fee').value = 0;
            document.getElementById('payBtn').innerText = 'Proceed';
        }
    }

    // Enrollment Logic
    function processEnrollment() {
        let form = document.getElementById('enrollmentForm');
        if(!form.checkValidity()) {
            form.reportValidity();
            return;
        }
        
        // Check Fee
        let fee = parseFloat(document.getElementById('course_fee').value);
        if(fee > 0) {
            initiatePayment();
        } else {
            // Free Enrollment
            submitEnrollment(new FormData(form), {});
        }
    }

    function initiatePayment() {
        let form = document.getElementById('enrollmentForm');
        let payBtn = document.getElementById('payBtn');
        payBtn.disabled = true;
        payBtn.innerText = 'Processing...';

        let formData = new FormData(form);
        
        // 1. Create Order
        fetch('create-order.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if(data.status === 'success') {
                // Open Razorpay
                let options = {
                    "key": data.key_id,
                    "amount": data.amount * 100,
                    "currency": "INR",
                    "name": "MG Education",
                    "description": "Internship Fee",
                    "order_id": data.order_id,
                    "handler": function (response){
                        // Payment Success
                        submitEnrollment(formData, response);
                    },
                    "prefill": {
                        "name": formData.get('full_name'),
                        "email": formData.get('email'),
                        "contact": formData.get('mobile')
                    },
                    "theme": { "color": "#1358db" }
                };
                let rzp1 = new Razorpay(options);
                rzp1.open();
                rzp1.on('payment.failed', function (response){
                    alert("Payment Failed: " + response.error.description);
                    payBtn.disabled = false;
                    payBtn.innerText = 'Make Payment';
                });
            } else {
                alert("Order Creation Failed: " + data.message);
                payBtn.disabled = false;
                payBtn.innerText = 'Make Payment';
            }
        })
        .catch(err => {
            alert("Error: " + err);
            payBtn.disabled = false;
            payBtn.innerText = 'Make Payment';
        });
    }

    function submitEnrollment(formData, paymentResponse) {
        if(paymentResponse.razorpay_payment_id) {
            formData.append('razorpay_payment_id', paymentResponse.razorpay_payment_id);
            formData.append('razorpay_order_id', paymentResponse.razorpay_order_id);
            formData.append('razorpay_signature', paymentResponse.razorpay_signature);
        }
        
        let payBtn = document.getElementById('payBtn');
        payBtn.innerText = 'Finalizing...';
        
        fetch('process-enrollment.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if(data.status === 'success') {
                alert("Enrollment Successful! ID: " + data.enrollment_no);
                window.location.reload();
            } else {
                let msg = data.message;
                if(paymentResponse.razorpay_payment_id) {
                    msg += ". Payment ID: " + paymentResponse.razorpay_payment_id;
                }
                alert("Enrollment Failed: " + msg);
            }
            payBtn.disabled = false;
            payBtn.innerText = 'Proceed to Enrollment';
        });
    }
</script>

</body>
</html>
