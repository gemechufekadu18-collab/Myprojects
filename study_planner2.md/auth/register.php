<?php

session_start();

require_once __DIR__ . "/../config/database.php";

$error = "";

$full_name = "";
$email = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $full_name = trim($_POST["full_name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirm_password = $_POST["confirm_password"] ?? "";

    if ($full_name === "" || $email === "" || $password === "" || $confirm_password === "") {

        $error = "Please fill in all required fields.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "Please enter a valid email address.";

    } elseif (strlen($password) < 6) {

        $error = "Password must contain at least 6 characters.";

    } elseif ($password !== $confirm_password) {

        $error = "Passwords do not match.";

    } else {

        $checkStmt = $conn->prepare("
            SELECT id
            FROM users
            WHERE email = ?
            LIMIT 1
        ");

        $checkStmt->execute([$email]);

        if ($checkStmt->fetch()) {

            $error = "An account with this email already exists.";

        } else {

            $hashedPassword = password_hash(
                $password,
                PASSWORD_DEFAULT
            );

            $stmt = $conn->prepare("
                INSERT INTO users
                (
                    full_name,
                    email,
                    password,
                    role
                )
                VALUES (?, ?, ?, 'student')
            ");

            $stmt->execute([
                $full_name,
                $email,
                $hashedPassword
            ]);

            header("Location: login.php");
            exit;
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Create Account - Study Planner</title>

    <link rel="stylesheet"
          href="../assets/css/style.css">

    <style>

        body {
            margin: 0;
            min-height: 100vh;

            background:
                radial-gradient(
                    circle at top right,
                    rgba(79, 70, 229, 0.18),
                    transparent 35%
                ),
                linear-gradient(
                    135deg,
                    #f5f7fb,
                    #eef2ff
                );
        }

        .auth-page {
            min-height: 100vh;

            display: flex;
            align-items: center;
            justify-content: center;

            padding: 30px 20px;

            box-sizing: border-box;
        }

        .auth-container {
            width: 100%;
            max-width: 1050px;

            min-height: 650px;

            display: grid;
            grid-template-columns: 1fr 1fr;

            background: #ffffff;

            border-radius: 24px;

            overflow: hidden;

            box-shadow:
                0 25px 70px rgba(15, 23, 42, 0.14);
        }

        /* LEFT SIDE */

        .auth-brand {

            background:
                linear-gradient(
                    145deg,
                    #111827,
                    #312e81
                );

            color: #ffffff;

            padding: 60px;

            display: flex;
            flex-direction: column;
            justify-content: center;

            position: relative;

            overflow: hidden;
        }

        .auth-brand::before {

            content: "";

            position: absolute;

            width: 320px;
            height: 320px;

            border-radius: 50%;

            background:
                rgba(124, 58, 237, 0.25);

            top: -110px;
            right: -110px;
        }

        .auth-brand::after {

            content: "";

            position: absolute;

            width: 230px;
            height: 230px;

            border-radius: 50%;

            background:
                rgba(79, 70, 229, 0.18);

            bottom: -90px;
            left: -90px;
        }

        .brand-content {

            position: relative;
            z-index: 2;
        }

        .brand-logo {

            font-size: 34px;

            font-weight: 800;

            margin-bottom: 25px;
        }

        .brand-logo span {

            color: #818cf8;
        }

        .auth-brand h1 {

            font-size: 42px;

            line-height: 1.15;

            margin: 0 0 20px;
        }

        .auth-brand p {

            color: #c7d2fe;

            font-size: 16px;

            line-height: 1.7;

            max-width: 430px;
        }

        .feature-list {

            margin-top: 35px;
        }

        .feature {

            display: flex;

            align-items: center;

            gap: 12px;

            margin-bottom: 18px;

            color: #e0e7ff;
        }

        .feature-icon {

            width: 38px;
            height: 38px;

            display: flex;

            align-items: center;
            justify-content: center;

            background:
                rgba(255,255,255,0.10);

            border-radius: 10px;
        }

        /* RIGHT SIDE */

        .auth-form-area {

            padding: 50px 60px;

            display: flex;

            align-items: center;
        }

        .auth-form {

            width: 100%;

            max-width: 430px;

            margin: auto;
        }

        .auth-form h2 {

            font-size: 30px;

            margin: 0 0 8px;

            color: #172033;
        }

        .auth-subtitle {

            color: #718096;

            margin-bottom: 28px;
        }

        .auth-form .form-group {

            margin-bottom: 18px;
        }

        .auth-form .form-control {

            padding: 13px 15px;

            font-size: 15px;
        }

        .auth-submit {

            width: 100%;

            padding: 14px;

            font-size: 15px;

            margin-top: 5px;
        }

        .auth-footer {

            text-align: center;

            margin-top: 22px;

            color: #718096;
        }

        .auth-footer a {

            color: #4f46e5;

            font-weight: 700;

            text-decoration: none;
        }

        .auth-footer a:hover {

            text-decoration: underline;
        }

        .password-hint {

            margin-top: 6px;

            font-size: 12px;

            color: #718096;
        }

        .alert {

            margin-bottom: 20px;
        }

        @media (max-width: 850px) {

            .auth-container {

                grid-template-columns: 1fr;

                max-width: 520px;
            }

            .auth-brand {

                padding: 40px;

                min-height: 300px;
            }

            .auth-brand h1 {

                font-size: 32px;
            }

            .feature-list {

                display: none;
            }

            .auth-form-area {

                padding: 40px 30px;
            }
        }

        @media (max-width: 480px) {

            .auth-page {

                padding: 15px;
            }

            .auth-brand {

                padding: 30px 25px;
            }

            .auth-form-area {

                padding: 35px 22px;
            }

            .brand-logo {

                font-size: 28px;
            }

            .auth-brand h1 {

                font-size: 28px;
            }
        }

    </style>

</head>

<body>

<div class="auth-page">

    <div class="auth-container">

        <!-- LEFT SIDE -->

        <div class="auth-brand">

            <div class="brand-content">

                <div class="brand-logo">
                    Study<span>Planner</span>
                </div>

                <h1>
                    Build better<br>
                    study habits.
                </h1>

                <p>
                    Create your personal study workspace and
                    take control of your academic goals.
                </p>

                <div class="feature-list">

                    <div class="feature">

                        <div class="feature-icon">
                            📅
                        </div>

                        <span>
                            Organize your timetable
                        </span>

                    </div>

                    <div class="feature">

                        <div class="feature-icon">
                            ⏱️
                        </div>

                        <span>
                            Track your study sessions
                        </span>

                    </div>

                    <div class="feature">

                        <div class="feature-icon">
                            📊
                        </div>

                        <span>
                            Measure your progress
                        </span>

                    </div>

                </div>

            </div>

        </div>


        <!-- RIGHT SIDE -->

        <div class="auth-form-area">

            <div class="auth-form">

                <h2>
                    Create your account
                </h2>

                <p class="auth-subtitle">
                    Start organizing your study life today.
                </p>


                <?php if ($error !== ""): ?>

                    <div class="alert alert-danger">
                        <?php echo htmlspecialchars($error); ?>
                    </div>

                <?php endif; ?>


                <form method="POST">

                    <div class="form-group">

                        <label for="full_name">
                            Full Name
                        </label>

                        <input
                            type="text"
                            id="full_name"
                            name="full_name"
                            class="form-control"
                            placeholder="Enter your full name"
                            value="<?php echo htmlspecialchars($full_name); ?>"
                            autocomplete="name"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label for="email">
                            Email Address
                        </label>

                        <input
                            type="email"
                            id="email"
                            name="email"
                            class="form-control"
                            placeholder="Enter your email"
                            value="<?php echo htmlspecialchars($email); ?>"
                            autocomplete="email"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label for="password">
                            Password
                        </label>

                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-control"
                            placeholder="Create a password"
                            autocomplete="new-password"
                            minlength="6"
                            required
                        >

                        <div class="password-hint">
                            Use at least 6 characters.
                        </div>

                    </div>


                    <div class="form-group">

                        <label for="confirm_password">
                            Confirm Password
                        </label>

                        <input
                            type="password"
                            id="confirm_password"
                            name="confirm_password"
                            class="form-control"
                            placeholder="Confirm your password"
                            autocomplete="new-password"
                            minlength="6"
                            required
                        >

                    </div>


                    <button
                        type="submit"
                        class="btn btn-primary auth-submit"
                    >
                        Create Account →
                    </button>

                </form>


                <div class="auth-footer">

                    Already have an account?

                    <a href="login.php">
                        Sign in
                    </a>

                </div>

            </div>

        </div>

    </div>

</div>

</body>

</html>