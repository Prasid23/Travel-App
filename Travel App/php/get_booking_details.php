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

if (!isset($_GET['booking_id'])) {
    send_json_response(false, null, 'Booking ID is required');
}

try {
    require_once 'config.php';
    
    $booking_id = $_GET['booking_id'];
    
    // Get booking details with listing information
    $stmt = $conn->prepare("
        SELECT b.*, l.property_name, l.property_location, l.image_url 
        FROM Bookings b 
        JOIN Listings l ON b.listing_id = l.listing_id 
        WHERE b.booking_id = ?
    ");
    
    $stmt->bind_param("i", $booking_id);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to get booking details: ' . $stmt->error);
    }
    
    $result = $stmt->get_result();
    $booking = $result->fetch_assoc();
    
    if (!$booking) {
        throw new Exception('Booking not found');
    }
    
    // Calculate number of nights
    $check_in = new DateTime($booking['check_in_date']);
    $check_out = new DateTime($booking['check_out_date']);
    $nights = $check_out->diff($check_in)->days;
    
    // Calculate taxes (10% of total)
    $taxes = $booking['total_amount'] * 0.10;
    
    $response_data = [
        'booking_id' => (int)$booking['booking_id'],
        'property_name' => $booking['property_name'],
        'property_location' => $booking['property_location'],
        'image_url' => $booking['image_url'] ?? 'https://via.placeholder.com/300x200',
        'check_in' => $check_in->format('F j, Y'),
        'check_out' => $check_out->format('F j, Y'),
        'guests' => (int)$booking['number_of_guests'],
        'nights' => (int)$nights,
        'room_rate' => (float)($booking['total_amount'] - $taxes) / $nights,
        'taxes' => (float)$taxes,
        'total_amount' => (float)$booking['total_amount']
    ];
    
    send_json_response(true, $response_data);

} catch (Exception $e) {
    send_json_response(false, null, $e->getMessage());
} finally {
    if (isset($stmt)) $stmt->close();
    if (isset($conn)) $conn->close();
}
?>
