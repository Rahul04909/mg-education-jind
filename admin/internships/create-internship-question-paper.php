<?php
// Include database configuration
require_once __DIR__ . '/../../database/db-config.php';
// Ensure schema
require_once __DIR__ . '/../../database/update_internship_paper_schema.php';

$conn = getDbConnection();
$success_message = '';
$error_message = '';

// Edit Mode Logic
$edit_id = isset($_GET['edit_id']) ? intval($_GET['edit_id']) : 0;
$edit_data = null;
$existing_questions = [];

if ($edit_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM internship_question_papers WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows > 0) {
        $edit_data = $res->fetch_assoc();
        
        // Fetch Questions
        $q_stmt = $conn->prepare("SELECT * FROM internship_questions WHERE paper_id = ? ORDER BY id ASC");
        $q_stmt->bind_param("i", $edit_id);
        $q_stmt->execute();
        $q_res = $q_stmt->get_result();
        while($q = $q_res->fetch_assoc()) {
            $existing_questions[] = $q;
        }
    }
}

// Handle Save
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action']) && $_POST['action'] == 'save_paper') {
    $internship_id = intval($_POST['internship_id']);
    $session_id = intval($_POST['session_id']);
    $total_questions = intval($_POST['total_questions']);
    $marks_per_question = intval($_POST['marks_per_question']);
    $total_marks = $total_questions * $marks_per_question;
    $passing_marks = intval($_POST['passing_marks']);
    
    $exam_date = mysqli_real_escape_string($conn, $_POST['exam_date']);
    $start_time = mysqli_real_escape_string($conn, $_POST['start_time']);
    $exam_duration = intval($_POST['exam_duration']); // in minutes
    
    // Calculate End Time
    $start_timestamp = strtotime("$exam_date $start_time");
    $end_timestamp = $start_timestamp + ($exam_duration * 60);
    $end_time = date("H:i", $end_timestamp);
    
    // Start Transaction
    $conn->begin_transaction();
    
    try {
        if ($edit_id > 0) {
            // Update Existing Paper
            $sql_paper = "UPDATE internship_question_papers SET 
                         internship_id=$internship_id, 
                         session_id=$session_id, 
                         total_questions=$total_questions, 
                         marks_per_question=$marks_per_question, 
                         total_marks=$total_marks, 
                         passing_marks=$passing_marks, 
                         exam_date='$exam_date', 
                         exam_duration=$exam_duration, 
                         start_time='$start_time', 
                         end_time='$end_time' 
                         WHERE id=$edit_id";
            
            if (!$conn->query($sql_paper)) {
                throw new Exception("Error updating paper details: " . $conn->error);
            }
            
            $paper_id = $edit_id;
            
            // Check if results exist (students attempted)
            $res_check = $conn->query("SELECT COUNT(*) as cnt FROM internship_results WHERE internship_paper_id = $edit_id");
            $has_results = $res_check->fetch_assoc()['cnt'] > 0;
            
            if ($has_results) {
                // Cannot delete/replace questions
                $success_message = "Exam details updated successfully! (Questions were preserved and NOT modified because exams have already been submitted)";
            } else {
                // Safe to replace questions
                $conn->query("DELETE FROM internship_questions WHERE paper_id = $paper_id");
                
                // Insert Questions
                $questions_data = $_POST['questions'];
                foreach ($questions_data as $q) {
                    $q_text = mysqli_real_escape_string($conn, $q['text']);
                    $op_a = mysqli_real_escape_string($conn, $q['a']);
                    $op_b = mysqli_real_escape_string($conn, $q['b']);
                    $op_c = mysqli_real_escape_string($conn, $q['c']);
                    $op_d = mysqli_real_escape_string($conn, $q['d']);
                    $correct = mysqli_real_escape_string($conn, $q['correct']);
                    
                    $sql_q = "INSERT INTO internship_questions (paper_id, question_text, option_a, option_b, option_c, option_d, correct_option) 
                              VALUES ($paper_id, '$q_text', '$op_a', '$op_b', '$op_c', '$op_d', '$correct')";
                    $conn->query($sql_q);
                }
                $success_message = "Internship Question Paper updated successfully!";
            }
            
        } else {
            // New Paper Logic
            // Remove any existing for this combo to enforce one paper per session
            $del_sql = "DELETE FROM internship_question_papers WHERE internship_id = $internship_id AND session_id = $session_id";
            $conn->query($del_sql);
            
            // Insert Paper
            $sql_paper = "INSERT INTO internship_question_papers (internship_id, session_id, total_questions, marks_per_question, total_marks, passing_marks, exam_date, exam_duration, start_time, end_time) 
                          VALUES ($internship_id, $session_id, $total_questions, $marks_per_question, $total_marks, $passing_marks, '$exam_date', $exam_duration, '$start_time', '$end_time')";
            
            if (!$conn->query($sql_paper)) {
                throw new Exception("Error saving paper details: " . $conn->error);
            }
            
            $paper_id = $conn->insert_id;
            
            // Insert Questions
            $questions_data = $_POST['questions'];
            foreach ($questions_data as $q) {
                $q_text = mysqli_real_escape_string($conn, $q['text']);
                $op_a = mysqli_real_escape_string($conn, $q['a']);
                $op_b = mysqli_real_escape_string($conn, $q['b']);
                $op_c = mysqli_real_escape_string($conn, $q['c']);
                $op_d = mysqli_real_escape_string($conn, $q['d']);
                $correct = mysqli_real_escape_string($conn, $q['correct']);
                
                $sql_q = "INSERT INTO internship_questions (paper_id, question_text, option_a, option_b, option_c, option_d, correct_option) 
                          VALUES ($paper_id, '$q_text', '$op_a', '$op_b', '$op_c', '$op_d', '$correct')";
                $conn->query($sql_q);
            }
            $success_message = "Internship Question Paper saved successfully!";
        }
        
        $conn->commit();
        
        // Reset edit mode after save if needed, or redirect
        if($edit_id > 0) {
             echo "<script>setTimeout(function(){ window.location.href = 'view-question-papers.php'; }, 2000);</script>";
        }
        
    } catch (Exception $e) {
        $conn->rollback();
        $error_message = $e->getMessage();
    }
}

