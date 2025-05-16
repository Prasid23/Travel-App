<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'config.php';

header('Content-Type: application/json');

if (!isset($_SESSION['owner_id']) || !isset($_SESSION['listing_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated or no listing selected']);
    exit();
}

$month = $_GET['month'] ?? date('m');
$year = $_GET['year'] ?? date('Y');
$listing_id = $_SESSION['listing_id'];
$owner_id = $_SESSION['owner_id'];

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

    // Get availability for the month
    $sql = "SELECT date, status FROM Availability 
            WHERE listing_id = ? 
            AND MONTH(date) = ? 
            AND YEAR(date) = ?";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iii", $listing_id, $month, $year);
    $stmt->execute();
    $result = $stmt->get_result();

    $availability = [];
    while ($row = $result->fetch_assoc()) {
        $availability[$row['date']] = [
            'status' => $row['status']
        ];
    }

    // Get bookings for the month
    $bookings_sql = "SELECT check_in_date, check_out_date FROM Bookings 
                     WHERE listing_id = ? 
                     AND (
                         (MONTH(check_in_date) = ? AND YEAR(check_in_date) = ?) 
                         OR 
                         (MONTH(check_out_date) = ? AND YEAR(check_out_date) = ?)
                     )
                     AND booking_status = 'confirmed'";
                     
    $bookings_stmt = $conn->prepare($bookings_sql);
    $bookings_stmt->bind_param("iiiii", $listing_id, $month, $year, $month, $year);
    $bookings_stmt->execute();
    $bookings_result = $bookings_stmt->get_result();

    $bookings = [];
    while ($row = $bookings_result->fetch_assoc()) {
        $start = new DateTime($row['check_in_date']);
        $end = new DateTime($row['check_out_date']);
        $interval = new DateInterval('P1D');
        $period = new DatePeriod($start, $interval, $end);

        foreach ($period as $date) {
            if ($date->format('n') == $month && $date->format('Y') == $year) {
                $dateStr = $date->format('Y-m-d');
                $availability[$dateStr] = [
                    'is_available' => false
                ];
            }
        }
    }

    echo json_encode([
        'success' => true,
        'availability' => $availability
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    error_log($e->getMessage());
} finally {
    if (isset($stmt)) $stmt->close();
    if (isset($bookings_stmt)) $bookings_stmt->close();
    if (isset($conn)) $conn->close();
}
?>
