<?php
$c = @mysqli_connect('localhost', 'root', '', 'jhdindus_mg_skill');
if (!$c) {
    $c = @mysqli_connect('127.0.0.1', 'root', '', 'jhdindus_mg_skill');
}

if ($c) {
    $r = mysqli_query($c, "SELECT id FROM exam_schedules LIMIT 1");
    $e = mysqli_fetch_assoc($r);
    $r = mysqli_query($c, "SELECT id FROM admissions LIMIT 5");
    $s = [];
    while($row = @mysqli_fetch_assoc($r)) $s[] = $row['id'];
    echo json_encode(['e'=>($e['id'] ?? null),'s'=>$s]);
} else {
    echo json_encode(['error' => mysqli_connect_error()]);
}
?>
