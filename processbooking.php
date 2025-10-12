<?php
include('includes/config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['error_message'] = 'Please login to book tickets.';
        header('Location: index.php');
        exit();
    }
    
    $user_id = $_SESSION['user_id'];
    $movie_id = $_POST['movie_id'];
    $selected_seats = $_POST['selected_seats'];
    $ticket_class = $_POST['ticket_class'];
    $quantity = $_POST['quantity'];
    $total_price = $_POST['total_price'];
    
    try {
        // Insert booking into database
        $stmt = $pdo->prepare("INSERT INTO Tbl_Booking (UserId, MovieId, TicketClass, SeatNo, Quantity, TotalPrice, Status) VALUES (?, ?, ?, ?, ?, ?, 'Pending')");
        $stmt->execute([$user_id, $movie_id, $ticket_class, $selected_seats, $quantity, $total_price]);
        
        $_SESSION['success_message'] = 'Booking confirmed successfully! Your seat numbers: ' . $selected_seats;
        header('Location: check-booking.php');
        exit();
        
    } catch(Exception $e) {
        $_SESSION['error_message'] = 'Error processing booking: ' . $e->getMessage();
        header('Location: movie-details.php?id=' . $movie_id);
        exit();
    }
} else {
    header('Location: movies.php');
    exit();
}
?>