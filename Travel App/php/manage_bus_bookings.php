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

// Get request data
$data = json_decode(file_get_contents('php://input'), true);
$action = isset($_GET['action']) ? $_GET['action'] : '';

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
    
    switch ($action) {
        case 'get_bookings':
            // Get all bookings for this operator's bus services
            $stmt = $conn->prepare("
                SELECT 
                    bb.booking_id,
                    bb.user_id,
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
                ORDER BY bb.created_at DESC
            ");
            $stmt->execute([$operator_id]);
            
            $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
            send_json_response(true, $bookings, 'Bookings retrieved successfully');
            break;
            
        case 'update_status':
            // Validate required parameters
            if (!isset($data['booking_id']) || !isset($data['status'])) {
                send_json_response(false, null, "Missing booking_id or status");
            }
            
            // Check if booking belongs to this operator's bus service
            $check = $conn->prepare("
                SELECT bb.booking_id 
                FROM BusBookings bb
                JOIN BusServices bs ON bb.bus_service_id = bs.bus_service_id
                WHERE bb.booking_id = ? AND bs.operator_id = ?
            ");
            $check->execute([$data['booking_id'], $operator_id]);
            
            if ($check->rowCount() === 0) {
                send_json_response(false, null, "Booking not found or you don't have permission to update it");
            }
            
            // Validate status
            $validStatuses = ['pending', 'confirmed', 'cancelled'];
            if (!in_array($data['status'], $validStatuses)) {
                send_json_response(false, null, "Invalid status. Must be one of: " . implode(', ', $validStatuses));
            }
            
            // Update booking status
            $stmt = $conn->prepare("UPDATE BusBookings SET booking_status = ? WHERE booking_id = ?");
            
            if (!$stmt->execute([$data['status'], $data['booking_id']])) {
                throw new Exception('Failed to update booking status');
            }
            
            // Return success response
            send_json_response(true, null, 'Booking status updated successfully');
            break;
            
        case 'get_booking_details':
            // Validate required parameters
            if (!isset($data['booking_id'])) {
                send_json_response(false, null, "Missing booking_id");
            }
            
            // Check if booking belongs to this operator's bus service
            $stmt = $conn->prepare("
                SELECT 
                    bb.booking_id,
                    bb.user_id,
                    bb.journey_date,
                    bb.num_seats,
                    bb.seat_numbers,
                    bb.total_amount,
                    bb.booking_status,
                    bb.created_at,
                    bs.bus_service_id,
                    bs.bus_name,
                    bs.route,
                    bs.bus_type,
                    bs.price
                FROM BusBookings bb
                JOIN BusServices bs ON bb.bus_service_id = bs.bus_service_id
                WHERE bb.booking_id = ? AND bs.operator_id = ?
            ");
            $stmt->execute([$data['booking_id'], $operator_id]);
            
            if ($stmt->rowCount() === 0) {
                send_json_response(false, null, "Booking not found or you don't have permission to view it");
            }
            
            $booking = $stmt->fetch(PDO::FETCH_ASSOC);
            send_json_response(true, $booking, 'Booking details retrieved successfully');
            break;
            
        case 'get_booking_stats':
            // Get booking statistics for this operator
            
            // Total bookings count
            $stmt = $conn->prepare("
                SELECT COUNT(*) as total_bookings
                FROM BusBookings bb
                JOIN BusServices bs ON bb.bus_service_id = bs.bus_service_id
                WHERE bs.operator_id = ?
            ");
            $stmt->execute([$operator_id]);
            $totalBookings = $stmt->fetch(PDO::FETCH_ASSOC)['total_bookings'];
            
            // Confirmed bookings count
            $stmt = $conn->prepare("
                SELECT COUNT(*) as confirmed_bookings
                FROM BusBookings bb
                JOIN BusServices bs ON bb.bus_service_id = bs.bus_service_id
                WHERE bs.operator_id = ? AND bb.booking_status = 'confirmed'
            ");
            $stmt->execute([$operator_id]);
            $confirmedBookings = $stmt->fetch(PDO::FETCH_ASSOC)['confirmed_bookings'];
            
            // Pending bookings count
            $stmt = $conn->prepare("
                SELECT COUNT(*) as pending_bookings
                FROM BusBookings bb
                JOIN BusServices bs ON bb.bus_service_id = bs.bus_service_id
                WHERE bs.operator_id = ? AND bb.booking_status = 'pending'
            ");
            $stmt->execute([$operator_id]);
            $pendingBookings = $stmt->fetch(PDO::FETCH_ASSOC)['pending_bookings'];
            
            // Cancelled bookings count
            $stmt = $conn->prepare("
                SELECT COUNT(*) as cancelled_bookings
                FROM BusBookings bb
                JOIN BusServices bs ON bb.bus_service_id = bs.bus_service_id
                WHERE bs.operator_id = ? AND bb.booking_status = 'cancelled'
            ");
            $stmt->execute([$operator_id]);
            $cancelledBookings = $stmt->fetch(PDO::FETCH_ASSOC)['cancelled_bookings'];
            
            // Total revenue
            $stmt = $conn->prepare("
                SELECT SUM(bb.total_amount) as total_revenue
                FROM BusBookings bb
                JOIN BusServices bs ON bb.bus_service_id = bs.bus_service_id
                WHERE bs.operator_id = ? AND bb.booking_status = 'confirmed'
            ");
            $stmt->execute([$operator_id]);
            $totalRevenue = $stmt->fetch(PDO::FETCH_ASSOC)['total_revenue'] ?? 0;
            
            // Return statistics
            send_json_response(true, [
                'total_bookings' => $totalBookings,
                'confirmed_bookings' => $confirmedBookings,
                'pending_bookings' => $pendingBookings,
                'cancelled_bookings' => $cancelledBookings,
                'total_revenue' => $totalRevenue
            ], 'Booking statistics retrieved successfully');
            break;
            
        case 'get_booking_chart_data':
            // Get booking data for chart (last 7 days)
            $chartData = [
                'labels' => [],
                'confirmed' => [],
                'pending' => [],
                'cancelled' => []
            ];
            
            // Get the last 7 days
            for ($i = 6; $i >= 0; $i--) {
                $date = date('Y-m-d', strtotime("-$i days"));
                $chartData['labels'][] = date('D', strtotime($date)); // Day name (Mon, Tue, etc.)
                
                // Confirmed bookings for this day
                $stmt = $conn->prepare("
                    SELECT COUNT(*) as count
                    FROM BusBookings bb
                    JOIN BusServices bs ON bb.bus_service_id = bs.bus_service_id
                    WHERE bs.operator_id = ? AND bb.booking_status = 'confirmed' AND DATE(bb.created_at) = ?
                ");
                $stmt->execute([$operator_id, $date]);
                $chartData['confirmed'][] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
                
                // Pending bookings for this day
                $stmt = $conn->prepare("
                    SELECT COUNT(*) as count
                    FROM BusBookings bb
                    JOIN BusServices bs ON bb.bus_service_id = bs.bus_service_id
                    WHERE bs.operator_id = ? AND bb.booking_status = 'pending' AND DATE(bb.created_at) = ?
                ");
                $stmt->execute([$operator_id, $date]);
                $chartData['pending'][] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
                
                // Cancelled bookings for this day
                $stmt = $conn->prepare("
                    SELECT COUNT(*) as count
                    FROM BusBookings bb
                    JOIN BusServices bs ON bb.bus_service_id = bs.bus_service_id
                    WHERE bs.operator_id = ? AND bb.booking_status = 'cancelled' AND DATE(bb.created_at) = ?
                ");
                $stmt->execute([$operator_id, $date]);
                $chartData['cancelled'][] = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
            }
            
            send_json_response(true, $chartData, 'Booking chart data retrieved successfully');
            break;
            
        case 'get_revenue_chart_data':
            // Get revenue data for chart (last 4 weeks)
            $chartData = [
                'labels' => [],
                'revenue' => []
            ];
            
            // Get the last 4 weeks
            for ($i = 3; $i >= 0; $i--) {
                $startDate = date('Y-m-d', strtotime("-" . ($i * 7 + 6) . " days"));
                $endDate = date('Y-m-d', strtotime("-" . ($i * 7) . " days"));
                $chartData['labels'][] = 'Week ' . (4 - $i);
                
                // Revenue for this week
                $stmt = $conn->prepare("
                    SELECT SUM(bb.total_amount) as revenue
                    FROM BusBookings bb
                    JOIN BusServices bs ON bb.bus_service_id = bs.bus_service_id
                    WHERE bs.operator_id = ? AND bb.booking_status = 'confirmed' 
                    AND DATE(bb.created_at) BETWEEN ? AND ?
                ");
                $stmt->execute([$operator_id, $startDate, $endDate]);
                $revenue = $stmt->fetch(PDO::FETCH_ASSOC)['revenue'] ?? 0;
                $chartData['revenue'][] = $revenue;
            }
            
            send_json_response(true, $chartData, 'Revenue chart data retrieved successfully');
            break;
            
        case 'get_popular_routes':
            // Get popular routes data
            $stmt = $conn->prepare("
                SELECT bs.route, COUNT(bb.booking_id) as booking_count
                FROM BusBookings bb
                JOIN BusServices bs ON bb.bus_service_id = bs.bus_service_id
                WHERE bs.operator_id = ?
                GROUP BY bs.route
                ORDER BY booking_count DESC
                LIMIT 5
            ");
            $stmt->execute([$operator_id]);
            $routes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $chartData = [
                'routes' => [],
                'bookings' => []
            ];
            
            foreach ($routes as $route) {
                $chartData['routes'][] = $route['route'];
                $chartData['bookings'][] = $route['booking_count'];
            }
            
            send_json_response(true, $chartData, 'Popular routes data retrieved successfully');
            break;
            
        case 'generate_report':
            // Validate required parameters
            if (!isset($data['from_date']) || !isset($data['to_date']) || !isset($data['report_type'])) {
                send_json_response(false, null, "Missing required parameters");
            }
            
            $fromDate = $data['from_date'];
            $toDate = $data['to_date'];
            $reportType = $data['report_type'];
            
            // Validate dates
            if (strtotime($fromDate) > strtotime($toDate)) {
                send_json_response(false, null, "From date cannot be after to date");
            }
            
            $reportData = [];
            
            switch ($reportType) {
                case 'bookings':
                    // Generate bookings report
                    $stmt = $conn->prepare("
                        SELECT 
                            DATE(bb.created_at) as date,
                            bs.bus_name,
                            bs.route,
                            COUNT(bb.booking_id) as bookings,
                            bb.booking_status as status
                        FROM BusBookings bb
                        JOIN BusServices bs ON bb.bus_service_id = bs.bus_service_id
                        WHERE bs.operator_id = ? AND DATE(bb.created_at) BETWEEN ? AND ?
                        GROUP BY date, bs.bus_name, bs.route, bb.booking_status
                        ORDER BY date DESC, bs.bus_name
                    ");
                    $stmt->execute([$operator_id, $fromDate, $toDate]);
                    $reportData = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    break;
                    
                case 'revenue':
                    // Generate revenue report
                    $stmt = $conn->prepare("
                        SELECT 
                            DATE(bb.created_at) as date,
                            bs.bus_name,
                            bs.route,
                            SUM(bb.total_amount) as revenue
                        FROM BusBookings bb
                        JOIN BusServices bs ON bb.bus_service_id = bs.bus_service_id
                        WHERE bs.operator_id = ? AND DATE(bb.created_at) BETWEEN ? AND ? AND bb.booking_status = 'confirmed'
                        GROUP BY date, bs.bus_name, bs.route
                        ORDER BY date DESC, bs.bus_name
                    ");
                    $stmt->execute([$operator_id, $fromDate, $toDate]);
                    $reportData = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    break;
                    
                case 'occupancy':
                    // Generate occupancy report
                    $stmt = $conn->prepare("
                        SELECT 
                            DATE(bb.journey_date) as date,
                            bs.bus_name,
                            bs.route,
                            bs.total_seats as capacity,
                            SUM(bb.num_seats) as booked,
                            ROUND((SUM(bb.num_seats) / bs.total_seats) * 100, 2) as occupancy_percentage
                        FROM BusBookings bb
                        JOIN BusServices bs ON bb.bus_service_id = bs.bus_service_id
                        WHERE bs.operator_id = ? AND DATE(bb.journey_date) BETWEEN ? AND ? AND bb.booking_status = 'confirmed'
                        GROUP BY date, bs.bus_name, bs.route, bs.total_seats
                        ORDER BY date DESC, bs.bus_name
                    ");
                    $stmt->execute([$operator_id, $fromDate, $toDate]);
                    $reportData = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    break;
                    
                case 'cancellations':
                    // Generate cancellations report
                    $stmt = $conn->prepare("
                        SELECT 
                            DATE(bb.created_at) as date,
                            bs.bus_name,
                            bs.route,
                            COUNT(bb.booking_id) as cancellations,
                            'User cancelled' as reason
                        FROM BusBookings bb
                        JOIN BusServices bs ON bb.bus_service_id = bs.bus_service_id
                        WHERE bs.operator_id = ? AND DATE(bb.created_at) BETWEEN ? AND ? AND bb.booking_status = 'cancelled'
                        GROUP BY date, bs.bus_name, bs.route
                        ORDER BY date DESC, bs.bus_name
                    ");
                    $stmt->execute([$operator_id, $fromDate, $toDate]);
                    $reportData = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    break;
                    
                default:
                    send_json_response(false, null, "Invalid report type");
            }
            
            send_json_response(true, $reportData, 'Report generated successfully');
            break;
            
        default:
            send_json_response(false, null, 'Invalid action');
    }
    
} catch (PDOException $e) {
    send_json_response(false, null, 'Database error: ' . $e->getMessage());
} catch (Exception $e) {
    send_json_response(false, null, $e->getMessage());
} finally {
    // Close connection
    $conn = null;
}
?>
