<?php
// Exam Sidebar Component
?>
<div class="exam-sidebar">
    <!-- 1. Timer -->
    <div class="sidebar-header">
        <div class="timer-box">
            <span style="font-size:12px; color:#64748b; font-weight:600;">Time Remaining</span>
            <div id="exam-timer" class="timer-display">00:00:00</div>
        </div>
        <div class="user-mini-profile">
            <?php 
                $u_photo = "../assets/images/avatar-placeholder.png";
                if(!empty($student['student_photo']) && file_exists("../".$student['student_photo'])){
                    $u_photo = "../".$student['student_photo'];
                }
            ?>
            <img src="<?php echo $u_photo; ?>" alt="User" class="sidebar-avatar">
            <div class="sidebar-user-info">
                <div class="u-name"><?php echo htmlspecialchars($student['full_name']); ?></div>
                <div class="u-id"><?php echo htmlspecialchars($student['enrollment_no']); ?></div>
            </div>
        </div>
    </div>

    <!-- 2. Exam Info -->
    <div class="exam-info-panel">
        <div class="info-row">
            <span>Subject:</span> 
            <strong><?php echo htmlspecialchars($paper['subject_name'] ?? 'General'); ?></strong>
        </div>
         <div class="info-row">
            <span>Questions:</span> 
            <strong><?php echo $paper['total_questions']; ?></strong>
        </div>
        <div class="info-row">
            <span>Marks:</span> 
            <strong><?php echo $paper['total_marks']; ?></strong>
        </div>
        <div class="info-row">
            <span>Passing Marks:</span> 
            <strong><?php echo $paper['passing_marks']; ?></strong>
        </div>
    </div>

    <!-- 3. Palette -->
    <div class="question-palette-section">
        <h4 style="font-size:13px; margin-bottom:10px; color:#334155;">Question Palette:</h4>
        <div class="palette-grid">
            <?php 
            $real_total = isset($questions) ? count($questions) : $paper['total_questions'];
            for($i=1; $i<=$real_total; $i++): 
            ?>
                <button class="palette-btn not-visited" data-q="<?php echo $i; ?>" onclick="jumpToQuestion(<?php echo $i; ?>)">
                    <?php echo $i; ?>
                </button>
            <?php endfor; ?>
        </div>
    </div>

    <!-- 4. Legend -->
    <div class="palette-legend">
        <div class="legend-item"><span class="dot answered"></span> Answered</div>
        <div class="legend-item"><span class="dot not-answered"></span> Not Answered</div>
        <div class="legend-item"><span class="dot marked"></span> Marked</div>
        <div class="legend-item"><span class="dot not-visited"></span> Not Visited</div>
    </div>

    <!-- 5. Submit -->
    <div class="sidebar-footer">
        <button class="btn-submit-exam" onclick="confirmSubmit()">Submit Exam</button>
    </div>
</div>

<style>
    /* Sidebar Specific Styles */
    .exam-sidebar {
        width: 320px;
        background: white;
        border-left: 1px solid #e2e8f0;
        display: flex;
        flex-direction: column;
        height: 100vh;
        position: fixed;
        right: 0;
        top: 0;
        z-index: 500;
    }

    .sidebar-header {
        padding: 15px;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
    }
    .timer-display {
        font-size: 24px;
        font-weight: 700;
        color: #ef4444;
        font-family: monospace;
        letter-spacing: 1px;
    }
    .user-mini-profile {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-top: 10px;
        padding-top: 10px;
        border-top: 1px solid #e2e8f0;
    }
    .sidebar-avatar { width: 36px; height: 36px; border-radius: 50%; object-fit: cover; }
    .sidebar-user-info .u-name { font-size: 13px; font-weight: 700; line-height: 1.2; }
    .sidebar-user-info .u-id { font-size: 11px; color: #64748b; }

    .exam-info-panel { padding: 15px; background: #fff; border-bottom: 1px solid #e2e8f0; }
    .info-row { display: flex; justify-content: space-between; font-size: 13px; margin-bottom: 5px; color: #475569; }

    .question-palette-section {
        padding: 15px;
        flex: 1;
        overflow-y: auto;
        background: #fdfdfd;
    }
    .palette-grid {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 8px;
    }
    .palette-btn {
        width: 100%;
        aspect-ratio: 1;
        border: 1px solid #e2e8f0;
        background: white;
        border-radius: 4px;
        cursor: pointer;
        font-weight: 600;
        font-size: 13px;
        color: #334155;
        transition: 0.2s;
    }
    .palette-btn:hover { background: #f1f5f9; }

    /* Palette States */
    .palette-btn.not-visited { background: #fff; }
    .palette-btn.answered { background: #22c55e; color: white; border-color: #22c55e; }
    .palette-btn.not-answered { background: #ef4444; color: white; border-color: #ef4444; }
    .palette-btn.marked { background: #a855f7; color: white; border-color: #a855f7; }
    .palette-btn.active { border: 2px solid #0f172a; transform: scale(1.05); }

    .palette-legend {
        padding: 10px 15px;
        background: #f8fafc;
        border-top: 1px solid #e2e8f0;
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
        font-size: 11px;
        color: #64748b;
    }
    .legend-item { display: flex; align-items: center; gap: 6px; }
    .dot { width: 10px; height: 10px; border-radius: 2px; display: inline-block; border: 1px solid #cbd5e1; }
    .dot.answered { background: #22c55e; border-color: #22c55e; }
    .dot.not-answered { background: #ef4444; border-color: #ef4444; }
    .dot.marked { background: #a855f7; border-color: #a855f7; }
    .dot.not-visited { background: white; }

    .sidebar-footer { padding: 15px; background: white; border-top: 1px solid #e2e8f0; text-align: center; }
    .btn-submit-exam {
        width: 100%;
        background: #22c55e;
        color: white;
        border: none;
        padding: 12px;
        border-radius: 8px;
        font-weight: 700;
        cursor: pointer;
        font-size: 15px;
    }
    .btn-submit-exam:hover { background: #16a34a; }

    @media(max-width: 1024px) {
        .exam-sidebar { display: none; /* Mobile handling TBD */ }
    }
</style>
