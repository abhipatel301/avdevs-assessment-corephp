<?php
session_start(); // Start the session
require 'config/db.php'; // Include the database connection

$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $customerName = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT); // Hash the password

    // File upload logic
    $idProofPath = null;
    if (isset($_FILES['id_proof']) && $_FILES['id_proof']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['id_proof'];
        $allowedTypes = ['image/jpeg', 'image/png', 'application/pdf'];
        $maxFileSize = 2 * 1024 * 1024; // 2 MB

        if (in_array($file['type'], $allowedTypes) && $file['size'] <= $maxFileSize) {
            $uploadDir = 'uploads/'; // Ensure this directory exists and is writable
            $uniqueName = uniqid() . '_' . basename($file['name']);
            $idProofPath = $uploadDir . $uniqueName;

            if (!move_uploaded_file($file['tmp_name'], $idProofPath)) {
                $_SESSION['error'] = "File upload failed.";
                header("Location: " . $_SERVER['PHP_SELF']);
                exit;
            }
        } else {
            $_SESSION['error'] = "Invalid file type or size.";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        }
    } else {
        $_SESSION['error'] = "Please upload a valid ID proof.";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }

    try {
        // Check for unique constraints
        $query = $pdo->prepare("SELECT COUNT(*) FROM customers WHERE name = ? OR email = ? OR phone = ?");
        $query->execute([$customerName, $email, $phone]);
        $exists = $query->fetchColumn();

        if ($exists > 0) {
            $_SESSION['error'] = "Name, email, or phone already exists!";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit;
        }

        // Insert customer into the database
        $query = $pdo->prepare("INSERT INTO `customers`(`name`, `email`, `phone`, `id_proof`, `password`) VALUES (?, ?, ?, ?, ?)");
        $query->execute([$customerName, $email, $phone, $idProofPath, $password]);

        $_SESSION['success'] = "Customer Registration successful!";
        header("Location: login.php");
        exit;
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
        header("Location: " . $_SERVER['PHP_SELF']);
        exit;
    }
}

// Display error message if set
if (isset($_SESSION['error'])) {
    $errorMessage = $_SESSION['error'];
    unset($_SESSION['error']); // Clear the error message after displaying it
}

// Display success message if set
if (isset($_SESSION['success'])) {
    $successMessage = $_SESSION['success'];
    unset($_SESSION['success']); // Clear the success message after displaying it
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register</title>
</head>
<body>
    <h2>Register</h2>
    <?php if (!empty($errorMessage)): ?>
        <p style="color: red;"><?php echo $errorMessage; ?></p>
    <?php endif; ?>
    <?php if (!empty($successMessage)): ?>
        <p style="color: green;"><?php echo $successMessage; ?></p>
    <?php endif; ?>
    <form method="POST" enctype="multipart/form-data">
        <label>Customer:</label><br>
        <input type="text" name="name" required><br>
        <label>Email:</label><br>
        <input type="email" name="email" required><br>
        <label>Phone:</label><br>
        <input type="number" name="phone" required><br>
        <label>ID Proof:</label><br>
        <input type="file" name="id_proof" required><br>
        <label>Password:</label><br>
        <input type="password" name="password" required><br>
        <button type="submit">Register</button>
    </form>
</body>
</html>
