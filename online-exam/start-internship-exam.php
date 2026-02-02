<?php
session_start();
require_once __DIR__ . '/../database/db-config.php';
date_default_timezone_set('Asia/Kolkata');

if (!isset($_SESSION['student_id']) || !isset($_GET['exam_id'])) {
    header("Location: index.php");
    exit;
}

$conn = getDbConnection();
$student_id = $_SESSION['student_id'];
$exam_id = intval($_GET['exam_id']); // This is the id of internship_question_papers

// 0. Check if already attempted
$chk_sql = "SELECT id FROM internship_results WHERE internship_paper_id = $exam_id AND student_id = $student_id";
if ($conn->query($chk_sql)->num_rows > 0) {
    header("Location: internship-result.php?exam_id=" . $exam_id);
    exit;
}

// 1. Fetch Paper Details (Internship Exam)
$sql = "SELECT qp.*, qp.id as paper_id, i.title as subject_name 
        FROM internship_question_papers qp
        JOIN internships i ON qp.internship_id = i.id
        WHERE qp.id = $exam_id";

$res = $conn->query($sql);
if ($res->num_rows == 0) {
    die("Invalid Exam or Paper not found.");
}
$paper = $res->fetch_assoc();

// Calculate marks per question if not present?
// internship_question_papers has `marks_per_question`? 
// Let's check schema from create-internship-question-paper.php logic or view_file?
// I recall creating it. It has `marks_per_question`. 
// Wait, create script logic used manual calculation. Let's assume the column exists or I calculate it.
// The `database/update_internship_paper_schema.php` (from memory) added `marks_per_question`.
// Let's assume it exists. If not, calculate: total_marks / total_questions.

$marks_per_q = $paper['marks_per_question'] ?? ($paper['total_marks'] / $paper['total_questions']);


// 2. Fetch Questions
$p_id = $paper['paper_id'];
$q_sql = "SELECT id, question_text, option_a, option_b, option_c, option_d FROM internship_questions WHERE internship_paper_id = $p_id ORDER BY id ASC";
$q_res = $conn->query($q_sql);
$questions = [];
while ($row = $q_res->fetch_assoc()) {
    $questions[] = $row;
}

if (empty($questions)) {
    die("No questions found in this paper.");
}

// 4. Calculate Duration
$exam_start = strtotime($paper['exam_date'] . ' ' . $paper['start_time']);
$exam_end = $exam_start + ($paper['duration_minutes'] * 60);
$now = time();
$remaining_seconds = $exam_end - $now;

