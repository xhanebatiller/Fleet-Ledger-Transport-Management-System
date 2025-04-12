<?php
// add_waybill_to_topsheet.php
require_once 'connection.php';

ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', 'error.log');

$response = [
    'success' => false,
    'message' => '',
    'auto_assigned' => false,
    'assignments' => []
];

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Invalid request method");
    }

    $ts_id = filter_var($_POST['existing_topsheet'], FILTER_SANITIZE_STRING);
    if (empty($ts_id)) {
        throw new Exception("Topsheet number is required");
    }
    
    // Check if topsheet exists and get any existing assignments
    $checkSql = "SELECT 
                    COUNT(*) as count,
                    MAX(truck_id) as truck_id,
                    MAX(driver) as driver,
                    MAX(helper1) as helper1,
                    MAX(helper2) as helper2
                 FROM customerservice 
                 WHERE ts_id = ?";
    $checkStmt = $conn->prepare($checkSql);
    if (!$checkStmt) {
        throw new Exception("Database error: " . $conn->error);
    }
    
    $checkStmt->bind_param("s", $ts_id);
    if (!$checkStmt->execute()) {
        throw new Exception("Execution error: " . $checkStmt->error);
    }
    
    $result = $checkStmt->get_result();
    $checkData = $result->fetch_assoc();
    $checkStmt->close();
    
    if ($checkData['count'] == 0) {
        throw new Exception("Topsheet does not exist");
    }
    
    $requiredFields = [
        'waybill' => FILTER_VALIDATE_INT,
        'date' => FILTER_SANITIZE_STRING,
        'status' => FILTER_SANITIZE_STRING,
        'delivery_type' => FILTER_SANITIZE_STRING,
        'amount' => FILTER_SANITIZE_STRING,
        'source' => FILTER_SANITIZE_STRING,
        'pickup' => FILTER_SANITIZE_STRING,
        'dropoff' => FILTER_SANITIZE_STRING,
        'rate' => FILTER_SANITIZE_STRING,
        'call_time' => FILTER_SANITIZE_STRING
    ];
    
    $data = [];
    foreach ($requiredFields as $field => $filter) {
        if (empty($_POST[$field])) {
            throw new Exception("Field $field is required");
        }
        $data[$field] = filter_var($_POST[$field], $filter);
        if ($data[$field] === false) {
            throw new Exception("Invalid value for $field");
        }
    }

    $conn->begin_transaction();
    
    // First insert the basic waybill information
    $sql = "INSERT INTO customerservice 
            (ts_id, waybill, date, status, delivery_type, amount, 
             source, pickup, dropoff, rate, call_time, situation) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception("Database error: " . $conn->error);
    }
    
    $stmt->bind_param("sisssssssss", 
        $ts_id,
        $data['waybill'], 
        $data['date'], 
        $data['status'], 
        $data['delivery_type'],
        $data['amount'], 
        $data['source'], 
        $data['pickup'], 
        $data['dropoff'], 
        $data['rate'], 
        $data['call_time']
    );

    if (!$stmt->execute()) {
        throw new Exception("Execution error: " . $stmt->error);
    }
    
    $new_cs_id = $conn->insert_id;
    
    // Check if we should auto-assign from existing topsheet entries
    $hasAssignments = ($checkData['truck_id'] || $checkData['driver'] || $checkData['helper1'] || $checkData['helper2']);
    
    if ($hasAssignments) {
        $updateSql = "UPDATE customerservice SET 
                      truck_id = ?, 
                      driver = ?, 
                      helper1 = ?, 
                      helper2 = ?,
                      situation = 'Ready For Budgeting'
                      WHERE cs_id = ?";
        
        $updateStmt = $conn->prepare($updateSql);
        if (!$updateStmt) {
            throw new Exception("Database error: " . $conn->error);
        }
        
        $updateStmt->bind_param("iiiii",
            $checkData['truck_id'],
            $checkData['driver'],
            $checkData['helper1'],
            $checkData['helper2'],
            $new_cs_id
        );
        
        if (!$updateStmt->execute()) {
            throw new Exception("Execution error: " . $updateStmt->error);
        }
        
        $response['auto_assigned'] = true;
        $response['assignments'] = [
            'truck_id' => $checkData['truck_id'],
            'driver' => $checkData['driver'],
            'helper1' => $checkData['helper1'],
            'helper2' => $checkData['helper2']
        ];
    }
    
    $conn->commit();
    
    $response = [
        'success' => true,
        'message' => 'Waybill added successfully',
        'ts_id' => $ts_id,
        'auto_assigned' => $hasAssignments,
        'assignments' => $hasAssignments ? [
            'truck_id' => $checkData['truck_id'],
            'driver' => $checkData['driver'],
            'helper1' => $checkData['helper1'],
            'helper2' => $checkData['helper2']
        ] : []
    ];
    
} catch (Exception $e) {
    if ($conn && $conn->ping()) {
        $conn->rollback();
    }
    $response['message'] = $e->getMessage();
    error_log("Error in add_waybill_to_topsheet.php: " . $e->getMessage());
}

header('Content-Type: application/json');
echo json_encode($response);
exit;
?>