<?php
session_start();

require_once __DIR__ . "/../config/database.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST["email"] ?? "";

    // Normalize the submitted email and remove invisible spacing.
    if (is_string($email)) {
        $email = preg_replace('/[\s\p{Z}\x{200B}\x{FEFF}]+/u', '', $email);
        $email = strtolower(trim($email ?? ""));
    } else {
        $email = "";
    }

    $password = $_POST["password"] ?? "";
    $role = $_POST["role"] ?? "patient";

    $tables = [
        "patient" => "patients",
        "doctor"  => "doctors",
        "admin"   => "admins"
    ];

    $destinations = [
        "patient" => "../patient/",
        "doctor"  => "../doctor/",
        "admin"   => "../admin/"
    ];



    if ($email === "" || $password === "") {
        $error = "Please enter your email and password.";
    } elseif (!isset($tables[$role])) {
        $error = "Please select a valid account type.";
    } else {
        $table = $tables[$role];

        // Retrieve only the account matching the submitted email.
        $stmt = $conn->prepare(
            "SELECT id, full_name, email, password
             FROM `$table`
             WHERE LOWER(TRIM(email)) = ?
             LIMIT 1"
        );

        if (!$stmt) {
            $error = "Unable to process your login. Please try again.";
        } else {
            $stmt->bind_param("s", $email);

            if (!$stmt->execute()) {
                $error = "Unable to process your login. Please try again.";
            } else {
                $result = $stmt->get_result();
                $user = $result->fetch_assoc();

                if (
                    !$user ||
                    !password_verify($password, $user["password"])
                ) {
                    $error = "Invalid email address or password.";
                } else {
                    session_regenerate_id(true);

                    $_SESSION["user_id"] = (int) $user["id"];
                    $_SESSION["user_name"] = $user["full_name"];
                    $_SESSION["user_email"] = $user["email"];
                    $_SESSION["role"] = $role;

                    $stmt->close();
                    $conn->close();

                    header("Location: " . $destinations[$role]);
                    exit;
                }
            }

            $stmt->close();
        }
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Login | MediCare</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #eef7f8, #e4efff);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            color: #203047;
        }

        .login-container {
            width: 100%;
            max-width: 430px;
            background: #fff;
            padding: 38px;
            border-radius: 20px;
            box-shadow: 0 15px 45px rgba(30, 60, 90, 0.12);
        }

        .logo {
            text-align: center;
            color: #1769aa;
            font-size: 27px;
            font-weight: bold;
            margin-bottom: 28px;
        }

        .logo span {
            color: #20a58a;
        }

        h1 {
            text-align: center;
            font-size: 27px;
            margin-bottom: 10px;
        }

        .subtitle {
            text-align: center;
            color: #758195;
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 28px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 8px;
        }

        input,
        select {
            width: 100%;
            padding: 14px;
            border: 1px solid #dce4ed;
            border-radius: 9px;
            font-size: 15px;
            outline: none;
            background: white;
            color: #203047;
        }

        input:focus,
        select:focus {
            border-color: #1769aa;
            box-shadow: 0 0 0 3px rgba(23, 105, 170, 0.1);
        }

        .password-wrapper {
            position: relative;
            width: 100%;
        }

        .password-wrapper input {
            padding-right: 50px;
        }

        .password-toggle {
            position: absolute;
            top: 50%;
            right: 8px;
            transform: translateY(-50%);
            width: 36px;
            height: 36px;
            padding: 0;
            background: transparent;
            color: #1769aa;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
        }

        .password-toggle:hover {
            background: #eef7f8;
        }

        .password-toggle:focus-visible {
            outline: 2px solid #1769aa;
        }

        .submit-button {
            width: 100%;
            padding: 15px;
            background: #1769aa;
            color: white;
            border: none;
            border-radius: 9px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.2s ease, transform 0.2s ease;
        }

        .submit-button:hover {
            background: #105387;
            transform: translateY(-2px);
        }

        .error {
            background: #fff0f0;
            color: #a52626;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 18px;
            font-size: 13px;
            line-height: 1.5;
            overflow-wrap: anywhere;
        }

        .back-home {
            text-align: center;
            margin-top: 24px;
            font-size: 14px;
        }

        .back-home a {
            color: #1769aa;
            text-decoration: none;
            font-weight: bold;
        }

        .back-home a:hover {
            text-decoration: underline;
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 27px 22px;
            }
        }
    </style>
</head>

<body>
    <main class="login-container">

        <div class="logo">
            ✚ Medi<span>Care</span>
        </div>

        <h1>Welcome Back</h1>

        <p class="subtitle">
            Sign in to access your MediCare account.
        </p>

        <?php if ($error !== ""): ?>
            <div class="error" role="alert">
                <?= htmlspecialchars($error, ENT_QUOTES, "UTF-8") ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php">

            <div class="form-group">
                <label for="role">Account Type</label>

                <select id="role" name="role" required>
                    <option value="patient"
                        <?= (($_POST["role"] ?? "patient") === "patient")
                            ? "selected" : "" ?>>
                        Patient
                    </option>

                    <option value="doctor"
                        <?= (($_POST["role"] ?? "") === "doctor")
                            ? "selected" : "" ?>>
                        Doctor
                    </option>

                    <option value="admin"
                        <?= (($_POST["role"] ?? "") === "admin")
                            ? "selected" : "" ?>>
                        Administrator
                    </option>
                </select>
            </div>

            <div class="form-group">
                <label for="email">Email Address</label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    placeholder="Enter your email address"
                    value="<?= htmlspecialchars(
                        $_POST["email"] ?? "",
                        ENT_QUOTES,
                        "UTF-8"
                    ) ?>"
                    autocomplete="username"
                    required
                >
            </div>

            <div class="form-group">
                <label for="password">Password</label>

                <div class="password-wrapper">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Enter your password"
                        autocomplete="current-password"
                        required
                    >

                    <button
                        type="button"
                        id="togglePassword"
                        class="password-toggle"
                        aria-label="Show password"
                        aria-pressed="false"
                    >👁️</button>
                </div>
            </div>

            <button type="submit" class="submit-button">
                Sign In →
            </button>

        </form>

        <p class="back-home">
            <a href="/Hospital-Management-System/">
                ← Back to Homepage
            </a>
        </p>

    </main>

    <script>
        const passwordInput = document.getElementById("password");
        const togglePassword = document.getElementById("togglePassword");

        togglePassword.addEventListener("click", function () {
            const isHidden = passwordInput.type === "password";

            passwordInput.type = isHidden ? "text" : "password";
            togglePassword.textContent = isHidden ? "🙈" : "👁️";

            togglePassword.setAttribute(
                "aria-label",
                isHidden ? "Hide password" : "Show password"
            );

            togglePassword.setAttribute(
                "aria-pressed",
                isHidden ? "true" : "false"
            );
        });
    </script>
</body>
</html>

