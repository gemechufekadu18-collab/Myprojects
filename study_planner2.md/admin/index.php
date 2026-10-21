<?php

session_start();

require_once __DIR__ . "/../config/database.php";

/*
|--------------------------------------------------------------------------
| ADMIN AUTHENTICATION
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION["admin_id"]) || $_SESSION["admin_role"] !== "admin") {

    header("Location: login.php");
    exit;
}

$admin_name = $_SESSION["admin_name"];


/*
|--------------------------------------------------------------------------
| DASHBOARD STATISTICS
|--------------------------------------------------------------------------
*/

$total_users = $conn->query("
    SELECT COUNT(*) 
    FROM users
    WHERE role = 'student'
")->fetchColumn();

$total_subjects = $conn->query("
    SELECT COUNT(*) 
    FROM subjects
")->fetchColumn();

$total_tasks = $conn->query("
    SELECT COUNT(*) 
    FROM tasks
")->fetchColumn();

$total_plans = $conn->query("
    SELECT COUNT(*) 
    FROM study_plans
")->fetchColumn();

$total_sessions = $conn->query("
    SELECT COUNT(*) 
    FROM study_sessions
")->fetchColumn();

$total_notifications = $conn->query("
    SELECT COUNT(*) 
    FROM notifications
")->fetchColumn();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Admin Dashboard - Study Planner</title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

    <style>

        .admin-wrapper {
            max-width: 1400px;
            margin: 0 auto;
        }

        .admin-welcome {
            background:
                linear-gradient(
                    135deg,
                    #111827,
                    #312e81
                );

            color: white;

            border-radius: 18px;

            padding: 30px;

            margin-bottom: 25px;

            position: relative;

            overflow: hidden;
        }

        .admin-welcome::after {
            content: "";

            position: absolute;

            width: 220px;
            height: 220px;

            border-radius: 50%;

            background:
                rgba(124, 58, 237, 0.25);

            right: -70px;
            top: -90px;
        }

        .admin-welcome-content {
            position: relative;
            z-index: 2;
        }

        .admin-welcome h1 {
            margin: 0 0 8px;
            font-size: 30px;
        }

        .admin-welcome p {
            margin: 0;
            color: #c7d2fe;
        }

        .admin-badge {
            display: inline-block;

            margin-top: 18px;

            padding: 7px 13px;

            border-radius: 20px;

            background:
                rgba(255,255,255,0.12);

            color: #e0e7ff;

            font-size: 12px;

            font-weight: 700;
        }

        .admin-stats {
            display: grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap: 20px;

            margin-bottom: 25px;
        }

        .admin-stat-card {
            background: #ffffff;

            border: 1px solid var(--border);

            border-radius: 16px;

            padding: 22px;

            display: flex;

            align-items: center;

            gap: 16px;

            transition: 0.2s;
        }

        .admin-stat-card:hover {
            transform: translateY(-2px);

            box-shadow:
                0 10px 25px
                rgba(15, 23, 42, 0.07);
        }

        .admin-stat-icon {
            width: 52px;
            height: 52px;

            display: flex;

            align-items: center;
            justify-content: center;

            border-radius: 14px;

            background: #eef2ff;

            font-size: 25px;
        }

        .admin-stat-info h3 {
            margin: 0;

            font-size: 27px;

            color: var(--text);
        }

        .admin-stat-info p {
            margin: 4px 0 0;

            color: var(--muted);

            font-size: 13px;
        }

        .admin-actions {
            display: grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap: 20px;
        }

        .admin-action-card {
            background: #ffffff;

            border: 1px solid var(--border);

            border-radius: 16px;

            padding: 25px;

            text-decoration: none;

            color: var(--text);

            transition: 0.2s;
        }

        .admin-action-card:hover {
            transform: translateY(-3px);

            border-color: #c7d2fe;

            box-shadow:
                0 12px 30px
                rgba(15, 23, 42, 0.08);
        }

        .action-icon {
            font-size: 30px;

            margin-bottom: 15px;
        }

        .admin-action-card h3 {
            margin: 0 0 7px;
        }

        .admin-action-card p {
            margin: 0;

            color: var(--muted);

            font-size: 14px;

            line-height: 1.5;
        }

        .admin-section {
            margin-top: 25px;
        }

        .admin-section-header {
            margin-bottom: 15px;
        }

        .admin-section-header h2 {
            margin: 0 0 5px;
        }

        .admin-section-header p {
            margin: 0;

            color: var(--muted);
        }

        @media (max-width: 900px) {

            .admin-stats,
            .admin-actions {
                grid-template-columns:
                    repeat(2, 1fr);
            }
        }

        @media (max-width: 600px) {

            .admin-stats,
            .admin-actions {
                grid-template-columns: 1fr;
            }

            .admin-welcome h1 {
                font-size: 24px;
            }
        }

    </style>

</head>

<body>

<div class="app">

    <div class="sidebar">

        <div class="logo">
            Study<span>Planner</span>
        </div>

        <div class="nav-title">
            Administration
        </div>

        <ul class="nav-menu">

            <li>
                <a href="index.php">
                    <span class="nav-icon">📊</span>
                    <span>Dashboard</span>
                </a>
            </li>

            <li>
                <a href="users.php">
                    <span class="nav-icon">👥</span>
                    <span>Users</span>
                </a>
            </li>

        </ul>

        <br>

        <ul class="nav-menu">

            <li>
                <a href="../auth/login.php">🎓 Student Login</a>
            </li>

            <li>
                <a href="logout.php">
                    <span class="nav-icon">🚪</span>
                    <span>Admin Logout</span>
                </a>
            </li>

        </ul>

    </div>


    <div class="main">

        <div class="topbar">

            <div class="topbar-title">
                Administration
            </div>

            <div class="user-area">

                <div>

                    <div class="user-name">
                        <?php
                        echo htmlspecialchars($admin_name);
                        ?>
                    </div>

                    <div class="text-muted"
                         style="font-size:12px;">
                        Administrator
                    </div>

                </div>

                <div class="user-avatar">

                    <?php

                    echo strtoupper(
                        substr($admin_name, 0, 1)
                    );

                    ?>

                </div>

            </div>

        </div>


        <main class="content">

            <div class="admin-wrapper">


                <!-- WELCOME -->

                <div class="admin-welcome">

                    <div class="admin-welcome-content">

                        <h1>
                            Welcome, <?php
                            echo htmlspecialchars($admin_name);
                            ?> 👋
                        </h1>

                        <p>
                            Monitor and manage the Study Planner system
                            from your administration dashboard.
                        </p>

                        <span class="admin-badge">
                            🔐 Secure Administrator Area
                        </span>

                    </div>

                </div>


                <!-- STATISTICS -->

                <div class="admin-stats">


                    <div class="admin-stat-card">

                        <div class="admin-stat-icon">
                            👥
                        </div>

                        <div class="admin-stat-info">

                            <h3>
                                <?php
                                echo $total_users;
                                ?>
                            </h3>

                            <p>
                                Student Users
                            </p>

                        </div>

                    </div>


                    <div class="admin-stat-card">

                        <div class="admin-stat-icon">
                            📚
                        </div>

                        <div class="admin-stat-info">

                            <h3>
                                <?php
                                echo $total_subjects;
                                ?>
                            </h3>

                            <p>
                                Subjects
                            </p>

                        </div>

                    </div>


                    <div class="admin-stat-card">

                        <div class="admin-stat-icon">
                            ✅
                        </div>

                        <div class="admin-stat-info">

                            <h3>
                                <?php
                                echo $total_tasks;
                                ?>
                            </h3>

                            <p>
                                Tasks
                            </p>

                        </div>

                    </div>


                    <div class="admin-stat-card">

                        <div class="admin-stat-icon">
                            📋
                        </div>

                        <div class="admin-stat-info">

                            <h3>
                                <?php
                                echo $total_plans;
                                ?>
                            </h3>

                            <p>
                                Study Plans
                            </p>

                        </div>

                    </div>


                    <div class="admin-stat-card">

                        <div class="admin-stat-icon">
                            ⏱️
                        </div>

                        <div class="admin-stat-info">

                            <h3>
                                <?php
                                echo $total_sessions;
                                ?>
                            </h3>

                            <p>
                                Study Sessions
                            </p>

                        </div>

                    </div>


                    <div class="admin-stat-card">

                        <div class="admin-stat-icon">
                            🔔
                        </div>

                        <div class="admin-stat-info">

                            <h3>
                                <?php
                                echo $total_notifications;
                                ?>
                            </h3>

                            <p>
                                Notifications
                            </p>

                        </div>

                    </div>


                </div>


                <!-- ADMIN ACTIONS -->

                <div class="admin-section">

                    <div class="admin-section-header">

                        <h2>
                            Administration
                        </h2>

                        <p>
                            Manage important parts of the system.
                        </p>

                    </div>


                    <div class="admin-actions">


                        <a
                            href="users.php"
                            class="admin-action-card"
                        >

                            <div class="action-icon">
                                👥
                            </div>

                            <h3>
                                Manage Users
                            </h3>

                            <p>
                                View registered students,
                                account information and
                                manage user accounts.
                            </p>

                        </a>


                        <a
                            href="../dashboard/index.php"
                            class="admin-action-card"
                        >

                            <div class="action-icon">
                                📊
                            </div>

                            <h3>
                                Student Dashboard
                            </h3>

                            <p>
                                Open the main Study Planner
                                application and review the
                                student experience.
                            </p>

                        </a>


                        <a
                            href="logout.php"
                            class="admin-action-card"
                        >

                            <div class="action-icon">
                                🚪
                            </div>

                            <h3>
                                Secure Logout
                            </h3>

                            <p>
                                End the administrator session
                                and return to the admin login.
                            </p>

                        </a>


                    </div>

                </div>


            </div>

        </main>

    </div>

</div>

</body>

</html>