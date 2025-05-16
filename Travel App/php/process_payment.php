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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json_response(false, null, 'Invalid request method');
}

try {
    require_once 'config.php';
    
    // Get payment details
    $booking_id = $_POST['booking_id'];
    $card_name = $_POST['card_name'];
    $card_number = $_POST['card_number'];
    $expiry = $_POST['expiry'];
    $cvv = $_POST['cvv'];
    
    // In a real application, you would:
    // 1. Validate the card details
    // 2. Process the payment through a payment gateway
    // 3. Store the transaction details securely
    
    // For now, we'll just update the booking status
    $stmt = $conn->prepare("
        UPDATE Bookings 
        SET booking_status = 'confirmed' 
        WHERE booking_id = ?
    ");
    
    $stmt->bind_param("i", $booking_id);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to update booking status: ' . $stmt->error);
    }
    
    // Get user_id from the booking record
    $stmt = $conn->prepare("SELECT user_id, total_amount FROM Bookings WHERE booking_id = ?");
    $stmt->bind_param("i", $booking_id);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to get booking details: ' . $stmt->error);
    }
    
    $result = $stmt->get_result();
    $booking = $result->fetch_assoc();
    
    if (!$booking) {
        throw new Exception('Booking not found');
    }
    
    // Create a transaction record
    $stmt = $conn->prepare("
        INSERT INTO Transactions (booking_id, user_id, amount, status)
        VALUES (?, ?, ?, 'completed')
    ");
    
    $stmt->bind_param("iid", $booking_id, $booking['user_id'], $booking['total_amount']);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to create transaction record: ' . $stmt->error);
    }
    
    send_json_response(true, [
        'booking_id' => $booking_id,
        'status' => 'confirmed'
    ], 'Payment processed successfully');

} catch (Exception $e) {
    send_json_response(false, null, $e->getMessage());
} finally {
    if (isset($stmt)) $stmt->close();
    if (isset($conn)) $conn->close();
}
?>
