<?php
require_once __DIR__ . '/../../database/db-config.php';

if (isset($_GET['id'])) {
    $conn = getDbConnection();
    $id = intval($_GET['id']);
    
    // Optional: Delete related files (photo, aadhar, etc)
    // $sql_files = "SELECT aadhar_file, photo_file, resume_file, student_id_file FROM volunteers WHERE id = $id";
    // ... unlink files ...

    $sql = "DELETE FROM volunteers WHERE id = $id";
    
    if ($conn->query($sql) === TRUE) {
        header("Location: index.php?msg=deleted");
    } else {
        echo "Error deleting record: " . $conn->error;
    }
    
    $conn->close();
} else {
    header("Location: index.php");
}
?>
