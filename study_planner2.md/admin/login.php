<?php

session_start();
// Remove any student session
unset(
    $_SESSION["user_id"],
    $_SESSION["full_name"],
    $_SESSION["email"],
    $_SESSION["role"]
);

require_once __DIR__ . "/../config/database.php";

$error = "";

$email = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($email === "" || $password === "") {

        $error = "Please enter your admin email and password.";

    } else {

        $stmt = $conn->prepare("
            SELECT id, full_name, email, password, role
            FROM users
            WHERE email = ?
              AND role = 'admin'
            LIMIT 1
        ");

        $stmt->execute([$email]);

        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin && password_verify($password, $admin["password"])) {

            $_SESSION["admin_id"] = $admin["id"];
            $_SESSION["admin_name"] = $admin["full_name"];
            $_SESSION["admin_email"] = $admin["email"];
            $_SESSION["admin_role"] = $admin["role"];

            header("Location: index.php");
            exit;

        } else {

            $error = "Invalid administrator credentials.";

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

    <title>Admin Login - Study Planner</title>

    <link rel="stylesheet"
          href="../assets/css/style.css">

    <style>

        body {
            margin: 0;
            min-height: 100vh;

            background:
                radial-gradient(
                    circle at top right,
                    rgba(79, 70, 229, 0.20),
                    transparent 35%
                ),
                linear-gradient(
                    135deg,
                    #f5f7fb,
                    #eef2ff
                );
        }

        .admin-login-page {

            min-height: 100vh;

            display: flex;
            align-items: center;
            justify-content: center;

            padding: 20px;

            box-sizing: border-box;
        }

        .admin-login-card {

            width: 100%;
            max-width: 450px;

            background: #ffffff;

            border-radius: 24px;

            padding: 45px;

            box-sizing: border-box;

            box-shadow:
                0 25px 70px rgba(15, 23, 42, 0.14);
        }

        .admin-logo {

            width: 70px;
            height: 70px;

            margin: 0 auto 20px;

            display: flex;
            align-items: center;
            justify-content: center;

            background:
                linear-gradient(
                    135deg,
                    #4f46e5,
                    #7c3aed
                );

            color: #ffffff;

            border-radius: 20px;

            font-size: 32px;

            box-shadow:
                0 12px 25px rgba(79, 70, 229, 0.25);
        }

        .admin-heading {

            text-align: center;

            margin-bottom: 30px;
        }

        .admin-heading h1 {

            margin: 0 0 8px;

            font-size: 28px;

            color: #172033;
        }

        .admin-heading p {

            margin: 0;

            color: #718096;
        }

        .admin-badge {

            display: inline-block;

            margin-top: 12px;

            padding: 6px 12px;

            border-radius: 20px;

            background: #eef2ff;

            color: #4338ca;

            font-size: 12px;

            font-weight: 700;

            text-transform: uppercase;

            letter-spacing: 0.5px;
        }

        .form-group {

            margin-bottom: 20px;
        }

        .form-group label {

            display: block;

            margin-bottom: 8px;

            font-weight: 600;

            color: #172033;
        }

        .form-control {

            width: 100%;

            padding: 13px 14px;

            border: 1px solid #e7eaf0;

            border-radius: 10px;

            box-sizing: border-box;

            font-size: 14px;

            font-family: inherit;

            color: #172033;

            background: #ffffff;

            transition: 0.2s;
        }

        .form-control:focus {

            outline: none;

            border-color: #4f46e5;

            box-shadow:
                0 0 0 3px rgba(79, 70, 229, 0.10);
        }

        .admin-submit {

            width: 100%;

            padding: 14px;

            margin-top: 5px;

            font-size: 15px;
        }

        .back-link {

            display: block;

            text-align: center;

            margin-top: 25px;

            color: #718096;

            text-decoration: none;

            font-size: 14px;
        }

        .back-link:hover {

            color: #4f46e5;
        }

        .security-note {

            margin-top: 25px;

            padding: 13px;

            border-radius: 10px;

            background: #f8fafc;

            color: #64748b;

            font-size: 12px;

            line-height: 1.5;

            text-align: center;
        }

        @media (max-width: 480px) {

            .admin-login-card {

                padding: 30px 22px;

            }

        }

    </style>

</head>

<body>

<div class="admin-login-page">

    <div class="admin-login-card">

        <div class="admin-logo">
            🔐
        </div>


        <div class="admin-heading">

            <h1>
                Administrator Login
            </h1>

            <p>
                Secure access to Study Planner administration.
            </p>

            <span class="admin-badge">
                Admin Access
            </span>

        </div>


        <?php if ($error !== ""): ?>

            <div class="alert alert-danger">
                <?php echo htmlspecialchars($error); ?>
            </div>

        <?php endif; ?>


        <form method="POST">

            <div class="form-group">

                <label for="email">
                    Administrator Email
                </label>

                <input
                    type="email"
                    id="email"
                    name="email"
                    class="form-control"
                    placeholder="Enter admin email"
                    value="<?php echo htmlspecialchars($email); ?>"
                    autocomplete="username"
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
                    placeholder="Enter admin password"
                    autocomplete="current-password"
                    required
                >

            </div>


            <button
                type="submit"
                class="btn btn-primary admin-submit"
            >
                🔐 Sign In as Administrator
            </button>

        </form>


        <div class="security-note">
            Only accounts with administrator privileges
            can access this area.
        </div>


        <a
            href="../auth/login.php"
            class="back-link"
        >
            ← Return to normal login
        </a>

    </div>

</div>

</body>

</html>