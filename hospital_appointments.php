<?php
/**
 * Hospital Appointment Requests Queue
 */
require_once __DIR__ . '/config/db.php';
requireAuth(['hospital']);

$pageTitle = "Hospital Appointments";
$hospName = $currentUser['name'] ?? 'City Hospital';

// Handle Actions (Approve / Reject)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $id = $_POST['id'] ?? '';
    $reason = trim($_POST['reject_reason'] ?? '');

    if ($action === 'approve') {
        $stmt = $conn->prepare("UPDATE appointments SET status = 'Approved' WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("s", $id);
            $stmt->execute();
            logAudit($conn, $hospName, "Approved appointment #$id");
            setFlash("Appointment approved!", "success");
        }
    } elseif ($action === 'reject') {
        $stmt = $conn->prepare("UPDATE appointments SET status = 'Rejected', reject_reason = ? WHERE id = ?");
        if ($stmt) {
            $stmt->bind_param("ss", $reason, $id);
            $stmt->execute();
            logAudit($conn, $hospName, "Rejected appointment #$id (Reason: $reason)");
            setFlash("Appointment rejected.", "info");
        }
    }
    header("Location: hospital_appointments.php");
    exit;
}

$statusFilter = $_GET['status'] ?? 'All';
$sql = "SELECT * FROM appointments WHERE 1=1";
if ($statusFilter !== 'All') {
    $sql .= " AND status = '" . $conn->real_escape_string($statusFilter) . "'";
}
$sql .= " ORDER BY date DESC, time ASC";

$apts = [];
if ($conn && !$conn->connect_error) {
    $res = $conn->query($sql);
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $apts[] = $row;
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
            <i class="fa-solid fa-calendar-check text-success"></i> Patient Booking Requests
          </h2>
          <form method="GET" class="d-flex align-items-center gap-2">
            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
              <option value="All" <?php echo $statusFilter === 'All' ? 'selected' : ''; ?>>All Bookings</option>
              <option value="Pending" <?php echo $statusFilter === 'Pending' ? 'selected' : ''; ?>>Pending Approvals</option>
              <option value="Approved" <?php echo $statusFilter === 'Approved' ? 'selected' : ''; ?>>Approved</option>
              <option value="Rejected" <?php echo $statusFilter === 'Rejected' ? 'selected' : ''; ?>>Rejected</option>
            </select>
          </form>
        </div>

        <div class="table-responsive">
          <table class="table table-custom align-middle">
            <thead>
              <tr>
                <th style="width: 50px;">#</th>
                <th>Patient Name</th>
                <th>Service</th>
                <th>Date</th>
                <th>Time Slot</th>
                <th>Status</th>
                <th class="text-end" style="width: 180px;">Action</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($apts)): ?>
                <?php foreach ($apts as $idx => $a): ?>
                  <tr>
                    <td class="text-muted fw-bold"><?php echo $idx + 1; ?></td>
                    <td class="fw-bold text-dark"><?php echo htmlspecialchars($a['patient_name']); ?></td>
                    <td class="fw-semibold text-success"><?php echo htmlspecialchars($a['service']); ?></td>
                    <td class="text-muted"><?php echo htmlspecialchars($a['date']); ?></td>
                    <td class="text-muted font-monospace"><?php echo htmlspecialchars($a['time']); ?></td>
                    <td>
                      <?php 
                        $st = strtolower($a['status']);
                        $badgeClass = $st === 'approved' ? 'badge-status-approved' : ($st === 'rejected' ? 'badge-status-rejected' : 'badge-status-pending');
                      ?>
                      <span class="badge-status <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($a['status']); ?></span>
                    </td>
                    <td class="text-end">
                      <?php if ($a['status'] === 'Pending'): ?>
                        <form method="POST" class="d-inline">
                          <input type="hidden" name="action" value="approve">
                          <input type="hidden" name="id" value="<?php echo $a['id']; ?>">
                          <button type="submit" class="btn btn-sm btn-success py-1 px-2 fw-bold" title="Approve">
                            <i class="fa-solid fa-check"></i> Approve
                          </button>
                        </form>
                        <button class="btn btn-sm btn-danger py-1 px-2 fw-bold" data-bs-toggle="modal" data-bs-target="#rejectModal_<?php echo $a['id']; ?>" title="Reject">
                          <i class="fa-solid fa-xmark"></i> Reject
                        </button>
                      <?php else: ?>
                        <a href="view_appointment_slip.php?id=<?php echo $a['id']; ?>" class="btn-icon-custom btn-icon-blue" title="View Slip" target="_blank">
                          <i class="fa-solid fa-eye"></i>
                        </a>
                      <?php endif; ?>
                    </td>
                  </tr>

                  <!-- Reject Reason Modal -->
                  <div class="modal fade" id="rejectModal_<?php echo $a['id']; ?>" tabindex="-1">
                    <div class="modal-dialog">
                      <div class="modal-content">
                        <form method="POST">
                          <input type="hidden" name="action" value="reject">
                          <input type="hidden" name="id" value="<?php echo $a['id']; ?>">
                          <div class="modal-header">
                            <h5 class="modal-title fw-bold text-danger"><i class="fa-solid fa-triangle-exclamation me-1"></i> Reject Appointment</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                          </div>
                          <div class="modal-body">
                            <p class="small text-muted mb-2">Provide a reason for rejecting booking #<?php echo $a['id']; ?>:</p>
                            <textarea name="reject_reason" class="form-control" rows="3" required placeholder="e.g. Required time slot is fully booked. Please select another slot."></textarea>
                          </div>
                          <div class="modal-footer">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger btn-sm">Confirm Reject</button>
                          </div>
                        </form>
                      </div>
                    </div>
                  </div>
                <?php endforeach; ?>
              <?php else: ?>
                <tr><td colspan="7" class="text-center py-5 text-muted">No appointments found.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
