<?php

session_start();

require_once "../config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION["user_id"];


/* =========================
   DASHBOARD STATISTICS
   ========================= */

$stmt = $conn->prepare(
    "SELECT COUNT(*) FROM subjects WHERE user_id = ?"
);
$stmt->execute([$user_id]);
$subjectCount = $stmt->fetchColumn();


$stmt = $conn->prepare(
    "SELECT COUNT(*) FROM tasks
     WHERE user_id = ? AND status = 'pending'"
);
$stmt->execute([$user_id]);
$pendingTasks = $stmt->fetchColumn();


$stmt = $conn->prepare(
    "SELECT COUNT(*) FROM study_plans
     WHERE user_id = ?"
);
$stmt->execute([$user_id]);
$planCount = $stmt->fetchColumn();


$stmt = $conn->prepare(
    "SELECT COUNT(*) FROM notifications
     WHERE user_id = ? AND is_read = FALSE"
);
$stmt->execute([$user_id]);
$unreadNotifications = $stmt->fetchColumn();


/* =========================
   TODAY'S TASKS
   ========================= */

$stmt = $conn->prepare("
    SELECT
        tasks.id,
        tasks.title,
        tasks.due_date,
        tasks.priority,
        tasks.status,
        subjects.subject_name
    FROM tasks

    LEFT JOIN subjects
        ON tasks.subject_id = subjects.id

    WHERE tasks.user_id = ?
      AND tasks.status = 'pending'

    ORDER BY tasks.due_date ASC

    LIMIT 5
");

$stmt->execute([$user_id]);

$todayTasks = $stmt->fetchAll(PDO::FETCH_ASSOC);


/* =========================
   PROGRESS
   ========================= */

$stmt = $conn->prepare("
    SELECT
        AVG(progress_percentage)
    FROM progress
    WHERE user_id = ?
");

$stmt->execute([$user_id]);

$averageProgress = $stmt->fetchColumn();

$averageProgress = $averageProgress
    ? round($averageProgress)
    : 0;

?>

<!DOCTYPE html>

<html>

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Dashboard - Study Planner</title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

</head>

<body>

<div class="app">


    <!-- SIDEBAR -->

    <?php require_once "../includes/sidebar.php"; ?>


    <!-- MAIN CONTENT -->

    <div class="main">


        <!-- TOPBAR -->

        <?php require_once "../includes/topbar.php"; ?>


        <div class="content">


            <!-- WELCOME -->

            <div class="welcome-card">

                <h1>
                    Welcome back,
                    <?php
                    echo htmlspecialchars($_SESSION["full_name"]);
                    ?>
                    👋
                </h1>

                <p>
                    Stay organized, study consistently,
                    and move closer to your academic goals.
                </p>

            </div>


            <!-- STATISTICS -->

            <div class="stats-grid">


                <div class="stat-card">

                    <div class="stat-top">

                        <div>
                            <div class="stat-label">
                                My Subjects
                            </div>

                            <div class="stat-number">
                                <?php echo $subjectCount; ?>
                            </div>
                        </div>

                        <div class="stat-icon">
                            📚
                        </div>

                    </div>

                </div>


                <div class="stat-card">

                    <div class="stat-top">

                        <div>
                            <div class="stat-label">
                                Pending Tasks
                            </div>

                            <div class="stat-number">
                                <?php echo $pendingTasks; ?>
                            </div>
                        </div>

                        <div class="stat-icon">
                            ✅
                        </div>

                    </div>

                </div>


                <div class="stat-card">

                    <div class="stat-top">

                        <div>
                            <div class="stat-label">
                                Study Plans
                            </div>

                            <div class="stat-number">
                                <?php echo $planCount; ?>
                            </div>
                        </div>

                        <div class="stat-icon">
                            📋
                        </div>

                    </div>

                </div>


                <div class="stat-card">

                    <div class="stat-top">

                        <div>
                            <div class="stat-label">
                                Average Progress
                            </div>

                            <div class="stat-number">
                                <?php echo $averageProgress; ?>%
                            </div>
                        </div>

                        <div class="stat-icon">
                            📈
                        </div>

                    </div>

                </div>

            </div>


            <!-- MAIN GRID -->

            <div class="grid-2">


                <!-- TASKS -->

                <div class="card">

                    <div class="card-header">

                        <h2>
                            Upcoming Tasks
                        </h2>

                        <a
                            href="../tasks/index.php"
                            class="btn btn-primary btn-small"
                        >
                            View All
                        </a>

                    </div>


                    <?php if (count($todayTasks) > 0): ?>

                        <div class="table-wrapper">

                            <table class="table">

                                <thead>

                                    <tr>

                                        <th>Task</th>

                                        <th>Subject</th>

                                        <th>Due</th>

                                        <th>Priority</th>

                                    </tr>

                                </thead>

                                <tbody>

                                <?php foreach ($todayTasks as $task): ?>

                                    <tr>

                                        <td>
                                            <?php
                                            echo htmlspecialchars(
                                                $task["title"]
                                            );
                                            ?>
                                        </td>

                                        <td>
                                            <?php
                                            echo htmlspecialchars(
                                                $task["subject_name"]
                                                ?? "No subject"
                                            );
                                            ?>
                                        </td>

                                        <td>
                                            <?php
                                            echo htmlspecialchars(
                                                $task["due_date"]
                                            );
                                            ?>
                                        </td>

                                        <td>

                                            <?php if (
                                                $task["priority"] === "high"
                                            ): ?>

                                                <span
                                                    class="badge badge-danger"
                                                >
                                                    High
                                                </span>

                                            <?php elseif (
                                                $task["priority"] === "medium"
                                            ): ?>

                                                <span
                                                    class="badge badge-warning"
                                                >
                                                    Medium
                                                </span>

                                            <?php else: ?>

                                                <span
                                                    class="badge badge-info"
                                                >
                                                    Low
                                                </span>

                                            <?php endif; ?>

                                        </td>

                                    </tr>

                                <?php endforeach; ?>

                                </tbody>

                            </table>

                        </div>

                    <?php else: ?>

                        <div class="empty-state">

                            <div class="empty-icon">
                                🎉
                            </div>

                            <h3>
                                No pending tasks
                            </h3>

                            <p>
                                Great job! You are all caught up.
                            </p>

                        </div>

                    <?php endif; ?>

                </div>


                <!-- PROGRESS -->

                <div class="card">

                    <div class="card-header">

                        <h2>
                            Overall Progress
                        </h2>

                        <strong>
                            <?php echo $averageProgress; ?>%
                        </strong>

                    </div>


                    <div
                        class="progress-container"
                        style="margin-bottom: 15px;"
                    >

                        <div
                            class="progress-bar"
                            style="width:
                            <?php echo $averageProgress; ?>%;"
                        >
                        </div>

                    </div>


                    <p
                        style="
                        color: var(--muted);
                        font-size: 13px;
                        "
                    >
                        Keep studying consistently.
                        Every session moves you forward.
                    </p>


                    <br>

                    <a
                        href="../progress/index.php"
                        class="btn btn-primary"
                    >
                        View Progress
                    </a>

                </div>


            </div>


            <!-- QUICK ACTIONS -->

            <div class="card">

                <div class="card-header">

                    <h2>
                        Quick Actions
                    </h2>

                </div>


                <div class="actions">

                    <a
                        href="../subjects/add.php"
                        class="btn btn-primary"
                    >
                        + Add Subject
                    </a>

                    <a
                        href="../tasks/add.php"
                        class="btn btn-success"
                    >
                        + Add Task
                    </a>

                    <a
                        href="../study_plans/add.php"
                        class="btn btn-secondary"
                    >
                        + Create Study Plan
                    </a>

                    <a
                        href="../study_sessions/add.php"
                        class="btn btn-warning"
                    >
                        + Record Study Session
                    </a>

                    <a
                        href="../timetable/add.php"
                        class="btn btn-secondary"
                    >
                        + Add Timetable
                    </a>

                </div>

            </div>


        </div>

    </div>

</div>

</body>

</html>