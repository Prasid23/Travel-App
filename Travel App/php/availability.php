<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'config.php';

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

if (!isset($_SESSION['owner_id']) || !isset($_SESSION['listing_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated or no listing selected']);
    exit();
}

$owner_id = $_SESSION['owner_id'];
$listing_id = $_SESSION['listing_id'];
$date = $_POST['date'] ?? null;
$status = $_POST['status'] ?? null;

if (!$date || !$status || !in_array($status, ['available', 'unavailable', 'booked'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Date and valid status are required']);
    exit();
}

try {
    // Verify listing ownership
    $verify_sql = "SELECT owner_id FROM Listings WHERE listing_id = ?";
    $verify_stmt = $conn->prepare($verify_sql);
    $verify_stmt->bind_param("i", $listing_id);
    $verify_stmt->execute();
    $verify_result = $verify_stmt->get_result();

    if ($verify_result->num_rows === 0 || $verify_result->fetch_assoc()['owner_id'] !== $owner_id) {
        throw new Exception('Unauthorized access to listing');
    }
    $verify_stmt->close();

    // Check if there are any confirmed bookings for this date
    $booking_sql = "SELECT booking_id FROM Bookings 
                    WHERE listing_id = ? 
                    AND ? BETWEEN check_in_date AND DATE_SUB(check_out_date, INTERVAL 1 DAY)
                    AND booking_status = 'confirmed'";
    $booking_stmt = $conn->prepare($booking_sql);
    $booking_stmt->bind_param("is", $listing_id, $date);
    $booking_stmt->execute();
    $booking_result = $booking_stmt->get_result();

    if ($booking_result->num_rows > 0) {
        throw new Exception('Cannot update availability: There are confirmed bookings for this date');
    }
    $booking_stmt->close();

    // Begin transaction
    $conn->begin_transaction();

    // Check if availability record exists
    $check_sql = "SELECT availability_id FROM Availability 
                  WHERE listing_id = ? AND date = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("is", $listing_id, $date);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        // Update existing record
        $update_sql = "UPDATE Availability SET status = ? 
                      WHERE listing_id = ? AND date = ?";
        $stmt = $conn->prepare($update_sql);
        $stmt->bind_param("sis", $status, $listing_id, $date);
    } else {
        // Insert new record
        $insert_sql = "INSERT INTO Availability (listing_id, date, status) 
                      VALUES (?, ?, ?)";
        $stmt = $conn->prepare($insert_sql);
        $stmt->bind_param("iss", $listing_id, $date, $status);
    }

    if (!$stmt->execute()) {
        throw new Exception($stmt->error);
    }

    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Availability updated successfully'
    ]);

} catch (Exception $e) {
    if (isset($conn)) $conn->rollback();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    error_log($e->getMessage());
} finally {
    if (isset($check_stmt)) $check_stmt->close();
    if (isset($stmt)) $stmt->close();
    if (isset($conn)) $conn->close();
}
?>
