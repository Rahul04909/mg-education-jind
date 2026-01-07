<?php
require_once __DIR__ . '/../../includes/header.php';
?>
<style>
    :root {
        --primary: #1358db;
        --secondary: #0f1419;
        --light: #f3f4f6;
        --border: #e6e8ee;
        --white: #fff;
    }
    * { box-sizing: border-box; margin: 0; padding: 0; font-family: system-ui, -apple-system, sans-serif; }
    body { background: var(--light); color: var(--secondary); min-height: 100vh; }
    
    .page-header {
        background: linear-gradient(135deg, #1358db 0%, #06b6d4 100%);
        color: white;
        padding: 60px 0;
        text-align: center;
        margin-bottom: 40px;
    }
    .page-title { font-size: 32px; font-weight: 700; margin-bottom: 10px; line-height: 1.2; }
    .page-subtitle { font-size: 16px; opacity: 0.9; max-width: 600px; margin: 0 auto; line-height: 1.6; }

    .form-container {
        max-width: 1200px;
        margin: 0 auto 60px;
        padding: 0 20px;
        position: relative;
        z-index: 10;
    }
    
    .form-card {
        background: white;
        border-radius: 30px;
        box-shadow: 0 20px 40px rgba(0,0,0,0.05);
        padding: 40px;
        border: 1px solid white;
    }
    
    .section-title {
        font-size: 20px;
        font-weight: 700;
        margin: 30px 0 20px;
        padding-bottom: 10px;
        border-bottom: 1px solid var(--border);
        color: var(--primary);
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .section-number {
        background: var(--primary);
        color: white;
        width: 28px;
        height: 28px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        border: 2px solid rgba(255,255,255,0.2);
    }
    
    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 30px;
    }
    .form-full { grid-column: 1/-1; }
    
    .form-group { margin-bottom: 15px; }
    .form-label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        font-size: 14px;
        color: var(--secondary);
    }
    .form-control {
        width: 100%;
        padding: 12px;
        border: 1px solid var(--border);
        border-radius: 8px;
        font-size: 15px;
        transition: border .2s;
        background: white;
    }
    .form-control:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: none;
    }
    
    /* Custom Select Arrow */
    select.form-control {
        appearance: none;
        -webkit-appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23374151' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 16px center;
        background-size: 16px;
        padding-right: 40px;
    }

    /* Checkbox Group */
    .checkbox-group {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
    }
    .custom-check {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        padding: 10px 16px;
        border: 1px solid var(--border);
        border-radius: 8px;
        transition: all 0.2s;
        font-size: 14px;
        background: white;
    }
    .custom-check:hover { background: #f9fafb; }
    .custom-check input:checked + span { color: var(--primary); font-weight: 600; }
    .custom-check input { accent-color: var(--primary); }
    
    /* File Upload */
    .file-upload-box {
        width: 120px;
        height: 120px;
        border: 2px dashed var(--border);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        margin-top: 5px;
        background: #f9fafb;
        cursor: pointer;
        position: relative;
        transition: border-color 0.2s;
    }
    .file-upload-box:hover { border-color: var(--primary); }
    .file-upload-box img { width: 100%; height: 100%; object-fit: cover; }
    .upload-label { font-size: 12px; color: #64748b; text-align: center; padding: 10px; }
    
    .btn-submit {
        padding: 14px 28px;
        background: var(--primary);
        color: var(--white);
        border: none;
        border-radius: 8px;
        font-weight: 700;
        cursor: pointer;
        font-size: 16px;
        width: 100%;
        margin-top: 20px;
        transition: opacity 0.2s;
        box-shadow: none;
    }
    .btn-submit:hover { opacity: 0.9; transform: none; box-shadow: none; }
    .btn-submit:disabled { background: #ccc; cursor: not-allowed; }

    
    @media(max-width: 768px) {
        .form-grid { grid-template-columns: 1fr; }
        .page-title { font-size: 28px; }
        .form-card { padding: 24px; border-radius: 20px; }
    }

    /* Popup Styles */
    .popup-overlay {
        position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0,0,0,0.5); z-index: 1000;
        display: none; align-items: center; justify-content: center;
        backdrop-filter: blur(5px);
    }
    .popup-card {
        background: white; width: 100%; max-width: 400px;
        padding: 30px; border-radius: 20px; text-align: center;
        box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        animation: popUp 0.3s ease-out;
    }
    @keyframes popUp { from { transform: scale(0.9); opacity: 0; } to { transform: scale(1); opacity: 1; } }
    .popup-icon {
        width: 80px; height: 80px; background: #dcfce7; color: #16a34a;
        border-radius: 50%; display: flex; align-items: center; justify-content: center;
        margin: 0 auto 20px;
    }
    .popup-title { font-size: 24px; font-weight: 800; margin-bottom: 10px; color: #0f1419; }
    .popup-text { color: #64748b; line-height: 1.6; margin-bottom: 24px; }
    .popup-btn {
        display: block; width: 100%; padding: 14px; background: #1358db; color: white;
        text-decoration: none; font-weight: 700; border-radius: 12px;
    }
</style>

<div class="page-header">
    <h1 class="page-title">Join As A Volunteer</h1>
    <p class="page-subtitle">Be the change you wish to see in the world. Join our community and make a difference.</p>
</div>

<div class="form-container">
    <form id="volunteerForm" class="form-card" enctype="multipart/form-data">
        <!-- Form Content remains same, stripped for brevity in replace tool context if not replacing whole block -->
        <!-- ... (Existing Form Fields) ... -->
        
        <!-- 1. Personal Details -->
        <h3 class="section-title"><span class="section-number">1</span> Personal Details</h3>
        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">Full Name *</label>
                <input type="text" name="full_name" class="form-control" required placeholder="John Doe">
            </div>
            <div class="form-group">
                <label class="form-label">Date of Birth *</label>
                <input type="date" name="dob" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="form-label">Gender *</label>
                <select name="gender" class="form-control" required>
                    <option value="">Select Gender</option>
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Mobile Number *</label>
                <div style="position:relative">
                    <span style="position:absolute;left:16px;top:13px;color:#64748b;font-weight:600">+91</span>
                    <input type="tel" name="mobile" class="form-control" required style="padding-left:50px" pattern="[0-9]{10}" maxlength="10" placeholder="9876543210">
                </div>
            </div>
            <div class="form-group form-full">
                <label class="form-label">Email Address *</label>
                <input type="email" name="email" class="form-control" required placeholder="john@example.com">
            </div>
        </div>

        <!-- 2. Location Details -->
        <h3 class="section-title"><span class="section-number">2</span> Location Details</h3>
        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">Pincode *</label>
                <input type="text" name="pincode" id="pincode" class="form-control" required maxlength="6" placeholder="Enter 6-digit Pincode">
                <span id="pinMsg" style="font-size:12px; display:none; margin-top:4px"></span>
            </div>
            <div class="form-group">
                <label class="form-label">City *</label>
                <input type="text" name="city" id="city" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="form-label">State *</label>
                <input type="text" name="state" id="state" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="form-label">Country *</label>
                <input type="text" name="country" id="country" class="form-control" value="India" required>
            </div>
            <div class="form-group form-full">
                <label class="form-label">Full Address *</label>
                <textarea name="address" class="form-control" rows="2" required placeholder="House No, Street, Landmark"></textarea>
            </div>
        </div>

        <!-- 3. Interest & Availability -->
        <h3 class="section-title"><span class="section-number">3</span> Interest & Availability</h3>
        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">Role Preference *</label>
                <select name="role" class="form-control" required>
                    <option value="">Select Role</option>
                    <option value="Teaching (Online/Offline)">Teaching (Online/Offline)</option>
                    <option value="Blood Donation">Blood Donation</option>
                    <option value="Tree Plantation">Tree Plantation</option>
                    <option value="Event Management">Event Management</option>
                    <option value="Fundraising">Fundraising</option>
                    <option value="Social Media / Marketing">Social Media / Marketing</option>
                    <option value="Doubt Solver">Doubt Solver</option>
                    <option value="Content Creation">Content Creation</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Preferred Mode *</label>
                <select name="preferred_mode" class="form-control" required>
                    <option value="Online">Online</option>
                    <option value="Offline">Offline</option>
                    <option value="Hybrid">Hybrid</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Hours Per Week Availability</label>
                <input type="number" name="hours_per_week" class="form-control" min="1" max="168">
            </div>
            <div class="form-group form-full">
                <label class="form-label">Available Days</label>
                <div class="checkbox-group">
                    <label class="custom-check"><input type="checkbox" name="weekdays[]" value="Monday"> <span>Mon</span></label>
                    <label class="custom-check"><input type="checkbox" name="weekdays[]" value="Tuesday"> <span>Tue</span></label>
                    <label class="custom-check"><input type="checkbox" name="weekdays[]" value="Wednesday"> <span>Wed</span></label>
                    <label class="custom-check"><input type="checkbox" name="weekdays[]" value="Thursday"> <span>Thu</span></label>
                    <label class="custom-check"><input type="checkbox" name="weekdays[]" value="Friday"> <span>Fri</span></label>
                    <label class="custom-check"><input type="checkbox" name="weekdays[]" value="Saturday"> <span>Sat</span></label>
                    <label class="custom-check"><input type="checkbox" name="weekdays[]" value="Sunday"> <span>Sun</span></label>
                </div>
            </div>
        </div>

        <!-- 4. Documentation -->
        <h3 class="section-title"><span class="section-number">4</span> Documentation</h3>
        <div class="form-grid">
            <div class="form-group">
                <label class="form-label">Aadhar Number *</label>
                <input type="text" name="aadhar_no" class="form-control" required placeholder="XXXX XXXX XXXX">
            </div>
            <div class="form-group">
                <label class="form-label">Upload Aadhar (PDF) *</label>
                <input type="file" name="aadhar_file" class="form-control" accept="application/pdf" required>
            </div>
            <div class="form-group">
                <label class="form-label">Your Photo *</label>
                <div class="file-upload-box" onclick="document.getElementById('photoInput').click()">
                    <input type="file" name="photo_file" id="photoInput" accept="image/*" style="display:none" onchange="previewFile(this, 'photoPreview')" required>
                    <div class="upload-label">Click to Upload Photo</div>
                    <img id="photoPreview" class="preview-img">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Upload Resume (Optional)</label>
                <input type="file" name="resume_file" class="form-control" accept=".pdf,.doc,.docx">
            </div>
            
            <div class="form-full">
                <label class="custom-check">
                    <input type="checkbox" name="is_student" id="isStudent" onchange="toggleStudent(this.checked)">
                    <span>Are you currently a student?</span>
                </label>
            </div>
            
            <div id="studentFields" class="form-grid form-full" style="display:none; margin-top:20px; background:#f1f5f9; padding:20px; border-radius:10px">
                <div class="form-group">
                    <label class="form-label">Student ID Number</label>
                    <input type="text" name="student_id_no" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Upload Student ID</label>
                    <div class="file-upload-box" onclick="document.getElementById('idInput').click()">
                        <input type="file" name="student_id_file" id="idInput" accept="image/*" style="display:none" onchange="previewFile(this, 'idPreview')">
                        <div class="upload-label">Click to Upload ID</div>
                        <img id="idPreview" class="preview-img">
                    </div>
                </div>
            </div>
        </div>

        <!-- 5. Message -->
        <h3 class="section-title"><span class="section-number">5</span> Motivation</h3>
        <div class="form-group">
            <label class="form-label">Why do you want to volunteer with us? *</label>
            <textarea name="message" class="form-control" rows="4" required placeholder="Share your motivation..."></textarea>
        </div>

        <button type="submit" class="btn-submit" id="submitBtn">Submit Application</button>

    </form>
</div>

<!-- Success Popup -->
<div class="popup-overlay" id="successPopup">
    <div class="popup-card">
        <div class="popup-icon">
            <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
        </div>
        <h3 class="popup-title">Registration Successful!</h3>
        <p class="popup-text" id="popupMsg">Welcome to the team.</p>
        <a href="/" class="popup-btn">Go to Home</a>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>

<script>
    // Pincode Logic
    document.getElementById('pincode').addEventListener('blur', function() {
        let pin = this.value;
        let msg = document.getElementById('pinMsg');
        if(pin.length === 6) {
            msg.style.display='block';
            msg.innerText='Fetching location...';
            msg.style.color='#64748b';
            
            fetch('https://api.postalpincode.in/pincode/' + pin)
            .then(res => res.json())
            .then(data => {
                if(data[0].Status === 'Success') {
                    let po = data[0].PostOffice[0];
                    document.getElementById('city').value = po.District;
                    document.getElementById('state').value = po.State;
                    document.getElementById('country').value = po.Country;
                    msg.style.color = 'green';
                    msg.innerText = 'Location found!';
                } else {
                    msg.style.color = 'red';
                    msg.innerText = 'Invalid Pincode';
                }
            });
        }
    });

    // Student Toggle
    function toggleStudent(checked) {
        document.getElementById('studentFields').style.display = checked ? 'grid' : 'none';
        let inputs = document.querySelectorAll('#studentFields input');
        inputs.forEach(i => i.required = checked); // Toggle required
    }

    // File Preview
    function previewFile(input, id) {
        if(input.files && input.files[0]) {
            let reader = new FileReader();
            reader.onload = function(e) {
                let img = document.getElementById(id);
                img.src = e.target.result;
                img.style.display = 'block';
                input.parentElement.querySelector('.upload-label').style.display = 'none';
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Form Submission
    document.getElementById('volunteerForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        let btn = document.getElementById('submitBtn');
        let originalText = btn.innerText;
        btn.disabled = true;
        btn.innerText = 'Processing...';

        let formData = new FormData(this);

        fetch('../../actions/submit-volunteer.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if(data.status === 'success') {
                document.getElementById('popupMsg').innerText = data.message;
                document.getElementById('successPopup').style.display = 'flex';
            } else {
                alert('Error: ' + data.message);
                btn.disabled = false;
                btn.innerText = originalText;
            }
        })
        .catch(err => {
            console.error(err);
            alert('Something went wrong. Please try again.');
            btn.disabled = false;
            btn.innerText = originalText;
        });
    });
</script>
