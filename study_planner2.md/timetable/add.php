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
| Add Timetable Entry
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $subject_id = !empty($_POST["subject_id"])
        ? (int) $_POST["subject_id"]
        : null;

    $day_of_week = $_POST["day_of_week"] ?? "";

    $start_time = $_POST["start_time"] ?? "";

    $end_time = $_POST["end_time"] ?? "";

    $room = trim($_POST["room"] ?? "");

    $notes = trim($_POST["notes"] ?? "");


    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    if ($day_of_week === "" || $start_time === "" || $end_time === "") {

        $error = "Day, start time and end time are required.";

    } elseif ($end_time <= $start_time) {

        $error = "End time must be later than start time.";

    } else {

        $stmt = $conn->prepare("
            INSERT INTO timetable
            (
                user_id,
                subject_id,
                day_of_week,
                start_time,
                end_time,
                room,
                notes
            )
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $user_id,
            $subject_id,
            $day_of_week,
            $start_time,
            $end_time,
            $room,
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

    <title>Add Class - Study Planner</title>

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

                    <h1>Add Class</h1>

                    <p class="page-description">
                        Add a class to your weekly timetable.
                    </p>

                </div>

                <a
                    href="index.php"
                    class="btn btn-secondary"
                >
                    ← Back to Timetable
                </a>

            </div>


            <!-- Error Message -->

            <?php if ($error): ?>

                <div class="alert alert-danger">

                    <?php echo htmlspecialchars($error); ?>

                </div>

            <?php endif; ?>


            <!-- Form Card -->

            <div class="card form-card">

                <div class="card-header">

                    <div>

                        <h2>Class Details</h2>

                        <p>
                            Enter the schedule information for your class.
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


                    <!-- Day -->

                    <div class="form-group">

                        <label for="day_of_week">
                            Day of Week
                        </label>

                        <select
                            id="day_of_week"
                            name="day_of_week"
                            class="form-control"
                            required
                        >

                            <option value="">
                                Select Day
                            </option>

                            <?php
                            $days = [
                                "Monday",
                                "Tuesday",
                                "Wednesday",
                                "Thursday",
                                "Friday",
                                "Saturday",
                                "Sunday"
                            ];
                            ?>

                            <?php foreach ($days as $day): ?>

                                <option
                                    value="<?php echo $day; ?>"
                                    <?php echo (($_POST["day_of_week"] ?? "") === $day) ? "selected" : ""; ?>
                                >

                                    <?php echo $day; ?>

                                </option>

                            <?php endforeach; ?>

                        </select>

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


                    <!-- Room -->

                    <div class="form-group">

                        <label for="room">
                            Room / Location
                        </label>

                        <input
                            type="text"
                            id="room"
                            name="room"
                            class="form-control"
                            placeholder="Example: Room 204"
                            value="<?php echo htmlspecialchars($_POST["room"] ?? ""); ?>"
                        >

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
                            placeholder="Add any additional information..."
                        ><?php echo htmlspecialchars($_POST["notes"] ?? ""); ?></textarea>

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
                            + Add Class
                        </button>

                    </div>

                </form>

            </div>

        </main>

    </div>

</div>

</body>

</html>