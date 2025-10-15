<?php
<<<<<<< HEAD
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
=======

class LoginScreen {

    private $db;

    // Constructor to create a DB connection
    public function __construct() {
       
        $host = "127.0.0.1";   
        $dbname = "geolink"; 
        $user = "root"; 
        $pass = "0000"; 

        try {
            $this->db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass);
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
            }
        }

    // Show the login form
    public function show() {
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <title>Login</title>
            <style>
                body {
                    display: flex;
                    justify-content: center;
                    align-items: center;
                    height: 100vh;
                    font-family: cursive, sans-serif;
                    background: #f8f8f8;
                }
                .container {
                    text-align: center;
                    background: white;
                    padding: 30px;
                    border-radius: 12px;
                    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
                    width: 350px;
                }
                img {
                    width: 100px;
                    height: 100px;
                    border-radius: 50%;
                }
                input {
                    width: 90%;
                    padding: 10px;
                    margin: 10px 0;
                    border: 1px solid #ccc;
                    border-radius: 6px;
                }
                button {
                    background: brown;
                    color: white;
                    border: none;
                    padding: 10px 20px;
                    border-radius: 6px;
                    cursor: pointer;
                    font-weight: bold;
                }
                button:hover {
                    background: darkred;
                }
                .link {
                    margin-top: 15px;
                    display: block;
                    font-weight: bold;
                    font-size: 14px;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <img src="pp.png" alt="reg">
                <h1>Login Here</h1>

                <form method="POST" action="">
                    <input type="email" name="email" placeholder="Enter email" required><br>
                    <input type="password" name="password" placeholder="Enter password" required><br>
                    <button type="submit" name="login">Login</button>
                </form>

                <a href="signup.php" class="link">Already have an account? Register</a>
            </div>
        </body>
        </html>
        <?php
    }

    // Handle login logic with DB
    public function handleLogin() {
        if (isset($_POST['login'])) {
            $email = trim($_POST['email']);
            $password = trim($_POST['password']);

            // Query user from DB
            $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                echo "<p style='color:green; text-align:center;'>✅ Login successful! Welcome, {$user['email']}.</p>";
                // TODO: Start session and redirect to dashboard
            } else {
                echo "<p style='color:red; text-align:center;'>❌ Invalid email or password.</p>";
            }
        }
    }
}

// Run
$loginScreen = new LoginScreen();
$loginScreen->handleLogin();
$loginScreen->show();
>>>>>>> 4d17f55fd94804b4fa41cf873fd3a6e98e65b6e7
