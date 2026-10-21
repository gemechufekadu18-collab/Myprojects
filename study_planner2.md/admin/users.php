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


/*
|--------------------------------------------------------------------------
| FETCH USERS
|--------------------------------------------------------------------------
*/

$stmt = $conn->query("
    SELECT
        id,
        full_name,
        email,
        role,
        created_at
    FROM users
    ORDER BY created_at DESC
");

$users = $stmt->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| STATISTICS
|--------------------------------------------------------------------------
*/

$totalUsers = count($users);

$totalStudents = 0;
$totalAdmins = 0;

foreach ($users as $user) {

    if ($user["role"] === "student") {
        $totalStudents++;
    }

    if ($user["role"] === "admin") {
        $totalAdmins++;
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>User Management - Study Planner</title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

    <style>

        .admin-page {
            max-width: 1400px;
            margin: 0 auto;
        }

        .user-stats {
            display: grid;

            grid-template-columns:
                repeat(3, 1fr);

            gap: 20px;

            margin-bottom: 25px;
        }

        .user-stat {
            background: #ffffff;

            border: 1px solid var(--border);

            border-radius: 16px;

            padding: 20px;

            display: flex;

            align-items: center;

            gap: 15px;
        }

        .user-stat-icon {
            width: 50px;
            height: 50px;

            display: flex;

            align-items: center;
            justify-content: center;

            border-radius: 13px;

            background: #eef2ff;

            font-size: 24px;
        }

        .user-stat h3 {
            margin: 0;

            font-size: 25px;

            color: var(--text);
        }

        .user-stat p {
            margin: 4px 0 0;

            color: var(--muted);

            font-size: 13px;
        }

        .user-avatar-small {
            width: 40px;
            height: 40px;

            display: flex;

            align-items: center;
            justify-content: center;

            border-radius: 50%;

            background:
                linear-gradient(
                    135deg,
                    #4f46e5,
                    #7c3aed
                );

            color: white;

            font-weight: 700;

            flex-shrink: 0;
        }

        .user-info {
            display: flex;

            align-items: center;

            gap: 12px;
        }

        .user-name-cell {
            font-weight: 600;

            color: var(--text);
        }

        .user-email-cell {
            font-size: 12px;

            color: var(--muted);

            margin-top: 3px;
        }

        .role-admin {
            background: #ede9fe;
            color: #6d28d9;
        }

        .role-student {
            background: #dcfce7;
            color: #15803d;
        }

        .delete-btn {
            color: #dc2626;

            text-decoration: none;

            font-weight: 600;

            font-size: 13px;
        }

        .delete-btn:hover {
            text-decoration: underline;
        }

        .empty-users {
            text-align: center;

            padding: 60px 20px;

            color: var(--muted);
        }

        .empty-users-icon {
            font-size: 50px;

            margin-bottom: 15px;
        }

        @media (max-width: 800px) {

            .user-stats {
                grid-template-columns: 1fr;
            }

            .table-wrapper {
                overflow-x: auto;
            }

        }

    </style>

</head>

<body>

<div class="app">


    <!-- SIDEBAR -->

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

                    <span class="nav-icon">
                        📊
                    </span>

                    <span>
                        Dashboard
                    </span>

                </a>

            </li>


            <li>

                <a href="users.php">

                    <span class="nav-icon">
                        👥
                    </span>

                    <span>
                        Users
                    </span>

                </a>

            </li>

        </ul>


        <br>


        <ul class="nav-menu">

            <li>

                <a href="../dashboard/index.php">

                    <span class="nav-icon">
                        🏠
                    </span>

                    <span>
                        Student Area
                    </span>

                </a>

            </li>


            <li>

                <a href="logout.php">

                    <span class="nav-icon">
                        🚪
                    </span>

                    <span>
                        Admin Logout
                    </span>

                </a>

            </li>

        </ul>

    </div>


    <!-- MAIN -->

    <div class="main">


        <!-- TOPBAR -->

        <div class="topbar">

            <div class="topbar-title">
                User Management
            </div>


            <div class="user-area">

                <div>

                    <div class="user-name">

                        <?php

                        echo htmlspecialchars(
                            $_SESSION["admin_name"]
                        );

                        ?>

                    </div>

                    <div
                        class="text-muted"
                        style="font-size:12px;"
                    >
                        Administrator
                    </div>

                </div>


                <div class="user-avatar">

                    <?php

                    echo strtoupper(
                        substr(
                            $_SESSION["admin_name"],
                            0,
                            1
                        )
                    );

                    ?>

                </div>

            </div>

        </div>


        <!-- CONTENT -->

        <main class="content">


            <div class="admin-page">


                <!-- HEADER -->

                <div class="page-header">

                    <div>

                        <h1>
                            User Management
                        </h1>

                        <p class="page-description">
                            View and manage accounts registered
                            in the Study Planner system.
                        </p>

                    </div>


                    <a
                        href="index.php"
                        class="btn btn-secondary"
                    >
                        ← Admin Dashboard
                    </a>

                </div>


                <!-- STATISTICS -->

                <div class="user-stats">


                    <div class="user-stat">

                        <div class="user-stat-icon">
                            👥
                        </div>

                        <div>

                            <h3>
                                <?php
                                echo $totalUsers;
                                ?>
                            </h3>

                            <p>
                                Total Accounts
                            </p>

                        </div>

                    </div>


                    <div class="user-stat">

                        <div class="user-stat-icon">
                            🎓
                        </div>

                        <div>

                            <h3>
                                <?php
                                echo $totalStudents;
                                ?>
                            </h3>

                            <p>
                                Students
                            </p>

                        </div>

                    </div>


                    <div class="user-stat">

                        <div class="user-stat-icon">
                            🔐
                        </div>

                        <div>

                            <h3>
                                <?php
                                echo $totalAdmins;
                                ?>
                            </h3>

                            <p>
                                Administrators
                            </p>

                        </div>

                    </div>


                </div>


                <!-- USER TABLE -->

                <div class="card">

                    <div class="card-header">

                        <div>

                            <h2>
                                Registered Users
                            </h2>

                            <p>
                                All accounts currently registered
                                in the system.
                            </p>

                        </div>

                    </div>


                    <?php if (empty($users)): ?>


                        <div class="empty-users">

                            <div class="empty-users-icon">
                                👥
                            </div>

                            <h2>
                                No users found
                            </h2>

                            <p>
                                There are currently no registered
                                accounts.
                            </p>

                        </div>


                    <?php else: ?>


                        <div class="table-wrapper">

                            <table class="table">

                                <thead>

                                    <tr>

                                        <th>
                                            User
                                        </th>

                                        <th>
                                            Role
                                        </th>

                                        <th>
                                            Registered
                                        </th>

                                        <th>
                                            Action
                                        </th>

                                    </tr>

                                </thead>


                                <tbody>


                                <?php foreach ($users as $user): ?>


                                    <tr>


                                        <!-- USER -->

                                        <td>

                                            <div class="user-info">

                                                <div
                                                    class="user-avatar-small"
                                                >

                                                    <?php

                                                    echo strtoupper(
                                                        substr(
                                                            $user["full_name"],
                                                            0,
                                                            1
                                                        )
                                                    );

                                                    ?>

                                                </div>


                                                <div>

                                                    <div
                                                        class="user-name-cell"
                                                    >

                                                        <?php

                                                        echo htmlspecialchars(
                                                            $user["full_name"]
                                                        );

                                                        ?>

                                                    </div>


                                                    <div
                                                        class="user-email-cell"
                                                    >

                                                        <?php

                                                        echo htmlspecialchars(
                                                            $user["email"]
                                                        );

                                                        ?>

                                                    </div>

                                                </div>

                                            </div>

                                        </td>


                                        <!-- ROLE -->

                                        <td>

                                            <?php if ($user["role"] === "admin"): ?>

                                                <span
                                                    class="badge role-admin"
                                                >
                                                    Administrator
                                                </span>

                                            <?php else: ?>

                                                <span
                                                    class="badge role-student"
                                                >
                                                    Student
                                                </span>

                                            <?php endif; ?>

                                        </td>


                                        <!-- DATE -->

                                        <td>

                                            <?php

                                            echo date(
                                                "M d, Y",
                                                strtotime(
                                                    $user["created_at"]
                                                )
                                            );

                                            ?>

                                        </td>


                                        <!-- ACTION -->

                                        <td>

                                            <?php if (
                                                $user["id"]
                                                !=
                                                $_SESSION["admin_id"]
                                            ): ?>

                                                <a
                                                    href="delete_user.php?id=<?php echo $user["id"]; ?>"
                                                    class="delete-btn"
                                                    onclick="
                                                        return confirm(
                                                            'Are you sure you want to delete this user? This action cannot be undone.'
                                                        );
                                                    "
                                                >
                                                    🗑️ Delete
                                                </a>

                                            <?php else: ?>

                                                <span
                                                    class="text-muted"
                                                >
                                                    Current account
                                                </span>

                                            <?php endif; ?>

                                        </td>


                                    </tr>


                                <?php endforeach; ?>


                                </tbody>

                            </table>

                        </div>


                    <?php endif; ?>


                </div>


            </div>


        </main>


    </div>


</div>

</body>

</html>