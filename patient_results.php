<?php
/**
 * Patient Covid-19 Test Results & Certificates
 */
require_once __DIR__ . '/config/db.php';
requireAuth(['patient']);

$pageTitle = "Test Results";
$patId = $currentUser['id'] ?? 'pat_ali';
$patName = $currentUser['name'] ?? 'Ali Khan';

$tests = [];
if ($conn && !$conn->connect_error) {
    $stmt = $conn->prepare("SELECT * FROM covid_tests WHERE patient_id = ? OR patient_name = ? ORDER BY result_date DESC");
    if ($stmt) {
        $stmt->bind_param("ss", $patId, $patName);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) {
            $tests[] = $row;
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
            <i class="fa-solid fa-file-medical text-success"></i> Covid-19 Test Results &amp; Certificates
          </h2>
        </div>

        <div class="table-responsive">
          <table class="table table-custom align-middle">
            <thead>
              <tr>
                <th style="width: 50px;">#</th>
                <th>Test Type</th>
                <th>Hospital</th>
                <th>Sample Date</th>
                <th>Result Date</th>
                <th>Result</th>
                <th class="text-end" style="width: 180px;">Download Report</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($tests)): ?>
                <?php foreach ($tests as $idx => $t): ?>
                  <tr>
                    <td class="text-muted fw-bold"><?php echo $idx + 1; ?></td>
                    <td class="fw-bold text-dark"><?php echo htmlspecialchars($t['test_type']); ?></td>
                    <td class="text-muted"><?php echo htmlspecialchars($t['hospital_name']); ?></td>
                    <td class="text-muted"><?php echo htmlspecialchars($t['sample_date']); ?></td>
                    <td class="text-muted"><?php echo htmlspecialchars($t['result_date']); ?></td>
                    <td>
                      <?php 
                        $resLower = strtolower($t['result']);
                        $badgeClass = strpos($resLower, 'neg') !== false ? 'badge-status-negative' : 'badge-status-positive';
                      ?>
                      <span class="badge-status <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($t['result']); ?></span>
                    </td>
                    <td class="text-end">
                      <a href="view_test_certificate.php?id=<?php echo $t['id']; ?>" class="btn btn-sm btn-success py-1 px-3 fw-bold" target="_blank">
                        <i class="fa-solid fa-file-pdf me-1"></i> View Certificate
                      </a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr><td colspan="7" class="text-center py-5 text-muted">No test results found for your account.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
