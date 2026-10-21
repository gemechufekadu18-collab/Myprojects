<?php
session_start();

require_once __DIR__ . "/../config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION["user_id"];


/* Get progress records */

$stmt = $conn->prepare("
    SELECT
        progress.*,
        subjects.subject_name
    FROM progress
    LEFT JOIN subjects
        ON progress.subject_id = subjects.id
    WHERE progress.user_id = ?
    ORDER BY progress.updated_at DESC
");

$stmt->execute([$user_id]);

$progressRecords = $stmt->fetchAll(PDO::FETCH_ASSOC);


/* Statistics */

$totalRecords = count($progressRecords);

$totalProgress = 0;

foreach ($progressRecords as $record) {
    $totalProgress += (float)$record["progress_percentage"];
}

$averageProgress = 0;

if ($totalRecords > 0) {
    $averageProgress = round(
        $totalProgress / $totalRecords,
        1
    );
}


/* Overall level */

if ($averageProgress >= 80) {
    $progressMessage = "Excellent progress!";
    $progressIcon = "🏆";
} elseif ($averageProgress >= 60) {
    $progressMessage = "Good progress. Keep going!";
    $progressIcon = "🚀";
} elseif ($averageProgress >= 40) {
    $progressMessage = "You're making progress.";
    $progressIcon = "💪";
} elseif ($averageProgress > 0) {
    $progressMessage = "Keep building your study habit.";
    $progressIcon = "📚";
} else {
    $progressMessage = "Start tracking your progress.";
    $progressIcon = "🎯";
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

    <title>Progress | Study Planner</title>

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
                        My Progress
                    </h1>

                    <p class="page-description">
                        Monitor your academic progress across subjects.
                    </p>

                </div>


                <div>

                    <a
                        href="add.php"
                        class="btn btn-primary"
                    >
                        + Add Progress
                    </a>

                </div>

            </div>



            <!-- OVERALL PROGRESS CARD -->

            <div class="welcome-card">

                <div>

                    <div
                        style="
                            font-size: 42px;
                            margin-bottom: 10px;
                        "
                    >
                        <?php echo $progressIcon; ?>
                    </div>

                    <h2>
                        <?php echo $progressMessage; ?>
                    </h2>

                    <p>
                        Your current average academic progress
                    </p>

                </div>


                <div
                    style="
                        text-align: center;
                        min-width: 150px;
                    "
                >

                    <div
                        style="
                            font-size: 42px;
                            font-weight: 800;
                        "
                    >
                        <?php echo $averageProgress; ?>%
                    </div>

                    <div>
                        Overall Progress
                    </div>

                </div>

            </div>



            <!-- STATISTICS -->

            <div class="stats-grid">


                <div class="stat-card">

                    <div class="stat-icon">
                        📚
                    </div>

                    <div>

                        <div class="stat-label">
                            Subjects Tracked
                        </div>

                        <div class="stat-value">
                            <?php echo $totalRecords; ?>
                        </div>

                    </div>

                </div>


                <div class="stat-card">

                    <div class="stat-icon">
                        📈
                    </div>

                    <div>

                        <div class="stat-label">
                            Average Progress
                        </div>

                        <div class="stat-value">
                            <?php echo $averageProgress; ?>%
                        </div>

                    </div>

                </div>


            </div>



            <!-- SUBJECT PROGRESS -->

            <?php if ($totalRecords > 0): ?>


                <div class="card">

                    <div class="card-header">

                        <div>

                            <h2>
                                Subject Progress
                            </h2>

                            <p>
                                Track how far you have progressed in
                                each subject.
                            </p>

                        </div>

                    </div>


                    <div class="progress-list">


                    <?php foreach ($progressRecords as $record): ?>


                        <?php

                        $percentage = (float)
                            $record["progress_percentage"];

                        ?>


                        <div
                            class="progress-item"
                            style="
                                margin-bottom: 28px;
                            "
                        >


                            <!-- SUBJECT HEADER -->

                            <div
                                style="
                                    display: flex;
                                    justify-content: space-between;
                                    align-items: center;
                                    margin-bottom: 8px;
                                "
                            >

                                <div>

                                    <strong>

                                        <?php

                                        if (
                                            !empty(
                                                $record[
                                                    "subject_name"
                                                ]
                                            )
                                        ) {

                                            echo htmlspecialchars(
                                                $record[
                                                    "subject_name"
                                                ]
                                            );

                                        } else {

                                            echo "General Study";

                                        }

                                        ?>

                                    </strong>

                                </div>


                                <strong>

                                    <?php
                                    echo $percentage;
                                    ?>%

                                </strong>

                            </div>



                            <!-- PROGRESS BAR -->

                            <div class="progress-container">

                                <div
                                    class="progress-bar"
                                    style="
                                        width:
                                        <?php
                                        echo min(
                                            100,
                                            max(
                                                0,
                                                $percentage
                                            )
                                        );
                                        ?>%;
                                    "
                                ></div>

                            </div>



                            <!-- UPDATE DATE -->

                            <div
                                style="
                                    margin-top: 7px;
                                    color: var(--muted);
                                    font-size: 12px;
                                "
                            >

                                Last updated:

                                <?php

                                echo date(
                                    "M d, Y",
                                    strtotime(
                                        $record["updated_at"]
                                    )
                                );

                                ?>

                            </div>


                            <!-- ACTIONS -->

                            <div
                                class="actions"
                                style="margin-top: 10px;"
                            >

                                <a
                                    href="edit.php?id=<?php echo $record["id"]; ?>"
                                    class="btn btn-warning btn-small"
                                >
                                    Edit
                                </a>


                                <a
                                    href="delete.php?id=<?php echo $record["id"]; ?>"
                                    class="btn btn-danger btn-small"
                                    onclick="return confirm('Are you sure you want to delete this progress record?');"
                                >
                                    Delete
                                </a>

                            </div>


                        </div>


                    <?php endforeach; ?>


                    </div>

                </div>


            <?php else: ?>


                <!-- EMPTY STATE -->

                <div class="card">

                    <div class="empty-state">

                        <div class="empty-icon">
                            📈
                        </div>

                        <h2>
                            No Progress Records Yet
                        </h2>

                        <p>
                            Start tracking your progress for each
                            subject to see your academic growth.
                        </p>

                        <br>

                        <a
                            href="add.php"
                            class="btn btn-primary"
                        >
                            + Add Your First Progress
                        </a>

                    </div>

                </div>


            <?php endif; ?>


        </div>

    </div>

</div>

</body>

</html>