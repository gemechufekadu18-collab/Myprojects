<?php
session_start();

require_once __DIR__ . "/../config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION["user_id"];

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: index.php");
    exit;
}

$id = (int) $_GET["id"];

$error = "";


/*
|--------------------------------------------------------------------------
| Get Progress Record
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT
        progress.*,
        subjects.subject_name
    FROM progress
    LEFT JOIN subjects
        ON progress.subject_id = subjects.id
    WHERE progress.id = ?
      AND progress.user_id = ?
");

$stmt->execute([
    $id,
    $user_id
]);

$progress = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$progress) {
    header("Location: index.php");
    exit;
}


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
| Update Progress
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
        | Check For Another Progress Record
        |--------------------------------------------------------------------------
        */

        $check = $conn->prepare("
            SELECT id
            FROM progress
            WHERE user_id = ?
              AND subject_id = ?
              AND id != ?
        ");

        $check->execute([
            $user_id,
            $subject_id,
            $id
        ]);

        $existing = $check->fetch(PDO::FETCH_ASSOC);


        if ($existing) {

            /*
            |--------------------------------------------------------------------------
            | Update Existing Record
            |--------------------------------------------------------------------------
            */

            $update = $conn->prepare("
                UPDATE progress
                SET progress_percentage = ?
                WHERE id = ?
                  AND user_id = ?
            ");

            $update->execute([
                $progress_percentage,
                $existing["id"],
                $user_id
            ]);


            /*
            |--------------------------------------------------------------------------
            | Delete Current Duplicate Record
            |--------------------------------------------------------------------------
            */

            $delete = $conn->prepare("
                DELETE FROM progress
                WHERE id = ?
                  AND user_id = ?
            ");

            $delete->execute([
                $id,
                $user_id
            ]);

        } else {

            /*
            |--------------------------------------------------------------------------
            | Update Current Record
            |--------------------------------------------------------------------------
            */

            $update = $conn->prepare("
                UPDATE progress
                SET
                    subject_id = ?,
                    progress_percentage = ?
                WHERE id = ?
                  AND user_id = ?
            ");

            $update->execute([
                $subject_id,
                $progress_percentage,
                $id,
                $user_id
            ]);
        }


        header("Location: index.php");
        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | Keep Entered Values After Error
    |--------------------------------------------------------------------------
    */

    $progress["subject_id"] = $subject_id;
    $progress["progress_percentage"] = $progress_percentage;
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

    <title>Edit Progress - Study Planner</title>

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

                    <h1>Edit Progress</h1>

                    <p class="page-description">
                        Update your learning progress for this subject.
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
                            Update the subject and completion percentage.
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
                                    <?php
                                    echo (
                                        (int) $progress["subject_id"]
                                        ===
                                        (int) $subject["id"]
                                    )
                                    ? "selected"
                                    : "";
                                    ?>
                                >

                                    <?php
                                    echo htmlspecialchars(
                                        $subject["subject_name"]
                                    );
                                    ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>


                    <!-- Progress Percentage -->

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
                            value="<?php echo htmlspecialchars($progress["progress_percentage"]); ?>"
                            required
                        >

                    </div>


                    <!-- Progress Preview -->

                    <div class="card">

                        <div class="card-header">

                            <div>

                                <h2>Progress Preview</h2>

                                <p>
                                    Your current completion level.
                                </p>

                            </div>

                        </div>

                        <div class="progress-container">

                            <div
                                class="progress-bar"
                                id="progressPreview"
                                style="width: <?php echo min(100, max(0, (float) $progress["progress_percentage"])); ?>%;"
                            ></div>

                        </div>

                        <p
                            id="progressText"
                            style="margin-top: 10px; font-weight: 600;"
                        >
                            <?php echo htmlspecialchars($progress["progress_percentage"]); ?>%
                            completed
                        </p>

                    </div>


                    <!-- Information -->

                    <div class="alert">

                        <strong>Remember:</strong>

                        Progress should be between
                        <strong>0%</strong> and
                        <strong>100%</strong>.

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
                            ✓ Update Progress
                        </button>

                    </div>

                </form>

            </div>

        </main>

    </div>

</div>


<script>

const progressInput =
    document.getElementById("progress_percentage");

const progressPreview =
    document.getElementById("progressPreview");

const progressText =
    document.getElementById("progressText");


progressInput.addEventListener("input", function () {

    let value = parseFloat(this.value);

    if (isNaN(value)) {
        value = 0;
    }

    value = Math.max(0, Math.min(100, value));

    progressPreview.style.width = value + "%";

    progressText.textContent =
        value + "% completed";

});

</script>

</body>

</html>