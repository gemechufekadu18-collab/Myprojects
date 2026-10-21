<?php
session_start();

require_once __DIR__ . "/../config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION["user_id"];

/* Get study sessions */
$stmt = $conn->prepare("
    SELECT
        study_sessions.*,
        subjects.subject_name
    FROM study_sessions
    LEFT JOIN subjects
        ON study_sessions.subject_id = subjects.id
    WHERE study_sessions.user_id = ?
    ORDER BY study_sessions.study_date DESC,
             study_sessions.start_time DESC
");

$stmt->execute([$user_id]);

$sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);


/* Statistics */

$totalSessions = count($sessions);

$totalMinutes = 0;

foreach ($sessions as $session) {
    $totalMinutes += (int)$session["duration_minutes"];
}

$totalHours = floor($totalMinutes / 60);
$remainingMinutes = $totalMinutes % 60;


/* Average session */

$averageMinutes = 0;

if ($totalSessions > 0) {
    $averageMinutes = round($totalMinutes / $totalSessions);
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

    <title>Study Sessions | Study Planner</title>

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
                        Study Sessions
                    </h1>

                    <p class="page-description">
                        Track your study time and build consistent habits.
                    </p>

                </div>


                <div>

                    <a
                        href="add.php"
                        class="btn btn-primary"
                    >
                        + Record Session
                    </a>

                </div>

            </div>



            <!-- STATISTICS -->

            <div class="stats-grid">


                <!-- TOTAL SESSIONS -->

                <div class="stat-card">

                    <div class="stat-icon">
                        ⏱️
                    </div>

                    <div>

                        <div class="stat-label">
                            Total Sessions
                        </div>

                        <div class="stat-value">
                            <?php echo $totalSessions; ?>
                        </div>

                    </div>

                </div>



                <!-- TOTAL TIME -->

                <div class="stat-card">

                    <div class="stat-icon">
                        🕐
                    </div>

                    <div>

                        <div class="stat-label">
                            Total Study Time
                        </div>

                        <div class="stat-value">

                            <?php echo $totalHours; ?>

                            <span style="font-size:14px;">
                                h
                            </span>

                            <?php echo $remainingMinutes; ?>

                            <span style="font-size:14px;">
                                m
                            </span>

                        </div>

                    </div>

                </div>



                <!-- AVERAGE -->

                <div class="stat-card">

                    <div class="stat-icon">
                        📊
                    </div>

                    <div>

                        <div class="stat-label">
                            Average Session
                        </div>

                        <div class="stat-value">

                            <?php echo $averageMinutes; ?>

                            <span style="font-size:14px;">
                                min
                            </span>

                        </div>

                    </div>

                </div>

            </div>



            <!-- SESSION LIST -->

            <?php if ($totalSessions > 0): ?>


                <div class="card">

                    <div class="card-header">

                        <div>

                            <h2>
                                Study History
                            </h2>

                            <p>
                                Review your previous study sessions.
                            </p>

                        </div>

                    </div>


                    <div class="table-wrapper">

                        <table class="table">

                            <thead>

                                <tr>

                                    <th>#</th>

                                    <th>Date</th>

                                    <th>Subject</th>

                                    <th>Time</th>

                                    <th>Duration</th>

                                    <th>Notes</th>

                                    <th>Actions</th>

                                </tr>

                            </thead>


                            <tbody>


                            <?php foreach ($sessions as $index => $session): ?>


                                <tr>


                                    <!-- NUMBER -->

                                    <td>
                                        <?php echo $index + 1; ?>
                                    </td>



                                    <!-- DATE -->

                                    <td>

                                        <strong>

                                            <?php
                                            echo date(
                                                "M d, Y",
                                                strtotime(
                                                    $session["study_date"]
                                                )
                                            );
                                            ?>

                                        </strong>

                                    </td>



                                    <!-- SUBJECT -->

                                    <td>

                                        <?php

                                        if (
                                            !empty(
                                                $session["subject_name"]
                                            )
                                        ) {

                                            echo htmlspecialchars(
                                                $session["subject_name"]
                                            );

                                        } else {

                                            echo '<span class="text-muted">
                                                    General Study
                                                  </span>';

                                        }

                                        ?>

                                    </td>



                                    <!-- TIME -->

                                    <td>

                                        <?php

                                        if (
                                            !empty(
                                                $session["start_time"]
                                            )
                                            &&
                                            !empty(
                                                $session["end_time"]
                                            )
                                        ):

                                        ?>

                                            <span class="time-badge">

                                                <?php
                                                echo date(
                                                    "g:i A",
                                                    strtotime(
                                                        $session["start_time"]
                                                    )
                                                );
                                                ?>

                                                -

                                                <?php
                                                echo date(
                                                    "g:i A",
                                                    strtotime(
                                                        $session["end_time"]
                                                    )
                                                );
                                                ?>

                                            </span>

                                        <?php else: ?>

                                            <span class="text-muted">
                                                Not specified
                                            </span>

                                        <?php endif; ?>

                                    </td>



                                    <!-- DURATION -->

                                    <td>

                                        <strong>

                                            <?php
                                            echo $session[
                                                "duration_minutes"
                                            ];
                                            ?>

                                        </strong>

                                        min

                                    </td>



                                    <!-- NOTES -->

                                    <td>

                                        <?php

                                        if (
                                            !empty(
                                                $session["notes"]
                                            )
                                        ) {

                                            echo htmlspecialchars(
                                                $session["notes"]
                                            );

                                        } else {

                                            echo '<span class="text-muted">
                                                    —
                                                  </span>';

                                        }

                                        ?>

                                    </td>



                                    <!-- ACTIONS -->

                                    <td>

                                        <div class="actions">


                                            <a
                                                href="edit.php?id=<?php echo $session["id"]; ?>"
                                                class="btn btn-warning btn-small"
                                            >
                                                Edit
                                            </a>


                                            <a
                                                href="delete.php?id=<?php echo $session["id"]; ?>"
                                                class="btn btn-danger btn-small"
                                                onclick="return confirm('Are you sure you want to delete this study session?');"
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
                            ⏱️
                        </div>

                        <h2>
                            No Study Sessions Yet
                        </h2>

                        <p>
                            Record your study time to understand your
                            learning habits and improve consistency.
                        </p>

                        <br>

                        <a
                            href="add.php"
                            class="btn btn-primary"
                        >
                            + Record Your First Session
                        </a>

                    </div>

                </div>


            <?php endif; ?>


        </div>

    </div>

</div>

</body>

</html>