<?php
session_start();

require_once "../config/database.php";

// Protect the patient dashboard.
if (
    !isset($_SESSION["user_id"]) ||
    !isset($_SESSION["role"]) ||
    $_SESSION["role"] !== "patient"
) {
    header("Location: ../pages/login.php");
    exit;
}

// Get the logged-in patient's information.
$patientId = (int) $_SESSION["user_id"];
$patientName = $_SESSION["user_name"] ?? "Patient";

$error = "";
$appointments = [];

// Retrieve this patient's appointments.
$sql = "
    SELECT
        appointments.id,
        appointments.appointment_date,
        appointments.appointment_time,
        appointments.reason,
        appointments.status,
        doctors.full_name AS doctor_name,
        doctors.specialty
    FROM appointments
    INNER JOIN doctors
        ON appointments.doctor_id = doctors.id
    WHERE appointments.patient_id = ?
    ORDER BY appointments.appointment_date DESC,
             appointments.appointment_time DESC
";

$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->bind_param("i", $patientId);

    if ($stmt->execute()) {
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            $appointments[] = $row;
        }
    } else {
        $error = "Unable to load your appointments. Please try again.";
    }

    $stmt->close();
} else {
    $error = "Unable to load your appointments. Please try again.";
}

// Safely display text from the database.
function escapeHTML($value)
{
    return htmlspecialchars(
        (string) ($value ?? ""),
        ENT_QUOTES,
        "UTF-8"
    );
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Patient Dashboard | MediCare</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f3f7fb;
            color: #203047;
            line-height: 1.6;
        }

        header {
            background: #ffffff;
            padding: 20px 6%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.05);
        }

        .logo {
            font-size: 25px;
            font-weight: bold;
            color: #1769aa;
        }

        .logo span {
            color: #20a58a;
        }

        .logout {
            background: #1769aa;
            color: white;
            text-decoration: none;
            padding: 11px 20px;
            border-radius: 8px;
            white-space: nowrap;
        }

        .logout:hover {
            background: #105387;
        }

        .container {
            width: 88%;
            max-width: 1100px;
            margin: 40px auto;
        }

        .welcome {
            background: linear-gradient(120deg, #1769aa, #20a58a);
            color: white;
            padding: 35px;
            border-radius: 18px;
            margin-bottom: 30px;
        }

        .welcome h1 {
            margin-bottom: 12px;
            font-size: 30px;
        }

        .welcome p {
            line-height: 1.7;
        }

        .cards {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 22px;
            margin-bottom: 35px;
        }

        .card {
            background: white;
            padding: 28px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(30, 60, 90, 0.06);
        }

        .card h2 {
            font-size: 20px;
            margin-bottom: 12px;
        }

        .card p {
            color: #718096;
            line-height: 1.7;
            margin-bottom: 20px;
        }

        .button {
            display: inline-block;
            background: #1769aa;
            color: white;
            text-decoration: none;
            padding: 12px 18px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }

        .button:hover {
            background: #105387;
        }

        .secondary {
            background: #e8f5f2;
            color: #16836f;
        }

        .secondary:hover {
            background: #d4eee7;
        }

        /* Appointments section */

        .appointments-section {
            background: white;
            padding: 28px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(30, 60, 90, 0.06);
            margin-bottom: 30px;
        }

        .section-heading {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            margin-bottom: 22px;
            flex-wrap: wrap;
        }

        .section-heading h2 {
            font-size: 23px;
        }

        .appointment-count {
            background: #e8f5f2;
            color: #16836f;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: bold;
        }

        .table-wrapper {
            width: 100%;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 700px;
        }

        th,
        td {
            padding: 15px 12px;
            text-align: left;
            border-bottom: 1px solid #edf1f5;
            font-size: 14px;
        }

        th {
            background: #f7fafc;
            color: #536477;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        tbody tr:hover {
            background: #fafcff;
        }

        .status {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: capitalize;
        }

        .status-pending {
            background: #fff4d6;
            color: #946200;
        }

        .status-approved {
            background: #d9f7e8;
            color: #16794b;
        }

        .status-confirmed {
            background: #d9f7e8;
            color: #16794b;
        }

        .status-completed {
            background: #dcecff;
            color: #1759a6;
        }

        .status-cancelled {
            background: #ffe2e2;
            color: #a52626;
        }

        .empty-state {
            background: #f7fafc;
            text-align: center;
            padding: 35px 20px;
            border-radius: 12px;
        }

        .empty-state h3 {
            margin-bottom: 8px;
            color: #334155;
        }

        .empty-state p {
            color: #718096;
            margin-bottom: 18px;
        }

        .error-message {
            background: #fff0f0;
            color: #a52626;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        footer {
            text-align: center;
            padding: 25px;
            color: #718096;
            font-size: 14px;
        }

        @media (max-width: 650px) {
            .cards {
                grid-template-columns: 1fr;
            }

            .container {
                width: 92%;
                margin: 25px auto;
            }

            .welcome {
                padding: 25px;
            }

            .welcome h1 {
                font-size: 25px;
            }

            header {
                padding: 18px 5%;
            }

            .appointments-section {
                padding: 20px 15px;
            }

            .section-heading h2 {
                font-size: 20px;
            }
        }
    </style>
</head>

<body>

<header>
    <div class="logo">
        ✚ Medi<span>Care</span>
    </div>

    <a class="logout" href="../pages/logout.php">
        Log Out
    </a>
</header>

<main class="container">

    <!-- Welcome section -->

    <section class="welcome">
        <h1>
            Welcome,
            <?= escapeHTML($patientName) ?>!
        </h1>

        <p>
            Welcome to your MediCare patient dashboard.
            Manage your appointments and access your healthcare
            services from one convenient place.
        </p>
    </section>

    <!-- Main dashboard cards -->

    <section class="cards">

        <div class="card">
            <h2>📅 Book an Appointment</h2>

            <p>
                Schedule an appointment with a doctor
                and take the next step toward better health.
            </p>

            <a
                class="button"
                href="../index.php#appointment"
            >
                Book Appointment
            </a>
        </div>

        <div class="card">
            <h2>🩺 Find Our Doctors</h2>

            <p>
                Explore our healthcare services and find
                a doctor who can help with your needs.
            </p>

            <a
                class="button secondary"
                href="../index.php#doctors"
            >
                View Doctors
            </a>
        </div>

    </section>

    <!-- Patient appointment history -->

    <section class="appointments-section">

        <div class="section-heading">
            <h2>📋 My Appointments</h2>

            <span class="appointment-count">
                <?= count($appointments) ?>
                appointment<?= count($appointments) === 1 ? "" : "s" ?>
            </span>
        </div>

        <?php if ($error !== ""): ?>

            <div class="error-message">
                <?= escapeHTML($error) ?>
            </div>

        <?php elseif (count($appointments) === 0): ?>

            <div class="empty-state">
                <h3>No Appointments Yet</h3>

                <p>
                    You have not booked any appointments.
                    Your bookings will appear here.
                </p>

                <a
                    class="button"
                    href="../index.php#appointment"
                >
                    Book Your First Appointment
                </a>
            </div>

        <?php else: ?>

            <div class="table-wrapper">

                <table>
                    <thead>
                        <tr>
                            <th>Doctor</th>
                            <th>Specialty</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Reason</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php foreach ($appointments as $appointment): ?>

                        <?php
                        $status = strtolower(
                            trim($appointment["status"] ?? "pending")
                        );

                        $allowedStatuses = [
                            "pending",
                            "approved",
                            "confirmed",
                            "completed",
                            "cancelled"
                        ];

                        if (!in_array($status, $allowedStatuses, true)) {
                            $status = "pending";
                        }

                        $date = $appointment["appointment_date"];
                        $time = $appointment["appointment_time"];

                        $formattedDate = $date
                            ? date("d M Y", strtotime($date))
                            : "Not available";

                        $formattedTime = $time
                            ? date("h:i A", strtotime($time))
                            : "Not available";
                        ?>

                        <tr>
                            <td>
                                <?= escapeHTML($appointment["doctor_name"]) ?>
                            </td>

                            <td>
                                <?= escapeHTML($appointment["specialty"]) ?>
                            </td>

                            <td>
                                <?= escapeHTML($formattedDate) ?>
                            </td>

                            <td>
                                <?= escapeHTML($formattedTime) ?>
                            </td>

                            <td>
                                <?= escapeHTML($appointment["reason"]) ?>
                            </td>

                            <td>
                                <span class="status status-<?= escapeHTML($status) ?>">
                                    <?= escapeHTML($status) ?>
                                </span>
                            </td>
                        </tr>

                    <?php endforeach; ?>

                    </tbody>
                </table>

            </div>

        <?php endif; ?>

    </section>

</main>

<footer>
    &copy; <?= date("Y") ?> MediCare. All rights reserved.
</footer>

</body>
</html>

