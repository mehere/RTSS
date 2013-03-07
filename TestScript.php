<?php
//sleep(10);
$options = getopt("p:h:");
$part = $options["p"];
$part2 = $options["h"];
error_log("p = $part");
error_log("h = $part2");
//echo ("$part");
echo "run";
?>
