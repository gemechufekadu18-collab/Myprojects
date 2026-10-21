<?php
session_start();

require_once __DIR__ . "/../config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION["user_id"];

/* Get subjects */
$stmt = $conn->prepare("
    SELECT *
    FROM subjects
    WHERE user_id = ?
    ORDER BY created_at DESC
");

$stmt->execute([$user_id]);
$subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Subjects | Study Planner</title>

    <link rel="stylesheet" href="../assets/css/style.css">
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
                    <h1>My Subjects</h1>

                    <p class="page-description">
                        Manage the subjects you are currently studying.
                    </p>
                </div>

                <div>
                    <a href="add.php" class="btn btn-primary">
                        + Add Subject
                    </a>
                </div>

            </div>


            <!-- SUBJECT COUNT -->
            <div class="stats-grid">

                <div class="stat-card">

                    <div class="stat-icon">
                        📚
                    </div>

                    <div>

                        <div class="stat-label">
                            Total Subjects
                        </div>

                        <div class="stat-value">
                            <?php echo count($subjects); ?>
                        </div>

                    </div>

                </div>

            </div>


            <!-- SUBJECTS -->

            <?php if (count($subjects) > 0): ?>

                <div class="card">

                    <div class="card-header">

                        <div>
                            <h2>All Subjects</h2>

                            <p>
                                Your registered study subjects
                            </p>
                        </div>

                    </div>


                    <div class="table-wrapper">

                        <table class="table">

                            <thead>

                                <tr>

                                    <th>#</th>

                                    <th>Subject</th>

                                    <th>Description</th>

                                    <th>Created</th>

                                    <th>Actions</th>

                                </tr>

                            </thead>


                            <tbody>

                            <?php foreach ($subjects as $index => $subject): ?>

                                <tr>

                                    <td>
                                        <?php echo $index + 1; ?>
                                    </td>


                                    <td>

                                        <strong>
                                            <?php
                                            echo htmlspecialchars(
                                                $subject["subject_name"]
                                            );
                                            ?>
                                        </strong>

                                    </td>


                                    <td>

                                        <?php

                                        if (!empty($subject["description"])) {

                                            echo htmlspecialchars(
                                                $subject["description"]
                                            );

                                        } else {

                                            echo '<span class="text-muted">
                                                    No description
                                                  </span>';

                                        }

                                        ?>

                                    </td>


                                    <td>

                                        <?php
                                        echo date(
                                            "M d, Y",
                                            strtotime($subject["created_at"])
                                        );
                                        ?>

                                    </td>


                                    <td>

                                        <div class="actions">

                                            <a
                                                href="edit.php?id=<?php echo $subject["id"]; ?>"
                                                class="btn btn-warning btn-small"
                                            >
                                                Edit
                                            </a>


                                            <a
                                                href="delete.php?id=<?php echo $subject["id"]; ?>"
                                                class="btn btn-danger btn-small"
                                                onclick="return confirm('Are you sure you want to delete this subject?');"
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
                            📚
                        </div>

                        <h2>No Subjects Yet</h2>

                        <p>
                            Start organizing your studies by adding your
                            first subject.
                        </p>

                        <br>

                        <a
                            href="add.php"
                            class="btn btn-primary"
                        >
                            + Add Your First Subject
                        </a>

                    </div>

                </div>


            <?php endif; ?>


        </div>

    </div>

</div>

</body>

</html>