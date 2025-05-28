<?php
$lat = $_GET['lat'];
$lng = $_GET['lng'];
file_put_contents("gps_log.txt", "Lat: $lat, Lng: $lng\n", FILE_APPEND);
echo "OK";
?>
