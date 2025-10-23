<?php
session_start();

// If user is not logged in, redirect back to index
if (!isset($_SESSION['user_id'])) {
    $_SESSION['flash'] = 'Please login or register to access the home page.';
    header('Location: index.php');
    exit();
}

$user_id = intval($_SESSION['user_id']);
$name = isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'User';
$flash = '';
if (isset($_SESSION['flash'])) {
    $flash = htmlspecialchars($_SESSION['flash']);
    unset($_SESSION['flash']);
}

// Database configuration (same as other files)
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "contact_db";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die('Database connection error: ' . htmlspecialchars($conn->connect_error));
}

// Fetch items belonging to this user
$items = [];
$stmt = $conn->prepare('SELECT id, title, description, created_at, selected FROM items WHERE user_id = ? ORDER BY created_at DESC');
if ($stmt) {
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) {
        $items[] = $row;
    }
    $stmt->close();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Home</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f7fb; padding: 24px; }
        .container { max-width: 900px; margin: 0 auto; }
        .card { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 6px 18px rgba(0,0,0,0.06); margin-bottom: 18px; }
        .header { display:flex; justify-content:space-between; align-items:center; }
        .welcome { font-size: 20px; }
        .flash { background:#fff3cd; color:#856404; padding:10px 12px; border-radius:6px; margin-bottom:12px; }
        form .form-group { margin-bottom:12px; }
        form input, form textarea { width:100%; padding:10px; border-radius:6px; border:1px solid #ddd; }
        table { width:100%; border-collapse:collapse; }
        table th, table td { padding:8px 10px; border-bottom:1px solid #eee; text-align:left; }
        .actions a, .actions form { display:inline-block; margin-right:6px; }
        .btn { padding:8px 12px; background:#1976d2; color:white; border-radius:6px; text-decoration:none; }
        .btn-danger { background:#d32f2f; }
        .selected { background:#e8f5e9; }
    </style>
</head>
<body>
    <div class="container">
        <div class="card header">
            <div>
                <div class="welcome">Welcome, <?php echo $name; ?>!</div>
                <div style="font-size:12px;color:#666;">Manage your items below.</div>
            </div>
            <div>
                <a class="btn" href="logout.php">Log out</a>
            </div>
        </div>

        <?php if ($flash): ?>
            <div class="flash"><?php echo $flash; ?></div>
        <?php endif; ?>

        <div class="card">
            <h3>Add new item</h3>
            <form action="add_item.php" method="POST">
                <div class="form-group">
                    <label for="title">Title</label>
                    <input id="title" name="title" required maxlength="255">
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="3"></textarea>
                </div>
                <button class="btn" type="submit">Add Item</button>
            </form>
        </div>

        <div class="card">
            <h3>Your items</h3>
            <?php if (empty($items)): ?>
                <div>No items yet. Add one using the form above.</div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr><th>Title</th><th>Description</th><th>Created</th><th>Selected</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $it): ?>
                            <tr class="<?php echo ($it['selected'] ? 'selected' : ''); ?>">
                                <td><?php echo htmlspecialchars($it['title']); ?></td>
                                <td><?php echo nl2br(htmlspecialchars($it['description'])); ?></td>
                                <td><?php echo htmlspecialchars($it['created_at']); ?></td>
                                <td><?php echo $it['selected'] ? 'Yes' : 'No'; ?></td>
                                <td class="actions">
                                    <form style="display:inline" action="select_item.php" method="POST">
                                        <input type="hidden" name="id" value="<?php echo (int)$it['id']; ?>">
                                        <button class="btn" type="submit"><?php echo $it['selected'] ? 'Unselect' : 'Select'; ?></button>
                                    </form>
                                    <form style="display:inline" action="delete_item.php" method="POST" onsubmit="return confirm('Delete this item?');">
                                        <input type="hidden" name="id" value="<?php echo (int)$it['id']; ?>">
                                        <button class="btn btn-danger" type="submit">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>

<?php
$conn->close();
?>
