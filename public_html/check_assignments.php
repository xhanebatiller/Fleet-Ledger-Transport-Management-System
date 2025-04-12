<?php
session_start();
require_once 'connection.php';

$ts_id = $_GET['ts_id'] ?? null;

if (!$ts_id) {
    echo json_encode(['success' => false, 'message' => 'Missing topsheet ID']);
    exit;
}

$query = $conn->prepare("
    SELECT 
        t.model as truck_model,
        t.truck_plate,
        d.fullname as driver_name,
        h1.fullname as helper1_name,
        h2.fullname as helper2_name
    FROM customerservice cs
    LEFT JOIN trucks t ON cs.truck_id = t.truck_id
    LEFT JOIN drivers d ON cs.driver = d.driver_id
    LEFT JOIN helpers h1 ON cs.helper1 = h1.helper_id
    LEFT JOIN helpers h2 ON cs.helper2 = h2.helper_id
    WHERE cs.ts_id = ? 
    AND (cs.truck_id IS NOT NULL OR cs.driver IS NOT NULL OR cs.helper1 IS NOT NULL OR cs.helper2 IS NOT NULL)
    LIMIT 1
");
$query->bind_param("s", $ts_id);
$query->execute();
$result = $query->get_result();

if ($result->num_rows > 0) {
    $data = $result->fetch_assoc();
    $response = [
        'hasAssignments' => true,
        'truck' => $data['truck_model'] ? $data['truck_model'] . ' (' . $data['truck_plate'] . ')' : null,
        'driver' => $data['driver_name'] ?? null,
        'helper1' => $data['helper1_name'] ?? null,
        'helper2' => $data['helper2_name'] ?? null
    ];
} else {
    $response = ['hasAssignments' => false];
}

echo json_encode($response);
?>