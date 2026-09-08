<?php
require_once  __DIR__ .'/config/db.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>COVID Test & Vaccination System</title>

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap"
        rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', Arial, sans-serif;
            background: #ffffff;
            color: #14243a;
        }

        /* ================= NAVBAR ================= */

        .navbar {
            min-height: 75px;
            background: #ffffff;
            border-bottom: 1px solid #e7edf4;
        }

        .logo {
            text-decoration: none;
            color: #073d91;
            font-weight: 900;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .logo-icon {
            width: 43px;
            height: 43px;
            background: #1264d8;
            color: white;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
        }

        .logo-text {
            line-height: 1.1;
            font-size: 14px;
        }

        .logo-text small {
            display: block;
            font-size: 9px;
            color: #60758e;
            letter-spacing: .5px;
            margin-top: 4px;
        }

        .navbar-nav .nav-link {
            color: #526378 !important;
            font-size: 16px;
            font-weight: 600;
            margin-left: 15px;
        }

        .navbar-nav .nav-link:hover {
            color: #1264d8 !important;
        }

        /* ================= MAIN ================= */

        .main-area {
            background:
                radial-gradient(circle at 70% 15%,
                    rgba(45, 130, 235, .08),
                    transparent 30%),
                linear-gradient(180deg, #f7fbff, #ffffff);

            min-height: calc(100vh - 75px);
            padding-bottom: 50px;
        }

        .main-container {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 430px;
            gap: 40px;
            padding-top: 35px;
            padding-bottom: 30px;
        }

        /* ================= LEFT SIDE ================= */

        .welcome {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            background: #eaf4ff;
            color: #1264d8;
            border-radius: 50px;
            padding: 8px 14px;
            font-size: 15px;
            font-weight: 800;
            margin-bottom: 15px;
        }

        .hero-title {
            font-size: clamp(45px, 5vw, 68px);
            line-height: 1.02;
            font-weight: 900;
            letter-spacing: -2px;
            color: #073d91;
        }

        .hero-title span {
            color: #146ce2;
        }

        .hero-description {
            max-width: 620px;
            color: #63758a;
            font-size: 14px;
            line-height: 1.8;
            margin-top: 17px;
            margin-bottom: 22px;
        }

        /* ================= BUTTONS ================= */

        .hero-buttons {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .btn-book {
            background: #1264d8;
            color: white;
            border: none;
            padding: 12px 22px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 800;
        }

        .btn-book:hover {
            background: #084eae;
            color: white;
        }

        .btn-vaccinated {
            background: white;
            border: 1px solid #d5e1ee;
            color: #1d344f;
            padding: 12px 22px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 800;
        }

        .btn-vaccinated:hover {
            border-color: #1264d8;
            color: #1264d8;
        }

        /* ================= HERO IMAGE ================= */

        .hero-image {
            height: 500px;
            margin-top: 25px;
            border-radius: 17px;
            overflow: hidden;
            position: relative;
            background: #e8f4ff;
            border: 1px solid #dce9f5;
            box-shadow: 0 15px 35px rgba(20, 70, 120, .10);
        }

        .hero-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .image-overlay {
            position: absolute;
            inset: 0;
            background:
                linear-gradient(90deg,
                    rgba(255, 255, 255, .9),
                    rgba(255, 255, 255, .15),
                    transparent);
        }

        /* ================= COVID BOX ================= */

        .covid-box {
            margin-top: 25px;
            background: linear-gradient(100deg, #e9f5ff, #f7fbff);
            border: 1px solid #d6e7f8;
            border-radius: 14px;
            padding: 18px;
            display: flex;
            align-items: center;
            gap: 18px;
        }

        .covid-icon {
            min-width: 58px;
            height: 58px;
            border-radius: 50%;
            background: #d6ebff;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #1264d8;
            font-size: 25px;
        }

        .covid-box h5 {
            color: #1264d8;
            font-size: 16px;
            font-weight: 800;
            margin-bottom: 5px;
        }

        .covid-box p {
            color: #66788c;
            font-size: 11px;
            line-height: 1.6;
            margin: 0;
        }

        /* ================= SERVICES ================= */

        .services-section {
            width: 100%;
            max-width: 1200px;
            margin: 50px auto 0;
            text-align: center;
        }

        .section-label {
            text-align: center;
            color: #1264d8;
            font-size: 32px;
            font-weight: 900;
            letter-spacing: 1px;
            margin-top: 40px;
        }

        .section-title {
            text-align: center;
            font-size: 32px;
            font-weight: 900;
            color: #172c46;
            margin-top: 5px;
        }

        .section-subtitle {
            text-align: center;
            color: #728197;
            font-size: 16px;
            margin-bottom: 25px;
        }

        .services {
            width: 100%;
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 20px;
            margin: 0 auto;
        }

        .service-card {
            background: white;
            border: 1px solid #dfe8f1;
            border-radius: 11px;
            padding: 20px 15px;
            min-height: 170px;
            box-shadow: 0 5px 15px rgba(30, 70, 110, .05);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }

        .service-card i {
            color: #1264d8;
            font-size: 25px;
            margin-bottom: 12px;
        }

        .service-card h6 {
            font-size: 14px;
            font-weight: 800;
            color: #21344b;
            margin-bottom: 8px;
        }

        .service-card p {
            font-size: 11px;
            color: #718197;
            line-height: 1.6;
            margin: 0;
        }


        /* ================= FAQs ================= */

.faq-section {
    max-width: 1000px;
    margin: 70px auto;
    padding: 40px 20px;
    text-align: center;
}

.faq-container {
    max-width: 850px;
    margin: 30px auto 0;
    text-align: left;
}

.accordion-item {
    border: 1px solid #dce7f2;
    border-radius: 10px !important;
    overflow: hidden;
    margin-bottom: 15px;
    box-shadow: 0 5px 15px rgba(30, 70, 110, .05);
}

.accordion-button {
    font-size: 15px;
    font-weight: 700;
    color: #21344b;
    padding: 20px;
    background: #ffffff;
}

.accordion-button:not(.collapsed) {
    background: #eaf4ff;
    color: #1264d8;
    box-shadow: none;
}

.accordion-button:focus {
    box-shadow: none;
    border-color: #dce7f2;
}

.accordion-body {
    font-size: 14px;
    color: #718197;
    line-height: 1.7;
    padding: 20px;
}

.accordion-button i {
    color: #1264d8;
}

        /* ================= STEPS ================= */

        .steps-box {
            width: 100%;
            background: #eff7ff;
            border: 1px solid #dcecff;
            border-radius: 14px;
            margin-top: 35px;
            padding: 30px;
        }

        .steps {
            width: 100%;
            display: grid;
            grid-template-columns: repeat(4, minmax(0, 1fr));
            gap: 20px;
            margin: 0 auto;
        }

        .step {
            background: white;
            border: 1px solid #dce7f2;
            border-radius: 9px;
            padding: 18px;
            text-align: left;
        }

        .step-number {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: #1264d8;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 900;
            margin-bottom: 10px;
        }

        .step h6 {
            font-size: 13px;
            font-weight: 800;
            margin-bottom: 6px;
        }

        .step p {
            color: #738297;
            font-size: 11px;
            line-height: 1.5;
            margin: 0;
        }

        /* ================= LOGIN ================= */

        .login-column {
            position: sticky;
            top: 95px;
            height: fit-content;
        }

        .login-card {
            background: white;
            border: 1px solid #dfe6ee;
            border-radius: 18px;
            padding: 24px;
            box-shadow: 0 18px 50px rgba(25, 60, 100, .13);
        }

        .login-shield {
            width: 57px;
            height: 57px;
            border-radius: 50%;
            background: #eaf3ff;
            color: #1264d8;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 25px;
            margin: auto;
        }

        .login-title {
            text-align: center;
            font-size: 25px;
            font-weight: 900;
            margin-top: 20px;
        }

        .login-subtitle {
            text-align: center;
            color: #8996a5;
            font-size: 15px;
            margin-bottom: 18px;
        }

        .quick-login {
            border: 1px solid #d8e5f2;
            background: #f7fbff;
            border-radius: 10px;
            padding: 11px;
            margin-bottom: 20px;
        }

        .quick-heading {
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #1264d8;
            font-size: 13px;
            font-weight: 900;
            margin-bottom: 12px;
        }

        .ready {
            color: #079250;
            border: 1px solid #8bd5b0;
            background: #effcf5;
            padding: 3px 7px;
            border-radius: 5px;
            font-size: 10px;
        }

        .quick-btn {
            width: 100%;
            border-radius: 7px;
            padding: 8px 4px;
            font-size: 11px;
            font-weight: 800;
        }

        .divider {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #9ba7b4;
            font-size: 12px;
            margin: 15px 0;
        }

        .divider::before,
        .divider::after {
            content: "";
            height: 1px;
            background: #e5eaf0;
            flex: 1;
        }

        .form-label {
            font-size: 11px;
            font-weight: 800;
            color: #45566a;
            margin-bottom: 6px;
        }

        .form-control,
        .form-select {
            height: 43px;
            border: 1px solid #dce5ee;
            border-radius: 8px;
            font-size: 12px;
            box-shadow: none !important;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #1264d8;
        }

        .login-btn {
            width: 100%;
            height: 44px;
            border: none;
            border-radius: 8px;
            background: linear-gradient(100deg, #0e67e2, #1558d6);
            color: white;
            font-size: 14px;
            font-weight: 900;
        }

        .register {
            text-align: center;
            border-top: 1px solid #edf1f5;
            margin-top: 16px;
            padding-top: 13px;
            color: #7a8795;
            font-size: 12px;
        }

        .register a {
            color: #1264d8;
            font-weight: 800;
            text-decoration: none;
        }

        .help-box {
            background: #eef6ff;
            border-radius: 9px;
            margin-top: 13px;
            padding: 12px;
            font-size: 12px;
            color: #66798d;
        }

        .help-box strong {
            display: block;
            color: #1264d8;
            font-size: 12px;
            margin-bottom: 3px;
        }

        /* ================= FOOTER ================= */

        .site-footer {
            background: linear-gradient(135deg, #063b78, #075aa8);
            color: #fff;
            padding-top: 55px;
            margin-top: 50px;
        }

        .footer-brand {
            display: flex;
            align-items: center;
            gap: 13px;
            margin-bottom: 18px;
        }

        .footer-logo {
            width: 52px;
            height: 52px;
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.25);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 25px;
        }

        .footer-brand h4 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
        }

        .footer-brand h4 span {
            color: #75d5ff;
        }

        .footer-brand p {
            margin: 2px 0 0;
            font-size: 12px;
            opacity: .8;
        }

        .footer-text {
            color: rgba(255, 255, 255, .78);
            line-height: 1.8;
            font-size: 14px;
            max-width: 360px;
        }

        .footer-title {
            font-size: 17px;
            font-weight: 700;
            margin-bottom: 22px;
            position: relative;
            padding-bottom: 10px;
        }

        .footer-title::after {
            content: "";
            position: absolute;
            left: 0;
            bottom: 0;
            width: 38px;
            height: 3px;
            background: #75d5ff;
            border-radius: 5px;
        }

        .footer-links {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .footer-links li {
            margin-bottom: 12px;
        }

        .footer-links a {
            color: rgba(255, 255, 255, .78);
            text-decoration: none;
            font-size: 14px;
        }

        .footer-links a i {
            font-size: 10px;
            margin-right: 8px;
            color: #75d5ff;
        }

        .footer-contact p {
            display: flex;
            gap: 12px;
            color: rgba(255, 255, 255, .78);
            font-size: 14px;
            margin-bottom: 14px;
        }

        .footer-contact i {
            color: #75d5ff;
            margin-top: 4px;
            width: 15px;
        }

        .footer-social {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .footer-social a {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            background: rgba(255, 255, 255, .12);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
        }

        .footer-bottom {
            margin-top: 45px;
            border-top: 1px solid rgba(255, 255, 255, .15);
            padding: 18px 0;
        }

        .footer-bottom-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
        }

        .footer-bottom p {
            margin: 0;
            color: rgba(255, 255, 255, .7);
            font-size: 13px;
        }

        .footer-bottom a {
            color: rgba(255, 255, 255, .7);
            text-decoration: none;
            font-size: 13px;
        }

        .footer-bottom span {
            margin: 0 8px;
            color: rgba(255, 255, 255, .4);
        }

        /* ================= RESPONSIVE ================= */

        @media(max-width: 991px) {

            .main-container {
                grid-template-columns: 1fr;
            }

            .login-column {
                position: relative;
                top: auto;
            }

            .login-card {
                max-width: 500px;
                margin: auto;
            }

            .services,
            .steps {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media(max-width:575px) {

            .hero-title {
                font-size: 42px;
            }

            .services,
            .steps {
                grid-template-columns: 1fr;
            }

            .hero-image {
                height: 220px;
            }

            .covid-box {
                align-items: flex-start;
            }

            .footer-bottom-content {
                flex-direction: column;
                text-align: center;
            }

            .section-label {
                font-size: 25px;
            }

            .section-title {
                font-size: 24px;
            }

            .steps-box {
                padding: 20px;
            }
        }


/* ===== PROFESSIONAL HOME PAGE ANIMATION ===== */

@keyframes heroText {
    0% {
        opacity: 0;
        transform: translateY(35px);
    }
    100% {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes heroImage {
    0% {
        opacity: 0;
        transform: translateX(50px) scale(0.95);
    }
    100% {
        opacity: 1;
        transform: translateX(0) scale(1);
    }
}

@keyframes floating {
    0%, 100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-10px);
    }
}

@keyframes softPulse {
    0%, 100% {
        box-shadow: 0 8px 25px rgba(0,0,0,0.08);
    }
    50% {
        box-shadow: 0 15px 35px rgba(0,0,0,0.14);
    }
}

/* Hero text */
.hero-title {
    animation: heroText 0.9s ease-out both;
}

.hero-description {
    animation: heroText 1.1s ease-out both;
}

.hero-buttons {
    animation: heroText 1.3s ease-out both;
}

/* Hero image */
.hero-image {
    animation:
        heroImage 1s ease-out both,
        floating 4s ease-in-out 1s infinite;
}

/* Cards */
.card {
    transition: all 0.35s ease;
}

.card:hover {
    transform: translateY(-8px);
    box-shadow: 0 15px 35px rgba(0,0,0,0.12);
}

/* Buttons */
.btn {
    transition: all 0.3s ease;
}

.btn:hover {
    transform: translateY(-3px);
}

/* Icons */
.card i,
.card .fa,
.card .fas {
    transition: transform 0.35s ease;
}

.card:hover i,
.card:hover .fa,
.card:hover .fas {
    transform: scale(1.12);
}

/* Section headings */
section h2,
section h3 {
    animation: heroText 0.8s ease-out both;
}

/* Soft animation for important box */
.covid-box {
    animation: softPulse 3s ease-in-out infinite;
}

/* Mobile */
@media (max-width: 768px) {
    .hero-image {
        animation: heroImage 0.8s ease-out both;
    }

    .card:hover {
        transform: translateY(-4px);
    }
}

/* Accessibility */
@media (prefers-reduced-motion: reduce) {
    *,
    *::before,
    *::after {
        animation: none !important;
        transition: none !important;
    }
}
    </style>
    
</head>

<body>

    <!-- ================= NAVBAR ================= -->

    <nav class="navbar navbar-expand-lg">

        <div class="container">

            <a href="index.php" class="logo">

                <span class="logo-icon">
                    <i class="fa-solid fa-shield-heart"></i>
                </span>

                <span class="logo-text">
                    COVIDCARE

                    <small>
                        COVID-19 MANAGEMENT SYSTEM
                    </small>
                </span>

            </a>

            <button class="navbar-toggler"
                data-bs-toggle="collapse"
                data-bs-target="#nav">

                <span class="navbar-toggler-icon"></span>

            </button>

            <div class="collapse navbar-collapse" id="nav">

                <ul class="navbar-nav ms-auto">

                    <li class="nav-item">
                        <a href="#home" class="nav-link">Home</a>
                    </li>

                    <li class="nav-item">
                        <a href="#about" class="nav-link">About Us</a>
                    </li>

                    <li class="nav-item">
                        <a href="#services" class="nav-link">Services</a>
                    </li>

                    <li class="nav-item">
                        <a href="#login" class="nav-link">Hospitals</a>
                    </li>

                    <li class="nav-item">
                        <a href="#faq" class="nav-link">FAQs</a>
                    </li>

                    <li class="nav-item">
                        <a href="#footer" class="nav-link">Contact Us</a>
                    </li>

                </ul>

            </div>

        </div>

    </nav>


    <!-- ================= MAIN ================= -->

    <main class="main-area" id="home">

        <div class="container">

            <!-- HERO + LOGIN -->
          <div class="main-container" data-aos="fade-up" data-aos-duration="1200">

                <!-- LEFT SIDE -->
                <div>

                    <div class="welcome">
                        <i class="fa-solid fa-circle-plus"></i>
                        WELCOME TO COVIDCARE PORTAL
                    </div>

                    <h1 class="hero-title">
                        Protect Yourself.<br>
                        <span>Protect Everyone.</span>
                    </h1>

                    <p class="hero-description">
                        Book your COVID-19 test and vaccination appointment
                        online easily. Connect with certified hospitals and
                        monitor your immunization status across the nation.
                    </p>

                    <div class="hero-buttons">

                        <a href="#login" class="btn btn-book">
                            <i class="fa-solid fa-calendar-days me-2"></i>
                            Book Appointment
                        </a>

                        <a href="#services" class="btn btn-vaccinated">
                            <i class="fa-solid fa-shield-virus me-2"></i>
                            I'm Vaccinated
                        </a>

                    </div>


                    <div class="hero-image">

                        <img src="https://images.unsplash.com/photo-1584982751601-97dcc096659c?auto=format&fit=crop&w=1200&q=80"
                            alt="Healthcare">

                        <div class="image-overlay"></div>

                    </div>


                    <div class="covid-box" id="about">

                        <div class="covid-icon">
                            <i class="fa-solid fa-virus-covid"></i>
                        </div>

                        <div>

                            <h5>Coronavirus (COVID-19)</h5>

                            <p>
                                Stay informed and stay safe. Get tested if you
                                have symptoms, vaccinate yourself and help
                                prevent the spread of COVID-19.
                            </p>

                        </div>

                    </div>

                </div>


                <!-- ================= LOGIN ================= -->

                <div class="login-column" id="login">

                    <div class="login-card">

                        <div class="login-shield">
                            <i class="fa-solid fa-shield-halved"></i>
                        </div>

                        <h2 class="login-title">
                            Welcome Back!
                        </h2>

                        <p class="login-subtitle">
                            Please sign in to your account
                        </p>


                        <div class="quick-login">

                            <div class="quick-heading">

                                <span>
                                    <i class="fa-solid fa-bolt"></i>
                                    QUICK 1-CLICK LOGIN
                                </span>

                                <span class="ready">
                                    Ready
                                </span>

                            </div>


                            <div class="row g-2">

                                <div class="col-4">

                                    <a href="login.php?role=admin"
                                        class="btn btn-primary quick-btn">

                                        <i class="fa-solid fa-user-shield"></i>
                                        Admin

                                    </a>

                                </div>


                                <div class="col-4">

                                    <a href="login.php?role=hospital"
                                        class="btn btn-primary quick-btn">

                                        <i class="fa-solid fa-hospital"></i>
                                        Hospital

                                    </a>

                                </div>


                                <div class="col-4">

                                    <a href="login.php?role=patient"
                                        class="btn btn-secondary quick-btn">

                                        <i class="fa-solid fa-user"></i>
                                        Patient

                                    </a>

                                </div>

                            </div>

                        </div>


                        <div class="divider">
                            or
                        </div>


                        <form action="login.php" method="POST">

                            <div class="mb-3">

                                <label class="form-label">
                                    Email Address
                                </label>

                                <input type="email"
                                    name="email"
                                    class="form-control"
                                    placeholder="Enter your email"
                                    required>

                            </div>


                            <div class="mb-3">

                                <label class="form-label">
                                    Password
                                </label>

                                <div class="position-relative">

                                    <input type="password"
                                        name="password"
                                        id="password"
                                        class="form-control"
                                        placeholder="Enter your password"
                                        required>

                                    <button type="button"
                                        onclick="showPassword()"
                                        style="position:absolute; right:10px; top:9px; border:0; background:none; color:#74869a;">

                                        <i id="eye"
                                            class="fa-regular fa-eye"></i>

                                    </button>

                                </div>

                            </div>


                            <div class="mb-4">

                                <label class="form-label">
                                    Login As
                                </label>

                                <select name="role"
                                    class="form-select"
                                    required>

                                    <option value="admin">
                                        Admin Portal
                                    </option>

                                    <option value="hospital">
                                        Hospital Portal
                                    </option>

                                    <option value="patient">
                                        Patient Portal
                                    </option>

                                </select>

                            </div>


                            <button type="submit"
                                class="login-btn">

                                <i class="fa-solid fa-arrow-right-to-bracket me-2"></i>
                                Login

                            </button>

                        </form>


                        <div class="register">

                            Don't have an account?

                            <a href="register.php?type=patient">
                                Register Patient
                            </a>

                            <span> • </span>

                            <a href="register.php?type=hospital">
                                Register Hospital
                            </a>

                        </div>


                        <div class="help-box" id="faq">

                            <strong>
                                <i class="fa-solid fa-headset me-1"></i>
                                Need Help?
                            </strong>

                            Contact our support team for assistance.

                        </div>

                    </div>

                </div>

            </div>
            <!-- MAIN CONTAINER END -->


            <!-- ================= SERVICES ================= -->

            <section id="services" class="services-section">

                <div class="section-label">
                    OUR SERVICES
                </div>

                <h2 class="section-title">
                    Everything you need, in one place
                </h2>

                <p class="section-subtitle">
                    We provide simple and secure healthcare services for everyone.
                </p>


                <div class="services">

                    <div class="service-card">

                        <i class="fa-solid fa-calendar-days"></i>

                        <h6>
                            Book Appointment
                        </h6>

                        <p>
                            Book COVID-19 test and vaccination appointments online.
                        </p>

                    </div>


                    <div class="service-card">

                        <i class="fa-solid fa-syringe"></i>

                        <h6>
                            Vaccination
                        </h6>

                        <p>
                            Register for vaccination and manage your doses.
                        </p>

                    </div>


                    <div class="service-card">

                        <i class="fa-solid fa-clipboard-check"></i>

                        <h6>
                            COVID-19 Tests
                        </h6>

                        <p>
                            Book your test and get digital reports online.
                        </p>

                    </div>


                    <div class="service-card">

                        <i class="fa-solid fa-file-medical"></i>

                        <h6>
                            Health Records
                        </h6>

                        <p>
                            Access your vaccination and test history anytime.
                        </p>

                    </div>

                </div>


                <!-- ================= STEPS ================= -->

                <div class="steps-box">

                    <div class="section-label" style="margin-top:0">
                        4 SIMPLE STEPS
                    </div>

                    <h2 class="section-title">
                        Book your healthcare service
                    </h2>

                    <p class="section-subtitle">
                        Complete your appointment in four easy steps.
                    </p>


                    <div class="steps">

                        <div class="step">

                            <div class="step-number">
                                1
                            </div>

                            <h6>
                                Register / Login
                            </h6>

                            <p>
                                Create your account or login.
                            </p>

                        </div>


                        <div class="step">

                            <div class="step-number">
                                2
                            </div>

                            <h6>
                                Choose Service
                            </h6>

                            <p>
                                Select test or vaccination.
                            </p>

                        </div>


                        <div class="step">

                            <div class="step-number">
                                3
                            </div>

                            <h6>
                                Select Date & Time
                            </h6>

                            <p>
                                Pick your hospital and appointment time.
                            </p>

                        </div>


                        <div class="step">

                            <div class="step-number">
                                4
                            </div>

                            <h6>
                                Appointment Confirmed
                            </h6>

                            <p>
                                Get confirmation and visit hospital.
                            </p>

                        </div>

                    </div>

                </div>

            </section>

        </div>

    </main>


    

<section id="faq" class="faq-section">

    <div class="section-label">
        FREQUENTLY ASKED QUESTIONS
    </div>

    <h2 class="section-title">
        Got Questions? We Have Answers
    </h2>

    <p class="section-subtitle">
        Find answers to the most common questions about COVID-19
        tests, vaccination and appointments.
    </p>


    <div class="faq-container">

        <div class="accordion" id="faqAccordion">


            <!-- FAQ 1 -->
            <div class="accordion-item">

                <h2 class="accordion-header">

                    <button class="accordion-button"
                        type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#faq1">

                        <i class="fa-solid fa-calendar-check me-3"></i>

                        How can I book an appointment?

                    </button>

                </h2>

                <div id="faq1"
                    class="accordion-collapse collapse show"
                    data-bs-parent="#faqAccordion">

                    <div class="accordion-body">

                        You can book an appointment by selecting
                        your preferred hospital, service, date and time.

                    </div>

                </div>

            </div>


            <!-- FAQ 2 -->
            <div class="accordion-item">

                <h2 class="accordion-header">

                    <button class="accordion-button collapsed"
                        type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#faq2">

                        <i class="fa-solid fa-syringe me-3"></i>

                        How do I register for vaccination?

                    </button>

                </h2>

                <div id="faq2"
                    class="accordion-collapse collapse"
                    data-bs-parent="#faqAccordion">

                    <div class="accordion-body">

                        Register as a patient, login to your account
                        and select the vaccination service to continue.

                    </div>

                </div>

            </div>


            <!-- FAQ 3 -->
            <div class="accordion-item">

                <h2 class="accordion-header">

                    <button class="accordion-button collapsed"
                        type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#faq3">

                        <i class="fa-solid fa-vial me-3"></i>

                        How can I get my COVID-19 test report?

                    </button>

                </h2>

                <div id="faq3"
                    class="accordion-collapse collapse"
                    data-bs-parent="#faqAccordion">

                    <div class="accordion-body">

                        After your test is completed, you can login
                        to your patient account and view your test report.

                    </div>

                </div>

            </div>


            <!-- FAQ 4 -->
            <div class="accordion-item">

                <h2 class="accordion-header">

                    <button class="accordion-button collapsed"
                        type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#faq4">

                        <i class="fa-solid fa-hospital me-3"></i>

                        Can I choose my preferred hospital?

                    </button>

                </h2>

                <div id="faq4"
                    class="accordion-collapse collapse"
                    data-bs-parent="#faqAccordion">

                    <div class="accordion-body">

                        Yes. You can select your preferred available
                        hospital while booking your appointment.

                    </div>

                </div>

            </div>


            <!-- FAQ 5 -->
            <div class="accordion-item">

                <h2 class="accordion-header">

                    <button class="accordion-button collapsed"
                        type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#faq5">

                        <i class="fa-solid fa-shield-virus me-3"></i>

                        Is my medical information secure?

                    </button>

                </h2>

                <div id="faq5"
                    class="accordion-collapse collapse"
                    data-bs-parent="#faqAccordion">

                    <div class="accordion-body">

                        Yes. Your information is securely managed
                        and only authorized users can access it.

                    </div>

                </div>

            </div>


        </div>

    </div>

</section>




    <!-- ================= FOOTER ================= -->

    <footer class="site-footer" id="footer">

        <div class="container">

            <div class="row gy-4">

                <!-- About -->
                <div class="col-lg-4 col-md-6">

                    <div class="footer-brand">

                        <div class="footer-logo">
                            <i class="fas fa-shield-virus"></i>
                        </div>

                        <div>
                            <h4>COVID<span>Care</span></h4>
                            <p>Test & Vaccination System</p>
                        </div>

                    </div>

                    <p class="footer-text">
                        A secure and reliable healthcare platform for COVID-19
                        testing, vaccination appointments, records and patient care.
                    </p>


                    <div class="footer-social">

                        <a href="#">
                            <i class="fab fa-facebook-f"></i>
                        </a>

                        <a href="#">
                            <i class="fab fa-twitter"></i>
                        </a>

                        <a href="#">
                            <i class="fab fa-linkedin-in"></i>
                        </a>

                        <a href="#">
                            <i class="fab fa-instagram"></i>
                        </a>

                    </div>

                </div>


                <!-- Quick Links -->
                <div class="col-lg-2 col-md-6">

                    <h5 class="footer-title">
                        Quick Links
                    </h5>

                    <ul class="footer-links">

                        <li>
                            <a href="index.php">
                                <i class="fas fa-chevron-right"></i>
                                Home
                            </a>
                        </li>

                        <li>
                            <a href="#about">
                                <i class="fas fa-chevron-right"></i>
                                About Us
                            </a>
                        </li>

                        <li>
                            <a href="#services">
                                <i class="fas fa-chevron-right"></i>
                                Services
                            </a>
                        </li>

                    </ul>

                </div>


                <!-- Services -->
                <div class="col-lg-3 col-md-6">

                    <h5 class="footer-title">
                        Our Services
                    </h5>

                    <ul class="footer-links">

                        <li>
                            <a href="#">
                                <i class="fas fa-syringe"></i>
                                Vaccination
                            </a>
                        </li>

                        <li>
                            <a href="#">
                                <i class="fas fa-vial"></i>
                                COVID-19 Testing
                            </a>
                        </li>

                        <li>
                            <a href="#">
                                <i class="fas fa-calendar-check"></i>
                                Appointments
                            </a>
                        </li>

                        <li>
                            <a href="#">
                                <i class="fas fa-file-medical"></i>
                                Medical Records
                            </a>
                        </li>

                    </ul>

                </div>



                <!-- Contact -->
                <div class="col-lg-3 col-md-6">

                    <h5 class="footer-title">
                        Contact Us
                    </h5>

                    <div class="footer-contact">

                        <p>
                            <i class="fas fa-map-marker-alt"></i>
                            <span>Healthcare Center, Pakistan</span>
                        </p>

                        <p>
                            <i class="fas fa-phone-alt"></i>
                            <span>+92 300 1234567</span>
                        </p>

                        <p>
                            <i class="fas fa-envelope"></i>
                            <span>support@covidcare.com</span>
                        </p>

                        <p>
                            <i class="fas fa-clock"></i>
                            <span>24/7 Healthcare Support</span>
                        </p>

                    </div>

                </div>

            </div>

        </div>


        <div class="footer-bottom">

            <div class="container">

                <div class="footer-bottom-content">

                    <p>
                        © <?php echo date('Y'); ?>
                        <strong>COVIDCare</strong>.
                        All Rights Reserved.
                    </p>

                    <div>
                        <a href="#">Privacy Policy</a>
                        <span>|</span>
                        <a href="#">Terms & Conditions</a>
                    </div>

                </div>

            </div>

        </div>

    </footer>


    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>


    <script>
        function showPassword() {

            const password =
                document.getElementById("password");

            const eye =
                document.getElementById("eye");

            if (password.type === "password") {

                password.type = "text";

                eye.className =
                    "fa-regular fa-eye-slash";

            } else {

                password.type = "password";

                eye.className =
                    "fa-regular fa-eye";

            }
        }
    </script>

    
</body>

</html>