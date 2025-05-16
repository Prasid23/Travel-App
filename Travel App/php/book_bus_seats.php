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

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json_response(false, null, 'Invalid request method.');
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

// Validate required parameters
$required = ['service_id', 'journey_date', 'seats', 'total_amount'];
foreach ($required as $field) {
    if (!isset($data[$field])) {
        send_json_response(false, null, "Missing required field: $field");
    }
}

try {
    require_once 'config.php';
    
    $service_id = (int)$data['service_id'];
    $user_id = $_SESSION['user_id'];
    $journey_date = $data['journey_date'];
    $seats = $data['seats'];
    $total_amount = (float)$data['total_amount'];
    
    // Start transaction
    $conn->begin_transaction();

    // Check if seats are still available
    $stmt = $conn->prepare("SELECT seat_numbers FROM BusBookings 
                           WHERE bus_service_id = ? 
                           AND journey_date = ? 
                           AND booking_status != 'cancelled'");
    $stmt->bind_param('is', $service_id, $journey_date);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $booked_seats = [];
    while ($row = $result->fetch_assoc()) {
        $booked = explode(',', $row['seat_numbers']);
        $booked_seats = array_merge($booked_seats, $booked);
    }

    // Check for conflicts
    $selected_seats = explode(',', $seats);
    $conflicts = array_intersect($selected_seats, $booked_seats);
    
    if (!empty($conflicts)) {
        throw new Exception('Some selected seats are no longer available: ' . implode(', ', $conflicts));
    }

    // Create booking
    $stmt = $conn->prepare("INSERT INTO BusBookings (
        bus_service_id, user_id, journey_date, num_seats, 
        seat_numbers, total_amount, booking_status
    ) VALUES (?, ?, ?, ?, ?, ?, 'pending')");

    $num_seats = count($selected_seats);
    $stmt->bind_param('iisisi', 
        $service_id, $user_id, $journey_date, 
        $num_seats, $seats, $total_amount
    );

    if (!$stmt->execute()) {
        throw new Exception('Failed to create booking: ' . $stmt->error);
    }

    $booking_id = $stmt->insert_id;

    // Get bus details for the response
    $stmt = $conn->prepare("SELECT bus_name, route, bus_type FROM BusServices WHERE bus_service_id = ?");
    $stmt->bind_param('i', $service_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $bus_details = $result->fetch_assoc();

    // Commit transaction
    $conn->commit();

    send_json_response(true, [
        'booking_id' => $booking_id,
        'bus_name' => $bus_details['bus_name'],
        'route' => $bus_details['route'],
        'bus_type' => $bus_details['bus_type'],
        'journey_date' => $journey_date,
        'num_seats' => $num_seats,
        'seat_numbers' => $seats,
        'total_amount' => $total_amount,
        'message' => 'Seats booked successfully!'
    ]);

} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollback();
    }
    send_json_response(false, null, $e->getMessage());
} finally {
    if (isset($stmt)) $stmt->close();
    if (isset($conn)) $conn->close();
}
?>
