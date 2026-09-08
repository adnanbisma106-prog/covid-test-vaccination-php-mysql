<?php
/**
 * Hospital Profile & Capacity Management
 */
require_once __DIR__ . '/config/db.php';
requireAuth(['hospital']);

$pageTitle = "Hospital Profile";
$hospId = $currentUser['id'] ?? 'hosp_city';

// Handle Profile Update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $services = $_POST['services'] ?? 'Both';
    $capacity = intval($_POST['capacity'] ?? 100);

    $stmt = $conn->prepare("UPDATE hospitals SET name = ?, phone = ?, city = ?, location = ?, services = ?, capacity = ? WHERE id = ? OR email = ?");
    if ($stmt) {
        $stmt->bind_param("sssssiss", $name, $phone, $city, $location, $services, $capacity, $hospId, $currentUser['email']);
        $stmt->execute();
        logAudit($conn, $name, "Updated hospital profile settings");
        $_SESSION['user_name'] = $name;
        setFlash("Hospital profile updated successfully!", "success");
    }
    header("Location: hospital_profile.php");
    exit;
}

// Fetch Profile
$hosp = [
    'name' => $currentUser['name'] ?? 'City Hospital',
    'email' => $currentUser['email'] ?? 'city@hospital.com',
    'phone' => '03001234500',
    'license_no' => 'REG-HOSP-2024-8891',
    'city' => 'Lahore',
    'location' => 'Downtown Health District, City Center',
    'services' => 'Both',
    'capacity' => 100
];

if ($conn && !$conn->connect_error) {
    $stmt = $conn->prepare("SELECT * FROM hospitals WHERE email = ? LIMIT 1");
    if ($stmt) {
        $stmt->bind_param("s", $currentUser['email']);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        if ($res) $hosp = $res;
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="app-container">
  <?php require_once __DIR__ . '/includes/sidebar.php'; ?>

  <div class="main-content">
    <?php require_once __DIR__ . '/includes/topbar.php'; ?>

    <div class="page-body">
      <div class="panel-card-custom" style="max-width: 720px;">
        <div class="panel-header-custom">
          <h2 class="panel-title-custom">
            <i class="fa-solid fa-hospital text-success"></i> Hospital Profile &amp; Capacity
          </h2>
        </div>

        <form method="POST" class="p-4">
          <div class="mb-3">
            <label class="form-label small fw-bold">Hospital Name</label>
            <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($hosp['name']); ?>" required>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label small fw-bold">Official Email</label>
              <input type="email" class="form-control bg-light" value="<?php echo htmlspecialchars($hosp['email']); ?>" readonly>
            </div>
            <div class="col-md-6">
              <label class="form-label small fw-bold">License / Reg Number</label>
              <input type="text" class="form-control bg-light font-monospace" value="<?php echo htmlspecialchars($hosp['license_no']); ?>" readonly>
            </div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-4">
              <label class="form-label small fw-bold">Emergency Phone</label>
              <input type="tel" name="phone" class="form-control" value="<?php echo htmlspecialchars($hosp['phone']); ?>" required>
            </div>
            <div class="col-md-4">
              <label class="form-label small fw-bold">City</label>
              <input type="text" name="city" class="form-control" value="<?php echo htmlspecialchars($hosp['city']); ?>" required>
            </div>
            <div class="col-md-4">
              <label class="form-label small fw-bold">Daily Capacity</label>
              <input type="number" name="capacity" class="form-control" value="<?php echo $hosp['capacity']; ?>" min="10" required>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label small fw-bold">Services Provided</label>
            <select name="services" class="form-select">
              <option value="Both" <?php echo ($hosp['services'] ?? '') === 'Both' ? 'selected' : ''; ?>>Both (Test &amp; Vaccine)</option>
              <option value="Covid Test" <?php echo ($hosp['services'] ?? '') === 'Covid Test' ? 'selected' : ''; ?>>Covid Test Only</option>
              <option value="Vaccination" <?php echo ($hosp['services'] ?? '') === 'Vaccination' ? 'selected' : ''; ?>>Vaccination Only</option>
            </select>
          </div>

          <div class="mb-4">
            <label class="form-label small fw-bold">Location Address</label>
            <input type="text" name="location" class="form-control" value="<?php echo htmlspecialchars($hosp['location']); ?>" required>
          </div>

          <button type="submit" class="btn btn-emerald">
            <i class="fa-solid fa-floppy-disk me-1"></i> Save Hospital Profile
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
