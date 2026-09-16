<?php
session_start();
include 'config.php'; // Ensure this file initializes $pdo

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Fetch user's result
$stmt = $pdo->prepare("SELECT score FROM results WHERE user_id = ? ORDER BY id DESC LIMIT 1");
$stmt->execute([$_SESSION['user_id']]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

// Check if we have a result
if (!$result) {
    echo "<div class='error'>No results found.</div>";
    exit();
}

$score = $result['score'];
$total_questions = count($_SESSION['questions']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Results</title>
    <link rel="stylesheet" href="result.css"> <!-- Ensure this links to your CSS -->
</head>
<body>
    <div class="result-container">
        <div class="result-box">
            <h2>Your Exam Results</h2>
            <div class="score-display">
                <p>Your score is: <span><?php echo htmlspecialchars($score); ?>/<?php echo htmlspecialchars($total_questions); ?></span></p>
            </div>
            <a href="student_dashboard.php" class="btn">Back to Dashboard</a>
        </div>
    </div>
</body>
</html>
