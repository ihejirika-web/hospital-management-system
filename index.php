<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

$medicareRole = $_SESSION['role'] ?? null;
$medicareUserName = $_SESSION['user_name'] ?? '';
$medicareDashboard = [
    'patient' => '/Hospital-Management-System/patient/',
    'doctor'  => '/Hospital-Management-System/doctor/',
    'admin'   => '/Hospital-Management-System/admin/',
];
$medicareIsLoggedIn = isset($medicareRole, $medicareDashboard[$medicareRole]);

// Only show Logout if the project already has a logout endpoint.
$medicareLogoutPath = __DIR__ . '/pages/logout.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>MediCare Hospital | Quality Healthcare & Appointments</title>
    <meta
        name="description"
        content="MediCare connects patients with healthcare professionals for appointments, medical consultations and telemedicine."
    >

    <link rel="stylesheet" href="/Hospital-Management-System/assets/home.css?v=9">




    <script
        src="/Hospital-Management-System/assets/script.js"
        defer
    ></script>
</head>

<body>

    <!-- NAVIGATION -->
    <header class="navbar">
        <a href="#home" class="logo">
            <span class="brand-icon">✚</span>
            MediCare
        </a>

        <button
            class="mobile-menu-btn"
            id="mobile-menu-btn"
            type="button"
            aria-label="Open navigation menu"
            aria-expanded="false"
            aria-controls="main-navigation"
        >
            ☰
        </button>

<nav id="main-navigation">
    <ul class="nav-links">
        <li><a href="#home">Home</a></li>
        <li><a href="#services">Services</a></li>
        <li><a href="#doctors">Doctors</a></li>
        <li><a href="#telemedicine">Telemedicine</a></li>
        <li><a href="#contact">Contact</a></li>

        <?php if ($medicareIsLoggedIn): ?>
            <li>
                <a href="<?= htmlspecialchars($medicareDashboard[$medicareRole], ENT_QUOTES, 'UTF-8') ?>">
                    <?= $medicareRole === 'admin' ? 'Admin Dashboard' : ucfirst($medicareRole) . ' Dashboard' ?>
                </a>
            </li>
            <?php if (is_file($medicareLogoutPath)): ?>
                <li><a class="nav-login" href="/Hospital-Management-System/pages/logout.php">Logout</a></li>
            <?php endif; ?>
        <?php else: ?>
            <li>
                <a href="/Hospital-Management-System/pages/login.php" class="nav-login">Login</a>
            </li>
            <li>
                <a href="/Hospital-Management-System/pages/register.php" class="nav-register">Register</a>
            </li>
        <?php endif; ?>

        <li><a href="#appointment" class="nav-book">Book Appointment</a></li>
    </ul>
