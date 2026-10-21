<?php
session_start();

require_once __DIR__ . "/../config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION["user_id"];

/* Get study plans */
$stmt = $conn->prepare("
    SELECT *
    FROM study_plans
    WHERE user_id = ?
    ORDER BY start_date ASC, created_at DESC
");

$stmt->execute([$user_id]);

$plans = $stmt->fetchAll(PDO::FETCH_ASSOC);


/* Statistics */

$totalPlans = count($plans);

$pendingPlans = 0;
$activePlans = 0;
$completedPlans = 0;

foreach ($plans as $plan) {

    if ($plan["status"] === "pending") {
        $pendingPlans++;
    }

    if ($plan["status"] === "active") {
        $activePlans++;
    }

    if ($plan["status"] === "completed") {
        $completedPlans++;
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

    <title>Study Plans | Study Planner</title>

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

                    <h1>
                        Study Plans
                    </h1>

                    <p class="page-description">
                        Create and manage your academic study plans.
                    </p>

                </div>


                <div>

                    <a
                        href="add.php"
                        class="btn btn-primary"
                    >
                        + Create Study Plan
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
                            Total Plans
                        </div>

                        <div class="stat-value">
                            <?php echo $totalPlans; ?>
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
                            <?php echo $pendingPlans; ?>
                        </div>

                    </div>

                </div>



                <!-- ACTIVE -->

                <div class="stat-card">

                    <div class="stat-icon">
                        🚀
                    </div>

                    <div>

                        <div class="stat-label">
                            Active
                        </div>

                        <div class="stat-value">
                            <?php echo $activePlans; ?>
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
                            <?php echo $completedPlans; ?>
                        </div>

                    </div>

                </div>

            </div>



            <!-- STUDY PLANS -->

            <?php if ($totalPlans > 0): ?>


                <div class="card">

                    <div class="card-header">

                        <div>

                            <h2>
                                My Study Plans
                            </h2>

                            <p>
                                Track your academic goals and study periods.
                            </p>

                        </div>

                    </div>


                    <div class="table-wrapper">

                        <table class="table">

                            <thead>

                                <tr>

                                    <th>#</th>

                                    <th>Plan</th>

                                    <th>Start Date</th>

                                    <th>End Date</th>

                                    <th>Duration</th>

                                    <th>Status</th>

                                    <th>Actions</th>

                                </tr>

                            </thead>


                            <tbody>


                            <?php foreach ($plans as $index => $plan): ?>


                                <?php

                                $start = new DateTime(
                                    $plan["start_date"]
                                );

                                $end = new DateTime(
                                    $plan["end_date"]
                                );

                                $duration =
                                    $start->diff($end)->days + 1;

                                ?>


                                <tr>


                                    <!-- NUMBER -->

                                    <td>
                                        <?php echo $index + 1; ?>
                                    </td>



                                    <!-- PLAN -->

                                    <td>

                                        <strong>

                                            <?php
                                            echo htmlspecialchars(
                                                $plan["title"]
                                            );
                                            ?>

                                        </strong>


                                        <?php if (!empty($plan["description"])): ?>

                                            <div
                                                style="
                                                    color: var(--muted);
                                                    font-size: 13px;
                                                    margin-top: 5px;
                                                "
                                            >

                                                <?php
                                                echo htmlspecialchars(
                                                    $plan["description"]
                                                );
                                                ?>

                                            </div>

                                        <?php endif; ?>

                                    </td>



                                    <!-- START -->

                                    <td>

                                        <?php
                                        echo date(
                                            "M d, Y",
                                            strtotime(
                                                $plan["start_date"]
                                            )
                                        );
                                        ?>

                                    </td>



                                    <!-- END -->

                                    <td>

                                        <?php
                                        echo date(
                                            "M d, Y",
                                            strtotime(
                                                $plan["end_date"]
                                            )
                                        );
                                        ?>

                                    </td>



                                    <!-- DURATION -->

                                    <td>

                                        <strong>
                                            <?php echo $duration; ?>
                                        </strong>

                                        days

                                    </td>



                                    <!-- STATUS -->

                                    <td>

                                        <?php

                                        if (
                                            $plan["status"]
                                            === "completed"
                                        ):

                                        ?>

                                            <span
                                                class="badge badge-success"
                                            >
                                                Completed
                                            </span>


                                        <?php

                                        elseif (
                                            $plan["status"]
                                            === "active"
                                        ):

                                        ?>

                                            <span
                                                class="badge badge-primary"
                                            >
                                                Active
                                            </span>


                                        <?php else: ?>

                                            <span
                                                class="badge badge-warning"
                                            >
                                                Pending
                                            </span>

                                        <?php endif; ?>

                                    </td>



                                    <!-- ACTIONS -->

                                    <td>

                                        <div class="actions">


                                            <a
                                                href="edit.php?id=<?php echo $plan["id"]; ?>"
                                                class="btn btn-warning btn-small"
                                            >
                                                Edit
                                            </a>


                                            <a
                                                href="delete.php?id=<?php echo $plan["id"]; ?>"
                                                class="btn btn-danger btn-small"
                                                onclick="return confirm('Are you sure you want to delete this study plan?');"
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
                            📋
                        </div>

                        <h2>
                            No Study Plans Yet
                        </h2>

                        <p>
                            Create your first study plan to organize
                            your academic goals and deadlines.
                        </p>

                        <br>

                        <a
                            href="add.php"
                            class="btn btn-primary"
                        >
                            + Create Your First Plan
                        </a>

                    </div>

                </div>


            <?php endif; ?>


        </div>

    </div>

</div>

</body>

</html>