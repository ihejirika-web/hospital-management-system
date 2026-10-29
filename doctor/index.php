<?php
session_start();

require_once "../config/database.php";

// Allow only logged-in doctors to access this dashboard.
if (
    !isset($_SESSION["user_id"]) ||
    !isset($_SESSION["role"]) ||
    $_SESSION["role"] !== "doctor"
) {
    header("Location: ../pages/login.php");
    exit;
}

$doctorId = (int) $_SESSION["user_id"];
$doctorName = $_SESSION["user_name"] ?? "Doctor";

function escapeHTML($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
}

// Count this doctor's appointments.
$totalAppointments = 0;
$pendingAppointments = 0;
$approvedAppointments = 0;
$completedAppointments = 0;

$countSql = "
    SELECT
        COUNT(*) AS total,
        SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) AS pending,
        SUM(CASE WHEN status = 'Approved' THEN 1 ELSE 0 END) AS approved,
        SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) AS completed
    FROM appointments
    WHERE doctor_id = ?
";

$countStmt = $conn->prepare($countSql);
$countStmt->bind_param("i", $doctorId);
$countStmt->execute();

$countResult = $countStmt->get_result();
$counts = $countResult->fetch_assoc();

if ($counts) {
    $totalAppointments = (int) ($counts["total"] ?? 0);
    $pendingAppointments = (int) ($counts["pending"] ?? 0);
    $approvedAppointments = (int) ($counts["approved"] ?? 0);
    $completedAppointments = (int) ($counts["completed"] ?? 0);
}

$countStmt->close();

// Retrieve appointments assigned to this doctor.
$sql = "
    SELECT
        appointments.id,
        appointments.appointment_date,
        appointments.appointment_time,
        appointments.reason,
        appointments.status,
        patients.full_name AS patient_name,
        patients.email AS patient_email,
        patients.phone AS patient_phone
    FROM appointments
    INNER JOIN patients
        ON appointments.patient_id = patients.id
    WHERE appointments.doctor_id = ?
    ORDER BY appointments.appointment_date DESC,
             appointments.appointment_time DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $doctorId);
$stmt->execute();

$result = $stmt->get_result();

$appointments = [];

while ($row = $result->fetch_assoc()) {
    $appointments[] = $row;
}

$stmt->close();
?>

<!DOCTYPE html>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Doctor Dashboard | MediCare</title>

