<?php
session_start();

require_once __DIR__ . "/../config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION["user_id"];

/* Get timetable */
$stmt = $conn->prepare("
    SELECT 
        timetable.*,
        subjects.subject_name
    FROM timetable
    LEFT JOIN subjects
        ON timetable.subject_id = subjects.id
    WHERE timetable.user_id = ?
    ORDER BY
        FIELD(
            timetable.day_of_week,
            'Monday',
            'Tuesday',
            'Wednesday',
            'Thursday',
            'Friday',
            'Saturday',
            'Sunday'
        ),
        timetable.start_time
");

$stmt->execute([$user_id]);

$timetable = $stmt->fetchAll(PDO::FETCH_ASSOC);


/* Statistics */

$totalClasses = count($timetable);

$daysUsed = [];

foreach ($timetable as $item) {
    $daysUsed[] = $item["day_of_week"];
}

$activeDays = count(array_unique($daysUsed));

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Timetable | Study Planner</title>

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
                        My Timetable
                    </h1>

                    <p class="page-description">
                        Organize your weekly classes and study schedule.
                    </p>

                </div>


                <div>

                    <a
                        href="add.php"
                        class="btn btn-primary"
                    >
                        + Add Schedule
                    </a>

                </div>

            </div>



            <!-- STATISTICS -->

            <div class="stats-grid">


                <div class="stat-card">

                    <div class="stat-icon">
                        📅
                    </div>

                    <div>

                        <div class="stat-label">
                            Total Classes
                        </div>

                        <div class="stat-value">
                            <?php echo $totalClasses; ?>
                        </div>

                    </div>

                </div>



                <div class="stat-card">

                    <div class="stat-icon">
                        🗓️
                    </div>

                    <div>

                        <div class="stat-label">
                            Active Days
                        </div>

                        <div class="stat-value">
                            <?php echo $activeDays; ?>
                        </div>

                    </div>

                </div>

            </div>



            <!-- TIMETABLE -->

            <?php if ($totalClasses > 0): ?>


                <div class="card">

                    <div class="card-header">

                        <div>

                            <h2>
                                Weekly Schedule
                            </h2>

                            <p>
                                Your classes and study activities.
                            </p>

                        </div>

                    </div>


                    <div class="table-wrapper">

                        <table class="table">

                            <thead>

                                <tr>

                                    <th>Day</th>

                                    <th>Time</th>

                                    <th>Subject</th>

                                    <th>Room</th>

                                    <th>Notes</th>

                                    <th>Actions</th>

                                </tr>

                            </thead>


                            <tbody>


                            <?php foreach ($timetable as $item): ?>


                                <tr>


                                    <!-- DAY -->

                                    <td>

                                        <strong>

                                            <?php
                                            echo htmlspecialchars(
                                                $item["day_of_week"]
                                            );
                                            ?>

                                        </strong>

                                    </td>



                                    <!-- TIME -->

                                    <td>

                                        <span class="time-badge">

                                            <?php
                                            echo date(
                                                "g:i A",
                                                strtotime(
                                                    $item["start_time"]
                                                )
                                            );
                                            ?>

                                            -

                                            <?php
                                            echo date(
                                                "g:i A",
                                                strtotime(
                                                    $item["end_time"]
                                                )
                                            );
                                            ?>

                                        </span>

                                    </td>



                                    <!-- SUBJECT -->

                                    <td>

                                        <?php

                                        if (
                                            !empty(
                                                $item["subject_name"]
                                            )
                                        ) {

                                            echo htmlspecialchars(
                                                $item["subject_name"]
                                            );

                                        } else {

                                            echo '<span class="text-muted">
                                                    General Study
                                                  </span>';

                                        }

                                        ?>

                                    </td>



                                    <!-- ROOM -->

                                    <td>

                                        <?php

                                        if (!empty($item["room"])) {

                                            echo htmlspecialchars(
                                                $item["room"]
                                            );

                                        } else {

                                            echo '<span class="text-muted">
                                                    —
                                                  </span>';

                                        }

                                        ?>

                                    </td>



                                    <!-- NOTES -->

                                    <td>

                                        <?php

                                        if (!empty($item["notes"])) {

                                            echo htmlspecialchars(
                                                $item["notes"]
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
                                                href="edit.php?id=<?php echo $item["id"]; ?>"
                                                class="btn btn-warning btn-small"
                                            >
                                                Edit
                                            </a>


                                            <a
                                                href="delete.php?id=<?php echo $item["id"]; ?>"
                                                class="btn btn-danger btn-small"
                                                onclick="return confirm('Are you sure you want to delete this schedule?');"
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
                            📅
                        </div>

                        <h2>
                            No Timetable Yet
                        </h2>

                        <p>
                            Add your classes and study periods to build
                            your weekly schedule.
                        </p>

                        <br>

                        <a
                            href="add.php"
                            class="btn btn-primary"
                        >
                            + Create Your Timetable
                        </a>

                    </div>

                </div>


            <?php endif; ?>


        </div>

    </div>

</div>

</body>

</html>