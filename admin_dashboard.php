<?php
/**
 * Admin Dashboard
 * COVID-19 Vaccination & Hospital Management System
 */

require_once __DIR__ . '/config/db.php';
requireAuth(['admin']);

$pageTitle = "Admin Dashboard";

/*
|--------------------------------------------------------------------------
| Database Statistics
|--------------------------------------------------------------------------
*/

$totalPatients = 0;
$totalHospitals = 0;
$totalAppointments = 0;
$totalTests = 0;

if ($conn && !$conn->connect_error) {

    /* Total Patients */
    $res1 = $conn->query(
        "SELECT COUNT(*) AS cnt
         FROM users
         WHERE role = 'patient'"
    );

    if ($res1) {
        $row = $res1->fetch_assoc();
        $totalPatients = (int)$row['cnt'];
    }

    /* Total Hospitals */
    $res2 = $conn->query(
        "SELECT COUNT(*) AS cnt
         FROM hospitals"
    );

    if ($res2) {
        $row = $res2->fetch_assoc();
        $totalHospitals = (int)$row['cnt'];
    }

    /* Total Appointments */
    $res3 = $conn->query(
        "SELECT COUNT(*) AS cnt
         FROM appointments"
    );

    if ($res3) {
        $row = $res3->fetch_assoc();
        $totalAppointments = (int)$row['cnt'];
    }

    /* Total COVID Tests */
    $res4 = $conn->query(
        "SELECT COUNT(*) AS cnt
         FROM covid_tests"
    );

    if ($res4) {
        $row = $res4->fetch_assoc();
        $totalTests = (int)$row['cnt'];
    }
}


/*
|--------------------------------------------------------------------------
| Recent Appointments
|--------------------------------------------------------------------------
*/

$recentApts = [];

if ($conn && !$conn->connect_error) {

    $res = $conn->query(
        "SELECT *
         FROM appointments
         ORDER BY id DESC
         LIMIT 5"
    );

    if ($res) {
        while ($row = $res->fetch_assoc()) {
            $recentApts[] = $row;
        }
    }
}


