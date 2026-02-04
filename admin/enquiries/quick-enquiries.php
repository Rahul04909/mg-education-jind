<?php
session_start();
require_once __DIR__ . '/../../database/db-config.php';

// Auth Check
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.php");
    exit;
}

$conn = getDbConnection();

// Fetch Enquiries
$sql = "SELECT * FROM quick_enquiries ORDER BY created_at DESC";
$result = $conn->query($sql);
$enquiries = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $enquiries[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quick Enquiries - Admin Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
         :root {
            --primary: #4f46e5; /* Admin Primary */
            --bg-body: #f8fafc; 
            --text-main: #1e293b;
            --text-light: #64748b;
            --sidebar-w: 260px;
            --card-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
        }
        
        * { box-sizing: border-box; }
        body { margin: 0; font-family: 'Outfit', sans-serif; background: var(--bg-body); color: var(--text-main); }
        .main-content { margin-left: var(--sidebar-w); padding: 30px; transition: all 0.3s; min-height: 100vh; }
        body.sidebar-collapsed .main-content { margin-left: 80px; }

        .page-header { margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center; }
        .page-title { font-size: 24px; font-weight: 700; color: var(--text-main); margin-bottom: 5px; }
        .page-subtitle { color: var(--text-light); font-size: 14px; }

        .data-card {
            background: white;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            border: 1px solid #e2e8f0;
            overflow: hidden;
        }

        .enquiry-table {
            width: 100%;
            border-collapse: collapse;
        }
        .enquiry-table th {
            text-align: left;
            padding: 16px 24px;
            background: #f8fafc;
            color: var(--text-light);
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
            border-bottom: 1px solid #e2e8f0;
        }
        .enquiry-table td {
            padding: 16px 24px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: top;
            font-size: 14px;
        }
        .enquiry-table tr:hover { background: #f8fafc; }
        
        .badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }
        .badge-pending { background: #fff7ed; color: #c2410c; border: 1px solid #ffedd5; }
        .badge-contacted { background: #f0fdf4; color: #15803d; border: 1px solid #dcfce7; }
        .badge-archived { background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0; }

        .btn-action {
            background: white;
            border: 1px solid #e2e8f0;
            padding: 6px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 500;
            color: var(--text-main);
            transition: all 0.2s;
        }
        .btn-action:hover { border-color: var(--primary); color: var(--primary); }

        /* Modal */
        .modal-overlay {
            position: fixed; top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.5);
            display: none; justify-content: center; align-items: center; z-index: 1000;
        }
        .modal {
            background: white;
            width: 100%; max-width: 500px;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }
        .modal-header { display: flex; justify-content: space-between; margin-bottom: 20px; align-items: center;}
        .modal-title { font-size: 18px; font-weight: 600; color: var(--text-main); }
        .close-modal { cursor: pointer; font-size: 24px; line-height: 1; color: #9ca3af; }
        
        .form-group { margin-bottom: 16px; }
        .form-label { display: block; font-size: 14px; font-weight: 500; margin-bottom: 6px; color: var(--text-main); }
        .form-control {
            width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #cbd5e1;
            font-family: inherit; font-size: 14px; outline: none;
        }
        .form-control:focus { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1); }
        .btn-save {
            width: 100%; padding: 12px; background: var(--primary); color: white;
            border: none; border-radius: 8px; font-weight: 500; cursor: pointer;
        }
        .btn-save:hover { background: #4338ca; }

        @media (max-width: 768px) {
            .main-content { margin-left: 0; padding: 20px; }
            .enquiry-table { display: block; overflow-x: auto; }
        }
    </style>
</head>
<body>

<?php include '../sidebar.php'; ?>

<main class="main-content">
    <div class="page-header">
        <div>
            <h1 class="page-title">Quick Enquiries</h1>
            <div class="page-subtitle">Manage online course enquiries and responses</div>
        </div>
        <button onclick="location.reload()" class="btn-action">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="vertical-align:bottom"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
            Refresh
        </button>
    </div>

    <div class="data-card">
        <?php if (count($enquiries) > 0): ?>
        <table class="enquiry-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Name / Mobile</th>
                    <th>Message</th>
                    <th>Source</th>
                    <th>Status</th>
                    <th>Response</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($enquiries as $row): ?>
                <tr id="row-<?php echo $row['id']; ?>">
                    <td style="color:var(--text-light); white-space:nowrap;">
                        <?php echo date('M d, Y', strtotime($row['created_at'])); ?><br>
                        <span style="font-size:12px;"><?php echo date('h:i A', strtotime($row['created_at'])); ?></span>
                    </td>
                    <td>
                        <div style="font-weight:600;"><?php echo htmlspecialchars($row['name']); ?></div>
                        <div style="font-size:13px; color:var(--primary);"><?php echo htmlspecialchars($row['phone']); ?></div>
                    </td>
                    <td style="max-width:300px;">
                        <?php echo nl2br(htmlspecialchars($row['message'])); ?>
                    </td>
                    <td style="color:var(--text-light);">
                        <span style="background:#f1f5f9; padding:2px 8px; border-radius:4px; font-size:12px;">
                            <?php echo htmlspecialchars($row['course_source']); ?>
                        </span>
                    </td>
                    <td>
                        <span class="badge badge-<?php echo $row['status']; ?>" id="status-badge-<?php echo $row['id']; ?>">
                            <?php echo ucfirst($row['status']); ?>
                        </span>
                    </td>
                    <td style="color:var(--text-light); font-style:italic;" id="response-text-<?php echo $row['id']; ?>">
                        <?php echo !empty($row['response_note']) ? htmlspecialchars($row['response_note']) : '-'; ?>
                    </td>
                    <td>
                        <button class="btn-action" onclick="openModal(<?php echo $row['id']; ?>, '<?php echo $row['status']; ?>', '<?php echo addslashes($row['response_note'] ?? ''); ?>')">
                            Manage
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
            <div style="padding:60px; text-align:center; color:var(--text-light);">
                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" style="margin-bottom:10px; color:#cbd5e1;"><circle cx="12" cy="12" r="10"></circle><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                <h3>No enquiries found</h3>
                <p>New website enquiries will appear here.</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Edit Modal -->
    <div id="actionModal" class="modal-overlay">
        <div class="modal">
            <div class="modal-header">
                <div class="modal-title">Update Enquiry</div>
                <div class="close-modal" onclick="closeModal()">&times;</div>
            </div>
            <form id="updateForm">
                <input type="hidden" name="id" id="enquiryId">
                
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status" id="enquiryStatus" class="form-control">
                        <option value="pending">Pending</option>
                        <option value="contacted">Contacted</option>
                        <option value="archived">Archived</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Response / Note</label>
                    <textarea name="response_note" id="enquiryNote" class="form-control" rows="4" placeholder="Enter details of conversation..."></textarea>
                </div>

                <button type="submit" class="btn-save" id="saveBtn">Save Update</button>
            </form>
        </div>
    </div>

</main>

<script>
    const modal = document.getElementById('actionModal');
    
    function openModal(id, status, note) {
        document.getElementById('enquiryId').value = id;
        document.getElementById('enquiryStatus').value = status;
        document.getElementById('enquiryNote').value = note;
        modal.style.display = 'flex';
    }

    function closeModal() {
        modal.style.display = 'none';
    }

    modal.addEventListener('click', (e) => {
        if (e.target === modal) closeModal();
    });

    document.getElementById('updateForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const btn = document.getElementById('saveBtn');
        const originalText = btn.innerText;
        btn.innerText = 'Saving...';
        btn.disabled = true;

        const formData = new FormData(this);
        const data = {};
        formData.forEach((value, key) => data[key] = value);

        fetch('../../actions/update-enquiry-status.php', {
            method: 'POST',
            body: JSON.stringify(data),
            headers: { 'Content-Type': 'application/json' }
        })
        .then(response => response.json())
        .then(result => {
            if(result.success) {
                // Update UI directly
                const id = data.id;
                const statusBadge = document.getElementById('status-badge-' + id);
                const responseText = document.getElementById('response-text-' + id);
                
                statusBadge.className = 'badge badge-' + data.status;
                statusBadge.innerText = data.status.charAt(0).toUpperCase() + data.status.slice(1);
                responseText.innerText = data.response_note;
                
                closeModal();
            } else {
                alert('Error: ' + result.message);
            }
        })
        .catch(err => alert('Network error'))
        .finally(() => {
            btn.innerText = originalText;
            btn.disabled = false;
        });
    });
</script>

</body>
</html>
<?php $conn->close(); ?>
