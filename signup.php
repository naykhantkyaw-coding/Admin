<?php
include('includes/config.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Sign Up</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="assets/css/signup.css" />
</head>
<body>
  <div class="container d-flex justify-content-center align-items-center min-vh-100">
    <div class="auth-card row">
      <!-- Left: Welcome -->
      <div class="col-md-6 welcome-section d-none d-md-flex flex-column justify-content-center align-items-center">
        <h2>HELLO, FRIEND!</h2>
        <p>Enter your details to create an account.</p>
      </div>

      <!-- Right: Form -->
      <div class="col-md-6 form-section">
        <h2 class="text-center mb-4">Sign Up</h2>
        
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

        <form method="POST" action="signupprocess.php">
          <div class="mb-3">
            <input type="text" name="full_name" class="form-control" placeholder="Full Name" required />
          </div>
          <div class="mb-3">
            <input type="email" name="email" class="form-control" placeholder="Email" required />
          </div>
          <div class="mb-3">
            <input type="tel" name="phone" id="phone" class="form-control"
                   placeholder="Phone Number"
                   pattern="^09\d{7,9}$"
                   title="Phone number must start with 09 and be 9–11 digits long"
                   required />
          </div>
          <div class="mb-3">
            <input type="text" name="username" class="form-control" placeholder="Username" required />
          </div>
          <div class="mb-3">
            <input type="password" name="password" id="password" class="form-control" placeholder="Password" required />
          </div>
          <div class="mb-3">
            <input type="password" name="confirm_password" id="confirmPassword" class="form-control" placeholder="Confirm Password" required />
          </div>
          <button type="submit" class="btn btn-custom w-100">Sign Up</button>
        </form>
        <div class="extra-text mt-3 text-center">
          <p>Already have an account? <a href="index.php">Login</a></p>
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
  // Client-side validation
  document.getElementById('signupForm').addEventListener('submit', function(event) {
    const password = document.getElementById("password").value.trim();
    const confirmPassword = document.getElementById("confirmPassword").value.trim();
    const phone = document.getElementById("phone").value.trim();

    // Password check
    if (password !== confirmPassword) {
      event.preventDefault();
      alert("Passwords do not match!");
      return false;
    }

    // Phone format check
    const phonePattern = /^09\d{7,9}$/;
    if (!phonePattern.test(phone)) {
      event.preventDefault();
      alert("Please enter a valid phone number that starts with 09 and is 9–11 digits long.");
      return false;
    }

    // Password length check
    if (password.length < 6) {
      event.preventDefault();
      alert("Password must be at least 6 characters long.");
      return false;
    }

    return true;
  });
  </script>
</body>
</html>