<?php
/**
 * Logout Endpoint
 */
require_once __DIR__ . '/config/db.php';

session_unset();
session_destroy();

header("Location: index.php?msg=Logged out successfully.&type=info");
exit;