</nav>


    </header>


    <main>

        <!-- HERO SECTION -->
        <section class="professional-hero" id="home">

            <div class="hero-background-shape"></div>

            <div class="professional-hero-content">

                <span class="eyebrow">
                    YOUR TRUSTED HEALTHCARE PARTNER
                </span>

                <h1>
                    Your Health,<br>
                    <span>Our Priority.</span>
                </h1>

                <p class="hero-description">
                    Quality healthcare for you and your family.
                    Book appointments, connect with qualified doctors,
                    and explore convenient online consultations.
                </p>

                <div class="hero-actions">
                    <a href="#appointment" class="primary-button">
                        Book Appointment <span>→</span>
                    </a>

                    <a href="#services" class="secondary-button">
                        Explore Services
                    </a>
                </div>

                <div class="hero-trust-row">

                    <div class="trust-item">
                        <span class="trust-icon">✚</span>
                        <div>
                            <strong>Patient-Focused</strong>
                            <small>Care that matters</small>
                        </div>
                    </div>

                    <div class="trust-item">
                        <span class="trust-icon">✓</span>
                        <div>
                            <strong>Easy Booking</strong>
                            <small>Simple appointments</small>
                        </div>
                    </div>

                    <div class="trust-item">
                        <span class="trust-icon">♡</span>
                        <div>
                            <strong>Better Access</strong>
                            <small>Healthcare support</small>
                        </div>
                    </div>

                </div>

            </div>


            <!-- DOCTOR IMAGE -->
            <div class="professional-hero-image">

                <!-- Main doctor photo stays in the hero -->
                <img
                    src="https://images.unsplash.com/photo-1612349317150-e413f6a5b16d?auto=format&fit=crop&w=1000&q=85"
                    alt="Doctor wearing a white medical coat and stethoscope"
                    fetchpriority="high"
                >

                <div class="floating-doctor-card">
                    <span class="floating-icon">✚</span>
                    <div>
                        <strong>Healthcare You Can Trust</strong>
                        <small>Your wellbeing comes first</small>
                    </div>
                </div>

                <div class="floating-support-card">
                    <span>♡</span>
                    <div>
                        <strong>Here for You</strong>
                        <small>Every step of the way</small>
                    </div>
                </div>

                <!-- Floating hospital building image -->
                <a
                    href="#services"
                    aria-label="Explore MediCare hospital services"
                    style="
                        position: absolute;
                        z-index: 5;
                        right: -18px;
                        bottom: -34px;
                        display: block;
                        width: min(58%, 310px);
                        padding: 8px;
                        border: 1px solid rgba(255,255,255,0.9);
                        border-radius: 18px;
                        background: rgba(255,255,255,0.92);
                        box-shadow: 0 18px 45px rgba(18, 45, 75, 0.22);
                        text-decoration: none;
                        animation: hospitalFloat 4.5s ease-in-out infinite;
                        backdrop-filter: blur(10px);
                    "
                >
                    <img
                        src="https://images.unsplash.com/photo-1587351021759-3e566b6af7cc?auto=format&fit=crop&w=800&q=85"
                        alt="Modern hospital building"
                        loading="lazy"
                        style="
                            display: block;
                            width: 100%;
                            height: 145px;
                            object-fit: cover;
                            border-radius: 12px;
                        "
                    >
                    <span style="
                        display: flex;
                        align-items: center;
                        gap: 8px;
                        padding: 11px 8px 5px;
                        color: #17324d;
                        font-size: 13px;
                        font-weight: 700;
                    ">
                        <span style="
                            display: inline-flex;
                            align-items: center;
                            justify-content: center;
                            width: 28px;
                            height: 28px;
                            flex-shrink: 0;
                            border-radius: 9px;
                            color: #fff;
                            background: #168b83;
                            font-size: 17px;
                        ">✚</span>
                        Modern Hospital Care
                    </span>
                </a>

                <style>
                    @keyframes hospitalFloat {
                        0%, 100% { transform: translateY(0); }
                        50% { transform: translateY(-10px); }
                    }

                    @media (max-width: 600px) {
                        .professional-hero-image a[aria-label="Explore MediCare hospital services"] {
                            right: -5px !important;
                            bottom: -24px !important;
                            width: 58% !important;
                        }

                        .professional-hero-image a[aria-label="Explore MediCare hospital services"] img {
                            height: 95px !important;
                        }

                        .professional-hero-image a[aria-label="Explore MediCare hospital services"] span {
                            font-size: 11px !important;
                        }
                    }

                    @media (prefers-reduced-motion: reduce) {
                        .professional-hero-image a[aria-label="Explore MediCare hospital services"] {
                            animation: none !important;
                        }
                    }
                </style>

            </div>


            <!-- HERO APPOINTMENT FORM -->
            <div class="hero-appointment-card" id="appointment">

                <div class="appointment-card-heading">
                    <span class="appointment-heading-icon">✚</span>

                    <div>
                        <h2>Book an Appointment</h2>
                        <p>Take the first step towards better health.</p>
                    </div>
                </div>

                <form
                    class="appointment-form"
                    action="/Hospital-Management-System/pages/book-appointment.php"
                    method="POST"
                >

                    <div class="form-group">
                        <label for="name">Full Name</label>

                        <input
                            type="text"
                            id="name"
                            name="name"
                            placeholder="Enter your full name"
                            autocomplete="name"
                            required
                        >
                    </div>


                    <div class="form-group">
                        <label for="email">Email Address</label>

                        <input
                            type="email"
                            id="email"
                            name="email"
                            placeholder="Enter your email"
                            autocomplete="email"
                            required
                        >
                    </div>


                    <div class="form-group">
                        <label for="phone">Phone Number</label>

                        <input
                            type="tel"
                            id="phone"
                            name="phone"
                            placeholder="Enter your phone number"
                            autocomplete="tel"
                            required
                        >
                    </div>


                    <div class="form-group">
                        <label for="doctor">Select Doctor</label>

                        <select id="doctor" name="doctor" required>
                            <option value="">Choose a doctor</option>

                            <option value="john-smith">
                                Dr. John Smith - General Physician
                            </option>

                            <option value="sarah-johnson">
                                Dr. Sarah Johnson - Cardiologist
                            </option>

                            <option value="michael-brown">
                                Dr. Michael Brown - Dermatologist
                            </option>
                        </select>
                    </div>


                    <div class="form-row">

                        <div class="form-group">
                            <label for="date">Appointment Date</label>

                            <input
                                type="date"
                                id="date"
                                name="date"
                                required
                            >
                        </div>

                        <div class="form-group">
                            <label for="time">Appointment Time</label>

                            <input
                                type="time"
                                id="time"
                                name="time"
                                required
                            >
                        </div>

                    </div>


                    <div class="form-group">
                        <label for="reason">Reason for Visit</label>

                        <textarea
                            id="reason"
                            name="reason"
                            rows="2"
                            placeholder="Briefly describe your reason for consultation"
                            required
                        ></textarea>
                    </div>


                    <button type="submit" class="appointment-btn">
                        Book Appointment <span>→</span>
                    </button>

                    <p class="form-note">
                        Please provide accurate contact information.
                    </p>

                </form>

            </div>

        </section>


        <!-- SERVICES -->
        <section class="services professional-section" id="services">

            <div class="section-heading">
                <span class="eyebrow">WHAT WE OFFER</span>

                <h2>
                    Healthcare Designed <span>For You</span>
                </h2>

                <p class="section-description">
                    Access convenient healthcare services designed
                    around your needs.
                </p>
            </div>


            <div class="services-container">

                <article class="service-card">
                    <div class="service-icon service-blue">♙</div>

                    <h3>Doctor Consultation</h3>

                    <p>
                        Connect with healthcare professionals for
                        consultations and medical guidance.
                    </p>

                    <a href="#doctors" class="service-link">
                        Meet Our Doctors →
                    </a>
                </article>


                <article class="service-card">
                    <div class="service-icon service-green">▦</div>

                    <h3>Book an Appointment</h3>

                    <p>
                        Request an appointment with a doctor
                        at a time that suits your needs.
                    </p>

                    <a href="#appointment" class="service-link">
                        Book Now →
                    </a>
                </article>


                <article class="service-card">
                    <div class="service-icon service-purple">▣</div>

                    <h3>Telemedicine</h3>

                    <p>
                        Explore online consultations and convenient
                        access to healthcare services.
                    </p>

                    <a href="#telemedicine" class="service-link">
                        Learn More →
                    </a>
                </article>


                <article class="service-card">
                    <div class="service-icon service-orange">▤</div>

                    <h3>Medical Records</h3>

                    <p>
                        Our system can support secure access to
                        patient records and medical information.
                    </p>

                    <a
                        href="/Hospital-Management-System/pages/login.php"
                        class="service-link"
                    >
                        Patient Login →
                    </a>
                </article>

            </div>

        </section>


        <!-- DOCTORS -->
        <section class="doctors professional-section" id="doctors">

            <div class="section-heading">
                <span class="eyebrow">OUR MEDICAL TEAM</span>

                <h2>
                    Meet Our <span>Doctors</span>
                </h2>

                <p class="section-description">
                    Discover our featured doctors and their
                    areas of medical practice.
                </p>
            </div>


            <div class="doctors-container">

                <article class="doctor-card">

                    <div class="doctor-photo">
                        <img
                            src="https://images.unsplash.com/photo-1612349317150-e413f6a5b16d?auto=format&fit=crop&w=600&q=80"
                            alt="Doctor representing general medicine"
                            loading="lazy"
                        >
                    </div>

                    <div class="doctor-details">
                        <span class="doctor-status">Featured Doctor</span>

                        <h3>Dr. John Smith</h3>

                        <p class="doctor-specialty">
                            General Physician
                        </p>

                        <p>
                            General medical consultation and
                            patient care.
                        </p>

                        <a
                            href="#appointment"
                            class="doctor-btn"
                        >
                            Book a Visit →
                        </a>
                    </div>

                </article>


                <article class="doctor-card">

                    <div class="doctor-photo">
                        <img
                            src="https://images.unsplash.com/photo-1559839734-2b71ea197ec2?auto=format&fit=crop&w=600&q=80"
                            alt="Doctor representing cardiology"
                            loading="lazy"
                        >
                    </div>

                    <div class="doctor-details">
                        <span class="doctor-status">Featured Doctor</span>

                        <h3>Dr. Sarah Johnson</h3>

                        <p class="doctor-specialty">
                            Cardiologist
                        </p>

                        <p>
                            Heart health, cardiovascular assessment
                            and medical care.
                        </p>

                        <a
                            href="#appointment"
                            class="doctor-btn"
                        >
                            Book a Visit →
                        </a>
                    </div>

                </article>


                <article class="doctor-card">

                    <div class="doctor-photo">
                        <img
                            src="https://images.unsplash.com/photo-1612531386530-97286d97c2d2?auto=format&fit=crop&w=600&q=80"
                            alt="Doctor representing dermatology"
                            loading="lazy"
                        >
                    </div>

                    <div class="doctor-details">
                        <span class="doctor-status">Featured Doctor</span>

                        <h3>Dr. Michael Brown</h3>

                        <p class="doctor-specialty">
                            Dermatologist
                        </p>

                        <p>
                            Skin health, dermatological assessment
                            and patient care.
                        </p>

                        <a
                            href="#appointment"
                            class="doctor-btn"
                        >
                            Book a Visit →
                        </a>
                    </div>

                </article>

            </div>

        </section>


        <!-- HEALTHCARE STATISTICS -->
        <section class="healthcare-stats">

            <div class="stat-item">
                <span class="stat-icon">♡</span>
                <div>
                    <strong>Patient-Focused</strong>
                    <p>Care built around people</p>
                </div>
            </div>

            <div class="stat-item">
                <span class="stat-icon">✚</span>
                <div>
                    <strong>Healthcare Services</strong>
                    <p>Multiple care options</p>
                </div>
            </div>

            <div class="stat-item">
                <span class="stat-icon">▣</span>
                <div>
                    <strong>Online Access</strong>
                    <p>Convenient digital services</p>
                </div>
            </div>

            <div class="stat-item">
                <span class="stat-icon">✓</span>
                <div>
                    <strong>Simple Booking</strong>
                    <p>Easy appointment requests</p>
                </div>
            </div>

        </section>


        <!-- TELEMEDICINE -->
        <section class="telemedicine-section" id="telemedicine">

            <div class="telemedicine-content">

                <span class="eyebrow">HEALTHCARE FROM ANYWHERE</span>

                <h2>
                    Connect With Healthcare,
                    <span>Wherever You Are.</span>
                </h2>

                <p>
                    Discover the convenience of telemedicine.
                    Our goal is to make healthcare services easier
                    to access through digital technology.
                </p>

                <a href="#appointment" class="telemedicine-btn">
                    Explore Appointment Options →
                </a>

            </div>


            <div class="telemedicine-image">
                <img
                    src="https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?auto=format&fit=crop&w=900&q=85"
                    alt="Healthcare professional providing medical care"
                    loading="lazy"
                >
            </div>

        </section>


        <!-- CONTACT -->
        <section class="contact professional-section" id="contact">

            <div class="section-heading">
                <span class="eyebrow">GET IN TOUCH</span>

                <h2>
                    We're Here <span>To Help</span>
                </h2>

                <p class="section-description">
                    Contact us for information about our healthcare services.
                </p>
            </div>


            <div class="contact-container">

                <article class="contact-card">
                    <div class="contact-icon">⌖</div>

                    <h3>Our Location</h3>

                    <p>
                        123 Health Street, Abuja, Nigeria
                    </p>
                </article>


                <article class="contact-card">
                    <div class="contact-icon">☎</div>

                    <h3>Phone</h3>

                    <p>+234 706 564 8075</p>
                </article>


                <article class="contact-card">
                    <div class="contact-icon">✉</div>

                    <h3>Email</h3>

                    <p>info@medicare.com</p>
                </article>

            </div>

        </section>

    </main>


    <!-- FOOTER -->
    <footer class="footer professional-footer">

        <a href="#home" class="footer-logo">
            ✚ MediCare
        </a>

        <p>
            Your Health, Our Priority.
        </p>

        <p class="copyright">
            &copy; 2026 MediCare Hospital Management System.
            All Rights Reserved.
        </p>

    </footer>

</body>

</html>