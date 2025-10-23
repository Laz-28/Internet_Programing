<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    $_SESSION['flash'] = 'Please login to delete items.';
    header('Location: index.php');
    exit();
}

$id = intval($_POST['id'] ?? 0);
if ($id <= 0) {
    $_SESSION['flash'] = 'Invalid item id.';
    header('Location: home.php');
    exit();
}

$user_id = intval($_SESSION['user_id']);

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

// Ensure the item belongs to this user
$stmt = $conn->prepare('DELETE FROM items WHERE id = ? AND user_id = ?');
if (!$stmt) {
    $_SESSION['flash'] = 'DB error: ' . $conn->error;
    $conn->close();
    header('Location: home.php');
    exit();
}
$stmt->bind_param('ii', $id, $user_id);
$stmt->execute();
$affected = $stmt->affected_rows;
$stmt->close();
$conn->close();

if ($affected > 0) {
    $_SESSION['flash'] = 'Item deleted.';
} else {
    $_SESSION['flash'] = 'Item not found or not owned by you.';
}
header('Location: home.php');
exit();
?>
