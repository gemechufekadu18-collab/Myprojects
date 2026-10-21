<?php
session_start();

require_once __DIR__ . "/../config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION["user_id"];

/* Get notifications */
$stmt = $conn->prepare("
    SELECT *
    FROM notifications
    WHERE user_id = ?
    ORDER BY notification_date DESC, created_at DESC
");

$stmt->execute([$user_id]);

$notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);


/* Statistics */

$totalNotifications = count($notifications);

$unreadNotifications = 0;
$readNotifications = 0;

foreach ($notifications as $notification) {

    if ((int)$notification["is_read"] === 0) {
        $unreadNotifications++;
    } else {
        $readNotifications++;
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

    <title>Notifications | Study Planner</title>

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


            <!-- PAGE HEADER -->

            <div class="page-header">

                <div>

                    <h1>
                        Notifications
                    </h1>

                    <p class="page-description">
                        Stay updated with your study reminders and important information.
                    </p>

                </div>


                <div>

                    <a
                        href="add.php"
                        class="btn btn-primary"
                    >
                        + Add Notification
                    </a>

                </div>

            </div>



            <!-- STATISTICS -->

            <div class="stats-grid">


                <div class="stat-card">

                    <div class="stat-icon">
                        🔔
                    </div>

                    <div>

                        <div class="stat-label">
                            Total
                        </div>

                        <div class="stat-value">
                            <?php echo $totalNotifications; ?>
                        </div>

                    </div>

                </div>


                <div class="stat-card">

                    <div class="stat-icon">
                        📬
                    </div>

                    <div>

                        <div class="stat-label">
                            Unread
                        </div>

                        <div class="stat-value">
                            <?php echo $unreadNotifications; ?>
                        </div>

                    </div>

                </div>


                <div class="stat-card">

                    <div class="stat-icon">
                        ✓
                    </div>

                    <div>

                        <div class="stat-label">
                            Read
                        </div>

                        <div class="stat-value">
                            <?php echo $readNotifications; ?>
                        </div>

                    </div>

                </div>

            </div>



            <!-- NOTIFICATIONS -->

            <?php if ($totalNotifications > 0): ?>

                <div class="card">

                    <div class="card-header">

                        <div>

                            <h2>
                                Recent Notifications
                            </h2>

                            <p>
                                Your latest study reminders and updates.
                            </p>

                        </div>

                    </div>


                    <div class="notification-list">


                    <?php foreach ($notifications as $notification): ?>


                        <?php
                        $isUnread = ((int)$notification["is_read"] === 0);
                        ?>


                        <div
                            class="notification-item"
                            style="
                                display: flex;
                                justify-content: space-between;
                                gap: 20px;
                                padding: 20px;
                                margin-bottom: 12px;
                                border: 1px solid var(--border);
                                border-radius: 12px;
                                background:
                                <?php
                                echo $isUnread
                                    ? "#f8faff"
                                    : "#ffffff";
                                ?>;
                            "
                        >


                            <div style="display:flex; gap:15px;">


                                <div
                                    style="
                                        font-size: 28px;
                                        min-width: 40px;
                                    "
                                >
                                    <?php
                                    echo $isUnread
                                        ? "🔔"
                                        : "📖";
                                    ?>
                                </div>


                                <div>

                                    <div
                                        style="
                                            display:flex;
                                            align-items:center;
                                            gap:10px;
                                            margin-bottom:5px;
                                        "
                                    >

                                        <strong>

                                            <?php
                                            echo htmlspecialchars(
                                                $notification["title"]
                                            );
                                            ?>

                                        </strong>


                                        <?php if ($isUnread): ?>

                                            <span
                                                class="badge badge-primary"
                                            >
                                                New
                                            </span>

                                        <?php endif; ?>

                                    </div>


                                    <p
                                        style="
                                            margin:0 0 8px;
                                            color:var(--muted);
                                        "
                                    >

                                        <?php
                                        echo nl2br(
                                            htmlspecialchars(
                                                $notification["message"]
                                            )
                                        );
                                        ?>

                                    </p>


                                    <small
                                        style="
                                            color:var(--muted);
                                        "
                                    >

                                        <?php
                                        echo date(
                                            "M d, Y • g:i A",
                                            strtotime(
                                                $notification[
                                                    "notification_date"
                                                ]
                                            )
                                        );
                                        ?>

                                    </small>

                                </div>

                            </div>


                            <div
                                class="actions"
                                style="align-self:center;"
                            >

                                <?php if ($isUnread): ?>

                                    <a
                                        href="read.php?id=<?php echo $notification["id"]; ?>"
                                        class="btn btn-success btn-small"
                                    >
                                        Mark Read
                                    </a>

                                <?php endif; ?>


                                <a
                                    href="delete.php?id=<?php echo $notification["id"]; ?>"
                                    class="btn btn-danger btn-small"
                                    onclick="return confirm('Are you sure you want to delete this notification?');"
                                >
                                    Delete
                                </a>

                            </div>


                        </div>


                    <?php endforeach; ?>


                    </div>

                </div>


            <?php else: ?>


                <!-- EMPTY STATE -->

                <div class="card">

                    <div class="empty-state">

                        <div class="empty-icon">
                            🔔
                        </div>

                        <h2>
                            No Notifications
                        </h2>

                        <p>
                            You don't have any notifications yet.
                            Create reminders to stay organized.
                        </p>

                        <br>

                        <a
                            href="add.php"
                            class="btn btn-primary"
                        >
                            + Create Notification
                        </a>

                    </div>

                </div>

            <?php endif; ?>


        </div>

    </div>

</div>

</body>

</html>