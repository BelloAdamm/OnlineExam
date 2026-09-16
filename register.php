<?php
session_start(); // Start the session
include 'config.php';

// Connect to the database
$host = 'localhost'; // or your DB host
$db = 'online_exam'; // Your database name
$user = 'root'; // Your DB username
$pass = ''; // Your DB password
$charset = 'utf8mb4';

// Set up the PDO connection
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

// Check if the form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $role = $_POST['role']; // student or examiner
    $password = $_POST['password'];

    // Hash the password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Insert into the database
    $sql = "INSERT INTO users (username, password, role) VALUES (?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$username, $hashed_password, $role]);

    // Set a success message
    $_SESSION['message'] = "Registration successful! You can now log in.";
    header('Location: login.php'); // Redirect to the login page
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="register.css">
</head>
<body>

    <?php if (isset($_SESSION['message'])): ?>
        <div class="success-message">
            <?php
            echo $_SESSION['message']; 
            unset($_SESSION['message']); // Clear the message after displaying it
            ?>
        </div>
    <?php endif; ?>

    <div class="container">
    <h2>Create an Account</h2>
    
    <form action="register.php" method="POST">
        <label for="username">Username:</label>
        <input type="text" name="username" id="username" required>
        
        <label for="role">Role:</label>
        <select name="role" id="role" required>
            <option value="student">Student</option>
            <option value="examiner">Examiner</option>
        </select>
        
        <label for="password">Password:</label>
        <input type="password" name="password" id="password" required>
        
        <input type="submit" value="Register">
    </form>
    
    <p class="center-text small-text">
        Already have an account? <a href="login.php">Login here</a>
    </p>
</div>

</div>

</body>
</html>
