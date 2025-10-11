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
        <form id="signupForm" onsubmit="return validateSignup(event)">
          <input type="text" name="full_name" class="form-control mb-3" placeholder="Full Name" required />
          <input type="email" name="email" class="form-control mb-3" placeholder="Email" required />

          <!-- ✅ Updated Phone Field -->
          <input type="tel" name="phone" id="phone" class="form-control mb-3"
                 placeholder="Phone Number"
                 pattern="^09\d{7,9}$"
                 title="Phone number must start with 09 and be 9–11 digits long"
                 required />

          <input type="text" name="username" class="form-control mb-3" placeholder="Username" required />
          <input type="password" name="password" id="password" class="form-control mb-3" placeholder="Password" required />
          <input type="password" id="confirmPassword" class="form-control mb-3" placeholder="Confirm Password" required />
          <button type="submit" class="btn btn-custom w-100">Sign Up</button>
        </form>
        <div class="extra-text mt-3 text-center">
          <p>Already have an account? <a href="index.php">Login</a></p>
        </div>
      </div>
    </div>
  </div>

  <script>
  function validateSignup(event) {
    event.preventDefault(); // Prevent normal form submission

    const password = document.getElementById("password").value.trim();
    const confirmPassword = document.getElementById("confirmPassword").value.trim();
    const phone = document.getElementById("phone").value.trim();

    // Password check
    if (password !== confirmPassword) {
      alert("Passwords do not match!");
      return false;
    }

    // Phone format check (must start with 09 and be 9–11 digits)
    const phonePattern = /^09\d{7,9}$/;
    if (!phonePattern.test(phone)) {
      alert("Please enter a valid phone number that starts with 09 and is 9–11 digits long.");
      return false;
    }

    alert("Account created successfully!");
    window.location.href = "login.php"; // Redirect to login
    return true;
  }
  </script>
</body>
</html>
