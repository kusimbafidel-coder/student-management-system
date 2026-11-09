<?php
mysqli_report(MYSQLI_REPORT_OFF);
$conn = new mysqli("localhost", "root", "", "student_management");

if ($conn->connect_error) {
    die("Database connection failed: " . htmlspecialchars($conn->connect_error));
}

if (!$conn->set_charset("utf8mb4")) {
    die("Could not set the database character set.");
}
?>
