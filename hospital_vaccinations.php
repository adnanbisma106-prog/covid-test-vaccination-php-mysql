<?php
/**
 * Hospital Vaccination Requests & Administration
 */

require_once __DIR__ . '/config/db.php';
requireAuth(['hospital']);

$pageTitle = "Vaccination Management";

$hospName = $currentUser['name'] ?? 'City Hospital';
$hospId   = $currentUser['id'] ?? 'hosp_city';


/* =========================================================
   HANDLE POST ACTIONS
========================================================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? '';


    /* =====================================================
       APPROVE VACCINATION REQUEST
    ===================================================== */

    if ($action === 'approve_request') {

        $appointmentId = trim($_POST['appointment_id'] ?? '');

        if ($appointmentId !== '') {

            $stmt = $conn->prepare("
                UPDATE appointments
                SET status = 'Approved'
                WHERE id = ?
                AND hospital_name = ?
                AND service LIKE '%Vacc%'
            ");

            if ($stmt) {

                $stmt->bind_param(
                    "ss",
                    $appointmentId,
                    $hospName
                );

                $stmt->execute();
                $stmt->close();

                logAudit(
                    $conn,
                    $hospName,
                    "Approved vaccination request #$appointmentId"
                );

                setFlash(
                    "Vaccination request approved successfully.",
                    "success"
                );
            }
        }

        header("Location: hospital_vaccinations.php");
        exit;
    }


    /* =====================================================
       REJECT VACCINATION REQUEST
    ===================================================== */

    if ($action === 'reject_request') {

        $appointmentId = trim($_POST['appointment_id'] ?? '');

        if ($appointmentId !== '') {

            $stmt = $conn->prepare("
                UPDATE appointments
                SET status = 'Rejected'
                WHERE id = ?
                AND hospital_name = ?
                AND service LIKE '%Vacc%'
            ");

            if ($stmt) {

                $stmt->bind_param(
                    "ss",
                    $appointmentId,
                    $hospName
                );

                $stmt->execute();
                $stmt->close();

                logAudit(
                    $conn,
                    $hospName,
                    "Rejected vaccination request #$appointmentId"
                );

                setFlash(
                    "Vaccination request rejected.",
                    "warning"
                );
            }
        }

        header("Location: hospital_vaccinations.php");
        exit;
    }


    /* =====================================================
       ADMINISTER VACCINE
    ===================================================== */

    if ($action === 'administer') {

        $patId = trim($_POST['patient_id'] ?? '');
        $vaccineName = trim($_POST['vaccine_name'] ?? '');
        $doseNum = intval($_POST['dose_number'] ?? 1);
        $doseDate = $_POST['dose_date'] ?? date('Y-m-d');
        $batchNo = trim($_POST['batch_no'] ?? '');
        $vaccinator = trim($_POST['vaccinator'] ?? '');


        if ($patId === '' || $vaccineName === '') {

            setFlash(
                "Please select patient and vaccine.",
                "danger"
            );

            header("Location: hospital_vaccinations.php");
            exit;
        }


        /* =================================================
           GET PATIENT INFORMATION
        ================================================= */

        $patName = 'Patient';
        $patCnic = '';

        $pstmt = $conn->prepare("
            SELECT name, cnic
            FROM users
            WHERE id = ?
            AND role = 'patient'
            LIMIT 1
        ");

        if ($pstmt) {

            $pstmt->bind_param("s", $patId);
            $pstmt->execute();

            $pres = $pstmt->get_result()->fetch_assoc();

            if ($pres) {

                $patName = $pres['name'];
                $patCnic = $pres['cnic'] ?? '';
            }

            $pstmt->close();
        }


        /* =================================================
           CHECK VACCINE STOCK
        ================================================= */

        $availableDoses = 0;

        $stockStmt = $conn->prepare("
            SELECT available_doses
            FROM vaccines
            WHERE name = ?
            AND status = 'Active'
            LIMIT 1
        ");

        if ($stockStmt) {

            $stockStmt->bind_param(
                "s",
                $vaccineName
            );

            $stockStmt->execute();

            $stockResult =
                $stockStmt->get_result()->fetch_assoc();

            if ($stockResult) {

                $availableDoses =
                    intval($stockResult['available_doses']);
            }

            $stockStmt->close();
        }


        if ($availableDoses <= 0) {

            setFlash(
                "This vaccine is currently out of stock.",
                "danger"
            );

            header("Location: hospital_vaccinations.php");
            exit;
        }


        /* =================================================
           NEXT DOSE DATE
        ================================================= */

        $nextDate = null;

        if ($doseNum === 1) {

            $nextDate = date(
                'Y-m-d',
                strtotime($doseDate . ' + 28 days')
            );
        }


        /* =================================================
           GENERATE ID & CERTIFICATE
        ================================================= */

        $id =
            'vacrec_' .
            time() .
            '_' .
            rand(100, 999);

        $certNo =
            'VAC-PK-' .
            rand(1000000, 9999999) .
            '-' .
            strtoupper(
                substr($patName, 0, 3)
            );


        /* =================================================
           INSERT VACCINATION RECORD
        ================================================= */

        $stmt = $conn->prepare("
            INSERT INTO vaccination_doses
            (
                id,
                patient_id,
                patient_name,
                patient_cnic,
                hospital_id,
                hospital_name,
                vaccine_name,
                dose_number,
                total_doses_required,
                dose_date,
                next_dose_date,
                batch_no,
                vaccinator,
                certificate_no
            )
            VALUES
            (
                ?, ?, ?, ?, ?, ?, ?, ?, 2, ?, ?, ?, ?, ?
            )
        ");

        if ($stmt) {

            $stmt->bind_param(
                "sssssssisssss",
                $id,
                $patId,
                $patName,
                $patCnic,
                $hospId,
                $hospName,
                $vaccineName,
                $doseNum,
                $doseDate,
                $nextDate,
                $batchNo,
                $vaccinator,
                $certNo
            );


            if ($stmt->execute()) {

                $stmt->close();


                /* =================================================
                   DECREASE VACCINE STOCK
                ================================================= */

                $stockUpdate = $conn->prepare("
                    UPDATE vaccines
                    SET available_doses = available_doses - 1
                    WHERE name = ?
                    AND status = 'Active'
                    AND available_doses > 0
                ");

                if ($stockUpdate) {

                    $stockUpdate->bind_param(
                        "s",
                        $vaccineName
                    );

                    $stockUpdate->execute();
                    $stockUpdate->close();
                }


                /* =================================================
                   AUDIT LOG
                ================================================= */

                logAudit(
                    $conn,
                    $hospName,
                    "Administered Dose $doseNum ($vaccineName) to $patName"
                );


                /* =================================================
                   MARK VACCINATION APPOINTMENT COMPLETED
                ================================================= */

                $appointmentUpdate = $conn->prepare("
                    UPDATE appointments
                    SET status = 'Completed'
                    WHERE patient_id = ?
                    AND hospital_name = ?
                    AND service LIKE '%Vacc%'
                    AND status = 'Approved'
                    ORDER BY date ASC
                    LIMIT 1
                ");

                if ($appointmentUpdate) {

                    $appointmentUpdate->bind_param(
                        "ss",
                        $patId,
                        $hospName
                    );

                    $appointmentUpdate->execute();
                    $appointmentUpdate->close();
                }


                setFlash(
                    "Vaccination administered and digital pass generated successfully!",
                    "success"
                );

            } else {

                $stmt->close();

                setFlash(
                    "Unable to save vaccination record.",
                    "danger"
                );
            }

        } else {

            setFlash(
                "Database error while saving vaccination record.",
                "danger"
            );
        }


        header("Location: hospital_vaccinations.php");
        exit;
    }
}


/* =========================================================
   FETCH VACCINATION REQUESTS
========================================================= */

$vaccineRequests = [];

$requestStmt = $conn->prepare("
    SELECT *
    FROM appointments
    WHERE hospital_name = ?
    AND service LIKE '%Vacc%'
    ORDER BY date DESC, time ASC
");

if ($requestStmt) {

    $requestStmt->bind_param(
        "s",
        $hospName
    );

    $requestStmt->execute();

    $requestResult =
        $requestStmt->get_result();

    while ($row = $requestResult->fetch_assoc()) {

        $vaccineRequests[] = $row;
    }

    $requestStmt->close();
}


/* =========================================================
   FETCH VACCINATION DOSES
========================================================= */

$doses = [];

$doseStmt = $conn->prepare("
    SELECT *
    FROM vaccination_doses
    WHERE hospital_name = ?
    ORDER BY dose_date DESC
");

if ($doseStmt) {

    $doseStmt->bind_param(
        "s",
        $hospName
    );

    $doseStmt->execute();

    $doseResult =
        $doseStmt->get_result();

    while ($row = $doseResult->fetch_assoc()) {

        $doses[] = $row;
    }

    $doseStmt->close();
}


/* =========================================================
   FETCH ACTIVE VACCINES
========================================================= */

$vaccines = [];

$res = $conn->query("
    SELECT name, available_doses
    FROM vaccines
    WHERE status = 'Active'
    ORDER BY name ASC
");

if ($res) {

    while ($row = $res->fetch_assoc()) {

        $vaccines[] = $row;
    }
}


/* =========================================================
   FETCH PATIENTS
========================================================= */

$patients = [];

$res = $conn->query("
    SELECT id, name, cnic
    FROM users
    WHERE role = 'patient'
    ORDER BY name ASC
");

if ($res) {

    while ($row = $res->fetch_assoc()) {

        $patients[] = $row;
    }
}


/* =========================================================
   STATISTICS
========================================================= */

$totalRequests = count($vaccineRequests);

$pendingRequests = 0;
$approvedRequests = 0;
$completedRequests = 0;
$rejectedRequests = 0;


foreach ($vaccineRequests as $request) {

    $status =
        strtolower(
            trim(
                $request['status'] ?? ''
            )
        );


    if ($status === 'pending') {

        $pendingRequests++;

    } elseif ($status === 'approved') {

        $approvedRequests++;

    } elseif ($status === 'completed') {

        $completedRequests++;

    } elseif ($status === 'rejected') {

        $rejectedRequests++;
    }
}


require_once __DIR__ . '/includes/header.php';
?>

<div class="app-container">

    <?php require_once __DIR__ . '/includes/sidebar.php'; ?>

    <div class="main-content">

        <?php require_once __DIR__ . '/includes/topbar.php'; ?>

        <div class="page-body">


            <!-- =================================================
                 PAGE HEADER
            ================================================== -->

            <div class="panel-card-custom mb-4">

                <div class="panel-header-custom">

                    <div>

                        <h2 class="panel-title-custom mb-1">

                            <i class="fa-solid fa-syringe text-success"></i>

                            Vaccination Management

                        </h2>

                        <small class="text-muted">

                            Manage vaccination requests and update vaccination status.

                        </small>

                    </div>


                    <button
                        class="btn btn-emerald btn-sm"
                        data-bs-toggle="modal"
                        data-bs-target="#administerDoseModal">

                        <i class="fa-solid fa-plus me-1"></i>

                        Record Dose Given

                    </button>

                </div>

            </div>


            <!-- =================================================
                 STATISTICS
            ================================================== -->

            <div class="row g-3 mb-4">

                <div class="col-md-3">

                    <div class="panel-card-custom text-center p-3">

                        <div class="fs-3 fw-bold text-primary">

                            <?php echo $totalRequests; ?>

                        </div>

                        <small class="text-muted">
                            Total Requests
                        </small>

                    </div>

                </div>


                <div class="col-md-3">

                    <div class="panel-card-custom text-center p-3">

                        <div class="fs-3 fw-bold text-warning">

                            <?php echo $pendingRequests; ?>

                        </div>

                        <small class="text-muted">
                            Pending
                        </small>

                    </div>

                </div>


                <div class="col-md-3">

                    <div class="panel-card-custom text-center p-3">

                        <div class="fs-3 fw-bold text-success">

                            <?php echo $approvedRequests; ?>

                        </div>

                        <small class="text-muted">
                            Approved
                        </small>

                    </div>

                </div>


                <div class="col-md-3">

                    <div class="panel-card-custom text-center p-3">

                        <div class="fs-3 fw-bold text-info">

                            <?php echo $completedRequests; ?>

                        </div>

                        <small class="text-muted">
                            Completed
                        </small>

                    </div>

                </div>

            </div>


            <!-- =================================================
                 VACCINATION REQUESTS
            ================================================== -->

            <div class="panel-card-custom mb-4">

                <div class="panel-header-custom">

                    <h3 class="panel-title-custom">

                        <i class="fa-solid fa-calendar-check text-success"></i>

                        Vaccination Requests

                    </h3>

                </div>


                <div class="table-responsive">

                    <table class="table table-custom align-middle">

                        <thead>

                            <tr>

                                <th>#</th>
                                <th>Patient</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Service</th>
                                <th>Status</th>
                                <th class="text-end">
                                    Action
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                        <?php if (!empty($vaccineRequests)): ?>

                            <?php foreach ($vaccineRequests as $idx => $r): ?>

                                <?php

                                $status =
                                    $r['status'] ?? 'Pending';

                                $statusLower =
                                    strtolower($status);


                                if ($statusLower === 'approved') {

                                    $badge =
                                        'badge-status-approved';

                                } elseif ($statusLower === 'rejected') {

                                    $badge =
                                        'badge-status-rejected';

                                } elseif ($statusLower === 'completed') {

                                    $badge =
                                        'badge-status-approved';

                                } else {

                                    $badge =
                                        'badge-status-pending';
                                }

                                ?>

                                <tr>

                                    <td class="fw-bold text-muted">

                                        <?php
                                        echo $idx + 1;
                                        ?>

                                    </td>


                                    <td>

                                        <div class="fw-bold">

                                            <?php

                                            echo htmlspecialchars(
                                                $r['patient_name'] ??
                                                'Patient'
                                            );

                                            ?>

                                        </div>


                                        <?php if (!empty($r['patient_cnic'])): ?>

                                            <small class="text-muted">

                                                <?php

                                                echo htmlspecialchars(
                                                    $r['patient_cnic']
                                                );

                                                ?>

                                            </small>

                                        <?php endif; ?>

                                    </td>


                                    <td>

                                        <?php

                                        echo htmlspecialchars(
                                            $r['date'] ?? '-'
                                        );

                                        ?>

                                    </td>


                                    <td>

                                        <?php

                                        echo htmlspecialchars(
                                            $r['time'] ?? '-'
                                        );

                                        ?>

                                    </td>


                                    <td>

                                        <?php

                                        echo htmlspecialchars(
                                            $r['service'] ??
                                            'Vaccination'
                                        );

                                        ?>

                                    </td>


                                    <td>

                                        <span
                                            class="badge-status <?php echo $badge; ?>"
                                        >

                                            <?php

                                            echo htmlspecialchars(
                                                $status
                                            );

                                            ?>

                                        </span>

                                    </td>


                                    <td class="text-end">

                                        <?php if ($statusLower === 'pending'): ?>

                                            <div class="d-flex justify-content-end gap-1">


                                                <!-- APPROVE -->

                                                <form method="POST">

                                                    <input
                                                        type="hidden"
                                                        name="action"
                                                        value="approve_request"
                                                    >

                                                    <input
                                                        type="hidden"
                                                        name="appointment_id"
                                                        value="<?php echo htmlspecialchars($r['id']); ?>"
                                                    >

                                                    <button
                                                        type="submit"
                                                        class="btn btn-sm btn-success"
                                                    >

                                                        <i class="fa-solid fa-check"></i>

                                                        Approve

                                                    </button>

                                                </form>


                                                <!-- REJECT -->

                                                <form method="POST">

                                                    <input
                                                        type="hidden"
                                                        name="action"
                                                        value="reject_request"
                                                    >

                                                    <input
                                                        type="hidden"
                                                        name="appointment_id"
                                                        value="<?php echo htmlspecialchars($r['id']); ?>"
                                                    >

                                                    <button
                                                        type="submit"
                                                        class="btn btn-sm btn-outline-danger"
                                                    >

                                                        <i class="fa-solid fa-xmark"></i>

                                                        Reject

                                                    </button>

                                                </form>

                                            </div>


                                        <?php elseif ($statusLower === 'approved'): ?>

                                            <span class="text-success fw-semibold">

                                                <i class="fa-solid fa-circle-check"></i>

                                                Ready for Vaccination

                                            </span>


                                        <?php elseif ($statusLower === 'completed'): ?>

                                            <span class="text-info fw-semibold">

                                                <i class="fa-solid fa-check-double"></i>

                                                Completed

                                            </span>


                                        <?php else: ?>

                                            <span class="text-muted">

                                                No action

                                            </span>

                                        <?php endif; ?>

                                    </td>

                                </tr>

                            <?php endforeach; ?>


                        <?php else: ?>

                            <tr>

                                <td
                                    colspan="7"
                                    class="text-center py-5 text-muted"
                                >

                                    <i
                                        class="fa-solid fa-calendar-xmark fs-2 mb-2"
                                    ></i>

                                    <div>

                                        No vaccination requests found.

                                    </div>

                                </td>

                            </tr>

                        <?php endif; ?>

                        </tbody>

                    </table>

                </div>

            </div>


            <!-- =================================================
                 VACCINATION ADMINISTRATION LOG
            ================================================== -->

            <div class="panel-card-custom">

                <div class="panel-header-custom">

                    <h3 class="panel-title-custom">

                        <i class="fa-solid fa-syringe text-success"></i>

                        Vaccination Administration Log

                    </h3>

                </div>


                <div class="table-responsive">

                    <table class="table table-custom align-middle">

                        <thead>

                            <tr>

                                <th>#</th>
                                <th>Beneficiary</th>
                                <th>Vaccine</th>
                                <th>Dose</th>
                                <th>Date Given</th>
                                <th>Next Due</th>
                                <th class="text-end">
                                    Digital Pass
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                        <?php if (!empty($doses)): ?>

                            <?php foreach ($doses as $idx => $d): ?>

                                <tr>

                                    <td class="text-muted fw-bold">

                                        <?php
                                        echo $idx + 1;
                                        ?>

                                    </td>


                                    <td>

                                        <div class="fw-bold">

                                            <?php

                                            echo htmlspecialchars(
                                                $d['patient_name']
                                            );

                                            ?>

                                        </div>


                                        <small class="text-muted">

                                            <?php

                                            echo htmlspecialchars(
                                                $d['patient_cnic'] ?? ''
                                            );

                                            ?>

                                        </small>

                                    </td>


                                    <td class="fw-bold text-success">

                                        <?php

                                        echo htmlspecialchars(
                                            $d['vaccine_name']
                                        );

                                        ?>

                                    </td>


                                    <td class="fw-semibold">

                                        Dose

                                        <?php

                                        echo htmlspecialchars(
                                            $d['dose_number']
                                        );

                                        ?>

                                    </td>


                                    <td class="text-muted">

                                        <?php

                                        echo htmlspecialchars(
                                            $d['dose_date']
                                        );

                                        ?>

                                    </td>


                                    <td class="text-muted">

                                        <?php

                                        echo htmlspecialchars(
                                            $d['next_dose_date'] ??
                                            'Completed'
                                        );

                                        ?>

                                    </td>


                                    <td class="text-end">

                                        <a
                                            href="view_vaccine_certificate.php?id=<?php echo urlencode($d['id']); ?>"
                                            class="btn btn-sm btn-success py-1 px-2 fw-bold"
                                            target="_blank"
                                        >

                                            <i class="fa-solid fa-certificate me-1"></i>

                                            Pass / QR

                                        </a>

                                    </td>

                                </tr>

                            <?php endforeach; ?>


                        <?php else: ?>

                            <tr>

                                <td
                                    colspan="7"
                                    class="text-center py-5 text-muted"
                                >

                                    No vaccination records found.

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


<!-- =========================================================
     ADMINISTER DOSE MODAL
========================================================= -->

<div
    class="modal fade"
    id="administerDoseModal"
    tabindex="-1"
    aria-hidden="true"
>

    <div class="modal-dialog modal-lg">

        <div class="modal-content">

            <form method="POST">

                <input
                    type="hidden"
                    name="action"
                    value="administer"
                >


                <div class="modal-header">

                    <h5 class="modal-title fw-bold text-success">

                        <i class="fa-solid fa-syringe me-2"></i>

                        Administer COVID-19 Vaccine

                    </h5>


                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                    ></button>

                </div>


                <div class="modal-body">


                    <!-- PATIENT -->

                    <div class="mb-3">

                        <label class="form-label small fw-bold">

                            Select Beneficiary Patient *

                        </label>


                        <select
                            name="patient_id"
                            class="form-select"
                            required
                        >

                            <option
                                value=""
                                selected
                                disabled
                            >

                                Select patient

                            </option>


                            <?php foreach ($patients as $p): ?>

                                <option
                                    value="<?php echo htmlspecialchars($p['id']); ?>"
                                >

                                    <?php

                                    echo htmlspecialchars(
                                        $p['name']
                                    );

                                    ?>

                                    -

                                    <?php

                                    echo htmlspecialchars(
                                        $p['cnic']
                                    );

                                    ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>


                    <!-- VACCINE + DOSE -->

                    <div class="row g-3 mb-3">

                        <div class="col-md-8">

                            <label class="form-label small fw-bold">

                                Vaccine Brand *

                            </label>


                            <select
                                name="vaccine_name"
                                class="form-select fw-semibold"
                                required
                            >

                                <option
                                    value=""
                                    selected
                                    disabled
                                >

                                    Select vaccine

                                </option>


                                <?php foreach ($vaccines as $v): ?>

                                    <option
                                        value="<?php echo htmlspecialchars($v['name']); ?>"
                                    >

                                        <?php

                                        echo htmlspecialchars(
                                            $v['name']
                                        );

                                        ?>

                                        (Available:

                                        <?php

                                        echo htmlspecialchars(
                                            $v['available_doses']
                                        );

                                        ?>

                                        doses)

                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>


                        <div class="col-md-4">

                            <label class="form-label small fw-bold">

                                Dose Number *

                            </label>


                            <select
                                name="dose_number"
                                class="form-select fw-bold"
                                required
                            >

                                <option value="1">
                                    Dose 1
                                </option>

                                <option value="2">
                                    Dose 2
                                </option>

                                <option value="3">
                                    Booster
                                </option>

                            </select>

                        </div>

                    </div>


                    <!-- DATE + BATCH -->

                    <div class="row g-3 mb-3">

                        <div class="col-md-6">

                            <label class="form-label small fw-bold">

                                Date Administered *

                            </label>


                            <input
                                type="date"
                                name="dose_date"
                                class="form-control"
                                value="<?php echo date('Y-m-d'); ?>"
                                required
                            >

                        </div>


                        <div class="col-md-6">

                            <label class="form-label small fw-bold">

                                Batch / Lot Number *

                            </label>


                            <input
                                type="text"
                                name="batch_no"
                                class="form-control font-monospace"
                                value="LOT-<?php echo rand(1000,9999); ?>X"
                                required
                            >

                        </div>

                    </div>


                    <!-- VACCINATOR -->

                    <div class="mb-3">

                        <label class="form-label small fw-bold">

                            Vaccinator / Nurse Name *

                        </label>


                        <input
                            type="text"
                            name="vaccinator"
                            class="form-control"
                            value="Nurse Fatima Noor"
                            required
                        >

                    </div>


                    <div class="alert alert-info small mb-0">

                        <i class="fa-solid fa-circle-info me-1"></i>

                        After administration, the vaccination record will
                        be saved and a digital pass/QR certificate can be viewed.

                    </div>

                </div>


                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-secondary btn-sm"
                        data-bs-dismiss="modal"
                    >

                        Cancel

                    </button>


                    <button
                        type="submit"
                        class="btn btn-emerald btn-sm"
                    >

                        <i class="fa-solid fa-qrcode me-1"></i>

                        Administer & Issue Pass

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>


<?php require_once __DIR__ . '/includes/footer.php'; ?>