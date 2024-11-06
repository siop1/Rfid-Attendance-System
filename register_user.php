<?php
// register_user.php
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "rfid_system";

$uid = $_POST['uid'];
$name = $_POST['name'];
$email = $_POST['email'];
$entry_time = $_POST['entry_time'];  // Time user can enter (HH:MM:SS)
$exit_time = $_POST['exit_time'];    // Time user can exit (HH:MM:SS)

// Create connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

if (!$conn) {
    echo json_encode(["error" => "Database connection failed"]);
    exit();
}

$sql = "INSERT INTO registered_user (uid, name, email, entry_time, exit_time) 
        VALUES ('$uid', '$name', '$email', '$entry_time', '$exit_time')";

if (mysqli_query($conn, $sql)) {
    echo json_encode(["success" => "User registered successfully"]);
} else {
    echo json_encode(["error" => "Failed to register user"]);
}

mysqli_close($conn);
?>
