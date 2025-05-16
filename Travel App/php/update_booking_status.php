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
$required = ['booking_id', 'status'];
foreach ($required as $field) {
    if (!isset($data[$field])) {
        send_json_response(false, null, "Missing required field: $field");
    }
}

try {
    require_once 'config.php';
    
    $booking_id = (int)$data['booking_id'];
    $status = $data['status'];
    $payment_method = isset($data['payment_method']) ? $data['payment_method'] : 'card';
    $user_id = $_SESSION['user_id'];
    
    // Validate status
    $valid_statuses = ['pending', 'confirmed', 'cancelled'];
    if (!in_array($status, $valid_statuses)) {
        throw new Exception('Invalid status value.');
    }
    
    // Verify booking belongs to user
    $stmt = $conn->prepare("SELECT * FROM BusBookings WHERE booking_id = ? AND user_id = ?");
    $stmt->bind_param('ii', $booking_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Booking not found or does not belong to current user.');
    }
    
    // Update booking status
    $stmt = $conn->prepare("UPDATE BusBookings SET booking_status = ?, payment_method = ? WHERE booking_id = ?");
    $stmt->bind_param('ssi', $status, $payment_method, $booking_id);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to update booking status: ' . $stmt->error);
    }
    
    if ($stmt->affected_rows === 0) {
        throw new Exception('No changes were made to the booking.');
    }
    
    send_json_response(true, [
        'booking_id' => $booking_id,
        'status' => $status,
        'message' => 'Booking status updated successfully!'
    ]);

} catch (Exception $e) {
    send_json_response(false, null, $e->getMessage());
} finally {
    if (isset($stmt)) $stmt->close();
    if (isset($conn)) $conn->close();
}
?>
