<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$hosts = ['localhost', '127.0.0.1'];
$users = ['root', 'jhdindus_mg_skill'];
$passes = ['', 'Rd14072003@./'];
$db = 'jhdindus_mg_skill';

foreach ($hosts as $h) {
    foreach ($users as $u) {
        foreach ($passes as $p) {
            echo "Testing $u @ $h ... ";
            $conn = @new mysqli($h, $u, $p, $db);
            if ($conn->connect_error) {
                echo "Failed: " . $conn->connect_error . "\n";
            } else {
                echo "SUCCESS!\n";
                echo "Host: $h, User: $u, Pass: " . ($p === '' ? '(empty)' : 'provided') . "\n";
                exit;
            }
        }
    }
}
?>
