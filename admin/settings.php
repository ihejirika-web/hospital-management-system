<?php
session_start();

require_once __DIR__ . '/../config/database.php';

// Allow only authenticated administrators.
if (
    !isset($_SESSION['user_id'], $_SESSION['role']) ||
    $_SESSION['role'] !== 'admin'
) {
    header('Location: ../pages/login.php');
    exit;
}

$adminId = (int) $_SESSION['user_id'];
$adminName = $_SESSION['user_name'] ?? 'Administrator';
$adminEmail = $_SESSION['user_email'] ?? '';

$success = '';
$error = '';

// Load the current administrator's account details.
$stmt = $conn->prepare(
    'SELECT full_name, email, password FROM admins WHERE id = ? LIMIT 1'
);

if (!$stmt) {
    die('Unable to load administrator settings. Please check your database.');
}

$stmt->bind_param('i', $adminId);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();
$stmt->close();

if (!$admin) {
    // The session does not point to an existing administrator account.
    $_SESSION = [];
    session_destroy();

    header('Location: ../pages/login.php');
    exit;
}

$adminName = $admin['full_name'];
$adminEmail = $admin['email'];

// Process a password-change request.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (
        $currentPassword === '' ||
        $newPassword === '' ||
        $confirmPassword === ''
    ) {
        $error = 'Please complete all password fields.';
    } elseif (!password_verify($currentPassword, $admin['password'])) {
        $error = 'Your current password is incorrect.';
    } elseif (strlen($newPassword) < 12) {
        $error = 'Your new password must contain at least 12 characters.';
    } elseif ($newPassword !== $confirmPassword) {
        $error = 'The new passwords do not match.';
    } elseif ($currentPassword === $newPassword) {
        $error = 'Your new password must be different from your current password.';
    } else {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        $update = $conn->prepare(
            'UPDATE admins SET password = ? WHERE id = ?'
        );

        if (!$update) {
            $error = 'Unable to prepare the password update.';
        } else {
            $update->bind_param('si', $hashedPassword, $adminId);

            if ($update->execute() && $update->affected_rows === 1) {
                $success = 'Your password has been changed successfully.';
                $admin['password'] = $hashedPassword;
            } else {
                $error = 'The password could not be changed. Please try again.';
            }

            $update->close();
        }
    }
}

$conn->close();

function escapeHtml(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Settings | MediCare</title>

    <link rel="stylesheet" href="../assets/admin.css">

    <style>
        .settings-container {
            max-width: 850px;
            margin: 30px auto;
        }

        .settings-card {
            background: #fff;
            padding: 28px;
            margin-bottom: 24px;
            border-radius: 14px;
            box-shadow: 0 4px 18px rgba(0, 0, 0, 0.06);
        }

        .settings-card h2 {
            margin-top: 0;
            margin-bottom: 10px;
        }

        .settings-description {
            color: #64748b;
            margin-bottom: 24px;
        }

        .account-detail {
            margin: 18px 0;
        }

        .account-detail label,
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
        }

        .account-detail p {
            margin: 0;
            padding: 12px;
            background: #f8fafc;
            border-radius: 8px;
            overflow-wrap: anywhere;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group input {
            width: 100%;
            box-sizing: border-box;
            padding: 12px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font: inherit;
        }

        .settings-button {
            display: inline-block;
            padding: 12px 20px;
            background: #1769aa;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            font: inherit;
        }

        .settings-button:hover {
            background: #12558a;
        }

        .message {
            padding: 14px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .message.success {
            background: #dcfce7;
            color: #166534;
        }

        .message.error {
            background: #fee2e2;
            color: #991b1b;
        }

        .settings-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 20px;
        }

        @media (max-width: 600px) {
            .settings-container {
                margin: 20px 10px;
            }

            .settings-card {
                padding: 18px;
            }
        }
    </style>
</head>

<body>
<div class="dashboard">

    <aside class="sidebar">
        <a href="../index.php" class="brand">
            <span class="brand-icon">✚</span>
            <span>MediCare</span>
        </a>

        <p class="sidebar-label">ADMIN MENU</p>

        <nav class="sidebar-nav">
            <a href="index.php" class="nav-item">
                <span>▦</span> Dashboard
            </a>

            <a href="settings.php" class="nav-item active">
                <span>⚙</span> Settings
            </a>

            <a href="../pages/logout.php" class="nav-item">
                <span>↪</span> Log Out
            </a>
        </nav>

        <div class="sidebar-bottom">
            <p>Hospital Management System</p>
            <a href="index.php">← Back to Dashboard</a>
        </div>
    </aside>

    <main class="main-content">

        <header class="topbar">
            <div>
                <p class="topbar-greeting">Manage your account</p>
                <h1>Admin Settings</h1>
            </div>
        </header>

        <div class="settings-container">

            <?php if ($success !== ''): ?>
                <div class="message success">
                    <?= escapeHtml($success) ?>
                </div>
            <?php endif; ?>

            <?php if ($error !== ''): ?>
                <div class="message error">
                    <?= escapeHtml($error) ?>
                </div>
            <?php endif; ?>

            <section class="settings-card">
                <h2>Administrator Profile</h2>

                <p class="settings-description">
                    View the account currently signed in to MediCare.
                </p>

                <div class="account-detail">
                    <label>Full Name</label>
                    <p><?= escapeHtml($adminName) ?></p>
                </div>

                <div class="account-detail">
                    <label>Email Address</label>
                    <p><?= escapeHtml($adminEmail) ?></p>
                </div>

                <div class="account-detail">
                    <label>Account Type</label>
                    <p>Administrator</p>
                </div>
            </section>

            <section class="settings-card">
                <h2>Change Password</h2>

                <p class="settings-description">
                    Enter your current password and choose a new password
                    containing at least 12 characters.
                </p>

                <form method="POST" action="settings.php" autocomplete="off">

                    <div class="form-group">
                        <label for="current_password">
                            Current Password
                        </label>

                        <input
                            type="password"
                            id="current_password"
                            name="current_password"
                            autocomplete="current-password"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="new_password">New Password</label>

                        <input
                            type="password"
                            id="new_password"
                            name="new_password"
                            minlength="12"
                            autocomplete="new-password"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">
                            Confirm New Password
                        </label>

                        <input
                            type="password"
                            id="confirm_password"
                            name="confirm_password"
                            minlength="12"
                            autocomplete="new-password"
                            required
                        >
                    </div>

                    <button type="submit" class="settings-button">
                        Update Password
                    </button>
                </form>
            </section>

            <section class="settings-card">
                <h2>Account Actions</h2>

                <p class="settings-description">
                    Return to the dashboard or securely end your session.
                </p>

                <div class="settings-actions">
                    <a href="index.php" class="settings-button">
                        Back to Dashboard
                    </a>

                    <a href="../pages/logout.php" class="settings-button">
                        Log Out
                    </a>
                </div>
            </section>

        </div>

        <footer class="dashboard-footer">
            <p>
                &copy; <?= date('Y') ?> MediCare Hospital Management System.
            </p>
        </footer>

    </main>
</div>
</body>
</html>

