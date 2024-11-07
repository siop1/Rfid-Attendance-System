<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Homepage with Navbar</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        nav {
            background-color: #333;
            overflow: hidden;
        }
        nav a {
            float: left;
            display: block;
            color: #f2f2f2;
            padding: 14px 20px;
            text-align: center;
            text-decoration: none;
        }
        nav a:hover {
            background-color: #ddd;
            color: black;
        }
        .section {
            display: none; /* Hide all sections by default */
            padding: 20px;
        }
        .active {
            display: block; /* Show the active section */
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav>
        <a href="javascript:void(0);" onclick="showSection('home')">Home</a>
        <a href="javascript:void(0);" onclick="showSection('register')">Register</a>
        <a href="javascript:void(0);" onclick="showSection('attendance')">Attendance</a>
    </nav>

    <!-- Sections -->
    <div id="home" class="section active">
        <h2>Welcome to the Homepage</h2>
        <p>This is the homepage. Click on the navigation menu to go to the different sections.</p>
    </div>

    <!-- Register Section -->
    <div id="register" class="section">
        <h2>User Registration</h2>
        <form action="register_user.php" method="POST">
            <label for="uid">UID:</label><br>
            <input type="text" id="uid" name="uid" required><br><br>

            <label for="name">Name:</label><br>
            <input type="text" id="name" name="name" required><br><br>

            <label for="entry_time">Entry Time (HH:MM:SS):</label><br>
            <input type="time" id="entry_time" name="entry_time" required><br><br>

            <label for="exit_time">Exit Time (HH:MM:SS):</label><br>
            <input type="time" id="exit_time" name="exit_time" required><br><br>

            <label for="faculty">Faculty:</label><br>
            <select id="faculty" name="faculty" required>
                <option value="BICTE">BICTE</option>
            </select><br><br>

            <label for="semester">Semester (1-8):</label><br>
            <input type="number" id="semester" name="semester" min="1" max="8" required><br><br>

            <input type="submit" value="Register">
        </form>
    </div>

    <!-- Attendance Section -->
    <div id="attendance" class="section">
        <h2>Attendance Records</h2>
        <form action="" method="POST">
            <label for="faculty_select">Select Faculty:</label><br>
            <select id="faculty_select" name="faculty" required>
                <option value="BICTE">BICTE</option>
            </select><br><br>

            <label for="semester_select">Select Semester (1-8):</label><br>
            <input type="number" id="semester_select" name="semester" min="1" max="8" required><br><br>

            <input type="submit" value="Get Attendance">
        </form>

        <?php
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Connect to the database
            $servername = "localhost";
            $username = "root";
            $password = "";
            $dbname = "rfid_system";

            $faculty = $_POST['faculty'];
            $semester = $_POST['semester'];

            // Create connection
            $conn = mysqli_connect($servername, $username, $password, $dbname);
            if (!$conn) {
                die("Connection failed: " . mysqli_connect_error());
            }

            // Query to get the number of attendances for a particular faculty and semester
            $sql = "SELECT COUNT(*) AS total_attendance FROM attendance_record 
                    WHERE faculty = '$faculty' AND semester = $semester";
            $result = mysqli_query($conn, $sql);

            if ($result) {
                $row = mysqli_fetch_assoc($result);
                echo "<h3>Total Attendance for Faculty $faculty, Semester $semester: " . $row['total_attendance'] . "</h3>";
            } else {
                echo "<h3>No attendance records found.</h3>";
            }

            // Close connection
            mysqli_close($conn);
        }
        ?>
    </div>

    <script>
        function showSection(sectionId) {
            // Hide all sections
            var sections = document.querySelectorAll('.section');
            sections.forEach(function(section) {
                section.classList.remove('active');
            });

            // Show the clicked section
            var activeSection = document.getElementById(sectionId);
            activeSection.classList.add('active');
        }
    </script>
</body>
</html>
