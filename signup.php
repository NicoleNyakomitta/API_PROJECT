<?php
include 'db_connect.php';

if (isset($_POST['signup'])) {
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Check if email already exists
    $check = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows > 0) {
        echo "<script>alert('Email already registered! Please log in.'); window.location='signup.html';</script>";
    } else {
        $stmt = $conn->prepare("INSERT INTO users (fullname, email, phone, password) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $fullname, $email, $phone, $password);

        if ($stmt->execute()) {
            echo "<script>alert('Signup successful! You can now log in.'); window.location='login.html';</script>";
        } else {
            echo "<script>alert('Error: Could not complete signup.'); window.location='signup.html';</script>";
        }

        $stmt->close();
    }

    $check->close();
    $conn->close();
}
?>
