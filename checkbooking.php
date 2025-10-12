<?php
include('includes/config.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    $_SESSION['error_message'] = 'Please login to view your bookings.';
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user's bookings with movie and user information
$stmt = $pdo->prepare("
    SELECT 
        b.*, 
        m.MovieTitle, 
        m.ImageUrl,
        m.Duration,
        m.Genrer,
        u.UserName,
        u.Email
    FROM Tbl_Booking b
    LEFT JOIN Tbl_Movies m ON b.MovieId = m.MovieId
    LEFT JOIN Tbl_Users u ON b.UserId = u.UserId
    WHERE b.UserId = ?
    ORDER BY b.BookingDate DESC
");
$stmt->execute([$user_id]);
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>My Bookings - MovieHub</title>
  <link rel="stylesheet" href="assets/css/home.css">
   <link rel="stylesheet" href="assets/css/checkbooking.css">
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

<!-- Navigation Bar -->
<nav class="navbar navbar-expand-lg navbar-custom">
    <div class="container">
        <a class="navbar-brand" href="home.php">
            <i class="fas fa-film me-2"></i>MovieHub
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="home.php">
                        <i class="fas fa-home"></i>Home
                    </a>
                </li>
                <li class="nav-item">
                </li>
                <li class="nav-item">
                </li>
            </ul>
            
            <div class="d-flex align-items-center">
                <!-- User Info -->
                <div class="user-info">
                    <i class="fas fa-user me-1"></i>Welcome, <?= htmlspecialchars($_SESSION['username'] ?? 'User') ?>
                </div>
                
                <!-- Logout Button -->
                <a href="index.php" class="btn btn-custom" onclick="return confirm('Are you sure you want to logout?')">
                    <i class="fas fa-sign-out-alt me-1"></i>Logout
                </a>
            </div>
        </div>
    </div>
</nav>

<div class="wrap">
    <h1 class="page-title">My Bookings</h1>
    
    <!-- Message Display Section -->
    <?php if (isset($_SESSION['success_message'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        <?= $_SESSION['success_message'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error_message'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>
        <?= $_SESSION['error_message'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['error_message']); ?>
    <?php endif; ?>
    
    <?php if (count($bookings) > 0): ?>
    <div class="bookings-list">
        <?php foreach($bookings as $booking): 
            // Determine status class
            $status_class = 'status-pending';
            switch(strtolower($booking['Status'])) {
                case 'confirmed':
                    $status_class = 'status-confirmed';
                    break;
                case 'completed':
                    $status_class = 'status-completed';
                    break;
                case 'cancelled':
                    $status_class = 'status-cancelled';
                    break;
                default:
                    $status_class = 'status-pending';
            }
            
            // Format dates
            $booking_date = date('M d, Y g:i A', strtotime($booking['BookingDate']));
            $release_date = date('M d, Y', strtotime($booking['ReleaseDate'] ?? ''));
            
            // Default image
            $movie_image = !empty($booking['ImageUrl']) ? $booking['ImageUrl'] : 'assets/img/movie/default-movie.jpg';
            
            // Calculate payment deadline for confirmed bookings
            $payment_deadline = '';
            if (strtolower($booking['Status']) == 'confirmed') {
                $booking_time = strtotime($booking['BookingDate']);
                $deadline_time = $booking_time + (2 * 60 * 60); // 2 hours from booking time
                $payment_deadline = date('M d, Y g:i A', $deadline_time);
            }
        ?>
        <div class="booking-card">
            <div class="booking-header">
                <div class="booking-id">
                    <i class="fas fa-ticket-alt me-2"></i>Booking Id - <?= $booking['BookingId'] ?>
                </div>
                <div class="booking-date">
                    <i class="fas fa-calendar me-1"></i><?= $booking_date ?>
                </div>
            </div>
            
            <div class="booking-body">
                <div class="movie-poster">
                    <img src="<?= $movie_image ?>" alt="<?= htmlspecialchars($booking['MovieTitle']) ?>">
                </div>
                
                <div class="booking-details">
                    <h3 class="movie-title"><?= htmlspecialchars($booking['MovieTitle']) ?></h3>
                    
                    <div class="movie-meta">
                        <?php if($booking['Genrer']): ?>
                        <div class="meta-item">
                            <i class="fas fa-tag"></i>
                            <span><?= htmlspecialchars($booking['Genrer']) ?></span>
                        </div>
                        <?php endif; ?>
                        
                        <?php if($booking['Duration']): ?>
                        <div class="meta-item">
                            <i class="fas fa-clock"></i>
                            <span><?= $booking['Duration'] ?> minutes</span>
                        </div>
                        <?php endif; ?>
                        
                        <?php if($booking['ReleaseDate']): ?>
                        <div class="meta-item">
                            <i class="fas fa-calendar-alt"></i>
                            <span>Released: <?= $release_date ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="booking-info">
                        <div class="info-item">
                            <div class="info-label">Ticket Class</div>
                            <div class="info-value">
                                <i class="fas fa-ticket me-1"></i>
                                <?= htmlspecialchars($booking['TicketClass']) ?>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">Seat Numbers</div>
                            <div class="info-value">
                                <i class="fas fa-chair me-1"></i>
                                <?= htmlspecialchars($booking['SeatNo']) ?>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">Quantity</div>
                            <div class="info-value">
                                <i class="fas fa-users me-1"></i>
                                <?= $booking['Quantity'] ?> tickets
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">Total Price</div>
                            <div class="info-value">
                                <i class="fas fa-dollar-sign me-1"></i>
                                $<?= number_format($booking['TotalPrice'], 2) ?>
                            </div>
                        </div>
                        
                        <div class="info-item">
                            <div class="info-label">Status</div>
                            <div class="info-value">
                                <span class="status-badge <?= $status_class ?>">
                                    <?= htmlspecialchars($booking['Status']) ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Payment Reminder for Confirmed Bookings -->
                    <?php if(strtolower($booking['Status']) == 'confirmed' && $payment_deadline): ?>
                    <div class="payment-reminder alert alert-warning mt-3">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-exclamation-triangle me-3 fs-4"></i>
                            <div>
                                <h6 class="mb-1 fw-bold">
                                    <i class="fas fa-clock me-1"></i>Payment Required
                                </h6>
                                <p class="mb-1">
                                    Please complete your payment at the counter within 2 hours of booking.
                                </p>
                                <p class="mb-0 fw-bold text-danger">
                                    <i class="fas fa-hourglass-end me-1"></i>
                                    Payment Deadline: <?= $payment_deadline ?>
                                </p>
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Your booking will be automatically cancelled if payment is not completed by this time.
                                </small>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if($booking['Status'] == 'Pending'): ?>
                    <div class="action-buttons">
                        <button class="btn btn-warning btn-sm" onclick="cancelBooking(<?= $booking['BookingId'] ?>)">
                            <i class="fas fa-times me-1"></i>Cancel Booking
                        </button>
                        <button class="btn btn-info btn-sm" onclick="contactSupport()">
                            <i class="fas fa-headset me-1"></i>Contact Support
                        </button>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="no-bookings">
        <i class="fas fa-ticket-alt"></i>
        <h3>No Bookings Found</h3>
        <p>You haven't made any bookings yet. Start by exploring our movie collection!</p>
        <a href="home.php" class="btn btn-primary mt-3">
            <i class="fas fa-play-circle me-2"></i>Browse Movies
        </a>
    </div>
<?php endif; ?>
</div>

<!-- Bootstrap JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
function cancelBooking(bookingId) {
    if (confirm('Are you sure you want to cancel this booking? This action cannot be undone.')) {
        // Redirect to cancel booking script
        window.location.href = `cancelbooking.php?id=${bookingId}`;
    }
}

function contactSupport() {
    alert('Please contact our support team at support@moviehub.com or call +1-555-0123 for assistance.');
}

// Auto-dismiss alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
});
</script>
</body>
</html>