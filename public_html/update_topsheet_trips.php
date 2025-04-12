<?php 
// update_topsheet_trips.php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

header('Content-Type: application/json');

// Get the JSON data
$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid data received'
    ]);
    exit;
}

require_once 'connection.php';

$topsheet_no = $data['topsheet_no'];
$truck_id = $data['truck_id'];
$driver = $data['driver'];
$helper1 = $data['helper1'];
$helper2 = $data['helper2'];

// Start a transaction
$conn->begin_transaction();

try {
    // Build the SET part of the query based on which values are provided
    $setClauses = [];
    $params = [];
    $types = "";
    
    if (!empty($truck_id)) {
        $setClauses[] = "truck_id = ?";
        $params[] = $truck_id;
        $types .= "i"; // integer
    }
    
    if (!empty($driver)) {
        $setClauses[] = "driver = ?";
        $params[] = $driver;
        $types .= "i"; // integer
    }
    
    if (!empty($helper1)) {
        $setClauses[] = "helper1 = ?";
        $params[] = $helper1;
        $types .= "i"; // integer
    }
    
    if (!empty($helper2)) {
        $setClauses[] = "helper2 = ?";
        $params[] = $helper2;
        $types .= "i"; // integer
    }
    
    // If no valid values provided, exit
    if (empty($setClauses)) {
        echo json_encode([
            'success' => false,
            'message' => 'No valid fields to update'
        ]);
        exit;
    }
    
    // Also update the situation to Budgeted
    $setClauses[] = "situation = 'Ready For Budgeting'";
    
    $setClause = implode(", ", $setClauses);
    
    // Add the topsheet parameter
    $params[] = $topsheet_no;
    $types .= "s"; // string
    
    // Update all trips with the same topsheet number in the customerservice table
    $sql = "UPDATE customerservice SET $setClause WHERE ts_id = ? AND situation != 'Ready For Budgeting'";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    
    $affectedRows = $stmt->affected_rows;
    
    // Commit the transaction
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => "Updated $affectedRows waybill(s) in topsheet $topsheet_no"
    ]);
} catch (Exception $e) {
    // Rollback on error
    $conn->rollback();
    
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}

$conn->close();
?>