<?php
/**
 * Professional Hospital Dashboard
 * COVID-19 Vaccination & Hospital Management System
 */

require_once __DIR__ . '/config/db.php';
requireAuth(['hospital']);

$pageTitle = "Hospital Dashboard";

/*
|--------------------------------------------------------------------------
| CURRENT HOSPITAL
|--------------------------------------------------------------------------
*/

$hospName = $currentUser['name'] ?? 'City Hospital';


/*
|--------------------------------------------------------------------------
| INITIAL VALUES
|--------------------------------------------------------------------------
*/

$todayAppointments = 0;
$pendingAppointments = 0;
$testsConducted = 0;
$vaccinations = 0;

$upcomingApts = [];
$vaccines = [];


/*
|--------------------------------------------------------------------------
| DATABASE
|--------------------------------------------------------------------------
*/

if ($conn && !$conn->connect_error) {

    $safeHospital = $conn->real_escape_string($hospName);


    /*
    |--------------------------------------------------------------------------
    | TODAY'S APPOINTMENTS
    |--------------------------------------------------------------------------
    */

    $res = $conn->query("
        SELECT COUNT(*) AS cnt
        FROM appointments
        WHERE hospital_name = '$safeHospital'
        AND date = CURDATE()
    ");

    if ($res) {
        $todayAppointments = (int)$res->fetch_assoc()['cnt'];
    }


    /*
    |--------------------------------------------------------------------------
    | PENDING APPOINTMENTS
    |--------------------------------------------------------------------------
    */

    $res = $conn->query("
        SELECT COUNT(*) AS cnt
        FROM appointments
        WHERE hospital_name = '$safeHospital'
        AND status = 'Pending'
    ");

    if ($res) {
        $pendingAppointments = (int)$res->fetch_assoc()['cnt'];
    }


    /*
    |--------------------------------------------------------------------------
    | COVID TESTS CONDUCTED
    |--------------------------------------------------------------------------
    */

    $res = $conn->query("
        SELECT COUNT(*) AS cnt
        FROM covid_tests
        WHERE hospital_name = '$safeHospital'
    ");

    if ($res) {
        $testsConducted = (int)$res->fetch_assoc()['cnt'];
    }


    /*
    |--------------------------------------------------------------------------
    | VACCINATIONS
    |--------------------------------------------------------------------------
    */

    $res = $conn->query("
        SELECT COUNT(*) AS cnt
        FROM vaccination_doses
        WHERE hospital_name = '$safeHospital'
    ");

    if ($res) {
        $vaccinations = (int)$res->fetch_assoc()['cnt'];
    }


    /*
    |--------------------------------------------------------------------------
    | UPCOMING APPOINTMENTS
    |--------------------------------------------------------------------------
    */

    $res = $conn->query("
        SELECT *
        FROM appointments
        WHERE hospital_name = '$safeHospital'
        ORDER BY date ASC, time ASC
        LIMIT 5
    ");

    if ($res) {

        while ($row = $res->fetch_assoc()) {

            $upcomingApts[] = $row;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | VACCINE STOCK
    |--------------------------------------------------------------------------
    */

    $res = $conn->query("
        SELECT *
        FROM vaccines
        ORDER BY name ASC
        LIMIT 6
    ");

    if ($res) {

        while ($row = $res->fetch_assoc()) {

            $vaccines[] = $row;
        }
    }
}


/*
|--------------------------------------------------------------------------
| HEADER
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/includes/header.php';
?>


<div class="app-container">


    <!-- =========================================================
         SIDEBAR
    ========================================================== -->

    <?php require_once __DIR__ . '/includes/sidebar.php'; ?>


    <!-- =========================================================
         MAIN CONTENT
    ========================================================== -->

    <div class="main-content">


        <!-- TOPBAR -->

        <?php require_once __DIR__ . '/includes/topbar.php'; ?>


        <div class="page-body">


            <!-- =================================================
                 PAGE HEADER
            ================================================== -->

            <div
                class="d-flex justify-content-between
                       align-items-center mb-4">


                <div>

                    <h2
                        class="fw-bold text-dark fs-3 mb-1">

                        Hospital Dashboard

                    </h2>


                    <p
                        class="text-muted small mb-0">

                        <?php
                        echo htmlspecialchars($hospName);
                        ?>

                        — manage appointments, testing,
                        vaccinations and vaccine inventory.

                    </p>

                </div>


                <div
                    class="d-flex gap-2">


                    <!-- NEW TEST -->

                    <a
                        href="hospital_tests.php"
                        class="btn btn-outline-secondary btn-sm">

                        <i
                            class="fa-solid fa-vial-virus
                                   me-1 text-success">
                        </i>

                        New Test Result

                    </a>


                    <!-- VACCINATION -->

                    <a
                        href="hospital_vaccinations.php"
                        class="btn btn-emerald btn-sm">

                        <i
                            class="fa-solid fa-syringe
                                   me-1">
                        </i>

                        Administer Vaccine

                    </a>


                </div>

            </div>



            <!-- =================================================
                 HOSPITAL INFO BAR
            ================================================== -->

            <div
                class="panel-card-custom mb-4">


                <div
                    class="p-3 d-flex
                           justify-content-between
                           align-items-center">


                    <div
                        class="d-flex
                               align-items-center
                               gap-3">


                        <div
                            class="stat-icon-box stat-icon-blue">

                            <i
                                class="fa-solid fa-hospital">
                            </i>

                        </div>


                        <div>

                            <div
                                class="text-muted small">

                                Logged in as Hospital

                            </div>


                            <div
                                class="fw-bold text-dark">

                                <?php
                                echo htmlspecialchars($hospName);
                                ?>

                            </div>

                        </div>

                    </div>


                    <span
                        class="badge-status
                               badge-status-approved">

                        <i
                            class="fa-solid fa-circle-check
                                   me-1">
                        </i>

                        Hospital Account Active

                    </span>


                </div>


            </div>



            <!-- =================================================
                 KPI CARDS
            ================================================== -->

            <div
                class="row g-3 mb-4">


                <!-- TODAY -->

                <div
                    class="col-sm-6 col-xl-3">

                    <div
                        class="stat-card-custom">


                        <div
                            class="stat-icon-box stat-icon-green">

                            <i
                                class="fa-solid fa-calendar-day">
                            </i>

                        </div>


                        <div>

                            <div
                                class="stat-label-text">

                                Today's Appointments

                            </div>


                            <div
                                class="stat-number-val">

                                <?php
                                echo number_format(
                                    $todayAppointments
                                );
                                ?>

                            </div>

                        </div>


                    </div>

                </div>



                <!-- PENDING -->

                <div
                    class="col-sm-6 col-xl-3">

                    <div
                        class="stat-card-custom">


                        <div
                            class="stat-icon-box stat-icon-amber">

                            <i
                                class="fa-solid fa-hourglass-half">
                            </i>

                        </div>


                        <div>

                            <div
                                class="stat-label-text">

                                Pending Approvals

                            </div>


                            <div
                                class="stat-number-val">

                                <?php
                                echo number_format(
                                    $pendingAppointments
                                );
                                ?>

                            </div>

                        </div>


                    </div>

                </div>



                <!-- TESTS -->

                <div
                    class="col-sm-6 col-xl-3">

                    <div
                        class="stat-card-custom">


                        <div
                            class="stat-icon-box stat-icon-blue">

                            <i
                                class="fa-solid fa-vial-virus">
                            </i>

                        </div>


                        <div>

                            <div
                                class="stat-label-text">

                                Tests Conducted

                            </div>


                            <div
                                class="stat-number-val">

                                <?php
                                echo number_format(
                                    $testsConducted
                                );
                                ?>

                            </div>

                        </div>


                    </div>

                </div>



                <!-- VACCINATIONS -->

                <div
                    class="col-sm-6 col-xl-3">

                    <div
                        class="stat-card-custom">


                        <div
                            class="stat-icon-box stat-icon-purple">

                            <i
                                class="fa-solid fa-syringe">
                            </i>

                        </div>


                        <div>

                            <div
                                class="stat-label-text">

                                Vaccinations

                            </div>


                            <div
                                class="stat-number-val">

                                <?php
                                echo number_format(
                                    $vaccinations
                                );
                                ?>

                            </div>

                        </div>


                    </div>

                </div>


            </div>



            <!-- =================================================
                 APPOINTMENTS + STOCK
            ================================================== -->

            <div
                class="row g-4">


                <!-- =================================================
                     UPCOMING APPOINTMENTS
                ================================================== -->

                <div
                    class="col-lg-8">


                    <div
                        class="panel-card-custom h-100 mb-0">


                        <div
                            class="panel-header-custom">


                            <h3
                                class="panel-title-custom">

                                <i
                                    class="fa-solid
                                           fa-calendar-days
                                           text-success">
                                </i>

                                Upcoming Appointments

                            </h3>


                            <a
                                href="hospital_appointments.php"
                                class="small fw-bold
                                       text-success
                                       text-decoration-none">

                                View All

                                <i
                                    class="fa-solid
                                           fa-chevron-right
                                           ms-1">
                                </i>

                            </a>


                        </div>



                        <div
                            class="table-responsive">


                            <table
                                class="table table-custom
                                       align-middle mb-0">


                                <thead>

                                    <tr>

                                        <th>
                                            Patient
                                        </th>

                                        <th>
                                            Service
                                        </th>

                                        <th>
                                            Date
                                        </th>

                                        <th>
                                            Time
                                        </th>

                                        <th>
                                            Status
                                        </th>

                                        <th
                                            class="text-end">

                                            Action

                                        </th>

                                    </tr>

                                </thead>


                                <tbody>


                                <?php
                                if (!empty($upcomingApts)):
                                ?>


                                    <?php
                                    foreach (
                                        $upcomingApts
                                        as $apt
                                    ):
                                    ?>


                                        <tr>


                                            <!-- PATIENT -->

                                            <td>

                                                <div
                                                    class="d-flex
                                                           align-items-center
                                                           gap-2">


                                                    <div
                                                        class="rounded-circle
                                                               bg-success
                                                               bg-opacity-10
                                                               text-success
                                                               fw-bold
                                                               d-flex
                                                               align-items-center
                                                               justify-content-center"
                                                        style="
                                                        width:34px;
                                                        height:34px;
                                                        ">

                                                        <?php

                                                        $patientName =
                                                            $apt[
                                                                'patient_name'
                                                            ] ?? 'P';

                                                        echo htmlspecialchars(
                                                            strtoupper(
                                                                substr(
                                                                    $patientName,
                                                                    0,
                                                                    1
                                                                )
                                                            )
                                                        );

                                                        ?>

                                                    </div>


                                                    <span
                                                        class="fw-bold
                                                               text-dark">

                                                        <?php
                                                        echo htmlspecialchars(
                                                            $patientName
                                                        );
                                                        ?>

                                                    </span>


                                                </div>

                                            </td>



                                            <!-- SERVICE -->

                                            <td
                                                class="fw-semibold
                                                       text-success">

                                                <?php
                                                echo htmlspecialchars(
                                                    $apt['service']
                                                );
                                                ?>

                                            </td>



                                            <!-- DATE -->

                                            <td
                                                class="text-muted">

                                                <?php
                                                echo htmlspecialchars(
                                                    $apt['date']
                                                );
                                                ?>

                                            </td>



                                            <!-- TIME -->

                                            <td
                                                class="text-muted
                                                       font-monospace">

                                                <?php
                                                echo htmlspecialchars(
                                                    $apt['time']
                                                );
                                                ?>

                                            </td>



                                            <!-- STATUS -->

                                            <td>


                                                <?php

                                                $st =
                                                    strtolower(
                                                        trim(
                                                            $apt[
                                                                'status'
                                                            ]
                                                        )
                                                    );


                                                if (
                                                    $st ===
                                                    'approved'
                                                ) {

                                                    $badgeClass =
                                                        'badge-status-approved';

                                                } elseif (
                                                    $st ===
                                                    'rejected'
                                                ) {

                                                    $badgeClass =
                                                        'badge-status-rejected';

                                                } else {

                                                    $badgeClass =
                                                        'badge-status-pending';
                                                }

                                                ?>


                                                <span
                                                    class="badge-status
                                                           <?php
                                                           echo $badgeClass;
                                                           ?>">

                                                    <?php
                                                    echo htmlspecialchars(
                                                        $apt[
                                                            'status'
                                                        ]
                                                    );
                                                    ?>

                                                </span>


                                            </td>



                                            <!-- ACTION -->

                                            <td
                                                class="text-end">


                                                <?php
                                                if (
                                                    strtolower(
                                                        trim(
                                                            $apt[
                                                                'status'
                                                            ]
                                                        )
                                                    ) === 'pending'
                                                ):
                                                ?>


                                                    <form
                                                        method="POST"
                                                        action="hospital_appointments.php"
                                                        class="d-inline">


                                                        <input
                                                            type="hidden"
                                                            name="action"
                                                            value="approve">


                                                        <input
                                                            type="hidden"
                                                            name="id"
                                                            value="<?php
                                                            echo htmlspecialchars(
                                                                $apt['id']
                                                            );
                                                            ?>">


                                                        <button
                                                            type="submit"
                                                            class="btn-icon-custom
                                                                   btn-icon-green"
                                                            title="Approve">

                                                            <i
                                                                class="fa-solid
                                                                       fa-check">
                                                            </i>

                                                        </button>


                                                    </form>


                                                <?php endif; ?>


                                                <a
                                                    href="view_appointment_slip.php?id=<?php
                                                    echo urlencode(
                                                        $apt['id']
                                                    );
                                                    ?>"
                                                    class="btn-icon-custom
                                                           btn-icon-blue"
                                                    title="View Appointment"
                                                    target="_blank">

                                                    <i
                                                        class="fa-solid
                                                               fa-eye">
                                                    </i>

                                                </a>


                                            </td>


                                        </tr>


                                    <?php endforeach; ?>


                                <?php else: ?>


                                    <tr>

                                        <td
                                            colspan="6"
                                            class="text-center
                                                   py-5
                                                   text-muted">


                                            <i
                                                class="fa-solid
                                                       fa-calendar-xmark
                                                       fa-2x mb-3">
                                            </i>


                                            <div
                                                class="fw-semibold">

                                                No upcoming appointments

                                            </div>


                                            <small>

                                                New patient appointments
                                                will appear here.

                                            </small>


                                        </td>

                                    </tr>


                                <?php endif; ?>


                                </tbody>


                            </table>


                        </div>


                    </div>


                </div>



                <!-- =================================================
                     VACCINE STOCK
                ================================================== -->

                <div
                    class="col-lg-4">


                    <div
                        class="panel-card-custom
                               h-100 mb-0">


                        <div
                            class="panel-header-custom">


                            <h3
                                class="panel-title-custom
                                       fs-6">

                                <i
                                    class="fa-solid
                                           fa-boxes-stacked
                                           text-primary">
                                </i>

                                Vaccine Stock

                            </h3>


                            <a
                                href="hospital_stock.php"
                                class="small fw-bold
                                       text-primary
                                       text-decoration-none">

                                View Stock

                                <i
                                    class="fa-solid
                                           fa-chevron-right
                                           ms-1">
                                </i>

                            </a>


                        </div>


                        <div
                            class="p-3">


                            <?php
                            if (!empty($vaccines)):
                            ?>


                                <?php
                                foreach (
                                    $vaccines
                                    as $v
                                ):
                                ?>


                                    <div
                                        class="d-flex
                                               justify-content-between
                                               align-items-center
                                               py-3
                                               border-bottom">


                                        <div>

                                            <div
                                                class="small
                                                       fw-bold
                                                       text-dark">

                                                <?php
                                                echo htmlspecialchars(
                                                    $v['name']
                                                );
                                                ?>

                                            </div>


                                            <?php
                                            if (
                                                isset(
                                                    $v[
                                                        'available_doses'
                                                    ]
                                                )
                                            ):
                                            ?>

                                                <small
                                                    class="text-muted">

                                                    Available doses

                                                </small>

                                            <?php endif; ?>


                                        </div>


                                        <span
                                            class="badge
                                                   bg-success
                                                   bg-opacity-10
                                                   text-success">

                                            <?php
                                            echo number_format(
                                                $v[
                                                    'available_doses'
                                                ]
                                            );
                                            ?>

                                            Doses

                                        </span>


                                    </div>


                                <?php endforeach; ?>


                            <?php else: ?>


                                <div
                                    class="text-muted
                                           small
                                           text-center
                                           py-5">


                                    <i
                                        class="fa-solid
                                               fa-box-open
                                               fa-2x mb-2">
                                    </i>


                                    <div>

                                        No vaccine stock available.

                                    </div>


                                </div>


                            <?php endif; ?>


                        </div>


                    </div>


                </div>


            </div>


        </div>


    </div>

</div>


<?php
require_once __DIR__ . '/includes/footer.php';
?>