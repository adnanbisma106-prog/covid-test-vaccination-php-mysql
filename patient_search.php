<?php
/**
 * Search Accredited Covid-19 Hospitals
 */
require_once __DIR__ . '/config/db.php';
requireAuth(['patient']);

$pageTitle = "Search Hospitals";

$cityFilter = $_GET['city'] ?? 'All';
$serviceFilter = $_GET['service'] ?? 'All';
$search = trim($_GET['search'] ?? '');

$sql = "SELECT * FROM hospitals WHERE approved = 1";
if ($cityFilter !== 'All') {
    $sql .= " AND city = '" . $conn->real_escape_string($cityFilter) . "'";
}
if ($serviceFilter !== 'All') {
    $sql .= " AND (services = '" . $conn->real_escape_string($serviceFilter) . "' OR services = 'Both')";
}
if (!empty($search)) {
    $sql .= " AND (name LIKE '%" . $conn->real_escape_string($search) . "%' OR location LIKE '%" . $conn->real_escape_string($search) . "%')";
}

$hospitals = [];
if ($conn && !$conn->connect_error) {
    $res = $conn->query($sql);
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $hospitals[] = $row;
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
      <div class="mb-4">
        <h2 class="fw-bold text-dark fs-3 mb-1">Search Hospitals &amp; Vaccination Centers</h2>
        <p class="text-muted small mb-0">Find accredited hospitals offering Covid RT-PCR tests and certified vaccines</p>
      </div>

      <!-- Filters Form -->
      <div class="p-3 bg-white border rounded-3 mb-4 shadow-sm">
        <form method="GET" class="row g-3">
          <div class="col-md-4">
            <label class="form-label small fw-bold text-muted">City</label>
            <select name="city" class="form-select form-select-sm" onchange="this.form.submit()">
              <option value="All" <?php echo $cityFilter === 'All' ? 'selected' : ''; ?>>All Cities</option>
              <option value="Lahore" <?php echo $cityFilter === 'Lahore' ? 'selected' : ''; ?>>Lahore</option>
              <option value="Karachi" <?php echo $cityFilter === 'Karachi' ? 'selected' : ''; ?>>Karachi</option>
              <option value="Islamabad" <?php echo $cityFilter === 'Islamabad' ? 'selected' : ''; ?>>Islamabad</option>
              <option value="Rawalpindi" <?php echo $cityFilter === 'Rawalpindi' ? 'selected' : ''; ?>>Rawalpindi</option>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label small fw-bold text-muted">Service Type</label>
            <select name="service" class="form-select form-select-sm" onchange="this.form.submit()">
              <option value="All" <?php echo $serviceFilter === 'All' ? 'selected' : ''; ?>>All Services</option>
              <option value="Covid Test" <?php echo $serviceFilter === 'Covid Test' ? 'selected' : ''; ?>>Covid Test</option>
              <option value="Vaccination" <?php echo $serviceFilter === 'Vaccination' ? 'selected' : ''; ?>>Vaccination</option>
              <option value="Both" <?php echo $serviceFilter === 'Both' ? 'selected' : ''; ?>>Both (Test &amp; Vaccination)</option>
            </select>
          </div>
          <div class="col-md-4">
            <label class="form-label small fw-bold text-muted">Search Keyword</label>
            <div class="input-group input-group-sm">
              <input type="text" name="search" class="form-control" placeholder="Hospital name, location..." value="<?php echo htmlspecialchars($search); ?>">
              <button class="btn btn-secondary" type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
            </div>
          </div>
        </form>
      </div>

      <!-- Hospital Cards Grid -->
      <div class="row g-4">
        <?php if (!empty($hospitals)): ?>
          <?php foreach ($hospitals as $h): ?>
            <div class="col-md-6">
              <div class="card border rounded-3 p-4 h-100 shadow-sm d-flex flex-column justify-content-between">
                <div>
                  <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                      <h4 class="fw-bold text-dark fs-5 mb-1"><?php echo htmlspecialchars($h['name']); ?></h4>
                      <p class="text-muted small mb-0"><i class="fa-solid fa-location-dot text-success me-1"></i> <?php echo htmlspecialchars($h['location']); ?></p>
                    </div>
                    <span class="badge-status badge-status-approved"><?php echo htmlspecialchars($h['services']); ?></span>
                  </div>

                  <div class="row g-2 my-3 p-2 bg-light rounded small text-muted">
                    <div class="col-6"><i class="fa-solid fa-phone me-1"></i> <?php echo htmlspecialchars($h['phone']); ?></div>
                    <div class="col-6"><i class="fa-solid fa-shield-halved text-success me-1"></i> Accredited Center</div>
                    <div class="col-6"><i class="fa-solid fa-users text-primary me-1"></i> Capacity: <?php echo $h['capacity']; ?>/day</div>
                    <div class="col-6"><i class="fa-solid fa-clock text-muted me-1"></i> 08:00 AM - 05:00 PM</div>
                  </div>
                </div>

                <a href="patient_book.php?hospital=<?php echo urlencode($h['name']); ?>" class="btn btn-emerald w-100 btn-sm">
                  <i class="fa-solid fa-calendar-check me-1"></i> Book Appointment Here
                </a>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="col-12 text-center py-5 text-muted">No hospitals match your search criteria.</div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
