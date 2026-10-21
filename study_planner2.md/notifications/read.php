<?php

session_start();

require_once __DIR__ . "/../config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION["user_id"];

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id) {
    header("Location: index.php");
    exit;
}

$stmt = $conn->prepare("
    UPDATE notifications
    SET is_read = TRUE
    WHERE id = ?
      AND user_id = ?
");

$stmt->execute([
    $id,
    $user_id
]);

header("Location: index.php");
exit;

?>