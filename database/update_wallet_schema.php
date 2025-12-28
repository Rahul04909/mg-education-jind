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
    // 1. Add wallet_balance to centers if not exists
    $columns = $conn->query("SHOW COLUMNS FROM centers LIKE 'wallet_balance'");
    if ($columns && $columns->num_rows == 0) {
        $conn->query("ALTER TABLE centers ADD COLUMN wallet_balance DECIMAL(10,2) DEFAULT 0.00 AFTER royalty_percentage");
    }

    // 2. Create wallet_transactions table
    // Removing FK constraint to prevent 'Failed to open referenced table' error on some server configs
    $sql_transactions = "CREATE TABLE IF NOT EXISTS wallet_transactions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        center_id INT NOT NULL,
        amount DECIMAL(10,2) NOT NULL COMMENT 'Amount Paid via Razorpay',
        credit_amount DECIMAL(10,2) NOT NULL COMMENT 'Amount Added to Wallet',
        payment_id VARCHAR(255) NOT NULL,
        status ENUM('pending', 'success', 'failed') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB";

    if ($conn->query($sql_transactions) === TRUE) {
        echo "";
    } else {
        echo "Error creating table wallet_transactions: " . $conn->error;
    }

} else {
    // Table centers does not exist
    echo "CRITICAL ERROR: Table 'centers' not found in database. Cannot create wallet transactions.";
    // Potentially try to create centers table here if we knew schema, but for now just stop.
}
?>
