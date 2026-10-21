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
| Get Study Plan
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT *
    FROM study_plans
    WHERE id = ? AND user_id = ?
");

$stmt->execute([$id, $user_id]);

$plan = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$plan) {
    header("Location: index.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| Update Study Plan
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $title = trim($_POST["title"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $start_date = $_POST["start_date"] ?? "";
    $end_date = $_POST["end_date"] ?? "";
    $status = $_POST["status"] ?? "pending";

    if ($title === "" || $start_date === "" || $end_date === "") {

        $error = "Title, start date and end date are required.";

    } elseif ($end_date < $start_date) {

        $error = "End date cannot be earlier than the start date.";

    } else {

        $update = $conn->prepare("
            UPDATE study_plans
            SET
                title = ?,
                description = ?,
                start_date = ?,
                end_date = ?,
                status = ?
            WHERE id = ? AND user_id = ?
        ");

        $update->execute([
            $title,
            $description,
            $start_date,
            $end_date,
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

    $plan["title"] = $title;
    $plan["description"] = $description;
    $plan["start_date"] = $start_date;
    $plan["end_date"] = $end_date;
    $plan["status"] = $status;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Study Plan - Study Planner</title>

    <link rel="stylesheet" href="../assets/css/style.css">

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

                    <h1>Edit Study Plan</h1>

                    <p class="page-description">
                        Update the details of your study plan.
                    </p>

                </div>

                <a href="index.php" class="btn btn-secondary">
                    ← Back to Plans
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

                        <h2>Study Plan Details</h2>

                        <p>
                            Make changes to your study plan below.
                        </p>

                    </div>

                </div>


                <form method="POST">


                    <!-- Title -->

                    <div class="form-group">

                        <label for="title">
                            Plan Title
                        </label>

                        <input
                            type="text"
                            id="title"
                            name="title"
                            class="form-control"
                            value="<?php echo htmlspecialchars($plan["title"]); ?>"
                            placeholder="Example: Final Exam Preparation"
                            required
                        >

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
                            placeholder="Describe your study plan..."
                        ><?php echo htmlspecialchars($plan["description"] ?? ""); ?></textarea>

                    </div>


                    <!-- Dates -->

                    <div class="form-grid">

                        <div class="form-group">

                            <label for="start_date">
                                Start Date
                            </label>

                            <input
                                type="date"
                                id="start_date"
                                name="start_date"
                                class="form-control"
                                value="<?php echo htmlspecialchars($plan["start_date"]); ?>"
                                required
                            >

                        </div>


                        <div class="form-group">

                            <label for="end_date">
                                End Date
                            </label>

                            <input
                                type="date"
                                id="end_date"
                                name="end_date"
                                class="form-control"
                                value="<?php echo htmlspecialchars($plan["end_date"]); ?>"
                                required
                            >

                        </div>

                    </div>


                    <!-- Status -->

                    <div class="form-group">

                        <label for="status">
                            Plan Status
                        </label>

                        <select
                            id="status"
                            name="status"
                            class="form-control"
                        >

                            <option
                                value="pending"
                                <?php echo $plan["status"] === "pending" ? "selected" : ""; ?>
                            >
                                Pending
                            </option>

                            <option
                                value="active"
                                <?php echo $plan["status"] === "active" ? "selected" : ""; ?>
                            >
                                Active
                            </option>

                            <option
                                value="completed"
                                <?php echo $plan["status"] === "completed" ? "selected" : ""; ?>
                            >
                                Completed
                            </option>

                        </select>

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
                            ✓ Update Study Plan
                        </button>

                    </div>

                </form>

            </div>

        </main>

    </div>

</div>

</body>

</html>