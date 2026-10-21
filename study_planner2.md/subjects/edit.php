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
| Get Subject
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT *
    FROM subjects
    WHERE id = ? AND user_id = ?
");

$stmt->execute([$id, $user_id]);

$subject = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$subject) {
    header("Location: index.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| Update Subject
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $subject_name = trim($_POST["subject_name"] ?? "");
    $description = trim($_POST["description"] ?? "");

    if ($subject_name === "") {

        $error = "Subject name is required.";

    } else {

        $update = $conn->prepare("
            UPDATE subjects
            SET
                subject_name = ?,
                description = ?
            WHERE id = ? AND user_id = ?
        ");

        $update->execute([
            $subject_name,
            $description,
            $id,
            $user_id
        ]);

        header("Location: index.php");
        exit;
    }

    /*
    |--------------------------------------------------------------------------
    | Keep entered values if validation fails
    |--------------------------------------------------------------------------
    */

    $subject["subject_name"] = $subject_name;
    $subject["description"] = $description;
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

    <title>Edit Subject - Study Planner</title>

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

                    <h1>Edit Subject</h1>

                    <p class="page-description">
                        Update the information for this subject.
                    </p>

                </div>

                <a
                    href="index.php"
                    class="btn btn-secondary"
                >
                    ← Back to Subjects
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

                        <h2>Subject Details</h2>

                        <p>
                            Update the subject name and description.
                        </p>

                    </div>

                </div>


                <form method="POST">


                    <!-- Subject Name -->

                    <div class="form-group">

                        <label for="subject_name">
                            Subject Name
                        </label>

                        <input
                            type="text"
                            id="subject_name"
                            name="subject_name"
                            class="form-control"
                            placeholder="Example: Database Management"
                            value="<?php echo htmlspecialchars($subject["subject_name"]); ?>"
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
                            placeholder="Write a short description about this subject..."
                        ><?php echo htmlspecialchars($subject["description"] ?? ""); ?></textarea>

                    </div>


                    <!-- Form Actions -->

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
                            ✓ Update Subject
                        </button>

                    </div>

                </form>

            </div>

        </main>

    </div>

</div>

</body>

</html>