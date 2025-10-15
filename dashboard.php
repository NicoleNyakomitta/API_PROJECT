<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Hotel Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="signup-container">
    <h2>Welcome, <?php echo $_SESSION['fullname']; ?>!</h2>
    <p>You have successfully logged in to the Hotel Management System.</p>
    <form action="logout.php" method="POST">
        <button type="submit" name="logout">Logout</button>
    </form>
</div>
</body>
</html>
