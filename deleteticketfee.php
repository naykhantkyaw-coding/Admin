<?php
session_start();
include('includes/config.php');

if (isset($_GET['id'])) {
    $ticket_fee_id = $_GET['id'];
    
    try {
        // Delete the ticket fee
        $stmt = $pdo->prepare("DELETE FROM Tbl_TicketFees WHERE TicketFeesId = ?");
        $stmt->execute([$ticket_fee_id]);
        
        if ($stmt->rowCount() > 0) {
            $_SESSION['success_message'] = 'Ticket fee deleted successfully';
        } else {
            $_SESSION['error_message'] = 'Ticket fee not found or already deleted';
        }
        
    } catch(PDOException $e) {
        $_SESSION['error_message'] = 'Database error: ' . $e->getMessage();
    }
} else {
    $_SESSION['error_message'] = 'No ticket fee ID provided';
}

header('Location: ticketfeesmanagement.php');
exit();
?>