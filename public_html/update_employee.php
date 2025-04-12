<?php
// update_employee.php
session_start();

require_once 'connection.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Check permissions - only admin (role 1) can edit employees
if ($_SESSION["u_id"] != 1) {
    header("location: employees.php?error=Unauthorized%20access");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['updatedata'])) {
    $emp_id = $_POST['emp_id'];
    $emp_num = trim($_POST['emp_num']);
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $u_id = $_POST['position'];

    // Validate inputs
    $errors = [];
    
    if (empty($emp_num)) {
        $errors[] = "Employee number is required";
    }
    
    if (empty($fullname)) {
        $errors[] = "Full name is required";
    }
    
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    if (empty($u_id)) {
        $errors[] = "Position is required";
    }

    if (count($errors) === 0) {
        // Check if employee number already exists (excluding current record)
        $check_query = "SELECT emp_id FROM employee WHERE emp_num = ? AND emp_id != ?";
        $stmt = $conn->prepare($check_query);
        $stmt->bind_param("si", $emp_num, $emp_id);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $errors[] = "Employee number already exists";
        } else {
            // Update the employee record
            $update_query = "UPDATE employee SET 
                            emp_num = ?, 
                            fullname = ?, 
                            email = ?, 
                            u_id = ? 
                            WHERE emp_id = ?";
            
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("sssii", $emp_num, $fullname, $email, $u_id, $emp_id);
            
            if ($stmt->execute()) {
                // Redirect with success message
                header("location: employee.php?success=Employee%20updated%20successfully");
                exit;
            } else {
                $errors[] = "Error updating record: " . $conn->error;
            }
        }
        $stmt->close();
    }
    
    // If there are errors, redirect back with error messages
    if (count($errors) > 0) {
        $error_string = implode("|", $errors);
        header("location: employees.php?error=" . urlencode($error_string) . "&emp_id=" . $emp_id);
        exit;
    }
} else {
    header("location: employees.php");
    exit;
}

$conn->close();
?>