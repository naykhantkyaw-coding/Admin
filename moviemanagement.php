<?php 
include('includes/config.php'); 

// Fetch all movies
$stmt = $pdo->query("SELECT * FROM Tbl_Movies ORDER BY MovieId DESC");
$movies = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get movie data for editing if movie_id is provided
$edit_movie = null;
if (isset($_GET['edit_id'])) {
    $edit_id = $_GET['edit_id'];
    $stmt = $pdo->prepare("SELECT * FROM Tbl_Movies WHERE MovieId = ?");
    $stmt->execute([$edit_id]);
    $edit_movie = $stmt->fetch(PDO::FETCH_ASSOC);
}

?>
<?php 
include('includes/header.php'); 
?>

<div class="text-body">
    <h4>Movie Management</h4>
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
                <p>Are you sure you want to delete this movie?</p>
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

<!-- Add New Movie Button -->
<div class="mb-4">
    <a href="?add_new=1" class="btn btn-primary">
        <i class="icon-base ti tabler-plus me-1"></i> Add New Movie
    </a>
</div>

<!-- Listing Form (Initially Visible) -->
<div id="listingForm" style="display: <?php echo ($edit_movie || isset($_GET['add_new'])) ? 'none' : 'block'; ?>;">
    <div class="card">
        <div class="table-responsive text-nowrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Movie Title</th>
                        <th>Genre</th>
                        <th>Duration</th>
                        <th>Release Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    <?php foreach ($movies as $movie): 
                        $status_class = 'bg-label-secondary';
                        $status_text = strtolower($movie['Status'] ?? '');
                        
                        switch($status_text) {
                            case 'active':
                            case 'released':
                                $status_class = 'bg-label-success';
                                break;
                            case 'upcoming':
                                $status_class = 'bg-label-warning';
                                break;
                            case 'inactive':
                            case 'cancelled':
                                $status_class = 'bg-label-danger';
                                break;
                            default:
                                $status_class = 'bg-label-secondary';
                        }
                        
                        // Default image if not set
                        $movie_image = !empty($movie['ImageUrl']) ? $movie['ImageUrl'] : 'assets/img/movie/default-movie.jpg';
                    ?>
                    <tr>
                        <td>
                            <img src="<?= $movie_image ?>" alt="<?= htmlspecialchars($movie['MovieTitle']) ?>" 
                                 class="rounded" width="60" height="80" style="object-fit: cover;">
                        </td>
                        <td>
                            <strong><?= htmlspecialchars($movie['MovieTitle']) ?></strong>
                            <?php if ($movie['Description']): ?>
                            <br><small class="text-muted"><?= substr(htmlspecialchars($movie['Description']), 0, 50) ?>...</small>
                            <?php endif; ?>
                        </td>
                        <td><span class="badge bg-label-info me-1"><?= htmlspecialchars($movie['Genrer']) ?></span></td>
                        <td><?= htmlspecialchars($movie['Duration']) ?> min</td>
                        <td><?= date('M d, Y', strtotime($movie['ReleaseDate'])) ?></td>
                        <td><span class="badge <?= $status_class ?> me-1"><?= htmlspecialchars($movie['Status']) ?></span></td>
                        <td>
                            <div class="dropdown">
                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                    <i class="icon-base ti tabler-dots-vertical"></i>
                                </button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item text-warning" href="?edit_id=<?= $movie['MovieId'] ?>">
                                        <i class="icon-base ti tabler-pencil me-1"></i> Edit
                                    </a>
                                    <a class="dropdown-item text-danger delete-movie" href="javascript:void(0);" 
                                       data-movieid="<?= $movie['MovieId'] ?>" 
                                       data-movietitle="<?= htmlspecialchars($movie['MovieTitle']) ?>">
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
<div id="editForm" style="display: <?php echo ($edit_movie || isset($_GET['add_new'])) ? 'block' : 'none'; ?>;">
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <?= isset($_GET['add_new']) ? 'Add New Movie' : 'Edit Movie - ' . ($edit_movie ? htmlspecialchars($edit_movie['MovieTitle']) : '') ?>
                    </h5>
                    <button type="button" class="btn btn-secondary" onclick="showListing()">
                        <i class="icon-base ti tabler-arrow-left me-1"></i> Back to List
                    </button>
                </div>
                <div class="card-body">
                    <form method="POST" action="updatemovie.php" enctype="multipart/form-data">
                        <?php if ($edit_movie): ?>
                        <input type="hidden" name="movie_id" value="<?= $edit_movie['MovieId'] ?>">
                        <?php endif; ?>
                        
                        <div class="row">
                            <div class="col-md-8">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="movie_title">Movie Title *</label>
                                            <div class="input-group input-group-merge">
                                                <span class="input-group-text"><i class="icon-base ti tabler-movie"></i></span>
                                                <input type="text" class="form-control" id="movie_title" name="movie_title" 
                                                       value="<?= $edit_movie ? htmlspecialchars($edit_movie['MovieTitle']) : '' ?>" 
                                                       placeholder="Enter movie title" required />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="genrer">Genre *</label>
                                            <div class="input-group input-group-merge">
                                                <span class="input-group-text"><i class="icon-base ti tabler-category"></i></span>
                                                <select class="form-select" id="genrer" name="genrer" required>
                                                    <option value="">Select Genre</option>
                                                    <option value="Action" <?= ($edit_movie && $edit_movie['Genrer'] == 'Action') ? 'selected' : '' ?>>Action</option>
                                                    <option value="Comedy" <?= ($edit_movie && $edit_movie['Genrer'] == 'Comedy') ? 'selected' : '' ?>>Comedy</option>
                                                    <option value="Drama" <?= ($edit_movie && $edit_movie['Genrer'] == 'Drama') ? 'selected' : '' ?>>Drama</option>
                                                    <option value="Horror" <?= ($edit_movie && $edit_movie['Genrer'] == 'Horror') ? 'selected' : '' ?>>Horror</option>
                                                    <option value="Romance" <?= ($edit_movie && $edit_movie['Genrer'] == 'Romance') ? 'selected' : '' ?>>Romance</option>
                                                    <option value="Sci-Fi" <?= ($edit_movie && $edit_movie['Genrer'] == 'Sci-Fi') ? 'selected' : '' ?>>Sci-Fi</option>
                                                    <option value="Thriller" <?= ($edit_movie && $edit_movie['Genrer'] == 'Thriller') ? 'selected' : '' ?>>Thriller</option>
                                                    <option value="Adventure" <?= ($edit_movie && $edit_movie['Genrer'] == 'Adventure') ? 'selected' : '' ?>>Adventure</option>
                                                    <option value="Fantasy" <?= ($edit_movie && $edit_movie['Genrer'] == 'Fantasy') ? 'selected' : '' ?>>Fantasy</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="duration">Duration (minutes) *</label>
                                            <div class="input-group input-group-merge">
                                                <span class="input-group-text"><i class="icon-base ti tabler-clock"></i></span>
                                                <input type="number" id="duration" name="duration" class="form-control" 
                                                       value="<?= $edit_movie ? htmlspecialchars($edit_movie['Duration']) : '' ?>" 
                                                       placeholder="120" min="1" max="300" required />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label" for="release_date">Release Date *</label>
                                            <div class="input-group input-group-merge">
                                                <span class="input-group-text"><i class="icon-base ti tabler-calendar"></i></span>
                                                <input type="date" id="release_date" name="release_date" class="form-control" 
                                                       value="<?= $edit_movie ? $edit_movie['ReleaseDate'] : '' ?>" required />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label" for="description">Description</label>
                                    <div class="input-group input-group-merge">
                                        <span class="input-group-text"><i class="icon-base ti tabler-notes"></i></span>
                                        <textarea id="description" name="description" class="form-control" 
                                                  placeholder="Enter movie description" rows="4"><?= $edit_movie ? htmlspecialchars($edit_movie['Description']) : '' ?></textarea>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label class="form-label" for="status">Status *</label>
                                    <div class="input-group input-group-merge">
                                        <span class="input-group-text"><i class="icon-base ti tabler-status-change"></i></span>
                                        <select class="form-select" id="status" name="status" required>
                                            <option value="Upcoming" <?= ($edit_movie && $edit_movie['Status'] == 'Upcoming') ? 'selected' : '' ?>>Upcoming</option>
                                            <option value="Active" <?= ($edit_movie && $edit_movie['Status'] == 'Active') ? 'selected' : '' ?>>Active</option>
                                            <option value="Inactive" <?= ($edit_movie && $edit_movie['Status'] == 'Inactive') ? 'selected' : '' ?>>Inactive</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label" for="movie_image">Movie Image</label>
                                    <div class="card">
                                        <div class="card-body text-center">
                                            <?php if ($edit_movie && !empty($edit_movie['ImageUrl'])): ?>
                                            <img id="imagePreview" src="<?= $edit_movie['ImageUrl'] ?>" 
                                                 alt="Current Image" class="rounded mb-3" 
                                                 style="max-width: 100%; max-height: 200px; object-fit: cover;">
                                            <?php else: ?>
                                            <img id="imagePreview" src="assets/img/movie/default-movie.jpg" 
                                                 alt="Default Image" class="rounded mb-3" 
                                                 style="max-width: 100%; max-height: 200px; object-fit: cover;">
                                            <?php endif; ?>
                                            
                                            <input type="file" id="movie_image" name="movie_image" 
                                                   class="form-control" accept="image/*"
                                                   onchange="previewImage(this)">
                                            <div class="form-text">
                                                Recommended size: 300x400px. Max size: 2MB
                                            </div>
                                            <?php if ($edit_movie && !empty($edit_movie['ImageUrl'])): ?>
                                            <div class="form-check mt-2">
                                                <input class="form-check-input" type="checkbox" id="remove_image" name="remove_image">
                                                <label class="form-check-label" for="remove_image">
                                                    Remove current image
                                                </label>
                                            </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="icon-base ti tabler-check me-1"></i>
                                    <?= isset($_GET['add_new']) ? 'Add Movie' : 'Update Movie' ?>
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

function previewImage(input) {
    const preview = document.getElementById('imagePreview');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
        }
        reader.readAsDataURL(input.files[0]);
    }
}

// Delete functionality
document.addEventListener('DOMContentLoaded', function() {
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));
    let movieIdToDelete = null;
    let movieTitleToDelete = null;

    // When delete link is clicked
    document.querySelectorAll('.delete-movie').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            movieIdToDelete = this.getAttribute('data-movieid');
            movieTitleToDelete = this.getAttribute('data-movietitle');
            
            // Update modal content with movie title
            document.querySelector('#deleteConfirmModal .modal-body p:first-child').textContent = 
                `Are you sure you want to delete "${movieTitleToDelete}"?`;
            
            // Show the modal
            deleteModal.show();
        });
    });

    // When confirm delete button is clicked
    document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
        if (movieIdToDelete) {
            // Redirect to delete script
            window.location.href = `deletemovie.php?id=${movieIdToDelete}`;
        }
    });

    // Reset when modal is hidden
    document.getElementById('deleteConfirmModal').addEventListener('hidden.bs.modal', function() {
        movieIdToDelete = null;
        movieTitleToDelete = null;
    });
});
</script>

<?php include('includes/footer.php') ?>