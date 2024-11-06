<?php
// get_wifi_credentials.php
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "rfid_system";

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
    echo json_encode(["error" => "Database connection failed"]);
    exit();
}

$sql = "SELECT ssid, password FROM wifi ORDER BY id DESC LIMIT 1";
$result = mysqli_query($conn, $sql);

if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    echo json_encode(["ssid" => $row["ssid"], "password" => $row["password"]]);
} else {
    echo json_encode(["error" => "No Wi-Fi credentials found"]);
}

mysqli_close($conn);
?>
