<?php 
include('includes/config.php'); 

$stmt = $pdo->query("SELECT * FROM Tbl_Users");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get user data for editing if user_id is provided
$edit_user = null;
if (isset($_GET['edit_id'])) {
    $edit_id = $_GET['edit_id'];
    $stmt = $pdo->prepare("SELECT * FROM Tbl_Users WHERE UserID = ?");
    $stmt->execute([$edit_id]);
    $edit_user = $stmt->fetch(PDO::FETCH_ASSOC);
}

?>
<?php 
include('includes/header.php'); 
?>

<div class="text-body">
    <h4>User Management</h4>
</div>

<!-- Message Display Section -->
<div class="text-body">
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
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteConfirmModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-warning"><i class="icon-base ti tabler-alert-triangle me-2"></i>Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this user?</p>
                <p class="text-danger"><strong>This action cannot be undone!</strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                    <i class="icon-base ti tabler-x me-1"></i> Cancel
                </button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                    <i class="icon-base ti tabler-trash me-1"></i> Delete
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Listing Form (Initially Visible) -->
<div id="listingForm" style="display: <?php echo $edit_user ? 'none' : 'block'; ?>;">
    <div class="card">
        <div class="table-responsive text-nowrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>User Name</th>
                        <th>Email</th>
                        <th>Phone Number</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    <?php foreach ($users as $user): 
                        $status_class = 'bg-label-secondary';
                        $status_text = strtolower($user['Status'] ?? '');
                        
                        switch($status_text) {
                            case 'approved':
                                $status_class = 'bg-label-success';
                                break;
                            case 'pending':
                                $status_class = 'bg-label-warning';
                                break;
                            case 'rejected':
                                $status_class = 'bg-label-danger';
                                break;
                            default:
                                $status_class = 'bg-label-secondary';
                        }
                    ?>
                    <tr>
                        <td><span> <img src="assets/img/favicon/favicon.ico" alt class="avatar rounded-circle" /></span><?= $user['UserName'] ?></td>
                        <td><?= htmlspecialchars($user['Email']) ?></td>
                        <td><?= htmlspecialchars($user['PhoneNo']) ?></td>
                        <td><span class="badge bg-label-primary me-1"><?= htmlspecialchars($user['Role']) ?></span></td>
                        <td><span class="badge <?= $status_class ?> me-1"><?= htmlspecialchars($user['Status']) ?></span></td>
                        <td>
                            <div class="dropdown">
                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                    <i class="icon-base ti tabler-dots-vertical"></i>
                                </button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item text-warning" href="?edit_id=<?= $user['UserId'] ?>">
                                        <i class="icon-base ti tabler-pencil me-1"></i> Edit
                                    </a>
                                    <a class="dropdown-item text-danger delete-user" href="javascript:void(0);" 
                                       data-userid="<?= $user['UserId'] ?>" 
                                       data-username="<?= htmlspecialchars($user['UserName']) ?>">
                                        <i class="icon-base ti tabler-trash me-1"></i> Delete
                                    </a>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Edit Form (Initially Hidden) -->
<div id="editForm" style="display: <?php echo $edit_user ? 'block' : 'none'; ?>;">
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Edit User - <?= $edit_user ? htmlspecialchars($edit_user['UserName']) : '' ?></h5>
                    <button type="button" class="btn btn-secondary" onclick="showListing()">
                        <i class="icon-base ti tabler-arrow-left me-1"></i> Back to List
                    </button>
                </div>
                <div class="card-body">
                    <?php if ($edit_user): ?>
                    <form method="POST" action="updateuser.php">
                        <input type="hidden" name="user_id" value="<?= $edit_user['UserId'] ?>">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label" for="user_name">Full Name</label>
                                    <div class="input-group input-group-merge">
                                        <span class="input-group-text"><i class="icon-base ti tabler-user"></i></span>
                                        <input type="text" class="form-control" id="user_name" name="user_name" 
                                               value="<?= htmlspecialchars($edit_user['UserName']) ?>" placeholder="John Doe" required />
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label" for="email">Email</label>
                                    <div class="input-group input-group-merge">
                                        <span class="input-group-text"><i class="icon-base ti tabler-mail"></i></span>
                                        <input type="email" id="email" name="email" class="form-control" 
                                               value="<?= htmlspecialchars($edit_user['Email']) ?>" placeholder="john.doe@example.com" required />
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label" for="phone">Phone No</label>
                                    <div class="input-group input-group-merge">
                                        <span class="input-group-text"><i class="icon-base ti tabler-phone"></i></span>
                                        <input type="text" id="phone" name="phone" class="form-control phone-mask" 
                                               value="<?= htmlspecialchars($edit_user['PhoneNo']) ?>" placeholder="658 799 8941" />
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label" for="role">Role</label>
                                    <div class="input-group input-group-merge">
                                        <span class="input-group-text"><i class="icon-base ti tabler-user-shield"></i></span>
                                        <select class="form-select" id="role" name="role" required>
                                            <option value="User" <?= $edit_user['Role'] == 'User' ? 'selected' : '' ?>>User</option>
                                            <option value="Admin" <?= $edit_user['Role'] == 'Admin' ? 'selected' : '' ?>>Admin</option>
                                        </select>
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
                                        <select class="form-select" id="status" name="status" required>
                                            <option value="Pending" <?= $edit_user['Status'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
                                            <option value="Approved" <?= $edit_user['Status'] == 'Approved' ? 'selected' : '' ?>>Approved</option>
                                            <option value="Rejected" <?= $edit_user['Status'] == 'Rejected' ? 'selected' : '' ?>>Rejected</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label" for="password">New Password (Leave blank to keep current)</label>
                                    <div class="input-group input-group-merge">
                                        <span class="input-group-text"><i class="icon-base ti tabler-lock"></i></span>
                                        <input type="password" id="password" name="password" class="form-control" placeholder="Enter new password" />
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="icon-base ti tabler-check me-1"></i> Update User
                                </button>
                                <button type="button" class="btn btn-outline-secondary" onclick="showListing()">
                                    <i class="icon-base ti tabler-x me-1"></i> Cancel
                                </button>
                            </div>
                        </div>
                    </form>
                    <?php else: ?>
                    <div class="alert alert-warning">User not found.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function showListing() {
    // Redirect back to listing without edit parameter
    window.location.href = window.location.pathname;
}

// Delete functionality
document.addEventListener('DOMContentLoaded', function() {
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
    let userIdToDelete = null;
    let userNameToDelete = null;

    // When delete link is clicked
    document.querySelectorAll('.delete-user').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            userIdToDelete = this.getAttribute('data-userid');
            userNameToDelete = this.getAttribute('data-username');
            
            // Update modal content with user name
            document.querySelector('#deleteConfirmModal .modal-body p:first-child').textContent = 
                `Are you sure you want to delete user "${userNameToDelete}"?`;
            
            // Show the modal
            deleteModal.show();
        });
    });

    // When confirm delete button is clicked
    document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
        if (userIdToDelete) {
            // Redirect to delete script
            window.location.href = `deleteuser.php?id=${userIdToDelete}`;
        }
    });

    // Reset when modal is hidden
    document.getElementById('deleteConfirmModal').addEventListener('hidden.bs.modal', function() {
        userIdToDelete = null;
        userNameToDelete = null;
    });
});
</script>

<?php include('includes/footer.php') ?>