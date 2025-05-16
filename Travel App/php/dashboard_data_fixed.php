<?php
// Set headers to allow cross-origin requests
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

// Include database connection
include 'config.php';

// Function to get dashboard data (stats and recent bookings)
function getDashboardData() {
    global $conn;
    
    $response = [
        'success' => true,
        'stats' => [
            'activeBookings' => 0,
            'totalClients' => 0,
            'revenue' => 0
        ],
        'bookings' => []
    ];
    
    try {
        // Check if this is a demo agent
        $isDemo = isset($_REQUEST['demo']) && $_REQUEST['demo'] === 'true';
        
        if ($isDemo) {
            // Return mock data for demo
            $response['stats'] = [
                'activeBookings' => 12,
                'totalClients' => 28,
                'revenue' => 15750
            ];
            
            $response['bookings'] = [
                [
                    'bookingId' => 12345,
                    'clientName' => 'Smita Dhungel',
                    'email' => 'smita@gmail.com',
                    'service' => 'Everest Base Camp Trek',
                    'dates' => 'May 5 - May 15, 2023',
                    'checkInDate' => '2023-05-05',
                    'checkOutDate' => '2023-05-15',
                    'guests' => 2,
                    'amount' => 1200,
                    'status' => 'confirmed',
                    'createdAt' => '2023-04-10 10:30:00'
                ],
                [
                    'bookingId' => 12346,
                    'clientName' => 'Rajesh Sharma',
                    'email' => 'rajesh@example.com',
                    'service' => 'Annapurna Circuit',
                    'dates' => 'June 10 - June 25, 2023',
                    'checkInDate' => '2023-06-10',
                    'checkOutDate' => '2023-06-25',
                    'guests' => 1,
                    'amount' => 950,
                    'status' => 'pending',
                    'createdAt' => '2023-05-01 14:45:00'
                ],
                [
                    'bookingId' => 12347,
                    'clientName' => 'Sarah Johnson',
                    'email' => 'sarah@example.com',
                    'service' => 'Langtang Valley Trek',
                    'dates' => 'July 3 - July 10, 2023',
                    'checkInDate' => '2023-07-03',
                    'checkOutDate' => '2023-07-10',
                    'guests' => 3,
                    'amount' => 1500,
                    'status' => 'confirmed',
                    'createdAt' => '2023-06-15 09:20:00'
                ]
            ];
            
            return $response;
        }
        
        // For regular agents, get data from database
        if (!$conn) {
            throw new Exception('Database connection failed');
        }
        
        // Get active bookings count
        $activeBookingsQuery = "SELECT COUNT(*) as count FROM Bookings WHERE booking_status = 'confirmed'";
        $stmt = $conn->prepare($activeBookingsQuery);
        if (!$stmt) {
            throw new Exception("Database error: " . $conn->error);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $response['stats']['activeBookings'] = $result->fetch_assoc()['count'] ?? 0;
        
        // Get total clients (unique guests)
        $totalClientsQuery = "SELECT COUNT(DISTINCT guest_email) as count FROM Bookings";
        $stmt = $conn->prepare($totalClientsQuery);
        if (!$stmt) {
            throw new Exception("Database error: " . $conn->error);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $response['stats']['totalClients'] = $result->fetch_assoc()['count'] ?? 0;
        
        // Get revenue (last 30 days)
        $revenueQuery = "SELECT SUM(total_amount) as revenue FROM Bookings 
                        WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
        $stmt = $conn->prepare($revenueQuery);
        if (!$stmt) {
            throw new Exception("Database error: " . $conn->error);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $revenueResult = $result->fetch_assoc();
        $response['stats']['revenue'] = $revenueResult['revenue'] ? floatval($revenueResult['revenue']) : 0;
        
        // Get recent bookings - FIXED: removed reference to listing_name
        $bookingsQuery = "SELECT * 
                FROM Bookings
                ORDER BY created_at DESC
                LIMIT 10";
        
        $stmt = $conn->prepare($bookingsQuery);
        if (!$stmt) {
            throw new Exception("Database error: " . $conn->error);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $bookings = [];
        while ($row = $result->fetch_assoc()) {
            // Format dates for display
            $checkInDate = new DateTime($row['check_in_date']);
            $checkOutDate = new DateTime($row['check_out_date']);
            $formattedDates = $checkInDate->format('M d, Y') . ' - ' . $checkOutDate->format('M d, Y');
            
            // Create booking object with the correct field names for the frontend
            // FIXED: removed reference to listing_name
            $bookings[] = [
                'bookingId' => $row['booking_id'],
                'clientName' => $row['guest_name'],
                'email' => $row['guest_email'],
                'service' => 'Tour Package', // Default service name since listing_name is not available
                'dates' => $formattedDates,
                'checkInDate' => $row['check_in_date'],
                'checkOutDate' => $row['check_out_date'],
                'guests' => $row['number_of_guests'],
                'amount' => $row['total_amount'],
                'status' => $row['booking_status'],
                'createdAt' => $row['created_at']
            ];
        }
        
        $response['bookings'] = $bookings;
        
    } catch (Exception $e) {
        $response = [
            'success' => false,
            'message' => 'Error retrieving dashboard data: ' . $e->getMessage()
        ];
    }
    
    return $response;
}

// Function to get booking details
function getBookingDetails($bookingId) {
    global $conn;
    
    $response = [
        'success' => false,
        'message' => 'Booking not found'
    ];
    
    try {
        // Check if this is a demo booking
        $isDemo = isset($_REQUEST['demo']) && $_REQUEST['demo'] === 'true';
        
        if ($isDemo) {
            // Create a mock booking based on the booking ID
            $mockBooking = [
                'bookingId' => $bookingId,
                'clientName' => $bookingId == 12345 ? 'Smita Dhungel' : 
                               ($bookingId == 12346 ? 'Rajesh Sharma' : 'Sarah Johnson'),
                'email' => $bookingId == 12345 ? 'smita@gmail.com' : 
                          ($bookingId == 12346 ? 'rajesh@example.com' : 'sarah@example.com'),
                'service' => $bookingId == 12345 ? 'Everest Base Camp Trek' : 
                            ($bookingId == 12346 ? 'Annapurna Circuit' : 'Langtang Valley Trek'),
                'dates' => $bookingId == 12345 ? 'May 5 - May 15, 2023' : 
                           ($bookingId == 12346 ? 'June 10 - June 25, 2023' : 'July 3 - July 10, 2023'),
                'checkInDate' => $bookingId == 12345 ? '2023-05-05' : 
                                ($bookingId == 12346 ? '2023-06-10' : '2023-07-03'),
                'checkOutDate' => $bookingId == 12345 ? '2023-05-15' : 
                                 ($bookingId == 12346 ? '2023-06-25' : '2023-07-10'),
                'guests' => $bookingId == 12345 ? 2 : 
                           ($bookingId == 12346 ? 1 : 3),
                'amount' => $bookingId == 12345 ? 1200 : 
                           ($bookingId == 12346 ? 950 : 1500),
                'status' => $bookingId == 12345 || $bookingId == 12347 ? 'confirmed' : 'pending',
                'createdAt' => $bookingId == 12345 ? '2023-04-10 10:30:00' : 
                              ($bookingId == 12346 ? '2023-05-01 14:45:00' : '2023-06-15 09:20:00')
            ];
            
            return [
                'success' => true,
                'booking' => $mockBooking
            ];
        }
        
        // For regular bookings, get data from database
        if (!$conn) {
            throw new Exception('Database connection failed');
        }
        
        // Get booking details - FIXED: removed reference to listing_name
        $sql = "SELECT * 
                FROM Bookings
                WHERE booking_id = ?";
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Database error: " . $conn->error);
        }
        
        $stmt->bind_param("i", $bookingId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();
            
            // Format dates for display
            $checkInDate = new DateTime($row['check_in_date']);
            $checkOutDate = new DateTime($row['check_out_date']);
            $formattedDates = $checkInDate->format('M d, Y') . ' - ' . $checkOutDate->format('M d, Y');
            
            // Create booking object with the correct field names for the frontend
            // FIXED: removed reference to listing_name
            $booking = [
                'bookingId' => $row['booking_id'],
                'clientName' => $row['guest_name'],
                'email' => $row['guest_email'],
                'service' => 'Tour Package', // Default service name since listing_name is not available
                'dates' => $formattedDates,
                'checkInDate' => $row['check_in_date'],
                'checkOutDate' => $row['check_out_date'],
                'guests' => $row['number_of_guests'],
                'amount' => $row['total_amount'],
                'status' => $row['booking_status'],
                'createdAt' => $row['created_at']
            ];
            
            $response = [
                'success' => true,
                'booking' => $booking
            ];
        }
        
    } catch (Exception $e) {
        $response = [
            'success' => false,
            'message' => 'Error retrieving booking details: ' . $e->getMessage()
        ];
    }
    
    return $response;
}

// Function to search bookings
function searchBookings($searchTerm) {
    global $conn;
    
    $response = [
        'success' => false,
        'message' => 'No bookings found',
        'bookings' => []
    ];
    
    try {
        // Check if this is a demo search
        $isDemo = isset($_REQUEST['demo']) && $_REQUEST['demo'] === 'true';
        
        if ($isDemo) {
            // Filter mock bookings based on search term
            $mockBookings = [
                [
                    'bookingId' => 12345,
                    'clientName' => 'Smita Dhungel',
                    'email' => 'smita@gmail.com',
                    'service' => 'Everest Base Camp Trek',
                    'dates' => 'May 5 - May 15, 2023',
                    'checkInDate' => '2023-05-05',
                    'checkOutDate' => '2023-05-15',
                    'guests' => 2,
                    'amount' => 1200,
                    'status' => 'confirmed',
                    'createdAt' => '2023-04-10 10:30:00'
                ],
                [
                    'bookingId' => 12346,
                    'clientName' => 'Rajesh Sharma',
                    'email' => 'rajesh@example.com',
                    'service' => 'Annapurna Circuit',
                    'dates' => 'June 10 - June 25, 2023',
                    'checkInDate' => '2023-06-10',
                    'checkOutDate' => '2023-06-25',
                    'guests' => 1,
                    'amount' => 950,
                    'status' => 'pending',
                    'createdAt' => '2023-05-01 14:45:00'
                ],
                [
                    'bookingId' => 12347,
                    'clientName' => 'Sarah Johnson',
                    'email' => 'sarah@example.com',
                    'service' => 'Langtang Valley Trek',
                    'dates' => 'July 3 - July 10, 2023',
                    'checkInDate' => '2023-07-03',
                    'checkOutDate' => '2023-07-10',
                    'guests' => 3,
                    'amount' => 1500,
                    'status' => 'confirmed',
                    'createdAt' => '2023-06-15 09:20:00'
                ]
            ];
            
            // Filter bookings based on search term
            if (!empty($searchTerm)) {
                $filteredBookings = [];
                $searchTerm = strtolower($searchTerm);
                
                foreach ($mockBookings as $booking) {
                    if (strpos(strtolower($booking['clientName']), $searchTerm) !== false || 
                        strpos(strtolower($booking['email']), $searchTerm) !== false ||
                        strpos(strtolower($booking['service']), $searchTerm) !== false) {
                        $filteredBookings[] = $booking;
                    }
                }
                
                $mockBookings = $filteredBookings;
            }
            
            return [
                'success' => true,
                'bookings' => $mockBookings
            ];
        }
        
        // For regular searches, get data from database
        if (!$conn) {
            throw new Exception('Database connection failed');
        }
        
        // Prepare search query - search by guest name or email
        // FIXED: removed reference to listing_name
        $sql = "SELECT * 
                FROM Bookings";
        
        // Add search condition if search term is provided
        if (!empty($searchTerm)) {
            $sql .= " WHERE guest_name LIKE ? OR guest_email LIKE ?";
        }
        
        $sql .= " ORDER BY created_at DESC LIMIT 20";
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Database error: " . $conn->error);
        }
        
        // Bind parameters if search term is provided
        if (!empty($searchTerm)) {
            $searchParam = "%$searchTerm%";
            $stmt->bind_param("ss", $searchParam, $searchParam);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        $bookings = [];
        while ($row = $result->fetch_assoc()) {
            // Format dates for display
            $checkInDate = new DateTime($row['check_in_date']);
            $checkOutDate = new DateTime($row['check_out_date']);
            $formattedDates = $checkInDate->format('M d, Y') . ' - ' . $checkOutDate->format('M d, Y');
            
            // Create booking object with the correct field names for the frontend
            // FIXED: removed reference to listing_name
            $bookings[] = [
                'bookingId' => $row['booking_id'],
                'clientName' => $row['guest_name'],
                'email' => $row['guest_email'],
                'service' => 'Tour Package', // Default service name since listing_name is not available
                'dates' => $formattedDates,
                'checkInDate' => $row['check_in_date'],
                'checkOutDate' => $row['check_out_date'],
                'guests' => $row['number_of_guests'],
                'amount' => $row['total_amount'],
                'status' => $row['booking_status'],
                'createdAt' => $row['created_at']
            ];
        }
        
        $response = [
            'success' => true,
            'bookings' => $bookings
        ];
        
    } catch (Exception $e) {
        $response = [
            'success' => false,
            'message' => 'Error searching bookings: ' . $e->getMessage()
        ];
    }
    
    return $response;
}

// Function to update booking
function updateBooking($data) {
    global $conn;
    
    $response = [
        'success' => false,
        'message' => 'Failed to update booking'
    ];
    
    try {
        // Extract booking data
        $bookingId = isset($data['bookingId']) ? intval($data['bookingId']) : 0;
        $clientName = isset($data['clientName']) ? $data['clientName'] : '';
        $email = isset($data['email']) ? $data['email'] : '';
        $status = isset($data['status']) ? $data['status'] : '';
        $checkInDate = isset($data['checkInDate']) ? $data['checkInDate'] : null;
        $checkOutDate = isset($data['checkOutDate']) ? $data['checkOutDate'] : null;
        $guests = isset($data['guests']) ? intval($data['guests']) : null;
        
        // Validate required fields
        if (empty($bookingId) || empty($clientName) || empty($email) || empty($status)) {
            throw new Exception('Required fields are missing');
        }
        
        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception('Invalid email format');
        }
        
        // Validate status
        $validStatuses = ['confirmed', 'pending', 'cancelled'];
        if (!in_array($status, $validStatuses)) {
            throw new Exception('Invalid status value');
        }
        
        // Check if this is a demo update
        $isDemo = isset($_REQUEST['demo']) && $_REQUEST['demo'] === 'true';
        
        if ($isDemo) {
            // Create a mock updated booking
            $mockBooking = [
                'bookingId' => $bookingId,
                'clientName' => $clientName,
                'email' => $email,
                'service' => $bookingId == 12345 ? 'Everest Base Camp Trek' : 
                            ($bookingId == 12346 ? 'Annapurna Circuit' : 'Langtang Valley Trek'),
                'dates' => $checkInDate && $checkOutDate ? 
                           (new DateTime($checkInDate))->format('M d, Y') . ' - ' . 
                           (new DateTime($checkOutDate))->format('M d, Y') : 
                           ($bookingId == 12345 ? 'May 5 - May 15, 2023' : 
                           ($bookingId == 12346 ? 'June 10 - June 25, 2023' : 'July 3 - July 10, 2023')),
                'checkInDate' => $checkInDate ?: ($bookingId == 12345 ? '2023-05-05' : 
                                ($bookingId == 12346 ? '2023-06-10' : '2023-07-03')),
                'checkOutDate' => $checkOutDate ?: ($bookingId == 12345 ? '2023-05-15' : 
                                 ($bookingId == 12346 ? '2023-06-25' : '2023-07-10')),
                'guests' => $guests ?: ($bookingId == 12345 ? 2 : 
                           ($bookingId == 12346 ? 1 : 3)),
                'amount' => $bookingId == 12345 ? 1200 : 
                           ($bookingId == 12346 ? 950 : 1500),
                'status' => $status,
                'createdAt' => $bookingId == 12345 ? '2023-04-10 10:30:00' : 
                              ($bookingId == 12346 ? '2023-05-01 14:45:00' : '2023-06-15 09:20:00')
            ];
            
            return [
                'success' => true,
                'message' => 'Booking updated successfully',
                'booking' => $mockBooking
            ];
        }
        
        // For regular updates, update database
        if (!$conn) {
            throw new Exception('Database connection failed');
        }
        
        // Update booking in database
        $sql = "UPDATE Bookings SET 
                guest_name = ?, 
                guest_email = ?, 
                booking_status = ?";
        
        $params = [$clientName, $email, $status];
        $types = "sss";
        
        // Add optional parameters if provided
        if ($checkInDate) {
            $sql .= ", check_in_date = ?";
            $params[] = $checkInDate;
            $types .= "s";
        }
        
        if ($checkOutDate) {
            $sql .= ", check_out_date = ?";
            $params[] = $checkOutDate;
            $types .= "s";
        }
        
        if ($guests) {
            $sql .= ", number_of_guests = ?";
            $params[] = $guests;
            $types .= "i";
        }
        
        $sql .= " WHERE booking_id = ?";
        $params[] = $bookingId;
        $types .= "i";
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Database error: " . $conn->error);
        }
        
        $stmt->bind_param($types, ...$params);
        $result = $stmt->execute();
        
        if ($result) {
            // Get updated booking details
            // FIXED: removed reference to listing_name
            $sql = "SELECT * 
                    FROM Bookings
                    WHERE booking_id = ?";
            
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Database error on select: " . $conn->error);
            }
            
            $stmt->bind_param("i", $bookingId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $row = $result->fetch_assoc();
                
                // Format dates for display
                $checkInDate = new DateTime($row['check_in_date']);
                $checkOutDate = new DateTime($row['check_out_date']);
                $formattedDates = $checkInDate->format('M d, Y') . ' - ' . $checkOutDate->format('M d, Y');
                
                // Create booking object with the correct field names for the frontend
                // FIXED: removed reference to listing_name
                $booking = [
                    'bookingId' => $row['booking_id'],
                    'clientName' => $row['guest_name'],
                    'email' => $row['guest_email'],
                    'service' => 'Tour Package', // Default service name since listing_name is not available
                    'dates' => $formattedDates,
                    'checkInDate' => $row['check_in_date'],
                    'checkOutDate' => $row['check_out_date'],
                    'guests' => $row['number_of_guests'],
                    'amount' => $row['total_amount'],
                    'status' => $row['booking_status'],
                    'createdAt' => $row['created_at']
                ];
                
                $response = [
                    'success' => true,
                    'message' => 'Booking updated successfully',
                    'booking' => $booking
                ];
            } else {
                $response = [
                    'success' => true,
                    'message' => 'Booking updated successfully'
                ];
            }
        }
        
    } catch (Exception $e) {
        $response = [
            'success' => false,
            'message' => 'Error updating booking: ' . $e->getMessage()
        ];
    }
    
    return $response;
}

// Handle the request
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

switch ($action) {
    case 'getDashboardData':
        echo json_encode(getDashboardData());
        break;
    
    case 'getBookingDetails':
        $bookingId = isset($_REQUEST['bookingId']) ? intval($_REQUEST['bookingId']) : 0;
        echo json_encode(getBookingDetails($bookingId));
        break;
    
    case 'searchBookings':
        $searchTerm = isset($_REQUEST['query']) ? $_REQUEST['query'] : '';
        echo json_encode(searchBookings($searchTerm));
        break;
    
    case 'updateBooking':
        echo json_encode(updateBooking($_POST));
        break;
    
    default:
        echo json_encode([
            'success' => false,
            'message' => 'Invalid action'
        ]);
}
?>
