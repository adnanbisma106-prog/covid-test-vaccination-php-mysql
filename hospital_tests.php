<?php
/**
 * Hospital COVID-19 Tests Management
 * Hospital can create, view and update COVID-19 patient test results.
 */

require_once __DIR__ . '/config/db.php';
requireAuth(['hospital']);

$pageTitle = "COVID-19 Tests";

$hospName = $currentUser['name'] ?? 'City Hospital';
$hospId   = $currentUser['id'] ?? '';

/*
|--------------------------------------------------------------------------
| HANDLE ADD / UPDATE TEST
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? '';

    /*
    |--------------------------------------------------------------------------
    | UPDATE EXISTING TEST
    |--------------------------------------------------------------------------
    */

    if ($action === 'update_test') {

        $testId     = trim($_POST['test_id'] ?? '');
        $result     = trim($_POST['result'] ?? '');
        $resultDate = $_POST['result_date'] ?? date('Y-m-d');
        $ctValue    = trim($_POST['ct_value'] ?? '');
        $notes      = trim($_POST['notes'] ?? '');

        if ($testId === '') {

            setFlash("Invalid test ID.", "danger");
            header("Location: hospital_tests.php");
            exit;
        }

        /*
        | Update only the current hospital's test
        */

        $stmt = $conn->prepare("
            UPDATE covid_tests
            SET
                result = ?,
                result_date = ?,
                ct_value = ?,
                notes = ?
            WHERE id = ?
            AND hospital_id = ?
        ");

        if ($stmt) {

            $stmt->bind_param(
                "ssssss",
                $result,
                $resultDate,
                $ctValue,
                $notes,
                $testId,
                $hospId
            );

            if ($stmt->execute()) {

                logAudit(
                    $conn,
                    $hospName,
                    "Updated COVID test #$testId"
                );

                setFlash(
                    "COVID-19 test updated successfully!",
                    "success"
                );

            } else {

                setFlash(
                    "Unable to update COVID-19 test.",
                    "danger"
                );
            }

            $stmt->close();

        } else {

            setFlash(
                "Database error while updating test.",
                "danger"
            );
        }

        header("Location: hospital_tests.php");
        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | ADD NEW TEST
    |--------------------------------------------------------------------------
    */

    $patId      = $_POST['patient_id'] ?? '';
    $testType   = trim($_POST['test_type'] ?? 'RT-PCR');
    $result     = trim($_POST['result'] ?? 'Negative');
    $sampleDate = $_POST['sample_date'] ?? date('Y-m-d');
    $resultDate = $_POST['result_date'] ?? date('Y-m-d');
    $ctValue    = trim($_POST['ct_value'] ?? '');
    $notes      = trim($_POST['notes'] ?? '');

    if ($patId === '') {

        setFlash(
            "Please select a patient.",
            "danger"
        );

        header("Location: hospital_tests.php");
        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | FETCH PATIENT
    |--------------------------------------------------------------------------
    */

    $patName = '';
    $patCnic = '';

    $pstmt = $conn->prepare("
        SELECT name, cnic
        FROM users
        WHERE id = ?
        AND role = 'patient'
    ");

    if ($pstmt) {

        $pstmt->bind_param("s", $patId);
        $pstmt->execute();

        $pres = $pstmt->get_result()->fetch_assoc();

        if ($pres) {

            $patName = $pres['name'];
            $patCnic = $pres['cnic'];
        }

        $pstmt->close();
    }


    if ($patName === '') {

        setFlash(
            "Selected patient was not found.",
            "danger"
        );

        header("Location: hospital_tests.php");
        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | TEST ID + LAB ID
    |--------------------------------------------------------------------------
    */

    $id = 'test_' . time() . '_' . rand(100, 999);

    $labId =
        'LAB-COVID-' .
        date('Ymd') .
        '-' .
        rand(1000, 9999);


    /*
    |--------------------------------------------------------------------------
    | DOCTOR
    |--------------------------------------------------------------------------
    */

    $doctor = $currentUser['doctor_name']
        ?? 'Hospital Medical Officer';


    /*
    |--------------------------------------------------------------------------
    | DEFAULT CT VALUE
    |--------------------------------------------------------------------------
    */

    if ($ctValue === '') {

        if ($result === 'Negative') {

            $ctValue = 'Target Not Detected';

        } elseif ($result === 'Positive') {

            $ctValue = 'Positive Detection';

        } else {

            $ctValue = 'Inconclusive';
        }
    }


    /*
    |--------------------------------------------------------------------------
    | INSERT TEST
    |--------------------------------------------------------------------------
    */

    $stmt = $conn->prepare("
        INSERT INTO covid_tests
        (
            id,
            patient_id,
            patient_name,
            patient_cnic,
            hospital_id,
            hospital_name,
            test_type,
            sample_date,
            result_date,
            result,
            ct_value,
            lab_id,
            doctor,
            notes
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    if ($stmt) {

        $stmt->bind_param(
            "ssssssssssssss",
            $id,
            $patId,
            $patName,
            $patCnic,
            $hospId,
            $hospName,
            $testType,
            $sampleDate,
            $resultDate,
            $result,
            $ctValue,
            $labId,
            $doctor,
            $notes
        );

        if ($stmt->execute()) {

            logAudit(
                $conn,
                $hospName,
                "Uploaded $testType result ($result) for $patName"
            );

            setFlash(
                "COVID-19 test result saved successfully!",
                "success"
            );

        } else {

            setFlash(
                "Unable to save test result.",
                "danger"
            );
        }

        $stmt->close();

    } else {

        setFlash(
            "Database error while preparing test result.",
            "danger"
        );
    }

    header("Location: hospital_tests.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| SEARCH / FILTER
|--------------------------------------------------------------------------
*/

$search = trim($_GET['search'] ?? '');

$resultFilter =
    $_GET['result'] ?? 'All';

$testTypeFilter =
    $_GET['test_type'] ?? 'All';


/*
|--------------------------------------------------------------------------
| FETCH CURRENT HOSPITAL TESTS
|--------------------------------------------------------------------------
*/

$tests = [];

if ($conn && !$conn->connect_error) {

    $conditions = [];

    $safeHospital =
        $conn->real_escape_string($hospName);

    $conditions[] =
        "hospital_name = '$safeHospital'";


    if ($search !== '') {

        $safeSearch =
            $conn->real_escape_string($search);

        $conditions[] = "
            (
                patient_name LIKE '%$safeSearch%'
                OR patient_cnic LIKE '%$safeSearch%'
                OR lab_id LIKE '%$safeSearch%'
                OR test_type LIKE '%$safeSearch%'
            )
        ";
    }


    if ($resultFilter !== 'All') {

        $safeResult =
            $conn->real_escape_string($resultFilter);

        $conditions[] =
            "result = '$safeResult'";
    }


    if ($testTypeFilter !== 'All') {

        $safeType =
            $conn->real_escape_string($testTypeFilter);

        $conditions[] =
            "test_type = '$safeType'";
    }


    $sql = "
        SELECT *
        FROM covid_tests
        WHERE " . implode(" AND ", $conditions) . "
        ORDER BY result_date DESC
    ";

    $res = $conn->query($sql);

    if ($res) {

        while ($row = $res->fetch_assoc()) {

            $tests[] = $row;
        }
    }
}


/*
|--------------------------------------------------------------------------
| STATISTICS
|--------------------------------------------------------------------------
*/

$totalTests       = 0;
$negativeTests    = 0;
$positiveTests    = 0;
$inconclusiveTests = 0;

if ($conn && !$conn->connect_error) {

    $safeHospital =
        $conn->real_escape_string($hospName);


    $res = $conn->query("
        SELECT COUNT(*) AS cnt
        FROM covid_tests
        WHERE hospital_name = '$safeHospital'
    ");

    if ($res) {
        $totalTests =
            (int)$res->fetch_assoc()['cnt'];
    }


    $res = $conn->query("
        SELECT COUNT(*) AS cnt
        FROM covid_tests
        WHERE hospital_name = '$safeHospital'
        AND result = 'Negative'
    ");

    if ($res) {
        $negativeTests =
            (int)$res->fetch_assoc()['cnt'];
    }


    $res = $conn->query("
        SELECT COUNT(*) AS cnt
        FROM covid_tests
        WHERE hospital_name = '$safeHospital'
        AND result = 'Positive'
    ");

    if ($res) {
        $positiveTests =
            (int)$res->fetch_assoc()['cnt'];
    }


    $res = $conn->query("
        SELECT COUNT(*) AS cnt
        FROM covid_tests
        WHERE hospital_name = '$safeHospital'
        AND result = 'Inconclusive'
    ");

    if ($res) {
        $inconclusiveTests =
            (int)$res->fetch_assoc()['cnt'];
    }
}


/*
|--------------------------------------------------------------------------
| FETCH PATIENTS
|--------------------------------------------------------------------------
*/

$patients = [];

if ($conn && !$conn->connect_error) {

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
}


/*
|--------------------------------------------------------------------------
| HEADER
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/includes/header.php';
?>

<div class="app-container">

    <?php
    require_once __DIR__. '/includes/sidebar.php';
    ?>

    <div class="main-content">

        <?php
        require_once __DIR__ . '/includes/topbar.php';
        ?>

        <div class="page-body">

            <!-- PAGE HEADER -->

            <div class="d-flex justify-content-between align-items-center mb-4">

                <div>

                    <h2 class="fw-bold text-dark fs-3 mb-1">
                        COVID-19 Test Management
                    </h2>

                    <p class="text-muted small mb-0">

                        Manage diagnostic tests and patient results for

                        <strong>
                            <?php
                            echo htmlspecialchars($hospName);
                            ?>
                        </strong>

                    </p>

                </div>


                <button
                    class="btn btn-emerald btn-sm"
                    data-bs-toggle="modal"
                    data-bs-target="#addTestResultModal">

                    <i class="fa-solid fa-plus me-1"></i>

                    Enter New Test Result

                </button>

            </div>


            <!-- STATISTICS -->

            <div class="row g-3 mb-4">

                <div class="col-sm-6 col-xl-3">

                    <div class="stat-card-custom">

                        <div class="stat-icon-box stat-icon-blue">

                            <i class="fa-solid fa-vial-virus"></i>

                        </div>

                        <div>

                            <div class="stat-label-text">
                                Total Tests
                            </div>

                            <div class="stat-number-val">

                                <?php
                                echo number_format($totalTests);
                                ?>

                            </div>

                        </div>

                    </div>

                </div>


                <div class="col-sm-6 col-xl-3">

                    <div class="stat-card-custom">

                        <div class="stat-icon-box stat-icon-green">

                            <i class="fa-solid fa-circle-check"></i>

                        </div>

                        <div>

                            <div class="stat-label-text">
                                Negative
                            </div>

                            <div class="stat-number-val">

                                <?php
                                echo number_format($negativeTests);
                                ?>

                            </div>

                        </div>

                    </div>

                </div>


                <div class="col-sm-6 col-xl-3">

                    <div class="stat-card-custom">

                        <div class="stat-icon-box stat-icon-red">

                            <i class="fa-solid fa-circle-xmark"></i>

                        </div>

                        <div>

                            <div class="stat-label-text">
                                Positive
                            </div>

                            <div class="stat-number-val">

                                <?php
                                echo number_format($positiveTests);
                                ?>

                            </div>

                        </div>

                    </div>

                </div>


                <div class="col-sm-6 col-xl-3">

                    <div class="stat-card-custom">

                        <div class="stat-icon-box stat-icon-amber">

                            <i class="fa-solid fa-circle-question"></i>

                        </div>

                        <div>

                            <div class="stat-label-text">
                                Inconclusive
                            </div>

                            <div class="stat-number-val">

                                <?php
                                echo number_format($inconclusiveTests);
                                ?>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            <!-- TEST TABLE -->

            <div class="panel-card-custom">

                <div class="panel-header-custom">

                    <h3 class="panel-title-custom">

                        <i class="fa-solid fa-file-medical text-success"></i>

                        Diagnostic Test Reports

                    </h3>

                    <span class="badge bg-light text-dark border">

                        <?php
                        echo count($tests);
                        ?>

                        Record(s)

                    </span>

                </div>


                <!-- FILTER -->

                <form
                    method="GET"
                    class="p-3 bg-light border-bottom">

                    <div class="row g-2">

                        <div class="col-lg-5">

                            <div class="input-group">

                                <span class="input-group-text bg-white">

                                    <i class="fa-solid fa-magnifying-glass text-muted"></i>

                                </span>

                                <input
                                    type="text"
                                    name="search"
                                    class="form-control"
                                    placeholder="Search patient, CNIC, lab ID..."
                                    value="<?php
                                    echo htmlspecialchars($search);
                                    ?>">

                            </div>

                        </div>


                        <div class="col-lg-2">

                            <select
                                name="result"
                                class="form-select">

                                <option value="All">
                                    All Results
                                </option>

                                <option
                                    value="Negative"
                                    <?php
                                    echo $resultFilter === 'Negative'
                                        ? 'selected'
                                        : '';
                                    ?>>

                                    Negative

                                </option>

                                <option
                                    value="Positive"
                                    <?php
                                    echo $resultFilter === 'Positive'
                                        ? 'selected'
                                        : '';
                                    ?>>

                                    Positive

                                </option>

                                <option
                                    value="Inconclusive"
                                    <?php
                                    echo $resultFilter === 'Inconclusive'
                                        ? 'selected'
                                        : '';
                                    ?>>

                                    Inconclusive

                                </option>

                            </select>

                        </div>


                        <div class="col-lg-3">

                            <select
                                name="test_type"
                                class="form-select">

                                <option value="All">
                                    All Test Types
                                </option>

                                <option
                                    value="RT-PCR"
                                    <?php
                                    echo $testTypeFilter === 'RT-PCR'
                                        ? 'selected'
                                        : '';
                                    ?>>

                                    RT-PCR

                                </option>

                                <option
                                    value="Rapid Antigen"
                                    <?php
                                    echo $testTypeFilter === 'Rapid Antigen'
                                        ? 'selected'
                                        : '';
                                    ?>>

                                    Rapid Antigen

                                </option>

                                <option
                                    value="Antibody Serology"
                                    <?php
                                    echo $testTypeFilter === 'Antibody Serology'
                                        ? 'selected'
                                        : '';
                                    ?>>

                                    Antibody Serology

                                </option>

                            </select>

                        </div>


                        <div class="col-lg-2">

                            <div class="d-flex gap-2">

                                <button
                                    type="submit"
                                    class="btn btn-success w-100">

                                    <i class="fa-solid fa-filter me-1"></i>

                                    Filter

                                </button>


                                <a
                                    href="hospital_tests.php"
                                    class="btn btn-outline-secondary"
                                    title="Reset">

                                    <i class="fa-solid fa-rotate-left"></i>

                                </a>

                            </div>

                        </div>

                    </div>

                </form>


                <!-- TABLE -->

                <div class="table-responsive">

                    <table class="table table-custom align-middle mb-0">

                        <thead>

                            <tr>

                                <th>#</th>

                                <th>Patient</th>

                                <th>Lab ID</th>

                                <th>Test Type</th>

                                <th>Sample Date</th>

                                <th>Result</th>

                                <th class="text-end">
                                    Actions
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                        <?php if (!empty($tests)): ?>

                            <?php foreach ($tests as $idx => $t): ?>

                                <?php

                                $resLower =
                                    strtolower($t['result']);

                                if (
                                    strpos(
                                        $resLower,
                                        'neg'
                                    ) !== false
                                ) {

                                    $badgeClass =
                                        'badge-status-approved';

                                } elseif (
                                    strpos(
                                        $resLower,
                                        'pos'
                                    ) !== false
                                ) {

                                    $badgeClass =
                                        'badge-status-rejected';

                                } else {

                                    $badgeClass =
                                        'badge-status-pending';
                                }

                                ?>

                                <tr>

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
                                                class="rounded-circle bg-success bg-opacity-10 text-success fw-bold d-flex align-items-center justify-content-center"
                                                style="width:36px;height:36px;">

                                                <?php

                                                echo htmlspecialchars(
                                                    strtoupper(
                                                        substr(
                                                            $t['patient_name'],
                                                            0,
                                                            1
                                                        )
                                                    )
                                                );

                                                ?>

                                            </div>


                                            <div>

                                                <div class="fw-bold text-dark">

                                                    <?php
                                                    echo htmlspecialchars(
                                                        $t['patient_name']
                                                    );
                                                    ?>

                                                </div>

                                                <small class="text-muted">

                                                    <?php
                                                    echo htmlspecialchars(
                                                        $t['patient_cnic']
                                                    );
                                                    ?>

                                                </small>

                                            </div>

                                        </div>

                                    </td>


                                    <!-- LAB ID -->

                                    <td class="font-monospace small text-muted">

                                        <?php
                                        echo htmlspecialchars(
                                            $t['lab_id']
                                        );
                                        ?>

                                    </td>


                                    <!-- TEST TYPE -->

                                    <td class="fw-semibold text-success">

                                        <?php
                                        echo htmlspecialchars(
                                            $t['test_type']
                                        );
                                        ?>

                                    </td>


                                    <!-- SAMPLE DATE -->

                                    <td class="text-muted">

                                        <?php
                                        echo htmlspecialchars(
                                            $t['sample_date']
                                        );
                                        ?>

                                    </td>


                                    <!-- RESULT -->

                                    <td>

                                        <span
                                            class="badge-status
                                            <?php
                                            echo $badgeClass;
                                            ?>">

                                            <?php
                                            echo htmlspecialchars(
                                                $t['result']
                                            );
                                            ?>

                                        </span>

                                    </td>


                                    <!-- ACTIONS -->

                                    <td class="text-end">

                                        <div class="d-flex justify-content-end gap-1">

                                            <!-- EDIT -->

                                            <button
                                                type="button"
                                                class="btn btn-sm btn-outline-primary"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editTestModal<?php
                                                echo htmlspecialchars($t['id']);
                                                ?>">

                                                <i class="fa-solid fa-pen-to-square"></i>

                                                Edit

                                            </button>


                                            <!-- CERTIFICATE -->

                                            <a
                                                href="view_test_certificate.php?id=<?php
                                                echo urlencode($t['id']);
                                                ?>"
                                                class="btn btn-sm btn-success"
                                                target="_blank">

                                                <i class="fa-solid fa-file-medical me-1"></i>

                                                View / Print

                                            </a>

                                        </div>

                                    </td>

                                </tr>


                                <!-- =================================================
                                     EDIT TEST MODAL
                                ================================================== -->

                                <div
                                    class="modal fade"
                                    id="editTestModal<?php
                                    echo htmlspecialchars($t['id']);
                                    ?>"
                                    tabindex="-1"
                                    aria-hidden="true">

                                    <div
                                        class="modal-dialog modal-lg modal-dialog-scrollable">

                                        <div class="modal-content">

                                            <form method="POST">

                                                <input
                                                    type="hidden"
                                                    name="action"
                                                    value="update_test">

                                                <input
                                                    type="hidden"
                                                    name="test_id"
                                                    value="<?php
                                                    echo htmlspecialchars(
                                                        $t['id']
                                                    );
                                                    ?>">


                                                <div class="modal-header">

                                                    <h5
                                                        class="modal-title fw-bold text-primary">

                                                        <i class="fa-solid fa-pen-to-square me-2"></i>

                                                        Update COVID-19 Test

                                                    </h5>

                                                    <button
                                                        type="button"
                                                        class="btn-close"
                                                        data-bs-dismiss="modal">
                                                    </button>

                                                </div>


                                                <div class="modal-body">

                                                    <div class="alert alert-light border">

                                                        <strong>
                                                            Patient:
                                                        </strong>

                                                        <?php
                                                        echo htmlspecialchars(
                                                            $t['patient_name']
                                                        );
                                                        ?>

                                                        <br>

                                                        <strong>
                                                            Lab ID:
                                                        </strong>

                                                        <?php
                                                        echo htmlspecialchars(
                                                            $t['lab_id']
                                                        );
                                                        ?>

                                                    </div>


                                                    <!-- RESULT -->

                                                    <div class="mb-3">

                                                        <label
                                                            class="form-label small fw-bold">

                                                            Test Result *

                                                        </label>

                                                        <select
                                                            name="result"
                                                            class="form-select"
                                                            required>

                                                            <option
                                                                value="Negative"
                                                                <?php
                                                                echo $t['result'] === 'Negative'
                                                                    ? 'selected'
                                                                    : '';
                                                                ?>>

                                                                Negative

                                                            </option>

                                                            <option
                                                                value="Positive"
                                                                <?php
                                                                echo $t['result'] === 'Positive'
                                                                    ? 'selected'
                                                                    : '';
                                                                ?>>

                                                                Positive

                                                            </option>

                                                            <option
                                                                value="Inconclusive"
                                                                <?php
                                                                echo $t['result'] === 'Inconclusive'
                                                                    ? 'selected'
                                                                    : '';
                                                                ?>>

                                                                Inconclusive

                                                            </option>

                                                        </select>

                                                    </div>


                                                    <!-- DATE -->

                                                    <div class="mb-3">

                                                        <label
                                                            class="form-label small fw-bold">

                                                            Result Release Date *

                                                        </label>

                                                        <input
                                                            type="date"
                                                            name="result_date"
                                                            class="form-control"
                                                            value="<?php
                                                            echo htmlspecialchars(
                                                                $t['result_date']
                                                            );
                                                            ?>"
                                                            required>

                                                    </div>


                                                    <!-- CT VALUE -->

                                                    <div class="mb-3">

                                                        <label
                                                            class="form-label small fw-bold">

                                                            Cycle Threshold (Ct Value)

                                                        </label>

                                                        <input
                                                            type="text"
                                                            name="ct_value"
                                                            class="form-control"
                                                            value="<?php
                                                            echo htmlspecialchars(
                                                                $t['ct_value']
                                                            );
                                                            ?>"
                                                            placeholder="e.g. 38.5 or Target Not Detected">

                                                    </div>


                                                    <!-- NOTES -->

                                                    <div class="mb-3">

                                                        <label
                                                            class="form-label small fw-bold">

                                                            Pathologist / Doctor Remarks

                                                        </label>

                                                        <textarea
                                                            name="notes"
                                                            class="form-control"
                                                            rows="4"
                                                            placeholder="Enter remarks..."><?php
                                                            echo htmlspecialchars(
                                                                $t['notes']
                                                            );
                                                            ?></textarea>

                                                    </div>

                                                </div>


                                                <div class="modal-footer">

                                                    <button
                                                        type="button"
                                                        class="btn btn-secondary btn-sm"
                                                        data-bs-dismiss="modal">

                                                        Cancel

                                                    </button>

                                                    <button
                                                        type="submit"
                                                        class="btn btn-primary btn-sm">

                                                        <i class="fa-solid fa-floppy-disk me-1"></i>

                                                        Update Test Result

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

                                    <i
                                        class="fa-solid fa-file-circle-xmark fa-2x text-muted mb-3">
                                    </i>

                                    <div class="fw-semibold text-muted">

                                        No COVID-19 tests found

                                    </div>

                                    <small class="text-muted">

                                        Enter a new test result
                                        to create a record.

                                    </small>

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


<!-- =============================================================
     ADD TEST RESULT MODAL
============================================================= -->

<div
    class="modal fade"
    id="addTestResultModal"
    tabindex="-1"
    aria-hidden="true">

    <div
        class="modal-dialog modal-lg modal-dialog-scrollable">

        <div class="modal-content">

            <form method="POST">

                <div class="modal-header">

                    <h5 class="modal-title fw-bold text-success">

                        <i class="fa-solid fa-vial-virus me-2"></i>

                        Enter COVID-19 Test Result

                    </h5>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal">
                    </button>

                </div>


                <div class="modal-body">

                    <!-- PATIENT -->

                    <div class="mb-3">

                        <label
                            class="form-label small fw-bold">

                            Select Patient *

                        </label>

                        <select
                            name="patient_id"
                            class="form-select"
                            required>

                            <option
                                value=""
                                selected
                                disabled>

                                Select patient

                            </option>

                            <?php foreach ($patients as $p): ?>

                                <option
                                    value="<?php
                                    echo htmlspecialchars($p['id']);
                                    ?>">

                                    <?php
                                    echo htmlspecialchars($p['name']);
                                    ?>

                                    -

                                    <?php
                                    echo htmlspecialchars($p['cnic']);
                                    ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>


                    <!-- TEST TYPE + RESULT -->

                    <div class="row g-3 mb-3">

                        <div class="col-md-6">

                            <label
                                class="form-label small fw-bold">

                                Test Type *

                            </label>

                            <select
                                name="test_type"
                                class="form-select"
                                required>

                                <option value="RT-PCR">
                                    RT-PCR (Real-Time PCR)
                                </option>

                                <option value="Rapid Antigen">
                                    Rapid Antigen (RAT)
                                </option>

                                <option value="Antibody Serology">
                                    Antibody Serology
                                </option>

                            </select>

                        </div>


                        <div class="col-md-6">

                            <label
                                class="form-label small fw-bold">

                                Test Result *

                            </label>

                            <select
                                name="result"
                                class="form-select fw-bold"
                                required>

                                <option value="Negative">
                                    Negative
                                </option>

                                <option value="Positive">
                                    Positive
                                </option>

                                <option value="Inconclusive">
                                    Inconclusive
                                </option>

                            </select>

                        </div>

                    </div>


                    <!-- DATES -->

                    <div class="row g-3 mb-3">

                        <div class="col-md-6">

                            <label
                                class="form-label small fw-bold">

                                Sample Date *

                            </label>

                            <input
                                type="date"
                                name="sample_date"
                                class="form-control"
                                value="<?php
                                echo date('Y-m-d');
                                ?>"
                                required>

                        </div>


                        <div class="col-md-6">

                            <label
                                class="form-label small fw-bold">

                                Result Release Date *

                            </label>

                            <input
                                type="date"
                                name="result_date"
                                class="form-control"
                                value="<?php
                                echo date('Y-m-d');
                                ?>"
                                required>

                        </div>

                    </div>


                    <!-- CT VALUE -->

                    <div class="mb-3">

                        <label
                            class="form-label small fw-bold">

                            Cycle Threshold (Ct Value)

                        </label>

                        <input
                            type="text"
                            name="ct_value"
                            class="form-control"
                            placeholder="e.g. 38.5 or Target Not Detected">

                    </div>


                    <!-- NOTES -->

                    <div class="mb-3">

                        <label
                            class="form-label small fw-bold">

                            Pathologist / Doctor Remarks

                        </label>

                        <textarea
                            name="notes"
                            class="form-control"
                            rows="3"
                            placeholder="Enter clinical or laboratory remarks..."></textarea>

                    </div>


                    <div class="alert alert-light border small mb-0">

                        <i
                            class="fa-solid fa-circle-info text-primary me-1">
                        </i>

                        The test result will be linked to

                        <strong>
                            <?php
                            echo htmlspecialchars($hospName);
                            ?>
                        </strong>

                        and recorded in the audit log.

                    </div>

                </div>


                <div class="modal-footer">

                    <button
                        type="button"
                        class="btn btn-secondary btn-sm"
                        data-bs-dismiss="modal">

                        Cancel

                    </button>

                    <button
                        type="submit"
                        class="btn btn-success btn-sm">

                        <i class="fa-solid fa-floppy-disk me-1"></i>

                        Save Test Result

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>


<?php
require_once __DIR__ . '/includes/footer.php';
?>