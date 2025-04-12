<?php
// get_topsheet_assignments.php
require_once 'connection.php';

$response = ['success' => false];

try {
    if (!isset($_GET['ts_id'])) {
        throw new Exception("Topsheet ID is required");
    }

    $ts_id = filter_var($_GET['ts_id'], FILTER_SANITIZE_STRING);
    
    $sql = "SELECT 
                t.model, t.truck_plate, t.truck_type,
                d.fullname AS driver_name,
                h1.fullname AS helper1_name,
                h2.fullname AS helper2_name
            FROM customerservice cs
            LEFT JOIN truck t ON cs.truck_id = t.truck_id
            LEFT JOIN driver d ON cs.driver = d.driver_id
            LEFT JOIN helper1 h1 ON cs.helper1 = h1.helper1_id
            LEFT JOIN helper2 h2 ON cs.helper2 = h2.helper2_id
            WHERE cs.ts_id = ? AND (cs.truck_id IS NOT NULL OR cs.driver IS NOT NULL OR cs.helper1 IS NOT NULL OR cs.helper2 IS NOT NULL)
            LIMIT 1";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $ts_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $response['success'] = true;
        $response['truck_details'] = $row['truck_type'] ? 
            "{$row['model']} - {$row['truck_plate']} ({$row['truck_type']})" : null;
        $response['driver_name'] = $row['driver_name'] ?? null;
        $response['helper1_name'] = $row['helper1_name'] ?? null;
        $response['helper2_name'] = $row['helper2_name'] ?? null;
    } else {
        $response['success'] = true; // Still success, just no assignments
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

header('Content-Type: application/json');
echo json_encode($response);
?>