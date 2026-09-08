<?php
/**
 * Book Hospital Appointment (Matching Screenshot 6)
 */
require_once __DIR__ . '/config/db.php';
requireAuth(['patient']);

$pageTitle = "Book Appointment";
$patId = $currentUser['id'] ?? 'pat_ali';
$patName = $currentUser['name'] ?? 'Ali Khan';

// Handle Booking Form Submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hospitalName = trim($_POST['hospital_name'] ?? '');
    $service = $_POST['service'] ?? 'Test & Vaccination';
    $date = $_POST['date'] ?? date('Y-m-d', strtotime('+1 day'));
    $time = $_POST['time'] ?? '10:00 AM';
    $message = trim($_POST['message'] ?? '');

    $hospId = 'hosp_city';
    $hstmt = $conn->prepare("SELECT id FROM hospitals WHERE name = ? LIMIT 1");
    if ($hstmt) {
        $hstmt->bind_param("s", $hospitalName);
        $hstmt->execute();
        $hres = $hstmt->get_result()->fetch_assoc();
        if ($hres) $hospId = $hres['id'];
    }

    $id = 'apt_' . time();
    $status = 'Pending';

    $stmt = $conn->prepare("INSERT INTO appointments (id, patient_id, patient_name, patient_email, patient_phone, hospital_id, hospital_name, service, date, time, status, message) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    if ($stmt) {
        $email = $currentUser['email'] ?? 'ali@gmail.com';
        $phone = '03001234567';
        $stmt->bind_param("ssssssssssss", $id, $patId, $patName, $email, $phone, $hospId, $hospitalName, $service, $date, $time, $status, $message);
        $stmt->execute();
        logAudit($conn, $patName, "Booked $service appointment at $hospitalName on $date at $time");
        setFlash("Appointment booked successfully! Hospital will approve shortly.", "success");
        header("Location: patient_appointments.php");
        exit;
    }
}

// Fetch Approved Hospitals
$hospitals = [];
if ($conn && !$conn->connect_error) {
    $res = $conn->query("SELECT name, city, services FROM hospitals WHERE approved = 1");
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
      <div class="panel-card-custom" style="max-width: 960px;">
        <div class="panel-header-custom">
          <h2 class="panel-title-custom">
            <i class="fa-solid fa-calendar-plus text-success"></i> Book Appointment
          </h2>
        </div>

        <div class="p-4">
          <div class="row g-4">
            <!-- Left: Booking Form (Matching Screenshot 6) -->
            <div class="col-lg-8">
              <form method="POST">
                <div class="mb-3">
                  <label class="form-label small fw-bold text-muted">Select Hospital *</label>
                  <select name="hospital_name" class="form-select fw-semibold" required>
                    <?php if (!empty($hospitals)): ?>
                      <?php foreach ($hospitals as $h): ?>
                        <option value="<?php echo htmlspecialchars($h['name']); ?>" <?php echo (isset($_GET['hospital']) && $_GET['hospital'] === $h['name']) ? 'selected' : ''; ?>>
                          <?php echo htmlspecialchars($h['name']); ?> (<?php echo htmlspecialchars($h['city']); ?> - <?php echo htmlspecialchars($h['services']); ?>)
                        </option>
                      <?php endforeach; ?>
                    <?php else: ?>
                      <option value="City Hospital">City Hospital</option>
                    <?php endif; ?>
                  </select>
                </div>

                <div class="mb-3">
                  <label class="form-label small fw-bold text-muted">Select Service *</label>
                  <select name="service" class="form-select fw-semibold">
                    <option value="Test & Vaccination">Test &amp; Vaccination</option>
                    <option value="Covid Test">Covid Test (RT-PCR / Rapid Antigen)</option>
                    <option value="Vaccination">COVID-19 Vaccination</option>
                  </select>
                </div>

                <div class="row g-3 mb-3">
                  <div class="col-md-6">
                    <label class="form-label small fw-bold text-muted">Preferred Date *</label>
                    <input type="date" name="date" class="form-control" min="<?php echo date('Y-m-d'); ?>" value="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" required>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label small fw-bold text-muted">Preferred Time *</label>
                    <select name="time" class="form-select fw-semibold">
                      <option value="10:00 AM">10:00 AM</option>
                      <option value="11:30 AM">11:30 AM</option>
                      <option value="01:00 PM">01:00 PM</option>
                      <option value="02:30 PM">02:30 PM</option>
                      <option value="03:30 PM">03:30 PM</option>
                      <option value="04:30 PM">04:30 PM</option>
                    </select>
                  </div>
                </div>

                <div class="mb-4">
                  <label class="form-label small fw-bold text-muted">Message (Optional)</label>
                  <textarea name="message" class="form-control" rows="3" placeholder="Enter any message or symptom details for the medical staff..."></textarea>
                </div>

                <button type="submit" class="btn btn-emerald w-100 py-2 fs-6 shadow-sm">
                  <i class="fa-solid fa-calendar-check me-1"></i> Book Appointment
                </button>
              </form>
            </div>

            <!-- Right: Instructions Card (Matching Screenshot 6) -->
            <div class="col-lg-4">
              <div class="p-4 bg-light border rounded-3 h-100 d-flex flex-column justify-content-between">
                <div>
                  <h5 class="fw-bold text-dark fs-6 mb-3"><i class="fa-solid fa-circle-info text-success me-1"></i> Instructions</h5>
                  <ul class="list-unstyled small text-muted d-flex flex-column gap-3 mb-0">
                    <li class="d-flex align-items-start gap-2">
                      <i class="fa-solid fa-check text-success mt-1"></i>
                      <span>Please select hospital, service, date and time.</span>
                    </li>
                    <li class="d-flex align-items-start gap-2">
                      <i class="fa-solid fa-check text-success mt-1"></i>
                      <span>Hospital will approve your appointment.</span>
                    </li>
                    <li class="d-flex align-items-start gap-2">
                      <i class="fa-solid fa-check text-success mt-1"></i>
                      <span>You will get token notification on your portal.</span>
                    </li>
                    <li class="d-flex align-items-start gap-2">
                      <i class="fa-solid fa-check text-success mt-1"></i>
                      <span>Please bring CNIC/ID at the time of appointment.</span>
                    </li>
                  </ul>
                </div>

                <!-- Calendar Vector / Graphic matching Image 6 -->
                <div class="text-center py-4 text-success opacity-50">
                  <i class="fa-solid fa-calendar-days" style="font-size: 5.5rem;"></i>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
