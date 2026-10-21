<?php

session_start();

require_once "../config/database.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION["user_id"];

$id = $_GET["id"] ?? null;

if (!$id) {
    header("Location: index.php");
    exit;
}

$stmt = $conn->prepare(
    "DELETE FROM study_plans
     WHERE id = ? AND user_id = ?"
);

$stmt->execute([
    $id,
    $user_id
]);

header("Location: index.php");
exit;

?>