<?php
session_start();
require_once 'connection.php';

if (isset($_SESSION["emp_id"])) {
    // Clear the session from database
    $stmt = $conn->prepare("UPDATE employee SET current_session_id = NULL WHERE emp_id = ?");
    $stmt->bind_param("i", $_SESSION["emp_id"]);
    $stmt->execute();
    $stmt->close();
}

session_destroy();
header("Location: index.php");
exit();
?>