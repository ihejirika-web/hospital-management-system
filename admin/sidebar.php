```php
<?php
$currentPage = basename($_SERVER['PHP_SELF'] ?? 'index.php');

$adminNavItems = [
    ['file' => 'index.php', 'icon' => '▦', 'label' => 'Dashboard'],
    ['file' => 'appointments.php', 'icon' => '▣', 'label' => 'Appointments'],
    ['file' => 'patients.php', 'icon' => '♙', 'label' => 'Patients'],
    ['file' => 'doctors.php', 'icon' => '✚', 'label' => 'Doctors'],
    ['file' => 'settings.php', 'icon' => '⚙', 'label' => 'Settings'],
];
?>

<aside class="sidebar">
    <a href="../index.php" class="brand">
        <span class="brand-icon">✚</span>
        <span>MediCare</span>
    </a>

    <p class="sidebar-label">ADMIN MENU</p>

    <nav class="sidebar-nav">
        <?php foreach ($adminNavItems as $item): ?>
            <a href="<?= htmlspecialchars($item['file']) ?>"
               class="nav-item<?= $currentPage === $item['file'] ? ' active' : '' ?>">
                <span><?= htmlspecialchars($item['icon']) ?></span>
                <?= htmlspecialchars($item['label']) ?>
            </a>
        <?php endforeach; ?>

        <a href="../pages/logout.php" class="nav-item">
            <span>↪</span> Log Out
        </a>
    </nav>

    <div class="sidebar-bottom">
        <p>Hospital Management System</p>
        <a href="../index.php">← Back to Homepage</a>
    </div>
</aside>
```
