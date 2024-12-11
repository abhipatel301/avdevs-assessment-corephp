<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require 'config/db.php'; // Include database connection

try {
    // Fetch user data from the database
    $query = $pdo->prepare("SELECT name, email, phone, id_proof FROM customers WHERE id = ?");
    $query->execute([$_SESSION['user_id']]);
    $user = $query->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo "User not found!";
        exit;
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
</head>
<body>
    <h1>Welcome to Your Dashboard</h1>
    <p><strong>Name:</strong> <?php echo htmlspecialchars($user['name']); ?></p>
    <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
    <p><strong>Phone:</strong> <?php echo htmlspecialchars($user['phone']); ?></p>
    <p><strong>ID Proof:</strong></p>
    <?php
        // Get the base URL dynamically
        $baseUrl = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);

        // Remove the script name (it includes the folder structure), and append the uploads directory
        $baseUrl = rtrim($baseUrl, '/') . '/uploads/';

        if (!empty($user['id_proof'])): ?>
            <a href="<?php echo $baseUrl . htmlspecialchars($user['id_proof']); ?>" target="_blank">View ID Proof</a>
        <?php else: ?>
            <p>No ID proof uploaded.</p>
        <?php endif; ?>
    <br>
    <a href="logout.php">Logout</a>
</body>
</html>
