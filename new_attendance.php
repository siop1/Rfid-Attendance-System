<?php
// check_attendance.php
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "rfid_system";

$uid = $_GET['uid']; // Get UID from URL

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
    echo json_encode(["error" => "Database connection failed"]);
    exit();
}

// Get the user's entry and exit time
$sql = "SELECT entry_time, exit_time FROM registered_user WHERE uid = '$uid'";
$result = mysqli_query($conn, $sql);

if (mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    
    $entry_time = $row['entry_time'];
    $exit_time = $row['exit_time'];
    
    // Get current time in HH:MM:SS format
    $current_time = date('H:i:s');
    
    if ($current_time >= $entry_time && $current_time <= $exit_time) {
        echo json_encode(["success" => "Attendance allowed"]);
    } else {
        echo json_encode(["error" => "Attendance not allowed outside the specified time range"]);
    }
} else {
    echo json_encode(["error" => "User not found"]);
}

mysqli_close($conn);
?>
