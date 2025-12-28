<style>
    :root {
        --student-primary: #a855f7; /* Purple 500 */
        --student-bg: #f8fafc;
        --student-text: #1e293b;
        --student-text-light: #64748b;
        --sidebar-w: 260px;
    }

    .s-sidebar {
        width: var(--sidebar-w);
        height: 100vh;
        background: #ffffff;
        position: fixed;
        left: 0;
        top: 0;
        border-right: 1px solid #f1f5f9;
        display: flex;
        flex-direction: column;
        /* padding: 24px; Removed global padding for edge scrollbar */
        box-sizing: border-box;
        font-family: 'Outfit', sans-serif;
        z-index: 50;
    }

    /* Brand */
    .s-brand {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0;
        margin-bottom: 20px;
        padding: 24px 10px 10px 10px;
        text-decoration: none;
        flex-shrink: 0;
    }
    .s-brand img {
        max-width: 180px;
        height: auto;
        max-height: 90px;
        border-radius: 8px;
    }

    /* Scrollable Content */
    .s-sidebar-content {
        flex: 1;
        overflow-y: auto;
        padding: 10px 24px 24px 24px;
        display: flex;
        flex-direction: column;
        min-height: 0; /* Important for firefox/flex */
    }
    .s-sidebar-content::-webkit-scrollbar { width: 4px; }
    .s-sidebar-content::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 4px; }

    /* Nav */
    .s-nav-section {
        margin-bottom: 24px;
    }
    .s-nav-label {
        font-size: 11px;
        text-transform: uppercase;
        color: #94a3b8;
        font-weight: 700;
        margin-bottom: 12px;
        padding-left: 12px;
        letter-spacing: 0.5px;
    }
    
    .s-nav-list {
        list-style: none;
        padding: 0;
        margin: 0;
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .s-nav-link {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px;
        color: var(--student-text-light);
        text-decoration: none;
        font-size: 14px;
        font-weight: 500;
        border-radius: 12px;
        transition: all 0.2s;
    }
    
    .s-nav-link:hover {
        background: #f1f5f9;
        color: var(--student-text);
    }
    
    .s-nav-link.active {
        background: #f3e8ff; /* Light purple */
        color: var(--student-primary);
        font-weight: 600;
    }
    .s-nav-link.active svg {
        stroke: var(--student-primary);
        fill: rgba(168, 85, 247, 0.1); /* Subtle fill */
    }

    .s-icon {
        width: 20px;
        height: 20px;
        stroke: currentColor;
        stroke-width: 2;
        fill: none;
    }

    /* Friends Section */
    .friends-list {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }
    .friend-item {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .friend-avatar {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        object-fit: cover;
        background: #e2e8f0;
    }
    .friend-info { flex: 1; }
    .friend-name {
        font-size: 13px;
        font-weight: 600;
        color: var(--student-text);
        display: block;
    }
    .friend-points {
        font-size: 11px;
        color: var(--student-text-light);
    }
</style>

<aside class="s-sidebar">
    <a href="index.php" class="s-brand">
        <img src="../../assets/images/sidebar-logo.jpg" alt="Student Panel">
    </a>
    
    <div class="s-sidebar-content">
        <div class="s-nav-section">
            <div class="s-nav-label">Overview</div>
            <ul class="s-nav-list">
                <li>
                    <a href="index.php" class="s-nav-link active">
                        <svg class="s-icon" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                        Dashboard
                    </a>
                </li>
                <li>
                    <a href="#" class="s-nav-link">
                        <svg class="s-icon" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle><polygon points="10 8 16 12 10 16 10 8"></polygon></svg>
                        My Class
                    </a>
                </li>
                <li>
                    <a href="#" class="s-nav-link">
                        <svg class="s-icon" viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>
                        My Courses
                    </a>
                </li>
                
                <?php
                // Fetch student origin to determine fee page
                // Assuming $conn is available from index.php or we need to connect if not. 
                // Sidebar is usually included, so check if conn exists.
                // Safest to just do a quick check if session id is set.
                $fee_link = "fees.php"; // Default
                if(isset($_SESSION['student_id'])) {
                    // We need a DB connection here if not already open? 
                    // Usually sidebar is included in pages that ALREADY have db config.
                    // But to be safe, let's use the valid variable if it exists or generic check.
                    // Ideally, the parent page sets a variable. 
                    // Let's assume the parent page has $conn. If not, this might fail.
                    // Better approach: User $_SESSION data if we stored 'added_by' or 'center_id' there.
                    // We didn't store it in login. Let's rely on DB query if $conn exists.
                    if(isset($conn)) {
                        $sid = $_SESSION['student_id'];
                        $chk = $conn->query("SELECT center_id FROM admissions WHERE id = $sid");
                        if($chk && $chk->num_rows > 0) {
                            $s_data = $chk->fetch_assoc();
                            if(!empty($s_data['center_id'])) {
                                $fee_link = "manage-fees.php";
                            }
                        }
                    }
                }
                ?>
                
                <li>
                    <a href="<?php echo $fee_link; ?>" class="s-nav-link">
                         <svg class="s-icon" viewBox="0 0 24 24"><path d="M12 1v22"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                        <?php echo ($fee_link == 'manage-fees.php') ? 'Manage Fees' : 'My Fees'; ?>
                    </a>
                </li>
                <li>
                    <a href="#" class="s-nav-link">
                        <svg class="s-icon" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                        Events
                    </a>
                </li>
                <li>
                    <a href="#" class="s-nav-link">
                        <svg class="s-icon" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle><polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"></polygon></svg>
                        Explore
                    </a>
                </li>
                <li>
                    <a href="idcard.php" class="s-nav-link">
                        <svg class="s-icon" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><path d="M7 7h10v10H7z"/><path d="M10 2v2"/><path d="M14 2v2"/><path d="M10 20v2"/><path d="M14 20v2"/></svg>
                        ID Card
                    </a>
                </li>
            </ul>
        </div>

        <div class="s-nav-section">
            <div class="s-nav-label">Settings</div>
            <ul class="s-nav-list">
                 <li>
                    <a href="#" class="s-nav-link">
                        <svg class="s-icon" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                        Reports
                    </a>
                </li>
                 <li>
                    <a href="#" class="s-nav-link">
                        <svg class="s-icon" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                        Instructors
                    </a>
                </li>
                 <li>
                    <a href="#" class="s-nav-link">
                        <svg class="s-icon" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                        Settings
                    </a>
                </li>
            </ul>
        </div>

        <div style="margin-top:auto;">
            <div class="s-nav-label">Friends</div>
            <div class="friends-list">
                <div class="friend-item">
                    <img src="https://i.pravatar.cc/100?img=5" class="friend-avatar" alt="Ava">
                    <div class="friend-info">
                        <span class="friend-name">Ava Lee</span>
                        <span class="friend-points">82 Points</span>
                    </div>
                </div>
                <div class="friend-item">
                    <img src="https://i.pravatar.cc/100?img=11" class="friend-avatar" alt="Noah">
                    <div class="friend-info">
                        <span class="friend-name">Noah Kim</span>
                        <span class="friend-points">128 Points</span>
                    </div>
                </div>
                <div class="friend-item">
                    <img src="https://i.pravatar.cc/100?img=12" class="friend-avatar" alt="Ethan">
                    <div class="friend-info">
                        <span class="friend-name">Ethan Cruz</span>
                        <span class="friend-points">56 Points</span>
                    </div>
                </div>
                 <div class="friend-item">
                    <img src="https://i.pravatar.cc/100?img=9" class="friend-avatar" alt="Maya">
                    <div class="friend-info">
                        <span class="friend-name">Maya Patel</span>
                        <span class="friend-points">150 Points</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</aside>
