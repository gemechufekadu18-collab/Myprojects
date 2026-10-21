<?php

session_start();

require_once __DIR__ . "/../config/database.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($email === "" || $password === "") {

        $error = "Please enter your email and password.";

    } else {

        // Student accounts only
        $stmt = $conn->prepare("
            SELECT id, full_name, email, password, role
            FROM users
            WHERE email = ?
              AND role = 'student'
            LIMIT 1
        ");

        $stmt->execute([$email]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user["password"])) {

            // Remove any admin session
            unset(
                $_SESSION["admin_id"],
                $_SESSION["admin_name"],
                $_SESSION["admin_email"],
                $_SESSION["admin_role"]
            );

            // Regenerate session ID for security
            session_regenerate_id(true);

            // Create student session
            $_SESSION["user_id"] = $user["id"];
            $_SESSION["full_name"] = $user["full_name"];
            $_SESSION["email"] = $user["email"];
            $_SESSION["role"] = "student";

            header("Location: ../dashboard/index.php");
            exit;

        } else {

            $error = "Invalid student email or password.";
        }
    }
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

    <title>Student Login - Study Planner</title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

    <style>

        body {
            margin: 0;
            min-height: 100vh;
            font-family: Arial, sans-serif;
            background: #f5f7fb;
        }

        .login-wrapper {
            min-height: 100vh;
            display: flex;
        }

        .login-left {
            width: 50%;
            background: linear-gradient(
                135deg,
                #111827,
                #312e81,
                #4f46e5
            );
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 60px;
            box-sizing: border-box;
        }

        .login-brand {
            max-width: 500px;
        }

        .login-brand h1 {
            font-size: 48px;
            margin-bottom: 20px;
        }

        .login-brand h1 span {
            color: #a5b4fc;
        }

        .login-brand p {
            font-size: 18px;
            line-height: 1.7;
            color: #e5e7eb;
        }

        .login-features {
            margin-top: 30px;
        }

        .login-feature {
            margin-bottom: 15px;
            font-size: 16px;
        }

        .login-right {
            width: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px;
            box-sizing: border-box;
            background: white;
        }

        .login-card {
            width: 100%;
            max-width: 430px;
        }

        .login-card h2 {
            font-size: 32px;
            color: #172033;
            margin-bottom: 8px;
        }

        .login-subtitle {
            color: #718096;
            margin-bottom: 30px;
        }

        .login-form-group {
            margin-bottom: 20px;
        }

        .login-form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #172033;
        }

        .login-input {
            width: 100%;
            padding: 14px;
            border: 1px solid #e7eaf0;
            border-radius: 10px;
            box-sizing: border-box;
            font-size: 15px;
            outline: none;
        }

        .login-input:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 225, 0.1);
        }

        .login-button {
            width: 100%;
            border: none;
            padding: 14px;
            border-radius: 10px;
            background: #4f46e5;
            color: white;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s;
        }

        .login-button:hover {
            background: #3730a3;
        }

        .login-error {
            background: #fee2e2;
            color: #991b1b;
            padding: 12px 15px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .login-links {
            margin-top: 25px;
            text-align: center;
        }

        .login-links p {
            color: #718096;
            margin: 10px 0;
        }

        .login-links a {
            color: #4f46e5;
            text-decoration: none;
            font-weight: 600;
        }

        .login-links a:hover {
            text-decoration: underline;
        }

        .admin-login-link {
            display: inline-block;
            margin-top: 8px;
            padding: 10px 16px;
            border-radius: 8px;
            background: #f3f4f6;
        }

        @media (max-width: 800px) {

            .login-wrapper {
                flex-direction: column;
            }

            .login-left,
            .login-right {
                width: 100%;
            }

            .login-left {
                padding: 40px 25px;
            }

            .login-brand h1 {
                font-size: 36px;
            }

            .login-right {
                padding: 35px 25px;
            }
        }

    </style>

</head>

<body>

<div class="login-wrapper">

    <!-- LEFT SIDE -->

    <div class="login-left">

        <div class="login-brand">

            <h1>
                Study<span>Planner</span>
            </h1>

            <p>
                Organize your studies, manage your tasks,
                track your progress, and build better study habits.
            </p>

            <div class="login-features">

                <div class="login-feature">
                    ✓ Manage your subjects
                </div>

                <div class="login-feature">
                    ✓ Create study plans
                </div>

                <div class="login-feature">
                    ✓ Manage your timetable
                </div>

                <div class="login-feature">
                    ✓ Track your study progress
                </div>

            </div>

        </div>

    </div>


    <!-- RIGHT SIDE -->

    <div class="login-right">

        <div class="login-card">

            <h2>Student Login</h2>

            <p class="login-subtitle">
                Sign in to continue to your Study Planner.
            </p>


            <?php if ($error): ?>

                <div class="login-error">
                    <?php echo htmlspecialchars($error); ?>
                </div>

            <?php endif; ?>


            <form method="POST">

                <div class="login-form-group">

                    <label for="email">
                        Email Address
                    </label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="login-input"
                        placeholder="Enter your email"
                        required
                    >

                </div>


                <div class="login-form-group">

                    <label for="password">
                        Password
                    </label>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="login-input"
                        placeholder="Enter your password"
                        required
                    >

                </div>


                <button
                    type="submit"
                    class="login-button"
                >
                    Login
                </button>

            </form>


            <div class="login-links">

                <p>
                    Don't have an account?
                    <a href="register.php">
                        Create Account
                    </a>
                </p>

                <p>
                    Are you an administrator?
                </p>

                <a
                    href="../admin/login.php"
                    class="admin-login-link"
                >
                    🔐 Administrator Login
                </a>

            </div>

        </div>

    </div>

</div>

</body>

</html>