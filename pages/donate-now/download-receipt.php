<?php
require_once __DIR__ . '/../../database/db-config.php';
require_once __DIR__ . '/../../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$id = isset($_GET['id']) ? intval($_GET['id']) : 0; // Donation ID

$conn = getDbConnection();
$sql = "SELECT * FROM donations WHERE id = $id AND status = 'success'";
$res = $conn->query($sql);

if ($res->num_rows == 0) {
    die("Receipt not found.");
}

$data = $res->fetch_assoc();

// Image Helper
function get_image_base64($path) {
    if (!file_exists($path)) {
        return '';
    }
    $type = pathinfo($path, PATHINFO_EXTENSION);
    $img_content = file_get_contents($path);
    if ($img_content === false) return '';
    return 'data:image/' . $type . ';base64,' . base64_encode($img_content);
}

$base_dir = __DIR__;
$sign_image_path = $base_dir . '/mg-sign.png';
$sign_src = get_image_base64($sign_image_path);

// Logo (Assuming logo exists in assets/img/logo.png, checking relative path)
// Adjust path as needed based on project structure. Usually ../../assets/img/logo.png
$logo_path = __DIR__ . '/../../assets/img/logo.png';
$logo_src = get_image_base64($logo_path);

// Number to Words
$f = new NumberFormatter("en", NumberFormatter::SPELLOUT);
$amount_words = ucwords($f->format($data['amount']));

$html = '
<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: "Times New Roman", serif; margin: 0; padding: 20px; color: #333; }
        .container { border: 2px solid #ccc; padding: 30px; position: relative; }
        .header { text-align: center; border-bottom: 2px solid #eee; padding-bottom: 20px; margin-bottom: 30px; }
        .header h1 { margin: 0; color: #dc2626; text-transform: uppercase; font-size: 28px; }
        .header p { margin: 5px 0; font-size: 14px; color: #666; }
        
        .receipt-title { text-align: center; font-size: 20px; font-weight: bold; margin-bottom: 30px; text-decoration: underline; background: #f9f9f9; padding: 10px; display: inline-block; margin-left: auto; margin-right: auto; }
        
        .info-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .info-table td { padding: 8px 10px; font-size: 16px; }
        .label { font-weight: bold; width: 150px; color: #555; }
        
        .amount-box { background: #f0fdf4; border: 1px solid #bbf7d0; padding: 15px; text-align: center; font-size: 20px; font-weight: bold; color: #15803d; margin: 30px 0; }
        
        .footer { margin-top: 50px; text-align: right; }
        .sign-box { display: inline-block; text-align: center; }
        .sign-box img { height: 60px; display: block; margin: 0 auto; }
        .sign-label { border-top: 1px solid #333; margin-top: 5px; padding-top: 5px; font-weight: bold; font-size: 12px; }
        
        .note { font-size: 12px; color: #888; margin-top: 40px; text-align: center; border-top: 1px solid #eee; padding-top: 10px; }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>MG Education & Social Development Organisation</h1>
        <p>H.N. 2102, Urban Estate, Jind, Haryana (126102), India</p>
        <p>Email: info@mgedu.in | Phone: +91 9813354588</p>
    </div>

    <div style="text-align:center;">
        <div class="receipt-title">DONATION RECEIPT</div>
    </div>

    <table width="100%">
        <tr>
            <td align="left"><strong>Receipt No:</strong> ' . htmlspecialchars($data['receipt_no']) . '</td>
            <td align="right"><strong>Date:</strong> ' . date("d-m-Y h:i A", strtotime($data['created_at'])) . '</td>
        </tr>
    </table>

    <hr style="border: 0; border-top: 1px solid #eee; margin: 20px 0;">

    <table class="info-table">
        <tr>
            <td class="label">Received From:</td>
            <td>' . htmlspecialchars($data['full_name']) . '</td>
        </tr>
        <tr>
            <td class="label">Mobile No:</td>
            <td>' . htmlspecialchars($data['mobile']) . '</td>
        </tr>
        <tr>
            <td class="label">Email:</td>
            <td>' . htmlspecialchars($data['email']) . '</td>
        </tr>
        <tr>
            <td class="label">PAN Number:</td>
            <td>' . ($data['pan_card'] ? htmlspecialchars($data['pan_card']) : 'N/A') . '</td>
        </tr>
        <tr>
            <td class="label">Address:</td>
            <td>' . ($data['address'] ? htmlspecialchars($data['address']) : 'N/A') . '</td>
        </tr>
        <tr>
            <td class="label">Purpose:</td>
            <td>' . htmlspecialchars($data['purpose']) . '</td>
        </tr>
        <tr>
            <td class="label">Transaction ID:</td>
            <td>' . htmlspecialchars($data['payment_id']) . '</td>
        </tr>
    </table>

    <div class="amount-box">
        Amount Received: Rs. ' . number_format($data['amount'], 2) . '<br>
        <span style="font-size: 14px; font-weight: normal; color: #333;">(' . $amount_words . ' Rupees Only)</span>
    </div>

    <div class="footer">
        <div class="sign-box">
            ' . ($sign_src ? '<img src="' . $sign_src . '">' : '') . '
            <div class="sign-label">AUTHORIZED SIGNATORY</div>
        </div>
    </div>

    <div class="note">
        This is a computer-generated receipt and requires signature for 80G purposes if applicable. <br>
        Thank you for your generous support!
    </div>
</div>

</body>
</html>
';

$options = new Options();
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("Donation_Receipt_" . $data['receipt_no'] . ".pdf", ["Attachment" => 1]);

$conn->close();
?>
