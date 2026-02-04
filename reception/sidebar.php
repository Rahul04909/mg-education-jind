<style>
    :root {
        --reception-primary: #ec4899; /* Pink 500 */
        --reception-bg: #fdf2f8; /* Pink 50 */
        --reception-text: #831843; /* Pink 900 */
        --reception-text-light: #be185d; /* Pink 700 */
        --sidebar-w: 260px;
    }

    .r-sidebar {
        width: var(--sidebar-w);
        height: 100vh;
        background: #ffffff;
        position: fixed;
        left: 0;
        top: 0;
        border-right: 1px solid #fbcfe8;
        display: flex;
        flex-direction: column;
        box-sizing: border-box;
        font-family: 'Outfit', sans-serif;
        z-index: 50;
    }

    /* Brand */
    .r-brand {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0;
        margin-bottom: 20px;
        padding: 24px 10px 10px 10px;
        text-decoration: none;
        flex-shrink: 0;
    }
    .r-brand img {
        max-width: 180px;
        height: auto;
        max-height: 90px;
        border-radius: 8px;
    }

    /* Scrollable Content */
    .r-sidebar-content {
        flex: 1;
        overflow-y: auto;
        padding: 10px 24px 24px 24px;
        display: flex;
        flex-direction: column;
        min-height: 0; 
    }
    .r-sidebar-content::-webkit-scrollbar { width: 4px; }
    .r-sidebar-content::-webkit-scrollbar-thumb { background: #f9a8d4; border-radius: 4px; }

    /* Nav */
    .r-nav-section {
        margin-bottom: 24px;
    }
    .r-nav-label {
        font-size: 11px;
        text-transform: uppercase;
        color: #db2777; /* Pink 600 */
        font-weight: 700;
        margin-bottom: 12px;
        padding-left: 12px;
        letter-spacing: 0.5px;
    }
    
    .r-nav-list {
        list-style: none;
        padding: 0;
        margin: 0;
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .r-nav-link {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px;
        color: var(--reception-text-light);
        text-decoration: none;
        font-size: 14px;
        font-weight: 500;
        border-radius: 12px;
        transition: all 0.2s;
    }
    
    .r-nav-link:hover {
        background: #fce7f3; /* Pink 100 */
        color: var(--reception-text);
    }
    
    .r-nav-link.active {
        background: #fbcfe8; /* Pink 200 */
        color: var(--reception-text);
        font-weight: 600;
    }
    .r-nav-link.active svg {
        stroke: var(--reception-primary);
        fill: rgba(236, 72, 153, 0.1); 
    }

    .r-icon {
        width: 20px;
        height: 20px;
        stroke: currentColor;
        stroke-width: 2;
        fill: none;
    }
</style>

<aside class="r-sidebar">
    <a href="index.php" class="r-brand">
        <!-- Assuming same logo for now, or user can change later -->
        <img src="../assets/images/sidebar-logo.jpg" alt="Reception Panel">
    </a>
    
    <div class="r-sidebar-content">
        <div class="r-nav-section">
            <div class="r-nav-label">Main</div>
            <ul class="r-nav-list">
                <li>
                    <a href="index.php" class="r-nav-link active">
                        <svg class="r-icon" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                        Dashboard
                    </a>
                </li>
                <li>
                    <a href="../../reception/student-search.php" class="r-nav-link">
                        <svg class="r-icon" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                        Student Search
                    </a>
                </li>
            </ul>
        </div>
        
        <div class="r-nav-section">
            <div class="r-nav-label">Front Desk</div>
            <ul class="r-nav-list">
                <li>
                    <a href="../../reception/quick-enquiries.php" class="r-nav-link">
                        <svg class="r-icon" viewBox="0 0 24 24"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>
                        Quick Enquiries
                    </a>
                </li>
                <li>
                    <a href="visitors.php" class="r-nav-link">
                        <svg class="r-icon" viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><line x1="20" y1="8" x2="20" y2="14"></line><line x1="23" y1="11" x2="17" y2="11"></line></svg>
                        Visitors
                    </a>
                </li>
            </ul>
        </div>

        <div class="r-nav-section">
            <div class="r-nav-label">Administration</div>
            <ul class="r-nav-list">
                 <li>
                    <a href="#" class="r-nav-link">
                        <svg class="r-icon" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                        Reports
                    </a>
                </li>
                 <li>
                    <a href="#" class="r-nav-link">
                        <svg class="r-icon" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"></circle><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path></svg>
                        Settings
                    </a>
                </li>
            </ul>
        </div>
    </div>
</aside>
