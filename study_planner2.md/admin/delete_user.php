<?php

session_start();

require_once __DIR__ . "/../config/database.php";


/*
|--------------------------------------------------------------------------
| ADMIN AUTHENTICATION
|--------------------------------------------------------------------------
*/

if (
    !isset($_SESSION["admin_id"]) ||
    $_SESSION["admin_role"] !== "admin"
) {
    header("Location: login.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| GET USER ID
|--------------------------------------------------------------------------
*/

$id = filter_input(
    INPUT_GET,
    "id",
    FILTER_VALIDATE_INT
);

if (!$id) {

    header("Location: users.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| PREVENT ADMIN FROM DELETING THEMSELVES
|--------------------------------------------------------------------------
*/

if ($id == $_SESSION["admin_id"]) {

    header("Location: users.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| CHECK USER EXISTS
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT id, role
    FROM users
    WHERE id = ?
    LIMIT 1
");

$stmt->execute([$id]);

$user = $stmt->fetch(PDO::FETCH_ASSOC);


if (!$user) {

    header("Location: users.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| PREVENT DELETING ANOTHER ADMIN
|--------------------------------------------------------------------------
|
| Only student accounts can be deleted from this page.
|
*/

if ($user["role"] === "admin") {

    header("Location: users.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| DELETE USER
|--------------------------------------------------------------------------
|
| Related records such as subjects, tasks, study plans,
| sessions, progress, notifications and timetable entries
| are automatically deleted because the database uses
| ON DELETE CASCADE.
|
*/

$deleteStmt = $conn->prepare("
    DELETE FROM users
    WHERE id = ?
      AND role = 'student'
");

$deleteStmt->execute([$id]);


/*
|--------------------------------------------------------------------------
| RETURN TO USER MANAGEMENT
|--------------------------------------------------------------------------
*/

header("Location: users.php");
exit;

?>