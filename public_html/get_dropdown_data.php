<?php
require_once 'connection.php';

header('Content-Type: application/json');

$response = ['success' => false, 'data' => []];

try {
    $type = $_GET['type'] ?? '';
    $trip_id = $_GET['trip_id'] ?? 0;

    switch ($type) {
        case 'trucks':
            $sql = "SELECT truck_id, model, truck_plate, truck_type FROM truck WHERE status = 'Active'";
            $result = $conn->query($sql);
            while ($row = $result->fetch_assoc()) {
                $response['data'][] = $row;
            }
            $response['success'] = true;
            break;

        case 'drivers':
            $sql = "SELECT driver_id, fullname FROM driver WHERE status = 'Active'";
            $result = $conn->query($sql);
            while ($row = $result->fetch_assoc()) {
                $response['data'][] = $row;
            }
            $response['success'] = true;
            break;

        case 'helper1':
            $sql = "SELECT helper1_id, fullname FROM helper1 WHERE status = 'Active'";
            $result = $conn->query($sql);
            while ($row = $result->fetch_assoc()) {
                $response['data'][] = $row;
            }
            $response['success'] = true;
            break;

        case 'helper2':
            $sql = "SELECT helper2_id, fullname FROM helper2 WHERE status = 'Active'";
            $result = $conn->query($sql);
            while ($row = $result->fetch_assoc()) {
                $response['data'][] = $row;
            }
            $response['success'] = true;
            break;

        default:
            $response['message'] = 'Invalid type specified';
            break;
    }
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
} finally {
    $conn->close();
}

echo json_encode($response);
?>