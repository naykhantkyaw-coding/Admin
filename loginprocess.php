<?php
include('includes/config.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Validate inputs
    if (empty($username) || empty($password)) {
        $_SESSION['error_message'] = "Please enter both username and password.";
        header("Location: login.php");
        exit();
    }

    try {
        // Prepare SQL statement to prevent SQL injection
        $stmt = $pdo->prepare("SELECT * FROM Tbl_Users WHERE UserName = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            // Check if user is approved
            if ($user['Status'] !== 'Approved') {
                $_SESSION['error_message'] = "Your account is pending approval. Please contact administrator.";
                header("Location: login.php");
                exit();
            }
            //password_verify($password, $user['Password'])
            // Verify password
            if ($password == $user['Password']) {
                // Set session variables
                $_SESSION['user_id'] = $user['UserId'];
                $_SESSION['username'] = $user['UserName'];
                $_SESSION['email'] = $user['Email'];
                $_SESSION['role'] = $user['Role'];
                $_SESSION['status'] = $user['Status'];
                $_SESSION['loggedin'] = true;

                // Redirect based on role or to default page
                if ($user['Role'] === 'Admin') {
                    header("Location: dashboard.php");
                } else {
                    header("Location: ../Movies/home.php");
                }
                exit();
            } else {
                $_SESSION['error_message'] = "Invalid username or password.".  $testing  ;
                header("Location: index.php");
                exit();
            }
        } else {
            $_SESSION['error_message'] = "Invalid username or password.";
            header("Location: index.php");
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Database error: " . $e->getMessage();
        header("Location: index.php");
        exit();
    }
} else {
    // If not POST request, redirect to login
    header("Location: login.php");
    exit();
}
?>