<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

require_once "../config/database.php";

$user_id = $_SESSION["user_id"];


if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: index.php");
    exit;
}

$progress_id = (int) $_GET["id"];


$sql = "DELETE FROM progress
        WHERE id = ? AND user_id = ?";

$stmt = $conn->prepare($sql);

$stmt->execute([
    $progress_id,
    $user_id
]);


header("Location: index.php");
exit;

?>