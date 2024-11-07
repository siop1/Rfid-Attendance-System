<?php
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "rfid_system";

// Get the UID from the URL
$uid = $_GET['uid'];

// Connect to the database
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    echo json_encode(["status" => "error", "message" => "Database connection failed"]);
    exit();
}

// Check if the user with the provided uid exists
$sql = "SELECT * FROM registered_user WHERE uid = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $uid);  // Use "s" because uid is a string (HEX value)
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Check if there's an attendance record for this uid today
    $sql = "SELECT * FROM attendance_record WHERE uid = ? AND DATE(timestamp) = CURDATE()";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $uid);  // Use "s" because uid is a string (HEX value)
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo json_encode(["status" => "already_attended"]);
    } else {
        echo json_encode(["status" => "not_attended"]);
    }
} else {
    echo json_encode(["status" => "not_found"]);
}

$stmt->close();
$conn->close();
?>
