<?php
session_start();
include('includes/config.php');

if (isset($_GET['id'])) {
    $movie_id = $_GET['id'];
    
    try {
        // Get movie image path before deletion
        $stmt = $pdo->prepare("SELECT ImageUrl FROM Tbl_Movies WHERE MovieId = ?");
        $stmt->execute([$movie_id]);
        $movie = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Delete the movie
        $stmt = $pdo->prepare("DELETE FROM Tbl_Movies WHERE MovieId = ?");
        $stmt->execute([$movie_id]);
        
        if ($stmt->rowCount() > 0) {
            // Delete associated image file
            if (!empty($movie['ImageUrl']) && file_exists('../' . $movie['ImageUrl'])) {
                unlink('../' . $movie['ImageUrl']);
            }
            $_SESSION['success_message'] = 'Movie deleted successfully';
        } else {
            $_SESSION['error_message'] = 'Movie not found or already deleted';
        }
        
    } catch(PDOException $e) {
        $_SESSION['error_message'] = 'Database error: ' . $e->getMessage();
    }
} else {
    $_SESSION['error_message'] = 'No movie ID provided';
}

header('Location: moviemanagement.php');
exit();
?>