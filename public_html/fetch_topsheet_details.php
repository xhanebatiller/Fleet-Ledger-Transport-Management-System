<?php
session_start();
require_once 'connection.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("HTTP/1.1 401 Unauthorized");
    exit;
}

if (!isset($_GET['ts_id'])) {
    header("HTTP/1.1 400 Bad Request");
    exit;
}

$ts_id = $_GET['ts_id'];
$response = ['success' => false, 'message' => ''];

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get the first trip in this topsheet to get the TDH details
    $stmt = $conn->prepare("
        SELECT 
            t.truck_id, t.model as truck_details,
            d.driver_id as driver, d.fullname as driver_name,
            h1.helper1_id as helper1, h1.fullname as helper1_name,
            h2.helper2_id as helper2, h2.fullname as helper2_name
        FROM customerservice cs
        LEFT JOIN trucks t ON cs.truck_id = t.truck_id
        LEFT JOIN drivers d ON cs.driver = d.driver_id
        LEFT JOIN helper1 h1 ON cs.helper1 = h1.helper1_id
        LEFT JOIN helper2 h2 ON cs.helper2 = h2.helper2_id
        WHERE cs.ts_id = :ts_id
        LIMIT 1
    ");
    $stmt->bindParam(':ts_id', $ts_id);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        $response['success'] = true;
        $response['data'] = $data;
    } else {
        $response['message'] = 'No trips found in this topsheet';
    }
} catch(PDOException $e) {
    $response['message'] = "Database error: " . $e->getMessage();
}

header('Content-Type: application/json');
echo json_encode($response);
?>