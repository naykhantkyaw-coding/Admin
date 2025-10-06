<?php
session_start();
include('includes/config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_id = $_POST['booking_id'] ?? '';
    $ticket_class = $_POST['ticket_class'] ?? '';
    $seat_no = $_POST['seat_no'] ?? '';
    $quantity = $_POST['quantity'] ?? '';
    $total_price = $_POST['total_price'] ?? '';
    $booking_date = $_POST['booking_date'] ?? '';
    $status = $_POST['status'] ?? '';
    
    try {
        $stmt = $pdo->prepare("UPDATE Tbl_Booking SET TicketClass = ?, SeatNo = ?, Quantity = ?, TotalPrice = ?, BookingDate = ?, Status = ? WHERE BookingId = ?");
        $stmt->execute([$ticket_class, $seat_no, $quantity, $total_price, $booking_date, $status, $booking_id]);
        
        $_SESSION['success_message'] = 'Booking updated successfully';
        header('Location: bookingmanagement.php');
        exit();
        
    } catch(PDOException $e) {
        $_SESSION['error_message'] = 'Database error: ' . $e->getMessage();
        header('Location: bookingmanagement.php?edit_id=' . $booking_id);
        exit();
    }
} else {
    $_SESSION['error_message'] = 'Invalid request method';
    header('Location: bookingmanagement.php');
    exit();
}
?>