<?php
session_start();

// Database configuration
$servername = "localhost";
$username = "root";  // Default XAMPP username
$password = "";      // Default XAMPP password (empty)
$dbname = "contact_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    // Use a simple HTML response (no headers yet) if DB is not available
    echo "<p>Connection failed: " . htmlspecialchars($conn->connect_error) . "</p>";
    exit();
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate input
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $age = intval($_POST['age']);
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Basic validation
    if (empty($name) || empty($email) || $age <= 0) {
        $_SESSION['flash'] = 'Please fill all fields correctly.';
        $_SESSION['old'] = ['name' => $name, 'email' => $email, 'age' => $_POST['age'] ?? ''];
        header('Location: index.php');
        exit();
    }

    // Password checks
    if (empty($password) || strlen($password) < 6) {
        $_SESSION['flash'] = 'Please choose a password with at least 6 characters.';
        $_SESSION['old'] = ['name' => $name, 'email' => $email, 'age' => $_POST['age'] ?? ''];
        header('Location: index.php');
        exit();
    }
    if ($password !== $confirm_password) {
        $_SESSION['flash'] = 'Passwords do not match.';
        $_SESSION['old'] = ['name' => $name, 'email' => $email, 'age' => $_POST['age'] ?? ''];
        header('Location: index.php');
        exit();
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['flash'] = 'Invalid email format.';
        $_SESSION['old'] = ['name' => $name, 'email' => $email, 'age' => $_POST['age'] ?? ''];
        header('Location: index.php');
        exit();
    }

    // Check if email already exists
    $check_stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    if (!$check_stmt) {
        $_SESSION['flash'] = 'Database error (prepare check): ' . $conn->error;
        $_SESSION['old'] = ['name' => $name, 'email' => $email, 'age' => $_POST['age'] ?? ''];
        header('Location: index.php');
        exit();
    }
    $check_stmt->bind_param("s", $email);
    $check_stmt->execute();
    $result = $check_stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $_SESSION['flash'] = 'Email already exists in database.';
        $check_stmt->close();
        $conn->close();
        $_SESSION['old'] = ['name' => $name, 'email' => $email, 'age' => $_POST['age'] ?? ''];
        header('Location: index.php');
        exit();
    }
    $check_stmt->close();

    // Prepare and bind (store password hash)
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    // Ensure the users table has a password_hash column. SQL recommendation provided in comments.
    $stmt = $conn->prepare("INSERT INTO users (name, email, age, password_hash) VALUES (?, ?, ?, ?)");
    if (!$stmt) {
        $_SESSION['flash'] = 'Database error (prepare insert): ' . $conn->error;
        $_SESSION['old'] = ['name' => $name, 'email' => $email, 'age' => $_POST['age'] ?? ''];
        header('Location: index.php');
        exit();
    }
    $stmt->bind_param("ssis", $name, $email, $age, $password_hash);

    // Execute the statement
    if ($stmt->execute()) {
        // Store user info in session (simple "login" after registration)
        $user_id = $conn->insert_id;
        $_SESSION['user_id'] = $user_id;
        $_SESSION['user_name'] = $name;
        $_SESSION['flash'] = 'Record saved successfully! Welcome, ' . $name . '.';

        // Redirect to the home page
        header('Location: home.php');
        exit();
    } else {
        $_SESSION['flash'] = 'Error saving record: ' . $stmt->error;
        $_SESSION['old'] = ['name' => $name, 'email' => $email, 'age' => $_POST['age'] ?? ''];
        header('Location: index.php');
        exit();
    }

    // Close statement
    $stmt->close();
}

// Close connection
$conn->close();
?>