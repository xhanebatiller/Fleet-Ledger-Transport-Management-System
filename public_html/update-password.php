<?php
session_start();
require_once 'connection.php';

header('Content-Type: application/json');

try {
    if ($_SERVER["REQUEST_METHOD"] != "POST") {
        throw new Exception("Invalid request method");
    }

    $email = trim($_POST["email"] ?? '');
    $newPassword = trim($_POST["newPassword"] ?? '');
    $confirmPassword = trim($_POST["confirmPassword"] ?? '');

    if (empty($email) || empty($newPassword) || empty($confirmPassword)) {
        throw new Exception("All fields are required");
    }

    if ($newPassword !== $confirmPassword) {
        throw new Exception("Passwords do not match");
    }

    // Password strength validation
    if (strlen($newPassword) < 8) {
        throw new Exception("Password must be at least 8 characters long");
    }

    // Get current password from database
    $stmt = $conn->prepare("SELECT password FROM employee WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception("User not found");
    }

    $user = $result->fetch_assoc();
    $stmt->close();

    // Check if new password matches current password
    if (password_verify($newPassword, $user['password'])) {
        throw new Exception("New password cannot be the same as your current password");
    }

    // Hash the new password
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    $updateStmt = $conn->prepare("UPDATE employee SET 
        password = ?, 
        otp = NULL, 
        otp_expiry = NULL 
        WHERE email = ?");
    $updateStmt->bind_param("ss", $hashedPassword, $email);

    if (!$updateStmt->execute()) {
        throw new Exception("Error updating password: " . $updateStmt->error);
    }

    echo json_encode(["success" => true, "message" => "Password updated successfully"]);

} catch (Exception $e) {
    error_log("Password Update Error: " . $e->getMessage());
    
    echo json_encode([
        "success" => false, 
        "message" => $e->getMessage(),
        "error_details" => $e->getMessage()
    ]);
} finally {
    if (isset($stmt)) $stmt->close();
    if (isset($updateStmt)) $updateStmt->close();
    if (isset($conn)) $conn->close();
}
?>