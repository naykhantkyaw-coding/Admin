<?php 
include('includes/config.php'); 

// Fetch bookings with user and movie information using JOIN
$stmt = $pdo->query("
    SELECT b.*, u.UserName, m.MovieTitle 
    FROM Tbl_Booking b 
    LEFT JOIN Tbl_Users u ON b.UserId = u.UserId 
    LEFT JOIN Tbl_Movies m ON b.MovieId = m.MovieId 
    ORDER BY b.BookingId DESC
");
$bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get booking data for editing if booking_id is provided
$edit_booking = null;
if (isset($_GET['edit_id'])) {
    $edit_id = $_GET['edit_id'];
    $stmt = $pdo->prepare("
        SELECT b.*, u.UserName, m.MovieTitle 
        FROM Tbl_Booking b 
        LEFT JOIN Tbl_Users u ON b.UserId = u.UserId 
        LEFT JOIN Tbl_Movies m ON b.MovieId = m.MovieId 
        WHERE b.BookingId = ?
    ");
    $stmt->execute([$edit_id]);
    $edit_booking = $stmt->fetch(PDO::FETCH_ASSOC);
}

?>
<?php 
include('includes/header.php'); 
?>

<div class="text-body">
    <h4>Booking Management</h4>
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
                <p>Are you sure you want to delete this booking?</p>
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
<div id="listingForm" style="display: <?php echo $edit_booking ? 'none' : 'block'; ?>;">
    <div class="card">
        <div class="table-responsive text-nowrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>User Name</th>
                        <th>Movie Title</th>
                        <th>Ticket Class</th>
                        <th>Seat No</th>
                        <th>Quantity</th>
                        <th>Total Price</th>
                        <th>Booking Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    <?php foreach ($bookings as $booking): 
                        $status_class = 'bg-label-secondary';
                        $status_text = strtolower($booking['Status'] ?? '');
                        
                        switch($status_text) {
                            case 'confirmed':
                            case 'completed':
                                $status_class = 'bg-label-success';
                                break;
                            case 'pending':
                                $status_class = 'bg-label-warning';
                                break;
                            case 'cancelled':
                            case 'rejected':
                                $status_class = 'bg-label-danger';
                                break;
                            default:
                                $status_class = 'bg-label-secondary';
                        }
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($booking['UserName'] ?? 'N/A') ?></td>
                        <td><?= htmlspecialchars($booking['MovieTitle'] ?? 'N/A') ?></td>
                        <td><span class="badge bg-label-info me-1"><?= htmlspecialchars($booking['TicketClass']) ?></span></td>
                        <td><?= htmlspecialchars($booking['SeatNo']) ?></td>
                        <td><?= htmlspecialchars($booking['Quantity']) ?></td>
                        <td>$<?= number_format($booking['TotalPrice'], 2) ?></td>
                        <td><?= date('M d, Y H:i', strtotime($booking['BookingDate'])) ?></td>
                        <td><span class="badge <?= $status_class ?> me-1"><?= htmlspecialchars($booking['Status']) ?></span></td>
                        <td>
                            <div class="dropdown">
                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                    <i class="icon-base ti tabler-dots-vertical"></i>
                                </button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item text-warning" href="?edit_id=<?= $booking['BookingId'] ?>">
                                        <i class="icon-base ti tabler-pencil me-1"></i> Edit
                                    </a>
                                    <a class="dropdown-item text-danger delete-booking" href="javascript:void(0);" 
                                       data-bookingid="<?= $booking['BookingId'] ?>" 
                                       data-bookinginfo="Booking #<?= $booking['BookingId'] ?>">
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
<div id="editForm" style="display: <?php echo $edit_booking ? 'block' : 'none'; ?>;">
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Edit Booking - #<?= $edit_booking ? $edit_booking['BookingId'] : '' ?></h5>
                    <button type="button" class="btn btn-secondary" onclick="showListing()">
                        <i class="icon-base ti tabler-arrow-left me-1"></i> Back to List
                    </button>
                </div>
                <div class="card-body">
                    <?php if ($edit_booking): ?>
                    <form method="POST" action="updatebooking.php">
                        <input type="hidden" name="booking_id" value="<?= $edit_booking['BookingId'] ?>">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label" for="user_name">User Name</label>
                                    <div class="input-group input-group-merge">
                                        <span class="input-group-text"><i class="icon-base ti tabler-user"></i></span>
                                        <input type="text" class="form-control" id="user_name" 
                                               value="<?= htmlspecialchars($edit_booking['UserName'] ?? 'N/A') ?>" readonly />
                                    </div>
                                    <div class="form-text">User information (read-only)</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label" for="movie_title">Movie Title</label>
                                    <div class="input-group input-group-merge">
                                        <span class="input-group-text"><i class="icon-base ti tabler-movie"></i></span>
                                        <input type="text" id="movie_title" class="form-control" 
                                               value="<?= htmlspecialchars($edit_booking['MovieTitle'] ?? 'N/A') ?>" readonly />
                                    </div>
                                    <div class="form-text">Movie information (read-only)</div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label" for="ticket_class">Ticket Class</label>
                                    <div class="input-group input-group-merge">
                                        <span class="input-group-text"><i class="icon-base ti tabler-ticket"></i></span>
                                          <input type="text" id="ticket_class" name="ticket_class" class="form-control" 
                                               value="<?= htmlspecialchars($edit_booking['TicketClass'] ?? 'N/A') ?>" readonly />
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label" for="seat_no">Seat Number</label>
                                    <div class="input-group input-group-merge">
                                        <span class="input-group-text"><i class="icon-base ti tabler-chair-director"></i></span>
                                        <input type="text" id="seat_no" name="seat_no" class="form-control" 
                                               value="<?= htmlspecialchars($edit_booking['SeatNo']) ?>" placeholder="e.g., A12, B05" readonly />
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label" for="quantity">Quantity</label>
                                    <div class="input-group input-group-merge">
                                        <span class="input-group-text"><i class="icon-base ti tabler-number"></i></span>
                                        <input type="number" id="quantity" name="quantity" class="form-control" 
                                               value="<?= htmlspecialchars($edit_booking['Quantity']) ?>" min="1" max="10" readonly />
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label" for="total_price">Total Price ($)</label>
                                    <div class="input-group input-group-merge">
                                        <span class="input-group-text"><i class="icon-base ti tabler-currency-dollar"></i></span>
                                        <input type="number" id="total_price" name="total_price" class="form-control" 
                                               value="<?= htmlspecialchars($edit_booking['TotalPrice']) ?>" step="0.01" min="0" readonly />
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label" for="booking_date">Booking Date & Time</label>
                                    <div class="input-group input-group-merge">
                                        <span class="input-group-text"><i class="icon-base ti tabler-calendar"></i></span>
                                        <input type="datetime-local" id="booking_date" name="booking_date" class="form-control" 
                                               value="<?= date('Y-m-d\TH:i', strtotime($edit_booking['BookingDate'])) ?>" required />
                                    </div>
                                    <div class="form-text">Date and time when booking was made</div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label" for="status">Status</label>
                                    <div class="input-group input-group-merge">
                                        <span class="input-group-text"><i class="icon-base ti tabler-status-change"></i></span>
                                        <select class="form-select" id="status" name="status" required>
                                            <option value="Pending" <?= $edit_booking['Status'] == 'Pending' ? 'selected' : '' ?>>Pending</option>
                                            <option value="Confirmed" <?= $edit_booking['Status'] == 'Confirmed' ? 'selected' : '' ?>>Confirmed</option>
                                            <option value="Completed" <?= $edit_booking['Status'] == 'Completed' ? 'selected' : '' ?>>Completed</option>
                                            <option value="Cancelled" <?= $edit_booking['Status'] == 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="icon-base ti tabler-check me-1"></i> Update Booking
                                </button>
                                <button type="button" class="btn btn-outline-secondary" onclick="showListing()">
                                    <i class="icon-base ti tabler-x me-1"></i> Cancel
                                </button>
                            </div>
                        </div>
                    </form>
                    <?php else: ?>
                    <div class="alert alert-warning">Booking not found.</div>
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
    let bookingIdToDelete = null;
    let bookingInfoToDelete = null;

    // When delete link is clicked
    document.querySelectorAll('.delete-booking').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            bookingIdToDelete = this.getAttribute('data-bookingid');
            bookingInfoToDelete = this.getAttribute('data-bookinginfo');
            
            // Update modal content with booking info
            document.querySelector('#deleteConfirmModal .modal-body p:first-child').textContent = 
                `Are you sure you want to delete ${bookingInfoToDelete}?`;
            
            // Show the modal
            deleteModal.show();
        });
    });

    // When confirm delete button is clicked
    document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
        if (bookingIdToDelete) {
            // Redirect to delete script
            window.location.href = `deletebooking.php?id=${bookingIdToDelete}`;
        }
    });

    // Reset when modal is hidden
    document.getElementById('deleteConfirmModal').addEventListener('hidden.bs.modal', function() {
        bookingIdToDelete = null;
        bookingInfoToDelete = null;
    });
});
</script>

<?php include('includes/footer.php') ?>