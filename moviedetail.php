<?php
include('includes/config.php');

if (isset($_GET['id'])) {
    $movie_id = $_GET['id'];
    $stmt = $pdo->prepare("SELECT * FROM Tbl_Movies WHERE MovieId = ?");
    $stmt->execute([$movie_id]);
    $movie = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$movie) {
        header('Location: movies.php');
        exit();
    }
} else {
    header('Location: movies.php');
    exit();
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= htmlspecialchars($movie['MovieTitle']) ?> - Movie Details</title>
  <link rel="stylesheet" href="assets/css/home.css">
  <link rel="stylesheet" href="assets/css/moviedetail.css">
 <!-- Bootstrap CSS for better navbar styling -->
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
          </li>
          <li class="nav-item">
          </li>
        </ul>
        
        <div class="d-flex align-items-center">
          <!-- User Info (if logged in) -->
          <?php if(isset($_SESSION['user_id'])): ?>
          <div class="user-info">
            <i class="fas fa-user me-1"></i>Welcome, <?= htmlspecialchars($_SESSION['username'] ?? 'User') ?>
          </div>
          <?php endif; ?>
          
          <!-- Check Booking Button -->
          <a href="check-booking.php" class="btn btn-outline-light me-2">
            <i class="fas fa-ticket-alt me-1"></i>Check Booking
          </a>
          
          <!-- Logout Button -->
          <?php if(isset($_SESSION['user_id'])): ?>
          <a href="index.php" class="btn btn-custom" onclick="return confirm('Are you sure you want to logout?')">
            <i class="fas fa-sign-out-alt me-1"></i>Logout
          </a>
          <?php else: ?>
          <a href="login.php" class="btn btn-custom">
            <i class="fas fa-sign-in-alt me-1"></i>Login
          </a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </nav>
  <div class="wrap">
    <a href="home.php" class="back-button">← Back to Movies</a>
    
    <div class="movie-detail">
      <div class="movie-header">
        <div class="movie-poster">
          <?php
          $movie_image = !empty($movie['ImageUrl']) ? $movie['ImageUrl'] : 'assets/img/movie/default-movie.jpg';
          ?>
          <img src="<?= $movie_image ?>" alt="<?= htmlspecialchars($movie['MovieTitle']) ?>">
        </div>
        <div class="movie-info">
          <h1><?= htmlspecialchars($movie['MovieTitle']) ?></h1>
          <div class="movie-meta">
            <p><strong>Genre:</strong> <?= htmlspecialchars($movie['Genrer']) ?></p>
            <p><strong>Duration:</strong> <?= htmlspecialchars($movie['Duration']) ?> minutes</p>
            <p><strong>Release Date:</strong> <?= date('M d, Y', strtotime($movie['ReleaseDate'])) ?></p>
            <p><strong>Status:</strong> <?= htmlspecialchars($movie['Status']) ?></p>
          </div>
          <div class="movie-description">
            <h3>Description</h3>
            <p><?= nl2br(htmlspecialchars($movie['Description'])) ?></p>
          </div>
          <!-- Add booking button or other actions here -->
        </div>
      </div>
    </div>
  </div>
</body>
</html>