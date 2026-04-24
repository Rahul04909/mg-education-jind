<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../database/db-config.php';

$conn = getDbConnection();

// Filtration Logic (Same as student-list.php)
$where_clauses = ["1=1"];
$params = [];
$types = "";

if (isset($_GET['internship_id']) && !empty($_GET['internship_id'])) {
    $where_clauses[] = "e.internship_id = ?";
    $params[] = intval($_GET['internship_id']);
    $types .= "i";
}

$search_term = '';
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_term = '%' . $_GET['search'] . '%';
    $where_clauses[] = "(e.full_name LIKE ? OR e.enrollment_no LIKE ? OR e.mobile LIKE ? OR e.email LIKE ?)";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $types .= "ssss";
}

$where_sql = implode(" AND ", $where_clauses);

// Fetch ALL students matching filters (no limit for PDF)
$sql = "SELECT e.*, i.title as internship_title 
        FROM internship_enrollments e 
        LEFT JOIN internships i ON e.internship_id = i.id 
        WHERE $where_sql 
        ORDER BY e.created_at DESC";

$stmt = $conn->prepare($sql);
if ($params) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

// Prepare HTML for mPDF
$html = '
<html>
<head>
    <style>
        body { font-family: "dejavusanscondensed", sans-serif; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #4f46e5; padding-bottom: 15px; margin-bottom: 20px; }
        .logo { width: 120px; margin-bottom: 10px; }
        .title { font-size: 20px; font-weight: bold; color: #4f46e5; text-transform: uppercase; }
        .subtitle { font-size: 14px; color: #666; margin-top: 5px; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background-color: #f3f4f6; color: #374151; padding: 10px; text-align: left; border: 1px solid #e5e7eb; font-size: 11px; text-transform: uppercase; }
        td { padding: 8px 10px; border: 1px solid #e5e7eb; font-size: 11px; vertical-align: middle; }
        tr:nth-child(even) { background-color: #fafafa; }
        
        .footer { position: fixed; bottom: 0; width: 100%; text-align: right; font-size: 10px; color: #999; border-top: 1px solid #eee; padding-top: 5px; }
        .badge { background-color: #eef2ff; color: #4f46e5; padding: 2px 6px; border-radius: 4px; font-weight: bold; }
        .enroll-no { font-family: monospace; font-weight: bold; color: #4338ca; }
    </style>
</head>
<body>
    <div class="header">
        <img src="../../assets/images/sidebar-logo.jpg" class="logo">
        <div class="title">Internship Student List</div>
        <div class="subtitle">Generated on: ' . date('d M, Y h:i A') . '</div>
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%">Sr.</th>
                <th width="15%">Enrollment No</th>
                <th width="25%">Student Name</th>
                <th width="25%">Internship</th>
                <th width="15%">Mobile</th>
                <th width="15%">Reg. Date</th>
            </tr>
        </thead>
        <tbody>';

if ($result->num_rows > 0) {
    $sr = 1;
    while($row = $result->fetch_assoc()) {
        $html .= '
            <tr>
                <td align="center">' . $sr++ . '</td>
                <td><span class="enroll-no">' . htmlspecialchars($row['enrollment_no']) . '</span></td>
                <td>
                    <b>' . htmlspecialchars($row['full_name']) . '</b><br>
                    <span style="font-size:9px; color:#666">' . htmlspecialchars($row['email']) . '</span>
                </td>
                <td>' . htmlspecialchars($row['internship_title'] ?? 'N/A') . '</td>
                <td>' . htmlspecialchars($row['mobile']) . '</td>
                <td>' . date('d/m/Y', strtotime($row['created_at'])) . '</td>
            </tr>';
    }
} else {
    $html .= '<tr><td colspan="6" align="center">No students found matching the criteria.</td></tr>';
}

$html .= '
        </tbody>
    </table>

    <div class="footer">
        Page {PAGENO} of {nbpg} | MG Skills Admin Panel
    </div>
</body>
</html>';

// mPDF Configuration
$mpdf = new \Mpdf\Mpdf([
    'mode' => 'utf-8',
    'format' => 'A4',
    'margin_left' => 10,
    'margin_right' => 10,
    'margin_top' => 10,
    'margin_bottom' => 15,
    'margin_header' => 5,
    'margin_footer' => 5,
]);

$mpdf->SetTitle('Internship Student List - ' . date('Ymd'));
$mpdf->WriteHTML($html);
$mpdf->Output('Internship_Students_' . date('Ymd_His') . '.pdf', 'D');

$conn->close();
?>
