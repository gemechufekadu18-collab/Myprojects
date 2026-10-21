<?php
session_start();

require_once __DIR__ . "/../config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION["user_id"];

$error = "";


/*
|--------------------------------------------------------------------------
| Get User Subjects
|--------------------------------------------------------------------------
*/

$subjectStmt = $conn->prepare("
    SELECT id, subject_name
    FROM subjects
    WHERE user_id = ?
    ORDER BY subject_name ASC
");

$subjectStmt->execute([$user_id]);

$subjects = $subjectStmt->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| Add Progress
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $subject_id = !empty($_POST["subject_id"])
        ? (int) $_POST["subject_id"]
        : null;

    $progress_percentage = $_POST["progress_percentage"] ?? "";


    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    if ($subject_id === null) {

        $error = "Please select a subject.";

    } elseif ($progress_percentage === "") {

        $error = "Progress percentage is required.";

    } elseif (
        !is_numeric($progress_percentage) ||
        $progress_percentage < 0 ||
        $progress_percentage > 100
    ) {

        $error = "Progress must be a number between 0 and 100.";

    } else {

        /*
        |--------------------------------------------------------------------------
        | Check if Progress Already Exists
        |--------------------------------------------------------------------------
        */

        $check = $conn->prepare("
            SELECT id
            FROM progress
            WHERE user_id = ? AND subject_id = ?
        ");

        $check->execute([
            $user_id,
            $subject_id
        ]);

        $existing = $check->fetch(PDO::FETCH_ASSOC);


        if ($existing) {

            /*
            |--------------------------------------------------------------------------
            | Update Existing Progress
            |--------------------------------------------------------------------------
            */

            $update = $conn->prepare("
                UPDATE progress
                SET progress_percentage = ?
                WHERE id = ? AND user_id = ?
            ");

            $update->execute([
                $progress_percentage,
                $existing["id"],
                $user_id
            ]);

        } else {

            /*
            |--------------------------------------------------------------------------
            | Insert New Progress
            |--------------------------------------------------------------------------
            */

            $insert = $conn->prepare("
                INSERT INTO progress
                (
                    user_id,
                    subject_id,
                    progress_percentage
                )
                VALUES (?, ?, ?)
            ");

            $insert->execute([
                $user_id,
                $subject_id,
                $progress_percentage
            ]);
        }


        header("Location: index.php");
        exit;
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

    <title>Add Progress - Study Planner</title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

</head>

<body>

<div class="app">

    <?php require_once __DIR__ . "/../includes/sidebar.php"; ?>

    <div class="main">

        <?php require_once __DIR__ . "/../includes/topbar.php"; ?>

        <main class="content">


            <!-- Page Header -->

            <div class="page-header">

                <div>

                    <h1>Track Progress</h1>

                    <p class="page-description">
                        Record your learning progress for each subject.
                    </p>

                </div>

                <a
                    href="index.php"
                    class="btn btn-secondary"
                >
                    ← Back to Progress
                </a>

            </div>


            <!-- Error -->

            <?php if ($error): ?>

                <div class="alert alert-danger">

                    <?php echo htmlspecialchars($error); ?>

                </div>

            <?php endif; ?>


            <!-- Form Card -->

            <div class="card form-card">

                <div class="card-header">

                    <div>

                        <h2>Progress Details</h2>

                        <p>
                            Select a subject and enter your current completion percentage.
                        </p>

                    </div>

                </div>


                <form method="POST">


                    <!-- Subject -->

                    <div class="form-group">

                        <label for="subject_id">
                            Subject
                        </label>

                        <select
                            id="subject_id"
                            name="subject_id"
                            class="form-control"
                            required
                        >

                            <option value="">
                                Select Subject
                            </option>

                            <?php foreach ($subjects as $subject): ?>

                                <option
                                    value="<?php echo $subject["id"]; ?>"
                                    <?php echo (($_POST["subject_id"] ?? "") == $subject["id"]) ? "selected" : ""; ?>
                                >

                                    <?php echo htmlspecialchars($subject["subject_name"]); ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>


                    <!-- Progress -->

                    <div class="form-group">

                        <label for="progress_percentage">
                            Progress Percentage
                        </label>

                        <input
                            type="number"
                            id="progress_percentage"
                            name="progress_percentage"
                            class="form-control"
                            min="0"
                            max="100"
                            step="0.01"
                            placeholder="Example: 75"
                            value="<?php echo htmlspecialchars($_POST["progress_percentage"] ?? ""); ?>"
                            required
                        >

                    </div>


                    <!-- Information -->

                    <div class="alert">

                        <strong>Progress guide:</strong>

                        <br>

                        0% = Not started

                        <br>

                        25% = Beginning

                        <br>

                        50% = Halfway completed

                        <br>

                        75% = Almost completed

                        <br>

                        100% = Fully completed

                    </div>


                    <!-- Actions -->

                    <div class="form-actions">

                        <a
                            href="index.php"
                            class="btn btn-secondary"
                        >
                            Cancel
                        </a>

                        <button
                            type="submit"
                            class="btn btn-primary"
                        >
                            + Save Progress
                        </button>

                    </div>

                </form>

            </div>

        </main>

    </div>

</div>

</body>

</html>