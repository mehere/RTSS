<?php

if ($_FILES["timetableFile"]["error"] > 0)
  {
  echo "Error: " . $_FILES["timetableFile"]["error"] . "<br>";
  }
else
  {
  echo "Upload: " . $_FILES["timetableFile"]["name"] . "<br>";
  echo "Type: " . $_FILES["timetableFile"]["type"] . "<br>";
  echo "Size: " . ($_FILES["timetableFile"]["size"] / 1024) . " kB<br>";
  echo "Stored in: " . $_FILES["timetableFile"]["tmp_name"];
  }
?>