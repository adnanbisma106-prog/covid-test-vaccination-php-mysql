<?php
/**
 * Top Navbar Component
 */
?>

<header class="top-navbar" style="overflow: visible; position: relative; z-index: 1000;">

  <!-- LEFT SIDE -->
  <div class="d-flex align-items-center gap-3">

    <button
      id="sidebarToggleBtn"
      class="btn btn-light d-lg-none py-1 px-2 border"
      type="button"
    >
      <i class="fa-solid fa-bars"></i>
    </button>

    <div class="top-navbar-title">
      <i class="fa-solid fa-shield-virus text-success"></i>
      <span>COVID Test &amp; Vaccination System</span>
    </div>

  </div>


  <!-- RIGHT SIDE -->
  <div
    class="d-flex align-items-center gap-3"
    style="overflow: visible; position: relative; z-index: 1001;"
  >

    <!-- =========================
         DEMO ROLE SWITCHER
         ========================= -->
    <div
      class="d-flex align-items-center gap-2"
      style="white-space: nowrap;"
    >

      <span class="small text-muted fw-bold">
        Switch Demo:
      </span>

      <form
        method="POST"
        action="login.php"
        class="m-0"
      >

        <select
          name="quick_role"
          class="form-select form-select-sm fw-semibold"
          style="min-width: 250px; cursor: pointer;"
          onchange="this.form.submit()"
        >

          <option value="">
            Select Role...
          </option>

          <option
            value="admin"
            <?php echo (($currentUser['role'] ?? '') === 'admin') ? 'selected' : ''; ?>
          >
            🛡️ Admin (admin@covid.gov)
          </option>

          <option
            value="hospital"
            <?php echo (($currentUser['role'] ?? '') === 'hospital') ? 'selected' : ''; ?>
          >
            🏥 Hospital (City Hospital)
          </option>
<option value="patient_ali"
    <?php echo (($currentUser['id'] ?? '') === 'pat_ali') ? 'selected' : ''; ?>>
    👤 Patient (Ali Khan)
</option>

<option value="patient_ayesha"
    <?php echo (($currentUser['name'] ?? '') === 'Ayesha Malik') ? 'selected' : ''; ?>>
    👩 Patient (Ayesha Malik)
</option>

        </select>

      </form>

    </div>


    <!-- =========================
         USER PROFILE
         ========================= -->
    <div class="dropdown">

      <div
        class="user-badge-pill dropdown-toggle"
        role="button"
        data-bs-toggle="dropdown"
        aria-expanded="false"
        style="cursor: pointer;"
      >

        <img
          class="user-avatar-sm"
          src="<?php echo htmlspecialchars(
            $currentUser['avatar']
            ?? 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=120&h=120&fit=crop'
          ); ?>"
          alt="Avatar"
        >

        <span class="small fw-bold text-dark">
          <?php echo htmlspecialchars($currentUser['name'] ?? 'User'); ?>
        </span>

      </div>


      <ul class="dropdown-menu dropdown-menu-end shadow-sm border">

        <li>
          <h6 class="dropdown-header">
            <?php echo strtoupper($currentUser['role'] ?? 'GUEST'); ?>
            ACCOUNT
          </h6>
        </li>

        <li>
          <a
            class="dropdown-item small"
            href="<?php
              echo ($currentUser['role'] ?? '') === 'hospital'
                ? 'hospital_profile.php'
                : (
                    ($currentUser['role'] ?? '') === 'patient'
                    ? 'patient_profile.php'
                    : 'admin_dashboard.php'
                  );
            ?>"
          >
            <i class="fa-solid fa-user me-2 text-muted"></i>
            Profile Settings
          </a>
        </li>

        <li>
          <hr class="dropdown-divider">
        </li>

        <li>
          <a
            class="dropdown-item small text-danger"
            href="logout.php"
          >
            <i class="fa-solid fa-power-off me-2"></i>
            Sign Out
          </a>
        </li>

      </ul>

    </div>

  </div>

</header>