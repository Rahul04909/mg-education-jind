<style>
    @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap');
    
    :root {
        --admin-primary: #4f46e5; /* Indigo 600 */
        --admin-primary-dark: #3730a3; /* Indigo 800 */
        --admin-primary-soft: #eef2ff; /* Indigo 50 */
        --admin-hover-bg: #f5f3ff;
        --text-main: #1e293b;
        --text-light: #64748b;
        --sidebar-w: 260px;
        --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        --shadow-glow: 0 10px 15px -3px rgba(79, 70, 229, 0.2);
    }

    .sidebar {
        width: var(--sidebar-w);
        height: 100vh;
        background: #ffffff;
        position: fixed;
        left: 0;
        top: 0;
        border-right: 1px solid #e2e8f0;
        display: flex;
        flex-direction: column;
        box-sizing: border-box;
        font-family: 'Outfit', sans-serif;
        z-index: 1000;
        transition: var(--transition);
        white-space: nowrap;
    }

    .sidebar.collapsed { width: 80px; }

    /* Brand */
    .brand {
        display: flex;
        align-items: center;
        padding: 10px 24px;
        flex-shrink: 0;
        min-height: 80px;
    }

    .brand img {
        max-width: 150px;
        height: auto;
        max-height: 70px;
        border-radius: 8px;
        transition: all 0.2s;
    }
    
    .sidebar.collapsed .brand img {
        max-width: 35px;
        max-height: 35px;
    }

    /* Hide text elements in collapsed state if any remain (though we are replacing HTML) */
    .sidebar.collapsed .brand-text { display: none; }

    /* Scrollable Content */
    .sidebar-content {
        flex: 1;
        overflow-y: auto;
        padding: 10px 24px 24px 24px;
        display: flex;
        flex-direction: column;
        min-height: 0;
    }
    .sidebar-content::-webkit-scrollbar { width: 4px; }
    .sidebar-content::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 4px; }
    .sidebar.collapsed .sidebar-content { overflow: visible; padding: 10px 12px; }

    /* Nav */
    .nav-label {
        font-size: 11px;
        text-transform: uppercase;
        color: #94a3b8;
        font-weight: 700;
        margin: 16px 0 8px 12px;
        letter-spacing: 0.5px;
    }
    .sidebar.collapsed .nav-label { display: none; }

    .nav-list { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 4px; }

    .nav-item { position: relative; }
    
    .nav-link {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px;
        color: var(--text-light);
        text-decoration: none;
        font-size: 14px;
        font-weight: 500;
        border-radius: 12px;
        transition: all 0.2s;
        cursor: pointer;
    }
    
    .nav-link:hover {
        background: var(--admin-hover-bg);
        color: var(--admin-primary);
    }
    
    .nav-item.active > .nav-link {
        background: var(--admin-primary-soft);
        color: var(--admin-primary);
        font-weight: 600;
    }
    .nav-item.active > .nav-link .nav-icon { stroke: var(--admin-primary); }

    .nav-icon {
        width: 20px; height: 20px; min-width: 20px;
        stroke: currentColor; stroke-width: 2; fill: none;
    }
    .nav-text { white-space: nowrap; flex: 1; transition: opacity 0.2s; }
    .sidebar.collapsed .nav-text { display: none; }
    
    /* Submenu */
    .nav-chevron {
        width: 16px; height: 16px;
        stroke: currentColor; stroke-width: 2; fill: none;
        transition: transform 0.3s;
        opacity: 0.5;
    }
    .sidebar.collapsed .nav-chevron { display: none; }
    
    .submenu {
        list-style: none; margin: 0; padding: 0;
        max-height: 0; overflow: hidden;
        transition: max-height 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
    }
    .submenu::before {
        content: ''; position: absolute; left: 22px; top: 0; bottom: 0;
        width: 1px; background: #e2e8f0;
    }
    .sidebar.collapsed .submenu { display: none !important; }

    .submenu-item { padding-left: 22px; }
    .submenu-item a {
        display: flex; align-items: center;
        padding: 8px 12px 8px 18px;
        color: var(--text-light); text-decoration: none;
        font-size: 13px; border-radius: 8px;
        position: relative; transition: all 0.2s;
    }
    .submenu-item a::before {
        content: ''; position: absolute; left: 0; top: 50%;
        width: 5px; height: 5px; background: #cbd5e1;
        border-radius: 50%; transform: translate(-50%, -50%);
        border: 2px solid #fff; z-index: 2; transition: all 0.2s;
    }
    .submenu-item a:hover { color: var(--admin-primary); background: var(--admin-hover-bg); }
    .submenu-item a:hover::before { background: var(--admin-primary); width: 7px; height: 7px; }

    /* Open State */
    .nav-item.open .submenu { max-height: 300px; margin-top: 4px; margin-bottom: 8px; }
    .nav-item.open .nav-chevron { transform: rotate(180deg); opacity: 1; }
    
    /* Sidebar Footer */
    .sidebar-footer {
        margin-top: auto;
        padding-top: 20px;
        border-top: 1px solid #f1f5f9;
    }
</style>

<aside class="sidebar" id="sidebar">
    <div class="brand">
        <img src="../../assets/images/sidebar-logo.jpg" alt="MG Admin">
        <!-- Toggle Button for desktop -->
        <div style="margin-left:auto; cursor:pointer; color:#94a3b8;" onclick="toggleSidebar()">
             <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg>
        </div>
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
             <a href="../../admin/logout.php" class="nav-link" style="color:#ef4444; background: #fef2f2;">
                 <svg class="nav-icon" viewBox="0 0 24 24" style="stroke:#ef4444"><path d="M9 3h6v4"/><path d="M9 21h6v-4"/><path d="M16 12H3"/><path d="M12 8l4 4-4 4"/></svg>
                 <span class="nav-text">Logout</span>
             </a>
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
