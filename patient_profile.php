<?php
/**
 * Patient Profile & Medical Information
 */

require_once __DIR__ . '/config/db.php';
requireAuth(['patient']);

/* Get currently logged-in patient */
$currentUser = getLoggedInUser();

$pageTitle = "My Profile";
$patId = $currentUser['id'] ?? '';

/* Safety check */
if (empty($patId)) {
    setFlash("Patient session not found. Please login again.", "danger");
    header("Location: login.php");
    exit;
}

/* =========================
   Handle Profile Update
   ========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $age = intval($_POST['age'] ?? 0);
    $gender = $_POST['gender'] ?? 'Male';
    $city = trim($_POST['city'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $bloodGroup = trim($_POST['blood_group'] ?? '');
    $allergies = trim($_POST['allergies'] ?? 'None');
    $emergency = trim($_POST['emergency_contact'] ?? '');

    /* Validate required fields */
    if ($name === '' || $phone === '') {

        setFlash("Name and phone number are required.", "danger");

    } elseif (!in_array($gender, ['Male', 'Female', 'Other'], true)) {

        setFlash("Invalid gender selected.", "danger");

    } else {

        $stmt = $conn->prepare("
            UPDATE users
            SET
                name = ?,
                phone = ?,
                age = ?,
                gender = ?,
                city = ?,
                address = ?,
                blood_group = ?,
                allergies = ?,
                emergency_contact = ?
            WHERE id = ?
            AND role = 'patient'
        ");

        if ($stmt) {

            $stmt->bind_param(
                "ssisssssss",
                $name,
                $phone,
                $age,
                $gender,
                $city,
                $address,
                $bloodGroup,
                $allergies,
                $emergency,
                $patId
            );

            if ($stmt->execute()) {

                /* Update session name */
                $_SESSION['user_name'] = $name;

                /* Audit log */
                logAudit(
                    $conn,
                    $name,
                    "Updated personal medical profile"
                );

                setFlash(
                    "Profile updated successfully!",
                    "success"
                );

            } else {

                setFlash(
                    "Profile update failed. Please try again.",
                    "danger"
                );
            }

            $stmt->close();

        } else {

            setFlash(
                "Database error while updating profile.",
                "danger"
            );
        }
    }

    header("Location: patient_profile.php");
    exit;
}


/* =========================
   Fetch Patient Profile
   ========================= */

$pat = [
    'name' => '',
    'email' => '',
    'phone' => '',
    'cnic' => '',
    'age' => '',
    'gender' => 'Male',
    'city' => '',
    'address' => '',
    'blood_group' => '',
    'allergies' => 'None',
    'emergency_contact' => ''
];

