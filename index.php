<?php
session_start();
$flash = '';
$old = ['name' => '', 'email' => '', 'age' => ''];
$login_email = '';
if (isset($_SESSION['flash'])) {
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
}
// If the form had old values stored (registration), use them
if (isset($_SESSION['old'])) {
    $old = array_merge($old, $_SESSION['old']);
    unset($_SESSION['old']);
}
// If the login form had an old value, use it
if (isset($_SESSION['old_login'])) {
    $login_email = $_SESSION['old_login'];
    unset($_SESSION['old_login']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Form</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .form-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            padding: 40px;
            width: 100%;
            max-width: 450px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .form-title {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
            font-size: 28px;
            font-weight: 300;
            letter-spacing: 1px;
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: 500;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .form-group input {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid #e1e5e9;
            border-radius: 12px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: rgba(255, 255, 255, 0.9);
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            transform: translateY(-2px);
        }

        .form-group input:hover {
            border-color: #667eea;
            transform: translateY(-1px);
        }

        .submit-btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 12px;
            color: white;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }

        .submit-btn:active {
            transform: translateY(0);
        }

        .flash { margin-bottom: 16px; padding: 10px 12px; background: rgba(255,255,255,0.9); border-radius:8px; color:#333; border-left:4px solid #ffd54f; }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .form-container {
            animation: slideIn 0.6s ease-out;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <div style="display:flex; gap:12px; margin-bottom:16px; align-items:center; justify-content:space-between;">
            <h2 class="form-title" style="margin:0;">Account</h2>
            <div>
                <button id="showRegister" class="submit-btn" style="background:#43a047; padding:8px 12px; font-size:14px;">Register</button>
                <button id="showLogin" class="submit-btn" style="background:#1976d2; padding:8px 12px; font-size:14px;">Login</button>
            </div>
        </div>
        <?php if ($flash): ?>
            <div class="flash"><?php echo htmlspecialchars($flash); ?></div>
        <?php endif; ?>

        <!-- Registration form (default visible) -->
        <form id="registerForm" action="process_form.php" method="POST">
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" required value="<?php echo htmlspecialchars($old['name']); ?>">
            </div>
            
            <div class="form-group">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($old['email']); ?>">
            </div>
            
            <div class="form-group">
                <label for="age">Age</label>
                <input type="number" id="age" name="age" min="1" max="120" required value="<?php echo htmlspecialchars($old['age']); ?>">
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required placeholder="Choose a password (min 6 chars)">
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required placeholder="Repeat password">
            </div>
            
            <button type="submit" class="submit-btn">Register</button>
        </form>

        <!-- Login form (hidden by default) -->
        <form id="loginForm" action="login.php" method="POST" style="display:none; margin-top:8px;">
            <div class="form-group">
                <label for="login_email">Email Address</label>
                <input type="email" id="login_email" name="email" required value="<?php echo htmlspecialchars($login_email); ?>">
            </div>
            <div class="form-group">
                <label for="login_password">Password</label>
                <input type="password" id="login_password" name="password" required placeholder="Your password">
            </div>
            <div class="form-group">
                <small>Enter the email and password you registered with to sign in.</small>
            </div>
            <button type="submit" class="submit-btn">Login</button>
        </form>

        <script>
            // Simple toggle between registration and login
            const registerForm = document.getElementById('registerForm');
            const loginForm = document.getElementById('loginForm');
            const showRegister = document.getElementById('showRegister');
            const showLogin = document.getElementById('showLogin');

            showRegister.addEventListener('click', function(e){
                e.preventDefault();
                registerForm.style.display = '';
                loginForm.style.display = 'none';
            });

            showLogin.addEventListener('click', function(e){
                e.preventDefault();
                registerForm.style.display = 'none';
                loginForm.style.display = '';
            });
        </script>
    </div>
</body>
</html>
