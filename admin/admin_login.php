<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// CHECK IF YOU ARE ALRADY LOGGED IN AS ADMIN
if (isset($_SESSION['admin_loggedin']) && $_SESSION['admin_loggedin'] === true) {
    header("Location: admin.php");
    exit;
}

$error = '';


// LOGIC PROCESS WHEN FORM IS SUBMITTED
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $admin_user_name = trim($_POST['user_name'] ?? '');
    $admin_password = trim($_POST['password'] ?? '');


    //HARD CODED $USER_NAME AND PASSWORD (CAN CCHANGE LATER)
    $user_name = 'myadmin';
    $password = 'myadmin123456789';

    if ($user_name ===  $admin_user_name && $password === $admin_password) {
        // ADMIN SESSION VARIABLES
        $_SESSION['admin_loggedin'] = true;
        $_SESSION['admin_username'] = $user_name;
        $_SESSION['admin_id'] =  1;
        // REDIRECT TO ADMIN PANEL
        header("location: admin.php");
        exit();
    } else {
        $error = 'Invalid username or password!';
    }
}

?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background: linear-gradient(rgba(0, 0, 0, 1), rgba(0, 0, 0, 0.2)), url('../images/Background/the-best-sports-shops-in-london.webp');
            background-size: cover;
            background-position: center;

        }

        .login-container {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(50px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 40px;
            border-radius: 0px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            width: 350px;
        }

        h2 {
            text-align: center;
            color: white;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .username,
        .password {
            color: white;
        }

        label {
            display: block;
            margin-bottom: 5px;
            color: #555;
            font-weight: bold;
        }

        input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 0px;
            box-sizing: border-box;
            font-size: 0.8rem;
        }

        button {
            width: 100%;
            padding: 12px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 0px;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background: #2980b9;
        }

        .error {
            background: #ffeaea;
            color: #e74c3c;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <h2>👩🏻‍💼 Admin Login</h2>

        <?php if (!empty($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="user_name" class="username">Username</label>
                <input type="text" id="user_name" name="user_name" required>
            </div>

            <div class="form-group">
                <label for="password" class="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>

            <button type="submit">Login</button>
        </form>
    </div>
</body>

</html>