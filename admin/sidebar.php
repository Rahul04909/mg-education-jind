<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap');
    
    :root {
        /* WordPress Admin Color Scheme */
        --wp-sidebar-bg: #1d2327;
        --wp-sidebar-text: #f0f0f1;
        --wp-sidebar-hover: #2c3338; /* Slightly lighter than bg */
        --wp-sidebar-active: #2271b1; /* WP Blue */
        --wp-sidebar-submenu-bg: #2c3338; 
        --wp-icon-color: #f0f0f1; /* White icons usually, or light gray */
        --wp-icon-hover: #72aee6; /* Light blue on hover sometimes, or white */
        
        --sidebar-w: 160px; /* WP Default is 160px */
        --transition: 0.1s ease-in-out; /* WP is snappy */
    }

    .sidebar {
        width: var(--sidebar-w);
        height: 100vh;
        background: var(--wp-sidebar-bg);
        position: fixed;
        left: 0;
        top: 0;
        display: flex;
        flex-direction: column;
        box-sizing: border-box;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
        z-index: 9999;
        font-size: 13px;
        line-height: 1.4em;
    }

    /* Collapse behavior handled via body class in WP usually, but keeping local toggle for now */
    .sidebar.collapsed { width: 36px; }

    /* Brand / Top Area */
    .brand {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 50px; /* Admin bar height usually 32px or 46px */
        background: #000000; /* Darker header for sidebar */
        color: #fff;
        font-weight: 600;
        padding: 0;
        flex-shrink: 0;
    }
    .brand img {
        max-width: 30px;
        height: auto;
        border-radius: 50%;
    }
    .brand-text { margin-left: 8px; font-size: 14px; }
    .sidebar.collapsed .brand-text { display: none; }
    .sidebar.collapsed .brand { padding: 0; }

    /* Content */
    .sidebar-content {
        flex: 1;
        overflow-y: auto;
        overflow-x: hidden;
        padding: 0;
        display: flex;
        flex-direction: column;
    }
    .sidebar-content::-webkit-scrollbar { width: 6px; }
    .sidebar-content::-webkit-scrollbar-thumb { background: #444; border-radius: 3px; }
    .sidebar-content::-webkit-scrollbar-track { background: #1d2327; }

    /* Nav List */
    .nav-list { list-style: none; padding: 0; margin: 10px 0 0 0; display: flex; flex-direction: column; }

    /* WP Separator logic (optional) - usually just spacing */
    .nav-label {
        display: none; /* WP doesn't use text labels between groups usually */
    }

    .nav-item { position: relative; margin: 0; }
    
    .nav-link {
        display: flex;
        align-items: center;
        padding: 8px 12px;
        color: var(--wp-sidebar-text);
        text-decoration: none;
        font-size: 13px;
        font-weight: 400;
        transition: none; /* WP is fast */
        position: relative;
        border-left: 3px solid transparent; /* Selection marker placeholder */
    }
    
    .nav-link:hover {
        background: var(--wp-sidebar-hover);
        color: #72aee6; /* WP Text Blue on hover */
    }
    
    .nav-link:hover .nav-icon { stroke: #72aee6; }

    .nav-item.active > .nav-link,
    .nav-item.open > .nav-link {
        background: var(--wp-sidebar-active);
        color: #fff;
        font-weight: 600;
        border-left-color: transparent; /* WP highlight is full bg */
    }
    .nav-item.active > .nav-link::after {
        /* Triangle on right for active item in WP? Usually acts as pointer to content. 
           We will optionaly add 'current' indicator if needed. */
         content: "";
         position: absolute;
         right: 0;
         top: 50%;
         transform: translateY(-50%);
         /* width: 0; height: 0; border: ... */
    }

    .nav-icon {
        width: 20px; height: 20px; min-width: 20px;
        stroke: currentColor; stroke-width: 1.5; fill: none;
        margin-right: 8px;
        opacity: 0.8;
    }
    .nav-item.active .nav-icon { opacity: 1; stroke: #fff; }
    
    .nav-text { white-space: nowrap; flex: 1; opacity: 0.9; }
    .sidebar.collapsed .nav-text { display: none; }
    
    /* Submenu - WP Style: Slides down or pops out */
    .nav-chevron {
        width: 14px; height: 14px;
        stroke: currentColor; stroke-width: 2; fill: none;
        opacity: 0.6;
        transition: transform 0.2s;
    }
    .sidebar.collapsed .nav-chevron { display: none; }
    
    .submenu {
        list-style: none; margin: 0; padding: 0;
        max-height: 0; overflow: hidden;
        background: var(--wp-sidebar-submenu-bg);
        transition: max-height 0.3s ease;
    }
    
    /* When active/open */
    .nav-item.open .submenu { max-height: 500px; }
    .nav-item.open .nav-chevron { transform: rotate(180deg); opacity: 1; }

    .submenu-item a {
        display: flex; align-items: center;
        padding: 6px 12px 6px 40px; /* Indented */
        color: rgba(240, 240, 241, 0.7);
        text-decoration: none;
        font-size: 13px;
        transition: color 0.1s;
    }
    .submenu-item a:hover {
        color: #72aee6;
    }
    .sidebar.collapsed .submenu { display: none !important; }

    /* Footer / Bottom */
    .sidebar-footer {
        border-top: 1px solid rgba(255,255,255,0.1);
        padding: 0;
    }
</style>

<aside class="sidebar" id="sidebar">
    <div class="brand">
        <!-- WP Style Icon Place holder or user logo -->
        <div style="width: 36px; height: 36px; display:flex; align-items:center; justify-content:center;">
             <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:#fff;"><circle cx="12" cy="12" r="10"/><path d="M12 2a14.5 14.5 0 0 0 0 20 14.5 14.5 0 0 0 0-20"/></svg>
        </div>
        <span class="brand-text">MG Skill</span>
    </div>

    <div class="sidebar-content">
        <ul class="nav-list">
            <li class="nav-item active">
                <a href="../../admin/index.php" class="nav-link">
                    <svg class="nav-icon" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                    <span class="nav-text">Dashboard</span>
                </a>
            </li>
        </ul>

        <div class="nav-label">Academic</div>
        <ul class="nav-list">
            <li class="nav-item">
                <a href="#" class="nav-link" onclick="toggleMenu(this)">
                    <svg class="nav-icon" viewBox="0 0 24 24"><path d="M4 4h16v4H4z"/><path d="M4 10h16v10H4z"/></svg>
                    <span class="nav-text">Courses</span>
                    <svg class="nav-chevron" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"></polyline></svg>
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
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>
                    <span class="nav-text">Internships</span>
                    <svg class="nav-chevron" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"></polyline></svg>
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
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><circle cx="8.5" cy="8.5" r="1.5"></circle><polyline points="21 15 16 10 5 21"></polyline></svg>
                    <span class="nav-text">Gallery</span>
                    <svg class="nav-chevron" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"></polyline></svg>
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
                    <svg class="nav-icon" viewBox="0 0 24 24"><circle cx="12" cy="7" r="3"/><path d="M5 21v-2a7 7 0 0 1 14 0v2"/></svg>
                    <span class="nav-text">Students</span>
                    <svg class="nav-chevron" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"></polyline></svg>
                </a>
                <ul class="submenu">
                    <li class="submenu-item"><a href="../../admin/mg-students">All Students</a></li>
                    <li class="submenu-item"><a href="../../admin/mg-students/add-student.php">Add Student</a></li>
                    <li class="submenu-item"><a href="../../admin/mg-students/manage-fees.php">Manage Fees</a></li>
                    <li class="submenu-item"><a href="../../admin/mg-students/mg-students.php">MG Students</a></li>
                </ul>
            </li>
            <!-- addon to centers menu start here -->
             <li class="nav-item">
                <a href="#" class="nav-link" onclick="toggleMenu(this)">
                    <svg class="nav-icon" viewBox="0 0 24 24"><circle cx="12" cy="7" r="3"/><path d="M5 21v-2a7 7 0 0 1 14 0v2"/></svg>
                    <span class="nav-text">Centers</span>
                    <svg class="nav-chevron" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"></polyline></svg>
                </a>
                <ul class="submenu">
                    <li class="submenu-item"><a href="../../admin/centers">All Centers</a></li>
                    <li class="submenu-item"><a href="../../admin/centers/add-center.php">Add Center</a></li>                </ul>
            </li>

            <!-- Addon to center menu ends here above it -->

             <!-- addon to Donations menu start here -->
             <li class="nav-item">
                <a href="#" class="nav-link" onclick="toggleMenu(this)">
                    <svg class="nav-icon" viewBox="0 0 24 24"><circle cx="12" cy="7" r="3"/><path d="M5 21v-2a7 7 0 0 1 14 0v2"/></svg>
                    <span class="nav-text">Donations</span>
                    <svg class="nav-chevron" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"></polyline></svg>
                </a>
                <ul class="submenu">
                    <li class="submenu-item"><a href="../../admin/donations/donation-enquiry.php">Donation Enquiry</a></li>
                    <li class="submenu-item"><a href="../../admin/donations/index.php">Donation List</a></li>
               </ul>
            </li>

            <!-- Addon to Donations menu ends here above it -->

            <!-- addon to Donations menu start here -->
             <li class="nav-item">
                <a href="#" class="nav-link" onclick="toggleMenu(this)">
                    <svg class="nav-icon" viewBox="0 0 24 24"><circle cx="12" cy="7" r="3"/><path d="M5 21v-2a7 7 0 0 1 14 0v2"/></svg>
                    <span class="nav-text">Enquiries</span>
                    <svg class="nav-chevron" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"></polyline></svg>
                </a>
                <ul class="submenu">
                    <li class="submenu-item"><a href="../../admin/enquiries/callback-requests.php">Callback Requests</a></li>
               </ul>
            </li>

            <!-- Addon to Donations menu ends here above it -->

            <!-- Addon to Blogs menu start here -->
             <li class="nav-item">
                <a href="#" class="nav-link" onclick="toggleMenu(this)">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path></svg>
                    <span class="nav-text">Blogs</span>
                    <svg class="nav-chevron" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"></polyline></svg>
                </a>
                <ul class="submenu">
                    <li class="submenu-item"><a href="../../admin/blogs/add-blog.php">Write Blog</a></li>
                    <li class="submenu-item"><a href="../../admin/blogs/add-blog-category.php">Add Category</a></li>
               </ul>
            </li>
            <!-- Addon to Blogs menu ends here -->

            <!-- Addon to Volunteers menu start here -->
             <li class="nav-item">
                <a href="#" class="nav-link" onclick="toggleMenu(this)">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                    <span class="nav-text">Volunteers</span>
                    <svg class="nav-chevron" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"></polyline></svg>
                </a>
                <ul class="submenu">
                    <li class="submenu-item"><a href="../../admin/volunteers/index.php">All Volunteers</a></li>
                    <li class="submenu-item"><a href="../../admin/volunteers/add-volunteer.php">Add Volunteer</a></li>
               </ul>
            </li>
            <!-- Addon to Volunteers menu ends here -->
             <!-- Addon to Volunteers menu start here -->
             <li class="nav-item">
                <a href="#" class="nav-link" onclick="toggleMenu(this)">
                    <svg class="nav-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                    <span class="nav-text">Landing Page</span>
                    <svg class="nav-chevron" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"></polyline></svg>
                </a>
                <ul class="submenu">
                    <li class="submenu-item"><a href="../../admin/frontend/manage-hero-slides.php">Hero Slides</a></li>
                    <li class="submenu-item"><a href="../../admin/frontend/manage-news.php">News Ticker</a></li>
                    <li class="submenu-item"><a href="../../admin/frontend/manage-universities.php">Universities</a></li>
               </ul>
            </li>
            <!-- Addon to Volunteers menu ends here -->


             <li class="nav-item">
                <a href="#" class="nav-link" onclick="toggleMenu(this)">
                   <svg class="nav-icon" viewBox="0 0 24 24"><path d="M12 2l9 9-9 9-9-9 9-9z"/></svg>
                    <span class="nav-text">Settings</span>
                    <svg class="nav-chevron" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"></polyline></svg>
                </a>
                <ul class="submenu">
                    <li class="submenu-item"><a href="../../admin/profile">Profile</a></li>
                    <li class="submenu-item"><a href="../../admin/roles">Roles & Permissions</a></li>
                    <li class="submenu-item"><a href="../../admin/settings/smtp-settings.php">SMTP Settings</a></li>
                </ul>
            </li>
        </ul>

        <div class="sidebar-footer">
             <div class="nav-item">
                 <a href="../../admin/logout.php" class="nav-link">
                     <svg class="nav-icon" viewBox="0 0 24 24"><path d="M9 3h6v4"/><path d="M9 21h6v-4"/><path d="M16 12H3"/><path d="M12 8l4 4-4 4"/></svg>
                     <span class="nav-text">Collapse Menu</span>
                 </a>
                 <div style="position: absolute; left: 0; top: 0; width: 100%; height: 100%; cursor: pointer;" onclick="toggleSidebar()"></div> 
             </div>
        </div>
    </div>
</aside>

<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        // If there's a main content wrapper in admin, we might need to toggle class there too
        // Assuming standard layout
        sidebar.classList.toggle('collapsed');
        document.body.classList.toggle('sidebar-collapsed'); // Common pattern
    }

    function toggleMenu(link) {
        const item = link.closest('.nav-item');
        // Simple toggle to allow multiple menus to be open
        item.classList.toggle('open');
    }
</script>
