<?php
session_start();

require_once __DIR__ . "/../config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION["user_id"];

$error = "";

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

        $stmt = $conn->prepare("
            INSERT INTO study_plans
            (
                user_id,
                title,
                description,
                start_date,
                end_date,
                status
            )
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $user_id,
            $title,
            $description,
            $start_date,
            $end_date,
            $status
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

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Create Study Plan - Study Planner</title>

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

                    <h1>Create Study Plan</h1>

                    <p class="page-description">
                        Create a structured plan to organize your study goals.
                    </p>

                </div>

                <a href="index.php" class="btn btn-secondary">
                    ← Back to Plans
                </a>

            </div>


            <!-- Error Message -->
            <?php if ($error): ?>

                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($error); ?>
                </div>

            <?php endif; ?>


            <!-- Form -->
            <div class="card form-card">

                <div class="card-header">

                    <div>

                        <h2>Study Plan Details</h2>

                        <p>
                            Enter the information for your new study plan.
                        </p>

                    </div>

                </div>


                <form method="POST">

                    <div class="form-group">

                        <label for="title">
                            Plan Title
                        </label>

                        <input
                            type="text"
                            id="title"
                            name="title"
                            class="form-control"
                            placeholder="Example: Final Exam Preparation"
                            value="<?php echo htmlspecialchars($_POST["title"] ?? ""); ?>"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label for="description">
                            Description
                        </label>

                        <textarea
                            id="description"
                            name="description"
                            class="form-control"
                            placeholder="Describe what you want to achieve with this study plan..."
                        ><?php echo htmlspecialchars($_POST["description"] ?? ""); ?></textarea>

                    </div>


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
                                value="<?php echo htmlspecialchars($_POST["start_date"] ?? ""); ?>"
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
                                value="<?php echo htmlspecialchars($_POST["end_date"] ?? ""); ?>"
                                required
                            >

                        </div>

                    </div>


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
                                <?php echo (($_POST["status"] ?? "pending") === "pending") ? "selected" : ""; ?>
                            >
                                Pending
                            </option>

                            <option
                                value="active"
                                <?php echo (($_POST["status"] ?? "") === "active") ? "selected" : ""; ?>
                            >
                                Active
                            </option>

                            <option
                                value="completed"
                                <?php echo (($_POST["status"] ?? "") === "completed") ? "selected" : ""; ?>
                            >
                                Completed
                            </option>

                        </select>

                    </div>


                    <div class="form-actions">

                        <a href="index.php" class="btn btn-secondary">
                            Cancel
                        </a>

                        <button
                            type="submit"
                            class="btn btn-primary"
                        >
                            + Create Study Plan
                        </button>

                    </div>

                </form>

            </div>

        </main>

    </div>

</div>

</body>

</html>