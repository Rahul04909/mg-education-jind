<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    $path = 'd:/wamp/www/mg-skill/database/db-config.php';
    if (!file_exists($path)) {
        die("File $path not found.\n");
    }
    require $path;
    
    // Test the define values
    echo "Host: " . DB_HOST . "\n";
    echo "User: " . DB_USER . "\n";
    echo "DB: " . DB_NAME . "\n";
    
    // mysqli_report(MYSQLI_REPORT_ALL); // This can be noisy but helpful
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    echo "Connected successfully!\n";
    
    $res = $conn->query("SELECT COUNT(*) as cnt FROM admissions");
    if ($res) {
        $row = $res->fetch_assoc();
        echo "Admissions count: " . $row['cnt'] . "\n";
    }

} catch (Exception $e) {
    echo "Caught Error: " . $e->getMessage() . "\n";
} catch (Error $e) {
    echo "Caught Fatal: " . $e->getMessage() . "\n";
}
?>
