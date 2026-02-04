<?php
session_start();
require_once __DIR__ . '/../database/db-config.php';

$conn = getDbConnection();

// Fetch Visitors
$sql = "SELECT * FROM visitors ORDER BY check_in_time DESC";
$result = $conn->query($sql);
$visitors = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $visitors[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visitor Management - Reception Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
         :root {
            --primary: #ec4899;
            --bg-body: #fdf2f8; 
            --text-main: #1e293b;
            --text-light: #64748b;
            --sidebar-w: 260px;
            --card-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.1);
        }
        
        * { box-sizing: border-box; }
        body { margin: 0; font-family: 'Outfit', sans-serif; background: var(--bg-body); color: var(--text-main); }
        .main-content { margin-left: var(--sidebar-w); padding: 30px; transition: all 0.3s; min-height: 100vh; }
        
        .page-header { margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center; }
        .page-title { font-size: 24px; font-weight: 700; color: #831843; margin-bottom: 5px; }
        .page-subtitle { color: var(--text-light); font-size: 14px; }
        
        .btn-primary {
            background: var(--primary);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
        }
        .btn-primary:hover { background: #db2777; }

        .data-card {
            background: white;
            border-radius: 16px;
            box-shadow: var(--card-shadow);
            overflow: hidden;
        }

        .visitor-table {
            width: 100%;
            border-collapse: collapse;
        }
        .visitor-table th {
            text-align: left;
            padding: 16px 24px;
            background: #fce7f3;
            color: #be185d;
            font-weight: 600;
            font-size: 13px;
            text-transform: uppercase;
        }
        .visitor-table td {
            padding: 16px 24px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }
        .visitor-table tr:hover { background: #fff1f2; }
        
        .badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }
        .badge-active { background: #dcfce7; color: #15803d; }
        .badge-checked_out { background: #f1f5f9; color: #64748b; }

        .btn-action {
            background: white;
            border: 1px solid #e2e8f0;
            padding: 6px 12px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 500;
            color: var(--text-main);
            transition: all 0.2s;
        }
        .btn-action:hover { border-color: var(--primary); color: var(--primary); }
        .btn-checkout { color: #be185d; border-color: #fbcfe8; background: #fdf2f8; }
        .btn-checkout:hover { background: #fce7f3; }

        /* Modal */
        .modal-overlay {
            position: fixed; top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.5);
            display: none; justify-content: center; align-items: center; z-index: 1000;
        }
        .modal {
            background: white;
            width: 100%; max-width: 500px;
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
        }
        .modal-header { display: flex; justify-content: space-between; margin-bottom: 20px; }
        .modal-title { font-size: 18px; font-weight: 700; color: #831843; }
        .close-modal { cursor: pointer; font-size: 24px; line-height: 1; color: #9ca3af; }
        
        .form-group { margin-bottom: 16px; }
        .form-label { display: block; font-size: 14px; font-weight: 600; margin-bottom: 6px; color: #374151; }
        .form-control {
            width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #cbd5e1;
            font-family: inherit; font-size: 14px;
        }
        .btn-save {
            width: 100%; padding: 12px; background: var(--primary); color: white;
            border: none; border-radius: 8px; font-weight: 600; cursor: pointer;
        }
        .btn-save:hover { background: #db2777; }

        @media (max-width: 768px) {
            .main-content { margin-left: 0; padding: 20px; }
            .visitor-table { display: block; overflow-x: auto; }
        }
    </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<main class="main-content">
    <div class="page-header">
        <div>
            <h1 class="page-title">Visitor Management</h1>
            <div class="page-subtitle">Track and manage daily visitors</div>
        </div>
        <button onclick="openAddModal()" class="btn-primary">
            <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Entry Visitor
        </button>
    </div>

    <div class="data-card">
        <?php if (count($visitors) > 0): ?>
        <table class="visitor-table">
            <thead>
                <tr>
                    <th>Visitor Details</th>
                    <th>Purpose</th>
                    <th>Meeting With</th>
                    <th>Check In</th>
                    <th>Check Out</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($visitors as $row): ?>
                <tr id="row-<?php echo $row['id']; ?>">
                    <td>
                        <div style="font-weight:600;"><?php echo htmlspecialchars($row['name']); ?></div>
                        <div style="font-size:13px; color:var(--text-light);"><?php echo htmlspecialchars($row['phone']); ?></div>
                    </td>
                    <td style="font-size:14px;"><?php echo htmlspecialchars($row['purpose']); ?></td>
                    <td style="font-size:14px;"><?php echo htmlspecialchars($row['meet_whom'] ?? '-'); ?></td>
                    <td style="font-size:13px; color:var(--text-light);">
                        <?php echo date('h:i A', strtotime($row['check_in_time'])); ?><br>
                        <span style="font-size:11px"><?php echo date('M d', strtotime($row['check_in_time'])); ?></span>
                    </td>
                    <td style="font-size:13px; color:var(--text-light);" id="checkout-time-<?php echo $row['id']; ?>">
                        <?php 
                        if($row['check_out_time']) {
                            echo date('h:i A', strtotime($row['check_out_time'])) . '<br><span style="font-size:11px">' . date('M d', strtotime($row['check_out_time'])) . '</span>';
                        } else {
                            echo '-';
                        }
                        ?>
                    </td>
                    <td>
                        <span class="badge badge-<?php echo $row['status']; ?>" id="status-badge-<?php echo $row['id']; ?>">
                            <?php echo $row['status'] == 'active' ? 'Active' : 'Checked Out'; ?>
                        </span>
                    </td>
                    <td>
                        <?php if($row['status'] == 'active'): ?>
                        <button class="btn-action btn-checkout" id="btn-checkout-<?php echo $row['id']; ?>" onclick="checkoutVisitor(<?php echo $row['id']; ?>)">
                            Check Out
                        </button>
                        <?php else: ?>
                        <span style="color:var(--text-light); font-size:12px;">Completed</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php else: ?>
            <div style="padding:40px; text-align:center; color:var(--text-light);">
                <h3>No visitors recorded today</h3>
                <p>Click "Entry Visitor" to add a new visitor.</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Add Visitor Modal -->
    <div id="addModal" class="modal-overlay">
        <div class="modal">
            <div class="modal-header">
                <div class="modal-title">New Visitor Entry</div>
                <div class="close-modal" onclick="closeAddModal()">&times;</div>
            </div>
            <form id="addVisitorForm">
                <div class="form-group">
                    <label class="form-label">Visitor Name</label>
                    <input type="text" name="name" class="form-control" required placeholder="Full Name">
                </div>
                <div class="form-group">
                    <label class="form-label">Phone Number</label>
                    <input type="tel" name="phone" class="form-control" required placeholder="Mobile Number">
                </div>
                <div class="form-group">
                    <label class="form-label">Purpose of Visit</label>
                    <input type="text" name="purpose" class="form-control" required placeholder="e.g. Enquiry, Meeting, Delivery">
                </div>
                <div class="form-group">
                    <label class="form-label">Meeting Whom</label>
                    <input type="text" name="meet_whom" class="form-control" placeholder="Staff Name (Optional)">
                </div>

                <div class="form-group">
                    <label class="form-label">Email (Optional)</label>
                    <input type="email" name="email" class="form-control" placeholder="Email Address">
                </div>

                <button type="submit" class="btn-save" id="saveBtn">Make Entry</button>
            </form>
        </div>
    </div>

</main>

<script>
    const addModal = document.getElementById('addModal');
    
    function openAddModal() {
        addModal.style.display = 'flex';
    }

    function closeAddModal() {
        addModal.style.display = 'none';
        document.getElementById('addVisitorForm').reset();
    }

    addModal.addEventListener('click', (e) => {
        if (e.target === addModal) closeAddModal();
    });

    // Add Visitor
    document.getElementById('addVisitorForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const btn = document.getElementById('saveBtn');
        const originalText = btn.innerText;
        btn.innerText = 'Saving...';
        btn.disabled = true;

        const formData = new FormData(this);

        fetch('../actions/add_visitor.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(result => {
            if(result.success) {
                alert('Visitor entry created successfully!');
                location.reload();
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

    // Checkout Visitor
    function checkoutVisitor(id) {
        if(!confirm('Are you sure you want to mark this visitor as checked out?')) return;

        const btn = document.getElementById('btn-checkout-' + id);
        btn.disabled = true;
        btn.innerText = '...';

        fetch('../actions/update_visitor_status.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id, status: 'checked_out' })
        })
        .then(response => response.json())
        .then(result => {
            if(result.success) {
                // Update UI
                document.getElementById('status-badge-' + id).className = 'badge badge-checked_out';
                document.getElementById('status-badge-' + id).innerText = 'Checked Out';
                
                // Set checkout time
                const now = new Date();
                const timeStr = now.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                const dateStr = now.toLocaleDateString([], {month: 'short', day: 'numeric'});
                document.getElementById('checkout-time-' + id).innerHTML = `${timeStr}<br><span style="font-size:11px">${dateStr}</span>`;
                
                // Remove button
                btn.parentNode.innerHTML = '<span style="color:var(--text-light); font-size:12px;">Completed</span>';
            } else {
                alert('Error: ' + result.message);
                btn.disabled = false;
                btn.innerText = 'Check Out';
            }
        })
        .catch(err => {
            alert('Network error');
            btn.disabled = false;
            btn.innerText = 'Check Out';
        });
    }
</script>

</body>
</html>
<?php $conn->close(); ?>
