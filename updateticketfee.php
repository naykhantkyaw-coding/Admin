<?php
session_start();
include('includes/config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ticket_fee_id = $_POST['ticket_fee_id'] ?? '';
    $movie_id = $_POST['movie_id'] ?? '';
    $ticket_class = $_POST['ticket_class'] ?? '';
    $price = $_POST['price'] ?? '';
    
    try {
        // Check if ticket fee already exists for this movie and class (for new entries)
        if (empty($ticket_fee_id)) {
            $check_stmt = $pdo->prepare("SELECT TicketFeesId FROM Tbl_TicketFees WHERE MovieId = ? AND TicketClass = ?");
            $check_stmt->execute([$movie_id, $ticket_class]);
            $existing_fee = $check_stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($existing_fee) {
                throw new Exception('Ticket fee already exists for this movie and ticket class combination.');
            }
        }
        
        if (empty($ticket_fee_id)) {
            // Insert new ticket fee
            $stmt = $pdo->prepare("INSERT INTO Tbl_TicketFees (MovieId, TicketClass, Price) VALUES (?, ?, ?)");
            $stmt->execute([$movie_id, $ticket_class, $price]);
            $_SESSION['success_message'] = 'Ticket fee added successfully';
        } else {
            // Update existing ticket fee
            $stmt = $pdo->prepare("UPDATE Tbl_TicketFees SET MovieId = ?, TicketClass = ?, Price = ? WHERE TicketFeesId = ?");
            $stmt->execute([$movie_id, $ticket_class, $price, $ticket_fee_id]);
            $_SESSION['success_message'] = 'Ticket fee updated successfully';
        }
        
        header('Location: ticketfeesmanagement.php');
        exit();
        
    } catch(Exception $e) {
        $_SESSION['error_message'] = $e->getMessage();
        if (!empty($ticket_fee_id)) {
            header('Location: ticketfeesmanagement.php?edit_id=' . $ticket_fee_id);
        } else {
            header('Location: ticketfeesmanagement.php?add_new=1');
        }
        exit();
    }
} else {
    $_SESSION['error_message'] = 'Invalid request method';
    header('Location: ticketfeesmanagement.php');
    exit();
}
?>