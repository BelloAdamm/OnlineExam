<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start(); // Start the session
include 'config.php';

// Database connection setup
$host = 'localhost';
$db = 'online_exam';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Initialize an error message variable
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Retrieve the user from the database
    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    // Verify password and handle login
    if ($user && password_verify($password, $user['password'])) {
        // Correct password, set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        // Redirect based on role
        if ($user['role'] == 'student') {
            header('Location: student_dashboard.php');
        } else if ($user['role'] == 'examiner') {
            header('Location: examiner_dashboard.php');
        }
        exit;
    } else {
        // Set a generic error message to avoid giving hints to attackers
        $error_message = "Invalid login credentials. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="login.css"> <!-- Link to your CSS file -->
</head>
<body>

<div class="container">
    <h2>Login to Your Account</h2>

    <!-- Display the error message if there is one -->
    <?php if (!empty($error_message)) : ?>
        <div class="error-message" style="color: red; margin-bottom: 20px;">
            <?= htmlspecialchars($error_message) ?>
        </div>
    <?php endif; ?>

    <form action="login.php" method="POST">
        <label for="username">Username:</label>
        <input type="text" name="username" required>

        <label for="password">Password:</label>
        <input type="password" name="password" required>

        <button type="submit">Login</button>
    </form>

    <div class="form-footer">
        Don't have an account? <a href="register.php">Register here</a>
    </div>
</div>

</body>
</html>
