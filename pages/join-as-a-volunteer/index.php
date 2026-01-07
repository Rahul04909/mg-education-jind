<?php
require_once __DIR__ . '/../../includes/header.php';
?>
<style>
    :root {
        --primary: #1358db;
        --secondary: #0f1419;
        --light: #f3f4f6;
        --border: #e6e8ee;
    }
    body { background: #f8fafc; }
    
    .page-header {
        background: linear-gradient(135deg, #1358db 0%, #06b6d4 100%);
        color: white;
        padding: 60px 0;
        text-align: center;
        margin-bottom: 40px;
    }
    .page-title { font-size: 36px; font-weight: 800; margin-bottom: 10px; }
    .page-subtitle { font-size: 18px; opacity: 0.9; max-width: 600px; margin: 0 auto; line-height: 1.6; }

    .form-container {
        max-width: 900px;
        margin: 0 auto 60px;
        padding: 0 20px;
    }
    
    .form-card {
        background: white;
        border-radius: 20px;
        box-shadow: 0 20px 40px rgba(0,0,0,0.05);
        padding: 40px;
        border: 1px solid var(--border);
    }
    
    .section-title {
        font-size: 20px;
        font-weight: 700;
        color: var(--secondary);
        margin-bottom: 24px;
        padding-bottom: 12px;
        border-bottom: 2px solid #f1f5f9;
        display: flex;
        align-items: center;
        gap: 10px;
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
    }
    
    .form-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 24px;
        margin-bottom: 30px;
    }
    .form-full { grid-column: 1/-1; }
    
    .form-group { margin-bottom: 5px; }
    .form-label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #475569;
        font-size: 14px;
    }
    .form-control {
        width: 100%;
        padding: 12px 16px;
        border: 1px solid #cbd5e1;
        border-radius: 10px;
        font-size: 15px;
        transition: all 0.2s;
        background: #f8fafc;
    }
    .form-control:focus {
        outline: none;
        border-color: var(--primary);
        background: white;
        box-shadow: 0 0 0 4px rgba(19, 88, 219, 0.1);
    }
    
    /* Checkbox Group */
    .checkbox-group {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
    }
    .custom-check {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        padding: 8px 16px;
        border: 1px solid #cbd5e1;
        border-radius: 50px;
        transition: all 0.2s;
        font-size: 14px;
    }
    .custom-check:hover { background: #f1f5f9; }
    .custom-check input:checked + span { color: var(--primary); font-weight: 600; }
    .custom-check input { accent-color: var(--primary); }
    
    /* File Upload */
    .file-upload-box {
        border: 2px dashed #cbd5e1;
        border-radius: 12px;
        padding: 20px;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s;
        position: relative;
    }
    .file-upload-box:hover { border-color: var(--primary); background: #f8fafc; }
    .upload-label { color: #64748b; font-size: 14px; pointer-events: none; }
    .preview-img {
        max-width: 100%;
        max-height: 150px;
        border-radius: 8px;
        margin-top: 10px;
        display: none;
    }
    
    .btn-submit {
        width: 100%;
        padding: 16px;
        background: var(--primary);
        color: white;
        border: none;
        border-radius: 12px;
        font-size: 18px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s;
        box-shadow: 0 10px 20px rgba(19, 88, 219, 0.2);
    }
    .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 15px 30px rgba(19, 88, 219, 0.3); }
    
    @media(max-width: 768px) {
        .form-grid { grid-template-columns: 1fr; }
        .page-title { font-size: 28px; }
        .form-card { padding: 24px; }
    }
</style>

<div class="page-header">
    <h1 class="page-title">Join As A Volunteer</h1>
    <p class="page-subtitle">Be the change you wish to see in the world. Join our community and make a difference.</p>
</div>

<div class="form-container">
    <form id="volunteerForm" class="form-card" enctype="multipart/form-data">
        
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
                alert('Success! ' + data.message);
                window.location.reload();
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
