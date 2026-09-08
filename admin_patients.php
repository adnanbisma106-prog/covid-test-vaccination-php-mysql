<?php
/**
 * Patients Management
 * COVID-19 Vaccination & Hospital Management System
 */

require_once __DIR__ . '/config/db.php';
requireAuth(['admin']);

$pageTitle = "Patients Management";

/*
|--------------------------------------------------------------------------
| Handle Actions
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? '';

    /*
    |--------------------------------------------------------------------------
    | ADD PATIENT
    |--------------------------------------------------------------------------
    */

    if ($action === 'add') {

        $name   = trim($_POST['name'] ?? '');
        $email  = trim($_POST['email'] ?? '');
        $phone  = trim($_POST['phone'] ?? '');
        $cnic   = trim($_POST['cnic'] ?? '');
        $status = $_POST['status'] ?? 'Active';

        $id = 'pat_' . time();

        if ($name === '' || $email === '' || $phone === '') {

            setFlash(
                "Please fill all required patient fields.",
                "danger"
            );

        } else {

            /*
             * Generate a temporary password.
             * The password is hashed before storing.
             */
            $temporaryPassword = password_hash(
                'password123',
                PASSWORD_DEFAULT
            );

            $stmt = $conn->prepare(
                "INSERT INTO users
                (id, name, email, password, role, phone, cnic, status)
                VALUES (?, ?, ?, ?, 'patient', ?, ?, ?)"
            );

            if ($stmt) {

                $stmt->bind_param(
                    "sssssss",
                    $id,
                    $name,
                    $email,
                    $temporaryPassword,
                    $phone,
                    $cnic,
                    $status
                );

                if ($stmt->execute()) {

                    logAudit(
                        $conn,
                        "Admin",
                        "Added new patient: $name ($email)"
                    );

                    setFlash(
                        "Patient '$name' added successfully!",
                        "success"
                    );

                } else {

                    setFlash(
                        "Unable to add patient. Email may already exist.",
                        "danger"
                    );
                }

                $stmt->close();

            } else {

                setFlash(
                    "Database error while adding patient.",
                    "danger"
                );
            }
        }

        header("Location: admin_patients.php");
        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | EDIT PATIENT
    |--------------------------------------------------------------------------
    */

    if ($action === 'edit') {

        $id     = $_POST['id'] ?? '';
        $name   = trim($_POST['name'] ?? '');
        $email  = trim($_POST['email'] ?? '');
        $phone  = trim($_POST['phone'] ?? '');
        $status = $_POST['status'] ?? 'Active';

        if ($id === '' || $name === '' || $email === '' || $phone === '') {

            setFlash(
                "Please fill all required fields.",
                "danger"
            );

        } else {

            $stmt = $conn->prepare(
                "UPDATE users
                 SET name = ?, email = ?, phone = ?, status = ?
                 WHERE id = ? AND role = 'patient'"
            );

            if ($stmt) {

                $stmt->bind_param(
                    "sssss",
                    $name,
                    $email,
                    $phone,
                    $status,
                    $id
                );

                if ($stmt->execute()) {

                    logAudit(
                        $conn,
                        "Admin",
                        "Updated patient details: $name"
                    );

                    setFlash(
                        "Patient updated successfully!",
                        "success"
                    );

                } else {

                    setFlash(
                        "Unable to update patient.",
                        "danger"
                    );
                }

                $stmt->close();

            } else {

                setFlash(
                    "Database error while updating patient.",
                    "danger"
                );
            }
        }

        header("Location: admin_patients.php");
        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | DELETE PATIENT
    |--------------------------------------------------------------------------
    */

    if ($action === 'delete') {

        $id = $_POST['id'] ?? '';

        if ($id !== '') {

            $stmt = $conn->prepare(
                "DELETE FROM users
                 WHERE id = ? AND role = 'patient'"
            );

            if ($stmt) {

                $stmt->bind_param("s", $id);
                $stmt->execute();

                logAudit(
                    $conn,
                    "Admin",
                    "Deleted patient record #$id"
                );

                setFlash(
                    "Patient deleted successfully.",
                    "info"
                );

                $stmt->close();
            }
        }

        header("Location: admin_patients.php");
        exit;
    }
}


/*
|--------------------------------------------------------------------------
| Search & Filter
|--------------------------------------------------------------------------
*/

$search = trim($_GET['search'] ?? '');
$statusFilter = trim($_GET['status'] ?? '');


/*
|--------------------------------------------------------------------------
| Fetch Patients
|--------------------------------------------------------------------------
*/

$patients = [];

if ($conn && !$conn->connect_error) {

    $sql = "
        SELECT *
        FROM users
        WHERE role = 'patient'
    ";

    if ($search !== '') {

        $safeSearch = $conn->real_escape_string($search);

        $sql .= "
            AND (
                name LIKE '%$safeSearch%'
                OR email LIKE '%$safeSearch%'
                OR phone LIKE '%$safeSearch%'
                OR cnic LIKE '%$safeSearch%'
            )
        ";
    }

    if ($statusFilter !== '') {

        $safeStatus = $conn->real_escape_string($statusFilter);

        $sql .= "
            AND status = '$safeStatus'
        ";
    }

    $sql .= " ORDER BY created_at DESC";

    $res = $conn->query($sql);

    if ($res) {

        while ($row = $res->fetch_assoc()) {

            $patients[] = $row;
        }
    }
}


/*
|--------------------------------------------------------------------------
| Patient Statistics
|--------------------------------------------------------------------------
*/

$totalPatients = 0;
$activePatients = 0;
$inactivePatients = 0;

if ($conn && !$conn->connect_error) {

    $resTotal = $conn->query(
        "SELECT COUNT(*) AS cnt
         FROM users
         WHERE role = 'patient'"
    );

    if ($resTotal) {
        $totalPatients =
            (int)$resTotal->fetch_assoc()['cnt'];
    }


    $resActive = $conn->query(
        "SELECT COUNT(*) AS cnt
         FROM users
         WHERE role = 'patient'
         AND status = 'Active'"
    );

    if ($resActive) {
        $activePatients =
            (int)$resActive->fetch_assoc()['cnt'];
    }


    $resInactive = $conn->query(
        "SELECT COUNT(*) AS cnt
         FROM users
         WHERE role = 'patient'
         AND status = 'Inactive'"
    );

    if ($resInactive) {
        $inactivePatients =
            (int)$resInactive->fetch_assoc()['cnt'];
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

    <!-- SIDEBAR -->
    <?php require_once __DIR__ . '/includes/sidebar.php'; ?>


    <!-- MAIN CONTENT -->
    <div class="main-content">

        <!-- TOPBAR -->
        <?php require_once __DIR__ . '/includes/topbar.php'; ?>


        <div class="page-body">


            <!-- =====================================================
                 PAGE HEADER
            ====================================================== -->

            <div class="d-flex justify-content-between align-items-center mb-4">

                <div>

                    <h2 class="fw-bold text-dark fs-3 mb-1">

                        Patients Management

                    </h2>

                    <p class="text-muted small mb-0">

                        Manage registered patients and their records

                    </p>

                </div>


                <div class="d-flex gap-2">

                    <a
                        href="export_csv.php?type=patients"
                        class="btn btn-outline-secondary btn-sm">

                        <i class="fa-solid fa-download me-1"></i>

                        Export CSV

                    </a>


                    <button
                        class="btn btn-success btn-sm"
                        data-bs-toggle="modal"
                        data-bs-target="#addPatientModal">

                        <i class="fa-solid fa-plus me-1"></i>

                        Add Patient

                    </button>

                </div>

            </div>



            <!-- =====================================================
                 STAT CARDS
            ====================================================== -->

            <div class="row g-3 mb-4">


                <!-- TOTAL -->
                <div class="col-md-4">

                    <div class="stat-card-custom">

                        <div
                            class="stat-icon-box stat-icon-green">

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

                        </div>

                    </div>

                </div>



                <!-- ACTIVE -->
                <div class="col-md-4">

                    <div class="stat-card-custom">

                        <div
                            class="stat-icon-box stat-icon-blue">

                            <i class="fa-solid fa-user-check"></i>

                        </div>

                        <div>

                            <div class="stat-label-text">

                                Active Patients

                            </div>

                            <div class="stat-number-val">

                                <?php
                                echo number_format($activePatients);
                                ?>

                            </div>

                        </div>

                    </div>

                </div>



                <!-- INACTIVE -->
                <div class="col-md-4">

                    <div class="stat-card-custom">

                        <div
                            class="stat-icon-box stat-icon-amber">

                            <i class="fa-solid fa-user-clock"></i>

                        </div>

                        <div>

                            <div class="stat-label-text">

                                Inactive Patients

                            </div>

                            <div class="stat-number-val">

                                <?php
                                echo number_format($inactivePatients);
                                ?>

                            </div>

                        </div>

                    </div>

                </div>

            </div>



            <!-- =====================================================
                 PATIENTS PANEL
            ====================================================== -->

            <div class="panel-card-custom">


                <!-- PANEL HEADER -->

                <div class="panel-header-custom">

                    <h3 class="panel-title-custom">

                        <i class="fa-solid fa-users text-success"></i>

                        Registered Patients

                    </h3>


                    <span
                        class="badge bg-light text-dark border">

                        <?php
                        echo count($patients);
                        ?>

                        Result(s)

                    </span>

                </div>



                <!-- =================================================
                     SEARCH & FILTER
                ================================================== -->

                <div
                    class="p-3 bg-light border-bottom">

                    <form
                        method="GET"
                        class="row g-2 align-items-center">


                        <!-- SEARCH -->

                        <div class="col-md-6">

                            <div class="input-group">

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
                                    placeholder="Search by name, email, phone or CNIC..."
                                    value="<?php
                                    echo htmlspecialchars($search);
                                    ?>">

                            </div>

                        </div>



                        <!-- STATUS -->

                        <div class="col-md-3">

                            <select
                                name="status"
                                class="form-select">

                                <option value="">
                                    All Status
                                </option>

                                <option
                                    value="Active"
                                    <?php
                                    echo $statusFilter === 'Active'
                                        ? 'selected'
                                        : '';
                                    ?>>

                                    Active

                                </option>

                                <option
                                    value="Inactive"
                                    <?php
                                    echo $statusFilter === 'Inactive'
                                        ? 'selected'
                                        : '';
                                    ?>>

                                    Inactive

                                </option>

                            </select>

                        </div>



                        <!-- SEARCH BUTTON -->

                        <div class="col-md-1">

                            <button
                                type="submit"
                                class="btn btn-success w-100">

                                <i
                                    class="fa-solid fa-search">
                                </i>

                            </button>

                        </div>



                        <!-- RESET -->

                        <div class="col-md-2">

                            <a
                                href="admin_patients.php"
                                class="btn btn-outline-secondary w-100">

                                <i
                                    class="fa-solid fa-rotate-left me-1">
                                </i>

                                Reset

                            </a>

                        </div>

                    </form>

                </div>



                <!-- =================================================
                     PATIENT TABLE
                ================================================== -->

                <div class="table-responsive">

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
                                    Contact
                                </th>

                                <th>
                                    CNIC / ID
                                </th>

                                <th>
                                    Status
                                </th>

                                <th>
                                    Registered
                                </th>

                                <th
                                    class="text-end"
                                    style="width:150px;">

                                    Action

                                </th>

                            </tr>

                        </thead>


                        <tbody>


                        <?php if (!empty($patients)): ?>


                            <?php foreach ($patients as $idx => $pat): ?>


                                <tr>


                                    <!-- NUMBER -->

                                    <td class="text-muted fw-bold">

                                        <?php
                                        echo $idx + 1;
                                        ?>

                                    </td>



                                    <!-- PATIENT -->

                                    <td>

                                        <div
                                            class="d-flex align-items-center gap-2">


                                            <div
                                                class="rounded-circle
                                                       bg-success
                                                       bg-opacity-10
                                                       text-success
                                                       fw-bold
                                                       d-flex
                                                       align-items-center
                                                       justify-content-center"
                                                style="width:38px;
                                                       height:38px;">

                                                <?php

                                                $patientName =
                                                    $pat['name'] ?? 'P';

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

                                                    ID:
                                                    <?php
                                                    echo htmlspecialchars(
                                                        $pat['id']
                                                    );
                                                    ?>

                                                </small>

                                            </div>

                                        </div>

                                    </td>



                                    <!-- CONTACT -->

                                    <td>

                                        <div
                                            class="text-dark">

                                            <?php
                                            echo htmlspecialchars(
                                                $pat['email'] ?? '-'
                                            );
                                            ?>

                                        </div>

                                        <small
                                            class="text-muted">

                                            <?php
                                            echo htmlspecialchars(
                                                $pat['phone'] ?? '-'
                                            );
                                            ?>

                                        </small>

                                    </td>



                                    <!-- CNIC -->

                                    <td class="font-monospace">

                                        <?php
                                        echo htmlspecialchars(
                                            $pat['cnic'] ?? '-'
                                        );
                                        ?>

                                    </td>



                                    <!-- STATUS -->

                                    <td>

                                        <?php

                                        $patientStatus =
                                            $pat['status'] ?? 'Active';

                                        $statusClass =
                                            $patientStatus === 'Active'
                                                ? 'badge-status-active'
                                                : 'badge-status-inactive';

                                        ?>

                                        <span
                                            class="badge-status
                                                   <?php
                                                   echo $statusClass;
                                                   ?>">

                                            <?php
                                            echo htmlspecialchars(
                                                $patientStatus
                                            );
                                            ?>

                                        </span>

                                    </td>



                                    <!-- REGISTERED DATE -->

                                    <td class="text-muted">

                                        <?php

                                        if (!empty($pat['created_at'])) {

                                            echo htmlspecialchars(
                                                date(
                                                    'd M Y',
                                                    strtotime(
                                                        $pat['created_at']
                                                    )
                                                )
                                            );

                                        } else {

                                            echo '-';
                                        }

                                        ?>

                                    </td>



                                    <!-- ACTIONS -->

                                    <td class="text-end">


                                        <!-- VIEW -->

                                        <button
                                            type="button"
                                            class="btn-icon-custom btn-icon-blue"
                                            data-bs-toggle="modal"
                                            data-bs-target="#viewModal_<?php
                                            echo htmlspecialchars(
                                                $pat['id']
                                            );
                                            ?>"
                                            title="View Patient">

                                            <i
                                                class="fa-solid fa-eye">
                                            </i>

                                        </button>



                                        <!-- EDIT -->

                                        <button
                                            type="button"
                                            class="btn-icon-custom btn-icon-blue"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editModal_<?php
                                            echo htmlspecialchars(
                                                $pat['id']
                                            );
                                            ?>"
                                            title="Edit Patient">

                                            <i
                                                class="fa-solid fa-pen-to-square">
                                            </i>

                                        </button>



                                        <!-- DELETE -->

                                        <form
                                            method="POST"
                                            class="d-inline"
                                            onsubmit="return confirm('Are you sure you want to delete this patient?');">

                                            <input
                                                type="hidden"
                                                name="action"
                                                value="delete">

                                            <input
                                                type="hidden"
                                                name="id"
                                                value="<?php
                                                echo htmlspecialchars(
                                                    $pat['id']
                                                );
                                                ?>">

                                            <button
                                                type="submit"
                                                class="btn-icon-custom btn-icon-red"
                                                title="Delete Patient">

                                                <i
                                                    class="fa-solid fa-trash-can">
                                                </i>

                                            </button>

                                        </form>

                                    </td>

                                </tr>



                                <!-- =================================================
                                     VIEW PATIENT MODAL
                                ================================================== -->

                                <div
                                    class="modal fade"
                                    id="viewModal_<?php
                                    echo htmlspecialchars($pat['id']);
                                    ?>"
                                    tabindex="-1">

                                    <div
                                        class="modal-dialog modal-lg">

                                        <div
                                            class="modal-content">


                                            <div
                                                class="modal-header">

                                                <h5
                                                    class="modal-title fw-bold">

                                                    <i
                                                        class="fa-solid fa-user text-success me-2">
                                                    </i>

                                                    Patient Details

                                                </h5>


                                                <button
                                                    type="button"
                                                    class="btn-close"
                                                    data-bs-dismiss="modal">
                                                </button>

                                            </div>



                                            <div
                                                class="modal-body">


                                                <div
                                                    class="row g-3">


                                                    <div
                                                        class="col-md-6">

                                                        <div
                                                            class="p-3 bg-light rounded">

                                                            <small
                                                                class="text-muted">

                                                                Patient ID

                                                            </small>

                                                            <div
                                                                class="fw-bold">

                                                                <?php
                                                                echo htmlspecialchars(
                                                                    $pat['id']
                                                                );
                                                                ?>

                                                            </div>

                                                        </div>

                                                    </div>



                                                    <div
                                                        class="col-md-6">

                                                        <div
                                                            class="p-3 bg-light rounded">

                                                            <small
                                                                class="text-muted">

                                                                Full Name

                                                            </small>

                                                            <div
                                                                class="fw-bold">

                                                                <?php
                                                                echo htmlspecialchars(
                                                                    $patientName
                                                                );
                                                                ?>

                                                            </div>

                                                        </div>

                                                    </div>



                                                    <div
                                                        class="col-md-6">

                                                        <div
                                                            class="p-3 bg-light rounded">

                                                            <small
                                                                class="text-muted">

                                                                Email

                                                            </small>

                                                            <div>

                                                                <?php
                                                                echo htmlspecialchars(
                                                                    $pat['email'] ?? '-'
                                                                );
                                                                ?>

                                                            </div>

                                                        </div>

                                                    </div>



                                                    <div
                                                        class="col-md-6">

                                                        <div
                                                            class="p-3 bg-light rounded">

                                                            <small
                                                                class="text-muted">

                                                                Phone

                                                            </small>

                                                            <div>

                                                                <?php
                                                                echo htmlspecialchars(
                                                                    $pat['phone'] ?? '-'
                                                                );
                                                                ?>

                                                            </div>

                                                        </div>

                                                    </div>



                                                    <div
                                                        class="col-md-6">

                                                        <div
                                                            class="p-3 bg-light rounded">

                                                            <small
                                                                class="text-muted">

                                                                CNIC / ID

                                                            </small>

                                                            <div>

                                                                <?php
                                                                echo htmlspecialchars(
                                                                    $pat['cnic'] ?? '-'
                                                                );
                                                                ?>

                                                            </div>

                                                        </div>

                                                    </div>



                                                    <div
                                                        class="col-md-6">

                                                        <div
                                                            class="p-3 bg-light rounded">

                                                            <small
                                                                class="text-muted">

                                                                Account Status

                                                            </small>

                                                            <div>

                                                                <span
                                                                    class="badge-status
                                                                    <?php
                                                                    echo $statusClass;
                                                                    ?>">

                                                                    <?php
                                                                    echo htmlspecialchars(
                                                                        $patientStatus
                                                                    );
                                                                    ?>

                                                                </span>

                                                            </div>

                                                        </div>

                                                    </div>



                                                    <div
                                                        class="col-12">

                                                        <div
                                                            class="p-3 border rounded">

                                                            <small
                                                                class="text-muted">

                                                                Registration Date

                                                            </small>

                                                            <div
                                                                class="fw-semibold">

                                                                <?php

                                                                if (!empty(
                                                                    $pat['created_at']
                                                                )) {

                                                                    echo htmlspecialchars(
                                                                        date(
                                                                            'd M Y, h:i A',
                                                                            strtotime(
                                                                                $pat['created_at']
                                                                            )
                                                                        )
                                                                    );

                                                                } else {

                                                                    echo '-';
                                                                }

                                                                ?>

                                                            </div>

                                                        </div>

                                                    </div>

                                                </div>


                                            </div>



                                            <div
                                                class="modal-footer">

                                                <button
                                                    type="button"
                                                    class="btn btn-secondary btn-sm"
                                                    data-bs-dismiss="modal">

                                                    Close

                                                </button>

                                            </div>


                                        </div>

                                    </div>

                                </div>



                                <!-- =================================================
                                     EDIT PATIENT MODAL
                                ================================================== -->

                                <div
                                    class="modal fade"
                                    id="editModal_<?php
                                    echo htmlspecialchars($pat['id']);
                                    ?>"
                                    tabindex="-1">

                                    <div
                                        class="modal-dialog">

                                        <div
                                            class="modal-content">


                                            <form method="POST">

                                                <input
                                                    type="hidden"
                                                    name="action"
                                                    value="edit">

                                                <input
                                                    type="hidden"
                                                    name="id"
                                                    value="<?php
                                                    echo htmlspecialchars(
                                                        $pat['id']
                                                    );
                                                    ?>">


                                                <div
                                                    class="modal-header">

                                                    <h5
                                                        class="modal-title fw-bold">

                                                        Edit Patient

                                                    </h5>

                                                    <button
                                                        type="button"
                                                        class="btn-close"
                                                        data-bs-dismiss="modal">
                                                    </button>

                                                </div>



                                                <div
                                                    class="modal-body">


                                                    <div
                                                        class="mb-3">

                                                        <label
                                                            class="form-label fw-semibold">

                                                            Full Name

                                                        </label>

                                                        <input
                                                            type="text"
                                                            name="name"
                                                            class="form-control"
                                                            value="<?php
                                                            echo htmlspecialchars(
                                                                $pat['name']
                                                            );
                                                            ?>"
                                                            required>

                                                    </div>



                                                    <div
                                                        class="mb-3">

                                                        <label
                                                            class="form-label fw-semibold">

                                                            Email

                                                        </label>

                                                        <input
                                                            type="email"
                                                            name="email"
                                                            class="form-control"
                                                            value="<?php
                                                            echo htmlspecialchars(
                                                                $pat['email']
                                                            );
                                                            ?>"
                                                            required>

                                                    </div>



                                                    <div
                                                        class="mb-3">

                                                        <label
                                                            class="form-label fw-semibold">

                                                            Phone

                                                        </label>

                                                        <input
                                                            type="text"
                                                            name="phone"
                                                            class="form-control"
                                                            value="<?php
                                                            echo htmlspecialchars(
                                                                $pat['phone']
                                                            );
                                                            ?>"
                                                            required>

                                                    </div>



                                                    <div
                                                        class="mb-3">

                                                        <label
                                                            class="form-label fw-semibold">

                                                            Status

                                                        </label>

                                                        <select
                                                            name="status"
                                                            class="form-select">

                                                            <option
                                                                value="Active"
                                                                <?php
                                                                echo $patientStatus === 'Active'
                                                                    ? 'selected'
                                                                    : '';
                                                                ?>>

                                                                Active

                                                            </option>

                                                            <option
                                                                value="Inactive"
                                                                <?php
                                                                echo $patientStatus === 'Inactive'
                                                                    ? 'selected'
                                                                    : '';
                                                                ?>>

                                                                Inactive

                                                            </option>

                                                        </select>

                                                    </div>


                                                </div>



                                                <div
                                                    class="modal-footer">

                                                    <button
                                                        type="button"
                                                        class="btn btn-secondary btn-sm"
                                                        data-bs-dismiss="modal">

                                                        Cancel

                                                    </button>

                                                    <button
                                                        type="submit"
                                                        class="btn btn-success btn-sm">

                                                        <i
                                                            class="fa-solid fa-save me-1">
                                                        </i>

                                                        Save Changes

                                                    </button>

                                                </div>


                                            </form>


                                        </div>

                                    </div>

                                </div>


                            <?php endforeach; ?>


                        <?php else: ?>


                            <tr>

                                <td
                                    colspan="7"
                                    class="text-center py-5">

                                    <div
                                        class="text-muted">

                                        <i
                                            class="fa-solid fa-user-slash fa-2x mb-3">
                                        </i>

                                        <h6 class="fw-bold">

                                            No Patients Found

                                        </h6>

                                        <p class="small mb-0">

                                            Try another search or add a new patient.

                                        </p>

                                    </div>

                                </td>

                            </tr>


                        <?php endif; ?>


                        </tbody>

                    </table>

                </div>



                <!-- =================================================
                     FOOTER / REAL COUNT
                ================================================== -->

                <div
                    class="p-3 bg-white border-top
                           d-flex justify-content-between
                           align-items-center">

                    <small class="text-muted">

                        Showing

                        <strong>
                            <?php
                            echo count($patients);
                            ?>
                        </strong>

                        patient(s)

                        <?php if ($search !== ''): ?>

                            matching:

                            <strong>
                                <?php
                                echo htmlspecialchars($search);
                                ?>
                            </strong>

                        <?php endif; ?>

                    </small>


                    <small class="text-muted">

                        Total registered:

                        <strong>

                            <?php
                            echo number_format($totalPatients);
                            ?>

                        </strong>

                    </small>

                </div>


            </div>

        </div>

    </div>

</div>



<!-- ===============================================================
     ADD PATIENT MODAL
================================================================ -->

<div
    class="modal fade"
    id="addPatientModal"
    tabindex="-1">

    <div
        class="modal-dialog">

        <div
            class="modal-content">


            <form method="POST">

                <input
                    type="hidden"
                    name="action"
                    value="add">


                <div
                    class="modal-header">

                    <h5
                        class="modal-title fw-bold">

                        <i
                            class="fa-solid fa-user-plus text-success me-2">
                        </i>

                        Add New Patient

                    </h5>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal">
                    </button>

                </div>



                <div
                    class="modal-body">


                    <div
                        class="mb-3">

                        <label
                            class="form-label fw-semibold">

                            Full Name
                            <span class="text-danger">*</span>

                        </label>

                        <input
                            type="text"
                            name="name"
                            class="form-control"
                            placeholder="Enter patient name"
                            required>

                    </div>



                    <div
                        class="mb-3">

                        <label
                            class="form-label fw-semibold">

                            Email Address
                            <span class="text-danger">*</span>

                        </label>

                        <input
                            type="email"
                            name="email"
                            class="form-control"
                            placeholder="patient@example.com"
                            required>

                    </div>



                    <div
                        class="row g-2 mb-3">


                        <div
                            class="col-md-6">

                            <label
                                class="form-label fw-semibold">

                                Phone
                                <span class="text-danger">*</span>

                            </label>

                            <input
                                type="text"
                                name="phone"
                                class="form-control"
                                placeholder="03XX-XXXXXXX"
                                required>

                        </div>



                        <div
                            class="col-md-6">

                            <label
                                class="form-label fw-semibold">

                                CNIC / ID

                            </label>

                            <input
                                type="text"
                                name="cnic"
                                class="form-control"
                                placeholder="XXXXX-XXXXXXX-X">

                        </div>


                    </div>



                    <div
                        class="mb-3">

                        <label
                            class="form-label fw-semibold">

                            Status

                        </label>

                        <select
                            name="status"
                            class="form-select">

                            <option value="Active">
                                Active
                            </option>

                            <option value="Inactive">
                                Inactive
                            </option>

                        </select>

                    </div>


                    <div
                        class="alert alert-info small mb-0">

                        <i
                            class="fa-solid fa-circle-info me-1">
                        </i>

                        A temporary login password will be created
                        for this patient.

                    </div>


                </div>



                <div
                    class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-secondary btn-sm"
                        data-bs-dismiss="modal">

                        Cancel

                    </button>


                    <button
                        type="submit"
                        class="btn btn-success btn-sm">

                        <i
                            class="fa-solid fa-user-plus me-1">
                        </i>

                        Add Patient

                    </button>

                </div>


            </form>


        </div>

    </div>

</div>



<?php
require_once __DIR__ . '/includes/footer.php';
?>