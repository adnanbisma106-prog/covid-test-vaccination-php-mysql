<?php
/**
 * COVID-19 System Login Processing Endpoint
 */

require_once __DIR__ . '/config/db.php';


/* =========================================================
   1. QUICK DEMO LOGIN
   ========================================================= */

// Supports both POST quick_role and GET role
$quickRole = $_POST['quick_role'] ?? $_GET['role'] ?? '';

if ($quickRole !== '') {

    /* ---------------- ADMIN ---------------- */

    if ($quickRole === 'admin') {

        $_SESSION['user_id'] = 'usr_admin';
        $_SESSION['user_name'] = 'System Administrator';
        $_SESSION['user_email'] = 'admin@covid.gov';
        $_SESSION['user_role'] = 'admin';

        $_SESSION['user_avatar'] =
            'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=120&h=120&fit=crop';

        setFlash(
            "Logged in as System Administrator (ADMIN)",
            "success"
        );

        header("Location: admin_dashboard.php");
        exit;
    }


    /* ---------------- HOSPITAL ---------------- */

    elseif ($quickRole === 'hospital') {

        $_SESSION['user_id'] = 'hosp_city';
        $_SESSION['user_name'] = 'City Hospital';
        $_SESSION['user_email'] = 'city@hospital.com';
        $_SESSION['user_role'] = 'hospital';

        $_SESSION['user_avatar'] =
            'https://images.unsplash.com/photo-1586773860418-d37222d8fce3?w=120&h=120&fit=crop';

        setFlash(
            "Logged in as City Hospital (HOSPITAL)",
            "success"
        );

        header("Location: hospital_dashboard.php");
        exit;
    }


    /* ---------------- ALI KHAN ---------------- */

    elseif ($quickRole === 'patient_ali') {

        $_SESSION['user_id'] = 'pat_ali';
        $_SESSION['user_name'] = 'Ali Khan';
        $_SESSION['user_email'] = 'ali@gmail.com';
        $_SESSION['user_role'] = 'patient';

        $_SESSION['user_avatar'] =
            'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=120&h=120&fit=crop';

        setFlash(
            "Logged in as Ali Khan (PATIENT)",
            "success"
        );

        header("Location: patient_dashboard.php");
        exit;
    }


    /* ---------------- AYESHA MALIK ---------------- */

    elseif (
        $quickRole === 'patient' ||
        $quickRole === 'patient_ayesha'
    ) {

        $ayesha = null;


        /*
         * First try to find Ayesha from database
         */

        if ($conn && !$conn->connect_error) {

            $stmt = $conn->prepare(
                "SELECT * FROM users
                 WHERE name = ?
                 AND role = 'patient'
                 LIMIT 1"
            );

            if ($stmt) {

                $ayeshaName = "Ayesha Malik";

                $stmt->bind_param(
                    "s",
                    $ayeshaName
                );

                $stmt->execute();

                $result = $stmt->get_result();

                if ($result) {
                    $ayesha = $result->fetch_assoc();
                }

                $stmt->close();
            }
        }


        /*
         * If Ayesha exists in database
         */

        if ($ayesha) {

            $_SESSION['user_id'] = $ayesha['id'];
            $_SESSION['user_name'] = $ayesha['name'];
            $_SESSION['user_email'] = $ayesha['email'];
            $_SESSION['user_role'] = 'patient';

            $_SESSION['user_avatar'] =
                $ayesha['avatar']
                ?? 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=120&h=120&fit=crop';

        }

        /*
         * Fallback demo Ayesha account
         */

        else {

            $_SESSION['user_id'] = 'pat_ayesha';
            $_SESSION['user_name'] = 'Ayesha Malik';
            $_SESSION['user_email'] = 'ayesha@gmail.com';
            $_SESSION['user_role'] = 'patient';

            $_SESSION['user_avatar'] =
                'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=120&h=120&fit=crop';
        }


        setFlash(
            "Logged in as Ayesha Malik (PATIENT)",
            "success"
        );

        header("Location: patient_dashboard.php");
        exit;
    }
}


