<?php 
include('includes/config.php'); 

$stmt = $pdo->query("SELECT * FROM Tbl_Users");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<?php 
include('includes/header.php'); 
?>

   
<div class="text-body">
    <h4>User Management</h4>
</div>

 <div class="card">
    <!-- <h5 class="card-header">Table Basic</h5> -->
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
                    <a class="dropdown-item" href="javascript:void(0);"><i class="icon-base ti tabler-pencil me-1"></i> Edit</a>
                    <a class="dropdown-item" href="javascript:void(0);"><i class="icon-base ti tabler-trash me-1"></i> Delete</a>
                    <a class="dropdown-item" href="javascript:void(0);"><i class="icon-base ti tabler-check me-1"></i>Approve</a>
                        <a class="dropdown-item" href="javascript:void(0);"><i class="icon-base ti tabler-x me-1"></i> Reject</a>
                </div>
              </div>
            </td>
                </tr>
                <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

<?php include('includes/footer.php') ?>