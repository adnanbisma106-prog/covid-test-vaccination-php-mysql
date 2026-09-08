<?php
/**
 * System Audit Logs
 */
require_once __DIR__ . '/config/db.php';
requireAuth(['admin']);

$pageTitle = "Audit Logs";

$logs = [];
if ($conn && !$conn->connect_error) {
    $res = $conn->query("SELECT * FROM audit_logs ORDER BY id DESC LIMIT 50");
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $logs[] = $row;
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
            <i class="fa-solid fa-list-check text-secondary"></i> System Audit Logs
          </h2>
        </div>

        <div class="table-responsive">
          <table class="table table-custom align-middle">
            <thead>
              <tr>
                <th style="width: 200px;">Timestamp</th>
                <th style="width: 220px;">Actor / Origin</th>
                <th>Action Detail</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($logs)): ?>
                <?php foreach ($logs as $log): ?>
                  <tr>
                    <td class="text-muted font-monospace small"><?php echo htmlspecialchars($log['timestamp']); ?></td>
                    <td class="fw-bold text-dark"><?php echo htmlspecialchars($log['user']); ?></td>
                    <td class="text-secondary"><?php echo htmlspecialchars($log['action']); ?></td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr><td colspan="3" class="text-center py-5 text-muted">No audit logs recorded yet.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
