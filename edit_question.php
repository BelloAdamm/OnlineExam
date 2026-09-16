<?php 
session_start();
include 'config.php';

// Ensure the examiner is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'examiner') {
    header('Location: login.php');
    exit();
}

// Ensure the question ID is provided
if (!isset($_GET['question_id']) || !is_numeric($_GET['question_id'])) {
    die("Invalid Question ID.");
}

$question_id = (int)$_GET['question_id']; // Ensure question_id is an integer

// Fetch the question details and ensure it belongs to the logged-in examiner
$stmt = $pdo->prepare("
    SELECT q.*, e.examiner_id 
    FROM questions q 
    JOIN exams e ON q.exam_id = e.id 
    WHERE q.id = ? AND e.examiner_id = ?
");
$stmt->execute([$question_id, $_SESSION['user_id']]);
$question = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$question) {
    die("Question not found or you don't have permission to edit this question.");
}

// Handle form submission to update the question
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and validate the input
    $question_text = trim($_POST['question_text']);
    
    if (empty($question_text)) {
        $error = "Question text cannot be empty.";
    } else {
        // Update the question in the database
        $stmt = $pdo->prepare("UPDATE questions SET question_text = ? WHERE id = ?");
        $stmt->execute([$question_text, $question_id]);

        // Redirect back to manage_exam.php after editing, with a success message
        header("Location: manage_exam.php?exam_id=" . $question['exam_id'] . "&message=Question+updated+successfully");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Question</title>
    <link rel="stylesheet" href="edit_question.css">
</head>
<body>

<div class="container">
    <h2>Edit Question</h2>

    <!-- Display error message if there is one -->
    <?php if (isset($error)): ?>
        <p class="error-message"><?php echo $error; ?></p>
    <?php endif; ?>

    <form action="edit_question.php?question_id=<?php echo $question_id; ?>" method="POST">
        <label for="question_text">Question Text:</label>
        <input type="text" name="question_text" value="<?php echo htmlspecialchars($question['question_text']); ?>" required>
        
        <button type="submit">Update Question</button>
    </form>

    <a href="manage_exam.php?exam_id=<?php echo $question['exam_id']; ?>">Back to Manage Exam</a>
</div>

</body>
</html>
