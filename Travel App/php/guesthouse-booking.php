<?php
header('Content-Type: application/json');
session_start();

// Function to send JSON response
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
    send_json_response(false, null, 'Please log in to make a booking');
}

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json_response(false, null, 'Invalid request method');
}

// Validate required fields
$required_fields = ['listing_id', 'check_in', 'check_out', 'guest-count', 'room_type'];
foreach ($required_fields as $field) {
    if (!isset($_POST[$field]) || empty($_POST[$field])) {
        send_json_response(false, null, "Missing required field: $field");
    }
}

try {
    require_once 'config.php';

    // Get form data
    $user_id = $_SESSION['user_id'];
    $listing_id = $_POST['listing_id'];
    $check_in = $_POST['check_in'];
    $check_out = $_POST['check_out'];
    $num_guests = $_POST['guest-count'];
    $room_type = $_POST['room_type'];
    $special_requests = isset($_POST['special_requests']) ? $_POST['special_requests'] : '';

    // Calculate number of nights
    $check_in_date = new DateTime($check_in);
    $check_out_date = new DateTime($check_out);
    $nights = $check_out_date->diff($check_in_date)->days;

    // Get base rate from listing
    $stmt = $conn->prepare("SELECT nightly_rate FROM Listings WHERE listing_id = ?");
    $stmt->bind_param("i", $listing_id);
    
    if (!$stmt->execute()) {
        throw new Exception('Failed to get listing details: ' . $stmt->error);
    }
    
    $result = $stmt->get_result();
    $listing = $result->fetch_assoc();
    
    if (!$listing) {
        throw new Exception('Listing not found');
    }

    // Calculate total amount based on room type
    $base_rate = $listing['nightly_rate'];
    $rate_multiplier = 1;
    switch ($room_type) {
        case 'deluxe':
            $rate_multiplier = 1.4;
            break;
        case 'suite':
            $rate_multiplier = 2;
            break;
    }
    
    $nightly_rate = $base_rate * $rate_multiplier;
    $total_amount = $nightly_rate * $nights;

    // Begin transaction
    $conn->begin_transaction();

    // Get guest details from form
    $guest_name = $_POST['guest-name'];
    $guest_email = $_POST['guest-email'];

    // Validate guest name and email
    if (empty($guest_name) || strlen($guest_name) < 3) {
        throw new Exception('Guest name must be at least 3 characters long');
    }

    if (empty($guest_email) || !filter_var($guest_email, FILTER_VALIDATE_EMAIL)) {
        throw new Exception('Please provide a valid guest email');
    }

    // Create booking
    $stmt = $conn->prepare("INSERT INTO Bookings (user_id, listing_id, guest_name, guest_email, 
                            check_in_date, check_out_date, number_of_guests, total_amount, 
                            booking_status) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')");

    $stmt->bind_param("iissssid", 
        $user_id, $listing_id, $guest_name, $guest_email,
        $check_in, $check_out, $num_guests, $total_amount
    );

    if (!$stmt->execute()) {
        throw new Exception('Failed to create booking: ' . $stmt->error);
    }

    $booking_id = $stmt->insert_id;
    
    // Commit transaction
    $conn->commit();

    // Send success response with booking details
    send_json_response(true, [
        'booking_id' => $booking_id,
        'total_amount' => $total_amount,
        'redirect_url' => "../html/guesthousepayment.html?booking_id=$booking_id"
    ], 'Booking created successfully');

} catch (Exception $e) {
    // Rollback transaction if active
    if (isset($conn) && $conn->ping()) {
        $conn->rollback();
    }
    
    send_json_response(false, null, $e->getMessage());

} finally {
    // Close statement and connection if they exist
    if (isset($stmt)) $stmt->close();
    if (isset($conn)) $conn->close();
}
?>