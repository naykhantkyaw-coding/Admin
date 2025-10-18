<?php 
include('includes/config.php'); 

// Fetch all ticket fees with movie information using JOIN
$stmt = $pdo->query("
    SELECT tf.*, m.MovieTitle 
    FROM Tbl_TicketFees tf 
    LEFT JOIN Tbl_Movies m ON tf.MovieId = m.MovieId 
    ORDER BY tf.TicketFeesId DESC
");
$ticket_fees = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch all movies for dropdown
$movies_stmt = $pdo->query("SELECT MovieId, MovieTitle FROM Tbl_Movies WHERE Status = 'Active' ORDER BY MovieTitle");
$movies = $movies_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get ticket fee data for editing if ticket_fee_id is provided
$edit_ticket_fee = null;
if (isset($_GET['edit_id'])) {
    $edit_id = $_GET['edit_id'];
    $stmt = $pdo->prepare("
        SELECT tf.*, m.MovieTitle 
        FROM Tbl_TicketFees tf 
        LEFT JOIN Tbl_Movies m ON tf.MovieId = m.MovieId 
        WHERE tf.TicketFeesId = ?
    ");
    $stmt->execute([$edit_id]);
    $edit_ticket_fee = $stmt->fetch(PDO::FETCH_ASSOC);
}

?>
<?php 
include('includes/header.php'); 
?>

<div class="text-body">
    <h4>Ticket Fees Management</h4>
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
                <p>Are you sure you want to delete this ticket fee?</p>
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

<!-- Add New Ticket Fee Button -->
<div class="mb-4">
    <a href="?add_new=1" class="btn btn-primary">
        <i class="icon-base ti tabler-plus me-1"></i> Add New Ticket Fee
    </a>
</div>

<!-- Listing Form (Initially Visible) -->
<div id="listingForm" style="display: <?php echo ($edit_ticket_fee || isset($_GET['add_new'])) ? 'none' : 'block'; ?>;">
    <div class="card">
        <div class="table-responsive text-nowrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Movie Title</th>
                        <th>Ticket Class</th>
                        <th>Price</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    <?php foreach ($ticket_fees as $fee): ?>
                    <tr>
                        <td>
                            <strong><?= htmlspecialchars($fee['MovieTitle'] ?? 'N/A') ?></strong>
                        </td>
                        <td>
                            <span class="badge bg-label-info me-1"><?= htmlspecialchars($fee['TicketClass']) ?></span>
                        </td>
                        <td>
                            <strong>$<?= number_format($fee['Price'], 2) ?></strong>
                        </td>
                        <td>
                            <div class="dropdown">
                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                    <i class="icon-base ti tabler-dots-vertical"></i>
                                </button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item text-warning" href="?edit_id=<?= $fee['TicketFeesId'] ?>">
                                        <i class="icon-base ti tabler-pencil me-1"></i> Edit
                                    </a>
                                    <a class="dropdown-item text-danger delete-ticket-fee" href="javascript:void(0);" 
                                       data-ticketfeeid="<?= $fee['TicketFeesId'] ?>" 
                                       data-ticketinfo="<?= htmlspecialchars($fee['MovieTitle'] ?? 'N/A') ?> - <?= htmlspecialchars($fee['TicketClass']) ?>">
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

<!-- Add/Edit Form (Initially Hidden) -->
<div id="editForm" style="display: <?php echo ($edit_ticket_fee || isset($_GET['add_new'])) ? 'block' : 'none'; ?>;">
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <?= isset($_GET['add_new']) ? 'Add New Ticket Fee' : 'Edit Ticket Fee' ?>
                    </h5>
                    <button type="button" class="btn btn-secondary" onclick="showListing()">
                        <i class="icon-base ti tabler-arrow-left me-1"></i> Back to List
                    </button>
                </div>
                <div class="card-body">
                    <form method="POST" action="updateticketfee.php">
                        <?php if ($edit_ticket_fee): ?>
                        <input type="hidden" name="ticket_fee_id" value="<?= $edit_ticket_fee['TicketFeesId'] ?>">
                        <?php endif; ?>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label" for="movie_id">Movie *</label>
                                    <div class="input-group input-group-merge">
                                        <span class="input-group-text"><i class="icon-base ti tabler-movie"></i></span>
                                        <?php if ($edit_ticket_fee && !isset($_GET['add_new'])): ?>
                                            <!-- Display as readonly input but still submit the value -->
                                            <input type="hidden" name="movie_id" value="<?= $edit_ticket_fee['MovieId'] ?>">
                                            <input type="text" class="form-control" 
                                                   value="<?= htmlspecialchars($edit_ticket_fee['MovieTitle'] ?? 'N/A') ?>" 
                                                   readonly>
                                        <?php else: ?>
                                            <select class="form-select" id="movie_id" name="movie_id" required>
                                                <option value="">Select Movie</option>
                                                <?php foreach ($movies as $movie): ?>
                                                <option value="<?= $movie['MovieId'] ?>" 
                                                    <?= ($edit_ticket_fee && $edit_ticket_fee['MovieId'] == $movie['MovieId']) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($movie['MovieTitle']) ?>
                                                </option>
                                                <?php endforeach; ?>
                                            </select>
                                        <?php endif; ?>
                                    </div>
                                    <?php if ($edit_ticket_fee && !isset($_GET['add_new'])): ?>
                                    <div class="form-text text-warning">
                                        <i class="icon-base ti tabler-info-circle me-1"></i>
                                        Movie cannot be changed when editing ticket fee
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label" for="ticket_class">Ticket Class *</label>
                                    <div class="input-group input-group-merge">
                                        <span class="input-group-text"><i class="icon-base ti tabler-ticket"></i></span>
                                        <select class="form-select" id="ticket_class" name="ticket_class" required>
                                            <option value="">Select Ticket Class</option>
                                            <option value="Standard" <?= ($edit_ticket_fee && $edit_ticket_fee['TicketClass'] == 'Standard') ? 'selected' : '' ?>>Standard</option>
                                            <option value="Premium" <?= ($edit_ticket_fee && $edit_ticket_fee['TicketClass'] == 'Premium') ? 'selected' : '' ?>>Premium</option>
                                            <option value="VIP" <?= ($edit_ticket_fee && $edit_ticket_fee['TicketClass'] == 'VIP') ? 'selected' : '' ?>>VIP</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label" for="price">Price ($) *</label>
                                    <div class="input-group input-group-merge">
                                        <span class="input-group-text"><i class="icon-base ti tabler-currency-dollar"></i></span>
                                        <input type="number" id="price" name="price" class="form-control" 
                                               value="<?= $edit_ticket_fee ? htmlspecialchars($edit_ticket_fee['Price']) : '' ?>" 
                                               placeholder="0.00" step="0.01" min="0" required />
                                    </div>
                                    <div class="form-text">Enter the price for this ticket class</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="icon-base ti tabler-check me-1"></i>
                                    <?= isset($_GET['add_new']) ? 'Add Ticket Fee' : 'Update Ticket Fee' ?>
                                </button>
                                <button type="button" class="btn btn-outline-secondary" onclick="showListing()">
                                    <i class="icon-base ti tabler-x me-1"></i> Cancel
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function showListing() {
    // Redirect back to listing without parameters
    window.location.href = window.location.pathname;
}

// Delete functionality
document.addEventListener('DOMContentLoaded', function() {
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
    let ticketFeeIdToDelete = null;
    let ticketInfoToDelete = null;

    // When delete link is clicked
    document.querySelectorAll('.delete-ticket-fee').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            ticketFeeIdToDelete = this.getAttribute('data-ticketfeeid');
            ticketInfoToDelete = this.getAttribute('data-ticketinfo');
            
            // Update modal content with ticket info
            document.querySelector('#deleteConfirmModal .modal-body p:first-child').textContent = 
                `Are you sure you want to delete ticket fee for "${ticketInfoToDelete}"?`;
            
            // Show the modal
            deleteModal.show();
        });
    });

    // When confirm delete button is clicked
    document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
        if (ticketFeeIdToDelete) {
            // Redirect to delete script
            window.location.href = `deleteticketfee.php?id=${ticketFeeIdToDelete}`;
        }
    });

    // Reset when modal is hidden
    document.getElementById('deleteConfirmModal').addEventListener('hidden.bs.modal', function() {
        ticketFeeIdToDelete = null;
        ticketInfoToDelete = null;
    });
});
</script>

<?php include('includes/footer.php') ?>