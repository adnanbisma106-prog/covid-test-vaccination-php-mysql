<?php
/**
 * Hospital Vaccine Inventory
 */
require_once __DIR__ . '/config/db.php';
requireAuth(['hospital']);

$pageTitle = "Vaccine Stock";

$vaccines = [];
if ($conn && !$conn->connect_error) {
    $res = $conn->query("SELECT * FROM vaccines ORDER BY available_doses DESC");
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
            <i class="fa-solid fa-boxes-stacked text-primary"></i> Hospital Vaccine Inventory &amp; Live Stock
          </h2>
        </div>

        <div class="table-responsive">
          <table class="table table-custom align-middle">
            <thead>
              <tr>
                <th style="width: 50px;">#</th>
                <th>Vaccine Brand</th>
                <th>Manufacturer</th>
                <th>Available Doses</th>
                <th>Total Capacity</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($vaccines)): ?>
                <?php foreach ($vaccines as $idx => $v): ?>
                  <tr>
                    <td class="text-muted fw-bold"><?php echo $idx + 1; ?></td>
                    <td class="fw-bold text-dark"><?php echo htmlspecialchars($v['name']); ?></td>
                    <td class="text-muted"><?php echo htmlspecialchars($v['manufacturer']); ?></td>
                    <td class="fw-bold text-success"><?php echo number_format($v['available_doses']); ?></td>
                    <td class="text-muted"><?php echo number_format($v['total_doses']); ?></td>
                    <td>
                      <span class="badge-status <?php echo $v['available_doses'] > 100 ? 'badge-status-active' : 'badge-status-pending'; ?>">
                        <?php echo $v['available_doses'] > 100 ? 'In Stock' : 'Low Stock'; ?>
                      </span>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr><td colspan="6" class="text-center py-5 text-muted">No vaccines found.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
