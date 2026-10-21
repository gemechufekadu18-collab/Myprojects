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
| Add Study Session
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

        $duration_minutes = ($end->getTimestamp() - $start->getTimestamp()) / 60;

        /*
        |--------------------------------------------------------------------------
        | Insert Study Session
        |--------------------------------------------------------------------------
        */

        $stmt = $conn->prepare("
            INSERT INTO study_sessions
            (
                user_id,
                subject_id,
                study_date,
                start_time,
                end_time,
                duration_minutes,
                notes
            )
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $user_id,
            $subject_id,
            $study_date,
            $start_time,
            $end_time,
            $duration_minutes,
            $notes
        ]);

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

    <title>Add Study Session - Study Planner</title>

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

                    <h1>Add Study Session</h1>

                    <p class="page-description">
                        Record the time you spend studying.
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
                            Record your study date, subject and study duration.
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
                                    <?php echo (($_POST["subject_id"] ?? "") == $subject["id"]) ? "selected" : ""; ?>
                                >

                                    <?php echo htmlspecialchars($subject["subject_name"]); ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

                    </div>


                    <!-- Date -->

                    <div class="form-group">

                        <label for="study_date">
                            Study Date
                        </label>

                        <input
                            type="date"
                            id="study_date"
                            name="study_date"
                            class="form-control"
                            value="<?php echo htmlspecialchars($_POST["study_date"] ?? date("Y-m-d")); ?>"
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
                                value="<?php echo htmlspecialchars($_POST["start_time"] ?? ""); ?>"
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
                                value="<?php echo htmlspecialchars($_POST["end_time"] ?? ""); ?>"
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
                        ><?php echo htmlspecialchars($_POST["notes"] ?? ""); ?></textarea>

                    </div>


                    <!-- Information -->

                    <div class="alert">

                        <strong>Tip:</strong>
                        The session duration will be calculated automatically
                        from your start and end times.

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
                            + Save Study Session
                        </button>

                    </div>

                </form>

            </div>

        </main>

    </div>

</div>

</body>

</html>