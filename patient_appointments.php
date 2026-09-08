<?php
/**
 * Patient Appointments
 */
require_once __DIR__ . '/config/db.php';
requireAuth(['patient']);

$currentUser = getLoggedInUser();

$pageTitle = "My Appointments";

$patId    = $currentUser['id'] ?? '';
$patName  = $currentUser['name'] ?? '';
$patEmail = $currentUser['email'] ?? '';

// =====================================================
// Cancel Appointment
// =====================================================

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? '';

    if ($action === 'cancel') {

        $appointmentId = trim($_POST['appointment_id'] ?? '');

        if ($appointmentId !== '') {

            $stmt = $conn->prepare("
                DELETE FROM appointments
                WHERE id = ?
                AND (
                    patient_id = ?
                    OR patient_email = ?
                    OR patient_name = ?
                )
                AND status = 'Pending'
            ");

            if ($stmt) {

                $stmt->bind_param(
                    "ssss",
                    $appointmentId,
                    $patId,
                    $patEmail,
                    $patName
                );

                $stmt->execute();

                if ($stmt->affected_rows > 0) {

                    setFlash(
                        "Appointment cancelled successfully.",
                        "success"
                    );

                } else {

                    setFlash(
                        "Appointment could not be cancelled.",
                        "danger"
                    );
                }

                $stmt->close();

            } else {

                setFlash(
                    "Database error while cancelling appointment.",
                    "danger"
                );
            }
        }

        header("Location: patient_appointments.php");
        exit;
    }
}


// =====================================================
// Fetch Patient Appointments
// =====================================================

$appointments = [];

