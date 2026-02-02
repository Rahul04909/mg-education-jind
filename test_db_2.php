<?php
require_once 'database/db-config.php';
echo "DB_USER: " . DB_USER . "<br>";
echo "DB_NAME: " . DB_NAME . "<br>";
echo "Included file: " . realpath('database/db-config.php') . "<br>";

try {
    $conn = getDbConnection();
    echo "Connected successfully to " . DB_NAME;
} catch (Throwable $e) {
    echo "Error: " . $e->getMessage();
}
?>
