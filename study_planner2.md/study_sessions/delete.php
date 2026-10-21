<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: ../auth/login.php");
    exit;
}

require_once "../config/database.php";

$user_id = $_SESSION["user_id"];


/*
|--------------------------------------------------------------------------
| Check session ID
|--------------------------------------------------------------------------
*/

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: index.php");
    exit;
}

$session_id = (int) $_GET["id"];


/*
|--------------------------------------------------------------------------
| Delete only user's own study session
|--------------------------------------------------------------------------
*/

$sql = "DELETE FROM study_sessions
        WHERE id = ? AND user_id = ?";

$stmt = $conn->prepare($sql);

$stmt->execute([
    $session_id,
    $user_id
]);


/*
|--------------------------------------------------------------------------
| Return to study sessions
|--------------------------------------------------------------------------
*/

header("Location: index.php");
exit;

?>