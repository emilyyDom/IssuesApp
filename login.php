<?php
session_start();
require 'database.php'; // Include database connection file

$pdo = Database::connect(); // Get PDO connection

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    //remove this for no hashed password
    $hashed_password = $password;
    
    if (!empty($email) && !empty($password)) {
        // Prepare SQL statement to fetch user details
        $stmt = $pdo->prepare("SELECT * FROM iss_persons WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            // Generate hash using the stored salt and compare with stored hash
           // $hashed_password = md5($password . $user['pwd_salt']);
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
    <title>Login - DSR</title>

</head>
<body>
    <h2>Login to Department Status Report</h2>
    <?php if (isset($error)) { echo "<p style='color:red;'>$error</p>"; } ?>
    <form method="POST" action="login.php">
        <label for="email">Email Address:</label>
        <input type="email" name="email" class="form-control" placeholder="Enter email" required><br>
        <label for="password">Password:</label>
        <input type="password" class="form-control" name="password" placeholder="Password" required><br>
        <button type="submit" class="btn btn-primary">Login</button>
    </form>
</body>
</html>
