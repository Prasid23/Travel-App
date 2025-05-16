<?php
header('Content-Type: application/json');
session_start();

function send_json_response($success, $data = null, $message = '') {
    $response = [
        'success' => $success,
        'message' => $message
    ];
    if ($data !== null) {
        $response['data'] = $data;
    }
    echo json_encode($response);
    exit();
}

// Check if operator is logged in
if (!isset($_SESSION['operator_id'])) {
    send_json_response(false, null, 'Unauthorized access. Please login.');
}

// Get request data
$data = json_decode(file_get_contents('php://input'), true);
$action = isset($_GET['action']) ? $_GET['action'] : '';

try {
    // Database configuration
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "Travel";
    
    // Create connection using PDO
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    // Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $operator_id = $_SESSION['operator_id'];
    
    switch ($action) {
        case 'add':
            // Validate required parameters
            $required = ['bus_name', 'route', 'bus_type', 'price', 'total_seats', 'departure_time', 'arrival_time'];
            foreach ($required as $field) {
                if (!isset($data[$field])) {
                    send_json_response(false, null, "Missing required field: $field");
                }
            }
            
            // Insert new bus service
            $stmt = $conn->prepare("INSERT INTO BusServices (
                operator_id, bus_name, route, bus_type, departure_time, arrival_time, 
                duration, price, total_seats, amenities
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            
            if (!$stmt->execute([
                $operator_id, 
                $data['bus_name'], 
                $data['route'], 
                $data['bus_type'], 
                $data['departure_time'],
                $data['arrival_time'],
                $data['duration'] ?? '',
                $data['price'], 
                $data['total_seats'],
                $data['amenities'] ?? ''
            ])) {
                throw new Exception('Failed to add bus service');
            }
            
            $bus_service_id = $conn->lastInsertId();
            
            // Return success response
            send_json_response(true, [
                'bus_service_id' => $bus_service_id
            ], 'Bus service added successfully');
            break;
            
        case 'update':
            // Validate required parameters
            if (!isset($data['bus_service_id'])) {
                send_json_response(false, null, "Missing bus_service_id");
            }
            
            // Check if bus service belongs to this operator
            $check = $conn->prepare("SELECT bus_service_id FROM BusServices WHERE bus_service_id = ? AND operator_id = ?");
            $check->execute([$data['bus_service_id'], $operator_id]);
            
            if ($check->rowCount() === 0) {
                send_json_response(false, null, "Bus service not found or you don't have permission to update it");
            }
            
            // Build update query dynamically
            $updateFields = [];
            $params = [];
            
            $allowedFields = ['bus_name', 'route', 'bus_type', 'departure_time', 'arrival_time', 
                             'duration', 'price', 'total_seats', 'amenities'];
            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $updateFields[] = "$field = ?";
                    $params[] = $data[$field];
                }
            }
            
            if (empty($updateFields)) {
                send_json_response(false, null, "No fields to update");
            }
            
            // Add bus_service_id and operator_id to params
            $params[] = $data['bus_service_id'];
            $params[] = $operator_id;
            
            $sql = "UPDATE BusServices SET " . implode(", ", $updateFields) . 
                   " WHERE bus_service_id = ? AND operator_id = ?";
            
            $stmt = $conn->prepare($sql);
            
            if (!$stmt->execute($params)) {
                throw new Exception('Failed to update bus service');
            }
            
            // Return success response
            send_json_response(true, null, 'Bus service updated successfully');
            break;
            
        case 'delete':
            // Validate required parameters
            if (!isset($data['bus_service_id'])) {
                send_json_response(false, null, "Missing bus_service_id");
            }
            
            // Check if bus service belongs to this operator
            $check = $conn->prepare("SELECT bus_service_id FROM BusServices WHERE bus_service_id = ? AND operator_id = ?");
            $check->execute([$data['bus_service_id'], $operator_id]);
            
            if ($check->rowCount() === 0) {
                send_json_response(false, null, "Bus service not found or you don't have permission to delete it");
            }
            
            // Check if there are any bookings for this bus service
            $bookingCheck = $conn->prepare("SELECT booking_id FROM BusBookings WHERE bus_service_id = ? LIMIT 1");
            $bookingCheck->execute([$data['bus_service_id']]);
            
            if ($bookingCheck->rowCount() > 0) {
                send_json_response(false, null, "Cannot delete bus service with existing bookings");
            }
            
            // Delete bus service
            $stmt = $conn->prepare("DELETE FROM BusServices WHERE bus_service_id = ? AND operator_id = ?");
            
            if (!$stmt->execute([$data['bus_service_id'], $operator_id])) {
                throw new Exception('Failed to delete bus service');
            }
            
            // Return success response
            send_json_response(true, null, 'Bus service deleted successfully');
            break;
            
        case 'get':
            // Get single bus service
            if (isset($data['bus_service_id'])) {
                $stmt = $conn->prepare("
                    SELECT * FROM BusServices 
                    WHERE bus_service_id = ? AND operator_id = ?
                ");
                $stmt->execute([$data['bus_service_id'], $operator_id]);
                
                if ($stmt->rowCount() === 0) {
                    send_json_response(false, null, "Bus service not found");
                }
                
                $bus_service = $stmt->fetch(PDO::FETCH_ASSOC);
                send_json_response(true, $bus_service, 'Bus service retrieved successfully');
            } 
            // Get all bus services for this operator
            else {
                $stmt = $conn->prepare("
                    SELECT * FROM BusServices 
                    WHERE operator_id = ?
                    ORDER BY bus_service_id DESC
                ");
                $stmt->execute([$operator_id]);
                
                $bus_services = $stmt->fetchAll(PDO::FETCH_ASSOC);
                send_json_response(true, $bus_services, 'Bus services retrieved successfully');
            }
            break;
            
        default:
            send_json_response(false, null, 'Invalid action');
    }
    
} catch (PDOException $e) {
    send_json_response(false, null, 'Database error: ' . $e->getMessage());
} catch (Exception $e) {
    send_json_response(false, null, $e->getMessage());
} finally {
    // Close connection
    $conn = null;
}
?>
