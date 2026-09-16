<?php
session_start();

// Include the database connection
include 'config.php';  // Ensure this points to your config file

// Check if user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header('Location: login.php');
    exit;
}

// Retrieve the username from the session
$username = $_SESSION['username'];

// Set the exam name directly (example: Final Exam)
$exam_name = "Final Exam";
$exam_id = 1; // Assuming the exam ID for the Final Exam is 1, update if necessary
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="student_dashboard.css">
</head>
<body>

<div class="student-dashboard-container">
    <div class="student-dashboard-header">
        <h1>Welcome, <?php echo htmlspecialchars($username); ?>!</h1> <!-- Display Username -->
    </div>

    <!-- Quick Links -->
    <div class="quick-links">
        <h2>Quick Links</h2>
        <ul>
            <li><a href="exam.php">Start New Exam</a></li> <!-- Link to exam.php -->
        </ul>
    </div>

    <!-- Notifications Section -->
    <div class="dashboard-notifications">
        <h2>Notifications</h2>
        <ul>
            <li><p>You have an exam scheduled for September 23rd at 10 AM.</p></li>
        </ul>
    </div>

    <!-- Logout Button Section -->
    <div class="logout-section">
        <a href="logout.php" class="btn-logout">Logout</a>
    </div>
</div>

</body>
</html>
