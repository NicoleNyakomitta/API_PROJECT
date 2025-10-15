<?php
session_start();
include 'db_connect.php';

if (isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Check if email exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();

        if (password_verify($password, $row['password'])) {
            // Login success
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['fullname'] = $row['fullname'];

            echo "<script>alert('Login successful! Welcome, {$row['fullname']}'); 
                  window.location='dashboard.php';</script>";
        } else {
            echo "<script>alert('Incorrect password. Please try again.'); 
                  window.location='login.html';</script>";
        }
    } else {
        echo "<script>alert('No account found with that email.'); 
              window.location='login.html';</script>";
    }

    $stmt->close();
    $conn->close();
}
?>
