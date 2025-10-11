<?php
session_start();
include('includes/config.php');
include('includes/auth.php');

// Get current user data
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM Tbl_Users WHERE UserId = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    $_SESSION['error_message'] = "User not found.";
    header("Location: dashboard.php");
    exit();
}

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_name = trim($_POST['user_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    try {
        // Verify current password if changing password
        if (!empty($new_password)) {
            if (!password_verify($current_password, $user['Password'])) {
                $_SESSION['error_message'] = "Current password is incorrect.";
                header("Location: profile.php");
                exit();
            }
            
            if ($new_password !== $confirm_password) {
                $_SESSION['error_message'] = "New passwords do not match.";
                header("Location: profile.php");
                exit();
            }
            
            if (strlen($new_password) < 6) {
                $_SESSION['error_message'] = "New password must be at least 6 characters long.";
                header("Location: profile.php");
                exit();
            }
            
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE Tbl_Users SET UserName = ?, Email = ?, PhoneNo = ?, Password = ? WHERE UserId = ?");
            $stmt->execute([$user_name, $email, $phone, $hashed_password, $user_id]);
        } else {
            // Update without changing password
            $stmt = $pdo->prepare("UPDATE Tbl_Users SET UserName = ?, Email = ?, PhoneNo = ? WHERE UserId = ?");
            $stmt->execute([$user_name, $email, $phone, $user_id]);
        }
        
        // Update session variables
        $_SESSION['username'] = $user_name;
        $_SESSION['email'] = $email;
        
        $_SESSION['success_message'] = 'Profile updated successfully';
        header('Location: profile.php');
        exit();
        
    } catch(PDOException $e) {
        $_SESSION['error_message'] = 'Database error: ' . $e->getMessage();
        header('Location: profile.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - MovieTicket Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- Your existing CSS files -->
</head>
<body class="backbodycolor">
    <!-- Include your header/navbar -->
    <?php include('includes/header.php'); ?>

    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="row">
            <div class="col-xl-12">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">My Profile</h5>
                        <a href="dashboard.php" class="btn btn-secondary">
                            <i class="icon-base ti tabler-arrow-left me-1"></i> Back to Dashboard
                        </a>
                    </div>
                    <div class="card-body">
                        <!-- Message Display -->
                        <?php if (isset($_SESSION['success_message'])): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="icon-base ti tabler-check me-2"></i>
                                <?= $_SESSION['success_message'] ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                            <?php unset($_SESSION['success_message']); ?>
                        <?php endif; ?>

                        <?php if (isset($_SESSION['error_message'])): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <i class="icon-base ti tabler-x me-2"></i>
                                <?= $_SESSION['error_message'] ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                            <?php unset($_SESSION['error_message']); ?>
                        <?php endif; ?>

                        <form method="POST" action="profile.php">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label" for="user_name">Username</label>
                                        <div class="input-group input-group-merge">
                                            <span class="input-group-text"><i class="icon-base ti tabler-user"></i></span>
                                            <input type="text" class="form-control" id="user_name" name="user_name" 
                                                   value="<?= htmlspecialchars($user['UserName']) ?>" required />
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label" for="email">Email</label>
                                        <div class="input-group input-group-merge">
                                            <span class="input-group-text"><i class="icon-base ti tabler-mail"></i></span>
                                            <input type="email" id="email" name="email" class="form-control" 
                                                   value="<?= htmlspecialchars($user['Email']) ?>" required />
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label" for="phone">Phone Number</label>
                                        <div class="input-group input-group-merge">
                                            <span class="input-group-text"><i class="icon-base ti tabler-phone"></i></span>
                                            <input type="text" id="phone" name="phone" class="form-control" 
                                                   value="<?= htmlspecialchars($user['PhoneNo']) ?>" />
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label" for="role">Role</label>
                                        <div class="input-group input-group-merge">
                                            <span class="input-group-text"><i class="icon-base ti tabler-user-shield"></i></span>
                                            <input type="text" class="form-control" value="<?= htmlspecialchars($user['Role']) ?>" readonly />
                                            <small class="form-text text-muted">Role cannot be changed</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label" for="status">Status</label>
                                        <div class="input-group input-group-merge">
                                            <span class="input-group-text"><i class="icon-base ti tabler-status-change"></i></span>
                                            <input type="text" class="form-control" value="<?= htmlspecialchars($user['Status']) ?>" readonly />
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label" for="created_date">Member Since</label>
                                        <div class="input-group input-group-merge">
                                            <span class="input-group-text"><i class="icon-base ti tabler-calendar"></i></span>
                                            <input type="text" class="form-control" 
                                                   value="<?= date('F j, Y', strtotime($user['CreatedDateTime'])) ?>" readonly />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr class="my-4">
                            <h6 class="mb-3">Change Password</h6>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label" for="current_password">Current Password</label>
                                        <input type="password" id="current_password" name="current_password" class="form-control" 
                                               placeholder="Enter current password" />
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label" for="new_password">New Password</label>
                                        <input type="password" id="new_password" name="new_password" class="form-control" 
                                               placeholder="Enter new password" />
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label" for="confirm_password">Confirm New Password</label>
                                        <input type="password" id="confirm_password" name="confirm_password" class="form-control" 
                                               placeholder="Confirm new password" />
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-12">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="icon-base ti tabler-check me-1"></i> Update Profile
                                    </button>
                                    <a href="dashboard.php" class="btn btn-outline-secondary">
                                        <i class="icon-base ti tabler-x me-1"></i> Cancel
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include('includes/footer.php'); ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>