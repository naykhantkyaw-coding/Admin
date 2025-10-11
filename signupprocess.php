<?php
include('includes/config.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate inputs
    if (empty($full_name) || empty($email) || empty($phone) || empty($username) || empty($password)) {
        $_SESSION['error_message'] = "Please fill in all required fields.";
        header("Location: signup.php");
        exit();
    }

    // Validate phone format
    if (!preg_match('/^09\d{7,9}$/', $phone)) {
        $_SESSION['error_message'] = "Please enter a valid phone number that starts with 09 and is 9–11 digits long.";
        header("Location: signup.php");
        exit();
    }

    // Check if passwords match
    if ($password !== $confirm_password) {
        $_SESSION['error_message'] = "Passwords do not match.";
        header("Location: signup.php");
        exit();
    }

    // Check password length
    if (strlen($password) < 6) {
        $_SESSION['error_message'] = "Password must be at least 6 characters long.";
        header("Location: signup.php");
        exit();
    }

    try {
        // Check if username or email already exists
        $stmt = $pdo->prepare("SELECT UserId FROM Tbl_Users WHERE UserName = ? OR Email = ?");
        $stmt->execute([$username, $email]);
        
        if ($stmt->rowCount() > 0) {
            $_SESSION['error_message'] = "Username or email already exists.";
            header("Location: signup.php");
            exit();
        }

        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert new user with default role 'User' and status 'Pending'
        $stmt = $pdo->prepare("INSERT INTO Tbl_Users (UserName, Email, Password, PhoneNo, Role, Status) VALUES (?, ?, ?, ?, 'User', 'Pending')");
        $stmt->execute([$username, $email, $hashed_password, $phone]);

        $_SESSION['success_message'] = "Registration successful! Your account is pending approval. You will be notified once approved.";
        header("Location: index.php");
        exit();

    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Registration failed: " . $e->getMessage();
        header("Location: signup.php");
        exit();
    }
} else {
    // If not POST request, redirect to signup
    header("Location: signup.php");
    exit();
}
?>