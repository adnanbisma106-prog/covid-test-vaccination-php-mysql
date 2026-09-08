<?php
/**
 * Role-Based Sidebar Navigation Component (Matching Screenshots)
 */
$role = $currentUser['role'] ?? 'patient';
$activePage = basename($_SERVER['PHP_SELF']);
?>
<aside id="mainSidebar" class="sidebar">
  <div class="sidebar-header">
    <div class="sidebar-logo-icon">
      <i class="fa-solid fa-shield-virus"></i>
    </div>
    <div>
      <div class="sidebar-brand-text">COVID Test &amp; Vaccination</div>
      <span class="sidebar-panel-tag"><?php echo strtoupper($role); ?> PANEL</span>
    </div>
  </div>

  <nav class="sidebar-nav">
    <?php if ($role === 'admin'): ?>
      <!-- ADMIN NAVIGATION (Matching Screenshot 2, 3, 7, 8, 9) -->
      <div class="sidebar-section-title">ADMIN PANEL</div>
      <a href="admin_dashboard.php" class="sidebar-nav-link <?php echo $activePage === 'admin_dashboard.php' ? 'active' : ''; ?>">
        <i class="fa-solid fa-chart-pie"></i>
        <span>Dashboard</span>
      </a>
      <a href="admin_patients.php" class="sidebar-nav-link <?php echo $activePage === 'admin_patients.php' ? 'active' : ''; ?>">
        <i class="fa-solid fa-users"></i>
        <span>Patients</span>
      </a>
      <a href="admin_hospitals.php" class="sidebar-nav-link <?php echo $activePage === 'admin_hospitals.php' ? 'active' : ''; ?>">
        <i class="fa-solid fa-hospital"></i>
        <span>Hospitals</span>
      </a>
      <a href="admin_vaccines.php" class="sidebar-nav-link <?php echo $activePage === 'admin_vaccines.php' ? 'active' : ''; ?>">
        <i class="fa-solid fa-syringe"></i>
        <span>Vaccines</span>
      </a>
      <a href="admin_appointments.php" class="sidebar-nav-link <?php echo $activePage === 'admin_appointments.php' ? 'active' : ''; ?>">
        <i class="fa-solid fa-calendar-check"></i>
        <span>Appointments</span>
      </a>
      <a href="admin_reports.php" class="sidebar-nav-link <?php echo $activePage === 'admin_reports.php' ? 'active' : ''; ?>">
        <i class="fa-solid fa-file-waveform"></i>
        <span>Covid Reports</span>
      </a>
      <a href="admin_rollback.php" class="sidebar-nav-link <?php echo $activePage === 'admin_rollback.php' ? 'active' : ''; ?>">
        <i class="fa-solid fa-clock-rotate-left"></i>
        <span>Rollback Requests</span>
      </a>
      <a href="admin_audit.php" class="sidebar-nav-link <?php echo $activePage === 'admin_audit.php' ? 'active' : ''; ?>">
        <i class="fa-solid fa-list-check"></i>
        <span>Audit Logs</span>
      </a>

    <?php elseif ($role === 'hospital'): ?>
      <!-- HOSPITAL NAVIGATION (Matching Screenshot 4) -->
      <div class="sidebar-section-title">HOSPITAL PANEL</div>
      <a href="hospital_dashboard.php" class="sidebar-nav-link <?php echo $activePage === 'hospital_dashboard.php' ? 'active' : ''; ?>">
        <i class="fa-solid fa-chart-pie"></i>
        <span>Dashboard</span>
      </a>
      <a href="hospital_appointments.php" class="sidebar-nav-link <?php echo $activePage === 'hospital_appointments.php' ? 'active' : ''; ?>">
        <i class="fa-solid fa-calendar-days"></i>
        <span>Appointments</span>
      </a>
      <a href="hospital_patients.php" class="sidebar-nav-link <?php echo $activePage === 'hospital_patients.php' ? 'active' : ''; ?>">
        <i class="fa-solid fa-users"></i>
        <span>Patients</span>
      </a>
      <a href="hospital_tests.php" class="sidebar-nav-link <?php echo $activePage === 'hospital_tests.php' ? 'active' : ''; ?>">
        <i class="fa-solid fa-vial-virus"></i>
        <span>Covid Tests</span>
      </a>
      <a href="hospital_vaccinations.php" class="sidebar-nav-link <?php echo $activePage === 'hospital_vaccinations.php' ? 'active' : ''; ?>">
        <i class="fa-solid fa-syringe"></i>
        <span>Vaccinations</span>
      </a>
      <a href="hospital_stock.php" class="sidebar-nav-link <?php echo $activePage === 'hospital_stock.php' ? 'active' : ''; ?>">
        <i class="fa-solid fa-boxes-stacked"></i>
        <span>Vaccine Stock</span>
      </a>
      <a href="hospital_profile.php" class="sidebar-nav-link <?php echo $activePage === 'hospital_profile.php' ? 'active' : ''; ?>">
        <i class="fa-solid fa-user-gear"></i>
        <span>Profile</span>
      </a>

    <?php else: ?>
      <!-- PATIENT NAVIGATION (Matching Screenshot 5, 6) -->
      <div class="sidebar-section-title">PATIENT PANEL</div>
      <a href="patient_dashboard.php" class="sidebar-nav-link <?php echo $activePage === 'patient_dashboard.php' ? 'active' : ''; ?>">
        <i class="fa-solid fa-chart-pie"></i>
        <span>Dashboard</span>
      </a>
      <a href="patient_search.php" class="sidebar-nav-link <?php echo $activePage === 'patient_search.php' ? 'active' : ''; ?>">
        <i class="fa-solid fa-magnifying-glass-location"></i>
        <span>Search Hospitals</span>
      </a>
      <a href="patient_book.php" class="sidebar-nav-link <?php echo $activePage === 'patient_book.php' ? 'active' : ''; ?>">
        <i class="fa-solid fa-calendar-plus"></i>
        <span>Book Appointment</span>
      </a>
      <a href="patient_appointments.php" class="sidebar-nav-link <?php echo $activePage === 'patient_appointments.php' ? 'active' : ''; ?>">
        <i class="fa-solid fa-calendar-check"></i>
        <span>My Appointments</span>
      </a>
      <a href="patient_results.php" class="sidebar-nav-link <?php echo $activePage === 'patient_results.php' ? 'active' : ''; ?>">
        <i class="fa-solid fa-file-medical"></i>
        <span>Covid Test Results</span>
      </a>
      <a href="patient_vaccine.php" class="sidebar-nav-link <?php echo $activePage === 'patient_vaccine.php' ? 'active' : ''; ?>">
        <i class="fa-solid fa-shield-halved"></i>
        <span>Vaccination Status</span>
      </a>
      <a href="patient_symptoms.php" class="sidebar-nav-link <?php echo $activePage === 'patient_symptoms.php' ? 'active' : ''; ?>">
        <i class="fa-solid fa-heart-pulse"></i>
        <span>Symptoms &amp; Guide</span>
      </a>
      <a href="patient_profile.php" class="sidebar-nav-link <?php echo $activePage === 'patient_profile.php' ? 'active' : ''; ?>">
        <i class="fa-solid fa-user-gear"></i>
        <span>My Profile</span>
      </a>
    <?php endif; ?>
  </nav>

  <div class="sidebar-footer">
    <a href="logout.php" class="sidebar-nav-link text-danger">
      <i class="fa-solid fa-arrow-right-from-bracket"></i>
      <span>Logout</span>
    </a>
  </div>
</aside>