$stmt = $conn->prepare("
    SELECT *
    FROM appointments
    WHERE
        patient_id = ?
        OR patient_email = ?
        OR patient_name = ?
    ORDER BY date DESC, time DESC
");

if (!$stmt) {

    die(
        "Appointment Query Error: " .
        htmlspecialchars($conn->error)
    );
}

$stmt->bind_param(
    "sss",
    $patId,
    $patEmail,
    $patName
);

$stmt->execute();

$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {

    $appointments[] = $row;
}

$stmt->close();


// =====================================================
// Statistics
// =====================================================

$totalAppointments     = count($appointments);
$pendingAppointments   = 0;
$approvedAppointments   = 0;
$completedAppointments = 0;
$rejectedAppointments   = 0;

foreach ($appointments as $appointment) {

    $status = strtolower(
        trim($appointment['status'] ?? '')
    );

    if ($status === 'pending') {
        $pendingAppointments++;
    }

    elseif ($status === 'approved') {
        $approvedAppointments++;
    }

    elseif ($status === 'completed') {
        $completedAppointments++;
    }

    elseif ($status === 'rejected') {
        $rejectedAppointments++;
    }
}


// =====================================================
// Header
// =====================================================

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

            <div class="d-flex justify-content-between align-items-center mb-4">

                <div>

                    <h2 class="fw-bold mb-1">

                        <i class="fa-solid fa-calendar-check text-success me-2"></i>

                        My Appointments

                    </h2>


                    <p class="text-muted mb-0">

                        View and manage your hospital appointments.

                    </p>

                </div>


                <a href="patient_book.php"
                   class="btn btn-emerald">

                    <i class="fa-solid fa-calendar-plus me-1"></i>

                    Book Appointment

                </a>

            </div>



            <!-- =================================================
                 STATISTICS
            ================================================== -->

            <div class="row g-3 mb-4">


                <!-- Total -->

                <div class="col-md-3">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-body">

                            <div class="d-flex justify-content-between align-items-center">

                                <div>

                                    <small class="text-muted">
                                        Total
                                    </small>

                                    <h3 class="fw-bold mb-0">

                                        <?php
                                        echo $totalAppointments;
                                        ?>

                                    </h3>

                                </div>


                                <div class="text-success fs-3">

                                    <i class="fa-solid fa-calendar"></i>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>



                <!-- Pending -->

                <div class="col-md-3">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-body">

                            <div class="d-flex justify-content-between align-items-center">

                                <div>

                                    <small class="text-muted">
                                        Pending
                                    </small>

                                    <h3 class="fw-bold mb-0">

                                        <?php
                                        echo $pendingAppointments;
                                        ?>

                                    </h3>

                                </div>


                                <div class="text-warning fs-3">

                                    <i class="fa-solid fa-clock"></i>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>



                <!-- Approved -->

                <div class="col-md-3">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-body">

                            <div class="d-flex justify-content-between align-items-center">

                                <div>

                                    <small class="text-muted">
                                        Approved
                                    </small>

                                    <h3 class="fw-bold mb-0">

                                        <?php
                                        echo $approvedAppointments;
                                        ?>

                                    </h3>

                                </div>


                                <div class="text-primary fs-3">

                                    <i class="fa-solid fa-circle-check"></i>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>



                <!-- Completed -->

                <div class="col-md-3">

                    <div class="card border-0 shadow-sm h-100">

                        <div class="card-body">

                            <div class="d-flex justify-content-between align-items-center">

                                <div>

                                    <small class="text-muted">
                                        Completed
                                    </small>

                                    <h3 class="fw-bold mb-0">

                                        <?php
                                        echo $completedAppointments;
                                        ?>

                                    </h3>

                                </div>


                                <div class="text-success fs-3">

                                    <i class="fa-solid fa-circle-check"></i>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>


            </div>



            <!-- =================================================
                 APPOINTMENT HISTORY
            ================================================== -->

            <div class="panel-card-custom">


                <div class="panel-header-custom">

                    <div>

                        <h3 class="panel-title-custom mb-1">

                            Appointment History

                        </h3>


                        <small class="text-muted">

                            Your booked hospital appointments

                        </small>

                    </div>

                </div>



                <div class="p-3">


                    <?php if (!empty($appointments)): ?>


                        <div class="table-responsive">

                            <table class="table table-hover align-middle">


                                <thead>

                                    <tr>

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

                                        <th>
                                            Message
                                        </th>

                                        <th class="text-center">
                                            Action
                                        </th>

                                    </tr>

                                </thead>



                                <tbody>


                                <?php foreach ($appointments as $appointment): ?>


                                    <?php

                                    $status = trim(
                                        $appointment['status'] ?? 'Pending'
                                    );

                                    $statusLower = strtolower($status);


                                    if ($statusLower === 'pending') {

                                        $badgeClass =
                                            'bg-warning text-dark';

                                    }

                                    elseif ($statusLower === 'approved') {

                                        $badgeClass =
                                            'bg-success';

                                    }

                                    elseif ($statusLower === 'completed') {

                                        $badgeClass =
                                            'bg-primary';

                                    }

                                    elseif ($statusLower === 'rejected') {

                                        $badgeClass =
                                            'bg-danger';

                                    }

                                    else {

                                        $badgeClass =
                                            'bg-secondary';
                                    }

                                    ?>


                                    <tr>


                                        <!-- Hospital -->

                                        <td>

                                            <div class="fw-semibold">

                                                <?php

                                                echo htmlspecialchars(
                                                    $appointment['hospital_name']
                                                    ?? 'Hospital'
                                                );

                                                ?>

                                            </div>

                                        </td>



                                        <!-- Service -->

                                        <td>

                                            <span class="fw-semibold">

                                                <?php

                                                echo htmlspecialchars(
                                                    $appointment['service']
                                                    ?? 'Appointment'
                                                );

                                                ?>

                                            </span>

                                        </td>



                                        <!-- Date -->

                                        <td>

                                            <?php

                                            echo htmlspecialchars(
                                                $appointment['date']
                                                ?? '-'
                                            );

                                            ?>

                                        </td>



                                        <!-- Time -->

                                        <td>

                                            <?php

                                            echo htmlspecialchars(
                                                $appointment['time']
                                                ?? '-'
                                            );

                                            ?>

                                        </td>



                                        <!-- Status -->

                                        <td>

                                            <span class="badge <?php echo $badgeClass; ?> px-3 py-2">

                                                <?php

                                                echo htmlspecialchars(
                                                    $status
                                                );

                                                ?>

                                            </span>

                                        </td>



                                        <!-- Message -->

                                        <td>

                                            <span class="text-muted small">

                                                <?php

                                                $message = trim(
                                                    $appointment['message']
                                                    ?? ''
                                                );


                                                echo htmlspecialchars(

                                                    $message !== ''

                                                        ? $message

                                                        : 'No message'

                                                );

                                                ?>

                                            </span>

                                        </td>



                                        <!-- Action -->

                                        <td class="text-center">


                                            <?php if ($statusLower === 'pending'): ?>


                                                <form method="POST"
                                                      class="d-inline"
                                                      onsubmit="return confirm('Are you sure you want to cancel this appointment?');">


                                                    <input type="hidden"
                                                           name="action"
                                                           value="cancel">


                                                    <input type="hidden"
                                                           name="appointment_id"
                                                           value="<?php

                                                           echo htmlspecialchars(
                                                               $appointment['id']
                                                           );

                                                           ?>">


                                                    <button type="submit"
                                                            class="btn btn-sm btn-outline-danger">

                                                        <i class="fa-solid fa-xmark me-1"></i>

                                                        Cancel

                                                    </button>


                                                </form>


                                            <?php elseif ($statusLower === 'approved'): ?>


                                                <span class="text-success small fw-semibold">

                                                    <i class="fa-solid fa-check-circle me-1"></i>

                                                    Approved

                                                </span>


                                            <?php elseif ($statusLower === 'completed'): ?>


                                                <span class="text-primary small fw-semibold">

                                                    <i class="fa-solid fa-circle-check me-1"></i>

                                                    Completed

                                                </span>


                                            <?php elseif ($statusLower === 'rejected'): ?>


                                                <span class="text-danger small fw-semibold">

                                                    <i class="fa-solid fa-circle-xmark me-1"></i>

                                                    Rejected

                                                </span>


                                            <?php endif; ?>


                                        </td>


                                    </tr>


                                <?php endforeach; ?>


                                </tbody>

                            </table>

                        </div>


                    <?php else: ?>


                        <!-- =================================================
                             NO APPOINTMENTS
                        ================================================== -->

                        <div class="text-center py-5">


                            <div class="mb-3">

                                <i class="fa-solid fa-calendar-xmark text-muted"
                                   style="font-size: 4rem;"></i>

                            </div>


                            <h5 class="fw-bold">

                                You have no appointment bookings yet.

                            </h5>


                            <p class="text-muted mb-4">

                                Book an appointment with an approved hospital.

                            </p>


                            <a href="patient_book.php"
                               class="btn btn-emerald">

                                <i class="fa-solid fa-calendar-plus me-1"></i>

                                Book Your First Appointment

                            </a>


                        </div>


                    <?php endif; ?>


                </div>


            </div>


        </div>

    </div>

</div>



<?php

require_once __DIR__ . '/includes/footer.php';

?>