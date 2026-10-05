<?php
// Hamro Ecommerce database connection for XAMPP/MySQL.
mysqli_report(MYSQLI_REPORT_OFF);

$host = 'localhost';
$dbname = 'hamro_ecommerce';
$username = 'root';
$password = '';

$conn = @new mysqli($host, $username, $password, $dbname);

if ($conn->connect_errno) {
    $db_error = true;
} else {
    $db_error = false;
    $conn->set_charset('utf8mb4');
}
?>