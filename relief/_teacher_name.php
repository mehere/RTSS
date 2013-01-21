<?php
header("Expires: 0");
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");

require_once '../controller-head.php';

require_once '../class/Teacher.php';

$type=$_GET['type'];
header('Content-type: application/json');
echo json_encode(Teacher::getTeahcerName($type));
?>
