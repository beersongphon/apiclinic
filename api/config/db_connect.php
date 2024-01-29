<?php
// Is this the production server or not?
define("PRODUCTION", $_SERVER["SERVER_NAME"] == "web.rmutp.ac.th");
// ตั้งค่าการเชื่อมต่อฐานข้อมูล
if (PRODUCTION) {
  // Production
  $servername = "localhost";
  $username = "luxurybyfon";
  $password = "mj95c2gx";
  $dbname = "luxurybyfon";
} else {
  // Development
  $servername = "localhost";
  $username = "root";
  $password = "";
  $dbname = "db_clinic";
}

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
// กำหนด charset ให้เป็น utf8 เพื่อรองรับภาษาไทย
mysqli_set_charset($conn, "UTF8");
?>