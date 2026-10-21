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

    $subject_name = trim($_POST["subject_name"] ?? "");
    $description = trim($_POST["description"] ?? "");

    if ($subject_name === "") {

        $error = "Subject name is required.";

    } else {

        $stmt = $conn->prepare("
            INSERT INTO subjects
            (user_id, subject_name, description)
            VALUES (?, ?, ?)
        ");

        $stmt->execute([
            $user_id,
            $subject_name,
            $description
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

    <title>Add Subject | Study Planner</title>

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

</head>

<body>

<div class="app">

    <!-- SIDEBAR -->

    <?php require_once __DIR__ . "/../includes/sidebar.php"; ?>


    <div class="main">

        <!-- TOPBAR -->

        <?php require_once __DIR__ . "/../includes/topbar.php"; ?>


        <div class="content">

            <!-- HEADER -->

            <div class="page-header">

                <div>

                    <h1>
                        Add New Subject
                    </h1>

                    <p class="page-description">
                        Add a subject to your study planner.
                    </p>

                </div>


                <div>

                    <a
                        href="index.php"
                        class="btn btn-secondary"
                    >
                        ← Back to Subjects
                    </a>

                </div>

            </div>


            <!-- FORM -->

            <div class="card form-card">

                <?php if ($error !== ""): ?>

                    <div class="alert alert-danger">

                        <?php
                        echo htmlspecialchars($error);
                        ?>

                    </div>

                <?php endif; ?>


                <form method="POST">


                    <!-- SUBJECT NAME -->

                    <div class="form-group">

                        <label for="subject_name">
                            Subject Name
                        </label>

                        <input
                            type="text"
                            id="subject_name"
                            name="subject_name"
                            class="form-control"
                            placeholder="e.g. Database Management System"
                            value="<?php
                            echo htmlspecialchars(
                                $_POST["subject_name"] ?? ""
                            );
                            ?>"
                            required
                        >

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
                            placeholder="Write a short description about this subject..."
                        ><?php
                        echo htmlspecialchars(
                            $_POST["description"] ?? ""
                        );
                        ?></textarea>

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
                            Create Subject
                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

</div>

</body>

</html>