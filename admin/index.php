<?php
session_start();

require_once __DIR__ . '/../config/database.php';

// Protect the dashboard: only authenticated administrators may enter.
if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'admin'
) {
    header('Location: ../pages/login.php');
    exit;
}

// Count records safely.
function getCount(mysqli $conn, string $table): int
{
    $allowedTables = ['patients', 'doctors', 'appointments'];

    if (!in_array($table, $allowedTables, true)) {
        return 0;
    }

    $result = $conn->query("SELECT COUNT(*) AS total FROM `$table`");

    if (!$result) {
        return 0;
    }

    return (int) $result->fetch_assoc()['total'];
}

$patientCount = getCount($conn, 'patients');
$doctorCount = getCount($conn, 'doctors');
$appointmentCount = getCount($conn, 'appointments');

// Count pending appointments.
$pendingCount = 0;

$pendingResult = $conn->query(
    "SELECT COUNT(*) AS total
     FROM appointments
     WHERE status = 'Pending'"
);

if ($pendingResult) {
    $pendingCount = (int) $pendingResult->fetch_assoc()['total'];
}

// Fetch recent appointments.
$recentAppointments = $conn->query(
    "SELECT
        a.id,
        p.full_name AS patient_name,
        d.full_name AS doctor_name,
        a.appointment_date,
        a.appointment_time,
        a.reason,
        a.status
     FROM appointments a
     LEFT JOIN patients p ON a.patient_id = p.id
     LEFT JOIN doctors d ON a.doctor_id = d.id
     ORDER BY a.id DESC
     LIMIT 5"
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Dashboard | MediCare</title>

    <link rel="stylesheet" href="../assets/admin.css">
</head>

<body>

<div class="dashboard">

    <!-- SIDEBAR -->
    <aside class="sidebar">

        <a href="../index.php" class="brand">
            <span class="brand-icon">✚</span>
            <span>MediCare</span>
        </a>

        <p class="sidebar-label">MAIN MENU</p>

        <nav class="sidebar-nav">

            <a href="index.php" class="nav-item active">
                <span>▦</span> Dashboard
            </a>

            <a href="#appointments" class="nav-item">
                <span>▣</span> Appointments
            </a>

            <a href="#patients" class="nav-item">
                <span>♙</span> Patients
            </a>

            <a href="#doctors" class="nav-item">
                <span>✚</span> Doctors
            </a>

            <!-- SETTINGS -->
            <a href="settings.php" class="nav-item">
                <span>⚙</span> Settings
            </a>

            <!-- LOG OUT -->
            <a href="../pages/logout.php" class="nav-item">
                <span>↪</span> Log Out
            </a>

        </nav>

        <div class="sidebar-bottom">
            <p>Hospital Management System</p>
            <a href="../index.php">← Back to Homepage</a>
        </div>

    </aside>

    <!-- MAIN CONTENT -->
    <main class="main-content">

        <header class="topbar">
            <div>
                <p class="topbar-greeting">Welcome back!</p>
                <h1>Admin Dashboard</h1>
            </div>

            <div class="admin-profile">
                <div class="profile-avatar">A</div>
                <div>
                    <strong>Administrator</strong>
                    <span>System Overview</span>
                </div>
            </div>
        </header>

        <!-- WELCOME BANNER -->
        <section class="welcome-banner">
            <div>
                <span class="banner-label">MEDICARE MANAGEMENT</span>
                <h2>Your Hospital, One Dashboard.</h2>
                <p>
                    Monitor appointments, patients and doctors
                    from one convenient place.
                </p>
            </div>

            <div class="banner-icon">✚</div>
        </section>

        <!-- STATISTICS -->
        <section class="stats-grid">

            <article class="stat-card">
                <div class="stat-icon blue">♙</div>
                <p>Total Patients</p>
                <h2><?= number_format($patientCount) ?></h2>
                <span class="stat-note">Registered patients</span>
            </article>

            <article class="stat-card">
                <div class="stat-icon green">✚</div>
                <p>Total Doctors</p>
                <h2><?= number_format($doctorCount) ?></h2>
                <span class="stat-note">Registered doctors</span>
            </article>

            <article class="stat-card">
                <div class="stat-icon purple">▣</div>
                <p>Appointments</p>
                <h2><?= number_format($appointmentCount) ?></h2>
                <span class="stat-note">All appointments</span>
            </article>

            <article class="stat-card">
                <div class="stat-icon orange">◷</div>
                <p>Pending</p>
                <h2><?= number_format($pendingCount) ?></h2>
                <span class="stat-note">Awaiting confirmation</span>
            </article>

        </section>

        <!-- RECENT APPOINTMENTS -->
        <section class="content-card" id="appointments">

            <div class="section-header">
                <div>
                    <h2>Recent Appointments</h2>
                    <p>Latest appointment requests in your system.</p>
                </div>

                <span class="section-badge">Latest 5</span>
            </div>

            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>Patient</th>
                            <th>Doctor</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php if ($recentAppointments && $recentAppointments->num_rows > 0): ?>

                        <?php while ($appointment = $recentAppointments->fetch_assoc()): ?>

                            <?php
                            $status = strtolower(
                                trim($appointment['status'] ?? 'pending')
                            );

                            $statusClass = in_array(
                                $status,
                                [
                                    'pending',
                                    'approved',
                                    'confirmed',
                                    'completed',
                                    'cancelled'
                                ],
                                true
                            ) ? $status : 'pending';
                            ?>

                            <tr>
                                <td>
                                    <strong>
                                        <?= htmlspecialchars(
                                            $appointment['patient_name'] ?? 'Unknown patient',
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </strong>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        $appointment['doctor_name'] ?? 'Unassigned',
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        $appointment['appointment_date'] ?? '',
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        $appointment['appointment_time'] ?? '',
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>
                                </td>

                                <td>
                                    <span class="status-badge <?= htmlspecialchars($statusClass, ENT_QUOTES, 'UTF-8') ?>">
                                        <?= htmlspecialchars(
                                            ucfirst($status),
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    </span>
                                </td>
                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>
                            <td colspan="5" class="empty-state">
                                No appointments have been recorded yet.
                            </td>
                        </tr>

                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </section>

        <!-- QUICK LINKS -->
        <section class="quick-links">

            <a href="../index.php#appointment" class="quick-card">
                <span class="quick-icon">＋</span>
                <div>
                    <h3>Book an Appointment</h3>
                    <p>Open the public booking form.</p>
                </div>
                <span class="arrow">→</span>
            </a>

            <a href="#patients" class="quick-card" id="patients">
                <span class="quick-icon green">♙</span>
                <div>
                    <h3>Patient Overview</h3>
                    <p><?= number_format($patientCount) ?> registered patient(s).</p>
                </div>
                <span class="arrow">→</span>
            </a>

            <a href="#doctors" class="quick-card" id="doctors">
                <span class="quick-icon purple">✚</span>
                <div>
                    <h3>Doctor Overview</h3>
                    <p><?= number_format($doctorCount) ?> registered doctor(s).</p>
                </div>
                <span class="arrow">→</span>
            </a>

        </section>

        <footer class="dashboard-footer">
            <p>&copy; <?= date('Y') ?> MediCare Hospital Management System.</p>
            <p>Healthcare management made simpler.</p>
        </footer>

    </main>

</div>

</body>
</html>

