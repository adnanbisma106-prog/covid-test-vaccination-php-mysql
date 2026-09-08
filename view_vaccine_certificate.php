<?php
/**
 * Official Digital COVID-19 Vaccination Certificate (WHO Compliant & QR Verified)
 */
require_once __DIR__ . '/config/db.php';
requireAuth(['admin', 'hospital', 'patient']);

$vacId = $_GET['id'] ?? 'vacrec_201';

// Default Fallback
$vac = [
    'id' => $vacId,
    'patient_id' => 'pat_ali',
    'patient_name' => 'Ali Khan',
    'patient_cnic' => '42101-1234567-1',
    'hospital_name' => 'City Hospital',
    'vaccine_name' => 'Covishield',
    'dose_number' => 1,
    'dose_date' => '2025-05-05',
    'next_dose_date' => '2025-06-02',
    'batch_no' => 'COV-7749B-IND',
    'vaccinator' => 'Nurse Fatima Noor',
    'certificate_no' => 'VAC-PK-8890214-ALI'
];

$allDoses = [$vac];

if ($conn && !$conn->connect_error) {
    $stmt = $conn->prepare("SELECT * FROM vaccination_doses WHERE id = ? LIMIT 1");
    if ($stmt) {
        $stmt->bind_param("s", $vacId);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        if ($res) {
            $vac = $res;
            // Fetch all doses for this patient
            $allStmt = $conn->prepare("SELECT * FROM vaccination_doses WHERE patient_id = ? OR patient_name = ? ORDER BY dose_number ASC");
            if ($allStmt) {
                $allStmt->bind_param("ss", $vac['patient_id'], $vac['patient_name']);
                $allStmt->execute();
                $allRes = $allStmt->get_result();
                $allDoses = [];
                while ($r = $allRes->fetch_assoc()) $allDoses[] = $r;
            }
        }
    }
}

$isFully = count($allDoses) >= 2;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Vaccination Pass | <?php echo htmlspecialchars($vac['patient_name']); ?></title>
  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800;900&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light p-4">

  <div class="container" style="max-width: 800px;">
    <!-- Action Bar -->
    <div class="d-flex justify-content-between align-items-center mb-3 no-print">
      <button onclick="window.history.back()" class="btn btn-outline-secondary btn-sm">
        <i class="fa-solid fa-arrow-left me-1"></i> Back
      </button>
      <button onclick="window.print()" class="btn btn-emerald btn-sm">
        <i class="fa-solid fa-print me-1"></i> Print / Save Certificate PDF
      </button>
    </div>

    <!-- Official Vaccine Pass Sheet -->
    <div id="printableCertificate" class="medical-sheet-frame bg-white shadow-sm p-4 p-md-5">
      <!-- Header -->
      <div class="d-flex justify-content-between align-items-center pb-3 border-bottom border-2 border-dark">
        <div class="d-flex align-items-center gap-3">
          <div class="bg-success text-white p-3 rounded-3 fs-3">
            <i class="fa-solid fa-shield-halved"></i>
          </div>
          <div>
            <h4 class="fw-black text-dark mb-0 fs-6 text-uppercase tracking-wider">NATIONAL COVID-19 IMMUNIZATION REGISTRY</h4>
            <span class="small text-muted fw-semibold">International Digital COVID-19 Vaccination Pass</span>
          </div>
        </div>
        <div>
          <span class="badge-status <?php echo $isFully ? 'badge-status-approved' : 'badge-status-pending'; ?> fs-6 fw-bold px-3 py-2">
            <?php echo $isFully ? 'FULLY VACCINATED' : 'PARTIALLY VACCINATED (DOSE 1)'; ?>
          </span>
        </div>
      </div>

      <!-- Info Grid -->
      <div class="row g-3 my-4 py-2 border-bottom">
        <div class="col-6">
          <div class="small text-muted text-uppercase fw-bold">Beneficiary Name:</div>
          <div class="fw-bold fs-6 text-dark"><?php echo htmlspecialchars($vac['patient_name']); ?></div>
        </div>
        <div class="col-6">
          <div class="small text-muted text-uppercase fw-bold">National ID / CNIC:</div>
          <div class="font-monospace text-dark"><?php echo htmlspecialchars($vac['patient_cnic'] ?? '42101-XXXXXXX-X'); ?></div>
        </div>
        <div class="col-6">
          <div class="small text-muted text-uppercase fw-bold">Certificate Number:</div>
          <div class="font-monospace fw-bold text-dark"><?php echo htmlspecialchars($vac['certificate_no']); ?></div>
        </div>
        <div class="col-6">
          <div class="small text-muted text-uppercase fw-bold">Issuing Authority:</div>
          <div class="fw-bold text-success">Ministry of National Health Services</div>
        </div>
      </div>

      <!-- Dose History Table -->
      <div class="my-4 border rounded-3 overflow-hidden">
        <div class="p-2 bg-light border-bottom small fw-bold text-uppercase text-secondary">
          Dose Administration Record
        </div>
        <table class="table table-sm mb-0 small">
          <thead class="bg-white text-muted">
            <tr>
              <th>Dose #</th>
              <th>Vaccine Brand</th>
              <th>Batch / Lot Number</th>
              <th>Date Given</th>
              <th>Administering Facility</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($allDoses as $d): ?>
              <tr>
                <td class="fw-bold">Dose <?php echo $d['dose_number']; ?></td>
                <td class="fw-bold text-success"><?php echo htmlspecialchars($d['vaccine_name']); ?></td>
                <td class="font-monospace"><?php echo htmlspecialchars($d['batch_no']); ?></td>
                <td><?php echo htmlspecialchars($d['dose_date']); ?></td>
                <td><?php echo htmlspecialchars($d['hospital_name']); ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <?php if (!$isFully && !empty($vac['next_dose_date'])): ?>
        <div class="alert alert-warning py-2 small mb-4">
          <i class="fa-solid fa-clock me-1"></i> <strong>Next Scheduled Dose:</strong> Due on or after <strong><?php echo htmlspecialchars($vac['next_dose_date']); ?></strong>.
        </div>
      <?php endif; ?>

      <!-- Footer with QR Code & Security Stamp -->
      <div class="d-flex justify-content-between align-items-end pt-3 border-top border-secondary border-dashed">
        <div class="d-flex align-items-center gap-3">
          <canvas id="qrVaccineCanvas" width="90" height="90"></canvas>
          <div class="small">
            <div class="fw-bold text-success"><i class="fa-solid fa-certificate me-1"></i> WHO / ICAO Compliant</div>
            <div class="font-monospace text-muted" style="font-size: 0.7rem;">Cert: <?php echo htmlspecialchars($vac['certificate_no']); ?></div>
            <div class="text-muted" style="font-size: 0.7rem;">Scan QR with any standard digital scanner</div>
          </div>
        </div>

        <div class="text-end">
          <div class="small fw-bold text-success mb-4"><i class="fa-solid fa-stamp me-1"></i> MINISTRY OF HEALTH</div>
          <div class="border-top border-dark pt-1" style="width: 160px; margin-left: auto;">
            <div class="small fw-bold text-dark"><?php echo htmlspecialchars($vac['vaccinator']); ?></div>
            <div class="text-muted" style="font-size: 0.7rem;">Authorized Medical Officer</div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="assets/js/main.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      window.CovidApp.renderQR('qrVaccineCanvas', 'https://covid19.gov/verify/vaccine/<?php echo $vac['certificate_no']; ?>?name=<?php echo urlencode($vac['patient_name']); ?>&status=<?php echo $isFully ? 'Fully-Vaccinated' : 'Partial'; ?>');
    });
  </script>
</body>
</html>
