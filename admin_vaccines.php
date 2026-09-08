<?php
/**
 * Vaccine Management (Matching Screenshot 3)
 */
require_once __DIR__ . '/config/db.php';
requireAuth(['admin']);

$pageTitle = "Vaccine Management";

// Handle Add / Edit / Delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $name = trim($_POST['name'] ?? '');
        $manufacturer = trim($_POST['manufacturer'] ?? '');
        $total = intval($_POST['total_doses'] ?? 1000);
        $avail = intval($_POST['available_doses'] ?? 1000);
        $gap = intval($_POST['gap_days'] ?? 28);
        $status = $_POST['status'] ?? 'Active';
        $id = 'vac_' . time();

        $stmt = $conn->prepare("INSERT INTO vaccines (id, name, manufacturer, total_doses, available_doses, gap_days, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        if ($stmt) {
            $stmt->bind_param("sssiiis", $id, $name, $manufacturer, $total, $avail, $gap, $status);
            $stmt->execute();
            logAudit($conn, "Admin", "Added vaccine '$name' into national registry");
            setFlash("Vaccine '$name' added successfully!", "success");
        }
    } elseif ($action === 'edit') {
        $id = $_POST['id'] ?? '';
        $name = trim($_POST['name'] ?? '');
        $manufacturer = trim($_POST['manufacturer'] ?? '');
        $total = intval($_POST['total_doses'] ?? 1000);
        $avail = intval($_POST['available_doses'] ?? 1000);
        $status = $_POST['status'] ?? 'Active';

        $stmt = $conn->prepare("UPDATE vaccines SET name = ?, manufacturer = ?, total_doses = ?, available_doses = ?, status = ? WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("ssiiss", $name, $manufacturer, $total, $avail, $status, $id);
            $stmt->execute();
            logAudit($conn, "Admin", "Updated vaccine '$name'");
            setFlash("Vaccine details updated!", "success");
        }
    } elseif ($action === 'delete') {
        $id = $_POST['id'] ?? '';
        $stmt = $conn->prepare("DELETE FROM vaccines WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("s", $id);
            $stmt->execute();
            logAudit($conn, "Admin", "Removed vaccine record #$id");
            setFlash("Vaccine removed from registry.", "info");
        }
    }
    header("Location: admin_vaccines.php");
    exit;
}

