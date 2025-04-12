<?php
session_start();
require_once 'connection.php';

$response = ['success' => false];

try {
    $ts_id = isset($_GET['ts_id']) ? $_GET['ts_id'] : null;
    
    // Check session first
    if (isset($_SESSION['last_assigned_truck'])) {
        $response = [
            'success' => true,
            'truck_id' => $_SESSION['last_assigned_truck'],
            'driver' => $_SESSION['last_assigned_driver'],
            'helper1' => $_SESSION['last_assigned_helper1'],
            'helper2' => $_SESSION['last_assigned_helper2']
        ];
        
        // Fetch details for display
        $conn = new mysqli($servername, $username, $password, $dbname);
        
        // Truck details
        if ($response['truck_id']) {
            $stmt = $conn->prepare("SELECT model, truck_plate, truck_type FROM truck WHERE truck_id = ?");
            $stmt->bind_param("i", $response['truck_id']);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $response['truck_details'] = $row['model'] . ' - ' . $row['truck_plate'] . ' (' . $row['truck_type'] . ')';
            }
        }
        
        // Driver name
        if ($response['driver']) {
            $stmt = $conn->prepare("SELECT fullname FROM driver WHERE driver_id = ?");
            $stmt->bind_param("i", $response['driver']);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $response['driver_name'] = $row['fullname'];
            }
        }
        
        // Helper names
        if ($response['helper1']) {
            $stmt = $conn->prepare("SELECT fullname FROM helper1 WHERE helper1_id = ?");
            $stmt->bind_param("i", $response['helper1']);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $response['helper1_name'] = $row['fullname'];
            }
        }
        
        if ($response['helper2']) {
            $stmt = $conn->prepare("SELECT fullname FROM helper2 WHERE helper2_id = ?");
            $stmt->bind_param("i", $response['helper2']);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $response['helper2_name'] = $row['fullname'];
            }
        }
        
        $conn->close();
    } 
    // Fallback: Get from the latest trip in the topsheet
    elseif ($ts_id) {
        $conn = new mysqli($servername, $username, $password, $dbname);
        
        $sql = "SELECT cs.truck_id, cs.driver, cs.helper1, cs.helper2,
                t.model, t.truck_plate, t.truck_type,
                d.fullname AS driver_name,
                h1.fullname AS helper1_name,
                h2.fullname AS helper2_name
                FROM customerservice cs
                LEFT JOIN truck t ON cs.truck_id = t.truck_id
                LEFT JOIN driver d ON cs.driver = d.driver_id
                LEFT JOIN helper1 h1 ON cs.helper1 = h1.helper1_id
                LEFT JOIN helper2 h2 ON cs.helper2 = h2.helper2_id
                WHERE cs.ts_id = ?
                ORDER BY cs.cs_id DESC
                LIMIT 1";
                
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $ts_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $response = [
                'success' => true,
                'truck_id' => $row['truck_id'],
                'driver' => $row['driver'],
                'helper1' => $row['helper1'],
                'helper2' => $row['helper2'],
                'truck_details' => $row['model'] . ' - ' . $row['truck_plate'] . ' (' . $row['truck_type'] . ')',
                'driver_name' => $row['driver_name'],
                'helper1_name' => $row['helper1_name'],
                'helper2_name' => $row['helper2_name']
            ];
        }
        
        $conn->close();
    }
} catch (Exception $e) {
    $response['error'] = $e->getMessage();
}

header('Content-Type: application/json');
echo json_encode($response);
?>