if ($conn && !$conn->connect_error) {

    $stmt = $conn->prepare("
        SELECT
            id,
            name,
            email,
            phone,
            cnic,
            age,
            gender,
            city,
            address,
            blood_group,
            allergies,
            emergency_contact
        FROM users
        WHERE id = ?
        AND role = 'patient'
        LIMIT 1
    ");

    if ($stmt) {

        $stmt->bind_param("s", $patId);
        $stmt->execute();

        $result = $stmt->get_result();
        $res = $result->fetch_assoc();

        if ($res) {
            $pat = array_merge($pat, $res);
        }

        $stmt->close();
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<div class="app-container">

    <?php require_once __DIR__ . '/includes/sidebar.php'; ?>

    <div class="main-content">

        <?php require_once __DIR__ . '/includes/topbar.php'; ?>

        <div class="page-body">

            <div class="panel-card-custom" style="max-width: 760px;">

                <div class="panel-header-custom">

                    <h2 class="panel-title-custom">

                        <i class="fa-solid fa-user text-success"></i>

                        Patient Profile &amp; Medical Details

                    </h2>

                </div>


                <form method="POST" class="p-4">

                    <!-- Full Name + CNIC -->
                    <div class="row g-3 mb-3">

                        <div class="col-md-6">

                            <label class="form-label small fw-bold">
                                Full Name
                            </label>

                            <input
                                type="text"
                                name="name"
                                class="form-control fw-semibold"
                                value="<?php echo htmlspecialchars($pat['name'] ?? ''); ?>"
                                required
                            >

                        </div>


                        <div class="col-md-6">

                            <label class="form-label small fw-bold">
                                National ID / CNIC
                            </label>

                            <input
                                type="text"
                                class="form-control bg-light font-monospace"
                                value="<?php echo htmlspecialchars($pat['cnic'] ?? ''); ?>"
                                readonly
                            >

                        </div>

                    </div>


                    <!-- Email + Phone -->
                    <div class="row g-3 mb-3">

                        <div class="col-md-6">

                            <label class="form-label small fw-bold">
                                Email Address
                            </label>

                            <input
                                type="email"
                                class="form-control bg-light"
                                value="<?php echo htmlspecialchars($pat['email'] ?? ''); ?>"
                                readonly
                            >

                        </div>


                        <div class="col-md-6">

                            <label class="form-label small fw-bold">
                                Mobile Phone
                            </label>

                            <input
                                type="tel"
                                name="phone"
                                class="form-control font-monospace"
                                value="<?php echo htmlspecialchars($pat['phone'] ?? ''); ?>"
                                required
                            >

                        </div>

                    </div>


                    <!-- Age + Gender + Blood Group -->
                    <div class="row g-3 mb-3">

                        <div class="col-md-4">

                            <label class="form-label small fw-bold">
                                Age
                            </label>

                            <input
                                type="number"
                                name="age"
                                class="form-control"
                                min="1"
                                max="120"
                                value="<?php echo htmlspecialchars($pat['age'] ?? ''); ?>"
                            >

                        </div>


                        <div class="col-md-4">

                            <label class="form-label small fw-bold">
                                Gender
                            </label>

                            <select name="gender" class="form-select">

                                <option
                                    value="Male"
                                    <?php echo (($pat['gender'] ?? '') === 'Male') ? 'selected' : ''; ?>
                                >
                                    Male
                                </option>

                                <option
                                    value="Female"
                                    <?php echo (($pat['gender'] ?? '') === 'Female') ? 'selected' : ''; ?>
                                >
                                    Female
                                </option>

                                <option
                                    value="Other"
                                    <?php echo (($pat['gender'] ?? '') === 'Other') ? 'selected' : ''; ?>
                                >
                                    Other
                                </option>

                            </select>

                        </div>


                        <div class="col-md-4">

                            <label class="form-label small fw-bold">
                                Blood Group
                            </label>

                            <input
                                type="text"
                                name="blood_group"
                                class="form-control"
                                value="<?php echo htmlspecialchars($pat['blood_group'] ?? ''); ?>"
                                placeholder="e.g. B+"
                            >

                        </div>

                    </div>


                    <!-- City + Allergies -->
                    <div class="row g-3 mb-3">

                        <div class="col-md-6">

                            <label class="form-label small fw-bold">
                                City
                            </label>

                            <input
                                type="text"
                                name="city"
                                class="form-control"
                                value="<?php echo htmlspecialchars($pat['city'] ?? ''); ?>"
                                placeholder="Enter your city"
                            >

                        </div>


                        <div class="col-md-6">

                            <label class="form-label small fw-bold">
                                Known Medical / Drug Allergies
                            </label>

                            <input
                                type="text"
                                name="allergies"
                                class="form-control"
                                value="<?php echo htmlspecialchars($pat['allergies'] ?? 'None'); ?>"
                                placeholder="e.g. None, Penicillin"
                            >

                        </div>

                    </div>


                    <!-- Address -->
                    <div class="mb-3">

                        <label class="form-label small fw-bold">
                            Residential Address
                        </label>

                        <input
                            type="text"
                            name="address"
                            class="form-control"
                            value="<?php echo htmlspecialchars($pat['address'] ?? ''); ?>"
                            placeholder="Enter residential address"
                        >

                    </div>


                    <!-- Emergency Contact -->
                    <div class="mb-4">

                        <label class="form-label small fw-bold">
                            Emergency Contact Information
                        </label>

                        <input
                            type="text"
                            name="emergency_contact"
                            class="form-control"
                            value="<?php echo htmlspecialchars($pat['emergency_contact'] ?? ''); ?>"
                            placeholder="Contact person name and phone number"
                        >

                    </div>


                    <!-- Save Button -->
                    <button
                        type="submit"
                        class="btn btn-emerald"
                    >

                        <i class="fa-solid fa-floppy-disk me-1"></i>

                        Save Changes

                    </button>

                </form>

            </div>

        </div>

    </div>

</div>


<?php require_once __DIR__ . '/includes/footer.php'; ?>