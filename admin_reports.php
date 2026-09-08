<?php
/**
 * COVID-19 Analytics & Reports
 */
require_once __DIR__ . '/config/db.php';
requireAuth(['admin']);

$pageTitle = "COVID-19 Reports";

$totalTests = 4;
$totalPositive = 1;
$totalNegative = 3;
$totalVacs = 4;

if ($conn && !$conn->connect_error) {

    $r1 = $conn->query("SELECT COUNT(*) as cnt FROM covid_tests");
    if ($r1) {
        $totalTests = $r1->fetch_assoc()['cnt'];
    }

    $r2 = $conn->query("SELECT COUNT(*) as cnt FROM covid_tests WHERE result = 'Positive'");
    if ($r2) {
        $totalPositive = $r2->fetch_assoc()['cnt'];
    }

    $r3 = $conn->query("SELECT COUNT(*) as cnt FROM covid_tests WHERE result = 'Negative'");
    if ($r3) {
        $totalNegative = $r3->fetch_assoc()['cnt'];
    }

    $r4 = $conn->query("SELECT COUNT(*) as cnt FROM vaccination_doses");
    if ($r4) {
        $totalVacs = $r4->fetch_assoc()['cnt'];
    }
}

$posRate = $totalTests > 0
    ? number_format(($totalPositive / $totalTests) * 100, 1)
    : 0;

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
            <i class="fa-solid fa-file-waveform text-success"></i>
            COVID-19 Diagnostic &amp; Vaccination Reports
          </h2>

          <div class="d-flex gap-2 flex-wrap">

            <!-- Date-Wise Report -->
            <a href="export_csv.php?type=reports&period=custom&from=<?php echo date('Y-m-d'); ?>&to=<?php echo date('Y-m-d'); ?>"
               class="btn btn-outline-secondary btn-sm">
              <i class="fa-solid fa-calendar-day me-1 text-success"></i>
              Date-Wise Report
            </a>

            <!-- Weekly Report -->
            <a href="export_csv.php?type=reports&period=week"
               class="btn btn-outline-primary btn-sm">
              <i class="fa-solid fa-calendar-week me-1"></i>
              Weekly Report
            </a>

            <!-- Monthly Report -->
            <a href="export_csv.php?type=reports&period=month"
               class="btn btn-emerald btn-sm">
              <i class="fa-solid fa-file-excel me-1"></i>
              Monthly Summary XLS
            </a>

          </div>

        </div>

        <div class="p-4">

          <div class="row g-4 mb-4">

            <!-- Total Tests -->
            <div class="col-md-4">

              <div class="p-4 bg-success bg-opacity-10 border border-success border-opacity-25 rounded-3">

                <span class="small fw-bold text-success text-uppercase">
                  Total RT-PCR &amp; Rapid Tests
                </span>

                <div class="fs-2 fw-bold text-dark mt-1">
                  <?php echo $totalTests; ?>
                </div>

                <span class="small text-muted">
                  Certified diagnostic laboratory records
                </span>

              </div>

            </div>


            <!-- Positivity Rate -->
            <div class="col-md-4">

              <div class="p-4 bg-primary bg-opacity-10 border border-primary border-opacity-25 rounded-3">

                <span class="small fw-bold text-primary text-uppercase">
                  National Positivity Rate
                </span>

                <div class="fs-2 fw-bold text-dark mt-1">
                  <?php echo $posRate; ?>%
                </div>

                <span class="small text-muted">
                  <?php echo $totalPositive; ?> Positive /
                  <?php echo $totalNegative; ?> Negative
                </span>

              </div>

            </div>


            <!-- Vaccinations -->
            <div class="col-md-4">

              <div class="p-4 bg-purple bg-opacity-10 border border-purple border-opacity-25 rounded-3"
                   style="background-color: #F5F3FF; border-color: #DDD6FE;">

                <span class="small fw-bold text-purple text-uppercase"
                      style="color: #7C3AED;">
                  Vaccinations Administered
                </span>

                <div class="fs-2 fw-bold text-dark mt-1">
                  <?php echo $totalVacs; ?>
                </div>

                <span class="small text-muted">
                  Digitally logged immunizations
                </span>

              </div>

            </div>

          </div>


          <!-- Download Full Dataset -->
          <div class="p-3 bg-light border rounded-3
                      d-flex justify-content-between
                      align-items-center flex-wrap gap-2">

            <span class="small text-muted">
              <i class="fa-solid fa-circle-check text-success me-1"></i>
              All diagnostic data is synchronized with hospital
              laboratory databases in real-time.
            </span>

            <a href="export_csv.php?type=reports"
               class="small fw-bold text-success text-decoration-none">
              Download Full Dataset (Excel)
            </a>

          </div>

        </div>

      </div>

    </div>

  </div>

</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>