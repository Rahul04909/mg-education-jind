<?php
// Include database configuration
require_once __DIR__ . '/../../database/db-config.php';
// Ensure schema
require_once __DIR__ . '/../../database/update_paper_schema.php';

$conn = getDbConnection();
$success_message = '';
$error_message = '';

// Handle Save
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action']) && $_POST['action'] == 'save_paper') {
    $subject_id = intval($_POST['subject_id']);
    $session_id = intval($_POST['session_id']);
    $total_questions = intval($_POST['total_questions']);
    $marks_per_question = intval($_POST['marks_per_question']);
    $total_marks = $total_questions * $marks_per_question;
    
    // Check if paper already exists for this subject/session combination, delete old one to replace
    $del_sql = "DELETE FROM question_papers WHERE subject_id = $subject_id AND session_id = $session_id";
    $conn->query($del_sql);

    // Insert Paper
    $sql_paper = "INSERT INTO question_papers (subject_id, session_id, total_questions, marks_per_question, total_marks) 
                  VALUES ($subject_id, $session_id, $total_questions, $marks_per_question, $total_marks)";
    
    if ($conn->query($sql_paper) === TRUE) {
        $paper_id = $conn->insert_id;
        $questions_data = $_POST['questions']; // Array of questions

        $q_error = false;
        foreach ($questions_data as $q) {
            $q_text = mysqli_real_escape_string($conn, $q['text']);
            $op_a = mysqli_real_escape_string($conn, $q['a']);
            $op_b = mysqli_real_escape_string($conn, $q['b']);
            $op_c = mysqli_real_escape_string($conn, $q['c']);
            $op_d = mysqli_real_escape_string($conn, $q['d']);
            $correct = mysqli_real_escape_string($conn, $q['correct']);

            $sql_q = "INSERT INTO questions (paper_id, question_text, option_a, option_b, option_c, option_d, correct_option) 
                      VALUES ($paper_id, '$q_text', '$op_a', '$op_b', '$op_c', '$op_d', '$correct')";
            if (!$conn->query($sql_q)) {
                $q_error = true;
            }
        }

        if (!$q_error) {
            $success_message = "Question Paper saved successfully!";
        } else {
            $error_message = "Paper saved but some questions failed to save.";
        }

    } else {
        $error_message = "Error saving paper: " . $conn->error;
    }
}

// Handle Delete
if (isset($_POST['action']) && $_POST['action'] == 'delete_paper') {
    $paper_id = intval($_POST['paper_id']);
    $conn->query("DELETE FROM question_papers WHERE id = $paper_id");
    $success_message = "Paper deleted successfully.";
}

