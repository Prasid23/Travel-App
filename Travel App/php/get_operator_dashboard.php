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

// Check if operator is logged in
if (!isset($_SESSION['operator_id'])) {
    send_json_response(false, null, 'Unauthorized access. Please login.');
}

try {
    // Database configuration
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "Travel";
    
    // Create connection using PDO
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    // Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $operator_id = $_SESSION['operator_id'];
    
    // Get bus services count
    $stmt = $conn->prepare("SELECT COUNT(*) as total_buses FROM BusServices WHERE operator_id = ?");
    $stmt->execute([$operator_id]);
    $total_buses = $stmt->fetch(PDO::FETCH_ASSOC)['total_buses'];
    
    // Get total bookings
    $stmt = $conn->prepare("
        SELECT COUNT(*) as total_bookings 
        FROM BusBookings bb 
        JOIN BusServices bs ON bb.bus_service_id = bs.bus_service_id 
        WHERE bs.operator_id = ?
    ");
    $stmt->execute([$operator_id]);
    $total_bookings = $stmt->fetch(PDO::FETCH_ASSOC)['total_bookings'];
    
    // Get booking statistics by status
    $stmt = $conn->prepare("
        SELECT 
            SUM(CASE WHEN bb.booking_status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_bookings,
            SUM(CASE WHEN bb.booking_status = 'pending' THEN 1 ELSE 0 END) as pending_bookings,
            SUM(CASE WHEN bb.booking_status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_bookings
        FROM BusBookings bb 
        JOIN BusServices bs ON bb.bus_service_id = bs.bus_service_id 
        WHERE bs.operator_id = ?
    ");
    $stmt->execute([$operator_id]);
    $booking_stats = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Get total revenue
    $stmt = $conn->prepare("
        SELECT SUM(bb.total_amount) as total_revenue 
        FROM BusBookings bb 
        JOIN BusServices bs ON bb.bus_service_id = bs.bus_service_id 
        WHERE bs.operator_id = ? AND bb.booking_status = 'confirmed'
    ");
    $stmt->execute([$operator_id]);
    $total_revenue = $stmt->fetch(PDO::FETCH_ASSOC)['total_revenue'] ?? 0;
    
    // Get recent bookings
    $stmt = $conn->prepare("
        SELECT 
            bb.booking_id, 
            bb.journey_date, 
            bb.num_seats, 
            bb.seat_numbers,
            bb.total_amount, 
            bb.booking_status,
            bb.created_at,
            bs.bus_name,
            bs.route,
            bs.bus_type
        FROM BusBookings bb 
        JOIN BusServices bs ON bb.bus_service_id = bs.bus_service_id 
        WHERE bs.operator_id = ?
        ORDER BY bb.booking_id DESC
        LIMIT 5
    ");
    $stmt->execute([$operator_id]);
    $recent_bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get bus services
    $stmt = $conn->prepare("
        SELECT 
            bus_service_id, 
            bus_name, 
            route, 
            bus_type,
            departure_time,
            arrival_time,
            duration,
            price,
            total_seats,
            amenities
        FROM BusServices 
        WHERE operator_id = ?
        ORDER BY bus_service_id DESC
    ");
    $stmt->execute([$operator_id]);
    $bus_services = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get upcoming schedules
    $stmt = $conn->prepare("
        SELECT 
            bs.schedule_id,
            bs.bus_service_id,
            bs.date,
            bs.status,
            b.bus_name,
            b.route
        FROM BusSchedules bs
        JOIN BusServices b ON bs.bus_service_id = b.bus_service_id
        WHERE b.operator_id = ? AND bs.date >= CURDATE()
        ORDER BY bs.date ASC
        LIMIT 10
    ");
    $stmt->execute([$operator_id]);
    $upcoming_schedules = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get monthly revenue data for charts
    $stmt = $conn->prepare("
        SELECT 
            DATE_FORMAT(bb.journey_date, '%Y-%m') as month,
            SUM(bb.total_amount) as revenue
        FROM BusBookings bb
        JOIN BusServices bs ON bb.bus_service_id = bs.bus_service_id
        WHERE bs.operator_id = ? AND bb.booking_status = 'confirmed'
        GROUP BY DATE_FORMAT(bb.journey_date, '%Y-%m')
        ORDER BY month DESC
        LIMIT 6
    ");
    $stmt->execute([$operator_id]);
    $monthly_revenue = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get popular routes
    $stmt = $conn->prepare("
        SELECT 
            bs.route,
            COUNT(bb.booking_id) as booking_count,
            SUM(bb.total_amount) as total_revenue
        FROM BusBookings bb
        JOIN BusServices bs ON bb.bus_service_id = bs.bus_service_id
        WHERE bs.operator_id = ? AND bb.booking_status = 'confirmed'
        GROUP BY bs.route
        ORDER BY booking_count DESC
        LIMIT 5
    ");
    $stmt->execute([$operator_id]);
    $popular_routes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Return dashboard data
    send_json_response(true, [
        'operator_info' => [
            'name' => $_SESSION['operator_name'],
            'email' => $_SESSION['operator_email'],
            'company' => $_SESSION['operator_company']
        ],
        'summary' => [
            'total_buses' => $total_buses,
            'total_bookings' => $total_bookings,
            'confirmed_bookings' => $booking_stats['confirmed_bookings'] ?? 0,
            'pending_bookings' => $booking_stats['pending_bookings'] ?? 0,
            'cancelled_bookings' => $booking_stats['cancelled_bookings'] ?? 0,
            'total_revenue' => $total_revenue
        ],
        'recent_bookings' => $recent_bookings,
        'bus_services' => $bus_services,
        'upcoming_schedules' => $upcoming_schedules,
        'charts' => [
            'monthly_revenue' => $monthly_revenue,
            'popular_routes' => $popular_routes
        ]
    ], 'Dashboard data retrieved successfully.');

} catch (PDOException $e) {
    send_json_response(false, null, 'Database error: ' . $e->getMessage());
} catch (Exception $e) {
    send_json_response(false, null, $e->getMessage());
} finally {
    // Close connection
    $conn = null;
}
?>
