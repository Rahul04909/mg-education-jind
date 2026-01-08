<?php
include '../../includes/header.php';
require_once __DIR__ . '/../../database/db-config.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$conn = getDbConnection();

$sql = "SELECT * FROM donations WHERE id = $id AND status = 'success'";
$res = $conn->query($sql);

if ($res->num_rows == 0) {
    echo "<div class='container mt-5'><div class='alert alert-danger'>Donation not found or processed.</div></div>";
    include '../../includes/footer.php';
    exit;
}

$data = $res->fetch_assoc();
?>

<div class="page-wrapper">
    <div class="content">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-lg border-0 rounded-lg mt-5 mb-5 text-center">
                    <div class="card-body p-5">
                        <div class="mb-4">
                            <i class="fas fa-check-circle text-success" style="font-size: 80px;"></i>
                        </div>
                        <h2 class="mb-3">Thank You for Your Donation!</h2>
                        <p class="lead">Dear <strong><?php echo htmlspecialchars($data['full_name']); ?></strong>, your generosity helps us make a difference.</p>
                        
                        <div class="alert alert-success d-inline-block px-5 py-3 mt-3">
                            <h4 class="mb-0">Amount donated: ₹<?php echo number_format($data['amount'], 2); ?></h4>
                        </div>

                        <div class="mt-4">
                            <p><strong>Transaction ID:</strong> <?php echo htmlspecialchars($data['payment_id']); ?></p>
                            <p><strong>Receipt No:</strong> <?php echo htmlspecialchars($data['receipt_no']); ?></p>
                        </div>

                        <div class="mt-5">
                            <a href="download-receipt.php?id=<?php echo $id; ?>" class="btn btn-primary btn-lg px-5">
                                <i class="fas fa-download me-2"></i> Download Donation Receipt
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
include '../../includes/footer.php';
$conn->close();
?>
