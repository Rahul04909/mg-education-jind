<?php
require_once __DIR__ . '/db-config.php';

$conn = getDbConnection();

// Add wallet_balance to centers if not exists
// Also check for royalty_percentage just in case, though it was in add-center.php
$columns = $conn->query("SHOW COLUMNS FROM centers LIKE 'wallet_balance'");
if ($columns->num_rows == 0) {
    $conn->query("ALTER TABLE centers ADD COLUMN wallet_balance DECIMAL(10,2) DEFAULT 0.00 AFTER royalty_percentage");
}

// Create wallet_transactions table
$sql_transactions = "CREATE TABLE IF NOT EXISTS wallet_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    center_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL COMMENT 'Amount Paid via Razorpay',
    credit_amount DECIMAL(10,2) NOT NULL COMMENT 'Amount Added to Wallet',
    payment_id VARCHAR(255) NOT NULL,
    status ENUM('pending', 'success', 'failed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (center_id) REFERENCES centers(id) ON DELETE CASCADE
)";

if ($conn->query($sql_transactions) === TRUE) {
    // success
} else {
    error_log("Error creating table wallet_transactions: " . $conn->error);
}
?>
