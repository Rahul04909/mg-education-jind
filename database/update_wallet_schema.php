<?php
require_once __DIR__ . '/db-config.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$conn = getDbConnection();

// Check if centers table exists first to avoid fatal error
$check_table = $conn->query("SHOW TABLES LIKE 'centers'");
if ($check_table && $check_table->num_rows > 0) {
    // Add wallet_balance to centers if not exists
    $columns = $conn->query("SHOW COLUMNS FROM centers LIKE 'wallet_balance'");
    if ($columns && $columns->num_rows == 0) {
        $conn->query("ALTER TABLE centers ADD COLUMN wallet_balance DECIMAL(10,2) DEFAULT 0.00 AFTER royalty_percentage");
    }
} else {
    // Table centers does not exist, so we can't add column
    // This might be the cause if DB is empty
    error_log("Table 'centers' not found in database.");
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
