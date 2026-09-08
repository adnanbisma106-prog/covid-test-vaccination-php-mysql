<?php
/**
 * Official Appointment Confirmation Slip (Printable)
 */
require_once __DIR__ . '/config/db.php';
requireAuth(['admin', 'hospital', 'patient']);

$aptId = $_GET['id'] ?? 'apt_101';

$apt = [
    'id' => $aptId,
    'patient_name' => 'Ali Khan',
    'patient_phone' => '03001234567',
    'hospital_name' => 'City Hospital',
    'service' => 'Test & Vaccination',
    'date' => '2025-05-12',
    'time' => '10:00 AM',
    'status' => 'Approved'
];

if ($conn && !$conn->connect_error) {
    $stmt = $conn->prepare("SELECT * FROM appointments WHERE id = ? LIMIT 1");
    if ($stmt) {
        $stmt->bind_param("s", $aptId);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        if ($res) $apt = $res;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Appointment Slip | <?php echo htmlspecialchars($apt['patient_name']); ?></title>
  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light p-4">

  <div class="container" style="max-width: 760px;">
    <!-- Action Bar -->
    <div class="d-flex justify-content-between align-items-center mb-3 no-print">
      <button onclick="window.history.back()" class="btn btn-outline-secondary btn-sm">
        <i class="fa-solid fa-arrow-left me-1"></i> Back
      </button>
      <button onclick="window.print()" class="btn btn-emerald btn-sm">
        <i class="fa-solid fa-print me-1"></i> Print Token Slip
      </button>
    </div>

    <!-- Printable Slip Frame -->
    <div id="printableCertificate" class="medical-sheet-frame bg-white shadow-sm p-4 p-md-5">
      <!-- Header -->
      <div class="d-flex justify-content-between align-items-center pb-3 border-bottom border-2 border-dark">
        <div class="d-flex align-items-center gap-3">
          <div class="bg-primary text-white p-3 rounded-3 fs-3">
            <i class="fa-solid fa-hospital-user"></i>
          </div>
          <div>
            <h4 class="fw-black text-dark mb-0 fs-6 text-uppercase tracking-wider">COVID-19 ONLINE REGISTRATION SYSTEM (ORS)</h4>
            <span class="small text-muted fw-semibold">Official Hospital Appointment Confirmation Slip</span>
          </div>
        </div>
        <div>
          <span class="badge-status badge-status-approved fs-6 fw-bold px-3 py-2">
            <?php echo strtoupper($apt['status']); ?>
          </span>
        </div>
      </div>

      <!-- Info Grid -->
      <div class="row g-3 my-4 py-2 border-bottom">
        <div class="col-6">
          <div class="small text-muted text-uppercase fw-bold">Appointment Token #:</div>
          <div class="font-monospace fw-bold fs-5 text-dark"><?php echo strtoupper($apt['id']); ?></div>
        </div>
        <div class="col-6">
          <div class="small text-muted text-uppercase fw-bold">Patient Name:</div>
          <div class="fw-bold fs-6 text-dark"><?php echo htmlspecialchars($apt['patient_name']); ?></div>
        </div>
        <div class="col-6">
          <div class="small text-muted text-uppercase fw-bold">Hospital Center:</div>
          <div class="fw-bold text-success"><?php echo htmlspecialchars($apt['hospital_name']); ?></div>
        </div>
        <div class="col-6">
          <div class="small text-muted text-uppercase fw-bold">Service Requested:</div>
          <div class="fw-semibold text-primary"><?php echo htmlspecialchars($apt['service']); ?></div>
        </div>
        <div class="col-6">
          <div class="small text-muted text-uppercase fw-bold">Scheduled Date:</div>
          <div class="fw-bold text-dark"><?php echo htmlspecialchars($apt['date']); ?></div>
        </div>
        <div class="col-6">
          <div class="small text-muted text-uppercase fw-bold">Scheduled Time Slot:</div>
          <div class="fw-bold text-dark"><?php echo htmlspecialchars($apt['time']); ?></div>
        </div>
      </div>

      <!-- Instructions Box -->
      <div class="p-3 bg-light border rounded-3 mb-4 small text-muted">
        <div class="fw-bold text-dark mb-1"><i class="fa-solid fa-circle-info text-primary me-1"></i> Patient Instructions:</div>
        <ul class="mb-0 ps-3">
          <li>Please arrive 15 minutes before your scheduled slot (<strong><?php echo htmlspecialchars($apt['time']); ?></strong>).</li>
          <li>Bring your original National ID (CNIC) and this printed booking token.</li>
          <li>Wear a surgical/N95 face mask and maintain standard social distancing.</li>
        </ul>
      </div>

      <!-- Footer -->
      <div class="d-flex justify-content-between align-items-end pt-3 border-top border-secondary border-dashed">
        <div class="d-flex align-items-center gap-3">
          <canvas id="qrSlipCanvas" width="80" height="80"></canvas>
          <div class="small">
            <div class="fw-bold text-primary"><i class="fa-solid fa-qrcode me-1"></i> Counter Verification</div>
            <div class="font-monospace text-muted" style="font-size: 0.7rem;">Token: <?php echo strtoupper($apt['id']); ?></div>
          </div>
        </div>

        <div class="text-end">
          <div class="small fw-bold text-primary mb-4"><i class="fa-solid fa-building-circle-check me-1"></i> ADMISSION DESK</div>
          <div class="border-top border-dark pt-1" style="width: 160px; margin-left: auto;">
            <div class="small fw-bold text-dark"><?php echo htmlspecialchars($apt['hospital_name']); ?></div>
            <div class="text-muted" style="font-size: 0.7rem;">Authorized Reception Desk</div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="assets/js/main.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      window.CovidApp.renderQR('qrSlipCanvas', 'https://covid19.gov/appointment/<?php echo $apt['id']; ?>');
    });
  </script>
</body>
</html>
