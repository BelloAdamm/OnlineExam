<?php
session_start();
include 'config.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Initialize answered questions array
if (!isset($_SESSION['answered_questions'])) {
    $_SESSION['answered_questions'] = [];
}

// Fetch the next question
if (count($_SESSION['answered_questions']) < 30) {
    // Fetch the next random math question that hasn't been answered yet
    $sql = "SELECT * FROM questions WHERE subject = 'maths' AND id NOT IN (" . implode(',', $_SESSION['answered_questions']) . ") ORDER BY RAND() LIMIT 1";
    $result = $conn->query($sql);
    $question = $result->fetch_assoc();
    
    // If there's a question, show it
    if ($question) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Process the answer
            $selected_answer = $_POST['answer'];
            $_SESSION['answered_questions'][] = $question['id'];

            // Redirect to the next question
            header('Location: next_question.php');
            exit();
        }
    } else {
        // No more questions available
        echo "No more questions available.";
        echo "<a href='results.php'>View Results</a>"; // Redirect to results page
        exit();
    }
} else {
    echo "You have completed the exam!";
    echo "<a href='results.php'>View Results</a>"; // Redirect to results page
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Exam</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h2>Question <?php echo count($_SESSION['answered_questions']) + 1; ?></h2>
        <form method="post">
            <p><?php echo $question['question_text']; ?></p>
            <label>
                <input type="radio" name="answer" value="a" required> 
                <?php echo $question['option_a']; ?>
            </label><br>
            <label>
                <input type="radio" name="answer" value="b"> 
                <?php echo $question['option_b']; ?>
            </label><br>
            <label>
                <input type="radio" name="answer" value="c"> 
                <?php echo $question['option_c']; ?>
            </label><br>
            <label>
                <input type="radio" name="answer" value="d"> 
                <?php echo $question['option_d']; ?>
            </label><br>
            <input type="submit" value="Next Question" class="btn">
        </form>
    </div>
</body>
</html>
