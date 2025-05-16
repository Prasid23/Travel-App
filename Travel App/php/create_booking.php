<?php
header('Content-Type: application/json');
require_once 'db_connect.php';

// Get JSON data from request body
$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['listing_id']) || !isset($data['check_in']) || !isset($data['check_out']) || 
    !isset($data['guests']) || !isset($data['name']) || !isset($data['email'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing required fields'
    ]);
    exit();
}

try {
    // Fetch the nightly rate for the listing
    $rate_sql = "SELECT nightly_rate FROM Listings WHERE listing_id = ?";
    $rate_stmt = $conn->prepare($rate_sql);
    $rate_stmt->bind_param("i", $data['listing_id']);
    $rate_stmt->execute();
    $rate_result = $rate_stmt->get_result();
    $rate_row = $rate_result->fetch_assoc();
    $nightly_rate = $rate_row['nightly_rate'];

    // Calculate total amount
    $check_in = new DateTime($data['check_in']);
    $check_out = new DateTime($data['check_out']);
    $nights = $check_in->diff($check_out)->days;
    $total_amount = $nights * $nightly_rate;

    // Create the booking
    $conn->begin_transaction();

    // Insert into Bookings table
    $booking_sql = "INSERT INTO Bookings (listing_id, guest_name, guest_email, 
                    check_in_date, check_out_date, number_of_guests, total_amount, booking_status) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, 'pending')";
                    
    $booking_stmt = $conn->prepare($booking_sql);
    $booking_stmt->bind_param("issssid", 
        $data['listing_id'], 
        $data['name'],
        $data['email'],
        $data['check_in'],
        $data['check_out'],
        $data['guests'],
        $total_amount
    );
    $booking_stmt->execute();
    $booking_id = $conn->insert_id;

    $conn->commit();

    echo json_encode([
        'success' => true,
        'booking_id' => $booking_id,
        'total_amount' => $total_amount,
        'message' => 'Booking created successfully'
    ]);

} catch (Exception $e) {
    $conn->rollback();
    error_log('Error in create_booking.php: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to create booking: ' . $e->getMessage()
    ]);
}

$conn = null;
?>
