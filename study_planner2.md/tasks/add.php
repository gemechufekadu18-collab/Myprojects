<?php
session_start();

require_once __DIR__ . "/../config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION["user_id"];

$error = "";


/* Get subjects */
$subjectStmt = $conn->prepare("
    SELECT id, subject_name
    FROM subjects
    WHERE user_id = ?
    ORDER BY subject_name ASC
");

$subjectStmt->execute([$user_id]);

$subjects = $subjectStmt->fetchAll(PDO::FETCH_ASSOC);


/* Get study plans */
$planStmt = $conn->prepare("
    SELECT id, title
    FROM study_plans
    WHERE user_id = ?
    ORDER BY start_date ASC
");

$planStmt->execute([$user_id]);

$plans = $planStmt->fetchAll(PDO::FETCH_ASSOC);


/* Handle form */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $title = trim($_POST["title"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $subject_id = !empty($_POST["subject_id"])
        ? $_POST["subject_id"]
        : null;

    $study_plan_id = !empty($_POST["study_plan_id"])
        ? $_POST["study_plan_id"]
        : null;

    $due_date = $_POST["due_date"] ?? "";
    $priority = $_POST["priority"] ?? "medium";


    if ($title === "" || $due_date === "") {

        $error = "Task title and due date are required.";

    } else {

        $stmt = $conn->prepare("
            INSERT INTO tasks
            (
                user_id,
                subject_id,
                study_plan_id,
                title,
                description,
                due_date,
                priority
            )
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $user_id,
            $subject_id,
            $study_plan_id,
            $title,
            $description,
            $due_date,
            $priority
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

    <title>Add Task | Study Planner</title>

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

        <div class="content">


            <!-- HEADER -->

            <div class="page-header">

                <div>

                    <h1>
                        Create New Task
                    </h1>

                    <p class="page-description">
                        Add a task to your study schedule.
                    </p>

                </div>

                <div>

                    <a
                        href="index.php"
                        class="btn btn-secondary"
                    >
                        ← Back to Tasks
                    </a>

                </div>

            </div>



            <!-- FORM CARD -->

            <div class="card form-card">


                <?php if ($error !== ""): ?>

                    <div class="alert alert-danger">
                        <?php echo htmlspecialchars($error); ?>
                    </div>

                <?php endif; ?>


                <form method="POST">


                    <!-- TASK TITLE -->

                    <div class="form-group">

                        <label for="title">
                            Task Title
                        </label>

                        <input
                            type="text"
                            id="title"
                            name="title"
                            class="form-control"
                            placeholder="e.g. Complete database assignment"
                            value="<?php echo htmlspecialchars($_POST["title"] ?? ""); ?>"
                            required
                        >

                    </div>



                    <!-- SUBJECT + STUDY PLAN -->

                    <div class="form-grid">


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
                                    Select subject
                                </option>

                                <?php foreach ($subjects as $subject): ?>

                                    <option
                                        value="<?php echo $subject["id"]; ?>"
                                        <?php
                                        echo (
                                            ($_POST["subject_id"] ?? "")
                                            == $subject["id"]
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



                        <div class="form-group">

                            <label for="study_plan_id">
                                Study Plan
                            </label>

                            <select
                                id="study_plan_id"
                                name="study_plan_id"
                                class="form-control"
                            >

                                <option value="">
                                    Select study plan
                                </option>

                                <?php foreach ($plans as $plan): ?>

                                    <option
                                        value="<?php echo $plan["id"]; ?>"
                                        <?php
                                        echo (
                                            ($_POST["study_plan_id"] ?? "")
                                            == $plan["id"]
                                        )
                                            ? "selected"
                                            : "";
                                        ?>
                                    >

                                        <?php
                                        echo htmlspecialchars(
                                            $plan["title"]
                                        );
                                        ?>

                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>

                    </div>



                    <!-- DATE + PRIORITY -->

                    <div class="form-grid">


                        <div class="form-group">

                            <label for="due_date">
                                Due Date
                            </label>

                            <input
                                type="date"
                                id="due_date"
                                name="due_date"
                                class="form-control"
                                value="<?php echo htmlspecialchars($_POST["due_date"] ?? ""); ?>"
                                required
                            >

                        </div>



                        <div class="form-group">

                            <label for="priority">
                                Priority
                            </label>

                            <select
                                id="priority"
                                name="priority"
                                class="form-control"
                            >

                                <option value="low">
                                    Low
                                </option>

                                <option
                                    value="medium"
                                    <?php
                                    echo (
                                        ($_POST["priority"] ?? "medium")
                                        === "medium"
                                    )
                                        ? "selected"
                                        : "";
                                    ?>
                                >
                                    Medium
                                </option>

                                <option value="high">
                                    High
                                </option>

                            </select>

                        </div>

                    </div>



                    <!-- DESCRIPTION -->

                    <div class="form-group">

                        <label for="description">
                            Description
                        </label>

                        <textarea
                            id="description"
                            name="description"
                            class="form-control"
                            rows="5"
                            placeholder="Add details about this task..."
                        ><?php echo htmlspecialchars($_POST["description"] ?? ""); ?></textarea>

                    </div>



                    <!-- BUTTONS -->

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
                            Create Task
                        </button>

                    </div>


                </form>

            </div>

        </div>

    </div>

</div>

</body>

</html>