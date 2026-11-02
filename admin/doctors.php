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

$result = $conn->query('SELECT id, full_name, specialty, email, phone, created_at FROM doctors ORDER BY full_name ASC, id ASC');
$error = $result ? '' : 'Unable to load doctors. Please check the database connection and table.';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Doctors | MediCare</title>
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
<div><p class="topbar-greeting">MediCare administration</p><h1>Doctor Management</h1></div>
</header>

<div class="page-intro"><p>View doctors registered in the MediCare system.</p></div>
<?php if ($error): ?><div class="notice error"><?= e($error) ?></div><?php endif; ?>
<input class="search-box" type="search" id="tableSearch" placeholder="Search doctors by name, specialty or email..." aria-label="Search doctors">
<section class="content-card">
<div class="section-header"><div><h2>Registered Doctors</h2><p>Doctor directory and specialties</p></div>
<span class="section-badge"><?= $result ? (int)$result->num_rows : 0 ?> doctors</span></div>
<div class="table-wrapper"><table id="dataTable">
<thead><tr><th>Doctor ID</th><th>Full Name</th><th>Specialty</th><th>Email Address</th><th>Phone</th><th>Registered</th></tr></thead><tbody>
<?php if ($result && $result->num_rows): while ($row = $result->fetch_assoc()): ?>
<tr><td>#<?= (int)$row['id'] ?></td><td><strong><?= e($row['full_name']) ?></strong></td><td><?= e($row['specialty']) ?></td><td><?= e($row['email']) ?></td><td><?= e($row['phone']) ?></td><td><?= e($row['created_at']) ?></td></tr>
<?php endwhile; else: ?><tr><td colspan="6">No registered doctors found.</td></tr><?php endif; ?>
</tbody></table></div></section>
<script>
document.getElementById('tableSearch').addEventListener('input', function () {
 const term = this.value.toLowerCase();
 document.querySelectorAll('#dataTable tbody tr').forEach(row => {
   row.hidden = !row.innerText.toLowerCase().includes(term);
 });
});
</script>
<footer class="dashboard-footer"><p>&copy; <?= date('Y') ?> MediCare Hospital Management System.</p></footer>
</main></div></body></html>
