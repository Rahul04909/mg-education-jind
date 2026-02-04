<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap');
    
    :root {
        /* GLOBAL DARK THEME (Slate/Charcoal) */
        --bg-body: #0f172a;       /* Deep Slate - Main Background */
        --bg-sidebar: #1e293b;    /* Lighter Slate - Sidebar */
        --bg-card: #1e293b;       /* Card Background */
        --bg-input: #334155;      /* Input Background */
        
        --text-main: #f1f5f9;     /* White-ish */
        --text-muted: #94a3b8;    /* Muted Text */
        --text-accent: #818cf8;   /* Indigo Accent */
        
        --border-color: #334155;  /* Slate Border */
        --hover-bg: #334155;      /* Hover State */
        --active-bg: #4f46e5;     /* Active Primary */
        
        --sidebar-w: 260px;
        --sidebar-collapsed-w: 64px; /* Wider for centered icons */
        --header-h: 60px;
    }

    /* GLOBAL RESET & THEME ENFORCEMENT */
    body {
        background-color: var(--bg-body) !important;
        color: var(--text-main) !important;
        font-family: 'Inter', sans-serif !important;
    }

    h1, h2, h3, h4, h5, h6 { color: var(--text-main) !important; }
    p, span, div { color: inherit; }
    
    /* Common Admin Components Theme Override */
    .card, .dashboard-card, .stat-card {
        background-color: var(--bg-card) !important;
        border: 1px solid var(--border-color) !important;
        color: var(--text-main) !important;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06) !important;
    }
    
    /* Tables */
    table, th, td { color: var(--text-main) !important; border-color: var(--border-color) !important; }
    thead th { background-color: #020617 !important; color: var(--text-muted) !important; }
    tr:nth-child(even) { background-color: rgba(255,255,255,0.02) !important; }
    
    /* Inputs */
    input, select, textarea {
        background-color: var(--bg-input) !important;
        border: 1px solid var(--border-color) !important;
        color: var(--text-main) !important;
    }

    /* SIDEBAR STYLES */
    .sidebar {
        width: var(--sidebar-w);
        height: 100vh;
        background: var(--bg-sidebar);
        position: fixed;
        left: 0;
        top: 0;
        border-right: 1px solid var(--border-color);
        display: flex;
        flex-direction: column;
        z-index: 1000;
        transition: width 0.3s ease;
        white-space: nowrap;
    }

    .sidebar.collapsed { width: var(--sidebar-collapsed-w); }

    /* Brand */
    .brand {
        height: var(--header-h);
        display: flex;
        align-items: center;
        padding: 0 20px;
        border-bottom: 1px solid var(--border-color);
        background: rgba(0,0,0,0.2);
    }
    .brand a { color: var(--text-main); text-decoration: none; display: flex; align-items: center; gap: 12px; font-weight: 600; font-size: 18px; }
    .sidebar.collapsed .brand { padding: 0; justify-content: center; }
    .sidebar.collapsed .brand-text-logo { display: none; }
    
    /* Navigation */
    .sidebar-content { flex: 1; overflow-y: auto; overflow-x: hidden; padding: 10px 0; }
    .nav-list { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 4px; }
    
    .nav-item { margin: 0 10px; }
    .sidebar.collapsed .nav-item { margin: 0 8px; } /* Tighter margin in collapsed */

    .nav-link {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 12px;
        color: var(--text-muted);
        text-decoration: none;
        font-size: 14px;
        font-weight: 500;
        border-radius: 6px;
        transition: all 0.2s;
        cursor: pointer;
    }

    /* Hover & Active */
    .nav-link:hover { background: var(--hover-bg); color: var(--text-main); }
    .nav-item.active > .nav-link, .nav-item.open > .nav-link {
        background: var(--active-bg);
        color: #fff;
        box-shadow: 0 4px 6px -1px rgba(79, 70, 229, 0.4);
    }

    .nav-icon {
        width: 20px; height: 20px; min-width: 20px;
        fill: currentColor;
        transition: transform 0.2s;
    }
    .nav-text { flex: 1; opacity: 1; transition: opacity 0.2s; }
    
    .sidebar.collapsed .nav-text, 
    .sidebar.collapsed .nav-chevron { display: none !important; }
    
    .sidebar.collapsed .nav-link { 
        justify-content: center; 
        padding: 10px 0; 
    }
    .sidebar.collapsed .nav-icon { margin: 0; }

    /* Submenu */
    .submenu {
        list-style: none; margin: 0; padding: 0;
        max-height: 0; overflow: hidden;
        transition: max-height 0.3s ease;
        background: rgba(0,0,0,0.2);
        border-radius: 6px;
        margin-top: 2px;
    }
    .nav-item.open .submenu { max-height: 800px; padding: 4px 0; }
    
    .submenu-item a {
        display: block;
        padding: 8px 12px 8px 44px;
        color: var(--text-muted);
        text-decoration: none;
        font-size: 13px;
        transition: color 0.2s;
    }
    .submenu-item a:hover { color: var(--text-main); }
    .sidebar.collapsed .submenu { display: none !important; }
    
    .nav-chevron { width: 16px; height: 16px; opacity: 0.5; transition: transform 0.3s; }
    .nav-item.open .nav-chevron { transform: rotate(180deg); }

    /* Footer */
    .sidebar-footer { padding: 10px; border-top: 1px solid var(--border-color); }

    /* Collapsed Main Content Adjustment Helper */
    /* This style assumes the main content has a class we can target or we use a general rule */
    body.sidebar-collapsed .main-content, 
    body.sidebar-collapsed .admin-content { 
        margin-left: var(--sidebar-collapsed-w) !important; 
    }
</style>

<aside class="sidebar" id="sidebar">
    <div class="brand">
        <!-- WP Style Brand/Home icon -->
        <a href="../../index.php" style="color:white; text-decoration:none; display:flex; gap:10px; align-items:center;">
             <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2L2 7l10 5 10-5-10-5zm0 9l2.5-1.25L12 8.5l-2.5 1.25L12 11zm0 2.5l-5-2.5-5 2.5L12 22l10-8.5-5-2.5-5 2.5z"/></svg>
             <span class="brand-text-logo">MG Skill</span>
        </a>
    </div>

    <div class="sidebar-content">
        <ul class="nav-list">
            <li class="nav-item active">
                <a href="../../admin/index.php" class="nav-link">
                    <!-- Dashicon: Dashboard -->
                    <svg class="nav-icon" viewBox="0 0 20 20"><path d="M10 2c4.42 0 8 3.58 8 8s-3.58 8-8 8-8-3.58-8-8 3.58-8 8-8zm0 10c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zM4 10c0-3.31 2.69-6 6-6s6 2.69 6 6-2.69 6-6 6-6-2.69-6-6z"/></svg>
                    <span class="nav-text">Dashboard</span>
                </a>
            </li>

             <!-- Separator -->
            <!-- <li style="height:10px;"></li> -->

            <li class="nav-item">
                <a href="#" class="nav-link" onclick="toggleMenu(this)">
                    <!-- Dashicon: Admin Page / Content -->
                    <svg class="nav-icon" viewBox="0 0 20 20"><path d="M5 4c-1.1 0-2 .9-2 2v10l4-4h8c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2H5zm0 8V6h10v6H9L5 12z"/></svg>
                    <span class="nav-text">Courses</span>
                    <svg class="nav-chevron" viewBox="0 0 20 20"><path d="M5 8l5 5 5-5H5z"/></svg>
                </a>
                <ul class="submenu">
                    <li class="submenu-item"><a href="../../admin/courses/">All Courses</a></li>
                    <li class="submenu-item"><a href="../../admin/courses/add-course.php">Create Course</a></li>
                    <li class="submenu-item"><a href="../../admin/courses/add-category.php">Categories</a></li>
                    <li class="submenu-item"><a href="../../admin/courses/manage-course-sessions.php">Manage Sessions</a></li>
                    <li class="submenu-item"><a href="../../admin/courses/manage-subjects.php">Manage Subjects</a></li>
                    <li class="submenu-item"><a href="../../admin/courses/manage-syllabus.php">Manage Syllabus</a></li>
                    <li class="submenu-item"><a href="../../admin/courses/manage-exam-schedule.php">Exam Schedule</a></li>
                    <li class="submenu-item"><a href="../../admin/courses/manage-question-paper.php">Question Paper</a></li>
                </ul>
            </li>

            <li class="nav-item">
                <a href="#" class="nav-link" onclick="toggleMenu(this)">
                    <!-- Dashicon: Awards/Internships -->
                    <svg class="nav-icon" viewBox="0 0 20 20"><path d="M2 3v14h16V3H2zm14 12H4V5h12v10z"/><path d="M6 7h8v2H6zm0 4h8v2H6z"/></svg>
                    <span class="nav-text">Internships</span>
                    <svg class="nav-chevron" viewBox="0 0 20 20"><path d="M5 8l5 5 5-5H5z"/></svg>
                </a>
                <ul class="submenu">
                    <li class="submenu-item"><a href="../../admin/internships/index.php">All Internships</a></li>
                    <li class="submenu-item"><a href="../../admin/internships/add-internship.php">Add Internship</a></li>
                    <li class="submenu-item"><a href="../../admin/internships/enroll-student.php">Enroll Student</a></li>
                    <li class="submenu-item"><a href="../../admin/internships/student-list.php">Student List</a></li>
                    <li class="submenu-item"><a href="../../admin/internships/manage-internship-sessions.php">Manage Sessions</a></li>
                </ul>
            </li>

            <li class="nav-item">
                <a href="#" class="nav-link" onclick="toggleMenu(this)">
                    <!-- Dashicon: Images-alt2 -->
                    <svg class="nav-icon" viewBox="0 0 20 20"><path d="M4 4h12c1.1 0 2 .9 2 2v8c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2zm0 10l4-4 2.5 2.5L13 10l3 3V6H4v8z"/></svg>
                    <span class="nav-text">Gallery</span>
                    <svg class="nav-chevron" viewBox="0 0 20 20"><path d="M5 8l5 5 5-5H5z"/></svg>
                </a>
                <ul class="submenu">
                    <li class="submenu-item"><a href="../../admin/gallery/add-gallery-category.php">Add Category</a></li>
                    <li class="submenu-item"><a href="../../admin/gallery/manage-gallery-category.php">Manage Categories</a></li>
                    <li class="submenu-item"><a href="../../admin/gallery/add-gallery-images.php">Add Image</a></li>
                    <li class="submenu-item"><a href="../../admin/gallery/manage-gallery-image.php">Manage Images</a></li>
                </ul>
            </li>

            <li class="nav-item">
                <a href="#" class="nav-link" onclick="toggleMenu(this)">
                    <!-- Dashicon: Groups -->
                    <svg class="nav-icon" viewBox="0 0 20 20"><path d="M7 6c0 1.66-1.34 3-3 3S1 7.66 1 6s1.34-3 3-3 3 1.34 3 3zm3 4c1.66 0 3-1.34 3-3S11.66 1 10 1s-3 1.34-3 3 1.34 3 3 3zm7-4c0 1.66-1.34 3-3 3s-3-1.34-3-3 1.34-3 3-3 3 1.34 3 3zM3.5 10c-1.5 0-2.8.8-3.5 2v3h7v-3c-.7-1.2-2-2-3.5-2zm6.5 0c-1.5 0-2.8.8-3.5 2v3h7v-3c-.7-1.2-2-2-3.5-2zm6.5 0c-1.5 0-2.8.8-3.5 2v3h7v-3c-.7-1.2-2-2-3.5-2z"/></svg>
                    <span class="nav-text">Students</span>
                    <svg class="nav-chevron" viewBox="0 0 20 20"><path d="M5 8l5 5 5-5H5z"/></svg>
                </a>
                <ul class="submenu">
                    <li class="submenu-item"><a href="../../admin/mg-students">All Students</a></li>
                    <li class="submenu-item"><a href="../../admin/mg-students/add-student.php">Add Student</a></li>
                    <li class="submenu-item"><a href="../../admin/mg-students/manage-fees.php">Manage Fees</a></li>
                    <li class="submenu-item"><a href="../../admin/mg-students/mg-students.php">MG Students</a></li>
                </ul>
            </li>
            
             <li class="nav-item">
                <a href="#" class="nav-link" onclick="toggleMenu(this)">
                    <!-- Dashicon: Location -->
                    <svg class="nav-icon" viewBox="0 0 20 20"><path d="M10 2C6.13 2 3 5.13 3 9c0 5.25 7 11 7 11s7-5.75 7-11c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
                    <span class="nav-text">Centers</span>
                    <svg class="nav-chevron" viewBox="0 0 20 20"><path d="M5 8l5 5 5-5H5z"/></svg>
                </a>
                <ul class="submenu">
                    <li class="submenu-item"><a href="../../admin/centers">All Centers</a></li>
                    <li class="submenu-item"><a href="../../admin/centers/add-center.php">Add Center</a></li>                
                </ul>
            </li>

             <li class="nav-item">
                <a href="#" class="nav-link" onclick="toggleMenu(this)">
                    <!-- Dashicon: Heart -->
                    <svg class="nav-icon" viewBox="0 0 20 20"><path d="M10 18l-1.5-1.4C3.4 12 0 8.8 0 5a5 5 0 018-4c1.8 0 3.5.8 4.6 2 1.1-1.2 2.8-2 4.6-2a5 5 0 015 4c0 3.8-3.4 7-8.5 11.6L10 18z"/></svg>
                    <span class="nav-text">Donations</span>
                    <svg class="nav-chevron" viewBox="0 0 20 20"><path d="M5 8l5 5 5-5H5z"/></svg>
                </a>
                <ul class="submenu">
                    <li class="submenu-item"><a href="../../admin/donations/donation-enquiry.php">Donation Enquiry</a></li>
                    <li class="submenu-item"><a href="../../admin/donations/index.php">Donation List</a></li>
               </ul>
            </li>

             <li class="nav-item">
                <a href="#" class="nav-link" onclick="toggleMenu(this)">
                    <!-- Dashicon: Phone -->
                    <svg class="nav-icon" viewBox="0 0 20 20"><path d="M17.06 12.3c-.7-.22-2.2-.68-2.54-.78-.34-.1-.58 0-.78.3-.22.28-.86 1.08-1.04 1.3-.2.24-.38.26-.7.1s-1.38-.5-2.62-1.62c-.98-.86-1.62-1.92-1.82-2.24-.18-.34 0-.5.16-.64.14-.14.32-.36.48-.54.16-.18.22-.3.32-.5.1-.2.06-.38-.02-.54-.1-.16-.86-2.08-1.18-2.84-.3-.72-.62-.62-.86-.64h-.74c-.26 0-.68.1-1.04.5C3.3 4.6 1.9 5.92 1.9 8.6c0 2.68 1.96 5.26 7.6 10.74 3.82 3.72 8.58 4.2 10.16 4.64.48.14 1.3.16 2.02-.06.84-.24 2.8-1.14 3.2-2.24.4-1.1.4-2.04.28-2.24-.1-.2-.38-.32-.8-.54z"/></svg>
                    <span class="nav-text">Enquiries</span>
                    <svg class="nav-chevron" viewBox="0 0 20 20"><path d="M5 8l5 5 5-5H5z"/></svg>
                </a>
                <ul class="submenu">
                    <li class="submenu-item"><a href="../../admin/enquiries/callback-requests.php">Callback Requests</a></li>
               </ul>
            </li>

             <li class="nav-item">
                <a href="#" class="nav-link" onclick="toggleMenu(this)">
                    <!-- Dashicon: Format-aside -->
                    <svg class="nav-icon" viewBox="0 0 20 20"><path d="M16 2H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2zm-6 13H5v-2h5v2zm5-4H9V9h6v2zm0-4H9V5h6v2z"/></svg>
                    <span class="nav-text">Blogs</span>
                    <svg class="nav-chevron" viewBox="0 0 20 20"><path d="M5 8l5 5 5-5H5z"/></svg>
                </a>
                <ul class="submenu">
                    <li class="submenu-item"><a href="../../admin/blogs/add-blog.php">Write Blog</a></li>
                    <li class="submenu-item"><a href="../../admin/blogs/add-blog-category.php">Add Category</a></li>
               </ul>
            </li>

             <li class="nav-item">
                <a href="#" class="nav-link" onclick="toggleMenu(this)">
                    <!-- Dashicon: Admin Users -->
                    <svg class="nav-icon" viewBox="0 0 20 20"><path d="M7 11c0 1.66 1.34 3 3 3s3-1.34 3-3-1.34-3-3-3-3 1.34-3 3zm3 4c1.66 0 3 1.34 3 3v2h-6v-2c0-1.66 1.34-3 3-3zm-5 0c0-1.66-1.34-3-3-3S-1 13.34-1 15v2h4v-2c0-.55.15-1.06.41-1.51C3.15 14.15 3 14.56 3 15v2h2v-2zm12-2c.26.45.41.96.41 1.51v2h4v-2c0-1.66-1.34-3-3-3s-3 1.34-3 3v2h2v-2c0-.44-.15-.85-.41-1.18z"/></svg>
                    <span class="nav-text">Volunteers</span>
                    <svg class="nav-chevron" viewBox="0 0 20 20"><path d="M5 8l5 5 5-5H5z"/></svg>
                </a>
                <ul class="submenu">
                    <li class="submenu-item"><a href="../../admin/volunteers/index.php">All Volunteers</a></li>
                    <li class="submenu-item"><a href="../../admin/volunteers/add-volunteer.php">Add Volunteer</a></li>
               </ul>
            </li>

             <li class="nav-item">
                <a href="#" class="nav-link" onclick="toggleMenu(this)">
                    <!-- Dashicon: Admin Home -->
                    <svg class="nav-icon" viewBox="0 0 20 20"><path d="M10 2l-8 7h2v9h4v-6h4v6h4V9h2L10 2z"/></svg>
                    <span class="nav-text">Landing Page</span>
                    <svg class="nav-chevron" viewBox="0 0 20 20"><path d="M5 8l5 5 5-5H5z"/></svg>
                </a>
                <ul class="submenu">
                    <li class="submenu-item"><a href="../../admin/frontend/manage-hero-slides.php">Hero Slides</a></li>
                    <li class="submenu-item"><a href="../../admin/frontend/manage-news.php">News Ticker</a></li>
                    <li class="submenu-item"><a href="../../admin/frontend/manage-universities.php">Universities</a></li>
               </ul>
            </li>

             <li class="nav-item">
                <a href="#" class="nav-link" onclick="toggleMenu(this)">
                   <!-- Dashicon: Admin Settings -->
                   <svg class="nav-icon" viewBox="0 0 20 20"><path d="M11.1 2.3l-.3 2.6c.4.1.8.3 1.1.5l2.4-1.2 1.6 2.8-2.1 1.5c.1.4.1.7.1 1s0 .6-.1 1l2.1 1.5-1.6 2.8-2.4-1.2c-.4.2-.7.4-1.1.5l.3 2.6h-3.2l.3-2.6c-.4-.1-.8-.3-1.1-.5L4.7 13.9 3.1 11.1l2.1-1.5c-.1-.3-.1-.6-.1-1s0-.7.1-1l-2.1-1.5L4.7 3.3 7 4.5c.4-.2.7-.4 1.1-.5l-.3-2.6h3.3zM8 10c0 1.1.9 2 2 2s2-.9 2-2-.9-2-2-2-2 .9-2 2z"/></svg>
                    <span class="nav-text">Settings</span>
                    <svg class="nav-chevron" viewBox="0 0 20 20"><path d="M5 8l5 5 5-5H5z"/></svg>
                </a>
                <ul class="submenu">
                    <li class="submenu-item"><a href="../../admin/profile">Profile</a></li>
                    <li class="submenu-item"><a href="../../admin/roles">Roles & Permissions</a></li>
                    <li class="submenu-item"><a href="../../admin/settings/smtp-settings.php">SMTP Settings</a></li>
                </ul>
            </li>
        </ul>

        <div class="sidebar-footer">
             <a href="../../admin/logout.php" class="nav-link" style="color:#d63638; display:flex; gap:8px; padding-left:12px;">
                 <svg class="nav-icon" viewBox="0 0 20 20" style="fill:#d63638"><path d="M16 10l-4-4v3H6v2h6v3l4-4zM2 10c0 4.42 3.58 8 8 8v-2c-3.31 0-6-2.69-6-6s2.69-6 6-6V2c-4.42 0-8 3.58-8 8z"/></svg>
                 <span class="nav-text">Logout</span>
             </a>
             <!-- Collapse button mimic -->
             <div onclick="toggleSidebar()" style="cursor:pointer; display:flex; justify-content:flex-end; padding:10px 12px; border-top:1px solid #3c434a; margin-top:10px;">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="#f0f0f1" style="opacity:0.6"><path d="M10 18a8 8 0 100-16 8 8 0 000 16zm-1-11v6l-4-3 4-3z"/></svg>
             </div>
        </div>
    </div>
</aside>

<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        sidebar.classList.toggle('collapsed');
        document.body.classList.toggle('sidebar-collapsed'); 
    }

    function toggleMenu(link) {
        const item = link.closest('.nav-item');
        item.classList.toggle('open');
    }
</script>
