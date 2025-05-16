<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['owner_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

$owner_id = $_SESSION['owner_id'];

try {
    $sql = "SELECT l.*, 
            (SELECT COUNT(*) FROM Bookings b WHERE b.listing_id = l.listing_id AND b.booking_status = 'confirmed') as total_bookings,
            (SELECT SUM(total_amount) FROM Bookings b WHERE b.listing_id = l.listing_id AND b.booking_status = 'confirmed') as total_revenue
            FROM Listings l 
            WHERE l.owner_id = ?";
            
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('Database prepare failed: ' . $conn->error);
    }

    $stmt->bind_param("i", $owner_id);
    if (!$stmt->execute()) {
        throw new Exception('Database execute failed: ' . $stmt->error);
    }

    $result = $stmt->get_result();
    $listings = [];

    while ($row = $result->fetch_assoc()) {
        $listings[] = [
            'id' => $row['listing_id'],
            'name' => $row['property_name'],
            'location' => $row['property_location'],
            'type' => $row['property_type'],
            'description' => $row['property_description'],
            'amenities' => $row['property_amenities'],
            'rate' => $row['nightly_rate'],
            'total_bookings' => $row['total_bookings'],
            'total_revenue' => $row['total_revenue'],
            'created_at' => $row['created_at']
        ];
    }

    echo json_encode([
        'success' => true,
        'listings' => $listings
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error occurred']);
    error_log($e->getMessage());
} finally {
    if (isset($stmt)) $stmt->close();
    if (isset($conn)) $conn->close();
}
?>
