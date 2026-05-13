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
        
        // Function to fix double-encoded text
        $fix = function($str) {
            if (empty($str)) return $str;
            // Detect if it's double-encoded (contains characteristic characters like à¤)
            if (strpos($str, 'à¤') !== false || strpos($str, 'Ã') !== false) {
                // Convert from UTF-8 back to Latin-1 bytes, which are the actual UTF-8 bytes
                $fixed = @mb_convert_encoding($str, 'latin1', 'utf-8');
                // Check if the result is valid UTF-8
                if (mb_check_encoding($fixed, 'utf-8')) {
                    return $fixed;
                }
            }
            return $str;
        };

        $new_title = $fix($row['title']);
        $new_desc = $fix($row['description']);
        $new_mtitle = $fix($row['meta_title']);
        $new_mdesc = $fix($row['meta_desc']);
        
        // Update if changed
        if ($new_title !== $row['title'] || $new_desc !== $row['description']) {
            $stmt = $conn->prepare("UPDATE blogs SET title = ?, description = ?, meta_title = ?, meta_desc = ? WHERE id = ?");
            $stmt->bind_param("ssssi", $new_title, $new_desc, $new_mtitle, $new_mdesc, $id);
            if ($stmt->execute()) {
                echo "Fixed blog ID $id\n";
            } else {
                echo "Error fixing blog ID $id: " . $stmt->error . "\n";
            }
            $stmt->close();
        }
    }
}
echo "Done.\n";
?>
