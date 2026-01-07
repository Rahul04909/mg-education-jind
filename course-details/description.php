<?php
?>
<style>
.desc{background:#fff}
.desc-wrap{max-width:1200px;margin:0 auto;padding:20px 16px 30px}

/* Grid Layout */
.desc-grid {
    display: grid;
    grid-template-columns: 2fr 1fr; /* 66% - 33% */
    gap: 30px;
}

/* Description Column */
.desc-card{border:1px solid #e6e8ee;background:#fff;border-radius:18px;padding:24px}
.desc-head{display:flex;align-items:center;gap:12px;margin-bottom:15px}
.desc-title{margin:0;color:#0b1020;font-size:24px;font-weight:800}
.desc-content{position:relative;color:#2b313b;font-size:16px;line-height:1.7}
.desc-content p{margin:12px 0}
.desc-content ul{margin:12px 0 0 0;padding-left:20px}
.desc-content li{margin:8px 0}

/* Callback Form Column */
.callback-card {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 18px;
    padding: 24px;
    position: sticky;
    top: 100px; /* Sticky effect */
    height: fit-content;
}
.cb-title {
    font-size: 20px;
    font-weight: 700;
    margin-bottom: 20px;
    color: #1e293b;
    display: flex;
    align-items: center;
    gap: 10px;
}
.cb-form .form-group {
    margin-bottom: 15px;
}
.cb-form label {
    display: block;
    margin-bottom: 6px;
    font-size: 14px;
    font-weight: 600;
    color: #475569;
}
.cb-form input {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    font-size: 14px;
    transition: border 0.2s;
}
.cb-form input:focus {
    outline: none;
    border-color: #6366f1;
}
.time-row {
    display: flex;
    gap: 10px;
}
.cb-btn {
    width: 100%;
    padding: 12px;
    background: #4f46e5;
    color: white;
    border: none;
    border-radius: 8px;
    font-weight: 700;
    cursor: pointer;
    margin-top: 10px;
    transition: background 0.2s;
}
.cb-btn:hover {
    background: #4338ca;
}

@media(max-width:900px){
    .desc-grid { grid-template-columns: 1fr; }
    .callback-card { position: static; margin-top: 20px; }
}
</style>

<section class="desc" aria-label="Course description">
  <div class="desc-wrap">
    <div class="desc-grid">
        
        <!-- Left: Description -->
        <div class="desc-card">
            <div class="desc-head">
                <h3 class="desc-title">About this Course</h3>
            </div>
            <div class="desc-content">
                <?php if (!empty($course['description'])): ?>
                    <?php echo $course['description']; ?>
                <?php else: ?>
                    <p>No description available for this course.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Right: Request Callback -->
        <div class="callback-card">
            <h3 class="cb-title">
                <svg width="24" height="24" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                Request a Callback
            </h3>
            <form id="callbackForm" class="cb-form">
                <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                <input type="hidden" name="page_url" value="<?php echo "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"; ?>">
                
                <div class="form-group">
                    <label>Your Name *</label>
                    <input type="text" name="full_name" required placeholder="Enter your name">
                </div>
                
                <div class="form-group">
                    <label>Mobile Number *</label>
                    <input type="tel" name="mobile" required pattern="[0-9]{10}" placeholder="10-digit mobile">
                </div>

                <div class="form-group">
                    <label>Email ID (Optional)</label>
                    <input type="email" name="email" placeholder="Enter your email">
                </div>

                <div class="form-group">
                    <label>Preferred Time to Call</label>
                    <div class="time-row">
                        <input type="time" name="schedule_from">
                        <span style="align-self:center">-</span>
                        <input type="time" name="schedule_to">
                    </div>
                </div>

                <button type="submit" class="cb-btn" id="cbSubmitParams">Request Callback</button>
                <div id="cbMsg" style="margin-top:10px; font-size:14px; display:none;"></div>
            </form>
        </div>

    </div>
  </div>
</section>

<script>
document.getElementById('callbackForm').addEventListener('submit', function(e) {
    e.preventDefault();
    let btn = document.getElementById('cbSubmitParams');
    let msg = document.getElementById('cbMsg');
    
    btn.disabled = true;
    btn.innerText = 'Sending...';
    
    let formData = new FormData(this);

    fetch('actions/submit-callback-request.php', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        msg.style.display = 'block';
        if(data.status === 'success') {
            msg.style.color = 'green';
            msg.innerText = data.message;
            this.reset();
        } else {
            msg.style.color = 'red';
            msg.innerText = data.message;
        }
        btn.disabled = false;
        btn.innerText = 'Request Callback';
    })
    .catch(err => {
        console.error(err);
        msg.style.display = 'block';
        msg.style.color = 'red';
        msg.innerText = 'Something went wrong.';
        btn.disabled = false;
        btn.innerText = 'Request Callback';
    });
});
</script>
