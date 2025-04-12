<?php
// update_helpers.php
session_start();

require_once 'connection.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['updatedata'])) {
    $helper_id = $_POST['helper_id'];
    $source_table = $_POST['source_table'];
    $fullname = trim($_POST['fullname']);
    $contact = trim($_POST['contact']);
    $status = $_POST['status'];

    // Validate inputs
    $errors = [];
    
    if (empty($fullname)) {
        $errors[] = "Full name is required";
    }
    
    // Sanitize contact by removing non-digits first
    $contact = preg_replace('/\D/', '', $contact);
    
    // Validate contact number
    if (empty($contact)) {
        $errors[] = "Contact number is required";
    } elseif (!preg_match('/^09\d{9}$/', $contact)) {
        $errors[] = "Invalid contact number format. Must start with 09 and have 11 digits";
    }
    
    if (empty($status) || !in_array($status, ['Active', 'Inactive'])) {
        $errors[] = "Status must be either Active or Inactive";
    }

    if (count($errors) === 0) {
        // Determine the ID column name based on the source table
        $id_column = $source_table . "_id";

        $query = "UPDATE $source_table SET fullname=?, contact=?, status=? WHERE $id_column=?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssi", $fullname, $contact, $status, $helper_id);
        
        if ($stmt->execute()) {
            // Redirect with success message
            header("location: helpers.php?success=Helper%20updated%20successfully");
            exit;
        } else {
            $errors[] = "Error updating record: " . $conn->error;
        }
        $stmt->close();
    }
    
    // If there are errors, redirect back with error messages
    if (count($errors) > 0) {
        $error_string = implode("|", $errors);
        header("location: helpers.php?error=" . urlencode($error_string) . "&helper_id=" . $helper_id);
        exit;
    }
} else {
    header("location: helpers.php");
    exit;
}

$conn->close();
?>