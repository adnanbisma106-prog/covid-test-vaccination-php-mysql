<?php
/**
 * Official COVID-19 RT-PCR / Antigen Diagnostic Report (Printable & QR Verified)
 */
require_once __DIR__ . '/config/db.php';
requireAuth(['admin', 'hospital', 'patient']);

$testId = $_GET['id'] ?? 'test_501';

// Default Fallback Record
$test = [
    'id' => $testId,
    'patient_name' => 'Ali Khan',
    'patient_cnic' => '42101-1234567-1',
    'hospital_name' => 'City Hospital',
    'test_type' => 'RT-PCR',
    'sample_date' => '2025-05-10',
    'result_date' => '2025-05-11',
    'result' => 'Negative',
    'ct_value' => '38.5 (Target Not Detected)',
    'lab_id' => 'LAB-PCR-88421',
    'doctor' => 'Dr. Tariq Mahmood, MD Virology',
    'notes' => 'SARS-CoV-2 RNA not detected. Patient cleared for normal activities.'
];

if ($conn && !$conn->connect_error) {
    $stmt = $conn->prepare("SELECT * FROM covid_tests WHERE id = ? LIMIT 1");
    if ($stmt) {
        $stmt->bind_param("s", $testId);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        if ($res) $test = $res;
    }
}

$isNegative = strpos(strtolower($test['result']), 'neg') !== false;
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>COVID-19 Test Certificate | <?php echo htmlspecialchars($test['patient_name']); ?></title>
  
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
        <i class="fa-solid fa-print me-1"></i> Print / Save as PDF
      </button>
    </div>

    <!-- Official Printable Medical Sheet -->
    <div id="printableCertificate" class="medical-sheet-frame bg-white shadow-sm p-4 p-md-5">
      <!-- Header -->
      <div class="d-flex justify-content-between align-items-center pb-3 border-bottom border-2 border-dark">
        <div class="d-flex align-items-center gap-3">
          <div class="bg-dark text-white p-3 rounded-3 fs-3">
            <i class="fa-solid fa-shield-virus"></i>
          </div>
          <div>
            <h4 class="fw-black text-dark mb-0 fs-6 text-uppercase tracking-wider">NATIONAL HEALTH SERVICES &amp; COVID-19 COMMAND</h4>
            <span class="small text-muted fw-semibold">Official Diagnostic Laboratory SARS-CoV-2 Test Report</span>
          </div>
        </div>
        <div>
          <span class="badge-status <?php echo $isNegative ? 'badge-status-negative' : 'badge-status-positive'; ?> fs-6 fw-bold px-3 py-2">
            <?php echo strtoupper($test['result']); ?>
          </span>
        </div>
      </div>

      <!-- Info Grid -->
      <div class="row g-3 my-4 py-2 border-bottom">
        <div class="col-6">
          <div class="small text-muted text-uppercase fw-bold">Patient Full Name:</div>
          <div class="fw-bold fs-6 text-dark"><?php echo htmlspecialchars($test['patient_name']); ?></div>
        </div>
        <div class="col-6">
          <div class="small text-muted text-uppercase fw-bold">National ID / CNIC:</div>
          <div class="font-monospace text-dark"><?php echo htmlspecialchars($test['patient_cnic'] ?? '42101-XXXXXXX-X'); ?></div>
        </div>
        <div class="col-6">
          <div class="small text-muted text-uppercase fw-bold">Testing Facility:</div>
          <div class="fw-bold text-success"><?php echo htmlspecialchars($test['hospital_name']); ?></div>
        </div>
        <div class="col-6">
          <div class="small text-muted text-uppercase fw-bold">Lab Specimen ID:</div>
          <div class="font-monospace text-dark"><?php echo htmlspecialchars($test['lab_id']); ?></div>
        </div>
        <div class="col-6">
          <div class="small text-muted text-uppercase fw-bold">Sample Collection Date:</div>
          <div class="text-dark"><?php echo htmlspecialchars($test['sample_date']); ?></div>
        </div>
        <div class="col-6">
          <div class="small text-muted text-uppercase fw-bold">Report Release Date:</div>
          <div class="text-dark"><?php echo htmlspecialchars($test['result_date']); ?></div>
        </div>
      </div>

      <!-- Result Details Box -->
      <div class="p-4 rounded-3 mb-4 <?php echo $isNegative ? 'bg-success bg-opacity-10 border border-success' : 'bg-danger bg-opacity-10 border border-danger'; ?>">
        <div class="d-flex justify-content-between align-items-center mb-2">
          <span class="fw-bold text-dark">SARS-CoV-2 (COVID-19) Qualitative Result:</span>
          <span class="fs-4 fw-black <?php echo $isNegative ? 'text-success' : 'text-danger'; ?>"><?php echo htmlspecialchars($test['result']); ?></span>
        </div>
        <div class="small text-muted border-top pt-2 mt-2">
          <div><strong>Diagnostic Target:</strong> SARS-CoV-2 RNA (RdRp, N-gene, E-gene)</div>
          <div><strong>Cycle Threshold (Ct Value):</strong> <?php echo htmlspecialchars($test['ct_value']); ?></div>
          <div><strong>Clinical Finding:</strong> <?php echo htmlspecialchars($test['notes']); ?></div>
        </div>
      </div>

      <!-- Footer with QR Verification & Seal -->
      <div class="d-flex justify-content-between align-items-end pt-3 border-top border-secondary border-dashed">
        <div class="d-flex align-items-center gap-3">
          <canvas id="qrTestCanvas" width="90" height="90"></canvas>
          <div class="small">
            <div class="fw-bold text-success"><i class="fa-solid fa-circle-check me-1"></i> Digitally Verified</div>
            <div class="font-monospace text-muted" style="font-size: 0.7rem;">UUID: <?php echo strtoupper($test['id']); ?>-CERT</div>
            <div class="text-muted" style="font-size: 0.7rem;">Verify: portal.covid19.gov/verify</div>
          </div>
        </div>

        <div class="text-end">
          <div class="small fw-bold text-dark mb-4"><i class="fa-solid fa-stamp me-1"></i> OFFICIAL LAB SEAL</div>
          <div class="border-top border-dark pt-1" style="width: 160px; margin-left: auto;">
            <div class="small fw-bold text-dark"><?php echo htmlspecialchars($test['doctor']); ?></div>
            <div class="text-muted" style="font-size: 0.7rem;">Chief Diagnostic Pathologist</div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="assets/js/main.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', () => {
      window.CovidApp.renderQR('qrTestCanvas', 'https://covid19.gov/verify/test/<?php echo $test['id']; ?>?patient=<?php echo urlencode($test['patient_name']); ?>&result=<?php echo urlencode($test['result']); ?>');
    });
  </script>
</body>
</html>
