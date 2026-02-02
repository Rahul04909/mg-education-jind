<?php
require_once 'database/db-config.php';
try {
    $conn = getDbConnection();
    echo "Connected successfully to " . DB_NAME;
} catch (Throwable $e) {
    echo "Error: " . $e->getMessage();
}
?>
