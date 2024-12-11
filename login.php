<?php
session_start(); // Start the session
require 'config/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    try {
        // Fetch customer record
        $query = $pdo->prepare("SELECT id, password FROM customers WHERE email = ?");
        $query->execute([$email]);
        $customer = $query->fetch(PDO::FETCH_ASSOC);

        if ($customer) {
            if (password_verify($password, $customer['password'])) {
                // Set session data
                $_SESSION['user_id'] = $customer['id'];
                $_SESSION['user_email'] = $email;
                $_SESSION['success'] = "Welcome back, user ID: " . $customer['id'];

                // Redirect to dashboard
                header("Location: dashboard.php");
                exit;
            } else {
                $_SESSION['error'] = "Invalid password!";
            }
        } else {
            $_SESSION['error'] = "No user found with this email!";
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }

    // Redirect back to login page on failure
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
</head>
<body>
    <h2>Login</h2>
    <?php
    if (isset($_SESSION['error'])) {
        echo "<p style='color: red;'>" . $_SESSION['error'] . "</p>";
        unset($_SESSION['error']); // Clear the error message after displaying it
    }

    if (isset($_SESSION['success'])) {
        echo "<p style='color: green;'>" . $_SESSION['success'] . "</p>";
        unset($_SESSION['success']); // Clear the success message after displaying it
    }
    ?>
    <form method="POST">
        <label>Email:</label><br>
        <input type="email" name="email" required><br>
        <label>Password:</label><br>
        <input type="password" name="password" required><br>
        <button type="submit">Login</button>
    </form>
</body>
</html>
