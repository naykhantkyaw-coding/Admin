<?php
session_start();
include('includes/config.php');

if (isset($_GET['id'])) {
    $user_id = $_GET['id'];
    
    try {
        // Delete the user
        $stmt = $pdo->prepare("DELETE FROM Tbl_Users WHERE UserID = ?");
        $stmt->execute([$user_id]);
        
        if ($stmt->rowCount() > 0) {
            $_SESSION['success_message'] = 'User deleted successfully';
        } else {
            $_SESSION['error_message'] = 'User not found or already deleted';
        }
        
    } catch(PDOException $e) {
        $_SESSION['error_message'] = 'Database error: ' . $e->getMessage();
    }
} else {
    $_SESSION['error_message'] = 'No user ID provided';
}

header('Location: index.php');
exit();
?>