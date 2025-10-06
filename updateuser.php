<?php
session_start();
include('includes/config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_POST['user_id'] ?? '';
    $user_name = $_POST['user_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $role = $_POST['role'] ?? '';
    $status = $_POST['status'] ?? '';
    $password = $_POST['password'] ?? '';
    
    try {
        if (!empty($password)) {
            // If password is provided, update it too
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE Tbl_Users SET UserName = ?, Email = ?, PhoneNo = ?, Role = ?, Status = ?, Password = ? WHERE UserID = ?");
            $stmt->execute([$user_name, $email, $phone, $role, $status, $hashed_password, $user_id]);
        } else {
            // Update without changing password
            $stmt = $pdo->prepare("UPDATE Tbl_Users SET UserName = ?, Email = ?, PhoneNo = ?, Role = ?, Status = ? WHERE UserID = ?");
            $stmt->execute([$user_name, $email, $phone, $role, $status, $user_id]);
        }
        
        $_SESSION['success_message'] = 'User updated successfully';
        header('Location: index.php');
        exit();
        
    } catch(PDOException $e) {
        $_SESSION['error_message'] = 'Database error: ' . $e->getMessage();
        header('Location: index.php?edit_id=' . $user_id);
        exit();
    }
} else {
    $_SESSION['error_message'] = 'Invalid request method';
    header('Location: index.php');
    exit();
}
?>