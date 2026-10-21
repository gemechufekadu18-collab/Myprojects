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
| Get Task
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT *
    FROM tasks
    WHERE id = ? AND user_id = ?
");

$stmt->execute([$id, $user_id]);

$task = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$task) {
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
| Get Study Plans
|--------------------------------------------------------------------------
*/

$planStmt = $conn->prepare("
    SELECT id, title
    FROM study_plans
    WHERE user_id = ?
    ORDER BY start_date ASC
");

$planStmt->execute([$user_id]);

$plans = $planStmt->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| Update Task
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $title = trim($_POST["title"] ?? "");
    $description = trim($_POST["description"] ?? "");

    $subject_id = !empty($_POST["subject_id"])
        ? (int) $_POST["subject_id"]
        : null;

    $study_plan_id = !empty($_POST["study_plan_id"])
        ? (int) $_POST["study_plan_id"]
        : null;

    $due_date = $_POST["due_date"] ?? "";

    $priority = $_POST["priority"] ?? "medium";

    $status = $_POST["status"] ?? "pending";


    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    if ($title === "" || $due_date === "") {

        $error = "Task title and due date are required.";

    } elseif (!in_array($priority, ["low", "medium", "high"], true)) {

        $error = "Invalid priority selected.";

    } elseif (!in_array($status, ["pending", "completed"], true)) {

        $error = "Invalid task status selected.";

    } else {

        /*
        |--------------------------------------------------------------------------
        | Update
        |--------------------------------------------------------------------------
        */

        $update = $conn->prepare("
            UPDATE tasks
            SET
                subject_id = ?,
                study_plan_id = ?,
                title = ?,
                description = ?,
                due_date = ?,
                priority = ?,
                status = ?
            WHERE id = ? AND user_id = ?
        ");

        $update->execute([
            $subject_id,
            $study_plan_id,
            $title,
            $description,
            $due_date,
            $priority,
            $status,
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

    $task["title"] = $title;
    $task["description"] = $description;
    $task["subject_id"] = $subject_id;
    $task["study_plan_id"] = $study_plan_id;
    $task["due_date"] = $due_date;
    $task["priority"] = $priority;
    $task["status"] = $status;
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

    <title>Edit Task - Study Planner</title>

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

                    <h1>Edit Task</h1>

                    <p class="page-description">
                        Update your task details and track its status.
                    </p>

                </div>

                <a
                    href="index.php"
                    class="btn btn-secondary"
                >
                    ← Back to Tasks
                </a>

            </div>


            <!-- Error -->

            <?php if ($error): ?>

                <div class="alert alert-danger">

                    <?php echo htmlspecialchars($error); ?>

                </div>

            <?php endif; ?>


            <!-- Form -->

            <div class="card form-card">

                <div class="card-header">

                    <div>

                        <h2>Task Details</h2>

                        <p>
                            Modify the information for this task.
                        </p>

                    </div>

                </div>


                <form method="POST">


                    <!-- Task Title -->

                    <div class="form-group">

                        <label for="title">
                            Task Title
                        </label>

                        <input
                            type="text"
                            id="title"
                            name="title"
                            class="form-control"
                            placeholder="Example: Complete database assignment"
                            value="<?php echo htmlspecialchars($task["title"]); ?>"
                            required
                        >

                    </div>


                    <!-- Subject + Study Plan -->

                    <div class="form-grid">


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
                                        <?php echo ((int) $task["subject_id"] === (int) $subject["id"]) ? "selected" : ""; ?>
                                    >

                                        <?php echo htmlspecialchars($subject["subject_name"]); ?>

                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>


                        <!-- Study Plan -->

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
                                    No Study Plan
                                </option>

                                <?php foreach ($plans as $plan): ?>

                                    <option
                                        value="<?php echo $plan["id"]; ?>"
                                        <?php echo ((int) $task["study_plan_id"] === (int) $plan["id"]) ? "selected" : ""; ?>
                                    >

                                        <?php echo htmlspecialchars($plan["title"]); ?>

                                    </option>

                                <?php endforeach; ?>

                            </select>

                        </div>

                    </div>


                    <!-- Due Date + Priority -->

                    <div class="form-grid">


                        <!-- Due Date -->

                        <div class="form-group">

                            <label for="due_date">
                                Due Date
                            </label>

                            <input
                                type="date"
                                id="due_date"
                                name="due_date"
                                class="form-control"
                                value="<?php echo htmlspecialchars($task["due_date"]); ?>"
                                required
                            >

                        </div>


                        <!-- Priority -->

                        <div class="form-group">

                            <label for="priority">
                                Priority
                            </label>

                            <select
                                id="priority"
                                name="priority"
                                class="form-control"
                            >

                                <option
                                    value="low"
                                    <?php echo $task["priority"] === "low" ? "selected" : ""; ?>
                                >
                                    Low
                                </option>

                                <option
                                    value="medium"
                                    <?php echo $task["priority"] === "medium" ? "selected" : ""; ?>
                                >
                                    Medium
                                </option>

                                <option
                                    value="high"
                                    <?php echo $task["priority"] === "high" ? "selected" : ""; ?>
                                >
                                    High
                                </option>

                            </select>

                        </div>

                    </div>


                    <!-- Status -->

                    <div class="form-group">

                        <label for="status">
                            Task Status
                        </label>

                        <select
                            id="status"
                            name="status"
                            class="form-control"
                        >

                            <option
                                value="pending"
                                <?php echo $task["status"] === "pending" ? "selected" : ""; ?>
                            >
                                Pending
                            </option>

                            <option
                                value="completed"
                                <?php echo $task["status"] === "completed" ? "selected" : ""; ?>
                            >
                                Completed
                            </option>

                        </select>

                    </div>


                    <!-- Description -->

                    <div class="form-group">

                        <label for="description">
                            Description
                        </label>

                        <textarea
                            id="description"
                            name="description"
                            class="form-control"
                            placeholder="Add additional details about this task..."
                        ><?php echo htmlspecialchars($task["description"] ?? ""); ?></textarea>

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
                            ✓ Update Task
                        </button>

                    </div>

                </form>

            </div>

        </main>

    </div>

</div>

</body>

</html>