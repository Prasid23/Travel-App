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

if (!isset($_GET['booking_id'])) {
    send_json_response(false, null, 'Booking ID is required');
}

try {
    require_once 'config.php';
    
    $booking_id = (int)$_GET['booking_id'];
    $user_id = $_SESSION['user_id'];
    
    // Get bus booking details with bus service information
    $stmt = $conn->prepare("
        SELECT bb.*, bs.bus_name, bs.route, bs.bus_type, 
               bs.departure_time, bs.arrival_time, bs.duration, bs.price,
               bs.amenities
        FROM BusBookings bb 
        JOIN BusServices bs ON bb.bus_service_id = bs.bus_service_id 
        WHERE bb.booking_id = ? AND bb.user_id = ?
    ");
    
    $stmt->bind_param("ii", $booking_id, $user_id);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to get booking details: ' . $stmt->error);
    }
    
    $result = $stmt->get_result();
    $booking = $result->fetch_assoc();
    
    if (!$booking) {
        throw new Exception('Booking not found or does not belong to current user');
    }
    
    // Format the response data
    $response_data = [
        'booking_id' => (int)$booking['booking_id'],
        'bus_service_id' => (int)$booking['bus_service_id'],
        'bus_name' => $booking['bus_name'],
        'route' => $booking['route'],
        'bus_type' => $booking['bus_type'],
        'journey_date' => $booking['journey_date'],
        'departure_time' => $booking['departure_time'],
        'arrival_time' => $booking['arrival_time'],
        'duration' => $booking['duration'],
        'num_seats' => (int)$booking['num_seats'],
        'seat_numbers' => $booking['seat_numbers'],
        'price_per_seat' => (float)$booking['price'],
        'total_amount' => (float)$booking['total_amount'],
        'booking_status' => $booking['booking_status'],
        'amenities' => json_decode($booking['amenities'], true)
    ];
    
    send_json_response(true, $response_data);

} catch (Exception $e) {
    send_json_response(false, null, $e->getMessage());
} finally {
    if (isset($stmt)) $stmt->close();
    if (isset($conn)) $conn->close();
}
?>
