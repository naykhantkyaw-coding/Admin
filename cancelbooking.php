<?php
include('includes/config.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_message'] = 'Please login to cancel bookings.';
    header('Location: index.php');
    exit();
}

if (isset($_GET['id'])) {
    $booking_id = $_GET['id'];
    $user_id = $_SESSION['user_id'];
    
    try {
        // Verify that the booking belongs to the user and update status to Cancelled
        $stmt = $pdo->prepare("UPDATE Tbl_Booking SET Status = 'Cancelled' WHERE BookingId = ? AND UserId = ? AND Status = 'Pending'");
        $stmt->execute([$booking_id, $user_id]);
        
        if ($stmt->rowCount() > 0) {
            $_SESSION['success_message'] = 'Booking cancelled successfully.';
        } else {
            $_SESSION['error_message'] = 'Booking not found or cannot be cancelled.';
        }
        
    } catch(Exception $e) {
        $_SESSION['error_message'] = 'Error cancelling booking: ' . $e->getMessage();
    }
} else {
    $_SESSION['error_message'] = 'No booking ID provided.';
}

header('Location: checkbooking.php');
exit();
?>