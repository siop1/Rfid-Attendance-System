<?php
// register_user.php
header('Content-Type: application/json');

// Database credentials
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "rfid_system";

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the data from the POST request
    $uid = $_POST['uid'];
    $name = $_POST['name'];
    $entry_time = $_POST['entry_time'];  // Time user can enter (HH:MM:SS)
    $exit_time = $_POST['exit_time'];    // Time user can exit (HH:MM:SS)
    $faculty = $_POST['faculty'];        // Faculty (only 'BICTE' in this case)
    $semester = $_POST['semester'];      // Semester (1 to 8)

    // Create connection
    $conn = mysqli_connect($servername, $username, $password, $dbname);

    if (!$conn) {
        echo json_encode(["error" => "Database connection failed"]);
        exit();
    }

    // Insert the user data into the registered_user table
    $sql = "INSERT INTO registered_user (uid, name, entry_time, exit_time, faculty, semester) 
            VALUES ('$uid', '$name', '$entry_time', '$exit_time', '$faculty', '$semester')";

    if (mysqli_query($conn, $sql)) {
        echo json_encode(["success" => "User registered successfully"]);
    } else {
        echo json_encode(["error" => "Failed to register user"]);
    }

    // Close the database connection
    mysqli_close($conn);
}
?>
