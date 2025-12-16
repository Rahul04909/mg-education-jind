<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - Skillio</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --bg-main: #f8fafc;
            --white: #ffffff;
            --primary: #a855f7; /* Purple */
            --primary-light: #f3e8ff;
            --text-dark: #1e293b;
            --text-gray: #64748b;
        }
        
        body { font-family: 'Outfit', sans-serif; background-color: var(--bg-main); margin: 0; color: var(--text-dark); }
        
        .main-container {
            margin-left: 260px; /* Sidebar width */
            padding: 30px;
            min-height: 100vh;
        }

        /* Search & Header */
        .s-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        .search-bar {
            background: white;
            border-radius: 50px;
            padding: 12px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            width: 400px;
            border: 1px solid transparent;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);
            transition: all 0.2s;
        }
        .search-bar:focus-within { border-color: var(--primary); box-shadow: 0 0 0 4px var(--primary-light); }
        .search-input { border: none; outline: none; width: 100%; font-family: inherit; font-size: 14px; }
        
        .header-actions { display: flex; gap: 16px; align-items: center; }
        .action-btn { 
            width: 40px; height: 40px; 
            border-radius: 50%; 
            background: white; 
            display: flex; align-items: center; justify-content: center;
            color: var(--text-gray);
            cursor: pointer;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
            transition: all 0.2s;
        }
        .action-btn:hover { color: var(--primary); background: var(--primary-light); }
        
        .user-profile {
            width: 40px; height: 40px; border-radius: 50%; overflow: hidden;
            border: 2px solid white; box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        /* Banner */
        .premium-banner {
            background: #fdf4ff; /* Light Pink/Purple bg */
            border-radius: 24px;
            padding: 40px;
            position: relative;
            overflow: hidden;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
        }
        .banner-decoration {
            background: linear-gradient(135deg, #e879f9 0%, #a855f7 100%);
            opacity: 0.1;
            position: absolute;
            inset: 0;
            z-index: 0;
        }
        .banner-content { position: relative; z-index: 1; max-width: 50%; }
        .banner-title { font-size: 28px; font-weight: 800; margin-bottom: 12px; color: #4a044e; line-height: 1.2; }
        .banner-text { color: #701a75; margin-bottom: 24px; font-size: 14px; line-height: 1.5; }
        .btn-premium {
            background: linear-gradient(135deg, #d946ef 0%, #a855f7 100%);
            color: white;
            border: none;
            padding: 12px 28px;
            border-radius: 50px;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 10px 20px -5px rgba(168, 85, 247, 0.4);
            transition: transform 0.2s;
        }
        .btn-premium:hover { transform: translateY(-2px); }
        
        .banner-img {
            position: absolute;
            right: 40px;
            bottom: 0;
            height: 110%; /* Overflow slightly */
            z-index: 1;
        }

        /* Stats Row */
        .stats-row {
            display: grid;
            grid-template-columns: 1fr 1fr 1.2fr;
            gap: 24px;
            margin-bottom: 30px;
        }
        .s-card {
            background: white;
            border-radius: 24px;
            padding: 24px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);
            position: relative;
        }
        .icon-box {
            width: 48px; height: 48px; border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 24px; margin-bottom: 16px;
        }
        .card-label { font-size: 13px; color: var(--text-gray); font-weight: 500; margin-bottom: 4px; }
        .card-value { font-size: 24px; font-weight: 700; color: var(--text-dark); display: flex; align-items: baseline; gap: 4px; }
        .card-sub { font-size: 13px; color: var(--text-gray); }

        /* Custom Progress Circle */
        .progress-container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 100%;
        }
        .circular-chart {
            display: block;
            margin: 0 auto;
            max-width: 100px;
            max-height: 250px;
        }
        .circle-bg {
            fill: none;
            stroke: #f3e8ff;
            stroke-width: 3.8;
        }
        .circle {
            fill: none;
            stroke-width: 2.8;
            stroke-linecap: round;
            animation: progress 1s ease-out forwards;
        }
        .percentage {
            fill: #a855f7;
            font-family: 'Outfit', sans-serif;
            font-weight: bold;
            font-size: 0.5em;
            text-anchor: middle;
        }

        /* Continue Watching */
        .section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .cw-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        .video-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.03);
            cursor: pointer;
            transition: transform 0.3s;
        }
        .video-card:hover { transform: translateY(-4px); }
        .video-thumb {
            height: 120px;
            background: #e2e8f0;
            position: relative;
            background-size: cover;
            background-position: center;
        }
        .play-btn {
            position: absolute; inset: 0; margin: auto;
            width: 32px; height: 32px; background: rgba(0,0,0,0.5);
            border-radius: 50%; display: flex; align-items: center; justify-content: center;
            color: white; backdrop-filter: blur(4px);
        }
        .video-info { padding: 16px; }
        .video-cat { font-size: 11px; color: var(--text-gray); margin-bottom: 4px; }
        .video-title { font-size: 14px; font-weight: 700; margin-bottom: 8px; line-height: 1.4; }
        .progress-bar { height: 4px; background: #f1f5f9; border-radius: 2px; overflow: hidden; }
        .progress-fill { height: 100%; background: var(--primary); border-radius: 2px; }

        /* Widgets Grid */
        .widgets-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }
        .widget { background: white; border-radius: 24px; padding: 24px; box-shadow: 0 4px 6px rgba(0,0,0,0.02); height: 100%; }
        .widget-title { font-size: 16px; font-weight: 700; margin-bottom: 20px; display: flex; justify-content: space-between; }
        
        /* Calendar/Events List */
        .event-item {
            display: flex; gap: 16px; margin-bottom: 20px;
            align-items: flex-start;
        }
        .event-date {
            background: #f8fafc; padding: 8px 12px; border-radius: 12px;
            text-align: center; border: 1px solid #e2e8f0;
            min-width: 44px;
        }
        .d-day { font-size: 10px; color: var(--text-gray); text-transform: uppercase; font-weight: 700; }
        .d-num { font-size: 16px; font-weight: 700; color: var(--text-dark); }
        
        .event-details h5 { margin: 0 0 4px 0; font-size: 14px; font-weight: 600; }
        .event-details p { margin: 0; font-size: 12px; color: var(--text-gray); }
        .e-icon { 
            width: 32px; height: 32px; border-radius: 8px; 
            display: flex; align-items: center; justify-content: center;
            font-size: 14px; margin-bottom: 0;
        }

        /* Daily Quest */
        .quest-item {
            display: flex; gap: 12px; padding: 16px;
            background: #f8fafc; border-radius: 16px; margin-bottom: 12px;
            border: 1px solid #f1f5f9;
        }
        .quest-icon {
            width: 36px; height: 36px; border-radius: 10px;
            background: #e0f2fe; color: #0284c7;
            display: flex; align-items: center; justify-content: center;
        }
        .q-info h5 { margin: 0 0 2px 0; font-size: 13px; font-weight: 700; }
        .q-info span { font-size: 11px; color: var(--primary); font-weight: 600; }
        .btn-claim {
            margin-left: auto;
            background: #e0f2fe; color: #0284c7;
            border: none; padding: 6px 12px; border-radius: 20px;
            font-size: 10px; font-weight: 700; cursor: pointer;
        }
        .btn-claim.active { background: var(--primary); color: white; }

        .quest-progress { margin-top: 20px; }
        .qp-label { display: flex; justify-content: space-between; font-size: 12px; color: var(--text-gray); margin-bottom: 6px; }
        
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<main class="main-container">
    <!-- specific header -->
    <div class="s-header">
        <div class="search-bar">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
            <input type="text" class="search-input" placeholder="Search by topic, title, or name">
        </div>
        
        <div class="header-actions">
            <div class="action-btn">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg>
            </div>
                <div class="action-btn">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>
            </div>
                <div class="action-btn">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
            </div>
            <img src="https://i.pravatar.cc/150?img=32" class="user-profile" alt="Profile">
            <svg width="16" height="16" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"></polyline></svg>
        </div>
    </div>

    <!-- Banner -->
    <div class="premium-banner">
        <div class="banner-decoration"></div>
        <div class="banner-content">
            <div class="banner-title">Unlock 1,000+ Premium<br>Courses Today</div>
            <div class="banner-text">Learn from industry experts with exclusive content designed to boost your skills.</div>
            <button class="btn-premium">Go Premium ↗</button>
        </div>
        <!-- Using a generic 3D illustration placeholder -->
        <img src="https://cdni.iconscout.com/illustration/premium/thumb/student-studying-online-2995963-2493817.png" class="banner-img" alt="Illustration">
    </div>

    <!-- Stats -->
    <div class="stats-row">
        <div class="s-card">
            <div>
                <div style="width:40px; height:40px; background:#dcfce7; color:#22c55e; border-radius:10px; display:flex; align-items:center; justify-content:center; margin-bottom:12px;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                </div>
                <div class="card-label">Courses</div>
                <div class="card-value">05<span style="color:#cbd5e1; font-weight:400;">/08</span></div>
                <div class="card-sub">Completed</div>
            </div>
        </div>

        <div class="s-card">
            <div>
                <div style="width:40px; height:40px; background:#ffedd5; color:#f97316; border-radius:10px; display:flex; align-items:center; justify-content:center; margin-bottom:12px;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path></svg>
                </div>
                <div class="card-label">Points Earned</div>
                <div class="card-value">80<span style="color:#cbd5e1; font-weight:400;">/100</span></div>
                <div class="card-sub">Points</div>
            </div>
        </div>

        <div class="s-card" style="flex-direction:row; align-items:center;">
            <div style="flex:1;">
                <div class="progress-container">
                    <svg viewBox="0 0 36 36" class="circular-chart" style="width:100px;">
                        <path class="circle-bg"
                        d="M18 2.0845
                            a 15.9155 15.9155 0 0 1 0 31.831
                            a 15.9155 15.9155 0 0 1 0 -31.831"
                        />
                        <path class="circle"
                        stroke="url(#gradient)" 
                        stroke-dasharray="75, 100"
                        d="M18 2.0845
                            a 15.9155 15.9155 0 0 1 0 31.831
                            a 15.9155 15.9155 0 0 1 0 -31.831"
                        />
                        <defs>
                            <linearGradient id="gradient" x1="0%" y1="0%" x2="100%" y2="0%">
                                <stop offset="0%" stop-color="#d946ef" />
                                <stop offset="100%" stop-color="#a855f7" />
                            </linearGradient>
                        </defs>
                        <text x="18" y="20.35" class="percentage">75%</text>
                    </svg>
                    <div style="text-align:center; margin-right:20px;">
                        <div style="font-size:18px; font-weight:700;">Progress</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Continue Watching -->
    <div class="section-header">
        <h3 style="margin:0; font-size:18px;">Continue Watching</h3>
        <a href="#" style="font-size:13px; font-weight:600; color:var(--text-gray); text-decoration:none;">View All</a>
    </div>
    <div class="cw-grid">
        <div class="video-card">
            <div class="video-thumb" style="background-image: url('https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?auto=format&fit=crop&q=80&w=300');">
                <div class="play-btn">▶</div>
                <div style="position:absolute; bottom:8px; right:8px; background:rgba(0,0,0,0.6); color:white; font-size:10px; padding:2px 6px; border-radius:4px;">04:50</div>
            </div>
            <div class="video-info">
                <div class="video-cat">UX/UI for Beginners</div>
                <div class="video-title">UI & UX Mastery: Design First App</div>
                <div class="progress-bar"><div class="progress-fill" style="width:40%;"></div></div>
            </div>
        </div>
        
        <div class="video-card">
            <div class="video-thumb" style="background-image: url('https://images.unsplash.com/photo-1587620962725-abab7fe55159?auto=format&fit=crop&q=80&w=300');">
                <div class="play-btn">▶</div>
                    <div style="position:absolute; bottom:8px; right:8px; background:rgba(0,0,0,0.6); color:white; font-size:10px; padding:2px 6px; border-radius:4px;">03:25</div>
            </div>
            <div class="video-info">
                <div class="video-cat">Responsive Web Design</div>
                <div class="video-title">Create Mobile-First Websites</div>
                <div class="progress-bar"><div class="progress-fill" style="width:75%;"></div></div>
            </div>
        </div>
        
        <div class="video-card">
            <div class="video-thumb" style="background-image: url('https://images.unsplash.com/photo-1593720213428-28a5b9e94613?auto=format&fit=crop&q=80&w=300');">
                <div class="play-btn">▶</div>
                    <div style="position:absolute; bottom:8px; right:8px; background:rgba(0,0,0,0.6); color:white; font-size:10px; padding:2px 6px; border-radius:4px;">03:45</div>
            </div>
            <div class="video-info">
                <div class="video-cat">Framer Essentials</div>
                <div class="video-title">Build Interactive Prototypes</div>
                    <div class="progress-bar"><div class="progress-fill" style="width:20%;"></div></div>
            </div>
        </div>
    </div>

    <!-- Widgets Grid -->
    <div class="widgets-grid">
        <!-- Events Widget -->
        <div class="widget">
            <div class="widget-title">
                Events
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2"><circle cx="12" cy="12" r="1"></circle><circle cx="19" cy="12" r="1"></circle><circle cx="5" cy="12" r="1"></circle></svg>
            </div>
            
            <div class="event-item">
                <div class="event-date">
                    <div class="d-day">Mon</div>
                    <div class="d-num" style="color:#a855f7;">22</div>
                </div>
                <div class="event-details">
                    <h5>UI Basics Quiz</h5>
                    <p>5 quick MCQs on design.</p>
                </div>
            </div>
            
            <div class="event-item">
                <div class="event-date">
                    <div class="d-day">Tue</div>
                    <div class="d-num">23</div>
                </div>
                <div class="event-details">
                    <h5>Framer Homework</h5>
                    <p>Make 3 Wireframes.</p>
                </div>
            </div>
                
                <div class="event-item">
                <div class="event-date">
                    <div class="d-day">Wed</div>
                    <div class="d-num">24</div>
                </div>
                <div class="event-details">
                    <h5>CSS Live Code</h5>
                    <p>Create interactive card.</p>
                </div>
            </div>
        </div>

        <!-- Daily Quest -->
        <div class="widget">
            <div class="widget-title">
                Daily Quest
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="2"><circle cx="12" cy="12" r="1"></circle><circle cx="19" cy="12" r="1"></circle><circle cx="5" cy="12" r="1"></circle></svg>
            </div>
            
            <div class="quest-item">
                <div class="quest-icon">💎</div>
                <div class="q-info">
                    <h5>Log In 5 Days</h5>
                    <span>+5 Points</span>
                </div>
            </div>
            
                <div class="quest-item">
                <div class="quest-icon" style="background:#fef3c7; color:#d97706;">🏆</div>
                <div class="q-info">
                    <h5>Ace 3 Quizzes</h5>
                    <span>+15 Points</span>
                </div>
            </div>

            <div class="quest-progress">
                <div class="qp-label">
                    <span>5/5 Completed</span>
                    <button class="btn-claim active">Claim Reward</button>
                </div>
                    <div class="qp-label" style="margin-top:10px;">
                    <span>1/3 Completed</span>
                    <button class="btn-claim">Claim Reward</button>
                </div>
            </div>
        </div>
    </div>
</main>

</body>
</html>
