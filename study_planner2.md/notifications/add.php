<?php

session_start();

require_once __DIR__ . "/../config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION["user_id"];

$error = "";

$title = "";
$message = "";
$notification_date = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $title = trim($_POST["title"] ?? "");
    $message = trim($_POST["message"] ?? "");
    $notification_date = $_POST["notification_date"] ?? "";

    if ($title === "" || $message === "" || $notification_date === "") {

        $error = "Title, message, and notification date are required.";

    } else {

        $stmt = $conn->prepare("
            INSERT INTO notifications
            (
                user_id,
                title,
                message,
                notification_date
            )
            VALUES (?, ?, ?, ?)
        ");

        $stmt->execute([
            $user_id,
            $title,
            $message,
            $notification_date
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

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Add Notification - Study Planner</title>

    <link rel="stylesheet"
          href="../assets/css/style.css">

</head>

<body>

<div class="app">

    <?php require_once __DIR__ . "/../includes/sidebar.php"; ?>

    <div class="main">

        <?php require_once __DIR__ . "/../includes/topbar.php"; ?>

        <main class="content">

            <div class="page-header">

                <div>

                    <h1>Add Notification</h1>

                    <p class="page-description">
                        Create a reminder or notification for your study activities.
                    </p>

                </div>

                <a href="index.php"
                   class="btn btn-secondary">
                    ← Back to Notifications
                </a>

            </div>


            <?php if ($error !== ""): ?>

                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($error); ?>
                </div>

            <?php endif; ?>


            <div class="card form-card">

                <div class="card-header">

                    <div>

                        <h2>Notification Details</h2>

                        <p>
                            Enter the information for your new notification.
                        </p>

                    </div>

                </div>


                <form method="POST">


                    <div class="form-group">

                        <label for="title">
                            Notification Title
                        </label>

                        <input
                            type="text"
                            id="title"
                            name="title"
                            class="form-control"
                            placeholder="Example: Mathematics assignment reminder"
                            value="<?php echo htmlspecialchars($title); ?>"
                            required
                        >

                    </div>


                    <div class="form-group">

                        <label for="message">
                            Message
                        </label>

                        <textarea
                            id="message"
                            name="message"
                            class="form-control"
                            placeholder="Write your notification message here..."
                            required
                        ><?php echo htmlspecialchars($message); ?></textarea>

                    </div>


                    <div class="form-grid">

                        <div class="form-group">

                            <label for="notification_date">
                                Notification Date & Time
                            </label>

                            <input
                                type="datetime-local"
                                id="notification_date"
                                name="notification_date"
                                class="form-control"
                                value="<?php echo htmlspecialchars($notification_date); ?>"
                                required
                            >

                        </div>


                        <div class="form-group">

                            <label>
                                Notification Status
                            </label>

                            <div
                                class="form-control"
                                style="
                                    background:#f8fafc;
                                    display:flex;
                                    align-items:center;
                                    color:#64748b;
                                "
                            >
                                🔔 New notification
                            </div>

                        </div>

                    </div>


                    <div class="form-actions">

                        <a href="index.php"
                           class="btn btn-secondary">
                            Cancel
                        </a>

                        <button
                            type="submit"
                            class="btn btn-primary"
                        >
                            🔔 Create Notification
                        </button>

                    </div>


                </form>

            </div>

        </main>

    </div>

</div>

</body>

</html>