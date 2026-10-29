
<?php
session_start();

require_once "../config/database.php";

$error = "";
$success = "";

$name = "";
$email = "";
$phone = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $phone = trim($_POST["phone"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirmPassword = $_POST["confirm_password"] ?? "";

    if (
        $name === "" ||
        $email === "" ||
        $phone === "" ||
        $password === "" ||
        $confirmPassword === ""
    ) {
        $error = "Please fill in all fields.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";

    } elseif (strlen($password) < 8) {
        $error = "Your password must contain at least 8 characters.";

    } elseif ($password !== $confirmPassword) {
        $error = "The passwords do not match.";

    } else {

        // Check whether this email is already registered.
        $check = $conn->prepare(
            "SELECT id FROM patients WHERE email = ? LIMIT 1"
        );

        $check->bind_param("s", $email);
        $check->execute();

        $result = $check->get_result();

        if ($result->num_rows > 0) {
            $error = "This email is already registered. Please log in.";

        } else {

            // Securely hash the password before saving it.
            $hashedPassword = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            $insert = $conn->prepare(
                "INSERT INTO patients
                (full_name, email, phone, password)
                VALUES (?, ?, ?, ?)"
            );

            $insert->bind_param(
                "ssss",
                $name,
                $email,
                $phone,
                $hashedPassword
            );

            if ($insert->execute()) {

                // Registration succeeded. Send the patient to login.
                header("Location: login.php?registered=1");
                exit;

            } else {
                $error = "Registration failed. Please try again.";
            }

            $insert->close();
        }

        $check->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Patient Registration | MediCare</title>

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
            padding: 25px;
            color: #203047;
        }

        .register-container {
            width: 100%;
            max-width: 460px;
            background: #fff;
            padding: 35px;
            border-radius: 18px;
            box-shadow: 0 15px 45px rgba(30, 60, 90, 0.12);
        }

        .logo {
            text-align: center;
            color: #1769aa;
            font-size: 27px;
            font-weight: bold;
            margin-bottom: 22px;
        }

        .logo span {
            color: #20a58a;
        }

        h1 {
            text-align: center;
            font-size: 25px;
            margin-bottom: 10px;
        }

        .subtitle {
            text-align: center;
            color: #758195;
            font-size: 14px;
            margin-bottom: 25px;
            line-height: 1.6;
        }

        .form-group {
            margin-bottom: 17px;
        }

        label {
            display: block;
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 8px;
        }

        input {
            width: 100%;
            padding: 13px;
            border: 1px solid #dce4ed;
            border-radius: 9px;
            font-size: 15px;
            outline: none;
        }

        input:focus {
            border-color: #1769aa;
            box-shadow: 0 0 0 3px rgba(23, 105, 170, 0.1);
        }

        .password-wrapper {
            position: relative;
        }

        .password-wrapper input {
            padding-right: 48px;
        }

        .toggle-password {
            position: absolute;
            right: 8px;
            top: 50%;
            transform: translateY(-50%);
            width: 35px;
            height: 35px;
            background: transparent;
            border: none;
            cursor: pointer;
            font-size: 18px;
        }

        .submit-btn {
            width: 100%;
            padding: 14px;
            background: #1769aa;
            color: white;
            border: none;
            border-radius: 9px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 5px;
        }

        .submit-btn:hover {
            background: #105387;
        }

        .error {
            background: #fff0f0;
            color: #a52626;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 18px;
            font-size: 14px;
            line-height: 1.5;
        }

        .links {
            text-align: center;
            margin-top: 22px;
            font-size: 14px;
            line-height: 2;
        }

        .links a {
            color: #1769aa;
            text-decoration: none;
            font-weight: bold;
        }

        @media (max-width: 480px) {
            .register-container {
                padding: 27px 22px;
            }
        }
    </style>
</head>

<body>

<main class="register-container">

    <div class="logo">
        ✚ Medi<span>Care</span>
    </div>

    <h1>Create Your Account</h1>

    <p class="subtitle">
        Register as a patient to manage your healthcare appointments.
    </p>

    <?php if ($error !== ""): ?>
        <div class="error">
            <?= htmlspecialchars($error, ENT_QUOTES, "UTF-8") ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="register.php">

        <div class="form-group">
            <label for="name">Full Name</label>
            <input
                type="text"
                id="name"
                name="name"
                value="<?= htmlspecialchars($name, ENT_QUOTES, "UTF-8") ?>"
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
                value="<?= htmlspecialchars($email, ENT_QUOTES, "UTF-8") ?>"
                placeholder="Enter your email address"
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
                value="<?= htmlspecialchars($phone, ENT_QUOTES, "UTF-8") ?>"
                placeholder="Enter your phone number"
                autocomplete="tel"
                required
            >
        </div>

        <div class="form-group">
            <label for="password">Create Password</label>

            <div class="password-wrapper">
                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="At least 8 characters"
                    autocomplete="new-password"
                    minlength="8"
                    required
                >

                <button
                    type="button"
                    class="toggle-password"
                    data-target="password"
                    aria-label="Show password"
                    aria-pressed="false"
                >👁️</button>
            </div>
        </div>

        <div class="form-group">
            <label for="confirm_password">Confirm Password</label>

            <div class="password-wrapper">
                <input
                    type="password"
                    id="confirm_password"
                    name="confirm_password"
                    placeholder="Enter your password again"
                    autocomplete="new-password"
                    minlength="8"
                    required
                >

                <button
                    type="button"
                    class="toggle-password"
                    data-target="confirm_password"
                    aria-label="Show password"
                    aria-pressed="false"
                >👁️</button>
            </div>
        </div>

        <button type="submit" class="submit-btn">
            Create Account →
        </button>

    </form>

    <div class="links">
        Already registered?
        <a href="login.php">Sign In</a>
        <br>
        <a href="../index.php">← Back to Homepage</a>
    </div>

</main>

<script>
document.querySelectorAll(".toggle-password").forEach(function(button) {

    button.addEventListener("click", function() {

        const input = document.getElementById(this.dataset.target);

        const showPassword = input.type === "password";

        input.type = showPassword ? "text" : "password";

        this.setAttribute("aria-pressed", String(showPassword));

        this.setAttribute(
            "aria-label",
            showPassword ? "Hide password" : "Show password"
        );

        this.textContent = showPassword ? "🙈" : "👁️";
    });

});
</script>

</body>
</html>