<style>
    * {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }

    body {
        font-family: Arial, Helvetica, sans-serif;
        background: #f3f7fc;
        color: #243247;
        line-height: 1.6;
    }

    .topbar {
        background: #123b66;
        color: white;
        padding: 18px 6%;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 15px;
        flex-wrap: wrap;
    }

    .logo {
        font-size: 25px;
        font-weight: bold;
    }

    .logo span {
        color: #52d6bd;
    }

    .topbar-right {
        display: flex;
        align-items: center;
        gap: 18px;
    }

    .logout {
        background: #ffffff;
        color: #123b66;
        padding: 9px 16px;
        text-decoration: none;
        border-radius: 7px;
        font-weight: bold;
        transition: 0.25s ease;
    }

    .logout:hover {
        background: #52d6bd;
        transform: translateY(-2px);
    }

    .container {
        width: 90%;
        max-width: 1200px;
        margin: 35px auto;
    }

    .welcome {
        background: linear-gradient(120deg, #1c5284, #24a68d);
        color: white;
        padding: 30px;
        border-radius: 14px;
        margin-bottom: 28px;
        box-shadow: 0 8px 24px rgba(25, 75, 120, 0.12);
    }

    .welcome h1 {
        margin-bottom: 8px;
        font-size: 28px;
    }

    .welcome p {
        color: #e7f6ff;
    }

    .stats {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 18px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: white;
        padding: 23px;
        border-radius: 12px;
        border: 1px solid #e4ebf3;
        box-shadow: 0 4px 15px rgba(30, 60, 90, 0.04);
        transition: transform 0.25s ease, box-shadow 0.25s ease;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 25px rgba(30, 60, 90, 0.10);
    }

    .stat-card p {
        color: #68788c;
        font-size: 14px;
    }

    .stat-card h2 {
        color: #174d7b;
        font-size: 31px;
        margin-top: 8px;
    }

    .section-card {
        background: white;
        border-radius: 13px;
        padding: 25px;
        box-shadow: 0 4px 18px rgba(30, 60, 90, 0.05);
        margin-bottom: 25px;
    }

    .section-heading {
        margin-bottom: 18px;
        color: #173f65;
    }

    .table-wrapper {
        width: 100%;
        overflow-x: auto;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        min-width: 760px;
    }

    th, td {
        padding: 14px 12px;
        text-align: left;
        border-bottom: 1px solid #e9eef4;
        font-size: 14px;
    }

    th {
        background: #f5f8fc;
        color: #52647a;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.4px;
    }

    tbody tr {
        transition: background 0.2s ease;
    }

    tbody tr:hover {
        background: #f7fbff;
    }

    .status {
        display: inline-block;
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: bold;
        white-space: nowrap;
    }

    .pending {
        background: #fff1cb;
        color: #8a6200;
    }

    .approved {
        background: #d9f5e8;
        color: #14784d;
    }

    .completed {
        background: #dceaff;
        color: #2356a0;
    }

    .rejected, .cancelled {
        background: #ffe0e0;
        color: #a52b2b;
    }

    .empty-state {
        text-align: center;
        padding: 35px 15px;
        color: #738196;
    }

    footer {
        text-align: center;
        padding: 22px;
        color: #78869a;
        font-size: 13px;
    }

    @media (max-width: 850px) {
        .stats {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (max-width: 520px) {
        .container {
            width: 94%;
            margin-top: 20px;
        }

        .welcome {
            padding: 22px;
        }

        .welcome h1 {
            font-size: 23px;
        }

        .stats {
            grid-template-columns: 1fr;
        }

        .section-card {
            padding: 16px;
        }

        .topbar {
            padding: 16px 4%;
        }
    }
</style>

</head>

<body>

<header class="topbar">
    <div class="logo">✚ Medi<span>Care</span></div>

<div class="topbar-right">
    <span>Doctor Portal</span>
    <a class="logout" href="../pages/logout.php">Logout</a>
</div>

</header>

<main class="container">

```
<section class="welcome">
    <h1>Welcome, Dr. <?= escapeHTML($doctorName) ?>!</h1>
    <p>
        Manage your appointments and review your scheduled patient visits
        from your personal dashboard.
    </p>
</section>

<section class="stats">

    <article class="stat-card">
        <p>Total Appointments</p>
        <h2><?= $totalAppointments ?></h2>
    </article>

    <article class="stat-card">
        <p>Pending Requests</p>
        <h2><?= $pendingAppointments ?></h2>
    </article>

    <article class="stat-card">
        <p>Approved Appointments</p>
        <h2><?= $approvedAppointments ?></h2>
    </article>

    <article class="stat-card">
        <p>Completed Appointments</p>
        <h2><?= $completedAppointments ?></h2>
    </article>

</section>

<section class="section-card">

    <h2 class="section-heading">My Patient Appointments</h2>

    <?php if (count($appointments) > 0): ?>

        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>Patient</th>
                        <th>Contact</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Reason</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($appointments as $appointment): ?>
                        <?php
                        $statusClass = strtolower(
                            preg_replace(
                                '/[^a-zA-Z0-9_-]/',
                                '',
                                $appointment["status"]
                            )
                        );
                        ?>

                        <tr>
                            <td>
                                <?= escapeHTML($appointment["patient_name"]) ?>
                            </td>

                            <td>
                                <?= escapeHTML($appointment["patient_email"]) ?>
                                <br>
                                <?= escapeHTML($appointment["patient_phone"]) ?>
                            </td>

                            <td>
                                <?= escapeHTML($appointment["appointment_date"]) ?>
                            </td>

                            <td>
                                <?= escapeHTML($appointment["appointment_time"]) ?>
                            </td>

                            <td>
                                <?= escapeHTML($appointment["reason"] ?: "Not provided") ?>
                            </td>

                            <td>
                                <span class="status <?= escapeHTML($statusClass) ?>">
                                    <?= escapeHTML($appointment["status"]) ?>
                                </span>
                            </td>
                        </tr>

                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    <?php else: ?>

        <div class="empty-state">
            <h3>No appointments yet</h3>
            <p>
                When patients book appointments with you,
                they will appear here.
            </p>
        </div>

    <?php endif; ?>

</section>

</main>

<footer>
    &copy; <?= date("Y") ?> MediCare Hospital Management System.
    All rights reserved.
</footer>

</body>
</html>
