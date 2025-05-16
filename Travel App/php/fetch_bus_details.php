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

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    send_json_response(false, null, 'Please log in to continue.');
}

// Validate required parameters
if (!isset($_GET['service_id']) || !isset($_GET['journey_date'])) {
    send_json_response(false, null, 'Missing required parameters.');
}

try {
    require_once 'config.php';

    $service_id = (int)$_GET['service_id'];
    $journey_date = $_GET['journey_date'];

    // Get bus details with schedule and availability
    $stmt = $conn->prepare("SELECT bs.*, 
                                  bsch.schedule_id, bsch.status as schedule_status,
                                  ba.availability_id, ba.status as seat_status, ba.notes
                           FROM BusServices bs
                           LEFT JOIN BusSchedules bsch ON bs.bus_service_id = bsch.bus_service_id AND bsch.date = ?
                           LEFT JOIN BusAvailability ba ON bsch.schedule_id = ba.schedule_id
                           WHERE bs.bus_service_id = ?");
    $stmt->bind_param('si', $journey_date, $service_id);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to fetch bus details: ' . $stmt->error);
    }

    $result = $stmt->get_result();
    $bus = $result->fetch_assoc();

    if (!$bus) {
        throw new Exception('Bus service not found.');
    }

    // Check if bus is available for booking
    // If no schedule exists, assume it's available
    if ($bus['schedule_id'] && ($bus['schedule_status'] === 'unavailable' || $bus['seat_status'] === 'unavailable')) {
        throw new Exception('This bus service is not available for booking on the selected date.');
    }

    // Get booked seats for this journey
    $stmt = $conn->prepare("SELECT seat_numbers FROM BusBookings 
                           WHERE bus_service_id = ? 
                           AND journey_date = ? 
                           AND booking_status != 'cancelled'");
    $stmt->bind_param('is', $service_id, $journey_date);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to fetch booked seats: ' . $stmt->error);
    }

    $result = $stmt->get_result();
    $booked_seats = [];
    
    while ($row = $result->fetch_assoc()) {
        $seats = explode(',', $row['seat_numbers']);
        $booked_seats = array_merge($booked_seats, $seats);
    }

    // Format response data
    $response_data = [
        'bus_name' => $bus['bus_name'],
        'route' => $bus['route'],
        'bus_type' => ucfirst($bus['bus_type']),
        'departure_time' => date('h:i A', strtotime($bus['departure_time'])),
        'arrival_time' => date('h:i A', strtotime($bus['arrival_time'])),
        'duration' => $bus['duration'],
        'price' => (float)$bus['price'],
        'total_seats' => (int)$bus['total_seats'],
        'booked_seats' => $booked_seats,
        'amenities' => json_decode($bus['amenities'], true)
    ];

    send_json_response(true, $response_data);
} catch (Exception $e) {
    send_json_response(false, null, $e->getMessage());
} finally {
    if (isset($stmt)) $stmt->close();
    if (isset($conn)) $conn->close();
}
?>
