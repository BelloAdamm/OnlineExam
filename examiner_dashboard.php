<?php
session_start();
include 'config.php'; // Include database connection

// Check if the user is logged in and is an examiner
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'examiner') {
    header('Location: login.php');
    exit();
}

try {
    // Assuming you meant to use `user_id` instead of `examiner_id`
    $stmt = $pdo->prepare("SELECT * FROM exams WHERE examiner_id = :examiner_id");
    $stmt->execute(['examiner_id' => $_SESSION['user_id']]);
    $exams = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo 'Error: ' . $e->getMessage();
}

// Further code to display the exams, etc.


// Fetch all exams created by the examiner
$sql = "SELECT * FROM exams WHERE examiner_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$_SESSION['user_id']]);
$exams = $stmt->fetchAll();

// Handle the creation of new exams
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $exam_name = $_POST['exam_name'];

    // Insert a new exam into the database
    $sql = "INSERT INTO exams (exam_name, examiner_id) VALUES (?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$exam_name, $_SESSION['user_id']]);

    header('Location: examiner_dashboard.php');
    exit;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Examiner Dashboard</title>
    <link rel="stylesheet" href="examiner_dashboard.css">
</head>
<body>

<div class="container">
    <h2>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
    <p>You are logged in as an examiner.</p>

    <h3>Create a New Exam</h3>
    <form action="examiner_dashboard.php" method="POST">
        <label for="exam_name">Exam Name:</label>
        <input type="text" name="exam_name" required>
        <button type="submit">Create Exam</button>
    </form>

    <h3>Your Exams</h3>
    <ul>
        <?php foreach ($exams as $exam): ?>
            <li>
                <?php echo htmlspecialchars($exam['exam_name']); ?>
                <a href="manage_exam.php?exam_id=<?php echo $exam['id']; ?>">Manage Exam</a>
            </li>
        <?php endforeach; ?>
    </ul>

    <a href="logout.php" class="logout">Logout</a>
</div>
</div>

</body>
</html>
