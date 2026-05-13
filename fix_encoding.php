<?php
require_once __DIR__ . '/database/db-config.php';
$conn = getDbConnection();

// Ensure connection is UTF-8
$conn->set_charset("utf8mb4");

$sql = "SELECT id, title, description, meta_title, meta_desc FROM blogs";
$result = $conn->query($sql);

if ($result) {
    while($row = $result->fetch_assoc()) {
        $id = $row['id'];
        echo "Checking blog ID $id: " . htmlspecialchars(substr($row['title'], 0, 50)) . "...<br>";
        
        $fix = function($str) {
            if (empty($str)) return $str;
            
            // Try to convert if it looks like Mojibake
            // Common patterns: à¤ (Devanagari), Ã (Common UTF-8 start)
            if (preg_match('/[\xc3\xc2]/', $str) || strpos($str, 'à¤') !== false) {
                $original = $str;
                $fixed = @mb_convert_encoding($str, 'latin1', 'utf-8');
                
                if ($fixed && $fixed !== $original && mb_check_encoding($fixed, 'UTF-8')) {
                    return $fixed;
                }
            }
            return $str;
        };

        $new_title = $fix($row['title']);
        $new_desc = $fix($row['description']);
        $new_mtitle = $fix($row['meta_title']);
        $new_mdesc = $fix($row['meta_desc']);
        
        if ($new_title !== $row['title'] || $new_desc !== $row['description']) {
            $stmt = $conn->prepare("UPDATE blogs SET title = ?, description = ?, meta_title = ?, meta_desc = ? WHERE id = ?");
            $stmt->bind_param("ssssi", $new_title, $new_desc, $new_mtitle, $new_mdesc, $id);
            if ($stmt->execute()) {
                echo "Successfully FIXED blog ID $id<br>";
            } else {
                echo "Error updating blog ID $id: " . $conn->error . "<br>";
            }
            $stmt->close();
        } else {
            echo "No changes needed for blog ID $id<br>";
        }
    }
} else {
    echo "Query failed: " . $conn->error . "<br>";
}
echo "Done.\n";
?>