/* =========================================================
   2. STANDARD FORM LOGIN
   ========================================================= */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $selectedRole = trim($_POST['role'] ?? '');


    /* =====================================================
       DATABASE LOGIN
       ===================================================== */

    if ($conn && !$conn->connect_error) {

        $stmt = $conn->prepare(
            "SELECT * FROM users
             WHERE email = ?
             LIMIT 1"
        );

        if ($stmt) {

            $stmt->bind_param(
                "s",
                $email
            );

            $stmt->execute();

            $result = $stmt->get_result();

            if ($user = $result->fetch_assoc()) {


                /* ---------------- PASSWORD CHECK ---------------- */

                if (
                    $user['password'] === $password ||
                    password_verify(
                        $password,
                        $user['password']
                    )
                ) {


                    /* =================================================
                       ROLE CHECK
                       ================================================= */

                    if (
                        $selectedRole !== '' &&
                        $selectedRole !== $user['role']
                    ) {

                        header(
                            "Location: index.php?msg=Please select the correct Login As option for this account.&type=warning"
                        );

                        exit;
                    }


                    /* =================================================
                       HOSPITAL APPROVAL CHECK
                       ================================================= */

                    if ($user['role'] === 'hospital') {

                        $hstmt = $conn->prepare(
                            "SELECT approved
                             FROM hospitals
                             WHERE email = ?
                             LIMIT 1"
                        );

                        if ($hstmt) {

                            $hstmt->bind_param(
                                "s",
                                $email
                            );

                            $hstmt->execute();

                            $hres =
                                $hstmt
                                ->get_result()
                                ->fetch_assoc();


                            if (
                                $hres &&
                                $hres['approved'] == 0
                            ) {

                                header(
                                    "Location: index.php?msg=Your hospital account is awaiting Administrator approval.&type=warning"
                                );

                                exit;
                            }

                            $hstmt->close();
                        }
                    }


                    /* =================================================
                       CREATE SESSION
                       ================================================= */

                    $_SESSION['user_id'] =
                        $user['id'];

                    $_SESSION['user_name'] =
                        $user['name'];

                    $_SESSION['user_email'] =
                        $user['email'];

                    $_SESSION['user_role'] =
                        $user['role'];

                    $_SESSION['user_avatar'] =
                        $user['avatar']
                        ?? 'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?w=120&h=120&fit=crop';


                    /* =================================================
                       AUDIT LOG
                       ================================================= */

                    if (function_exists('logAudit')) {

                        logAudit(
                            $conn,
                            $user['name'],
                            "User logged into " .
                            strtoupper($user['role']) .
                            " portal"
                        );
                    }


                    /* =================================================
                       ROLE BASED REDIRECT
                       ================================================= */

                    if ($user['role'] === 'admin') {

                        header(
                            "Location: admin_dashboard.php"
                        );

                    }

                    elseif ($user['role'] === 'hospital') {

                        header(
                            "Location: hospital_dashboard.php"
                        );

                    }

                    elseif ($user['role'] === 'patient') {

                        header(
                            "Location: patient_dashboard.php"
                        );

                    }

                    else {

                        header(
                            "Location: index.php?msg=Invalid user role.&type=danger"
                        );
                    }

                    exit;
                }
            }

            $stmt->close();
        }
    }


    /* =========================================================
       3. FALLBACK DEMO LOGIN
       ========================================================= */


    /* ---------------- ADMIN ---------------- */

    if (
        $email === 'admin@covid.gov' &&
        $password === 'admin'
    ) {

        $_SESSION['user_id'] = 'usr_admin';
        $_SESSION['user_name'] = 'System Administrator';
        $_SESSION['user_email'] = $email;
        $_SESSION['user_role'] = 'admin';

        header(
            "Location: admin_dashboard.php"
        );

        exit;
    }


    /* ---------------- HOSPITAL ---------------- */

    elseif (
        $email === 'city@hospital.com' &&
        $password === 'city'
    ) {

        $_SESSION['user_id'] = 'hosp_city';
        $_SESSION['user_name'] = 'City Hospital';
        $_SESSION['user_email'] = $email;
        $_SESSION['user_role'] = 'hospital';

        header(
            "Location: hospital_dashboard.php"
        );

        exit;
    }


    /* ---------------- ALI ---------------- */

    elseif (
        $email === 'ali@gmail.com' &&
        $password === 'ali'
    ) {

        $_SESSION['user_id'] = 'pat_ali';
        $_SESSION['user_name'] = 'Ali Khan';
        $_SESSION['user_email'] = $email;
        $_SESSION['user_role'] = 'patient';

        header(
            "Location: patient_dashboard.php"
        );

        exit;
    }


    /* ---------------- AYESHA ---------------- */

    elseif (
        $email === 'ayesha@gmail.com' &&
        $password === 'ayesha'
    ) {

        $_SESSION['user_id'] = 'pat_ayesha';
        $_SESSION['user_name'] = 'Ayesha Malik';
        $_SESSION['user_email'] = $email;
        $_SESSION['user_role'] = 'patient';

        header(
            "Location: patient_dashboard.php"
        );

        exit;
    }


    /* =========================================================
       INVALID LOGIN
       ========================================================= */

    header(
        "Location: index.php?msg=Invalid email or password. Please try demo accounts.&type=danger"
    );

    exit;
}


/* =========================================================
   4. DEFAULT
   ========================================================= */

header("Location: index.php");
exit;
?>