<?php
session_start();
include('includes/config.php');

if (isset($_GET['id'])) {
    $booking_id = $_GET['id'];
    
    try {
        $stmt = $pdo->prepare("DELETE FROM Tbl_Booking WHERE BookingId = ?");
        $stmt->execute([$booking_id]);
        
        if ($stmt->rowCount() > 0) {
            $_SESSION['success_message'] = 'Booking deleted successfully';
        } else {
            $_SESSION['error_message'] = 'Booking not found or already deleted';
        }
        
    } catch(PDOException $e) {
        $_SESSION['error_message'] = 'Database error: ' . $e->getMessage();
    }
} else {
    $_SESSION['error_message'] = 'No booking ID provided';
}

header('Location: bookingmanagement.php');
exit();
?>