/*
|--------------------------------------------------------------------------
| Header
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/includes/header.php';
?>

<div class="app-container">

    <!-- LEFT SIDEBAR -->
    <?php require_once __DIR__ . '/includes/sidebar.php'; ?>


    <!-- MAIN CONTENT -->
    <div class="main-content">

        <!-- TOPBAR -->
        <?php require_once __DIR__ . '/includes/topbar.php'; ?>


        <div class="page-body">

            <!-- PAGE HEADER -->
            <div class="d-flex justify-content-between align-items-center mb-4">

                <div>

                    <h2 class="fw-bold text-dark fs-3 mb-1">
                        Admin Dashboard
                    </h2>

                    <p class="text-muted small mb-0">
                        Overview of nationwide COVID-19 testing,
                        vaccination, and hospital operations
                    </p>

                </div>


                <!-- BUTTONS -->
                <div class="d-flex gap-2">

                    <a href="admin_hospitals.php"
                       class="btn btn-success btn-sm">

                        <i class="fa-solid fa-hospital me-1"></i>
                        Add Hospital

                    </a>


                    <a href="export_csv.php?type=reports"
                       class="btn btn-outline-secondary btn-sm">

                        <i class="fa-solid fa-file-export me-1 text-success"></i>
                        Export Summary

                    </a>

                </div>

            </div>



            <!-- =====================================================
                 STAT CARDS
            ====================================================== -->

            <div class="row g-3 mb-4">


                <!-- TOTAL PATIENTS -->
                <div class="col-sm-6 col-xl-3">

                    <a href="admin_patients.php"
                       class="text-decoration-none">

                        <div class="stat-card-custom">

                            <div class="stat-icon-box stat-icon-green">

                                <i class="fa-solid fa-users"></i>

                            </div>

                            <div>

                                <div class="stat-label-text">
                                    Total Patients
                                </div>

                                <div class="stat-number-val">

                                    <?php
                                    echo number_format($totalPatients);
                                    ?>

                                </div>

                                <small class="text-success">
                                    View Patients →
                                </small>

                            </div>

                        </div>

                    </a>

                </div>



                <!-- TOTAL HOSPITALS -->
                <div class="col-sm-6 col-xl-3">

                    <a href="admin_hospitals.php"
                       class="text-decoration-none">

                        <div class="stat-card-custom">

                            <div class="stat-icon-box stat-icon-blue">

                                <i class="fa-solid fa-hospital"></i>

                            </div>

                            <div>

                                <div class="stat-label-text">
                                    Total Hospitals
                                </div>

                                <div class="stat-number-val">

                                    <?php
                                    echo number_format($totalHospitals);
                                    ?>

                                </div>

                                <small class="text-primary">
                                    Manage Hospitals →
                                </small>

                            </div>

                        </div>

                    </a>

                </div>



                <!-- TOTAL APPOINTMENTS -->
                <div class="col-sm-6 col-xl-3">

                    <a href="admin_appointments.php"
                       class="text-decoration-none">

                        <div class="stat-card-custom">

                            <div class="stat-icon-box stat-icon-purple">

                                <i class="fa-solid fa-calendar-check"></i>

                            </div>

                            <div>

                                <div class="stat-label-text">
                                    Appointments
                                </div>

                                <div class="stat-number-val">

                                    <?php
                                    echo number_format($totalAppointments);
                                    ?>

                                </div>

                                <small class="text-primary">
                                    View Appointments →
                                </small>

                            </div>

                        </div>

                    </a>

                </div>



                <!-- TOTAL TESTS -->
                <div class="col-sm-6 col-xl-3">

                    <a href="admin_reports.php"
                       class="text-decoration-none">

                        <div class="stat-card-custom">

                            <div class="stat-icon-box stat-icon-amber">

                                <i class="fa-solid fa-vial-virus"></i>

                            </div>

                            <div>

                                <div class="stat-label-text">
                                    Tests Conducted
                                </div>

                                <div class="stat-number-val">

                                    <?php
                                    echo number_format($totalTests);
                                    ?>

                                </div>

                                <small class="text-warning">
                                    View Reports →
                                </small>

                            </div>

                        </div>

                    </a>

                </div>

            </div>



            <!-- =====================================================
                 QUICK ACTIONS
            ====================================================== -->

            <div class="panel-card-custom mb-4">

                <div class="panel-header-custom">

                    <h3 class="panel-title-custom">

                        <i class="fa-solid fa-bolt text-success"></i>

                        Quick Actions

                    </h3>

                </div>


                <div class="p-3">

                    <div class="row g-3">


                        <!-- ADD HOSPITAL -->
                        <div class="col-md-3">

                            <a href="admin_hospitals.php"
                               class="btn btn-outline-success w-100 py-3">

                                <i class="fa-solid fa-hospital me-2"></i>

                                Add / Manage Hospital

                            </a>

                        </div>



                        <!-- PATIENTS -->
                        <div class="col-md-3">

                            <a href="admin_patients.php"
                               class="btn btn-outline-primary w-100 py-3">

                                <i class="fa-solid fa-users me-2"></i>

                                Patient Management

                            </a>

                        </div>



                        <!-- APPOINTMENTS -->
                        <div class="col-md-3">

                            <a href="admin_appointments.php"
                               class="btn btn-outline-info w-100 py-3">

                                <i class="fa-solid fa-calendar-check me-2"></i>

                                Appointment Management

                            </a>

                        </div>



                        <!-- REPORTS -->
                        <div class="col-md-3">

                            <a href="admin_reports.php"
                               class="btn btn-outline-warning w-100 py-3">

                                <i class="fa-solid fa-chart-column me-2"></i>

                                View Reports

                            </a>

                        </div>

                    </div>

                </div>

            </div>



            <!-- =====================================================
                 CHART + RECENT APPOINTMENTS
            ====================================================== -->

            <div class="row g-4">


                <!-- APPOINTMENTS CHART -->
                <div class="col-lg-8">

                    <div class="panel-card-custom h-100 mb-0">

                        <div class="panel-header-custom">

                            <h3 class="panel-title-custom">

                                <i class="fa-solid fa-chart-line text-success"></i>

                                Appointments Overview

                            </h3>

                            <span class="badge bg-light text-secondary border">
                                Jan - Dec Trend
                            </span>

                        </div>


                        <div class="p-3"
                             style="height: 300px;">

                            <canvas id="adminAppointmentsChart"></canvas>

                        </div>

                    </div>

                </div>



                <!-- RECENT APPOINTMENTS -->
                <div class="col-lg-4">

                    <div class="panel-card-custom h-100 mb-0">


                        <div class="panel-header-custom">

                            <h3 class="panel-title-custom fs-6">

                                <i class="fa-solid fa-clock text-primary"></i>

                                Recent Appointments

                            </h3>


                            <a href="admin_appointments.php"
                               class="small fw-bold text-success text-decoration-none">

                                View All

                            </a>

                        </div>



                        <div class="table-responsive">

                            <table
                                class="table table-hover align-middle mb-0"
                                style="font-size:0.825rem;">

                                <tbody>

                                <?php if (!empty($recentApts)): ?>

                                    <?php foreach ($recentApts as $apt): ?>

                                        <tr>

                                            <!-- PATIENT -->
                                            <td>

                                                <div class="d-flex align-items-center gap-2">

                                                    <div
                                                        class="rounded-circle
                                                               bg-success
                                                               bg-opacity-10
                                                               text-success
                                                               fw-bold
                                                               d-flex
                                                               align-items-center
                                                               justify-content-center"
                                                        style="width:28px;
                                                               height:28px;
                                                               font-size:0.75rem;">

                                                        <?php

                                                        $patientName =
                                                            $apt['patient_name'] ?? 'P';

                                                        echo htmlspecialchars(
                                                            substr($patientName, 0, 1)
                                                        );

                                                        ?>

                                                    </div>


                                                    <div>

                                                        <div class="fw-bold text-dark">

                                                            <?php
                                                            echo htmlspecialchars(
                                                                $patientName
                                                            );
                                                            ?>

                                                        </div>


                                                        <div
                                                            class="text-muted"
                                                            style="font-size:0.7rem;">

                                                            <?php
                                                            echo htmlspecialchars(
                                                                $apt['service'] ?? 'Appointment'
                                                            );
                                                            ?>

                                                        </div>

                                                    </div>

                                                </div>

                                            </td>



                                            <!-- DATE/TIME -->
                                            <td class="text-muted">

                                                <?php

                                                echo htmlspecialchars(
                                                    $apt['date'] ?? '-'
                                                );

                                                ?>

                                                <br>

                                                <small class="text-dark fw-semibold">

                                                    <?php

                                                    echo htmlspecialchars(
                                                        $apt['time'] ?? '-'
                                                    );

                                                    ?>

                                                </small>

                                            </td>



                                            <!-- STATUS -->
                                            <td>

                                                <?php

                                                $status =
                                                    $apt['status'] ?? 'Pending';

                                                $st =
                                                    strtolower($status);

                                                if ($st === 'approved') {

                                                    $badgeClass =
                                                        'badge-status-approved';

                                                } elseif ($st === 'rejected') {

                                                    $badgeClass =
                                                        'badge-status-rejected';

                                                } else {

                                                    $badgeClass =
                                                        'badge-status-pending';

                                                }

                                                ?>

                                                <span
                                                    class="badge-status
                                                           <?php echo $badgeClass; ?>">

                                                    <?php
                                                    echo htmlspecialchars($status);
                                                    ?>

                                                </span>

                                            </td>

                                        </tr>

                                    <?php endforeach; ?>


                                <?php else: ?>

                                    <tr>

                                        <td
                                            colspan="3"
                                            class="text-center py-4 text-muted">

                                            No appointments found.

                                        </td>

                                    </tr>

                                <?php endif; ?>

                                </tbody>

                            </table>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>



<!-- ============================================================
     CHART.JS
============================================================= -->

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


<script>

document.addEventListener('DOMContentLoaded', function () {

    const adminChartCanvas =
        document.getElementById('adminAppointmentsChart');

    if (!adminChartCanvas) {
        console.log('Appointments chart canvas not found.');
        return;
    }

    if (typeof Chart === 'undefined') {
        console.log('Chart.js is not loaded.');
        return;
    }

    new Chart(adminChartCanvas, {

        type: 'line',

        data: {

            labels: [
                'Jan',
                'Feb',
                'Mar',
                'Apr',
                'May',
                'Jun',
                'Jul',
                'Aug',
                'Sep',
                'Oct',
                'Nov',
                'Dec'
            ],

            datasets: [

                {

                    label: 'Appointments',

                    data: [
                        55,
                        110,
                        70,
                        70,
                        120,
                        150,
                        95,
                        145,
                        100,
                        110,
                        130,
                        205
                    ],

                    fill: true,

                    tension: 0.4,

                    borderWidth: 3,

                    pointRadius: 4

                }

            ]

        },

        options: {

            responsive: true,

            maintainAspectRatio: false,

            plugins: {

                legend: {

                    display: false

                }

            },

            scales: {

                y: {

                    beginAtZero: true

                }

            }

        }

    });

});

</script>



<?php
require_once __DIR__ . '/includes/footer.php';
?>