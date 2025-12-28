<style>
    @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap');
    
    :root {
        --primary: #059669; /* Emerald 600 */
        --primary-dark: #064e3b; /* Emerald 900 */
        --primary-soft: #d1fae5; /* Emerald 100 */
        --hover-bg: #f0fdf4;
        --text-main: #1e293b; /* Slate 800 */
        --text-light: #64748b; /* Slate 500 */
        --surface: #ffffff;
        --sidebar-width: 280px;
        --sidebar-collapsed-width: 80px;
        --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
        --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
        --shadow-glow: 0 10px 15px -3px rgba(5, 150, 105, 0.2);
        --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* Sidebar Container */
    .sidebar {
        width: var(--sidebar-width);
        height: 100vh;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
        position: fixed;
        top: 0;
        left: 0;
        display: flex;
        flex-direction: column;
        padding: 24px 16px;
        box-sizing: border-box;
        border-right: 1px solid #e2e8f0;
        z-index: 1000;
        transition: var(--transition);
        white-space: nowrap; /* Default for nav transitions */
        box-shadow: 4px 0 24px rgba(0,0,0,0.02);
    }

    .sidebar.collapsed {
        width: var(--sidebar-collapsed-width);
        padding: 24px 12px;
    }

    /* Logo Area */
    .brand {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 30px;
        padding: 10px 8px; /* More padding */
        min-height: 60px; /* Increased height */
        /* Removed overflow: hidden to prevent text cutoff */
    }
    .brand-logo {
        min-width: 44px;
        width: 44px;
        height: 44px;
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        color: white;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 22px;
        box-shadow: var(--shadow-glow);
        flex-shrink: 0;
    }
    .brand-text {
        display: flex;
        flex-direction: column;
        justify-content: center;
        opacity: 1;
        transition: opacity 0.2s;
    }
    .brand-title {
        font-size: 20px; /* Slightly larger */
        font-weight: 800;
        color: var(--text-main);
        letter-spacing: -0.5px;
        line-height: 1.2;
        padding-top: 2px;
    }
    .brand-subtitle {
        font-size: 11px;
        color: var(--text-light);
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 600;
    }
    .sidebar.collapsed .brand-text { opacity: 0; pointer-events: none; display: none; } /* Hide completely to fix layout */

    /* Navigation */
    .nav-label {
        font-size: 11px;
        font-weight: 700;
        color: #94a3b8;
        margin: 0 0 12px 14px;
        text-transform: uppercase;
        letter-spacing: 1px;
        transition: opacity 0.2s;
    }
    .sidebar.collapsed .nav-label { display: none; }

    .nav-list {
        list-style: none;
        padding: 0;
        margin: 0;
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .nav-item {
        position: relative;
    }

    /* Main Icon Link */
    .nav-link {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 12px 14px;
        text-decoration: none;
        color: var(--text-light);
        font-weight: 500;
        border-radius: 14px;
        transition: var(--transition);
        cursor: pointer;
        position: relative;
        overflow: hidden;
    }

    /* Icons */
    .nav-icon {
        width: 22px;
        height: 22px;
        min-width: 22px;
        stroke: currentColor;
        stroke-width: 2px;
        fill: none;
        transition: var(--transition);
    }

    /* Link Text */
    .nav-text {
        flex: 1;
        font-size: 14px;
        transition: opacity 0.2s;
    }
    .sidebar.collapsed .nav-text {
        opacity: 0;
        display: none;
    }

    /* Chevron for Dropdown */
    .nav-chevron {
        width: 16px;
        height: 16px;
        min-width: 16px;
        stroke: currentColor;
        stroke-width: 2px;
        fill: none;
        transition: transform 0.3s;
        opacity: 0.5;
    }
    .sidebar.collapsed .nav-chevron { display: none; }

    /* Hover & Active States */
    .nav-link:hover {
        background: var(--hover-bg);
        color: var(--primary);
        transform: translateX(4px);
    }
    .sidebar.collapsed .nav-link:hover { transform: none; }

    .nav-item.active > .nav-link {
        background: linear-gradient(90deg, var(--primary) 0%, var(--primary-dark) 100%);
        color: white;
        box-shadow: var(--shadow-glow);
    }
    .nav-item.active > .nav-link:hover { transform: none; }
    .nav-item.active > .nav-link .nav-chevron { opacity: 0.8; }

    /* Submenu styling */
    .submenu {
        list-style: none;
        margin: 0;
        padding: 0;
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        background: transparent;
        position: relative;
    }
    
    .submenu::before {
        content: '';
        position: absolute;
        left: 24px;
        top: 0;
        bottom: 0;
        width: 1px;
        background: #e2e8f0;
    }

    .sidebar.collapsed .submenu { display: none !important; }

    .submenu-item {
        position: relative;
        padding-left: 24px; /* Align with line */
    }

    .submenu-item a {
        display: flex;
        align-items: center;
        padding: 10px 14px 10px 20px;
        color: var(--text-light);
        text-decoration: none;
        font-size: 13px;
        font-weight: 500;
        transition: all 0.2s;
        border-radius: 8px;
        position: relative;
    }
    
    /* Dot for submenu items */
    .submenu-item a::before {
        content: '';
        position: absolute;
        left: 0; /* On the line */
        top: 50%;
        width: 5px;
        height: 5px;
        background: #cbd5e1;
        border-radius: 50%;
        transform: translate(-50%, -50%);
        transition: all 0.2s;
        z-index: 2;
        border: 2px solid #fff;
    }

    .submenu-item a:hover {
        color: var(--primary);
        background: var(--hover-bg);
    }
    .submenu-item a:hover::before {
        background: var(--primary);
        width: 7px;
        height: 7px;
    }

    /* Scrollbar */
    .sidebar-content {
        flex: 1;
        overflow-y: auto;
        overflow-x: hidden;
        padding-right: 2px; /* Avoid scrollbar overlay */
        min-height: 0; /* Critical for flex scrolling */
        display: flex;
        flex-direction: column;
    }
    .sidebar-content::-webkit-scrollbar { width: 4px; }
    .sidebar-content::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
    .sidebar.collapsed .sidebar-content { overflow: visible; }
    
    /* Open State for Dropdown */
    .nav-item.open .submenu {
        max-height: 300px;
        margin-top: 4px;
        margin-bottom: 8px;
    }
    .nav-item.open .nav-chevron {
        transform: rotate(180deg);
        opacity: 1;
    }

    /* Promo Card */
    .promo-card {
        margin-top: auto; /* Pushes to bottom */
        flex-shrink: 0; /* Prevents being squashed when content grows */
        /* Using a solid gradient or a subtle pattern is safer than a complex image for text readability */
        background: linear-gradient(145deg, #1e293b, #334155); 
        border-radius: 20px;
        padding: 24px 20px; /* Restored padding */
        color: white;
        position: relative;
        overflow: hidden;
        text-align: center;
        box-shadow: 0 10px 15px -3px rgba(0,0,0,0.2);
        transition: all 0.3s;
        border: 1px solid rgba(255,255,255,0.05);
        white-space: normal; /* VITAL: Inherit break override */
        margin-bottom: 10px; /* Spacing from viewport bottom */
    }
    
    /* Decorative Circle */
    .promo-card::before {
        content: '';
        position: absolute;
        top: -20px;
        right: -20px;
        width: 80px;
        height: 80px;
        background: rgba(255,255,255,0.1);
        border-radius: 50%;
        z-index: 0;
    }

    .promo-card::after {
        content: '';
        position: absolute;
        bottom: -30px;
        left: -10px;
        width: 100px;
        height: 100px;
        background: linear-gradient(45deg, transparent, rgba(5, 150, 105, 0.2));
        border-radius: 50%;
        z-index: 0;
    }

    .sidebar.collapsed .promo-card { display: none; }
    
    .promo-content { position: relative; z-index: 1; display: flex; flex-direction: column; align-items: center; }
    
    .promo-card h4 { 
        margin: 10px 0 6px 0; 
        font-size: 16px; 
        font-weight: 700; 
        color: #fff; 
        letter-spacing: -0.3px;
    }
    
    .promo-card p { 
        margin: 0 0 16px 0; 
        font-size: 12px; 
        opacity: 0.8; 
        line-height: 1.5; 
        color: #e2e8f0; 
        font-weight: 400;
        max-width: 90%;
    }
    
    .coming-soon-badge-sm {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 10px;
        background: rgba(251, 191, 36, 0.2);
        color: #fbbf24;
        border: 1px solid rgba(251, 191, 36, 0.4);
        border-radius: 20px;
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .btn-notify {
        background: rgba(255,255,255,0.1); 
        border: 1px solid rgba(255,255,255,0.2); 
        color: white; 
        padding: 10px 0; 
        width: 100%;
        border-radius: 12px; 
        font-size: 13px; 
        font-weight: 600; 
        cursor: pointer; 
        backdrop-filter: blur(4px);
        transition: all 0.2s ease;
    }
    .btn-notify:hover {
        background: rgba(255,255,255,0.2);
        border-color: rgba(255,255,255,0.4);
        transform: translateY(-2px);
    }
</style>

<aside class="sidebar" id="sidebar">
    <div class="sidebar-toggle" onclick="toggleSidebar()">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
    </div>

    <div class="brand">
        <div class="brand-logo">MG</div>
        <div class="brand-text">
            <span class="brand-title">MG Education</span>
            <span class="brand-subtitle">Center Panel</span>
        </div>
    </div>

    <div class="sidebar-content">
        <div class="nav-label">Overview</div>
        <ul class="nav-list">
            <li class="nav-item active">
                <a href="index.php" class="nav-link">
                    <svg class="nav-icon" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                    <span class="nav-text">Dashboard</span>
                </a>
            </li>
        </ul>

        <div class="nav-label" style="margin-top: 24px;">Academic</div>
        <ul class="nav-list">
            <!-- Students Dropdown -->
            <li class="nav-item">
                <a href="#" class="nav-link" onclick="toggleMenu(this)">
                    <svg class="nav-icon" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                    <span class="nav-text">Students</span>
                    <svg class="nav-chevron" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"></polyline></svg>
                </a>
                <ul class="submenu">
                    <li class="submenu-item"><a href="../../center/students/index.php">All Students</a></li>
                    <li class="submenu-item"><a href="../../center/students/add-student.php">New Admission</a></li>
                </ul>
            </li>

            <!-- Courses -->
            <li class="nav-item">
                <a href="../../center/courses/index.php" class="nav-link">
                    <svg class="nav-icon" viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>
                    <span class="nav-text">Courses</span>
                </a>
            </li>
            <ul class="nav-list">
             <li class="nav-item">
                <a href="../../center/wallet/wallet.php" class="nav-link">
                    <svg class="nav-icon" viewBox="0 0 24 24"><path d="M20 12V8H6a2 2 0 0 1-2-2 2 2 0 0 1 2-2h12v4"></path><path d="M4 6v12a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2z"></path></svg>
                    <span class="nav-text">Wallet</span>
                </a>
            </li>
             <li class="nav-item">
                <a href="../../center/profile.php" class="nav-link">
                    <svg class="nav-icon" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                    <span class="nav-text">My Profile</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="../../center/bank-details.php" class="nav-link">
                    <svg class="nav-icon" viewBox="0 0 24 24"><rect x="2" y="5" width="20" height="14" rx="2"></rect><line x1="2" y1="10" x2="22" y2="10"></line></svg>
                    <span class="nav-text">Bank Details</span>
                </a>
            </li>
        </ul>
            <!-- promo card sidebar -->

        <div class="promo-card">
            <div class="promo-content">
                <div class="coming-soon-badge-sm">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                    Coming Soon
                </div>
                <h4>Mobile App</h4>
                <p>Manage your center on the go with our upcoming app.</p>
                <button class="btn-notify">Notify Me</button>
            </div>
        </div>
    </div>
</aside>

<script>
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.querySelector('.main-content');
        sidebar.classList.toggle('collapsed');
        // Handle Main Content Expansion logic
        if(mainContent) {
           mainContent.classList.toggle('expanded');
        }
    }

    function toggleMenu(link) {
        const item = link.closest('.nav-item');
        // Close others?
        const currentOpen = document.querySelector('.nav-item.open');
        if(currentOpen && currentOpen !== item) {
            currentOpen.classList.remove('open');
        }
        item.classList.toggle('open');
    }
</script>
