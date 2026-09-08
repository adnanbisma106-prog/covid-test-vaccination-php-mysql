<?php
/**
 * Hospital Patients Directory
 */

require_once __DIR__ . '/config/db.php';
requireAuth(['hospital']);

$currentUser = getLoggedInUser();

$pageTitle = "Hospital Patients";

$patients = [];


// =====================================================
// Logged-in Hospital Information
// =====================================================

$hospitalUserId = $currentUser['id'] ?? '';
$hospitalName   = $currentUser['name'] ?? '';
$hospitalId     = '';


// Find hospital by logged-in user ID
if ($hospitalUserId !== '') {

    $stmt = $conn->prepare("
        SELECT id, name
        FROM hospitals
        WHERE user_id = ?
        LIMIT 1
    ");

    if ($stmt) {

        $stmt->bind_param("s", $hospitalUserId);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {

            $hospitalId   = $row['id'];
            $hospitalName = $row['name'];
        }

        $stmt->close();
    }
}


// =====================================================
// If hospital ID was not found, try hospital name
// =====================================================

if ($hospitalId === '' && $hospitalName !== '') {

    $stmt = $conn->prepare("
        SELECT id, name
        FROM hospitals
        WHERE name = ?
        LIMIT 1
    ");

    if ($stmt) {

        $stmt->bind_param("s", $hospitalName);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {

            $hospitalId   = $row['id'];
            $hospitalName = $row['name'];
        }

        $stmt->close();
    }
}


// =====================================================
// Status Filter
// =====================================================

$statusFilter = $_GET['status'] ?? 'Approved';

$allowedStatuses = [
    'Approved',
    'Rejected',
    'All'
];

if (!in_array($statusFilter, $allowedStatuses, true)) {

    $statusFilter = 'Approved';
}


// =====================================================
// Fetch Patients
// =====================================================

if ($hospitalId !== '' || $hospitalName !== '') {


    // -------------------------------------------------
    // ALL
    // -------------------------------------------------

    if ($statusFilter === 'All') {

        $stmt = $conn->prepare("
            SELECT DISTINCT
                u.id,
                u.name,
                u.cnic,
                u.phone,
                u.city,
                a.service,
                a.date AS appointment_date,
                a.time AS appointment_time,
                a.status AS appointment_status
            FROM appointments a
            INNER JOIN users u
                ON u.id = a.patient_id
            WHERE
                (
                    a.hospital_id = ?
                    OR a.hospital_name = ?
                )
                AND u.role = 'patient'
                AND a.status IN ('Approved', 'Rejected')
            ORDER BY
                a.date DESC,
                a.time ASC,
                u.name ASC
        ");

        if ($stmt) {

            $stmt->bind_param(
                "ss",
                $hospitalId,
                $hospitalName
            );

        }


    // -------------------------------------------------
    // APPROVED / REJECTED
    // -------------------------------------------------

    } else {

        $stmt = $conn->prepare("
            SELECT DISTINCT
                u.id,
                u.name,
                u.cnic,
                u.phone,
                u.city,
                a.service,
                a.date AS appointment_date,
                a.time AS appointment_time,
                a.status AS appointment_status
            FROM appointments a
            INNER JOIN users u
                ON u.id = a.patient_id
            WHERE
                (
                    a.hospital_id = ?
                    OR a.hospital_name = ?
                )
                AND u.role = 'patient'
                AND a.status = ?
            ORDER BY
                a.date DESC,
                a.time ASC,
                u.name ASC
        ");

        if ($stmt) {

            $stmt->bind_param(
                "sss",
                $hospitalId,
                $hospitalName,
                $statusFilter
            );

        }
    }


    // -------------------------------------------------
    // Execute
    // -------------------------------------------------

    if ($stmt) {

        $stmt->execute();

        $res = $stmt->get_result();

        while ($row = $res->fetch_assoc()) {

            $patients[] = $row;
        }

        $stmt->close();
    }
}


// =====================================================
// Statistics
// =====================================================

$approvedCount = 0;
$rejectedCount = 0;

foreach ($patients as $patient) {

    $status = strtolower(
        trim(
            $patient['appointment_status'] ?? ''
        )
    );

    if ($status === 'approved') {

        $approvedCount++;
    }

    if ($status === 'rejected') {

        $rejectedCount++;
    }
}


// =====================================================
// Header
// =====================================================

require_once __DIR__ . '/includes/header.php';

?>


<div class="app-container">


    <!-- Sidebar -->

    <?php require_once __DIR__ . '/includes/sidebar.php'; ?>


    <div class="main-content">


        <!-- Topbar -->

        <?php require_once __DIR__ . '/includes/topbar.php'; ?>


        <div class="page-body">


            <!-- =================================================
                 PAGE HEADER
            ================================================== -->

            <div class="d-flex justify-content-between align-items-center mb-4">

                <div>

                    <h2 class="fw-bold mb-1">

                        <i class="fa-solid fa-hospital-user text-primary me-2"></i>

                        Hospital Patients

                    </h2>


                    <p class="text-muted mb-0">

                        View approved and rejected patient appointments.

                    </p>

                </div>


                <!-- Filter -->

                <form method="GET">

                    <select
                        name="status"
                        class="form-select"
                        onchange="this.form.submit()"
                    >

                        <option
                            value="Approved"
                            <?php
                            echo ($statusFilter === 'Approved')
                                ? 'selected'
                                : '';
                            ?>
                        >
                            Approved Patients
                        </option>


                        <option
                            value="Rejected"
                            <?php
                            echo ($statusFilter === 'Rejected')
                                ? 'selected'
                                : '';
                            ?>
                        >
                            Rejected Patients
                        </option>


                        <option
                            value="All"
                            <?php
                            echo ($statusFilter === 'All')
                                ? 'selected'
                                : '';
                            ?>
                        >
                            All Patients
                        </option>

                    </select>

                </form>

            </div>



            <!-- =================================================
                 STATISTICS
            ================================================== -->

            <div class="row g-3 mb-4">


                <!-- Approved -->

                <div class="col-md-6">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-body">

                            <div class="d-flex justify-content-between align-items-center">

                                <div>

                                    <small class="text-muted">
                                        Approved Patients
                                    </small>

                                    <h3 class="fw-bold text-success mb-0">

                                        <?php
                                        echo $approvedCount;
                                        ?>

                                    </h3>

                                </div>


                                <div class="text-success fs-2">

                                    <i class="fa-solid fa-user-check"></i>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>



                <!-- Rejected -->

                <div class="col-md-6">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-body">

                            <div class="d-flex justify-content-between align-items-center">

                                <div>

                                    <small class="text-muted">
                                        Rejected Patients
                                    </small>

                                    <h3 class="fw-bold text-danger mb-0">

                                        <?php
                                        echo $rejectedCount;
                                        ?>

                                    </h3>

                                </div>


                                <div class="text-danger fs-2">

                                    <i class="fa-solid fa-user-xmark"></i>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>



            <!-- =================================================
                 PATIENT LIST
            ================================================== -->

            <div class="panel-card-custom">


                <div class="panel-header-custom">

                    <div>

                        <h3 class="panel-title-custom mb-1">

                            Patient List

                        </h3>


                        <small class="text-muted">

                            <?php

                            if ($statusFilter === 'Approved') {

                                echo 'Patients with approved appointments';

                            } elseif ($statusFilter === 'Rejected') {

                                echo 'Patients with rejected appointments';

                            } else {

                                echo 'Approved and rejected patients';

                            }

                            ?>

                        </small>

                    </div>

                </div>



                <div class="table-responsive">


                    <table class="table table-custom align-middle">


                        <thead>

                            <tr>

                                <th style="width:50px;">
                                    #
                                </th>

                                <th>
                                    Patient Name
                                </th>

                                <th>
                                    CNIC / ID
                                </th>

                                <th>
                                    Phone
                                </th>

                                <th>
                                    City
                                </th>

                                <th>
                                    Service
                                </th>

                                <th>
                                    Appointment
                                </th>

                                <th>
                                    Status
                                </th>

                                <th
                                    class="text-end"
                                    style="width:220px;"
                                >
                                    Quick Actions
                                </th>

                            </tr>

                        </thead>



                        <tbody>


                        <?php if (!empty($patients)): ?>


                            <?php foreach ($patients as $idx => $p): ?>


                                <?php

                                $appointmentStatus =
                                    trim(
                                        $p['appointment_status']
                                        ?? 'Pending'
                                    );


                                $statusLower =
                                    strtolower(
                                        $appointmentStatus
                                    );


                                if ($statusLower === 'approved') {

                                    $statusBadge =
                                        'bg-success';

                                } elseif ($statusLower === 'rejected') {

                                    $statusBadge =
                                        'bg-danger';

                                } else {

                                    $statusBadge =
                                        'bg-secondary';
                                }

                                ?>


                                <tr>


                                    <!-- Number -->

                                    <td class="text-muted fw-bold">

                                        <?php
                                        echo $idx + 1;
                                        ?>

                                    </td>



                                    <!-- Patient Name -->

                                    <td class="fw-bold text-dark">

                                        <?php

                                        echo htmlspecialchars(
                                            $p['name']
                                            ?? 'Patient'
                                        );

                                        ?>

                                    </td>



                                    <!-- CNIC -->

                                    <td class="text-muted font-monospace">

                                        <?php

                                        echo htmlspecialchars(
                                            $p['cnic']
                                            ?? 'Not Available'
                                        );

                                        ?>

                                    </td>



                                    <!-- Phone -->

                                    <td class="text-muted">

                                        <?php

                                        echo htmlspecialchars(
                                            $p['phone']
                                            ?? 'Not Available'
                                        );

                                        ?>

                                    </td>



                                    <!-- City -->

                                    <td class="text-muted">

                                        <?php

                                        echo htmlspecialchars(
                                            $p['city']
                                            ?? 'Not Available'
                                        );

                                        ?>

                                    </td>



                                    <!-- Service -->

                                    <td>

                                        <span class="badge bg-primary">

                                            <?php

                                            echo htmlspecialchars(
                                                $p['service']
                                                ?? 'N/A'
                                            );

                                            ?>

                                        </span>

                                    </td>



                                    <!-- Appointment -->

                                    <td class="text-muted">

                                        <?php

                                        echo htmlspecialchars(
                                            $p['appointment_date']
                                            ?? '-'
                                        );

                                        ?>

                                        <br>

                                        <small>

                                            <?php

                                            echo htmlspecialchars(
                                                $p['appointment_time']
                                                ?? '-'
                                            );

                                            ?>

                                        </small>

                                    </td>



                                    <!-- Status -->

                                    <td>

                                        <span
                                            class="badge <?php echo $statusBadge; ?> px-3 py-2"
                                        >

                                            <?php

                                            echo htmlspecialchars(
                                                $appointmentStatus
                                            );

                                            ?>

                                        </span>

                                    </td>



                                    <!-- Actions -->

                                    <td class="text-end">


                                        <?php if ($statusLower === 'approved'): ?>


                                            <a
                                                href="hospital_tests.php?pat_id=<?php echo urlencode($p['id']); ?>"
                                                class="btn btn-sm btn-outline-success py-1 px-2 mb-1"
                                            >

                                                <i class="fa-solid fa-vial me-1"></i>

                                                Add Test

                                            </a>


                                            <a
                                                href="hospital_vaccinations.php?pat_id=<?php echo urlencode($p['id']); ?>"
                                                class="btn btn-sm btn-outline-primary py-1 px-2 mb-1"
                                            >

                                                <i class="fa-solid fa-syringe me-1"></i>

                                                Vaccinate

                                            </a>


                                        <?php else: ?>


                                            <span class="text-muted small">

                                                No actions

                                            </span>


                                        <?php endif; ?>


                                    </td>


                                </tr>


                            <?php endforeach; ?>


                        <?php else: ?>


                            <tr>

                                <td
                                    colspan="9"
                                    class="text-center py-5 text-muted"
                                >

                                    <i
                                        class="fa-solid fa-user-slash fa-2x mb-2"
                                    ></i>

                                    <br>


                                    <?php

                                    if ($statusFilter === 'Approved') {

                                        echo 'No approved patients found for this hospital.';

                                    } elseif ($statusFilter === 'Rejected') {

                                        echo 'No rejected patients found for this hospital.';

                                    } else {

                                        echo 'No approved or rejected patients found for this hospital.';

                                    }

                                    ?>

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


<?php require_once __DIR__ . '/includes/footer.php'; ?>