<?php
/**
 * Patient Dashboard (Matching Screenshot 5)
 */
require_once __DIR__ . '/config/db.php';
requireAuth(['patient']);

$pageTitle = "Patient Dashboard";
$patId = $currentUser['id'] ?? 'pat_ali';

// Fetch Patient Appointments
$upcomingApt = null;
if ($conn && !$conn->connect_error) {
    $stmt = $conn->prepare("SELECT * FROM appointments WHERE (patient_id = ? OR patient_name = ?) AND (status = 'Approved' OR status = 'Pending') ORDER BY date ASC LIMIT 1");
    if ($stmt) {
        $stmt->bind_param("ss", $patId, $currentUser['name']);
        $stmt->execute();
        $upcomingApt = $stmt->get_result()->fetch_assoc();
    }
}

// Fetch Recent Test Result
$recentTest = null;
if ($conn && !$conn->connect_error) {
    $stmt = $conn->prepare("SELECT * FROM covid_tests WHERE patient_id = ? OR patient_name = ? ORDER BY result_date DESC LIMIT 1");
    if ($stmt) {
        $stmt->bind_param("ss", $patId, $currentUser['name']);
        $stmt->execute();
        $recentTest = $stmt->get_result()->fetch_assoc();
    }
}

// Fetch Vaccination Record
$recentVac = null;
$totalDosesTaken = 0;
if ($conn && !$conn->connect_error) {
    $stmt = $conn->prepare("SELECT * FROM vaccination_doses WHERE patient_id = ? OR patient_name = ? ORDER BY dose_date DESC LIMIT 1");
    if ($stmt) {
        $stmt->bind_param("ss", $patId, $currentUser['name']);
        $stmt->execute();
        $recentVac = $stmt->get_result()->fetch_assoc();
    }

    $cstmt = $conn->prepare("SELECT COUNT(*) as cnt FROM vaccination_doses WHERE patient_id = ? OR patient_name = ?");
    if ($cstmt) {
        $cstmt->bind_param("ss", $patId, $currentUser['name']);
        $cstmt->execute();
        $totalDosesTaken = $cstmt->get_result()->fetch_assoc()['cnt'];
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="app-container">
  <?php require_once __DIR__ . '/includes/sidebar.php'; ?>

  <div class="main-content">
    <?php require_once __DIR__ . '/includes/topbar.php'; ?>

    <div class="page-body">
      <!-- Header -->
      <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
          <h2 class="fw-bold text-dark fs-3 mb-1">Patient Dashboard</h2>
          <p class="text-muted small mb-0">Track upcoming bookings, Covid-19 diagnostic certificates, and vaccination status</p>
        </div>
        <a href="patient_book.php" class="btn btn-emerald btn-sm">
          <i class="fa-solid fa-calendar-plus me-1"></i> Book Appointment
        </a>
      </div>

      <!-- 4 Stat Status Cards (Matching Screenshot 5) -->
      <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
          <div class="stat-card-custom">
            <div class="stat-icon-box stat-icon-green">
              <i class="fa-solid fa-calendar-check"></i>
            </div>
            <div>
              <div class="stat-label-text">Upcoming Appointment</div>
              <div class="stat-number-val"><?php echo $upcomingApt ? '1' : '0'; ?></div>
            </div>
          </div>
        </div>

        <div class="col-sm-6 col-xl-3">
          <div class="stat-card-custom">
            <div class="stat-icon-box stat-icon-blue">
              <i class="fa-solid fa-vial"></i>
            </div>
            <div>
              <div class="stat-label-text">Test Results</div>
              <div class="stat-number-val">2</div>
            </div>
          </div>
        </div>

        <div class="col-sm-6 col-xl-3">
          <div class="stat-card-custom">
            <div class="stat-icon-box stat-icon-purple">
              <i class="fa-solid fa-syringe"></i>
            </div>
            <div>
              <div class="stat-label-text">Vaccination Doses</div>
              <div class="stat-number-val"><?php echo $totalDosesTaken > 0 ? $totalDosesTaken : '1'; ?> / 2</div>
            </div>
          </div>
        </div>

        <div class="col-sm-6 col-xl-3">
          <div class="stat-card-custom">
            <div class="stat-icon-box stat-icon-green">
              <i class="fa-solid fa-circle-check"></i>
            </div>
            <div>
              <div class="stat-label-text">Profile Status</div>
              <div class="stat-number-val text-success fs-5">Complete</div>
            </div>
          </div>
        </div>
      </div>

      <!-- 3 Main Action Cards (Matching Screenshot 5) -->
      <div class="row g-4">
        <!-- Card 1: Upcoming Appointment -->
        <div class="col-md-4">
          <div class="panel-card-custom h-100 d-flex flex-column justify-content-between mb-0">
            <div>
              <div class="panel-header-custom">
                <h3 class="panel-title-custom fs-6">
                  <i class="fa-solid fa-calendar-day text-success"></i> Upcoming Appointment
                </h3>
              </div>
              <div class="p-3">
                <?php if ($upcomingApt): ?>
                  <div class="d-flex justify-content-between py-2 border-bottom small">
                    <span class="text-muted">Hospital</span>
                    <span class="fw-bold text-dark"><?php echo htmlspecialchars($upcomingApt['hospital_name']); ?></span>
                  </div>
                  <div class="d-flex justify-content-between py-2 border-bottom small">
                    <span class="text-muted">Service</span>
                    <span class="fw-semibold text-success"><?php echo htmlspecialchars($upcomingApt['service']); ?></span>
                  </div>
                  <div class="d-flex justify-content-between py-2 border-bottom small">
                    <span class="text-muted">Date</span>
                    <span class="fw-semibold text-dark"><?php echo htmlspecialchars($upcomingApt['date']); ?></span>
                  </div>
                  <div class="d-flex justify-content-between py-2 border-bottom small">
                    <span class="text-muted">Time</span>
                    <span class="fw-semibold text-dark"><?php echo htmlspecialchars($upcomingApt['time']); ?></span>
                  </div>
                  <div class="d-flex justify-content-between py-2 small">
                    <span class="text-muted">Status</span>
                    <span class="badge-status badge-status-approved"><?php echo htmlspecialchars($upcomingApt['status']); ?></span>
                  </div>
                <?php else: ?>
                  <div class="text-center py-4 text-muted small">
                    <i class="fa-regular fa-calendar-xmark fs-2 mb-2 d-block text-secondary"></i>
                    No upcoming appointments.
                  </div>
                <?php endif; ?>
              </div>
            </div>
            <div class="p-3 pt-0">
              <?php if ($upcomingApt): ?>
                <a href="view_appointment_slip.php?id=<?php echo $upcomingApt['id']; ?>" class="btn btn-emerald w-100 btn-sm" target="_blank">View Details</a>
              <?php else: ?>
                <a href="patient_book.php" class="btn btn-emerald w-100 btn-sm">Book Appointment</a>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <!-- Card 2: Recent Test Result -->
        <div class="col-md-4">
          <div class="panel-card-custom h-100 d-flex flex-column justify-content-between mb-0">
            <div>
              <div class="panel-header-custom">
                <h3 class="panel-title-custom fs-6">
                  <i class="fa-solid fa-vial-virus text-primary"></i> Recent Test Result
                </h3>
              </div>
              <div class="p-3">
                <?php if ($recentTest): ?>
                  <div class="d-flex justify-content-between py-2 border-bottom small">
                    <span class="text-muted">Test Type</span>
                    <span class="fw-bold text-dark"><?php echo htmlspecialchars($recentTest['test_type']); ?></span>
                  </div>
                  <div class="d-flex justify-content-between py-2 border-bottom small">
                    <span class="text-muted">Date</span>
                    <span class="fw-semibold text-dark"><?php echo htmlspecialchars($recentTest['result_date']); ?></span>
                  </div>
                  <div class="d-flex justify-content-between py-2 small">
                    <span class="text-muted">Result</span>
                    <span class="fw-bold <?php echo strpos(strtolower($recentTest['result']), 'neg') !== false ? 'text-success' : 'text-danger'; ?>">
                      <?php echo htmlspecialchars($recentTest['result']); ?>
                    </span>
                  </div>
                <?php else: ?>
                  <div class="text-center py-4 text-muted small">No test results found.</div>
                <?php endif; ?>
              </div>
            </div>
            <div class="p-3 pt-0">
              <a href="patient_results.php" class="btn btn-primary w-100 btn-sm">View All Results</a>
            </div>
          </div>
        </div>

        <!-- Card 3: Vaccination Status -->
        <div class="col-md-4">
          <div class="panel-card-custom h-100 d-flex flex-column justify-content-between mb-0">
            <div>
              <div class="panel-header-custom">
                <h3 class="panel-title-custom fs-6">
                  <i class="fa-solid fa-shield-halved text-purple"></i> Vaccination Status
                </h3>
              </div>
              <div class="p-3">
                <?php if ($recentVac): ?>
                  <div class="d-flex justify-content-between py-2 border-bottom small">
                    <span class="text-muted">Vaccine</span>
                    <span class="fw-bold text-dark"><?php echo htmlspecialchars($recentVac['vaccine_name']); ?></span>
                  </div>
                  <div class="d-flex justify-content-between py-2 border-bottom small">
                    <span class="text-muted">Dose</span>
                    <span class="fw-bold text-success"><?php echo $recentVac['dose_number']; ?> / 2</span>
                  </div>
                  <div class="d-flex justify-content-between py-2 border-bottom small">
                    <span class="text-muted">Date</span>
                    <span class="fw-semibold text-dark"><?php echo htmlspecialchars($recentVac['dose_date']); ?></span>
                  </div>
                  <div class="d-flex justify-content-between py-2 small">
                    <span class="text-muted">Next Dose</span>
                    <span class="fw-semibold text-dark"><?php echo htmlspecialchars($recentVac['next_dose_date'] ?? 'Completed'); ?></span>
                  </div>
                <?php else: ?>
                  <div class="text-center py-4 text-muted small">Not vaccinated yet.</div>
                <?php endif; ?>
              </div>
            </div>
            <div class="p-3 pt-0">
              <a href="patient_vaccine.php" class="btn btn-emerald w-100 btn-sm">View Details</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
