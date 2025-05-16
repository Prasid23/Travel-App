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

// Validate required parameters
$required_params = ['from', 'to', 'date', 'passengers'];
foreach ($required_params as $param) {
    if (!isset($_GET[$param]) || empty($_GET[$param])) {
        send_json_response(false, null, "Missing required parameter: $param");
    }
}

try {
    require_once 'config.php';

    $from = $_GET['from'];
    $to = $_GET['to'];
    $date = $_GET['date'];
    $passengers = (int)$_GET['passengers'];
    $bus_type = isset($_GET['type']) && $_GET['type'] !== 'all' ? $_GET['type'] : null;

    // Convert date to day of week (0 = Sunday, 6 = Saturday)
    $day_of_week = date('w', strtotime($date));
    $days_map = [
        0 => 'Sunday',
        1 => 'Monday',
        2 => 'Tuesday',
        3 => 'Wednesday',
        4 => 'Thursday',
        5 => 'Friday',
        6 => 'Saturday'
    ];
    $day_name = $days_map[$day_of_week];

    // Build the route string to match
    $route = "$from to $to";

    // Build the query
    $query = "SELECT * FROM BusServices 
              WHERE route = ? 
              AND (total_seats - 
                  (SELECT COUNT(*) FROM BusBookings 
                   WHERE bus_service_id = BusServices.bus_service_id 
                   AND journey_date = ?
                   AND booking_status != 'cancelled')
              ) >= ?";
    $params = [$route, $date, $passengers];
    $types = "ssi";

    // Add bus type filter if specified
    if ($bus_type && $bus_type !== 'all') {
        $query .= " AND bus_type = ?";
        $params[] = $bus_type;
        $types .= "s";
    }

    // Add bus type filter if specified
    if ($bus_type) {
        $query .= " AND bus_type = ?";
        $params[] = $bus_type;
        $types .= "s";
    }

    // Add sorting
    $query .= " ORDER BY departure_time ASC";

    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);

    if (!$stmt->execute()) {
        throw new Exception('Failed to fetch bus services: ' . $stmt->error);
    }

    $result = $stmt->get_result();
    $services = [];

    while ($row = $result->fetch_assoc()) {
        // Format times for display
        $dept_time = date('h:i A', strtotime($row['departure_time']));
        $arr_time = date('h:i A', strtotime($row['arrival_time']));

        // Parse amenities
        $amenities = $row['amenities'] ? json_decode($row['amenities'], true) : [];

        // Calculate available seats
        $stmt2 = $conn->prepare("SELECT COUNT(*) as booked FROM BusBookings 
                               WHERE bus_service_id = ? 
                               AND journey_date = ? 
                               AND booking_status != 'cancelled'");
        $stmt2->bind_param('is', $row['bus_service_id'], $date);
        $stmt2->execute();
        $booking_result = $stmt2->get_result()->fetch_assoc();
        $available_seats = $row['total_seats'] - $booking_result['booked'];

        $services[] = [
            'service_id' => $row['bus_service_id'],
            'operator_name' => $row['bus_name'],
            'bus_type' => ucfirst($row['bus_type']),
            'departure_time' => $dept_time,
            'arrival_time' => $arr_time,
            'duration' => $row['duration'],
            'price' => (float)$row['price'],
            'available_seats' => (int)$available_seats,
            'amenities' => $amenities
        ];
        $stmt2->close();
    }

    send_json_response(true, [
        'services' => $services,
        'total' => count($services),
        'date' => $date,
        'from' => $from,
        'to' => $to,
        'passengers' => $passengers
    ]);

} catch (Exception $e) {
    send_json_response(false, null, $e->getMessage());
} finally {
    if (isset($stmt)) $stmt->close();
    if (isset($conn)) $conn->close();
}
?>