// Fetch Internships
$internships_result = $conn->query("SELECT id, title FROM internships WHERE is_active = 1 ORDER BY title ASC");
$internships = [];
if ($internships_result) {
    while ($row = $internships_result->fetch_assoc()) {
        $internships[] = $row;
    }
}

// Fetch All Sessions (Grouped by Internship ID for JS)
$sessions = [];
$s_sql = "SELECT id, internship_id, session_name FROM internship_sessions ORDER BY id DESC";
$s_res = $conn->query($s_sql);
if ($s_res) {
    while($row = $s_res->fetch_assoc()) {
        $sessions[$row['internship_id']][] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $edit_id ? 'Edit' : 'Create'; ?> Internship Paper - MG Admin</title>
    <style>
        :root{--active:#22c55e;--indigo:#6f75ff;--line:#e6e8ee;--text:#0b1020;--muted:#6f7787;--error:#ef4444;--success:#22c55e}
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;background:linear-gradient(180deg,#f8fafc 0%,#ffffff 100%);color:var(--text)}
        .admin-content{margin-left:260px;min-height:100vh;padding:20px;transition:margin-left .25s ease}
        .page-header{margin-bottom:30px; display:flex; justify-content:space-between; align-items:center}
        .page-title{font-size:32px;font-weight:800;color:var(--text);margin-bottom:8px}
        .card{background:#fff;border:1px solid var(--line);border-radius:18px;padding:30px;box-shadow:0 4px 12px rgba(0,0,0,.05);margin-bottom:20px}
        .form-group{margin-bottom:20px}
        .form-label{display:block;font-weight:700;color:var(--text);margin-bottom:8px;font-size:14px}
        .form-input, .form-select, .form-textarea{width:100%;padding:12px 16px;border:1px solid var(--line);border-radius:10px;font-size:15px;transition:all .2s ease;font-family:inherit;background:#fff}
        .form-input:focus, .form-select:focus{outline:none;border-color:var(--indigo);box-shadow:0 0 0 3px rgba(111,117,255,.1)}
        .btn{display:inline-flex;align-items:center;gap:8px;padding:12px 24px;border-radius:12px;font-weight:700;font-size:14px;border:none;cursor:pointer;transition:all .2s ease;text-decoration:none}
        .btn-primary{background:linear-gradient(135deg,var(--indigo) 0%,#5a5fff 100%);color:#fff}
        .btn-success{background:var(--success);color:#fff}
        .btn-danger{background:var(--error);color:#fff}
        .btn-outline{background:#fff;color:var(--indigo);border:2px solid var(--indigo)}
        .grid-2{display:grid;grid-template-columns:1fr 1fr;gap:20px}
        .grid-3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:20px}
        
        .question-card{background:#fff; border:1px solid var(--line); border-radius:12px; padding:20px; margin-bottom:15px; position:relative;}
        .q-opt-grid{display:grid; grid-template-columns:1fr 1fr; gap:15px; margin-top:10px;}
        .opt-row{display:flex; align-items:center; gap:10px;}
        .alert{padding:16px 20px;border-radius:12px;margin-bottom:20px;display:flex;align-items:center;gap:12px;border:1px solid}
        .alert-success{background:#d1fae5;border-color:#86efac;color:#065f46}
        .alert-error{background:#fee2e2;border-color:#fca5a5;color:#991b1b}
    </style>
</head>
<body>
    <main class="admin-content">
        <?php include __DIR__ . "/../sidebar.php"; ?>
        
        <div class="page-header">
            <div>
                <div style="font-size:14px;color:var(--muted);margin-bottom:10px">
                    <a href="../index.php" style="text-decoration:none;color:var(--indigo)">Dashboard</a> › <a href="view-question-papers.php" style="text-decoration:none;color:var(--indigo)">Internships</a> › <?php echo $edit_id ? 'Edit' : 'Create'; ?> Paper
                </div>
                <h1 class="page-title"><?php echo $edit_id ? 'Edit Internship Question Paper' : 'Create Internship Question Paper'; ?></h1>
            </div>
            <div>
                 <a href="view-question-papers.php" class="btn btn-outline">View All Papers</a>
            </div>
        </div>

        <?php if (!empty($success_message)): ?>
        <div class="alert alert-success"><span>✓ <?php echo $success_message; ?></span></div>
        <?php endif; ?>

        <?php if (!empty($error_message)): ?>
        <div class="alert alert-error"><span>⚠ <?php echo $error_message; ?></span></div>
        <?php endif; ?>

        <form method="POST" id="paperForm">
            <input type="hidden" name="action" value="save_paper">
            
            <!-- 1. Selection -->
            <div class="card">
                <h3 style="margin-bottom:15px;">Target Internship</h3>
                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label">Select Internship *</label>
                        <select name="internship_id" id="internship_id" class="form-select" onchange="populateSessions()" required>
                            <option value="">-- Choose --</option>
                            <?php foreach($internships as $i): ?>
                                <option value="<?php echo $i['id']; ?>" <?php echo ($edit_data && $edit_data['internship_id'] == $i['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($i['title']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Select Session *</label>
                        <select name="session_id" id="session_id" class="form-select" required>
                            <option value="">-- Choose Internship First --</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- 2. Exam Details -->
            <div class="card">
                <h3 style="margin-bottom:15px;">Exam Configuration</h3>
                
                <div class="grid-3" style="margin-bottom:20px;">
                    <div class="form-group">
                        <label class="form-label">Total Questions</label>
                        <input type="number" id="totalQuestions" name="total_questions" class="form-input" required oninput="calculateTotals()" value="<?php echo $edit_data['total_questions'] ?? ''; ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Marks per Question</label>
                        <input type="number" id="marksPerQuestion" name="marks_per_question" class="form-input" required oninput="calculateTotals()" value="<?php echo $edit_data['marks_per_question'] ?? ''; ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Total Marks (Auto)</label>
                        <input type="text" id="totalMarks" name="total_marks" class="form-input" readonly style="background:#f8fafc" value="<?php echo $edit_data['total_marks'] ?? ''; ?>">
                    </div>
                </div>
                
                <div class="form-group" style="max-width:33%;">
                    <label class="form-label">Passing Marks</label>
                    <input type="number" name="passing_marks" class="form-input" required value="<?php echo $edit_data['passing_marks'] ?? ''; ?>">
                </div>
                
                <hr style="border:0; border-top:1px solid var(--line); margin:20px 0;">
                
                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label">Exam Date</label>
                        <input type="date" name="exam_date" class="form-input" required value="<?php echo $edit_data['exam_date'] ?? ''; ?>">
                    </div>
                    <div class="grid-3">
                         <div class="form-group">
                            <label class="form-label">Start Time</label>
                            <input type="time" id="startTime" name="start_time" class="form-input" required onchange="calculateEndTime()" value="<?php echo $edit_data ? date('H:i', strtotime($edit_data['start_time'])) : ''; ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Duration (Mins)</label>
                            <input type="number" id="examDuration" name="exam_duration" class="form-input" placeholder="e.g 60" required oninput="calculateEndTime()" value="<?php echo $edit_data['exam_duration'] ?? ''; ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">End Time (Auto)</label>
                            <input type="time" id="endTime" name="end_time" class="form-input" readonly style="background:#f8fafc" value="<?php echo $edit_data ? date('H:i', strtotime($edit_data['end_time'])) : ''; ?>">
                        </div>
                    </div>
                </div>
                
                <div style="margin-top:15px">
                     <button type="button" id="startBtn" class="btn btn-primary" onclick="startBuilding()"><?php echo $edit_id ? 'Load Questions' : 'Next: Add Questions'; ?></button>
                </div>
            </div>

            <!-- 3. Question Builder -->
            <div id="builderSection" style="display:none;">
                <div id="questionsContainer"></div>
                
                <div style="margin-top:20px; display:flex; gap:15px;">
                    <button type="button" class="btn btn-outline" onclick="addQuestion()">+ Add Another Question</button>
                    <button type="submit" class="btn btn-success" style="padding:12px 30px;">Save Exam Paper</button>
                </div>
            </div>
        </form>
    </main>

    <script>
        const allSessions = <?php echo json_encode($sessions); ?>;
        
        // Edit Mode Logic in JS
        const editMode = <?php echo $edit_id ? 'true' : 'false'; ?>;
        const initialQuestions = <?php echo json_encode($existing_questions); ?>;
        const initialSessionId = <?php echo $edit_data['session_id'] ?? 'null'; ?>;
        
        document.addEventListener('DOMContentLoaded', function() {
            if(editMode) {
                populateSessions();
                if(initialSessionId) {
                    document.getElementById('session_id').value = initialSessionId;
                }
                
                // Auto trigger calculations
                calculateTotals();
                calculateEndTime();
                
                // If we want to auto-show questions
                 startBuilding();
            }
        });

        function populateSessions() {
            const internshipId = document.getElementById('internship_id').value;
            const sessionSelect = document.getElementById('session_id');
            sessionSelect.innerHTML = '<option value="">-- Choose --</option>';
            
            if (internshipId && allSessions[internshipId]) {
                allSessions[internshipId].forEach(sess => {
                    const opt = document.createElement('option');
                    opt.value = sess.id;
                    opt.textContent = sess.session_name;
                    sessionSelect.appendChild(opt);
                });
            }
        }
        
        function calculateTotals() {
            const q = parseInt(document.getElementById('totalQuestions').value) || 0;
            const m = parseInt(document.getElementById('marksPerQuestion').value) || 0;
            document.getElementById('totalMarks').value = q * m;
        }
        
        function calculateEndTime() {
            const start = document.getElementById('startTime').value;
            const dur = parseInt(document.getElementById('examDuration').value) || 0;
            
            if(start && dur) {
                const [hours, mins] = start.split(':').map(Number);
                const date = new Date();
                date.setHours(hours);
                date.setMinutes(mins + dur);
                
                const endHours = String(date.getHours()).padStart(2, '0');
                const endMins = String(date.getMinutes()).padStart(2, '0');
                document.getElementById('endTime').value = `${endHours}:${endMins}`;
            }
        }
        
        let questionCount = 0;
        let maxQuestions = 0;
        
        function startBuilding() {
            maxQuestions = parseInt(document.getElementById('totalQuestions').value) || 0;
            if(maxQuestions <= 0) {
                alert("Please enter total number of questions first.");
                return;
            }
            
            document.getElementById('builderSection').style.display = 'block';
            document.getElementById('startBtn').style.display = 'none';
            // Lock Config
            document.getElementById('totalQuestions').readOnly = true;
            
            // Populate if empty and we have data
            if(questionCount === 0) {
                if(editMode && initialQuestions.length > 0) {
                    initialQuestions.forEach(q => {
                        addQuestion(q);
                    });
                } else {
                    addQuestion();
                }
            }
        }
        
        function addQuestion(data = null) {
            if (questionCount >= maxQuestions) {
                alert(`Limit reached: ${maxQuestions} questions.`);
                return;
            }
            questionCount++;
            
            // Allow pre-fill
            const text = data ? data.question_text : '';
            const a = data ? data.option_a : '';
            const b = data ? data.option_b : '';
            const c = data ? data.option_c : '';
            const d = data ? data.option_d : '';
            const correct = data ? data.correct_option : '';
            
            const div = document.createElement('div');
            div.className = 'question-card';
            div.innerHTML = `
                <div style="font-weight:700; margin-bottom:10px;">Question ${questionCount}</div>
                <div class="form-group">
                    <textarea name="questions[${questionCount}][text]" class="form-textarea" rows="2" placeholder="Enter Question Text" required>${text}</textarea>
                </div>
                <div class="q-opt-grid">
                    <div class="opt-row"><input type="radio" name="questions[${questionCount}][correct]" value="A" required ${correct=='A'?'checked':''}> <input type="text" name="questions[${questionCount}][a]" class="form-input" placeholder="Option A" required value="${a}"></div>
                    <div class="opt-row"><input type="radio" name="questions[${questionCount}][correct]" value="B" ${correct=='B'?'checked':''}> <input type="text" name="questions[${questionCount}][b]" class="form-input" placeholder="Option B" required value="${b}"></div>
                    <div class="opt-row"><input type="radio" name="questions[${questionCount}][correct]" value="C" ${correct=='C'?'checked':''}> <input type="text" name="questions[${questionCount}][c]" class="form-input" placeholder="Option C" required value="${c}"></div>
                    <div class="opt-row"><input type="radio" name="questions[${questionCount}][correct]" value="D" ${correct=='D'?'checked':''}> <input type="text" name="questions[${questionCount}][d]" class="form-input" placeholder="Option D" required value="${d}"></div>
                </div>
            `;
            document.getElementById('questionsContainer').appendChild(div);
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>
