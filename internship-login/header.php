<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Ensure we have access to the current page name for active state
$current_page = basename($_SERVER['PHP_SELF']);
?>
<style>
    /* Header Specific Styles */
    .main-header {
        background: white;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        position: sticky;
        top: 0;
        z-index: 100;
        padding: 0 30px;
    }
    
    .header-top {
        display: flex;
        justify-content: space-between;
        align-items: center;
        height: 70px;
        border-bottom: 1px solid #f1f5f9;
    }

    .header-logo {
        display: flex;
        align-items: center;
        gap: 12px;
        text-decoration: none;
    }
    .header-logo img { height: 40px; border-radius: 6px; }
    .header-logo span {
        font-size: 18px;
        font-weight: 700;
        color: #4f46e5;
        letter-spacing: -0.5px;
    }

    .header-nav {
        display: flex;
        gap: 30px;
        overflow-x: auto;
        padding: 0 5px; /* Scroll padding */
    }
    
    .nav-link {
        display: flex;
        align-items: center;
        gap: 8px;
        height: 50px;
        font-size: 14px;
        font-weight: 500;
        color: #64748b;
        text-decoration: none;
        position: relative;
        white-space: nowrap;
        transition: 0.2s;
    }
    .nav-link:hover { color: #4f46e5; }
    .nav-link.active { color: #4f46e5; font-weight: 600; }
    .nav-link.active::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        height: 2px;
        background: #4f46e5;
        border-radius: 2px 2px 0 0;
    }

    .badge-soon {
        background: #fef3c7;
        color: #d97706;
        font-size: 10px;
        font-weight: 700;
        padding: 2px 6px;
        border-radius: 4px;
        text-transform: uppercase;
        margin-left: 4px;
    }

    .header-user {
        display: flex;
        align-items: center;
        gap: 15px;
    }
    .user-details { text-align: right; }
    .user-details h4 { font-size: 14px; margin: 0; font-weight: 600; color: #1e293b; }
    .user-details span { font-size: 11px; color: #64748b; display: block; }
    
    .user-avatar {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #e0e7ff;
    }

    .logout-btn {
        padding: 8px;
        border-radius: 8px;
        color: #ef4444;
        background: #fef2f2;
        border: 1px solid #fee2e2;
        display: flex;
        transition: 0.2s;
    }
    .logout-btn:hover { background: #fee2e2; }

    @media (max-width: 900px) {
        .main-header { padding: 0 15px; }
        .header-logo span { display: none; }
        .user-details { display: none; }
        .header-nav { gap: 20px; }
        .nav-text { display: none; } /* Show icons only on very small? No, keep text */
        .nav-link { font-size: 13px; }
    }
</style>

<header class="main-header">
    <div class="header-top">
        <!-- Logo -->
        <a href="index.php" class="header-logo">
            <img src="../assets/images/sidebar-logo.jpg" alt="Logo">
            <span>Internship Portal</span>
        </a>

        <!-- User Menu -->
        <div class="header-user">
            <div class="user-details">
                <h4><?php echo isset($student['full_name']) ? htmlspecialchars($student['full_name']) : 'Student'; ?></h4>
                <span><?php echo isset($student['enrollment_no']) ? htmlspecialchars($student['enrollment_no']) : ''; ?></span>
            </div>
            
            <?php 
                $h_photo = "../assets/images/avatar-placeholder.png";
                if(isset($student['student_photo']) && !empty($student['student_photo']) && file_exists(__DIR__ . "/../" . $student['student_photo'])) {
                    $h_photo = "../" . $student['student_photo'];
                }
            ?>
            <img src="<?php echo $h_photo; ?>" class="user-avatar" alt="Profile">
            
            <a href="logout.php" class="logout-btn" title="Logout">
                <i data-lucide="log-out" style="width:18px; height:18px;"></i>
            </a>
        </div>
    </div>

    <!-- Navigation Menu -->
    <div class="header-nav">
        <a href="index.php" class="nav-link <?php echo ($current_page == 'index.php') ? 'active' : ''; ?>">
            <i data-lucide="layout-dashboard" style="width:16px;"></i>
            Dashboard
        </a>
        
        <a href="assignments.php" class="nav-link <?php echo ($current_page == 'assignments.php') ? 'active' : ''; ?>">
            <i data-lucide="file-text" style="width:16px;"></i>
            Assignments
        </a>
        
        <a href="#" class="nav-link">
            <i data-lucide="book-open" style="width:16px;"></i>
            Study Material
            <span class="badge-soon">Coming Soon</span>
        </a>
        
        <a href="#" class="nav-link">
            <i data-lucide="video" style="width:16px;"></i>
            Live Classes
            <span class="badge-soon">Coming Soon</span>
        </a>

        <a href="#" class="nav-link">
            <i data-lucide="award" style="width:16px;"></i>
            Download Certificate
        </a>

        <a href="#" class="nav-link">
            <i data-lucide="bar-chart-2" style="width:16px;"></i>
            Results
        </a>
    </div>
</header>
<script>
    // re-init lucide if loaded
    if(typeof lucide !== 'undefined') {
        lucide.createIcons();
    }
</script>
