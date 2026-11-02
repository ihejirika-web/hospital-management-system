<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id'], $_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../pages/login.php');
    exit;
}

function e($value): string {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}

$notice = '';
$error = '';
$allowedStatuses = ['Pending', 'Approved', 'Completed', 'Cancelled'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['appointment_id'], $_POST['status'])) {
    $appointmentId = filter_var($_POST['appointment_id'], FILTER_VALIDATE_INT);
    $newStatus = (string) $_POST['status'];

    if (!$appointmentId || !in_array($newStatus, $allowedStatuses, true)) {
        $error = 'Invalid appointment update request.';
    } else {
        $update = $conn->prepare('UPDATE appointments SET status = ? WHERE id = ?');
        if (!$update) {
            $error = 'Could not prepare the appointment update.';
        } else {
            $update->bind_param('si', $newStatus, $appointmentId);
            if ($update->execute()) {
                $notice = $update->affected_rows > 0
                    ? 'Appointment status updated.'
                    : 'No change was made to that appointment.';
            } else {
                $error = 'Unable to update appointment status.';
            }
            $update->close();
        }
    }
}

$sql = "SELECT a.id, a.appointment_date, a.appointment_time, a.reason, a.status,
               p.full_name AS patient_name, p.email AS patient_email,
               d.full_name AS doctor_name, d.specialty
        FROM appointments a
        INNER JOIN patients p ON p.id = a.patient_id
        INNER JOIN doctors d ON d.id = a.doctor_id
        ORDER BY a.appointment_date DESC, a.appointment_time DESC, a.id DESC";
$result = $conn->query($sql);
if (!$result && !$error) {
    $error = 'Unable to load appointments. Please check the database tables.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Appointments | MediCare</title>
<link rel="stylesheet" href="../assets/admin.css">
<style>
.page-intro { margin-bottom:22px; }
.page-intro p { color:#718096; font-size:13px; }
.content-card { margin-bottom:24px; }
.status-form { display:flex; gap:7px; align-items:center; }
.status-form select,.status-form button { padding:7px 9px; border:1px solid #dbe4ec; border-radius:7px; }
.status-form button { background:#1769aa; color:#fff; border:0; cursor:pointer; }
.notice { padding:13px 16px; border-radius:9px; margin-bottom:18px; }
.notice.success { background:#dcfce7; color:#166534; }
.notice.error { background:#fee2e2; color:#991b1b; }
.search-box { width:100%; max-width:340px; padding:11px 13px; border:1px solid #dbe4ec; border-radius:8px; margin:0 0 18px; }
.table-wrapper { overflow-x:auto; }
</style>
</head>
<body>
<div class="dashboard">
<?php require __DIR__ . '/sidebar.php'; ?>
<main class="main-content">
<header class="topbar">
<div><p class="topbar-greeting">MediCare administration</p><h1>Appointments</h1></div>
</header>

<div class="page-intro"><p>Review appointments and update their status.</p></div>
<?php if ($notice): ?><div class="notice success"><?= e($notice) ?></div><?php endif; ?>
<?php if ($error): ?><div class="notice error"><?= e($error) ?></div><?php endif; ?>
<section class="content-card">
<div class="section-header"><div><h2>All Appointments</h2><p>Appointments recorded in MediCare</p></div>
<span class="section-badge"><?= $result ? (int)$result->num_rows : 0 ?> records</span></div>
<div class="table-wrapper"><table>
<thead><tr><th>ID</th><th>Patient</th><th>Doctor</th><th>Date</th><th>Time</th><th>Reason</th><th>Status</th><th>Update</th></tr></thead>
<tbody>
<?php if ($result && $result->num_rows): ?>
<?php while ($row = $result->fetch_assoc()): ?>
<tr>
<td>#<?= (int)$row['id'] ?></td>
<td><strong><?= e($row['patient_name']) ?></strong><br><?= e($row['patient_email']) ?></td>
<td><?= e($row['doctor_name']) ?><br><?= e($row['specialty']) ?></td>
<td><?= e($row['appointment_date']) ?></td>
<td><?= e(substr((string)$row['appointment_time'], 0, 5)) ?></td>
<td><?= e($row['reason']) ?></td>
<td><?= e($row['status']) ?></td>
<td><form class="status-form" method="post">
<input type="hidden" name="appointment_id" value="<?= (int)$row['id'] ?>">
<select name="status" aria-label="New status">
<?php foreach ($allowedStatuses as $status): ?>
<option value="<?= e($status) ?>" <?= $row['status'] === $status ? 'selected' : '' ?>><?= e($status) ?></option>
<?php endforeach; ?>
</select><button type="submit">Save</button></form></td>
</tr>
<?php endwhile; ?>
<?php else: ?><tr><td colspan="8">No appointments found.</td></tr><?php endif; ?>
</tbody></table></div></section>
<footer class="dashboard-footer"><p>&copy; <?= date('Y') ?> MediCare Hospital Management System.</p></footer>
</main></div></body></html>
