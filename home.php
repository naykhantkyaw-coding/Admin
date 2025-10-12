<?php
include('includes/config.php');
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Movies - Now Showing & Upcoming</title>
  <link rel="stylesheet" href="assets/css/home.css">
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
            <a class="nav-link" href="#now-showing">
              <i class="fas fa-play-circle"></i>Now Showing
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#upcoming">
              <i class="fas fa-calendar-alt"></i>Upcoming
            </a>
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
    <h1 class="section-title" id="now-showing">Now Showing</h1>

    <div class="movie-grid">
      <?php
      // Fetch active/released movies for customer view
      $stmt = $pdo->query("SELECT * FROM Tbl_Movies WHERE Status = 'Active' OR Status = 'Released' ORDER BY ReleaseDate DESC");
      $nowShowingMovies = $stmt->fetchAll(PDO::FETCH_ASSOC);
      
      if (count($nowShowingMovies) > 0):
        foreach($nowShowingMovies as $movie):
          // Default image if not set
          $movie_image = !empty($movie['ImageUrl']) ? $movie['ImageUrl'] : 'assets/img/movie/default-movie.jpg';
          
          // Format release date
          $release_date = date('M d, Y', strtotime($movie['ReleaseDate']));
          
          // Determine status class
          $status_class = 'status-released';
          if ($movie['Status'] == 'Upcoming') {
            $status_class = 'status-upcoming';
          }
      ?>
      <a class="movie-card" href="moviedetail.php?id=<?= $movie['MovieId'] ?>">
        <div class="movie-image">
          <img src="<?= $movie_image ?>" alt="<?= htmlspecialchars($movie['MovieTitle']) ?>">
        </div>
        <div class="movie-caption">
          <h3><?= htmlspecialchars($movie['MovieTitle']) ?></h3>
          <div class="movie-meta">
            <span class="movie-genre"><?= htmlspecialchars($movie['Genrer']) ?></span>
            <span class="movie-status <?= $status_class ?>"><?= htmlspecialchars($movie['Status']) ?></span>
          </div>
          <div class="movie-meta">
            <span><?= htmlspecialchars($movie['Duration']) ?> min</span>
            <span><?= $release_date ?></span>
          </div>
        </div>
      </a>
      <?php 
        endforeach;
      else:
      ?>
      <div class="no-movies">
        <p>No movies currently showing. Check back soon!</p>
      </div>
      <?php endif; ?>
    </div>

    <h1 class="section-title" id="upcoming">Upcoming Movies</h1>

    <div class="movie-grid">
      <?php
      // Fetch upcoming movies for customer view
      $stmt = $pdo->query("SELECT * FROM Tbl_Movies WHERE Status = 'Upcoming' ORDER BY ReleaseDate ASC");
      $upcomingMovies = $stmt->fetchAll(PDO::FETCH_ASSOC);
      
      if (count($upcomingMovies) > 0):
        foreach($upcomingMovies as $movie):
          // Default image if not set
          $movie_image = !empty($movie['ImageUrl']) ? $movie['ImageUrl'] : 'assets/img/movie/default-movie.jpg';
          
          // Format release date
          $release_date = date('M d, Y', strtotime($movie['ReleaseDate']));
          
          // Calculate days until release
          $today = new DateTime();
          $release = new DateTime($movie['ReleaseDate']);
          $days_until = $today->diff($release)->days;
          
          // Countdown text
          $countdown_text = '';
          if ($days_until == 0) {
            $countdown_text = 'Releases today!';
          } else if ($days_until == 1) {
            $countdown_text = 'Releases tomorrow!';
          } else {
            $countdown_text = "In $days_until days";
          }
      ?>
      <a class="movie-card" href="moviedetail.php?id=<?= $movie['MovieId'] ?>">
        <div class="movie-image">
          <img src="<?= $movie_image ?>" alt="<?= htmlspecialchars($movie['MovieTitle']) ?>">
          <div class="upcoming-badge">Coming Soon</div>
          <div class="countdown"><?= $countdown_text ?></div>
        </div>
        <div class="movie-caption">
          <h3><?= htmlspecialchars($movie['MovieTitle']) ?></h3>
          <div class="movie-meta">
            <span class="movie-genre"><?= htmlspecialchars($movie['Genrer']) ?></span>
            <span class="movie-status status-upcoming">Upcoming</span>
          </div>
          <div class="movie-meta">
            <span><?= htmlspecialchars($movie['Duration']) ?> min</span>
            <span><?= $release_date ?></span>
          </div>
        </div>
      </a>
      <?php 
        endforeach;
      else:
      ?>
      <div class="no-movies">
        <p>No upcoming movies announced yet. Stay tuned!</p>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Bootstrap JavaScript -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>