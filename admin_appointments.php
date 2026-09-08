<?php
/**
 * Professional Appointments Management
 * COVID-19 Vaccination & Hospital Management System
 */

require_once __DIR__ . '/config/db.php';
requireAuth(['admin']);

$pageTitle = "Appointments Management";

/*
|--------------------------------------------------------------------------
| HANDLE POST ACTIONS
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? '';
    $id     = $_POST['id'] ?? '';

    /*
    |--------------------------------------------------------------------------
    | APPROVE APPOINTMENT
    |--------------------------------------------------------------------------
    */

    if ($action === 'approve' && $id !== '') {

        $stmt = $conn->prepare(
            "UPDATE appointments
             SET status = 'Approved'
             WHERE id = ?"
        );

        if ($stmt) {

            $stmt->bind_param("s", $id);

            if ($stmt->execute()) {

                logAudit(
                    $conn,
                    "Admin",
                    "Approved appointment #$id"
                );

                setFlash(
                    "Appointment approved successfully!",
                    "success"
                );

            } else {

                setFlash(
                    "Unable to approve appointment.",
                    "danger"
                );
            }

            $stmt->close();
        }

        header("Location: admin_appointments.php");
        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | REJECT APPOINTMENT
    |--------------------------------------------------------------------------
    */

    if ($action === 'reject' && $id !== '') {

        $stmt = $conn->prepare(
            "UPDATE appointments
             SET status = 'Rejected'
             WHERE id = ?"
        );

        if ($stmt) {

            $stmt->bind_param("s", $id);

            if ($stmt->execute()) {

                logAudit(
                    $conn,
                    "Admin",
                    "Rejected appointment #$id"
                );

                setFlash(
                    "Appointment rejected.",
                    "info"
                );

            } else {

                setFlash(
                    "Unable to reject appointment.",
                    "danger"
                );
            }

            $stmt->close();
        }

        header("Location: admin_appointments.php");
        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | DELETE APPOINTMENT
    |--------------------------------------------------------------------------
    */

    if ($action === 'delete' && $id !== '') {

        $stmt = $conn->prepare(
            "DELETE FROM appointments
             WHERE id = ?"
        );

        if ($stmt) {

            $stmt->bind_param("s", $id);

            if ($stmt->execute()) {

                logAudit(
                    $conn,
                    "Admin",
                    "Deleted appointment #$id"
                );

                setFlash(
                    "Appointment removed successfully.",
                    "info"
                );

            } else {

                setFlash(
                    "Unable to delete appointment.",
                    "danger"
                );
            }

            $stmt->close();
        }

        header("Location: admin_appointments.php");
        exit;
    }
}


/*
|--------------------------------------------------------------------------
| SEARCH & FILTERS
|--------------------------------------------------------------------------
*/

$search = trim($_GET['search'] ?? '');

$statusFilter   = $_GET['status'] ?? 'All';
$hospitalFilter = $_GET['hospital'] ?? 'All';


/*
|--------------------------------------------------------------------------
| FETCH HOSPITALS FOR FILTER
|--------------------------------------------------------------------------
*/

$hospitalList = [];

if ($conn && !$conn->connect_error) {

    $hospitalResult = $conn->query(
        "SELECT DISTINCT hospital_name
         FROM appointments
         WHERE hospital_name IS NOT NULL
         AND hospital_name != ''
         ORDER BY hospital_name ASC"
    );

    if ($hospitalResult) {

        while ($hospital = $hospitalResult->fetch_assoc()) {

            $hospitalList[] = $hospital['hospital_name'];
        }
    }
}


/*
|--------------------------------------------------------------------------
| FETCH APPOINTMENTS
|--------------------------------------------------------------------------
*/

$appointments = [];

if ($conn && !$conn->connect_error) {

    $conditions = [];

    /*
    |--------------------------------------------------------------------------
    | SEARCH
    |--------------------------------------------------------------------------
    */

    if ($search !== '') {

        $safeSearch = $conn->real_escape_string($search);

        $conditions[] = "
            (
                patient_name LIKE '%$safeSearch%'
                OR hospital_name LIKE '%$safeSearch%'
                OR service LIKE '%$safeSearch%'
            )
        ";
    }


    /*
    |--------------------------------------------------------------------------
    | STATUS FILTER
    |--------------------------------------------------------------------------
    */

    if ($statusFilter !== 'All') {

        $safeStatus = $conn->real_escape_string($statusFilter);

        $conditions[] =
            "status = '$safeStatus'";
    }


    /*
    |--------------------------------------------------------------------------
    | HOSPITAL FILTER
    |--------------------------------------------------------------------------
    */

    if ($hospitalFilter !== 'All') {

        $safeHospital =
            $conn->real_escape_string($hospitalFilter);

        $conditions[] =
            "hospital_name = '$safeHospital'";
    }


    /*
    |--------------------------------------------------------------------------
    | BUILD QUERY
    |--------------------------------------------------------------------------
    */

    $sql = "SELECT * FROM appointments";

    if (!empty($conditions)) {

        $sql .= " WHERE " . implode(
            " AND ",
            $conditions
        );
    }

    $sql .= "
        ORDER BY date DESC, time ASC
    ";


    $res = $conn->query($sql);

    if ($res) {

        while ($row = $res->fetch_assoc()) {

            $appointments[] = $row;
        }
    }
}


/*
|--------------------------------------------------------------------------
| REAL STATISTICS
|--------------------------------------------------------------------------
*/

$totalAppointments  = 0;
$pendingAppointments = 0;
$approvedAppointments = 0;
$rejectedAppointments = 0;


if ($conn && !$conn->connect_error) {

    /*
    | Total
    */

    $result = $conn->query(
        "SELECT COUNT(*) AS cnt
         FROM appointments"
    );

    if ($result) {

        $totalAppointments =
            (int)$result->fetch_assoc()['cnt'];
    }


    /*
    | Pending
    */

    $result = $conn->query(
        "SELECT COUNT(*) AS cnt
         FROM appointments
         WHERE status = 'Pending'"
    );

    if ($result) {

        $pendingAppointments =
            (int)$result->fetch_assoc()['cnt'];
    }


    /*
    | Approved
    */

    $result = $conn->query(
        "SELECT COUNT(*) AS cnt
         FROM appointments
         WHERE status = 'Approved'"
    );

    if ($result) {

        $approvedAppointments =
            (int)$result->fetch_assoc()['cnt'];
    }


    /*
    | Rejected
    */

    $result = $conn->query(
        "SELECT COUNT(*) AS cnt
         FROM appointments
         WHERE status = 'Rejected'"
    );

    if ($result) {

        $rejectedAppointments =
            (int)$result->fetch_assoc()['cnt'];
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


    <!-- =============================================================
         SIDEBAR
    ============================================================= -->

    <?php require_once __DIR__ . '/includes/sidebar.php'; ?>


    <!-- =============================================================
         MAIN CONTENT
    ============================================================= -->

    <div class="main-content">


        <!-- TOPBAR -->

        <?php require_once __DIR__ . '/includes/topbar.php'; ?>


        <div class="page-body">


            <!-- =====================================================
                 PAGE HEADER
            ====================================================== -->

            <div
                class="d-flex justify-content-between
                       align-items-center mb-4">


                <div>

                    <h2
                        class="fw-bold text-dark fs-3 mb-1">

                        Appointments Management

                    </h2>


                    <p
                        class="text-muted small mb-0">

                        Manage COVID testing and vaccination appointments

                    </p>

                </div>


                <a
                    href="export_csv.php?type=appointments"
                    class="btn btn-outline-secondary btn-sm">

                    <i
                        class="fa-solid fa-file-export me-1">
                    </i>

                    Export CSV

                </a>

            </div>



            <!-- =====================================================
                 STAT CARDS
            ====================================================== -->

            <div
                class="row g-3 mb-4">


                <!-- TOTAL -->

                <div
                    class="col-sm-6 col-xl-3">

                    <div
                        class="stat-card-custom">


                        <div
                            class="stat-icon-box stat-icon-purple">

                            <i
                                class="fa-solid fa-calendar-check">
                            </i>

                        </div>


                        <div>

                            <div
                                class="stat-label-text">

                                Total Appointments

                            </div>


                            <div
                                class="stat-number-val">

                                <?php
                                echo number_format(
                                    $totalAppointments
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
                                class="fa-solid fa-clock">
                            </i>

                        </div>


                        <div>

                            <div
                                class="stat-label-text">

                                Pending

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



                <!-- APPROVED -->

                <div
                    class="col-sm-6 col-xl-3">

                    <div
                        class="stat-card-custom">


                        <div
                            class="stat-icon-box stat-icon-green">

                            <i
                                class="fa-solid fa-circle-check">
                            </i>

                        </div>


                        <div>

                            <div
                                class="stat-label-text">

                                Approved

                            </div>


                            <div
                                class="stat-number-val">

                                <?php
                                echo number_format(
                                    $approvedAppointments
                                );
                                ?>

                            </div>

                        </div>


                    </div>

                </div>



                <!-- REJECTED -->

                <div
                    class="col-sm-6 col-xl-3">

                    <div
                        class="stat-card-custom">


                        <div
                            class="stat-icon-box stat-icon-red">

                            <i
                                class="fa-solid fa-circle-xmark">
                            </i>

                        </div>


                        <div>

                            <div
                                class="stat-label-text">

                                Rejected

                            </div>


                            <div
                                class="stat-number-val">

                                <?php
                                echo number_format(
                                    $rejectedAppointments
                                );
                                ?>

                            </div>

                        </div>


                    </div>

                </div>


            </div>



            <!-- =====================================================
                 MAIN PANEL
            ====================================================== -->

            <div
                class="panel-card-custom">


                <!-- PANEL HEADER -->

                <div
                    class="panel-header-custom">


                    <h3
                        class="panel-title-custom">

                        <i
                            class="fa-solid fa-calendar-days text-success">
                        </i>

                        Appointments Directory

                    </h3>


                    <span
                        class="badge bg-light text-dark border">

                        <?php
                        echo count($appointments);
                        ?>

                        Result(s)

                    </span>


                </div>



                <!-- =================================================
                     SEARCH + FILTERS
                ================================================== -->

                <form
                    method="GET"
                    class="p-3 bg-light border-bottom">


                    <div
                        class="row g-2 align-items-center">


                        <!-- SEARCH -->

                        <div
                            class="col-lg-4">

                            <div
                                class="input-group">

                                <span
                                    class="input-group-text bg-white">

                                    <i
                                        class="fa-solid fa-magnifying-glass text-muted">
                                    </i>

                                </span>


                                <input
                                    type="text"
                                    name="search"
                                    class="form-control"
                                    placeholder="Search patient, hospital or service..."
                                    value="<?php
                                    echo htmlspecialchars($search);
                                    ?>">

                            </div>

                        </div>



                        <!-- STATUS -->

                        <div
                            class="col-lg-2">

                            <select
                                name="status"
                                class="form-select">


                                <option value="All">

                                    All Status

                                </option>


                                <option
                                    value="Pending"
                                    <?php
                                    echo $statusFilter === 'Pending'
                                        ? 'selected'
                                        : '';
                                    ?>>

                                    Pending

                                </option>


                                <option
                                    value="Approved"
                                    <?php
                                    echo $statusFilter === 'Approved'
                                        ? 'selected'
                                        : '';
                                    ?>>

                                    Approved

                                </option>


                                <option
                                    value="Rejected"
                                    <?php
                                    echo $statusFilter === 'Rejected'
                                        ? 'selected'
                                        : '';
                                    ?>>

                                    Rejected

                                </option>


                            </select>

                        </div>



                        <!-- HOSPITAL -->

                        <div
                            class="col-lg-3">

                            <select
                                name="hospital"
                                class="form-select">


                                <option value="All">

                                    All Hospitals

                                </option>


                                <?php
                                foreach ($hospitalList as $hospital):
                                ?>

                                    <option
                                        value="<?php
                                        echo htmlspecialchars(
                                            $hospital
                                        );
                                        ?>"
                                        <?php
                                        echo $hospitalFilter === $hospital
                                            ? 'selected'
                                            : '';
                                        ?>>

                                        <?php
                                        echo htmlspecialchars(
                                            $hospital
                                        );
                                        ?>

                                    </option>

                                <?php endforeach; ?>


                            </select>

                        </div>



                        <!-- FILTER -->

                        <div
                            class="col-lg-1">

                            <button
                                type="submit"
                                class="btn btn-success w-100"
                                title="Apply Filter">

                                <i
                                    class="fa-solid fa-filter">
                                </i>

                            </button>

                        </div>



                        <!-- RESET -->

                        <div
                            class="col-lg-2">

                            <a
                                href="admin_appointments.php"
                                class="btn btn-outline-secondary w-100">

                                <i
                                    class="fa-solid fa-rotate-left me-1">
                                </i>

                                Reset

                            </a>

                        </div>


                    </div>


                </form>



                <!-- =================================================
                     TABLE
                ================================================== -->

                <div
                    class="table-responsive">


                    <table
                        class="table table-custom align-middle mb-0">


                        <thead>

                            <tr>

                                <th style="width:50px;">
                                    #
                                </th>

                                <th>
                                    Patient
                                </th>

                                <th>
                                    Hospital
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
                                    class="text-end"
                                    style="width:190px;">

                                    Action

                                </th>

                            </tr>

                        </thead>


                        <tbody>


                        <?php if (!empty($appointments)): ?>


                            <?php
                            foreach (
                                $appointments
                                as $idx => $a
                            ):
                            ?>


                                <tr>


                                    <!-- NUMBER -->

                                    <td
                                        class="text-muted fw-bold">

                                        <?php
                                        echo $idx + 1;
                                        ?>

                                    </td>



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
                                                width:36px;
                                                height:36px;
                                                ">

                                                <?php

                                                $patientName =
                                                    $a['patient_name']
                                                    ?? 'P';

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


                                            <div>

                                                <div
                                                    class="fw-bold text-dark">

                                                    <?php
                                                    echo htmlspecialchars(
                                                        $patientName
                                                    );
                                                    ?>

                                                </div>


                                                <small
                                                    class="text-muted">

                                                    Appointment #<?php
                                                    echo htmlspecialchars(
                                                        $a['id']
                                                    );
                                                    ?>

                                                </small>

                                            </div>

                                        </div>

                                    </td>



                                    <!-- HOSPITAL -->

                                    <td
                                        class="text-muted">

                                        <i
                                            class="fa-solid fa-hospital
                                                   me-1 text-primary">
                                        </i>

                                        <?php
                                        echo htmlspecialchars(
                                            $a['hospital_name']
                                        );
                                        ?>

                                    </td>



                                    <!-- SERVICE -->

                                    <td
                                        class="fw-semibold text-success">

                                        <?php
                                        echo htmlspecialchars(
                                            $a['service']
                                        );
                                        ?>

                                    </td>



                                    <!-- DATE -->

                                    <td
                                        class="text-dark">

                                        <?php
                                        echo htmlspecialchars(
                                            $a['date']
                                        );
                                        ?>

                                    </td>



                                    <!-- TIME -->

                                    <td
                                        class="text-muted font-monospace">

                                        <?php
                                        echo htmlspecialchars(
                                            $a['time']
                                        );
                                        ?>

                                    </td>



                                    <!-- STATUS -->

                                    <td>


                                        <?php

                                        $st =
                                            strtolower(
                                                trim(
                                                    $a['status']
                                                )
                                            );


                                        if ($st === 'approved') {

                                            $badgeClass =
                                                'badge-status-approved';

                                        } elseif (
                                            $st === 'rejected'
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
                                                $a['status']
                                            );
                                            ?>

                                        </span>


                                    </td>



                                    <!-- ACTIONS -->

                                    <td
                                        class="text-end">


                                        <!-- VIEW -->

                                        <a
                                            href="view_appointment_slip.php?id=<?php
                                            echo urlencode(
                                                $a['id']
                                            );
                                            ?>"
                                            class="btn-icon-custom btn-icon-blue"
                                            title="View Appointment"
                                            target="_blank">

                                            <i
                                                class="fa-solid fa-eye">
                                            </i>

                                        </a>



                                        <?php
                                        if (
                                            strtolower(
                                                trim(
                                                    $a['status']
                                                )
                                            ) === 'pending'
                                        ):
                                        ?>


                                            <!-- APPROVE -->

                                            <form
                                                method="POST"
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
                                                        $a['id']
                                                    );
                                                    ?>">

                                                <button
                                                    type="submit"
                                                    class="btn-icon-custom btn-icon-green"
                                                    title="Approve">

                                                    <i
                                                        class="fa-solid fa-check">
                                                    </i>

                                                </button>

                                            </form>



                                            <!-- REJECT -->

                                            <form
                                                method="POST"
                                                class="d-inline"
                                                onsubmit="return confirm('Reject this appointment?');">

                                                <input
                                                    type="hidden"
                                                    name="action"
                                                    value="reject">

                                                <input
                                                    type="hidden"
                                                    name="id"
                                                    value="<?php
                                                    echo htmlspecialchars(
                                                        $a['id']
                                                    );
                                                    ?>">

                                                <button
                                                    type="submit"
                                                    class="btn-icon-custom btn-icon-red"
                                                    title="Reject">

                                                    <i
                                                        class="fa-solid fa-xmark">
                                                    </i>

                                                </button>

                                            </form>


                                        <?php endif; ?>



                                        <!-- DELETE -->

                                        <form
                                            method="POST"
                                            class="d-inline"
                                            onsubmit="return confirm('Are you sure you want to delete this appointment?');">

                                            <input
                                                type="hidden"
                                                name="action"
                                                value="delete">

                                            <input
                                                type="hidden"
                                                name="id"
                                                value="<?php
                                                echo htmlspecialchars(
                                                    $a['id']
                                                );
                                                ?>">

                                            <button
                                                type="submit"
                                                class="btn-icon-custom btn-icon-red"
                                                title="Delete">

                                                <i
                                                    class="fa-solid fa-trash-can">
                                                </i>

                                            </button>

                                        </form>


                                    </td>


                                </tr>


                            <?php endforeach; ?>


                        <?php else: ?>


                            <!-- EMPTY STATE -->

                            <tr>

                                <td
                                    colspan="8"
                                    class="text-center py-5">


                                    <div
                                        class="text-muted">


                                        <i
                                            class="fa-solid
                                                   fa-calendar-xmark
                                                   fa-2x mb-3">

                                        </i>


                                        <h6
                                            class="fw-bold">

                                            No Appointments Found

                                        </h6>


                                        <p
                                            class="small mb-0">

                                            No appointments match
                                            the selected filters.

                                        </p>


                                    </div>


                                </td>

                            </tr>


                        <?php endif; ?>


                        </tbody>


                    </table>


                </div>



                <!-- =================================================
                     TABLE FOOTER
                ================================================== -->

                <div
                    class="p-3 bg-white border-top
                           d-flex justify-content-between
                           align-items-center">


                    <small
                        class="text-muted">

                        Showing

                        <strong>

                            <?php
                            echo count($appointments);
                            ?>

                        </strong>

                        appointment(s)


                        <?php if ($search !== ''): ?>

                            matching

                            <strong>

                                "<?php
                                echo htmlspecialchars(
                                    $search
                                );
                                ?>"

                            </strong>

                        <?php endif; ?>


                    </small>


                    <small
                        class="text-muted">

                        Total appointments:

                        <strong>

                            <?php
                            echo number_format(
                                $totalAppointments
                            );
                            ?>

                        </strong>

                    </small>


                </div>


            </div>


        </div>


    </div>

</div>



<?php
require_once __DIR__ . '/includes/footer.php';
?>