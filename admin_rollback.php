<?php
/**
 * Rollback Authorization
 * Admin can Approve / Reject deleted-record restoration requests
 */

require_once __DIR__ . '/config/db.php';
requireAuth(['admin']);

$pageTitle = "Rollback Authorization";

/*
|--------------------------------------------------------------------------
| Handle Approve / Reject
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? '';
    $requestId = trim($_POST['request_id'] ?? '');

    if ($requestId === '') {
        setFlash("Invalid rollback request.", "danger");
        header("Location: admin_rollback.php");
        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | Get Rollback Request
    |--------------------------------------------------------------------------
    */
    $stmt = $conn->prepare(
        "SELECT * FROM rollback_requests WHERE id = ? LIMIT 1"
    );

    $request = null;

    if ($stmt) {
        $stmt->bind_param("s", $requestId);
        $stmt->execute();

        $result = $stmt->get_result();

        if ($result) {
            $request = $result->fetch_assoc();
        }

        $stmt->close();
    }

    if (!$request) {
        setFlash("Rollback request not found.", "danger");
        header("Location: admin_rollback.php");
        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | APPROVE
    |--------------------------------------------------------------------------
    */
    if ($action === 'approve') {

        /*
        IMPORTANT:
        The database currently stores rollback requests, but it does
        not contain a backup/snapshot column for deleted records.

        Therefore we safely mark the request as Approved and record it
        in the audit log. Actual restoration requires the deleted record
        data to have been saved before deletion.
        */

        $update = $conn->prepare(
            "UPDATE rollback_requests
             SET status = 'Approved'
             WHERE id = ? AND status = 'Pending'"
        );

        if ($update) {

            $update->bind_param("s", $requestId);
            $update->execute();

            if ($update->affected_rows > 0) {

                logAudit(
                    $conn,
                    "System Administrator",
                    "Approved rollback request #$requestId - " .
                    $request['type'] .
                    " record #" .
                    $request['record_id']
                );

                setFlash(
                    "Rollback request approved successfully.",
                    "success"
                );

            } else {

                setFlash(
                    "This request has already been processed.",
                    "warning"
                );
            }

            $update->close();
        }

        header("Location: admin_rollback.php");
        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | REJECT
    |--------------------------------------------------------------------------
    */
    if ($action === 'reject') {

        $update = $conn->prepare(
            "UPDATE rollback_requests
             SET status = 'Rejected'
             WHERE id = ? AND status = 'Pending'"
        );

        if ($update) {

            $update->bind_param("s", $requestId);
            $update->execute();

            if ($update->affected_rows > 0) {

                logAudit(
                    $conn,
                    "System Administrator",
                    "Rejected rollback request #$requestId - " .
                    $request['type'] .
                    " record #" .
                    $request['record_id']
                );

                setFlash(
                    "Rollback request rejected.",
                    "info"
                );

            } else {

                setFlash(
                    "This request has already been processed.",
                    "warning"
                );
            }

            $update->close();
        }

        header("Location: admin_rollback.php");
        exit;
    }
}

/*
|--------------------------------------------------------------------------
| Fetch Rollback Requests
|--------------------------------------------------------------------------
*/

$requests = [];

if ($conn && !$conn->connect_error) {

    $res = $conn->query(
        "SELECT *
         FROM rollback_requests
         ORDER BY
         CASE
             WHEN status = 'Pending' THEN 1
             WHEN status = 'Approved' THEN 2
             ELSE 3
         END,
         created_at DESC"
    );

    if ($res) {

        while ($row = $res->fetch_assoc()) {
            $requests[] = $row;
        }
    }
}

/*
|--------------------------------------------------------------------------
| Statistics
|--------------------------------------------------------------------------
*/

$totalRequests = count($requests);
$pendingRequests = 0;
$approvedRequests = 0;
$rejectedRequests = 0;

foreach ($requests as $r) {

    if ($r['status'] === 'Pending') {
        $pendingRequests++;
    }

    if ($r['status'] === 'Approved') {
        $approvedRequests++;
    }

    if ($r['status'] === 'Rejected') {
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

            <!-- =====================================================
                 PAGE HEADER
            ====================================================== -->

            <div class="d-flex justify-content-between align-items-center mb-4">

                <div>
                    <h2 class="fw-bold text-dark fs-3 mb-1">
                        <i class="fa-solid fa-rotate-left text-success me-2"></i>
                        Rollback Authorization
                    </h2>

                    <p class="text-muted small mb-0">
                        Review deleted records and authorize their restoration.
                    </p>
                </div>

            </div>


            <!-- =====================================================
                 STAT CARDS
            ====================================================== -->

            <div class="row g-3 mb-4">

                <div class="col-sm-6 col-xl-3">

                    <div class="stat-card-custom">

                        <div class="stat-icon-box stat-icon-blue">
                            <i class="fa-solid fa-list-check"></i>
                        </div>

                        <div>
                            <div class="stat-label-text">
                                Total Requests
                            </div>

                            <div class="stat-number-val">
                                <?php echo number_format($totalRequests); ?>
                            </div>
                        </div>

                    </div>

                </div>


                <div class="col-sm-6 col-xl-3">

                    <div class="stat-card-custom">

                        <div class="stat-icon-box stat-icon-amber">
                            <i class="fa-solid fa-hourglass-half"></i>
                        </div>

                        <div>
                            <div class="stat-label-text">
                                Pending
                            </div>

                            <div class="stat-number-val">
                                <?php echo number_format($pendingRequests); ?>
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
                                Approved
                            </div>

                            <div class="stat-number-val">
                                <?php echo number_format($approvedRequests); ?>
                            </div>
                        </div>

                    </div>

                </div>


                <div class="col-sm-6 col-xl-3">

                    <div class="stat-card-custom">

                        <div class="stat-icon-box stat-icon-purple">
                            <i class="fa-solid fa-circle-xmark"></i>
                        </div>

                        <div>
                            <div class="stat-label-text">
                                Rejected
                            </div>

                            <div class="stat-number-val">
                                <?php echo number_format($rejectedRequests); ?>
                            </div>
                        </div>

                    </div>

                </div>

            </div>


            <!-- =====================================================
                 ROLLBACK TABLE
            ====================================================== -->

            <div class="panel-card-custom">

                <div class="panel-header-custom">

                    <h3 class="panel-title-custom">

                        <i class="fa-solid fa-shield-halved text-success"></i>

                        Rollback Requests

                    </h3>

                    <span class="badge bg-light text-secondary border">
                        Admin Authorization
                    </span>

                </div>


                <div class="table-responsive">

                    <table class="table table-custom align-middle">

                        <thead>

                            <tr>

                                <th>#</th>

                                <th>Record Type</th>

                                <th>Record ID</th>

                                <th>Deleted By</th>

                                <th>Deleted On</th>

                                <th>Reason</th>

                                <th>Status</th>

                                <th class="text-end">
                                    Action
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            <?php if (!empty($requests)): ?>

                                <?php foreach ($requests as $index => $r): ?>

                                    <tr>

                                        <td class="text-muted fw-bold">
                                            <?php echo $index + 1; ?>
                                        </td>


                                        <!-- TYPE -->

                                        <td>

                                            <?php

                                            $type = strtolower($r['type']);

                                            if ($type === 'patient') {
                                                $icon = 'fa-user';
                                                $iconClass = 'text-success';
                                            } elseif ($type === 'appointment') {
                                                $icon = 'fa-calendar-check';
                                                $iconClass = 'text-primary';
                                            } elseif ($type === 'test') {
                                                $icon = 'fa-vial-virus';
                                                $iconClass = 'text-warning';
                                            } elseif ($type === 'vaccine') {
                                                $icon = 'fa-syringe';
                                                $iconClass = 'text-purple';
                                            } else {
                                                $icon = 'fa-file';
                                                $iconClass = 'text-secondary';
                                            }

                                            ?>

                                            <div class="d-flex align-items-center gap-2">

                                                <i class="fa-solid <?php echo $icon; ?> <?php echo $iconClass; ?>"></i>

                                                <span class="fw-bold text-dark">
                                                    <?php echo htmlspecialchars($r['type']); ?>
                                                </span>

                                            </div>

                                        </td>


                                        <!-- RECORD ID -->

                                        <td>

                                            <span class="font-monospace small text-muted">
                                                <?php echo htmlspecialchars($r['record_id']); ?>
                                            </span>

                                        </td>


                                        <!-- DELETED BY -->

                                        <td>

                                            <span class="small fw-semibold text-dark">
                                                <?php echo htmlspecialchars($r['deleted_by']); ?>
                                            </span>

                                        </td>


                                        <!-- DATE -->

                                        <td class="text-muted">

                                            <?php echo htmlspecialchars($r['deleted_on']); ?>

                                        </td>


                                        <!-- REASON -->

                                        <td style="min-width:220px;">

                                            <span class="small text-muted">

                                                <?php echo htmlspecialchars($r['reason']); ?>

                                            </span>

                                        </td>


                                        <!-- STATUS -->

                                        <td>

                                            <?php

                                            if ($r['status'] === 'Approved') {

                                                $statusClass = 'badge-status-approved';

                                            } elseif ($r['status'] === 'Rejected') {

                                                $statusClass = 'badge-status-rejected';

                                            } else {

                                                $statusClass = 'badge-status-pending';

                                            }

                                            ?>

                                            <span class="badge-status <?php echo $statusClass; ?>">

                                                <?php echo htmlspecialchars($r['status']); ?>

                                            </span>

                                        </td>


                                        <!-- ACTION -->

                                        <td class="text-end">

                                            <?php if ($r['status'] === 'Pending'): ?>

                                                <div class="d-flex justify-content-end gap-1">

                                                    <!-- APPROVE -->

                                                    <form method="POST" class="d-inline">

                                                        <input
                                                            type="hidden"
                                                            name="action"
                                                            value="approve"
                                                        >

                                                        <input
                                                            type="hidden"
                                                            name="request_id"
                                                            value="<?php echo htmlspecialchars($r['id']); ?>"
                                                        >

                                                        <button
                                                            type="submit"
                                                            class="btn btn-sm btn-success"
                                                            title="Approve Rollback"
                                                            onclick="return confirm('Approve this rollback request?');"
                                                        >

                                                            <i class="fa-solid fa-check me-1"></i>
                                                            Approve

                                                        </button>

                                                    </form>


                                                    <!-- REJECT -->

                                                    <form method="POST" class="d-inline">

                                                        <input
                                                            type="hidden"
                                                            name="action"
                                                            value="reject"
                                                        >

                                                        <input
                                                            type="hidden"
                                                            name="request_id"
                                                            value="<?php echo htmlspecialchars($r['id']); ?>"
                                                        >

                                                        <button
                                                            type="submit"
                                                            class="btn btn-sm btn-outline-danger"
                                                            title="Reject Rollback"
                                                            onclick="return confirm('Reject this rollback request?');"
                                                        >

                                                            <i class="fa-solid fa-xmark me-1"></i>
                                                            Reject

                                                        </button>

                                                    </form>

                                                </div>

                                            <?php elseif ($r['status'] === 'Approved'): ?>

                                                <span class="text-success small fw-bold">

                                                    <i class="fa-solid fa-circle-check me-1"></i>
                                                    Authorized

                                                </span>

                                            <?php else: ?>

                                                <span class="text-danger small fw-bold">

                                                    <i class="fa-solid fa-circle-xmark me-1"></i>
                                                    Rejected

                                                </span>

                                            <?php endif; ?>

                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                            <?php else: ?>

                                <tr>

                                    <td
                                        colspan="8"
                                        class="text-center py-5 text-muted"
                                    >

                                        <i class="fa-solid fa-inbox fs-2 d-block mb-2"></i>

                                        No rollback requests found.

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