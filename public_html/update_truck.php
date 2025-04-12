<?php
// update_truck.php
session_start();

require_once 'connection.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['updatedata'])) {
    $truck_id = $_POST['truck_id'];
    $model = trim($_POST['model']);
    $truck_plate = trim($_POST['truck_plate']);
    $status = $_POST['status'];
    $truck_type = trim($_POST['truck_type']);

    // Validate inputs
    $errors = [];
    
    if (empty($model)) {
        $errors[] = "Model is required";
    }
    
    if (empty($truck_plate)) {
        $errors[] = "Truck plate is required";
    }
    
    if (empty($status) || !in_array($status, ['Active', 'Inactive'])) {
        $errors[] = "Status must be either Active or Inactive";
    }
    
    if (empty($truck_type)) {
        $errors[] = "Truck type is required";
    }

    if (count($errors) === 0) {
        $query = "UPDATE truck SET model=?, truck_plate=?, status=?, truck_type=? WHERE truck_id=?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssssi", $model, $truck_plate, $status, $truck_type, $truck_id);
        
        if ($stmt->execute()) {
            // Redirect with success message
            header("location: trucks.php?success=Truck%20updated%20successfully");
            exit;
        } else {
            $errors[] = "Error updating record: " . $conn->error;
        }
        $stmt->close();
    }
    
    // If there are errors, redirect back with error messages
    if (count($errors) > 0) {
        $error_string = implode("|", $errors);
        header("location: trucks.php?error=" . urlencode($error_string) . "&truck_id=" . $truck_id);
        exit;
    }
} else {
    header("location: trucks.php");
    exit;
}

$conn->close();
?>