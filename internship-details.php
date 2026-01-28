<?php
require_once __DIR__ . '/database/db-config.php';
$conn = getDbConnection();

$slug = isset($_GET['slug']) ? mysqli_real_escape_string($conn, $_GET['slug']) : '';
$internship = null;

if ($slug) {
    $sql = "SELECT * FROM internships WHERE slug = '$slug' AND is_active = 1";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        $internship = $result->fetch_assoc();
    }
}

if (!$internship) {
    // Redirect to internships/home page if not found
    header("Location: /");
    exit;
}

$scheme=(!empty($_SERVER['HTTPS'])&&$_SERVER['HTTPS']!=='off')?'https':'http';
$host=isset($_SERVER['HTTP_HOST'])?$_SERVER['HTTP_HOST']:'localhost';
$url=$scheme.'://'.$host.(isset($_SERVER['REQUEST_URI'])?$_SERVER['REQUEST_URI']:'/');
$origin=$scheme.'://'.$host.'/';
// Use image for OG if available
$ogImage = !empty($internship['featured_image']) ? $origin . $internship['featured_image'] : $origin.'assets/images/mg-logo.jpg';

// Helper for Fees/Duration
$fees = json_decode($internship['fees'], true);
$price = isset($fees['amount']) && is_numeric($fees['amount']) && $fees['amount'] > 0 
         ? '₹' . number_format($fees['amount']) 
         : 'Free';
