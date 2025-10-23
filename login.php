<?php
session_start();

// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "contact_db";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    $_SESSION['flash'] = 'Database connection error.';
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL) || empty($password)) {
        $_SESSION['flash'] = 'Please provide a valid email and password to login.';
        $_SESSION['old_login'] = $email;
        header('Location: index.php');
        exit();
    }

    // Look up user by email and get password hash
    $stmt = $conn->prepare('SELECT id, name, password_hash FROM users WHERE email = ? LIMIT 1');
    if (!$stmt) {
        $_SESSION['flash'] = 'Database error.';
        $_SESSION['old_login'] = $email;
        header('Location: index.php');
        exit();
    }
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows === 1) {
        $row = $result->fetch_assoc();
        $hash = $row['password_hash'] ?? '';
        if ($hash && password_verify($password, $hash)) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['user_name'] = $row['name'];
            $_SESSION['flash'] = 'Welcome back, ' . $row['name'] . '!';
            header('Location: home.php');
            exit();
        }
        // wrong password
        $_SESSION['flash'] = 'Invalid credentials.';
        $_SESSION['old_login'] = $email;
        header('Location: index.php');
        exit();
    }

    $_SESSION['flash'] = 'No account found for that email.';
    $_SESSION['old_login'] = $email;
    header('Location: index.php');
    exit();
}

$conn->close();
header('Location: index.php');
exit();
?>
