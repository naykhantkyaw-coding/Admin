<?php 
include('includes/config.php'); 
include('includes/auth.php');

// Fetch statistics from database
try {
    // Total Users
    $stmt = $pdo->query("SELECT COUNT(*) as total_users FROM Tbl_Users");
    $total_users = $stmt->fetch(PDO::FETCH_ASSOC)['total_users'];

    // Total Movies
    $stmt = $pdo->query("SELECT COUNT(*) as total_movies FROM Tbl_Movies");
    $total_movies = $stmt->fetch(PDO::FETCH_ASSOC)['total_movies'];

    // Total Bookings
    $stmt = $pdo->query("SELECT COUNT(*) as total_bookings FROM Tbl_Booking");
    $total_bookings = $stmt->fetch(PDO::FETCH_ASSOC)['total_bookings'];

    // Total Revenue
    $stmt = $pdo->query("SELECT SUM(TotalPrice) as total_revenue FROM Tbl_Booking WHERE Status IN ('Confirmed', 'Completed')");
    $total_revenue = $stmt->fetch(PDO::FETCH_ASSOC)['total_revenue'] ?? 0;

    // Recent Bookings (last 7 days)
    $stmt = $pdo->query("
        SELECT COUNT(*) as recent_bookings 
        FROM Tbl_Booking 
        WHERE BookingDate >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    ");
    $recent_bookings = $stmt->fetch(PDO::FETCH_ASSOC)['recent_bookings'];

    // Pending Bookings
    $stmt = $pdo->query("SELECT COUNT(*) as pending_bookings FROM Tbl_Booking WHERE Status = 'Pending'");
    $pending_bookings = $stmt->fetch(PDO::FETCH_ASSOC)['pending_bookings'];

    // Confirmed Bookings
    $stmt = $pdo->query("SELECT COUNT(*) as confirmed_bookings FROM Tbl_Booking WHERE Status = 'Confirmed'");
    $confirmed_bookings = $stmt->fetch(PDO::FETCH_ASSOC)['confirmed_bookings'];

    // User status counts
    $stmt = $pdo->query("SELECT Status, COUNT(*) as count FROM Tbl_Users GROUP BY Status");
    $user_status_counts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Booking status counts for chart
    $stmt = $pdo->query("
        SELECT Status, COUNT(*) as count 
        FROM Tbl_Booking 
        WHERE BookingDate >= DATE_SUB(NOW(), INTERVAL 7 DAY)
        GROUP BY Status
    ");
    $booking_status_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Recent movies added
    $stmt = $pdo->query("
        SELECT MovieTitle, CreatedDateTime 
        FROM Tbl_Movies 
        ORDER BY CreatedDateTime DESC 
        LIMIT 5
    ");
    $recent_movies = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Popular movies by bookings
    $stmt = $pdo->query("
        SELECT m.MovieTitle, COUNT(b.BookingId) as booking_count
        FROM Tbl_Movies m
        LEFT JOIN Tbl_Booking b ON m.MovieId = b.MovieId
        GROUP BY m.MovieId, m.MovieTitle
        ORDER BY booking_count DESC
        LIMIT 5
    ");
    $popular_movies = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    // Handle database errors
    $total_users = $total_movies = $total_bookings = $total_revenue = 0;
    $recent_bookings = $pending_bookings = $confirmed_bookings = 0;
    $user_status_counts = $booking_status_data = $recent_movies = $popular_movies = [];
}
?>
<?php 
include('includes/header.php'); 
?>

<div class="text-body">
    <h4>Dashboard</h4>
</div>

<!-- Statistics Cards -->
<div class="row">
    <!-- Total Users -->
    <div class="col-12 col-sm-6 col-md-3 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div class="card-info">
                        <p class="card-text">Total Users</p>
                        <div class="d-flex align-items-end">
                            <h4 class="card-title mb-0 me-2"><?= $total_users ?></h4>
                        </div>
                    </div>
                    <div class="card-icon">
                        <span class="badge bg-label-primary rounded p-2">
                            <i class="icon-base ti tabler-users icon-lg"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Movies -->
    <div class="col-12 col-sm-6 col-md-3 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div class="card-info">
                        <p class="card-text">Total Movies</p>
                        <div class="d-flex align-items-end">
                            <h4 class="card-title mb-0 me-2"><?= $total_movies ?></h4>
                        </div>
                    </div>
                    <div class="card-icon">
                        <span class="badge bg-label-success rounded p-2">
                            <i class="icon-base ti tabler-movie icon-lg"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Bookings -->
    <div class="col-12 col-sm-6 col-md-3 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div class="card-info">
                        <p class="card-text">Total Bookings</p>
                        <div class="d-flex align-items-end">
                            <h4 class="card-title mb-0 me-2"><?= $total_bookings ?></h4>
                        </div>
                    </div>
                    <div class="card-icon">
                        <span class="badge bg-label-info rounded p-2">
                            <i class="icon-base ti tabler-ticket icon-lg"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Total Revenue -->
    <div class="col-12 col-sm-6 col-md-3 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div class="card-info">
                        <p class="card-text">Total Revenue</p>
                        <div class="d-flex align-items-end">
                            <h4 class="card-title mb-0 me-2">$<?= number_format($total_revenue, 2) ?></h4>
                        </div>
                    </div>
                    <div class="card-icon">
                        <span class="badge bg-label-warning rounded p-2">
                            <i class="icon-base ti tabler-currency-dollar icon-lg"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Booking Statistics -->
    <div class="col-12 col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between">
                <div class="card-title mb-0">
                    <h5 class="mb-1">Booking Statistics</h5>
                    <p class="card-subtitle">Last 7 Days</p>
                </div>
                <div class="dropdown">
                    <button class="btn btn-text-secondary rounded-pill text-body-secondary border-0 p-2 me-n1" type="button" id="bookingTrackerMenu" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="icon-base ti tabler-dots-vertical icon-md text-body-secondary"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="bookingTrackerMenu">
                        <a class="dropdown-item" href="bookingmanagement.php">View All Bookings</a>
                        <a class="dropdown-item" href="?refresh=1">Refresh</a>
                    </div>
                </div>
            </div>
            <div class="card-body row">
                <div class="col-12 col-sm-4">
                    <div class="mt-lg-4 mt-lg-2 mb-lg-6 mb-2">
                        <h2 class="mb-0"><?= $recent_bookings ?></h2>
                        <p class="mb-0">Recent Bookings</p>
                    </div>
                    <ul class="p-0 m-0">
                        <li class="d-flex gap-4 align-items-center mb-lg-3 pb-1">
                            <div class="badge rounded bg-label-primary p-1_5"><i class="icon-base ti tabler-ticket icon-md"></i></div>
                            <div>
                                <h6 class="mb-0 text-nowrap">Pending Bookings</h6>
                                <small class="text-body-secondary"><?= $pending_bookings ?></small>
                            </div>
                        </li>
                        <li class="d-flex gap-4 align-items-center mb-lg-3 pb-1">
                            <div class="badge rounded bg-label-success p-1_5"><i class="icon-base ti tabler-circle-check icon-md"></i></div>
                            <div>
                                <h6 class="mb-0 text-nowrap">Confirmed Bookings</h6>
                                <small class="text-body-secondary"><?= $confirmed_bookings ?></small>
                            </div>
                        </li>
                        <li class="d-flex gap-4 align-items-center pb-1">
                            <div class="badge rounded bg-label-warning p-1_5"><i class="icon-base ti tabler-currency-dollar icon-md"></i></div>
                            <div>
                                <h6 class="mb-0 text-nowrap">Total Revenue</h6>
                                <small class="text-body-secondary">$<?= number_format($total_revenue, 2) ?></small>
                            </div>
                        </li>
                    </ul>
                </div>
                <div class="col-12 col-md-8">
                    <div id="bookingChart" style="min-height: 200px;">
                        <!-- Simple chart using CSS -->
                        <div class="d-flex align-items-end justify-content-around h-100 py-3">
                            <?php 
                            $status_colors = [
                                'Pending' => 'bg-label-warning',
                                'Confirmed' => 'bg-label-success', 
                                'Completed' => 'bg-label-primary',
                                'Cancelled' => 'bg-label-danger'
                            ];
                            
                            $max_count = 0;
                            foreach ($booking_status_data as $data) {
                                if ($data['count'] > $max_count) $max_count = $data['count'];
                            }
                            
                            foreach ($booking_status_data as $data): 
                                $height = $max_count > 0 ? ($data['count'] / $max_count) * 100 : 0;
                            ?>
                            <div class="d-flex flex-column align-items-center">
                                <div class="chart-bar <?= $status_colors[$data['Status']] ?? 'bg-label-secondary' ?> rounded-top" 
                                     style="height: <?= $height ?>%; width: 30px;"></div>
                                <small class="mt-2 text-center"><?= $data['Status'] ?></small>
                                <small class="text-body-secondary"><?= $data['count'] ?></small>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- User Statistics -->
    <div class="col-12 col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between">
                <div class="card-title mb-0">
                    <h5 class="mb-1">User Statistics</h5>
                    <p class="card-subtitle">All Users</p>
                </div>
                <div class="dropdown">
                    <button class="btn btn-text-secondary rounded-pill text-body-secondary border-0 p-2 me-n1" type="button" id="userStatsMenu" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="icon-base ti tabler-dots-vertical icon-md text-body-secondary"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="userStatsMenu">
                        <a class="dropdown-item" href="usermanagement.php">View All Users</a>
                        <a class="dropdown-item" href="?refresh=1">Refresh</a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php foreach ($user_status_counts as $status): 
                        $status_class = 'bg-label-secondary';
                        switch(strtolower($status['Status'])) {
                            case 'approved': $status_class = 'bg-label-success'; break;
                            case 'pending': $status_class = 'bg-label-warning'; break;
                            case 'rejected': $status_class = 'bg-label-danger'; break;
                        }
                    ?>
                    <div class="col-6 col-sm-4 mb-3">
                        <div class="d-flex align-items-center">
                            <div class="badge rounded <?= $status_class ?> p-2 me-3">
                                <i class="icon-base ti tabler-user icon-md"></i>
                            </div>
                            <div>
                                <h6 class="mb-0"><?= $status['Status'] ?></h6>
                                <small class="text-body-secondary"><?= $status['count'] ?> users</small>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Recent Movies -->
    <div class="col-12 col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">Recent Movies Added</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-borderless">
                        <thead>
                            <tr>
                                <th>Movie Title</th>
                                <th>Added Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_movies as $movie): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm me-3">
                                            <span class="avatar-initial rounded bg-label-primary">
                                                <i class="icon-base ti tabler-movie"></i>
                                            </span>
                                        </div>
                                        <div>
                                            <h6 class="mb-0"><?= htmlspecialchars($movie['MovieTitle']) ?></h6>
                                        </div>
                                    </div>
                                </td>
                                <td><?= date('M d, Y', strtotime($movie['CreatedDateTime'])) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Popular Movies -->
    <div class="col-12 col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="card-title mb-0">Popular Movies</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-borderless">
                        <thead>
                            <tr>
                                <th>Movie Title</th>
                                <th>Bookings</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($popular_movies as $movie): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm me-3">
                                            <span class="avatar-initial rounded bg-label-success">
                                                <i class="icon-base ti tabler-star"></i>
                                            </span>
                                        </div>
                                        <div>
                                            <h6 class="mb-0"><?= htmlspecialchars($movie['MovieTitle']) ?></h6>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-label-primary"><?= $movie['booking_count'] ?> bookings</span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.chart-bar {
    transition: height 0.3s ease;
    min-height: 20px;
}
</style>

<script>
// Simple animation for chart bars
document.addEventListener('DOMContentLoaded', function() {
    const chartBars = document.querySelectorAll('.chart-bar');
    chartBars.forEach(bar => {
        const originalHeight = bar.style.height;
        bar.style.height = '0%';
        setTimeout(() => {
            bar.style.height = originalHeight;
        }, 100);
    });
});
</script>

<?php include('includes/footer.php') ?>