<?php
session_start();
include 'config.php'; // Ensure this file initializes $pdo

// Check if the user is logged in and is an examiner
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'examiner') {
    header('Location: login.php');
    exit();
}

// Check if the exam_id is set in the URL
if (!isset($_GET['exam_id'])) {
    die('Invalid exam ID.');
}

$exam_id = (int)$_GET['exam_id'];

try {
    // Fetch exam details
    $stmt = $pdo->prepare("SELECT * FROM exams WHERE id = :exam_id AND examiner_id = :examiner_id");
    $stmt->execute(['exam_id' => $exam_id, 'examiner_id' => $_SESSION['user_id']]);
    $exam = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$exam) {
        die('Exam not found or you do not have permission to manage this exam.');
    }

    // Fetch all questions for this exam
    $stmt = $pdo->prepare("SELECT * FROM questions WHERE exam_id = :exam_id");
    $stmt->execute(['exam_id' => $exam_id]);
    $questions = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo 'Error: ' . $e->getMessage();
}

// Handle form submission for adding a new question
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_question'])) {
        $question_text = $_POST['question_text'];
        $option_a = $_POST['option_a'];
        $option_b = $_POST['option_b'];
        $option_c = $_POST['option_c'];
        $option_d = $_POST['option_d'];
        $correct_option = $_POST['correct_option'];

        $stmt = $pdo->prepare("INSERT INTO questions (question_text, option_a, option_b, option_c, option_d, correct_option, exam_id) 
                               VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$question_text, $option_a, $option_b, $option_c, $option_d, $correct_option, $exam_id]);

        // Redirect back to the manage page to refresh the list of questions
        header('Location: manage_exam.php?exam_id=' . $exam_id);
        exit();
    }
}

// Handle delete action for a question
if (isset($_GET['delete'])) {
    $question_id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM questions WHERE id = ? AND exam_id = ?");
    $stmt->execute([$question_id, $exam_id]);

    header('Location: manage_exam.php?exam_id=' . $exam_id);
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Exam: <?php echo htmlspecialchars($exam['exam_name']); ?></title>
    <link rel="stylesheet" href="manage_exam.css">
</head>
<body>

<div class="container">
    <h1>Manage Exam: <?php echo htmlspecialchars($exam['exam_name']); ?></h1>

    <!-- Add New Question Form -->
    <form method="POST" action="manage_exam.php?exam_id=<?php echo $exam_id; ?>">
        <h2>Add New Question</h2>
        <div>
            <label for="question_text">Question:</label>
            <input type="text" name="question_text" id="question_text" required>
        </div>
        <div>
            <label for="option_a">Option A:</label>
            <input type="text" name="option_a" id="option_a" required>
        </div>
        <div>
            <label for="option_b">Option B:</label>
            <input type="text" name="option_b" id="option_b" required>
        </div>
        <div>
            <label for="option_c">Option C:</label>
            <input type="text" name="option_c" id="option_c" required>
        </div>
        <div>
            <label for="option_d">Option D:</label>
            <input type="text" name="option_d" id="option_d" required>
        </div>
        <div>
            <label for="correct_option">Correct Option:</label>
            <select name="correct_option" id="correct_option" required>
                <option value="a">A</option>
                <option value="b">B</option>
                <option value="c">C</option>
                <option value="d">D</option>
            </select>
        </div>
        <div>
            <input type="submit" name="add_question" value="Add Question">
        </div>
    </form>

    <!-- List of Existing Questions -->
    <h2>Existing Questions</h2>
    <table>
        <thead>
            <tr>
                <th>Question</th>
                <th>Option A</th>
                <th>Option B</th>
                <th>Option C</th>
                <th>Option D</th>
                <th>Correct Option</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($questions as $question): ?>
                <tr>
                    <td><?php echo htmlspecialchars($question['question_text']); ?></td>
                    <td><?php echo htmlspecialchars($question['option_a']); ?></td>
                    <td><?php echo htmlspecialchars($question['option_b']); ?></td>
                    <td><?php echo htmlspecialchars($question['option_c']); ?></td>
                    <td><?php echo htmlspecialchars($question['option_d']); ?></td>
                    <td><?php echo strtoupper(htmlspecialchars($question['correct_option'])); ?></td>
                    <td>
                    <a href="edit_question.php?question_id=<?php echo $question['id']; ?>&exam_id=<?php echo $exam_id; ?>">Edit</a> | 
                    <a href="manage_exam.php?delete=<?php echo $question['id']; ?>&exam_id=<?php echo $exam_id; ?>" onclick="return confirm('Are you sure you want to delete this question?')">Delete</a>

                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

</body>
</html>
