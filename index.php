<?php
include('includes/config.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="assets/css/login.css" />
</head>
<body>
  <div class="container d-flex justify-content-center align-items-center min-vh-100">
    <div class="auth-card row">
      <!-- Left: Form -->
      <div class="col-md-6 form-section">
        <h2 class="text-center mb-4">Login</h2>
        
        <!-- Error Message Display -->
        <?php if (isset($_SESSION['error_message'])): ?>
          <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= $_SESSION['error_message'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
          <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['success_message'])): ?>
          <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $_SESSION['success_message'] ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
          <?php unset($_SESSION['success_message']); ?>
        <?php endif; ?>

        <form method="POST" action="loginprocess.php">
          <div class="mb-3">
            <input type="text" name="username" id="username" class="form-control" placeholder="Username" required />
          </div>
          <div class="mb-3">
            <input type="password" name="password" id="password" class="form-control" placeholder="Password" required />
          </div>
          <button type="submit" class="btn btn-custom w-100">Login</button>
        </form>
        <div class="extra-text mt-3 text-center">
          <p>Don't have an account? <a href="signup.php">Sign Up</a></p>
          <!-- <p><a href="forgot_password.php">Forgot Password?</a></p> -->
        </div>
      </div>

      <!-- Right: Welcome -->
      <div class="col-md-6 welcome-section d-none d-md-flex flex-column justify-content-center align-items-center">
        <h2>WELCOME BACK!</h2>
        <p>Please login to continue.</p>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>