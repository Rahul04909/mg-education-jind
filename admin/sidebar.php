<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap');
    
    :root {
        /* WordPress Admin Sidebar Colors */
        --sidebar-bg: #1d2327;
        --sidebar-text: #f0f0f1;
        --sidebar-text-hover: #72aee6; /* WP light blue text on hover sometimes */
        --sidebar-hover-bg: #135e96; /* WP hover background */
        --sidebar-active-bg: #2271b1; /* WP active background */
        --sidebar-submenu-bg: #2c3338;
        --sidebar-icon: #f0f0f1; /* White icons */
        
        --sidebar-w: 260px;
        --transition: none; /* WP is snappy, rarely transitions */
    }

    .sidebar {
        width: var(--sidebar-w);
        height: 100vh;
        background: var(--sidebar-bg);
        position: fixed;
        left: 0;
        top: 0;
        /* No border right in WP usually, handled by main content bg, but adding one for safety */
        /* border-right: 1px solid #c3c4c7;  */
        display: flex;
        flex-direction: column;
        box-sizing: border-box;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
        z-index: 1000;
        transition: width 0.3s; /* Only transition width if collapsing */
        white-space: nowrap;
        color: var(--sidebar-text);
        font-size: 13px;
        font-weight: 400;
    }

    .sidebar.collapsed { width: 36px; } /* WP collapsed sidebar is tiny */

    /* Brand */
    .brand {
        display: flex;
        align-items: center;
        padding: 0 20px;
        flex-shrink: 0;
        height: 50px; /* WP Admin Bar height approx */
        background: #000000; /* Darker header part */
        color: #fff;
        font-weight: 600;
    }

    .brand img {
        max-width: 120px;
        height: auto;
        max-height: 30px;
        border-radius: 0;
        display: none; /* Often WP relies on simple text or icon */
    }
    
    .brand-text-logo {
        font-size: 16px;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .sidebar.collapsed .brand { padding: 0 8px; justify-content: center; }
    .sidebar.collapsed .brand-text-logo { display: none; }
    .sidebar.collapsed .brand-icon-only { display: block; }

    /* Scrollable Content */
    .sidebar-content {
        flex: 1;
        overflow-y: auto;
        overflow-x: hidden;
        padding: 0;
        display: flex;
        flex-direction: column;
        min-height: 0;
    }
    .sidebar-content::-webkit-scrollbar { width: 6px; }
    .sidebar-content::-webkit-scrollbar-track { background: var(--sidebar-bg); }
    .sidebar-content::-webkit-scrollbar-thumb { background: #555; border-radius: 3px; }
    
    .sidebar.collapsed .sidebar-content { overflow: visible; }

    /* Nav */
    .nav-list { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 0; }

    /* Separators if needed, WP uses distinct grouping sometimes */
    .nav-label { display: none; } /* WP generally doesn't use section labels in the sidebar text, just dividers */
    
    .nav-item { position: relative; margin: 0; }
    
    .nav-link {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 12px;
        color: var(--sidebar-text);
        text-decoration: none;
        font-size: 14px;
        font-weight: 400;
        border-radius: 0; /* Boxy */
        transition: background 0.1s, color 0.1s;
        cursor: pointer;
        position: relative;
    }
    
    /* Active State */
    .nav-item.active > .nav-link,
    .nav-item.open > .nav-link {
        background: var(--sidebar-active-bg);
        color: #fff;
        font-weight: 600;
    }
    
    /* Hover State */
    .nav-link:hover {
        background: var(--sidebar-bg); /* Usually stays dark, text turns blue */
        color: var(--sidebar-text-hover);
    }
    .nav-item.active > .nav-link:hover {
        background: var(--sidebar-active-bg);
        color: #fff;
    }
    
    /* WP Hover logic: if hovering over item it gets a darker bg or the blue text */
    .nav-item:not(.active):hover > .nav-link {
        background: #191e23;
        color: var(--sidebar-text-hover);
    }

    /* Active Indicator (Triangle) - WP classic style */
    .nav-item.active > .nav-link::after {
        content: ""; /* right pointing triangle if selected? WP actually keeps it simple blue block mostly now */
        /* display: block;
        position: absolute;
        right: 0;
        top: 50%;
        transform: translateY(-50%);
        border-top: 6px solid transparent;
        border-bottom: 6px solid transparent;
        border-right: 6px solid #f0f0f1; */ /* Pointing to content */
    }
    
    .nav-icon {
        width: 20px; height: 20px; min-width: 20px;
        fill: currentColor; /* Dashicons fill style */
        stroke: none;
        opacity: 0.8;
    }
    .nav-item.active .nav-icon { opacity: 1; fill: #fff; }
    
    .nav-text { white-space: nowrap; flex: 1; transition: opacity 0.2s; }
    .sidebar.collapsed .nav-text { display: none; }
    
    /* Submenu */
    .nav-chevron {
        width: 16px; height: 16px;
        fill: currentColor;
        stroke: none;
        opacity: 0.6;
        transition: transform 0.3s;
    }
    .sidebar.collapsed .nav-chevron { display: none; }
    
    .submenu {
        list-style: none; margin: 0; padding: 0;
        max-height: 0; overflow: hidden;
        transition: max-height 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        background: var(--sidebar-submenu-bg);
    }
    /* WP submenus have no line */
    .submenu::before { display: none; }
    
    .sidebar.collapsed .submenu { 
        display: none !important; /* In WP collapsed mode, submenus are popouts, complex JS needed. Hiding for now to satisfy simple constraints */
    }

    .submenu-item { padding: 0; }
    .submenu-item a {
        display: flex; align-items: center;
        padding: 6px 12px 6px 40px; /* Indented text */
        color: rgba(240, 246, 252, 0.7); 
        text-decoration: none;
        font-size: 13px;
        border-radius: 0;
        transition: color 0.1s;
    }
    .submenu-item a::before { display: none; }
    .submenu-item a:hover { color: var(--sidebar-text-hover); background: transparent; }
    .submenu-item a:focus { color: var(--sidebar-text-hover); }

    /* Open State */
    .nav-item.open .submenu { max-height: 1000px; /* Allow expansion */ }
    .nav-item.open .nav-chevron { transform: rotate(180deg); opacity: 1; }
    
    /* Sidebar Footer */
    .sidebar-footer {
        margin-top: auto;
        padding: 0;
    }
    
    /* Separator line */
    .nav-item + .nav-item { margin-top: 0; }
    
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
