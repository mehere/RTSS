<?php
header("Expires: 0");
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");

require_once '../../controller-head.php';

$output=array('hasConflict'=>-1);

$output['error']=var_export($_GET, TRUE);

header('Content-type: application/json');
echo json_encode($output);
?>