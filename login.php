<?php
session_start();
require 'database.php'; // Include database connection file

$pdo = Database::connect(); // Get PDO connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $stored_hash = $user['pwd_salt'];
    //remove this for no hashed password
    //$hashed_password = $password;
    
    if (!empty($email) && !empty($password)) {
        // Prepare SQL statement to fetch user details
        $stmt = $pdo->prepare("SELECT * FROM iss_persons WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            // Generate hash using the stored salt and compare with stored hash
            $hashed_password = md5($password . $stored_hash);
            if ($hashed_password === $user['pwd_hash']) {
                // Authentication successful, start session
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['fname'] . ' ' . $user['lname'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['admin'] = $user['admin'];
                header("Location: issues_list.php");
                exit();
            } else {
                $error = "Invalid email or password.";
            }
        } else {
            $error = "Invalid email or password.";
        }
    } else {
        $error = "Please fill in all fields.";
    }
}

Database::disconnect(); // Close the connection
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h2>Issue Tracking System - Login</h2>
        <?php if (isset($error)) { echo "<p style='color:red;'>$error</p>"; } ?>
        <form method="POST" action="login.php" class="px-3 py-4">
            <div class="mb-3">
                <label for="email" class="form-label">Email Address:</label>
                <input type="email" class="form-control" id="email" name="email" placeholder="Enter email" required>
            </div>
            <div class="mb-3">
            <label for="password" class="form-label">Password:</label>
            <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
            </div>
            
            <button type="submit" class="btn btn-primary">Login</button>
        </form>

        <p>Don't have an account? <a href="register.php">Register here</a></p>
    </div>
</body>
</html>
