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
            $required = ['bus_service_id', 'departure_date'];
            foreach ($required as $field) {
                if (!isset($data[$field])) {
                    send_json_response(false, null, "Missing required field: $field");
                }
            }
            
            // Check if bus service belongs to this operator
            $check = $conn->prepare("SELECT bus_service_id FROM BusServices WHERE bus_service_id = ? AND operator_id = ?");
            $check->execute([$data['bus_service_id'], $operator_id]);
            
            if ($check->rowCount() === 0) {
                send_json_response(false, null, "Bus service not found or you don't have permission to add schedules for it");
            }
            
            // Insert new bus schedule
            $stmt = $conn->prepare("INSERT INTO BusSchedules (
                bus_service_id, date, status
            ) VALUES (?, ?, ?)");
            
            $status = isset($data['status']) ? $data['status'] : 'available';
            
            if (!$stmt->execute([
                $data['bus_service_id'],
                $data['departure_date'],
                $status
            ])) {
                throw new Exception('Failed to add bus schedule');
            }
            
            $schedule_id = $conn->lastInsertId();
            
            // Return success response
            send_json_response(true, [
                'schedule_id' => $schedule_id
            ], 'Bus schedule added successfully');
            break;
            
        case 'update':
            // Validate required parameters
            if (!isset($data['schedule_id'])) {
                send_json_response(false, null, "Missing schedule_id");
            }
            
            // Check if schedule belongs to this operator's bus service
            $check = $conn->prepare("
                SELECT bs.schedule_id, bs.bus_service_id, b.operator_id 
                FROM BusSchedules bs
                JOIN BusServices b ON bs.bus_service_id = b.bus_service_id
                WHERE bs.schedule_id = ? AND b.operator_id = ?
            ");
            $check->execute([$data['schedule_id'], $operator_id]);
            
            // Debug info
            $debug_info = [
                'schedule_id' => $data['schedule_id'],
                'operator_id' => $operator_id,
                'row_count' => $check->rowCount()
            ];
            
            if ($check->rowCount() === 0) {
                // Let's check if the schedule exists at all
                $checkSchedule = $conn->prepare("SELECT schedule_id, bus_service_id FROM BusSchedules WHERE schedule_id = ?");
                $checkSchedule->execute([$data['schedule_id']]);
                
                if ($checkSchedule->rowCount() > 0) {
                    $scheduleData = $checkSchedule->fetch(PDO::FETCH_ASSOC);
                    $debug_info['schedule_exists'] = true;
                    $debug_info['bus_service_id'] = $scheduleData['bus_service_id'];
                    
                    // Check if the bus service exists
                    $checkService = $conn->prepare("SELECT bus_service_id, operator_id FROM BusServices WHERE bus_service_id = ?");
                    $checkService->execute([$scheduleData['bus_service_id']]);
                    
                    if ($checkService->rowCount() > 0) {
                        $serviceData = $checkService->fetch(PDO::FETCH_ASSOC);
                        $debug_info['service_exists'] = true;
                        $debug_info['service_operator_id'] = $serviceData['operator_id'];
                    } else {
                        $debug_info['service_exists'] = false;
                    }
                } else {
                    $debug_info['schedule_exists'] = false;
                }
                
                send_json_response(false, $debug_info, "Schedule not found or you don't have permission to update it");
            }
            
            // Build update query dynamically
            $updateFields = [];
            $params = [];
            
            // Handle departure_date parameter (map it to date field in database)
            if (isset($data['departure_date'])) {
                $updateFields[] = "date = ?";
                $params[] = $data['departure_date'];
            }
            
            // Handle status parameter
            if (isset($data['status'])) {
                $updateFields[] = "status = ?";
                $params[] = $data['status'];
            }
            
            if (empty($updateFields)) {
                send_json_response(false, null, "No fields to update");
            }
            
            // Add schedule_id to params
            $params[] = $data['schedule_id'];
            
            $sql = "UPDATE BusSchedules SET " . implode(", ", $updateFields) . " WHERE schedule_id = ?";
            
            $stmt = $conn->prepare($sql);
            
            if (!$stmt->execute($params)) {
                throw new Exception('Failed to update bus schedule');
            }
            
            // Return success response
            send_json_response(true, null, 'Bus schedule updated successfully');
            break;
            
        case 'delete':
            // Validate required parameters
            if (!isset($data['schedule_id'])) {
                send_json_response(false, null, "Missing schedule_id");
            }
            
            // Check if schedule belongs to this operator's bus service
            $check = $conn->prepare("
                SELECT bs.schedule_id 
                FROM BusSchedules bs
                JOIN BusServices b ON bs.bus_service_id = b.bus_service_id
                WHERE bs.schedule_id = ? AND b.operator_id = ?
            ");
            $check->execute([$data['schedule_id'], $operator_id]);
            
            if ($check->rowCount() === 0) {
                send_json_response(false, null, "Schedule not found or you don't have permission to delete it");
            }
            
            // Check if there are any bookings for this schedule
            $bookingCheck = $conn->prepare("
                SELECT booking_id 
                FROM BusBookings 
                WHERE bus_service_id = (
                    SELECT bus_service_id FROM BusSchedules WHERE schedule_id = ?
                )
                AND journey_date = (
                    SELECT date FROM BusSchedules WHERE schedule_id = ?
                )
                LIMIT 1
            ");
            $bookingCheck->execute([$data['schedule_id'], $data['schedule_id']]);
            
            if ($bookingCheck->rowCount() > 0) {
                send_json_response(false, null, "Cannot delete schedule with existing bookings");
            }
            
            // Delete bus schedule
            $stmt = $conn->prepare("DELETE FROM BusSchedules WHERE schedule_id = ?");
            
            if (!$stmt->execute([$data['schedule_id']])) {
                throw new Exception('Failed to delete bus schedule');
            }
            
            // Return success response
            send_json_response(true, null, 'Bus schedule deleted successfully');
            break;
            
        case 'get':
            // Get a single schedule by its ID
            if (isset($data['schedule_id'])) {
                $stmt = $conn->prepare("
                    SELECT bs.*, b.bus_name, b.route
                    FROM BusSchedules bs
                    JOIN BusServices b ON bs.bus_service_id = b.bus_service_id
                    WHERE bs.schedule_id = ? AND b.operator_id = ?
                ");
                $stmt->execute([$data['schedule_id'], $operator_id]);
                
                if ($stmt->rowCount() === 0) {
                    send_json_response(false, null, "Schedule not found or you don't have permission to view it");
                }
                
                $schedule = $stmt->fetch(PDO::FETCH_ASSOC);
                send_json_response(true, $schedule, 'Schedule retrieved successfully');
            }
            // Get schedules for a specific bus service
            else if (isset($data['bus_service_id'])) {
                // Check if bus service belongs to this operator
                $check = $conn->prepare("SELECT bus_service_id FROM BusServices WHERE bus_service_id = ? AND operator_id = ?");
                $check->execute([$data['bus_service_id'], $operator_id]);
                
                if ($check->rowCount() === 0) {
                    send_json_response(false, null, "Bus service not found or you don't have permission to view its schedules");
                }
                
                $stmt = $conn->prepare("
                    SELECT * FROM BusSchedules 
                    WHERE bus_service_id = ?
                    ORDER BY date DESC
                ");
                $stmt->execute([$data['bus_service_id']]);
                
                $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
                send_json_response(true, $schedules, 'Bus schedules retrieved successfully');
            } 
            // Get all schedules for all bus services of this operator
            else {
                $stmt = $conn->prepare("
                    SELECT bs.*, b.bus_name, b.route
                    FROM BusSchedules bs
                    JOIN BusServices b ON bs.bus_service_id = b.bus_service_id
                    WHERE b.operator_id = ?
                    ORDER BY bs.date DESC
                ");
                $stmt->execute([$operator_id]);
                
                $schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
                send_json_response(true, $schedules, 'All bus schedules retrieved successfully');
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
