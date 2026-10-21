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
| Get Study Session
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT *
    FROM study_sessions
    WHERE id = ? AND user_id = ?
");

$stmt->execute([$id, $user_id]);

$sessionData = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$sessionData) {
    header("Location: index.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| Get Subjects
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
| Update Study Session
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $subject_id = !empty($_POST["subject_id"])
        ? (int) $_POST["subject_id"]
        : null;

    $study_date = $_POST["study_date"] ?? "";

    $start_time = $_POST["start_time"] ?? "";

    $end_time = $_POST["end_time"] ?? "";

    $notes = trim($_POST["notes"] ?? "");

    $duration_minutes = 0;


    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    if ($study_date === "" || $start_time === "" || $end_time === "") {

        $error = "Study date, start time and end time are required.";

    } elseif ($end_time <= $start_time) {

        $error = "End time must be later than start time.";

    } else {

        /*
        |--------------------------------------------------------------------------
        | Calculate Duration
        |--------------------------------------------------------------------------
        */

        $start = new DateTime($start_time);
        $end = new DateTime($end_time);

        $duration_minutes =
            ($end->getTimestamp() - $start->getTimestamp()) / 60;


        /*
        |--------------------------------------------------------------------------
        | Update Study Session
        |--------------------------------------------------------------------------
        */

        $update = $conn->prepare("
            UPDATE study_sessions
            SET
                subject_id = ?,
                study_date = ?,
                start_time = ?,
                end_time = ?,
                duration_minutes = ?,
                notes = ?
            WHERE id = ? AND user_id = ?
        ");

        $update->execute([
            $subject_id,
            $study_date,
            $start_time,
            $end_time,
            $duration_minutes,
            $notes,
            $id,
            $user_id
        ]);

        header("Location: index.php");
        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | Keep entered values after validation error
    |--------------------------------------------------------------------------
    */

    $sessionData["subject_id"] = $subject_id;
    $sessionData["study_date"] = $study_date;
    $sessionData["start_time"] = $start_time;
    $sessionData["end_time"] = $end_time;
    $sessionData["notes"] = $notes;
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

    <title>Edit Study Session - Study Planner</title>

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

                    <h1>Edit Study Session</h1>

                    <p class="page-description">
                        Update your study session record.
                    </p>

                </div>

                <a
                    href="index.php"
                    class="btn btn-secondary"
                >
                    ← Back to Sessions
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

                        <h2>Study Session Details</h2>

                        <p>
                            Modify the date, subject, time or notes.
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
                        >

                            <option value="">
                                No Subject
                            </option>

                            <?php foreach ($subjects as $subject): ?>

                                <option
                                    value="<?php echo $subject["id"]; ?>"
                                    <?php echo ((int) $sessionData["subject_id"] === (int) $subject["id"]) ? "selected" : ""; ?>
                                >

                                    <?php echo htmlspecialchars($subject["subject_name"]); ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>


                    <!-- Study Date -->

                    <div class="form-group">

                        <label for="study_date">
                            Study Date
                        </label>

                        <input
                            type="date"
                            id="study_date"
                            name="study_date"
                            class="form-control"
                            value="<?php echo htmlspecialchars($sessionData["study_date"]); ?>"
                            required
                        >

                    </div>


                    <!-- Time -->

                    <div class="form-grid">


                        <div class="form-group">

                            <label for="start_time">
                                Start Time
                            </label>

                            <input
                                type="time"
                                id="start_time"
                                name="start_time"
                                class="form-control"
                                value="<?php echo htmlspecialchars(substr($sessionData["start_time"], 0, 5)); ?>"
                                required
                            >

                        </div>


                        <div class="form-group">

                            <label for="end_time">
                                End Time
                            </label>

                            <input
                                type="time"
                                id="end_time"
                                name="end_time"
                                class="form-control"
                                value="<?php echo htmlspecialchars(substr($sessionData["end_time"], 0, 5)); ?>"
                                required
                            >

                        </div>

                    </div>


                    <!-- Notes -->

                    <div class="form-group">

                        <label for="notes">
                            Notes
                        </label>

                        <textarea
                            id="notes"
                            name="notes"
                            class="form-control"
                            placeholder="What did you study during this session?"
                        ><?php echo htmlspecialchars($sessionData["notes"] ?? ""); ?></textarea>

                    </div>


                    <!-- Duration Information -->

                    <div class="alert">

                        <strong>Automatic duration:</strong>
                        The study duration will be recalculated from the
                        start and end times when you save the changes.

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
                            ✓ Update Study Session
                        </button>

                    </div>

                </form>

            </div>

        </main>

    </div>

</div>

</body>

</html>