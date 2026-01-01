<?php
require_once __DIR__ . '/../database/db-config.php';
$conn = getDbConnection();

echo "<h3>Timezone Debugger</h3>";
echo "PHP Time: " . date("Y-m-d H:i:s") . "<br>";
echo "PHP Timezone: " . date_default_timezone_get() . "<br>";

$res = $conn->query("SELECT NOW() as db_time, @@global.time_zone as global_tz, @@session.time_zone as session_tz");
$row = $res->fetch_assoc();

echo "DB Time (NOW()): " . $row['db_time'] . "<br>";
echo "DB Global TZ: " . $row['global_tz'] . "<br>";
echo "DB Session TZ: " . $row['session_tz'] . "<br>";

$php_ts = time();
$db_ts = strtotime($row['db_time']);
$diff = $php_ts - $db_ts;

echo "Difference (PHP - DB): " . $diff . " seconds<br>";

if (abs($diff) > 60) {
    echo "<b style='color:red'>CRITICAL TIMING MISMATCH DETECTED</b>";
} else {
    echo "<b style='color:green'>Time Sync OK</b>";
}
?>