$vaccines = [];
if ($conn && !$conn->connect_error) {
    $res = $conn->query("SELECT * FROM vaccines ORDER BY id ASC");
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $vaccines[] = $row;
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="app-container">
  <?php require_once __DIR__ . '/includes/sidebar.php'; ?>

  <div class="main-content">
    <?php require_once __DIR__ . '/includes/topbar.php'; ?>

    <div class="page-body">
      <div class="panel-card-custom">
        <div class="panel-header-custom">
          <h2 class="panel-title-custom">
            <i class="fa-solid fa-syringe text-success"></i> Vaccine Management
          </h2>
          <button class="btn btn-emerald btn-sm" data-bs-toggle="modal" data-bs-target="#addVaccineModal">
            <i class="fa-solid fa-plus me-1"></i> Add Vaccine
          </button>
        </div>

        <!-- Vaccine Table (Matching Screenshot 3) -->
        <div class="table-responsive">
          <table class="table table-custom align-middle">
            <thead>
              <tr>
                <th style="width: 50px;">#</th>
                <th>Vaccine Name</th>
                <th>Manufacturer</th>
                <th>Total Doses</th>
                <th>Available Doses</th>
                <th>Status</th>
                <th class="text-end" style="width: 120px;">Action</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($vaccines)): ?>
                <?php foreach ($vaccines as $idx => $v): ?>
                  <tr>
                    <td class="text-muted fw-bold"><?php echo $idx + 1; ?></td>
                    <td class="fw-bold text-dark"><?php echo htmlspecialchars($v['name']); ?></td>
                    <td class="text-muted"><?php echo htmlspecialchars($v['manufacturer']); ?></td>
                    <td class="fw-semibold"><?php echo number_format($v['total_doses']); ?></td>
                    <td class="fw-bold text-success"><?php echo number_format($v['available_doses']); ?></td>
                    <td>
                      <span class="badge-status <?php echo ($v['status'] ?? 'Active') === 'Active' ? 'badge-status-active' : 'badge-status-inactive'; ?>">
                        <?php echo htmlspecialchars($v['status'] ?? 'Active'); ?>
                      </span>
                    </td>
                    <td class="text-end">
                      <button class="btn-icon-custom btn-icon-blue" data-bs-toggle="modal" data-bs-target="#editVacModal_<?php echo $v['id']; ?>" title="Edit">
                        <i class="fa-solid fa-pen-to-square"></i>
                      </button>
                      <form method="POST" class="d-inline" onsubmit="return confirm('Delete this vaccine?');">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?php echo $v['id']; ?>">
                        <button type="submit" class="btn-icon-custom btn-icon-red" title="Delete">
                          <i class="fa-solid fa-trash-can"></i>
                        </button>
                      </form>
                    </td>
                  </tr>

                  <!-- Edit Vaccine Modal -->
                  <div class="modal fade" id="editVacModal_<?php echo $v['id']; ?>" tabindex="-1">
                    <div class="modal-dialog">
                      <div class="modal-content">
                        <form method="POST">
                          <input type="hidden" name="action" value="edit">
                          <input type="hidden" name="id" value="<?php echo $v['id']; ?>">
                          <div class="modal-header">
                            <h5 class="modal-title fw-bold">Edit Vaccine Details</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                          </div>
                          <div class="modal-body">
                            <div class="mb-3">
                              <label class="form-label small fw-bold">Vaccine Name</label>
                              <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($v['name']); ?>" required>
                            </div>
                            <div class="mb-3">
                              <label class="form-label small fw-bold">Manufacturer</label>
                              <input type="text" name="manufacturer" class="form-control" value="<?php echo htmlspecialchars($v['manufacturer']); ?>" required>
                            </div>
                            <div class="row g-2 mb-3">
                              <div class="col-6">
                                <label class="form-label small fw-bold">Total Doses</label>
                                <input type="number" name="total_doses" class="form-control" value="<?php echo $v['total_doses']; ?>">
                              </div>
                              <div class="col-6">
                                <label class="form-label small fw-bold">Available Doses</label>
                                <input type="number" name="available_doses" class="form-control" value="<?php echo $v['available_doses']; ?>">
                              </div>
                            </div>
                            <div class="mb-3">
                              <label class="form-label small fw-bold">Status</label>
                              <select name="status" class="form-select">
                                <option value="Active" <?php echo $v['status'] === 'Active' ? 'selected' : ''; ?>>Active</option>
                                <option value="Inactive" <?php echo $v['status'] === 'Inactive' ? 'selected' : ''; ?>>Inactive</option>
                              </select>
                            </div>
                          </div>
                          <div class="modal-footer">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-emerald btn-sm">Save Changes</button>
                          </div>
                        </form>
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              <?php else: ?>
                <tr><td colspan="7" class="text-center py-5 text-muted">No vaccines registered.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>

        <div class="p-3 bg-white border-top small text-muted">
          Showing 1 to <?php echo count($vaccines); ?> of <?php echo count($vaccines); ?> entries
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Add Vaccine Modal -->
<div class="modal fade" id="addVaccineModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST">
        <input type="hidden" name="action" value="add">
        <div class="modal-header">
          <h5 class="modal-title fw-bold">Add New Vaccine</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label small fw-bold">Vaccine Name *</label>
            <input type="text" name="name" class="form-control" placeholder="e.g. Covishield" required>
          </div>
          <div class="mb-3">
            <label class="form-label small fw-bold">Manufacturer *</label>
            <input type="text" name="manufacturer" class="form-control" placeholder="e.g. Serum Institute" required>
          </div>
          <div class="row g-2 mb-3">
            <div class="col-6">
              <label class="form-label small fw-bold">Total Doses</label>
              <input type="number" name="total_doses" class="form-control" value="1000">
            </div>
            <div class="col-6">
              <label class="form-label small fw-bold">Available Doses</label>
              <input type="number" name="available_doses" class="form-control" value="1000">
            </div>
          </div>
          <div class="row g-2 mb-3">
            <div class="col-6">
              <label class="form-label small fw-bold">Dose Gap (Days)</label>
              <input type="number" name="gap_days" class="form-control" value="28">
            </div>
            <div class="col-6">
              <label class="form-label small fw-bold">Status</label>
              <select name="status" class="form-select">
                <option value="Active">Active</option>
                <option value="Inactive">Inactive</option>
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-emerald btn-sm">Add Vaccine</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
