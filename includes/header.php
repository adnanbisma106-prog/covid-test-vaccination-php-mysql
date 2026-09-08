<?php
/**
 * Global Header Component for COVID-19 System
 */
require_once __DIR__ . '/../config/db.php';
$currentUser = getLoggedInUser();
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' | ' : ''; ?>COVID-19 Test & Vaccination System</title>
  
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  
  <!-- FontAwesome 6 Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  
  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
  
  <!-- Custom Design System CSS -->
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<?php if ($flash): ?>
  <div class="position-fixed top-0 end-0 p-3" style="z-index: 1100;">
    <div class="alert alert-<?php echo $flash['type'] === 'error' ? 'danger' : htmlspecialchars($flash['type']); ?> alert-dismissible fade show shadow-sm" role="alert">
      <i class="fa-solid fa-circle-info me-2"></i>
      <?php echo htmlspecialchars($flash['message']); ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  </div>
<?php endif; ?>
