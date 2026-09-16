<?php
session_start();
include 'config.php'; // Ensure this file initializes $pdo

// Check if the user is logged in and is a student
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'student') {
    header('Location: login.php');
    exit();
}

// Initialize exam session variables if they are not set
if (!isset($_SESSION['current_question'])) {
    $_SESSION['current_question'] = 0; // Start from the first question
    $_SESSION['score'] = 0; // Initialize score
    $_SESSION['time_remaining'] = 10 * 60; // 10 minutes in seconds
    // Fetch 30 random questions and store them in session
    $sql = "SELECT * FROM questions WHERE subject = 'Final Exam' ORDER BY RAND() LIMIT 30";
    $result = $pdo->query($sql);
    $_SESSION['questions'] = $result->fetchAll(PDO::FETCH_ASSOC);
}

// Update time remaining if it's been sent via the form submission
if (isset($_POST['time_remaining'])) {
    $_SESSION['time_remaining'] = (int)$_POST['time_remaining'];
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Process the answer
    if (isset($_POST['answer']) && !empty($_POST['answer'])) {
        $submitted_answer = $_POST['answer'];
        $current_question = $_SESSION['questions'][$_SESSION['current_question']];
        if ($submitted_answer === $current_question['correct_option']) {
            $_SESSION['score']++; // Increment score for correct answer
        }
        // Store the selected answer in the session
        $_SESSION['selected_answers'][$_SESSION['current_question']] = $submitted_answer;
    }

    // Move to the next question or handle submission
    if (isset($_POST['next'])) {
        $_SESSION['current_question']++;
    } elseif (isset($_POST['submit'])) {
        // Save result and redirect to results page
        $stmt = $pdo->prepare("INSERT INTO results (user_id, score) VALUES (?, ?)");
        $stmt->execute([$_SESSION['user_id'], $_SESSION['score']]);
        header('Location: result.php');
        exit();
    }
}

// Handle Previous button
if (isset($_GET['action']) && $_GET['action'] === 'previous') {
    if ($_SESSION['current_question'] > 0) {
        $_SESSION['current_question']--;
    }
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}

// Check if the current question index is valid
if ($_SESSION['current_question'] >= count($_SESSION['questions'])) {
    // All questions answered, save the result
    $stmt = $pdo->prepare("INSERT INTO results (user_id, score) VALUES (?, ?)");
    $stmt->execute([$_SESSION['user_id'], $_SESSION['score']]);
    header('Location: result.php');
    exit();
}

// Get the current question
$current_question = $_SESSION['questions'][$_SESSION['current_question']];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Final Exam - Question <?php echo $_SESSION['current_question'] + 1; ?></title>
    <link rel="stylesheet" href="exam.css">
    <script>
        let timeRemaining = <?php echo $_SESSION['time_remaining']; ?>;

        function updateTimerDisplay() {
            const minutes = Math.floor(timeRemaining / 60);
            const seconds = timeRemaining % 60;
            document.getElementById('timer').textContent = `${minutes}:${seconds < 10 ? '0' : ''}${seconds}`;
        }

        function submitForm() {
            document.getElementById('examForm').submit(); // Automatically submit the form
        }

        window.onload = function() {
            updateTimerDisplay();
            const countdown = setInterval(function() {
                if (timeRemaining > 0) {
                    timeRemaining--;
                    document.getElementById('time_remaining').value = timeRemaining;
                    updateTimerDisplay();
                }

                if (timeRemaining <= 0) {
                    clearInterval(countdown); // Stop the countdown
                    submitForm(); // Submit the form when time runs out
                }
            }, 1000);
        };
    </script>
</head>
<body>
    <div class="container">
        <h2>Question <?php echo $_SESSION['current_question'] + 1; ?></h2>
        <div id="timer" class="timer"></div>
        <div class="question">
            <p><?php echo htmlspecialchars($current_question['question_text']); ?></p>
            <form method="post" id="examForm">
                <input type="hidden" name="time_remaining" id="time_remaining" value="<?php echo $_SESSION['time_remaining']; ?>">

                <?php
                // Retrieve the previously selected answer, if any
                $selected_answer = isset($_SESSION['selected_answers'][$_SESSION['current_question']]) ? $_SESSION['selected_answers'][$_SESSION['current_question']] : null;
                ?>

                <label>
                    <input type="radio" name="answer" value="a" <?php echo ($selected_answer === 'a') ? 'checked' : ''; ?> required> 
                    <?php echo htmlspecialchars($current_question['option_a']); ?>
                </label>
                <label>
                    <input type="radio" name="answer" value="b" <?php echo ($selected_answer === 'b') ? 'checked' : ''; ?>> 
                    <?php echo htmlspecialchars($current_question['option_b']); ?>
                </label>
                <label>
                    <input type="radio" name="answer" value="c" <?php echo ($selected_answer === 'c') ? 'checked' : ''; ?>> 
                    <?php echo htmlspecialchars($current_question['option_c']); ?>
                </label>
                <label>
                    <input type="radio" name="answer" value="d" <?php echo ($selected_answer === 'd') ? 'checked' : ''; ?>> 
                    <?php echo htmlspecialchars($current_question['option_d']); ?>
                </label>

                <div style="display: flex; justify-content: space-between; margin-top: 20px;">
                    <?php if ($_SESSION['current_question'] > 0): ?>
                        <a href="<?php echo $_SERVER['PHP_SELF']; ?>?action=previous" class="btn">Previous</a>
                    <?php endif; ?>
                    <?php if ($_SESSION['current_question'] === count($_SESSION['questions']) - 1): ?>
                        <input type="submit" name="submit" value="Submit" class="btn">
                    <?php else: ?>
                        <input type="submit" name="next" value="Next" class="btn">
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