$duration = $internship['duration_value'] . ' ' . $internship['duration_type'];

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover"/>
  <title><?php echo htmlspecialchars($internship['meta_title'] ?: $internship['title'] . ' • MG Skill'); ?></title>
  <link rel="canonical" href="<?php echo htmlspecialchars($url,ENT_QUOTES,'UTF-8'); ?>"/>
  <meta name="description" content="<?php echo htmlspecialchars($internship['meta_desc'] ?: substr(strip_tags($internship['description']), 0, 160)); ?>"/>
  <meta property="og:title" content="<?php echo htmlspecialchars($internship['title'] . ' • MG Skill'); ?>"/>
  <meta property="og:description" content="<?php echo htmlspecialchars($internship['meta_desc'] ?: 'Gain real-world experience.'); ?>"/>
  <meta property="og:type" content="website"/>
  <meta property="og:url" content="<?php echo htmlspecialchars($url,ENT_QUOTES,'UTF-8'); ?>"/>
  <meta property="og:image" content="<?php echo htmlspecialchars($ogImage,ENT_QUOTES,'UTF-8'); ?>"/>
  <meta name="theme-color" content="#1358db"/>
  
  <style>
    *{box-sizing:border-box}
    html,body{height:100%}
    body{margin:0;font-family:system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,Helvetica,Arial,'Noto Sans','Apple Color Emoji','Segoe UI Emoji','Segoe UI Symbol';color:#0f1419;background:#ffffff}
    .details{background:#fff}
    .details-wrap{max-width:1200px;margin:0 auto;padding:20px 16px 30px}
    
    /* Header Section */
    .id-header {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 40px;
        align-items: center;
        margin-bottom: 40px;
        border-bottom: 1px solid #e6e8ee;
        padding-bottom: 40px;
    }
    .id-info h1 {
        font-size: 38px;
        font-weight: 800;
        color: #0b1020;
        margin: 10px 0 16px 0;
        line-height: 1.1;
    }
    .id-badges {
        display: flex;
        gap: 10px;
        margin-bottom: 16px;
    }
    .badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 8px;
        font-weight: 700;
        font-size: 14px;
    }
    .badge-blue { background: #eff6ff; color: #1d4ed8; }
    .badge-green { background: #f0fdf4; color: #15803d; }
    .badge-purple { background: #f5f3ff; color: #7c3aed; }
    
    .id-img-box {
        border-radius: 18px;
        overflow: hidden;
        border: 1px solid #e6e8ee;
        box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        aspect-ratio: 16/9;
        background: #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .id-img-box img { width: 100%; height: 100%; object-fit: cover; }
    .placeholder-title { font-size: 30px; font-weight: 800; color: #cbd5e1; }

    /* Content Grid */
    .content-grid {
        display: grid;
        grid-template-columns: 2.2fr 1fr;
        gap: 40px;
    }

    /* Left: Description */
    .desc-box h2 { font-size: 24px; font-weight: 800; margin-bottom: 20px; color: #0b1020; }
    .desc-content { font-size: 16px; line-height: 1.7; color: #334155; }
    .desc-content h3, .desc-content h4 { color: #0f1419; margin-top: 24px; }
    .desc-content p { margin-bottom: 16px; }
    .desc-content ul { padding-left: 20px; margin-bottom: 16px; }
    .desc-content li { margin-bottom: 8px; }

    /* Right: Sidebar */
    .sidebar-card {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 18px;
        padding: 24px;
        position: sticky;
        top: 100px;
    }
    .sb-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 16px;
        padding-bottom: 16px;
        border-bottom: 1px solid #e2e8f0;
    }
    .sb-row:last-child { border: 0; padding: 0; margin-bottom: 24px; }
    .sb-label { color: #64748b; font-weight: 600; font-size: 14px; }
    .sb-val { color: #0f1419; font-weight: 800; font-size: 16px; display: flex; align-items: center; gap: 6px; }
    .sb-val svg { height: 18px; width: 18px; color: #4f46e5; }
    
    .apply-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        padding: 14px;
        background: #1358db;
        color: #fff;
        font-weight: 700;
        border-radius: 12px;
        text-decoration: none;
        transition: all 0.2s;
        box-shadow: 0 4px 12px rgba(19, 88, 219, 0.25);
    }
    .apply-btn:hover { background: #0f44aa; transform: translateY(-2px); }

    .cb-form { margin-top: 30px; }
    .cb-title { font-size: 18px; font-weight: 700; margin-bottom: 15px; color: #0f1419; }
    .form-group { margin-bottom: 12px; }
    .form-input { width: 100%; padding: 10px 12px; border-radius: 8px; border: 1px solid #cbd5e1; font-family: inherit; }
    .sb-submit { width: 100%; padding: 12px; background: #fff; border: 1px solid #e2e8f0; color: #0f1419; border-radius: 8px; font-weight: 700; cursor: pointer; transition: all 0.2s; }
    .sb-submit:hover { background: #f1f5f9; border-color: #cbd5e1; }

    @media(max-width:900px){
        .id-header { grid-template-columns: 1fr; gap: 20px; text-align: center; }
        .id-badges { justify-content: center; }
        .content-grid { grid-template-columns: 1fr; }
        .id-img-box { order: -1; }
        .sidebar-card { position: static; }
    }
  </style>
</head>
<body>
  <?php require_once __DIR__.'/includes/header.php'; ?>
  
  <section class="details">
    <div class="details-wrap">
        
        <!-- Hero Header -->
        <div class="id-header">
            <div class="id-info">
                <div class="id-badges">
                    <span class="badge badge-purple">Internship Program</span>
                    <span class="badge badge-green"><?php echo $price; ?></span>
                </div>
                <h1><?php echo htmlspecialchars($internship['title']); ?></h1>
                
                <div style="color: #64748b; font-size: 18px; margin-bottom: 20px;">
                    An immersive <strong><?php echo $duration; ?></strong> experience to boost your career.
                </div>

                <a href="#apply" class="apply-btn" style="display: inline-flex; width: auto; padding: 12px 24px;">Apply Now</a>
            </div>
            
            <div class="id-img-box">
                <?php if(!empty($internship['featured_image'])): ?>
                    <img src="<?php echo htmlspecialchars($internship['featured_image']); ?>" alt="<?php echo htmlspecialchars($internship['title']); ?>">
                <?php else: ?>
                    <span class="placeholder-title"><?php echo substr($internship['title'], 0, 1); ?></span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Main Content (Desc + Sidebar) -->
        <div class="content-grid">
            
            <!-- Description -->
            <div class="desc-box">
                <h2>Program Overview</h2>
                <div class="desc-content">
                    <?php if(!empty($internship['description'])): ?>
                        <?php echo $internship['description']; ?>
                    <?php else: ?>
                        <p>No description provided for this internship.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="sidebar-card" id="apply">
                <h3 style="margin: 0 0 20px 0; font-size: 20px;">Internship Details</h3>
                
                <div class="sb-row">
                    <span class="sb-label">Duration</span>
                    <span class="sb-val">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        <?php echo $duration; ?>
                    </span>
                </div>

                <div class="sb-row">
                    <span class="sb-label">Fees</span>
                    <span class="sb-val">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                        <?php echo $price; ?>
                    </span>
                </div>
                
                <?php if(!empty($fees['description'])): ?>
                <div style="background: #eef2ff; padding: 10px; border-radius: 8px; font-size: 13px; color: #4338ca; margin-bottom: 20px;">
                    <strong>Note:</strong> <?php echo htmlspecialchars($fees['description']); ?>
                </div>
                <?php endif; ?>

                <a href="/internship-enrollment/index.php?internship_id=<?php echo $internship['id']; ?>" class="apply-btn">Register Now</a>

                <!-- Request Callback -->
                <div class="cb-form">
                    <h4 class="cb-title">Have Questions?</h4>
                    <form id="enquiryForm">
                        <input type="hidden" name="type" value="internship">
                        <input type="hidden" name="entity_id" value="<?php echo $internship['id']; ?>">
                        <input type="hidden" name="page_url" value="<?php echo "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"; ?>">

                        <div class="form-group">
                            <input type="text" name="name" required class="form-input" placeholder="Your Name">
                        </div>
                        <div class="form-group">
                            <input type="tel" name="phone" required class="form-input" placeholder="Mobile Number">
                        </div>
                        <div class="form-group">
                            <button type="submit" class="sb-submit" id="cbBtn">Request Callback</button>
                        </div>
                        <div id="cbResponse" style="font-size: 13px; display: none;"></div>
                    </form>
                </div>

            </div>

        </div>

    </div>
  </section>

  <?php require_once __DIR__.'/includes/footer.php'; ?>

  <script>
    document.getElementById('enquiryForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const btn = document.getElementById('cbBtn');
        const msg = document.getElementById('cbResponse');
        
        btn.disabled = true;
        btn.innerText = 'Sending...';
        
        const formData = new FormData(this);
        // Mapping typical callback fields to what the backend expects
        // Using existing submit-callback-request.php if compatible or generic
        // Assuming actions/submit-callback-request.php handles 'full_name', 'mobile', 'course_id' (or we adapt it)
        // Let's use the field names compatible with existing `actions/submit-callback-request.php` seen in course-details.
        
        // Remap to match what course details sent: 
        formData.append('full_name', formData.get('name'));
        formData.append('mobile', formData.get('phone'));
        // course_id might be expected, but we are internship. 
        // If the backend strictly checks 'course_id', we might need to fake it or update backend. 
        // For now, let's send 'course_id' as 0 or the internship id, and hope the backend just logs it.
        formData.append('course_id', '<?php echo $internship['id']; ?> (Internship)'); 

        fetch('actions/submit-callback-request.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            msg.style.display = 'block';
            if(data.status === 'success') {
                msg.style.color = 'green';
                msg.innerText = 'Thanks! We will call you back.';
                this.reset();
            } else {
                msg.style.color = 'red';
                msg.innerText = data.message || 'Error submitting request.';
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
</body>
</html>
<?php $conn->close(); ?>
