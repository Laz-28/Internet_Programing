<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    $_SESSION['flash'] = 'Please login to add items.';
    header('Location: index.php');
    exit();
}

$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');
$user_id = intval($_SESSION['user_id']);

if ($title === '') {
    $_SESSION['flash'] = 'Title is required.';
    header('Location: home.php');
    exit();
}

$servername = 'localhost';
$username = 'root';
$password = '';
$dbname = 'contact_db';

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    $_SESSION['flash'] = 'Database error.';
    header('Location: home.php');
    exit();
}

$stmt = $conn->prepare('INSERT INTO items (user_id, title, description, created_at, selected) VALUES (?, ?, ?, NOW(), 0)');
if (!$stmt) {
    $_SESSION['flash'] = 'DB error: ' . $conn->error;
    $conn->close();
    header('Location: home.php');
    exit();
}
$stmt->bind_param('iss', $user_id, $title, $description);
$stmt->execute();
$stmt->close();
$conn->close();

$_SESSION['flash'] = 'Item added.';
header('Location: home.php');
exit();
?>