if ($remaining_seconds <= 0) {
    die("Exam time has ended.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attempt Exam - <?php echo htmlspecialchars($paper['subject_name']); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        :root { --primary: #6366f1; --bg: #f8fafc; --text: #0f172a; }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family: 'Outfit', sans-serif; background: var(--bg); color: var(--text); overflow: hidden; height: 100vh; }
        
        .exam-layout { display: flex; height: 100vh; }
        .question-area { flex: 1; padding: 30px; overflow-y: auto; margin-right: 320px; }
        .q-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #e2e8f0; padding-bottom: 15px; margin-bottom: 20px; }
        .q-number { font-size: 20px; font-weight: 700; color: var(--primary); }
        .q-marks { font-size: 14px; background: #e0e7ff; color: var(--primary); padding: 4px 10px; border-radius: 4px; font-weight: 600; }
        .q-text { font-size: 18px; font-weight: 500; line-height: 1.6; margin-bottom: 30px; }
        .options-list { display: flex; flex-direction: column; gap: 15px; }
        .option-label { display: flex; align-items: center; padding: 15px 20px; border: 2px solid #e2e8f0; border-radius: 10px; cursor: pointer; transition: 0.2s; }
        .option-label:hover { border-color: #cbd5e1; background: #f8fafc; }
        .option-label.selected { border-color: var(--primary); background: #eef2ff; }
        .opt-radio { margin-right: 15px;accent-color: var(--primary); transform: scale(1.2); }
        .opt-val { font-size: 16px; font-weight: 500; }
        
        .action-bar { position: fixed; bottom: 0; left: 0; right: 320px; background: white; border-top: 1px solid #e2e8f0; padding: 15px 30px; display: flex; justify-content: space-between; align-items: center; z-index: 100; }
        .btn-action { padding: 10px 24px; border-radius: 8px; font-weight: 600; border: none; cursor: pointer; font-size: 14px; transition: 0.2s; }
        .btn-next { background: var(--primary); color: white; }
        .btn-next:hover { background: #4f46e5; }
        .btn-mark { background: #a855f7; color: white; }
        .btn-clear { background: white; border: 1px solid #cbd5e1; color: #475569; }
        .btn-prev { background: #f1f5f9; color: #475569; }
        .loading-mask { position: fixed; inset: 0; background: white; z-index: 1000; display: flex; align-items: center; justify-content: center; font-size: 20px; font-weight: 700; color: var(--primary); }
    </style>
</head>
<body>

    <div class="loading-mask" id="loader">Loading Exam Environment...</div>

    <div class="exam-layout">
        <main class="question-area">
            <div class="q-header">
                <div class="q-number">Question <span id="disp-q-no">1</span></div>
                <div class="q-marks">+<?php echo $marks_per_q; ?> Marks</div>
            </div>
            
            <div class="q-text" id="disp-q-text"></div>
            <div class="options-list" id="disp-options"></div>
            <div style="height: 100px;"></div>
        </main>
        
        <?php include 'exam-sidebar.php'; ?>
    </div>

    <div class="action-bar">
        <div style="display:flex; gap:10px;">
            <button class="btn-action btn-prev" onclick="changeQuestion(-1)">Previous</button>
            <button class="btn-action btn-clear" onclick="clearSelection()">Clear Response</button>
            <button class="btn-action btn-mark" onclick="markForReview()">Mark for Review</button>
        </div>
        <button class="btn-action btn-next" onclick="saveAndNext()">Save & Next</button>
    </div>

    <script>
        const questions = <?php echo json_encode($questions); ?>;
        const totalQuestions = questions.length;
        let currentIdx = 0;
        let userResponses = {}; 
        let timeRemaining = <?php echo $remaining_seconds; ?>;
        
        window.onload = function() {
            document.getElementById('loader').style.display = 'none';
            renderQuestion();
            startTimer();
        };

        function startTimer() {
            const timerEl = document.getElementById('exam-timer');
            const interval = setInterval(() => {
                timeRemaining--;
                if (timeRemaining <= 0) {
                    clearInterval(interval);
                    submitExam(true); 
                }
                const h = Math.floor(timeRemaining / 3600).toString().padStart(2, '0');
                const m = Math.floor((timeRemaining % 3600) / 60).toString().padStart(2, '0');
                const s = (timeRemaining % 60).toString().padStart(2, '0');
                if(timerEl) timerEl.innerText = `${h}:${m}:${s}`;
                if (timeRemaining < 300 && timerEl) timerEl.style.color = '#dc2626';
            }, 1000);
        }

        function renderQuestion() {
            const q = questions[currentIdx];
            document.getElementById('disp-q-no').innerText = currentIdx + 1;
            document.getElementById('disp-q-text').innerText = q.question_text;
            
            const optsContainer = document.getElementById('disp-options');
            optsContainer.innerHTML = '';
            
            const ops = ['A', 'B', 'C', 'D'];
            const labels = [q.option_a, q.option_b, q.option_c, q.option_d];
            const savedResp = userResponses[q.id]?.selected || null;
            
            ops.forEach((op, i) => {
                const isChecked = (savedResp === op) ? 'checked' : '';
                const isSelectedClass = (savedResp === op) ? 'selected' : '';
                const html = `
                    <label class="option-label ${isSelectedClass}" onclick="selectOption(this, '${op}')">
                        <input type="radio" name="opt_grp" class="opt-radio" value="${op}" ${isChecked}>
                        <span class="opt-val">${labels[i]}</span>
                    </label>
                `;
                optsContainer.insertAdjacentHTML('beforeend', html);
            });

            document.querySelectorAll('.palette-btn').forEach(btn => btn.classList.remove('active'));
            const pBtn = document.querySelector(`.palette-btn[data-q="${currentIdx+1}"]`);
            if(pBtn) pBtn.classList.add('active');
        }

        function selectOption(labelEl, val) {
            document.querySelectorAll('.option-label').forEach(el => el.classList.remove('selected'));
            labelEl.classList.add('selected');
            labelEl.querySelector('input').checked = true;
        }

        function getSelectedOption() {
            const el = document.querySelector('input[name="opt_grp"]:checked');
            return el ? el.value : null;
        }

        function saveAndNext() {
            const val = getSelectedOption();
            const qId = questions[currentIdx].id;
            
            if (val) {
                userResponses[qId] = { selected: val, status: 'answered' };
                updatePalette(currentIdx + 1, 'answered');
            } else if (!userResponses[qId]) {
                userResponses[qId] = { selected: null, status: 'not-answered' };
                updatePalette(currentIdx + 1, 'not-answered');
            }

            if (currentIdx < totalQuestions - 1) {
                currentIdx++;
                renderQuestion();
            }
        }

        function markForReview() {
            const val = getSelectedOption();
            const qId = questions[currentIdx].id;
            userResponses[qId] = { selected: val, status: 'marked' };
            updatePalette(currentIdx + 1, 'marked');
            if (currentIdx < totalQuestions - 1) {
                currentIdx++;
                renderQuestion();
            }
        }

        function clearSelection() {
            document.querySelectorAll('input[name="opt_grp"]').forEach(el => el.checked = false);
            document.querySelectorAll('.option-label').forEach(el => el.classList.remove('selected'));
            const qId = questions[currentIdx].id;
            if(userResponses[qId]) {
                delete userResponses[qId];
                updatePalette(currentIdx + 1, 'not-visited');
            }
        }

        function changeQuestion(dir) {
            const newIdx = currentIdx + dir;
            if (newIdx >= 0 && newIdx < totalQuestions) {
                currentIdx = newIdx;
                renderQuestion();
            }
        }
        
        function jumpToQuestion(qNo) {
            currentIdx = qNo - 1;
            renderQuestion();
        }

        function updatePalette(qNo, status) {
            const btn = document.querySelector(`.palette-btn[data-q="${qNo}"]`);
            if(btn) btn.className = `palette-btn ${status}`;
        }

        function confirmSubmit() {
            let answeredCount = 0;
            questions.forEach(q => {
                if (userResponses[q.id] && userResponses[q.id].selected) answeredCount++;
            });

            if (answeredCount < totalQuestions) {
                alert(`You have only answered ${answeredCount} out of ${totalQuestions} questions.`);
            }

            if(confirm("Are you sure you want to submit?")) {
                submitExam(false);
            }
        }

        function submitExam(isAuto) {
            document.getElementById('loader').innerText = isAuto ? "Time Up! Submitting..." : "Submitting...";
            document.getElementById('loader').style.display = 'flex';
            
            fetch('submit-internship-exam.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    exam_id: <?php echo $exam_id; ?>,
                    answers: userResponses
                })
            })
            .then(res => res.json())
            .then(data => {
                if(data.status === 'success') {
                    alert("Submitted Successfully!");
                    window.location.href = "internship-result.php?exam_id=" + <?php echo $exam_id; ?>;
                } else {
                    alert("Failed: " + data.message);
                    document.getElementById('loader').style.display = 'none';
                }
            })
            .catch(err => {
                console.error(err);
                alert("Error submitting exam.");
                document.getElementById('loader').style.display = 'none';
            });
        }
    </script>
</body>
</html>
