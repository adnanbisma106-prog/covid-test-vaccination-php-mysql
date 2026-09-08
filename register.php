<?php
/**
 * Patient and Hospital Registration Page
 */
require_once __DIR__ . '/config/db.php';

$type = $_GET['type'] ?? 'patient';
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $regRole = $_POST['reg_role'] ?? 'patient';

    if ($regRole === 'patient') {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $cnic = trim($_POST['cnic'] ?? '');
        $age = intval($_POST['age'] ?? 25);
        $gender = $_POST['gender'] ?? 'Male';
        $city = trim($_POST['city'] ?? 'Lahore');
        $address = trim($_POST['address'] ?? '');

        if (!empty($name) && !empty($email) && !empty($password)) {
            $id = 'pat_' . time();
            $stmt = $conn->prepare("INSERT INTO users (id, name, email, password, role, phone, cnic, age, gender, city, address, status) VALUES (?, ?, ?, ?, 'patient', ?, ?, ?, ?, ?, ?, 'Active')");
            if ($stmt) {
                $stmt->bind_param("ssssssisss", $id, $name, $email, $password, $phone, $cnic, $age, $gender, $city, $address);
                if ($stmt->execute()) {
                    logAudit($conn, $name, "New Patient Registered: $name");
                    $_SESSION['user_id'] = $id;
                    $_SESSION['user_name'] = $name;
                    $_SESSION['user_email'] = $email;
                    $_SESSION['user_role'] = 'patient';
                    setFlash("Account created successfully! Welcome to the portal.", "success");
                    header("Location: patient_dashboard.php");
                    exit;
                } else {
                    $error = "Email already registered or database error.";
                }
            }
        } else {
            $error = "Please fill all required fields.";
        }
    } elseif ($regRole === 'hospital') {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $license = trim($_POST['license'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $city = trim($_POST['city'] ?? 'Lahore');
        $location = trim($_POST['location'] ?? '');
        $services = $_POST['services'] ?? 'Both';
        $capacity = intval($_POST['capacity'] ?? 50);

        if (!empty($name) && !empty($email) && !empty($password) && !empty($license)) {
            $id = 'hosp_' . time();
            $stmt1 = $conn->prepare("INSERT INTO users (id, name, email, password, role, phone, city, address, status) VALUES (?, ?, ?, ?, 'hospital', ?, ?, ?, 'Active')");
            $stmt2 = $conn->prepare("INSERT INTO hospitals (id, user_id, name, email, password, license_no, phone, city, location, services, capacity, approved) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0)");

            if ($stmt1 && $stmt2) {
                $stmt1->bind_param("sssssss", $id, $name, $email, $password, $phone, $city, $location);
                $stmt2->bind_param("ssssssssssi", $id, $id, $name, $email, $password, $license, $phone, $city, $location, $services, $capacity);

                if ($stmt1->execute() && $stmt2->execute()) {
                    logAudit($conn, "System", "Hospital Registration Submitted: $name ($license)");
                    header("Location: index.php?msg=Hospital registration submitted! Awaiting Administrator approval.&type=info");
                    exit;
                } else {
                    $error = "Hospital email already registered.";
                }
            }
        } else {
            $error = "Please fill all required hospital details.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register | COVID-19 Test & Vaccination System</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light py-5">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-lg-7 col-md-9">
        <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5">
          <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
            <div>
              <h2 class="fw-bold text-dark fs-4 mb-0"><?php echo $type === 'hospital' ? '🏥 Hospital Registration' : '👤 Patient Registration'; ?></h2>
              <span class="small text-muted">Create your COVID-19 ORS Portal Account</span>
            </div>
            <a href="index.php" class="btn btn-outline-secondary btn-sm"><i class="fa-solid fa-arrow-left me-1"></i> Back to Login</a>
          </div>

          <?php if ($error): ?>
            <div class="alert alert-danger py-2 small mb-4"><?php echo htmlspecialchars($error); ?></div>
          <?php endif; ?>

          <?php if ($type === 'patient'): ?>
            <!-- Patient Form -->
            <form method="POST" action="register.php?type=patient">
              <input type="hidden" name="reg_role" value="patient">
              <div class="row g-3 mb-3">
                <div class="col-md-6">
                  <label class="form-label small fw-bold">Full Name *</label>
                  <input type="text" name="name" class="form-control" placeholder="e.g. Ali Khan" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label small fw-bold">CNIC / ID *</label>
                  <input type="text" name="cnic" class="form-control" placeholder="42101-1234567-1" required>
                </div>
              </div>

              <div class="row g-3 mb-3">
                <div class="col-md-6">
                  <label class="form-label small fw-bold">Email Address *</label>
                  <input type="email" name="email" class="form-control" placeholder="ali@gmail.com" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label small fw-bold">Password *</label>
                  <input type="password" name="password" class="form-control" placeholder="Create Password" required>
                </div>
              </div>

              <div class="row g-3 mb-3">
                <div class="col-md-4">
                  <label class="form-label small fw-bold">Phone Number *</label>
                  <input type="tel" name="phone" class="form-control" placeholder="03001234567" required>
                </div>
                <div class="col-md-4">
                  <label class="form-label small fw-bold">Age</label>
                  <input type="number" name="age" class="form-control" value="28">
                </div>
                <div class="col-md-4">
                  <label class="form-label small fw-bold">Gender</label>
                  <select name="gender" class="form-select">
                    <option value="Male">Male</option>
                    <option value="Female">Female</option>
                    <option value="Other">Other</option>
                  </select>
                </div>
              </div>

              <div class="row g-3 mb-4">
                <div class="col-md-4">
                  <label class="form-label small fw-bold">City</label>
                  <input type="text" name="city" class="form-control" value="Lahore">
                </div>
                <div class="col-md-8">
                  <label class="form-label small fw-bold">Residential Address</label>
                  <input type="text" name="address" class="form-control" placeholder="House/Street/Area">
                </div>
              </div>

              <button type="submit" class="btn btn-emerald w-100 py-2 fs-6">
                <i class="fa-solid fa-user-plus me-1"></i> Complete Patient Registration
              </button>
            </form>

          <?php else: ?>
            <!-- Hospital Form -->
            <form method="POST" action="register.php?type=hospital">
              <input type="hidden" name="reg_role" value="hospital">
              <div class="mb-3">
                <label class="form-label small fw-bold">Hospital Name *</label>
                <input type="text" name="name" class="form-control" placeholder="e.g. City Care Hospital" required>
              </div>

              <div class="row g-3 mb-3">
                <div class="col-md-6">
                  <label class="form-label small fw-bold">Official Email *</label>
                  <input type="email" name="email" class="form-control" placeholder="contact@hospital.com" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label small fw-bold">Password *</label>
                  <input type="password" name="password" class="form-control" placeholder="Password" required>
                </div>
              </div>

              <div class="row g-3 mb-3">
                <div class="col-md-6">
                  <label class="form-label small fw-bold">License / Reg Number *</label>
                  <input type="text" name="license" class="form-control" placeholder="REG-HOSP-2025-XXXX" required>
                </div>
                <div class="col-md-6">
                  <label class="form-label small fw-bold">Emergency Phone *</label>
                  <input type="tel" name="phone" class="form-control" placeholder="03001234500" required>
                </div>
              </div>

              <div class="row g-3 mb-3">
                <div class="col-md-4">
                  <label class="form-label small fw-bold">City *</label>
                  <input type="text" name="city" class="form-control" value="Lahore" required>
                </div>
                <div class="col-md-4">
                  <label class="form-label small fw-bold">Services Provided</label>
                  <select name="services" class="form-select">
                    <option value="Both">Both (Test &amp; Vaccine)</option>
                    <option value="Covid Test">Covid Test Only</option>
                    <option value="Vaccination">Vaccination Only</option>
                  </select>
                </div>
                <div class="col-md-4">
                  <label class="form-label small fw-bold">Daily Capacity</label>
                  <input type="number" name="capacity" class="form-control" value="100">
                </div>
              </div>

              <div class="mb-4">
                <label class="form-label small fw-bold">Location Address</label>
                <input type="text" name="location" class="form-control" placeholder="Hospital Sector / Road">
              </div>

              <div class="alert alert-warning py-2 small mb-4">
                <i class="fa-solid fa-circle-info me-1"></i> Hospital registrations require verification and approval by the System Administrator.
              </div>

              <button type="submit" class="btn btn-primary w-100 py-2 fs-6">
                <i class="fa-solid fa-hospital-user me-1"></i> Submit Hospital for Approval
              </button>
            </form>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
