<?php
/**
 * COVID-19 Excel / CSV Data Exporter
 */
require_once __DIR__ . '/config/db.php';
requireAuth(['admin']);

$type = $_GET['type'] ?? 'reports';
$period = $_GET['period'] ?? 'all';

$from = $_GET['from'] ?? '';
$to   = $_GET['to'] ?? '';

/* -----------------------------
   Date Filter
----------------------------- */

if ($period === 'week') {
    $from = date('Y-m-d', strtotime('monday this week'));
    $to   = date('Y-m-d', strtotime('sunday this week'));
}

if ($period === 'month') {
    $from = date('Y-m-01');
    $to   = date('Y-m-t');
}

$dateFilterTests = '';
$dateFilterVaccines = '';

if (!empty($from) && !empty($to)) {
    $fromSafe = $conn->real_escape_string($from);
    $toSafe   = $conn->real_escape_string($to);

    $dateFilterTests =
        " WHERE sample_date BETWEEN '$fromSafe' AND '$toSafe'";

    $dateFilterVaccines =
        " WHERE dose_date BETWEEN '$fromSafe' AND '$toSafe'";
}

/* -----------------------------
   Excel File Headers
----------------------------- */

$filename =
    'COVID19_' .
    ucfirst($type) .
    '_' .
    date('Y-m-d') .
    '.xls';

header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");

/* -----------------------------
   Patients
----------------------------- */

if ($type === 'patients') {

    echo "<table border='1'>";
    echo "<tr>
            <th>#</th>
            <th>Full Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>CNIC</th>
            <th>Age</th>
            <th>Gender</th>
            <th>City</th>
            <th>Address</th>
            <th>Status</th>
            <th>Registered On</th>
          </tr>";

    if ($conn && !$conn->connect_error) {

        $res = $conn->query(
            "SELECT * FROM users
             WHERE role = 'patient'
             ORDER BY created_at DESC"
        );

        if ($res) {

            $i = 1;

            while ($row = $res->fetch_assoc()) {

                echo "<tr>";
                echo "<td>" . $i++ . "</td>";
                echo "<td>" . htmlspecialchars($row['name'] ?? '') . "</td>";
                echo "<td>" . htmlspecialchars($row['email'] ?? '') . "</td>";
                echo "<td>" . htmlspecialchars($row['phone'] ?? '') . "</td>";
                echo "<td>" . htmlspecialchars($row['cnic'] ?? '') . "</td>";
                echo "<td>" . htmlspecialchars($row['age'] ?? '') . "</td>";
                echo "<td>" . htmlspecialchars($row['gender'] ?? '') . "</td>";
                echo "<td>" . htmlspecialchars($row['city'] ?? '') . "</td>";
                echo "<td>" . htmlspecialchars($row['address'] ?? '') . "</td>";
                echo "<td>" . htmlspecialchars($row['status'] ?? 'Active') . "</td>";
                echo "<td>" . htmlspecialchars($row['created_at'] ?? '') . "</td>";
                echo "</tr>";
            }
        }
    }

    echo "</table>";
    exit;
}

/* -----------------------------
   Appointments
----------------------------- */

if ($type === 'appointments') {

    echo "<table border='1'>";

    echo "<tr>
            <th>Appointment ID</th>
            <th>Patient Name</th>
            <th>Phone</th>
            <th>Hospital</th>
            <th>Service</th>
            <th>Date</th>
            <th>Time</th>
            <th>Status</th>
            <th>Notes</th>
          </tr>";

    if ($conn && !$conn->connect_error) {

        $res = $conn->query(
            "SELECT * FROM appointments
             ORDER BY date DESC"
        );

        if ($res) {

            while ($row = $res->fetch_assoc()) {

                echo "<tr>";
                echo "<td>" . htmlspecialchars($row['id'] ?? '') . "</td>";
                echo "<td>" . htmlspecialchars($row['patient_name'] ?? '') . "</td>";
                echo "<td>" . htmlspecialchars($row['patient_phone'] ?? '') . "</td>";
                echo "<td>" . htmlspecialchars($row['hospital_name'] ?? '') . "</td>";
                echo "<td>" . htmlspecialchars($row['service'] ?? '') . "</td>";
                echo "<td>" . htmlspecialchars($row['date'] ?? '') . "</td>";
                echo "<td>" . htmlspecialchars($row['time'] ?? '') . "</td>";
                echo "<td>" . htmlspecialchars($row['status'] ?? '') . "</td>";
                echo "<td>" . htmlspecialchars($row['message'] ?? '') . "</td>";
                echo "</tr>";
            }
        }
    }

    echo "</table>";
    exit;
}

/* -----------------------------
   Comprehensive Reports
----------------------------- */

echo "<table border='1'>";

echo "<tr>
        <th colspan='10'>
            COVID-19 NATIONAL DIAGNOSTIC & IMMUNIZATION REPORT
        </th>
      </tr>";

echo "<tr>
        <td colspan='10'>
            Generated On: " . date('Y-m-d H:i:s') . "
        </td>
      </tr>";

if (!empty($from) && !empty($to)) {

    echo "<tr>
            <td colspan='10'>
                Report Period: $from to $to
            </td>
          </tr>";
}

/* -----------------------------
   COVID Tests
----------------------------- */

echo "<tr><th colspan='10'>COVID-19 DIAGNOSTIC TESTS</th></tr>";

echo "<tr>
        <th>Test ID</th>
        <th>Patient Name</th>
        <th>CNIC</th>
        <th>Hospital</th>
        <th>Test Type</th>
        <th>Sample Date</th>
        <th>Result Date</th>
        <th>Result</th>
        <th>CT Value</th>
        <th>Pathologist</th>
      </tr>";

if ($conn && !$conn->connect_error) {

    $res1 = $conn->query(
        "SELECT * FROM covid_tests
         $dateFilterTests
         ORDER BY sample_date DESC"
    );

    if ($res1) {

        while ($row = $res1->fetch_assoc()) {

            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['id'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($row['patient_name'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($row['patient_cnic'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($row['hospital_name'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($row['test_type'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($row['sample_date'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($row['result_date'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($row['result'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($row['ct_value'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($row['doctor'] ?? '') . "</td>";
            echo "</tr>";
        }
    }
}

/* -----------------------------
   Vaccination
----------------------------- */

echo "<tr><td colspan='10'></td></tr>";

echo "<tr>
        <th colspan='10'>COVID-19 VACCINATIONS ADMINISTERED</th>
      </tr>";

echo "<tr>
        <th>Record ID</th>
        <th>Beneficiary Name</th>
        <th>CNIC</th>
        <th>Hospital</th>
        <th>Vaccine</th>
        <th>Dose #</th>
        <th>Date Given</th>
        <th>Next Due Date</th>
        <th>Batch No</th>
        <th>Certificate No</th>
      </tr>";

if ($conn && !$conn->connect_error) {

    $res2 = $conn->query(
        "SELECT * FROM vaccination_doses
         $dateFilterVaccines
         ORDER BY dose_date DESC"
    );

    if ($res2) {

        while ($row = $res2->fetch_assoc()) {

            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['id'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($row['patient_name'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($row['patient_cnic'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($row['hospital_name'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($row['vaccine_name'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($row['dose_number'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($row['dose_date'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($row['next_dose_date'] ?? 'Completed') . "</td>";
            echo "<td>" . htmlspecialchars($row['batch_no'] ?? '') . "</td>";
            echo "<td>" . htmlspecialchars($row['certificate_no'] ?? '') . "</td>";
            echo "</tr>";
        }
    }
}

echo "</table>";
exit;