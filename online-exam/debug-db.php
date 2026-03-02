<?php
$path = 'd:/wamp/www/mg-skill/database/db-config.php';
echo "Checking path: $path\n";
if (file_exists($path)) {
    echo "File exists.\n";
    require $path;
    if (function_exists('getDbConnection')) {
        echo "Function getDbConnection exists.\n";
        $conn = getDbConnection();
        echo "Connected successfully!\n";
    } else {
        echo "Function getDbConnection DOES NOT exist.\n";
    }
} else {
    echo "File DOES NOT exist.\n";
}
?>
