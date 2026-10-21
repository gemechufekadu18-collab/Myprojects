<?php
session_start();

require_once __DIR__ . "/../config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION["user_id"];

/* Get all tasks */
$stmt = $conn->prepare("
    SELECT 
        tasks.*,
        subjects.subject_name
    FROM tasks
    LEFT JOIN subjects 
        ON tasks.subject_id = subjects.id
    WHERE tasks.user_id = ?
    ORDER BY 
        tasks.status ASC,
        tasks.due_date ASC,
        tasks.created_at DESC
");

$stmt->execute([$user_id]);

$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);


/* Task statistics */

$totalTasks = count($tasks);

$completedTasks = 0;
$pendingTasks = 0;
$highPriorityTasks = 0;

foreach ($tasks as $task) {

    if ($task["status"] === "completed") {
        $completedTasks++;
    } else {
        $pendingTasks++;
    }

    if ($task["priority"] === "high" && $task["status"] !== "completed") {
        $highPriorityTasks++;
    }
}


/* Completion percentage */

$completionPercentage = 0;

if ($totalTasks > 0) {
    $completionPercentage =
        round(($completedTasks / $totalTasks) * 100);
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

    <title>Tasks | Study Planner</title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

</head>

<body>

<div class="app">


    <!-- SIDEBAR -->

    <?php require_once __DIR__ . "/../includes/sidebar.php"; ?>


    <div class="main">


        <!-- TOPBAR -->

        <?php require_once __DIR__ . "/../includes/topbar.php"; ?>


        <div class="content">


            <!-- PAGE HEADER -->

            <div class="page-header">

                <div>

                    <h1>My Tasks</h1>

                    <p class="page-description">
                        Organize assignments, study tasks and deadlines.
                    </p>

                </div>


                <div>

                    <a
                        href="add.php"
                        class="btn btn-primary"
                    >
                        + Add Task
                    </a>

                </div>

            </div>



            <!-- STATISTICS -->

            <div class="stats-grid">


                <!-- TOTAL -->

                <div class="stat-card">

                    <div class="stat-icon">
                        📋
                    </div>

                    <div>

                        <div class="stat-label">
                            Total Tasks
                        </div>

                        <div class="stat-value">
                            <?php echo $totalTasks; ?>
                        </div>

                    </div>

                </div>


                <!-- PENDING -->

                <div class="stat-card">

                    <div class="stat-icon">
                        ⏳
                    </div>

                    <div>

                        <div class="stat-label">
                            Pending
                        </div>

                        <div class="stat-value">
                            <?php echo $pendingTasks; ?>
                        </div>

                    </div>

                </div>


                <!-- COMPLETED -->

                <div class="stat-card">

                    <div class="stat-icon">
                        ✅
                    </div>

                    <div>

                        <div class="stat-label">
                            Completed
                        </div>

                        <div class="stat-value">
                            <?php echo $completedTasks; ?>
                        </div>

                    </div>

                </div>


                <!-- HIGH PRIORITY -->

                <div class="stat-card">

                    <div class="stat-icon">
                        🔥
                    </div>

                    <div>

                        <div class="stat-label">
                            High Priority
                        </div>

                        <div class="stat-value">
                            <?php echo $highPriorityTasks; ?>
                        </div>

                    </div>

                </div>

            </div>



            <!-- OVERALL PROGRESS -->

            <div class="card">

                <div class="card-header">

                    <div>

                        <h2>
                            Task Completion
                        </h2>

                        <p>
                            Overall progress on your tasks
                        </p>

                    </div>

                    <strong>
                        <?php echo $completionPercentage; ?>%
                    </strong>

                </div>


                <div class="progress-container">

                    <div
                        class="progress-bar"
                        style="width: <?php echo $completionPercentage; ?>%;"
                    ></div>

                </div>

            </div>



            <!-- TASK LIST -->

            <?php if ($totalTasks > 0): ?>

                <div class="card">

                    <div class="card-header">

                        <div>

                            <h2>
                                All Tasks
                            </h2>

                            <p>
                                Manage your study activities and deadlines.
                            </p>

                        </div>

                    </div>


                    <div class="table-wrapper">

                        <table class="table">

                            <thead>

                                <tr>

                                    <th>#</th>

                                    <th>Task</th>

                                    <th>Subject</th>

                                    <th>Due Date</th>

                                    <th>Priority</th>

                                    <th>Status</th>

                                    <th>Actions</th>

                                </tr>

                            </thead>


                            <tbody>

                            <?php foreach ($tasks as $index => $task): ?>

                                <tr>


                                    <!-- NUMBER -->

                                    <td>

                                        <?php
                                        echo $index + 1;
                                        ?>

                                    </td>



                                    <!-- TASK -->

                                    <td>

                                        <strong>

                                            <?php
                                            echo htmlspecialchars(
                                                $task["title"]
                                            );
                                            ?>

                                        </strong>


                                        <?php if (!empty($task["description"])): ?>

                                            <div
                                                style="
                                                    color: var(--muted);
                                                    font-size: 13px;
                                                    margin-top: 4px;
                                                "
                                            >

                                                <?php
                                                echo htmlspecialchars(
                                                    $task["description"]
                                                );
                                                ?>

                                            </div>

                                        <?php endif; ?>

                                    </td>



                                    <!-- SUBJECT -->

                                    <td>

                                        <?php

                                        if (!empty($task["subject_name"])) {

                                            echo htmlspecialchars(
                                                $task["subject_name"]
                                            );

                                        } else {

                                            echo '<span class="text-muted">
                                                    No subject
                                                  </span>';

                                        }

                                        ?>

                                    </td>



                                    <!-- DATE -->

                                    <td>

                                        <?php
                                        echo date(
                                            "M d, Y",
                                            strtotime($task["due_date"])
                                        );
                                        ?>

                                    </td>



                                    <!-- PRIORITY -->

                                    <td>

                                        <?php

                                        if ($task["priority"] === "high"):

                                        ?>

                                            <span class="badge badge-danger">
                                                High
                                            </span>

                                        <?php

                                        elseif ($task["priority"] === "medium"):

                                        ?>

                                            <span class="badge badge-warning">
                                                Medium
                                            </span>

                                        <?php else: ?>

                                            <span class="badge badge-success">
                                                Low
                                            </span>

                                        <?php endif; ?>

                                    </td>



                                    <!-- STATUS -->

                                    <td>

                                        <?php

                                        if ($task["status"] === "completed"):

                                        ?>

                                            <span class="badge badge-success">
                                                Completed
                                            </span>

                                        <?php else: ?>

                                            <span class="badge badge-warning">
                                                Pending
                                            </span>

                                        <?php endif; ?>

                                    </td>



                                    <!-- ACTIONS -->

                                    <td>

                                        <div class="actions">


                                            <?php if ($task["status"] !== "completed"): ?>

                                                <a
                                                    href="complete.php?id=<?php echo $task["id"]; ?>"
                                                    class="btn btn-success btn-small"
                                                >
                                                    Complete
                                                </a>

                                            <?php endif; ?>


                                            <a
                                                href="edit.php?id=<?php echo $task["id"]; ?>"
                                                class="btn btn-warning btn-small"
                                            >
                                                Edit
                                            </a>


                                            <a
                                                href="delete.php?id=<?php echo $task["id"]; ?>"
                                                class="btn btn-danger btn-small"
                                                onclick="return confirm('Are you sure you want to delete this task?');"
                                            >
                                                Delete
                                            </a>

                                        </div>

                                    </td>


                                </tr>

                            <?php endforeach; ?>

                            </tbody>

                        </table>

                    </div>

                </div>


            <?php else: ?>


                <!-- EMPTY STATE -->

                <div class="card">

                    <div class="empty-state">

                        <div class="empty-icon">
                            ✅
                        </div>

                        <h2>
                            No Tasks Yet
                        </h2>

                        <p>
                            Create your first task and start
                            organizing your study work.
                        </p>

                        <br>

                        <a
                            href="add.php"
                            class="btn btn-primary"
                        >
                            + Create Your First Task
                        </a>

                    </div>

                </div>

            <?php endif; ?>


        </div>

    </div>

</div>

</body>

</html>