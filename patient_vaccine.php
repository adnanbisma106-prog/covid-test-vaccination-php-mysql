<?php
/**
 * Patient Vaccination Status & Immunization Pass
 */

require_once __DIR__ . '/config/db.php';
requireAuth(['patient']);

$pageTitle = "Vaccination Status";

/*
 * Current logged-in patient
 */
$patId   = $_SESSION['user_id'] ?? '';
$patName = $_SESSION['user_name'] ?? '';
$patCnic = '';

/*
 * Get actual patient information from users table
 */
if ($conn && !$conn->connect_error && $patId !== '') {

    $stmt = $conn->prepare("
        SELECT name, cnic
        FROM users
        WHERE id = ?
        AND role = 'patient'
        LIMIT 1
    ");

    if ($stmt) {
        $stmt->bind_param("s", $patId);
        $stmt->execute();

        $userData = $stmt->get_result()->fetch_assoc();

        if ($userData) {
            $patName = $userData['name'] ?? $patName;
            $patCnic = $userData['cnic'] ?? '';
        }

        $stmt->close();
    }
}


/*
 * Fetch vaccination doses
 */
$doses = [];

if ($conn && !$conn->connect_error && $patId !== '') {

    /*
     * First try patient_id
     */
    $stmt = $conn->prepare("
        SELECT *
        FROM vaccination_doses
        WHERE patient_id = ?
        ORDER BY dose_number ASC, dose_date ASC
    ");

    if ($stmt) {

        $stmt->bind_param("s", $patId);
        $stmt->execute();

        $res = $stmt->get_result();

        while ($row = $res->fetch_assoc()) {
            $doses[] = $row;
        }

        $stmt->close();
    }


    /*
     * If no records found,
     * try patient_name for old/demo records.
     */
    if (empty($doses) && $patName !== '') {

        $stmt = $conn->prepare("
            SELECT *
            FROM vaccination_doses
            WHERE patient_name = ?
            ORDER BY dose_number ASC, dose_date ASC
        ");

        if ($stmt) {

            $stmt->bind_param("s", $patName);
            $stmt->execute();

            $res = $stmt->get_result();

            while ($row = $res->fetch_assoc()) {
                $doses[] = $row;
            }

            $stmt->close();
        }
    }
}


/*
 * Check immunization status
 */
$isFully = count($doses) >= 2;


/*
 * Header
 */
require_once __DIR__. '/includes/header.php';
?>


<div class="app-container">

  <?php require_once __DIR__ . '/includes/sidebar.php'; ?>


  <div class="main-content">

    <?php require_once __DIR__ . '/includes/topbar.php'; ?>


    <div class="page-body">

      <div class="panel-card-custom" style="max-width: 900px;">

        <!-- Page Header -->

        <div class="panel-header-custom">

          <h2 class="panel-title-custom">

            <i class="fa-solid fa-syringe text-success"></i>

            Official Vaccination Immunization Record

          </h2>

        </div>


        <div class="p-4">


          <?php if (!empty($doses)): ?>


            <!-- Digital Pass Banner -->

            <div
              class="p-4 text-white rounded-3 mb-4 shadow-sm"
              style="background: linear-gradient(135deg, #065F46 0%, #047857 50%, #0D9488 100%);"
            >

              <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">


                <div>

                  <!-- Immunization Status -->

                  <span class="badge bg-white bg-opacity-25 text-white mb-2 text-uppercase fw-bold">

                    <?php
                    echo $isFully
                        ? 'Fully Immunized'
                        : 'Partially Immunized';
                    ?>

                  </span>


                  <!-- Patient Name -->

                  <h3 class="fw-bold mb-1 fs-4">

                    <?php echo htmlspecialchars($patName); ?>

                  </h3>


                  <!-- Actual Patient CNIC -->

                  <div class="small opacity-75 font-monospace">

                    CNIC:

                    <?php
                    echo htmlspecialchars(
                        $patCnic !== '' ? $patCnic : 'Not Available'
                    );
                    ?>

                  </div>

                </div>


                <!-- Digital Pass -->

                <a
                  href="view_vaccine_certificate.php?id=<?php echo (int)$doses[0]['id']; ?>"
                  class="btn btn-light btn-sm fw-bold text-success shadow"
                  target="_blank"
                >

                  <i class="fa-solid fa-qrcode me-1"></i>

                  View Digital Pass

                </a>

              </div>

            </div>


            <!-- Dose Timeline -->

            <div class="d-flex flex-column gap-3">


              <?php foreach ($doses as $d): ?>


                <div
                  class="p-3 bg-white border rounded-3 d-flex justify-content-between align-items-center flex-wrap gap-3"
                >


                  <div class="d-flex align-items-center gap-3">


                    <!-- Dose Number -->

                    <div
                      class="rounded-3 bg-success bg-opacity-10 text-success fw-bold d-flex align-items-center justify-content-center"
                      style="width: 48px; height: 48px; font-size: 1.25rem;"
                    >

                      #

                      <?php echo (int)$d['dose_number']; ?>

                    </div>


                    <div>


                      <!-- Vaccine Name -->

                      <h5 class="fw-bold text-dark fs-6 mb-0">

                        Dose

                        <?php echo (int)$d['dose_number']; ?>

                        :

                        <?php
                        echo htmlspecialchars(
                            $d['vaccine_name'] ?? 'COVID-19 Vaccine'
                        );
                        ?>

                      </h5>


                      <!-- Hospital + Date -->

                      <div class="small text-muted mt-1">

                        <i class="fa-solid fa-hospital me-1"></i>

                        <?php
                        echo htmlspecialchars(
                            $d['hospital_name'] ?? 'Hospital'
                        );
                        ?>

                        &bull;

                        <i class="fa-solid fa-calendar me-1"></i>

                        <?php
                        echo htmlspecialchars(
                            $d['dose_date'] ?? ''
                        );
                        ?>

                      </div>


                      <!-- Batch -->

                      <div class="small text-muted font-monospace mt-1">

                        Batch:

                        <?php
                        echo htmlspecialchars(
                            $d['batch_no'] ?? 'Not Available'
                        );
                        ?>

                      </div>


                      <!-- Next Dose -->

                      <?php if (!empty($d['next_dose_date'])): ?>

                        <div class="small text-muted mt-1">

                          <i class="fa-solid fa-calendar-plus me-1"></i>

                          Next Dose:

                          <?php
                          echo htmlspecialchars(
                              $d['next_dose_date']
                          );
                          ?>

                        </div>

                      <?php endif; ?>


                    </div>

                  </div>


                  <!-- Certificate Button -->

                  <a
                    href="view_vaccine_certificate.php?id=<?php echo (int)$d['id']; ?>"
                    class="btn btn-sm btn-outline-success"
                    target="_blank"
                  >

                    <i class="fa-solid fa-certificate me-1"></i>

                    Certificate

                  </a>


                </div>


              <?php endforeach; ?>


            </div>


          <?php else: ?>


            <!-- No Vaccination Records -->

            <div class="text-center py-5 bg-light rounded-3 border">


              <i
                class="fa-solid fa-syringe text-secondary fs-1 mb-2 d-block opacity-50"
              ></i>


              <h5 class="fw-bold text-dark">

                No Vaccination Records Found

              </h5>


              <p class="small text-muted mb-3">

                No vaccination records are currently available for

                <?php echo htmlspecialchars($patName); ?>.

              </p>


              <a
                href="patient_book.php"
                class="btn btn-emerald btn-sm"
              >

                <i class="fa-solid fa-calendar-plus me-1"></i>

                Book Vaccination Slot

              </a>


            </div>


          <?php endif; ?>


        </div>

      </div>

    </div>

  </div>

</div>


<?php require_once __DIR__ . '/includes/footer.php'; ?>