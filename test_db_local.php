<?php
$conn = new mysqli('localhost', 'root', '', 'mg_skill');
if ($conn->connect_error) {
    echo "Connection failed: " . $conn->connect_error;
} else {
    echo "Connected successfully to mg_skill";
}
?>