// Fetch Subjects
$subjects_result = $conn->query("SELECT s.id, s.name, s.code, s.theory_marks, s.assignment_marks, c.title as course_name 
                                 FROM subjects s 
                                 JOIN courses c ON s.course_id = c.id 
                                 JOIN courses c ON s.course_id = c.id 
                                 ORDER BY c.title ASC, s.name ASC");
$subjects = [];
while ($row = $subjects_result->fetch_assoc()) {
    $subjects[] = $row;
}

// Fetch Sessions
$sessions = [];
$s_sql = "SELECT id, course_id, session_name FROM course_sessions WHERE is_active = 1 ORDER BY id DESC";
$s_res = $conn->query($s_sql);
while($row = $s_res->fetch_assoc()) {
    $sessions[$row['course_id']][] = $row;
}

// Data for Edit Mode
$edit_mode = false;
$existing_paper = null;
$existing_questions = [];

if (isset($_GET['subject_id'])) {
    $sub_id = intval($_GET['subject_id']);
    // For edit mode, we might need more specific targeting if multiple sessions exist, 
    // but usually edit comes from a list where we have the specific ID. 
    // If we only have subject_id, we might pick the latest or require session_id in GET.
    // Assuming for now if coming from a "Subject View" we pick the latest, 
    // otherwise if session_id is provided use that.
    $sess_filter = "";
    if (isset($_GET['session_id'])) {
        $sess_id = intval($_GET['session_id']);
        $sess_filter = " AND session_id = $sess_id";
    }
    
    $paper_result = $conn->query("SELECT * FROM question_papers WHERE subject_id = $sub_id $sess_filter ORDER BY id DESC LIMIT 1");
    if ($paper_result->num_rows > 0) {
        $edit_mode = true;
        $existing_paper = $paper_result->fetch_assoc();
        $paper_id = $existing_paper['id'];
        
        $qs_result = $conn->query("SELECT * FROM questions WHERE paper_id = $paper_id ORDER BY id ASC");
        while($q = $qs_result->fetch_assoc()) {
            $existing_questions[] = $q;
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Question Paper - MG Education</title>
    <style>
        :root{--active:#22c55e;--indigo:#6f75ff;--line:#e6e8ee;--text:#0b1020;--muted:#6f7787;--error:#ef4444;--success:#22c55e}
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;background:linear-gradient(180deg,#f8fafc 0%,#ffffff 100%);color:var(--text)}
        .admin-content{margin-left:260px;min-height:100vh;padding:20px;transition:margin-left .25s ease}
        body.sidebar-collapsed .admin-content{margin-left:88px}
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
        .info-box{background:#f8fafc; padding:15px; border-radius:10px; border:1px solid var(--line); font-size:14px; color:var(--text); margin-bottom:20px; display:none;}
        
        .question-card{background:#fff; border:1px solid var(--line); border-radius:12px; padding:20px; margin-bottom:15px; position:relative;}
        .remove-q-btn{position:absolute; top:15px; right:15px; color:var(--error); cursor:pointer; font-weight:700;}
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
                    <a href="../index.php" style="text-decoration:none;color:var(--indigo)">Dashboard</a> › Courses › Create Question Paper
                </div>
                <h1 class="page-title"><?php echo $edit_mode ? 'Edit Question Paper' : 'Create Question Paper'; ?></h1>
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
                <h3 style="margin-bottom:15px;">Exam Details</h3>
                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label">Select Subject *</label>
                        <select name="subject_id" id="subject_id" class="form-select" onchange="fetchSubjectDetails()" required>
                            <option value="">-- Choose --</option>
                            <?php foreach($subjects as $s): ?>
                                <option value="<?php echo $s['id']; ?>" 
                                    data-course="<?php echo $s['course_id']; ?>"
                                    data-theory="<?php echo $s['theory_marks']; ?>" 
                                    data-assign="<?php echo $s['assignment_marks']; ?>"
                                    <?php echo ($edit_mode && $existing_paper['subject_id'] == $s['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($s['name']); ?> (<?php echo htmlspecialchars($s['course_name']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Select Session *</label>
                        <select name="session_id" id="session_id" class="form-select" required>
                            <option value="">-- Choose --</option>
                        </select>
                    </div>
                </div>

                <div id="subjectInfo" class="info-box">
                    <div class="grid-2">
                        <div><strong>Theory Marks:</strong> <span id="theoryMarks">0</span></div>
                        <div><strong>Assignment Marks:</strong> <span id="assignMarks">0</span></div>
                    </div>
                </div>
            </div>

            <!-- 2. Configuration -->
            <div class="card" id="configCard" style="display:none;">
                <h3 style="margin-bottom:15px;">Paper Configuration</h3>
                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label">Total No. of Questions</label>
                        <input type="number" id="totalQuestions" name="total_questions" class="form-input" required oninput="validateConfig()">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Marks per Question</label>
                        <input type="number" id="marksPerQuestion" name="marks_per_question" class="form-input" required oninput="validateConfig()">
                    </div>
                </div>
                <div id="validationMsg" style="color:var(--error); font-weight:600; font-size:14px; margin-bottom:10px;"></div>
                <button type="button" id="startBtn" class="btn btn-primary" onclick="startBuilding()" disabled>Start Adding Questions</button>
            </div>

            <!-- 3. Question Builder -->
            <div id="builderSection" style="display:none;">
                <div id="questionsContainer">
                    <!-- Questions will be added here -->
                </div>
                
                <div style="margin-top:20px; display:flex; gap:15px;">
                    <button type="button" class="btn btn-outline" onclick="addQuestion()">+ Add Another Question</button>
                    <button type="submit" class="btn btn-success" style="padding:12px 30px;">Save Exam Paper</button>
                </div>
            </div>
        </form>
    </main>

    <script>
        let theoryMarks = 0;
        let questionCount = 0;
        let maxQuestions = 0;

        // Edit Mode Data
        const editMode = <?php echo $edit_mode ? 'true' : 'false'; ?>;
        const initialConfig = <?php echo $edit_mode ? json_encode($existing_paper) : 'null'; ?>;
        const initialQuestions = <?php echo $edit_mode ? json_encode($existing_questions) : '[]'; ?>;

        document.addEventListener("DOMContentLoaded", function() {
            if (editMode && initialConfig) {
                // Trigger fetch to set theory marks
                // fetchSubjectDetails(); // Called manually below to wait for session population logic

                // Set Config
                document.getElementById('totalQuestions').value = initialConfig.total_questions;
                document.getElementById('marksPerQuestion').value = initialConfig.marks_per_question;
                
                // Validate & Start
                validateConfig();
                startBuilding();

                // Load Questions
                // Note: startBuilding added an empty one, let's remove it if we have existing ones, or just populate
                // Since startBuilding adds one if count is 0, we can clear the container first
                if (initialQuestions.length > 0) {
                     document.getElementById('questionsContainer').innerHTML = '';
                     questionCount = 0;
                     initialQuestions.forEach(q => {
                         addQuestion(q);
                     });
                }
            }
        });

            }
        });

        // Dynamic Sessions Data
        const allSessions = <?php echo json_encode($sessions); ?>;
        const subjectSelect = document.getElementById('subject_id');
        const sessionSelect = document.getElementById('session_id');

        // Initial Logic for Edit Mode
        if (editMode && initialConfig && initialConfig.subject_id) {
            // Wait for DOM to be ready implicitly or just force populate
            setTimeout(() => {
                populateSessions(initialConfig.subject_id, initialConfig.session_id);
                fetchSubjectDetails(); // Now fetch details
            }, 0);
        }

        subjectSelect.addEventListener('change', function() {
            populateSessions(this.value);
            fetchSubjectDetails();
        });

        function populateSessions(subjectId, selectedSessionId = null) {
            sessionSelect.innerHTML = '<option value="">-- Choose --</option>';
            if (!subjectId) return;

            const selectedOption = subjectSelect.querySelector(`option[value="${subjectId}"]`);
            if (selectedOption) {
                const courseId = selectedOption.getAttribute('data-course');
                if (courseId && allSessions[courseId]) {
                    allSessions[courseId].forEach(sess => {
                        const opt = document.createElement('option');
                        opt.value = sess.id;
                        opt.textContent = sess.session_name;
                        if (selectedSessionId && sess.id == selectedSessionId) {
                            opt.selected = true;
                        }
                        sessionSelect.appendChild(opt);
                    });
                }
            }
        }

        function fetchSubjectDetails() {
            const select = document.getElementById('subject_id');
            const selectedOption = select.options[select.selectedIndex];
            const configCard = document.getElementById('configCard');
            const subjectInfo = document.getElementById('subjectInfo');

            if (select.value) {
                theoryMarks = parseInt(selectedOption.getAttribute('data-theory'));
                document.getElementById('theoryMarks').innerText = theoryMarks;
                document.getElementById('assignMarks').innerText = selectedOption.getAttribute('data-assign');
                
                subjectInfo.style.display = 'block';
                configCard.style.display = 'block';
            } else {
                subjectInfo.style.display = 'none';
                configCard.style.display = 'none';
                document.getElementById('builderSection').style.display = 'none';
            }
        }

        function validateConfig() {
            const totalQ = parseInt(document.getElementById('totalQuestions').value) || 0;
            const marksPerQ = parseInt(document.getElementById('marksPerQuestion').value) || 0;
            const btn = document.getElementById('startBtn');
            const msg = document.getElementById('validationMsg');

            if (totalQ > 0 && marksPerQ > 0) {
                const calcTotal = totalQ * marksPerQ;
                if (calcTotal === theoryMarks) {
                    msg.style.color = '#22c55e';
                    msg.innerText = `Perfect! ${totalQ} questions x ${marksPerQ} marks = ${calcTotal} (Matches Theory Marks)`;
                    btn.disabled = false;
                    maxQuestions = totalQ;
                } else {
                    msg.style.color = '#ef4444';
                    msg.innerText = `Mismatch: ${totalQ} x ${marksPerQ} = ${calcTotal}. Must equal ${theoryMarks} marks.`;
                    btn.disabled = true;
                }
            } else {
                btn.disabled = true;
                msg.innerText = '';
            }
        }

        function startBuilding() {
            document.getElementById('builderSection').style.display = 'block';
            // Disable config editing
            document.getElementById('totalQuestions').readOnly = true;
            document.getElementById('marksPerQuestion').readOnly = true;
            document.getElementById('startBtn').style.display = 'none';

            // Add first question automatically IF not edit mode (handled in DOMContentLoaded)
            if(questionCount === 0 && !editMode) addQuestion();
        }

        function addQuestion(data = null) {
            if (questionCount >= maxQuestions) {
                alert(`You have reached the limit of ${maxQuestions} questions.`);
                return;
            }
            questionCount++;
            
            const container = document.getElementById('questionsContainer');
            const div = document.createElement('div');
            div.className = 'question-card';
            
            // Default empty values
            let text = '', a='', b='', c='', d='', correct='';
            if(data) {
                text = data.question_text || '';
                a = data.option_a || '';
                b = data.option_b || '';
                c = data.option_c || '';
                d = data.option_d || '';
                correct = data.correct_option || '';
            }

            div.innerHTML = `
                <div style="font-weight:700; margin-bottom:10px;">Question ${questionCount}</div>
               
                <div class="form-group">
                    <textarea name="questions[${questionCount}][text]" class="form-textarea" rows="2" placeholder="Enter Question Text" required>${text}</textarea>
                </div>
                
                <div class="q-opt-grid">
                    <div class="opt-row">
                        <input type="radio" name="questions[${questionCount}][correct]" value="A" required ${correct === 'A' ? 'checked' : ''}>
                        <input type="text" name="questions[${questionCount}][a]" class="form-input" placeholder="Option A" value="${a}" required>
                    </div>
                    <div class="opt-row">
                        <input type="radio" name="questions[${questionCount}][correct]" value="B" ${correct === 'B' ? 'checked' : ''}>
                        <input type="text" name="questions[${questionCount}][b]" class="form-input" placeholder="Option B" value="${b}" required>
                    </div>
                    <div class="opt-row">
                        <input type="radio" name="questions[${questionCount}][correct]" value="C" ${correct === 'C' ? 'checked' : ''}>
                        <input type="text" name="questions[${questionCount}][c]" class="form-input" placeholder="Option C" value="${c}" required>
                    </div>
                    <div class="opt-row">
                        <input type="radio" name="questions[${questionCount}][correct]" value="D" ${correct === 'D' ? 'checked' : ''}>
                        <input type="text" name="questions[${questionCount}][d]" class="form-input" placeholder="Option D" value="${d}" required>
                    </div>
                </div>
            `;
            container.appendChild(div);
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>
