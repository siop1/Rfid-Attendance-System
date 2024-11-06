<?php
$servername = "localhost";  // Your database host
$username = "root";         // Database username
$password = "";             // Database password
$dbname = "attendance_db";  // Database name

// Create a connection to the database using MySQLi
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if 'uid' is received from the GET request
if (isset($_GET['uid'])) {
    $uid = trim($_GET['uid']);  // Trim any whitespace from the UID
    
    // Determine uname based on the value of uid
    if ($uid === "33:ba:9a:f5") {
        $uname = "Puspa Mahato";
    }elseif($uid === "b3:76:5b:dd") {
        $uname = "Bishnu Mahato";
    }

    else {
        $uname = "unknown user";
    }

    // Use a prepared statement to prevent SQL injection
    $stmt = $conn->prepare("INSERT INTO attendance_records (uid, uname, timestamp) VALUES (?, ?, NOW())");
    if ($stmt) {
        $stmt->bind_param("ss", $uid, $uname);  // Bind the UID and uname parameters as strings

        // Execute the query and check if successful
        if ($stmt->execute()) {
            echo "New record created successfully";
        } else {
            echo "Error: " . $stmt->error;
        }

        // Close the prepared statement
        $stmt->close();
    } else {
        echo "Error preparing statement: " . $conn->error;
    }
} else {
    echo "UID not received.";
}

// Close the connection
$conn->close();
?>
