<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    $_SESSION['flash'] = 'Please login to select items.';
    header('Location: index.php');
    exit();
}

$id = intval($_POST['id'] ?? 0);
$user_id = intval($_SESSION['user_id']);
if ($id <= 0) {
    $_SESSION['flash'] = 'Invalid item id.';
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

// Check current selected state for this item
$stmt = $conn->prepare('SELECT selected FROM items WHERE id = ? AND user_id = ? LIMIT 1');
if (!$stmt) {
    $_SESSION['flash'] = 'DB error: ' . $conn->error;
    $conn->close();
    header('Location: home.php');
    exit();
}
$stmt->bind_param('ii', $id, $user_id);
$stmt->execute();
$res = $stmt->get_result();
if (!$res || $res->num_rows !== 1) {
    $_SESSION['flash'] = 'Item not found.';
    $stmt->close();
    $conn->close();
    header('Location: home.php');
    exit();
}
$row = $res->fetch_assoc();
$current = (int)$row['selected'];
$stmt->close();

if ($current) {
    // Unselect this item
    $u = $conn->prepare('UPDATE items SET selected = 0 WHERE id = ? AND user_id = ?');
    $u->bind_param('ii', $id, $user_id);
    $u->execute();
    $u->close();
    $_SESSION['flash'] = 'Item unselected.';
    $conn->close();
    header('Location: home.php');
    exit();
} else {
    // Unselect other items for this user, then select this one
    $conn->begin_transaction();
    $conn->query('UPDATE items SET selected = 0 WHERE user_id = ' . $user_id);
    $s = $conn->prepare('UPDATE items SET selected = 1 WHERE id = ? AND user_id = ?');
    $s->bind_param('ii', $id, $user_id);
    $s->execute();
    $s->close();
    $conn->commit();
    $conn->close();
    $_SESSION['flash'] = 'Item selected.';
    header('Location: home.php');
    exit();
}
?>
