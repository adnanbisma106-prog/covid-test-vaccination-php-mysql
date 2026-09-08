<?php
/**
 * Hospitals Management & Approvals
 * COVID-19 Vaccination & Hospital Management System
 */

require_once __DIR__ . '/config/db.php';
requireAuth(['admin']);

$pageTitle = "Hospitals & Approvals";

$message = "";
$messageType = "success";

/*
|--------------------------------------------------------------------------
| Handle POST Actions
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? '';

    /*
    |--------------------------------------------------------------------------
    | ADD HOSPITAL
    |--------------------------------------------------------------------------
    */

    if ($action === 'add') {

        $name       = trim($_POST['name'] ?? '');
        $license_no = trim($_POST['license_no'] ?? '');
        $city       = trim($_POST['city'] ?? '');
        $services   = trim($_POST['services'] ?? '');
        $phone      = trim($_POST['phone'] ?? '');

        if (
            $name === '' ||
            $license_no === '' ||
            $city === '' ||
            $services === '' ||
            $phone === ''
        ) {

            setFlash("Please fill all hospital fields.", "danger");

        } else {

            $stmt = $conn->prepare(
                "INSERT INTO hospitals
                (name, license_no, city, services, phone, approved)
                VALUES (?, ?, ?, ?, ?, 0)"
            );

            if ($stmt) {

                $stmt->bind_param(
                    "sssss",
                    $name,
                    $license_no,
                    $city,
                    $services,
                    $phone
                );

                if ($stmt->execute()) {

                    logAudit(
                        $conn,
                        "Admin",
                        "Added hospital: $name"
                    );

                    setFlash(
                        "Hospital added successfully! It is pending approval.",
                        "success"
                    );

                } else {

                    setFlash(
                        "Unable to add hospital. License number may already exist.",
                        "danger"
                    );
                }

                $stmt->close();

            } else {

                setFlash(
                    "Database error while adding hospital.",
                    "danger"
                );
            }
        }

        header("Location: admin_hospitals.php");
        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | APPROVE HOSPITAL
    |--------------------------------------------------------------------------
    */

    if ($action === 'approve') {

        $id = $_POST['id'] ?? '';

        if ($id !== '') {

            $stmt = $conn->prepare(
                "UPDATE hospitals
                 SET approved = 1
                 WHERE id = ?"
            );

            if ($stmt) {

                $stmt->bind_param("i", $id);
                $stmt->execute();

                logAudit(
                    $conn,
                    "Admin",
                    "Approved hospital registration #$id"
                );

                setFlash(
                    "Hospital approved successfully!",
                    "success"
                );

                $stmt->close();
            }
        }

        header("Location: admin_hospitals.php");
        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | DELETE HOSPITAL
    |--------------------------------------------------------------------------
    */

    if ($action === 'delete') {

        $id = $_POST['id'] ?? '';

        if ($id !== '') {

            $stmt = $conn->prepare(
                "DELETE FROM hospitals
                 WHERE id = ?"
            );

            if ($stmt) {

                $stmt->bind_param("i", $id);
                $stmt->execute();

                logAudit(
                    $conn,
                    "Admin",
                    "Removed hospital #$id"
                );

                setFlash(
                    "Hospital removed successfully.",
                    "info"
                );

                $stmt->close();
            }
        }

        header("Location: admin_hospitals.php");
        exit;
    }
}


/*
|--------------------------------------------------------------------------
| FETCH HOSPITALS
|--------------------------------------------------------------------------
*/

$hospitals = [];

if ($conn && !$conn->connect_error) {

    $res = $conn->query(
        "SELECT *
         FROM hospitals
         ORDER BY created_at DESC"
    );

    if ($res) {

        while ($row = $res->fetch_assoc()) {

            $hospitals[] = $row;
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

                        Hospitals & Approvals

                    </h2>

                    <p class="text-muted small mb-0">

                        Add, manage and approve hospitals

                    </p>

                </div>


                <!-- ADD HOSPITAL BUTTON -->

                <button
                    type="button"
                    class="btn btn-success"
                    data-bs-toggle="collapse"
                    data-bs-target="#addHospitalForm">

                    <i class="fa-solid fa-plus me-1"></i>

                    Add Hospital

                </button>

            </div>



            <!-- =====================================================
                 ADD HOSPITAL FORM
            ====================================================== -->

            <div
                class="collapse mb-4"
                id="addHospitalForm">

                <div class="panel-card-custom">


                    <div class="panel-header-custom">

                        <h3 class="panel-title-custom">

                            <i class="fa-solid fa-hospital text-success"></i>

                            Add New Hospital

                        </h3>

                    </div>


                    <div class="p-4">

                        <form
                            method="POST"
                            action="admin_hospitals.php">


                            <input
                                type="hidden"
                                name="action"
                                value="add">


                            <div class="row g-3">


                                <!-- HOSPITAL NAME -->

                                <div class="col-md-6">

                                    <label class="form-label fw-semibold">

                                        Hospital Name
                                        <span class="text-danger">*</span>

                                    </label>

                                    <input
                                        type="text"
                                        name="name"
                                        class="form-control"
                                        placeholder="Enter hospital name"
                                        required>

                                </div>



                                <!-- LICENSE NUMBER -->

                                <div class="col-md-6">

                                    <label class="form-label fw-semibold">

                                        License Number
                                        <span class="text-danger">*</span>

                                    </label>

                                    <input
                                        type="text"
                                        name="license_no"
                                        class="form-control"
                                        placeholder="Enter license number"
                                        required>

                                </div>



                                <!-- CITY -->

                                <div class="col-md-4">

                                    <label class="form-label fw-semibold">

                                        City
                                        <span class="text-danger">*</span>

                                    </label>

                                    <input
                                        type="text"
                                        name="city"
                                        class="form-control"
                                        placeholder="e.g. Karachi"
                                        required>

                                </div>



                                <!-- PHONE -->

                                <div class="col-md-4">

                                    <label class="form-label fw-semibold">

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



                                <!-- SERVICES -->

                                <div class="col-md-4">

                                    <label class="form-label fw-semibold">

                                        Services
                                        <span class="text-danger">*</span>

                                    </label>

                                    <input
                                        type="text"
                                        name="services"
                                        class="form-control"
                                        placeholder="COVID Test, Vaccination"
                                        required>

                                </div>


                            </div>


                            <!-- BUTTONS -->

                            <div class="mt-4 d-flex gap-2">

                                <button
                                    type="submit"
                                    class="btn btn-success">

                                    <i class="fa-solid fa-save me-1"></i>

                                    Save Hospital

                                </button>


                                <button
                                    type="reset"
                                    class="btn btn-outline-secondary">

                                    <i class="fa-solid fa-rotate-left me-1"></i>

                                    Clear

                                </button>

                            </div>


                        </form>

                    </div>

                </div>

            </div>



            <!-- =====================================================
                 HOSPITALS LIST
            ====================================================== -->

            <div class="panel-card-custom">


                <div class="panel-header-custom">


                    <h2 class="panel-title-custom">

                        <i class="fa-solid fa-hospital text-primary"></i>

                        Hospitals & Approval Requests

                    </h2>


                    <span class="badge bg-light text-dark border">

                        <?php echo count($hospitals); ?>

                        Hospitals

                    </span>


                </div>



                <div class="table-responsive">


                    <table
                        class="table table-custom align-middle">


                        <thead>

                            <tr>

                                <th style="width:50px;">
                                    #
                                </th>

                                <th>
                                    Hospital Name
                                </th>

                                <th>
                                    City
                                </th>

                                <th>
                                    Services
                                </th>

                                <th>
                                    Phone
                                </th>

                                <th>
                                    Approval Status
                                </th>

                                <th
                                    class="text-end"
                                    style="width:180px;">

                                    Action

                                </th>

                            </tr>

                        </thead>


                        <tbody>


                        <?php if (!empty($hospitals)): ?>


                            <?php foreach ($hospitals as $idx => $h): ?>


                                <tr>


                                    <!-- NUMBER -->

                                    <td class="text-muted fw-bold">

                                        <?php
                                        echo $idx + 1;
                                        ?>

                                    </td>



                                    <!-- HOSPITAL -->

                                    <td>

                                        <div
                                            class="fw-bold text-dark">

                                            <?php
                                            echo htmlspecialchars(
                                                $h['name']
                                            );
                                            ?>

                                        </div>


                                        <div
                                            class="text-muted font-monospace small">

                                            License:

                                            <?php
                                            echo htmlspecialchars(
                                                $h['license_no']
                                            );
                                            ?>

                                        </div>

                                    </td>



                                    <!-- CITY -->

                                    <td class="text-muted">

                                        <?php
                                        echo htmlspecialchars(
                                            $h['city']
                                        );
                                        ?>

                                    </td>



                                    <!-- SERVICES -->

                                    <td class="fw-semibold text-success">

                                        <?php
                                        echo htmlspecialchars(
                                            $h['services']
                                        );
                                        ?>

                                    </td>



                                    <!-- PHONE -->

                                    <td class="text-muted font-monospace">

                                        <?php
                                        echo htmlspecialchars(
                                            $h['phone']
                                        );
                                        ?>

                                    </td>



                                    <!-- STATUS -->

                                    <td>


                                        <?php
                                        if ((int)$h['approved'] === 1):
                                        ?>

                                            <span
                                                class="badge-status
                                                       badge-status-approved">

                                                Approved

                                            </span>

                                        <?php else: ?>

                                            <span
                                                class="badge-status
                                                       badge-status-pending">

                                                Pending Approval

                                            </span>

                                        <?php endif; ?>


                                    </td>



                                    <!-- ACTIONS -->

                                    <td class="text-end">


                                        <?php
                                        if ((int)$h['approved'] === 0):
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
                                                    echo (int)$h['id'];
                                                    ?>">

                                                <button
                                                    type="submit"
                                                    class="btn btn-sm btn-success py-1 px-2 fw-bold"
                                                    title="Approve">

                                                    <i
                                                        class="fa-solid fa-check">
                                                    </i>

                                                    Approve

                                                </button>

                                            </form>


                                        <?php endif; ?>



                                        <!-- DELETE -->

                                        <form
                                            method="POST"
                                            class="d-inline"
                                            onsubmit="return confirm('Are you sure you want to remove this hospital?');">

                                            <input
                                                type="hidden"
                                                name="action"
                                                value="delete">

                                            <input
                                                type="hidden"
                                                name="id"
                                                value="<?php
                                                echo (int)$h['id'];
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


                            <tr>

                                <td
                                    colspan="7"
                                    class="text-center py-5 text-muted">

                                    <i
                                        class="fa-solid fa-hospital fa-2x mb-3 d-block">
                                    </i>

                                    No hospitals found.

                                    <br>

                                    <small>
                                        Click "Add Hospital" to add the first hospital.
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


<?php
require_once __DIR__ . '/includes/footer.php';
?>