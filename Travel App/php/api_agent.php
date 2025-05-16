<?php
// Ensure no output before headers
ob_start();

// Set headers for JSON response
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET');
header('Access-Control-Allow-Headers: Content-Type');

// Register shutdown function to ensure valid JSON is always returned
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null) {
        // Clear any output
        ob_clean();
        
        // Return a valid JSON error response
        echo json_encode([
            'success' => false,
            'message' => 'A server error occurred'
        ]);
    }
    ob_end_flush();
});

// Include database connection
include 'config.php';

// Get request action
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

// Handle API requests
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    
    switch ($action) {
        case 'login':
            handleLogin();
            break;
        case 'register':
            handleRegister();
            break;
        case 'getBookings':
            getBookings();
            break;
        case 'searchBookings':
            searchBookings();
            break;
        case 'getBookingDetails':
            getBookingDetails();
            break;
        case 'getAgentStats':
            getAgentStats();
            break;
        case 'updateBooking':
            updateBooking();
            break;
        case 'getListings':
            getListings();
            break;
        case 'createBooking':
            createBooking();
            break;
        case 'getClientList':
            getClientList();
            break;
        case 'getReportsData':
            getReportsData();
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
}

// Handle login request
function handleLogin() {
    // Get POST data
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    
    // For demo purposes, always allow login with demo credentials
    if (strpos(strtolower($email), 'demo') !== false || $email === 'agent@example.com') {
        $mockAgent = [
            'id' => 999,
            'fullName' => 'Demo Agent',
            'email' => $email,
            'agencyName' => 'Demo Travel Agency',
            'agencyId' => 'DEMO123'
        ];
        $response = ['success' => true, 'agent' => $mockAgent, 'demo' => true];
        echo json_encode($response);
        return;
    }
    
    // For non-demo users, check if credentials are empty
    if (empty($email) || empty($password)) {
        $response = ['success' => false, 'message' => 'Email and password are required'];
        echo json_encode($response);
        return;
    }
    
    // For all other users, return a fallback response
    // In a real app, this would check the database
    $response = ['success' => false, 'message' => 'Invalid email or password. Try using demo@example.com for testing.'];
    echo json_encode($response);
}

// Handle registration request
function handleRegister() {
    global $conn;
    
    // Get POST data
    $fullName = isset($_POST['fullName']) ? $_POST['fullName'] : '';
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $phone = isset($_POST['phone']) ? $_POST['phone'] : '';
    $agencyName = isset($_POST['agencyName']) ? $_POST['agencyName'] : '';
    $agencyId = isset($_POST['agencyId']) ? $_POST['agencyId'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    
    if (empty($fullName) || empty($email) || empty($phone) || empty($agencyName) || empty($agencyId) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required']);
        return;
    }
    
    // Check if email already exists
    $check_stmt = $conn->prepare("SELECT id FROM agents WHERE email = ?");
    $check_stmt->bind_param("s", $email);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Email already exists']);
        return;
    }
    
    // Prepare SQL statement for insertion
    $stmt = $conn->prepare("INSERT INTO agents (fullName, email, phone, agencyName, agencyId, password) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $fullName, $email, $phone, $agencyName, $agencyId, $password);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Registration successful']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Registration failed: ' . $conn->error]);
    }
}

// Get bookings for an agent
function getBookings() {
    global $conn;
    
    // Get agent ID from request
    $agentId = isset($_REQUEST['agentId']) ? intval($_REQUEST['agentId']) : 0;
    
    // Check if this is a demo agent
    $isDemo = ($agentId === 999);
    
    // For demo agent, return mock data
    if ($isDemo) {
        // Create mock bookings data
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
        
        echo json_encode(['success' => true, 'bookings' => $mockBookings]);
        return;
    }
    
    // For regular agents, check database connection
    if (!$conn) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        return;
    }
    
    try {
        // Query from the Bookings table with the new structure
        $query = "SELECT b.*, l.listing_name 
                FROM Bookings b
                LEFT JOIN Listings l ON b.listing_id = l.listing_id
                ORDER BY b.created_at DESC
                LIMIT 10";
        
        $stmt = $conn->prepare($query);
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
            $bookings[] = [
                'bookingId' => $row['booking_id'],
                'clientName' => $row['guest_name'],
                'email' => $row['guest_email'],
                'service' => $row['listing_name'] ?? 'Tour Package',
                'dates' => $formattedDates,
                'checkInDate' => $row['check_in_date'],
                'checkOutDate' => $row['check_out_date'],
                'guests' => $row['number_of_guests'],
                'amount' => $row['total_amount'],
                'status' => $row['booking_status'],
                'createdAt' => $row['created_at']
            ];
        }
        
        echo json_encode(['success' => true, 'bookings' => $bookings]);
    } catch (Exception $e) {
        // Log the error for server-side debugging
        error_log('Error in getBookings: ' . $e->getMessage());
        
        // Return a user-friendly error message
        echo json_encode([
            'success' => false, 
            'message' => 'Error retrieving bookings: ' . $e->getMessage()
        ]);
    }
}

// Search bookings
function searchBookings() {
    global $conn;
    
    // Get search term from request
    $searchTerm = isset($_REQUEST['query']) ? $_REQUEST['query'] : '';
    
    // Check database connection
    if (!$conn) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        return;
    }
    
    try {
        // Prepare search query - search by guest name or email
        $sql = "SELECT b.*, l.listing_name 
                FROM Bookings b
                LEFT JOIN Listings l ON b.listing_id = l.listing_id
                WHERE b.guest_name LIKE ? OR b.guest_email LIKE ?
                ORDER BY b.created_at DESC LIMIT 20";
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Database error: " . $conn->error);
        }
        
        // Add wildcards to search term
        $searchParam = "%$searchTerm%";
        $stmt->bind_param("ss", $searchParam, $searchParam);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $bookings = [];
        while ($row = $result->fetch_assoc()) {
            // Format dates for display
            $checkInDate = new DateTime($row['check_in_date']);
            $checkOutDate = new DateTime($row['check_out_date']);
            $formattedDates = $checkInDate->format('M d, Y') . ' - ' . $checkOutDate->format('M d, Y');
            
            // Create booking object with the correct field names for the frontend
            $bookings[] = [
                'bookingId' => $row['booking_id'],
                'clientName' => $row['guest_name'],
                'email' => $row['guest_email'],
                'service' => $row['listing_name'] ?? 'Tour Package',
                'dates' => $formattedDates,
                'checkInDate' => $row['check_in_date'],
                'checkOutDate' => $row['check_out_date'],
                'guests' => $row['number_of_guests'],
                'amount' => $row['total_amount'],
                'status' => $row['booking_status'],
                'createdAt' => $row['created_at']
            ];
        }
        
        // If no results and using demo agent, provide mock data
        if (empty($bookings) && (isset($_REQUEST['agentId']) && $_REQUEST['agentId'] == 999)) {
            $bookings = [
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
                ]
            ];
        }
        
        // Return the results
        echo json_encode(['success' => true, 'bookings' => $bookings]);
    } catch (Exception $e) {
        // Return the error message
        echo json_encode([
            'success' => false, 
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
}

// Get booking details
function getBookingDetails() {
    global $conn;
    
    // Get booking ID
    $bookingId = isset($_REQUEST['bookingId']) ? intval($_REQUEST['bookingId']) : 0;
    
    if (empty($bookingId)) {
        echo json_encode(['success' => false, 'message' => 'Booking ID is required']);
        return;
    }
    
    // Check database connection
    if (!$conn) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        return;
    }
    
    try {
        // Get booking details
        $sql = "SELECT b.*, l.listing_name 
                FROM Bookings b
                LEFT JOIN Listings l ON b.listing_id = l.listing_id
                WHERE b.booking_id = ?";
        
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception("Database error: " . $conn->error);
        }
        
        // Use 'i' for integer parameter (bookingId)
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
            $booking = [
                'bookingId' => $row['booking_id'],
                'clientName' => $row['guest_name'],
                'email' => $row['guest_email'],
                'service' => $row['listing_name'] ?? 'Tour Package',
                'dates' => $formattedDates,
                'checkInDate' => $row['check_in_date'],
                'checkOutDate' => $row['check_out_date'],
                'guests' => $row['number_of_guests'],
                'amount' => $row['total_amount'],
                'status' => $row['booking_status'],
                'createdAt' => $row['created_at']
            ];
            
            echo json_encode(['success' => true, 'booking' => $booking]);
        } else {
            // If using demo agent, provide mock data
            if (isset($_REQUEST['agentId']) && $_REQUEST['agentId'] == 999) {
                // Create a mock booking based on the booking ID
                $mockBooking = [
                    'bookingId' => $bookingId,
                    'clientName' => ($bookingId == 12345 ? 'Smita Dhungel' : 
                                   ($bookingId == 12346 ? 'Rajesh Sharma' : 'Sarah Johnson')),
                    'email' => ($bookingId == 12345 ? 'smita@gmail.com' : 
                              ($bookingId == 12346 ? 'rajesh@example.com' : 'sarah@example.com')),
                    'service' => ($bookingId == 12345 ? 'Everest Base Camp Trek' : 
                                ($bookingId == 12346 ? 'Annapurna Circuit' : 'Langtang Valley Trek')),
                    'dates' => ($bookingId == 12345 ? 'May 5 - May 15, 2023' : 
                               ($bookingId == 12346 ? 'June 10 - June 25, 2023' : 'July 3 - July 10, 2023')),
                    'checkInDate' => ($bookingId == 12345 ? '2023-05-05' : 
                                    ($bookingId == 12346 ? '2023-06-10' : '2023-07-03')),
                    'checkOutDate' => ($bookingId == 12345 ? '2023-05-15' : 
                                     ($bookingId == 12346 ? '2023-06-25' : '2023-07-10')),
                    'guests' => ($bookingId == 12345 ? 2 : 
                               ($bookingId == 12346 ? 1 : 3)),
                    'amount' => ($bookingId == 12345 ? 1200 : 
                               ($bookingId == 12346 ? 950 : 1500)),
                    'status' => ($bookingId == 12345 || $bookingId == 12347 ? 'confirmed' : 'pending'),
                    'createdAt' => ($bookingId == 12345 ? '2023-04-10 10:30:00' : 
                                  ($bookingId == 12346 ? '2023-05-01 14:45:00' : '2023-06-15 09:20:00'))
                ];
                
                echo json_encode(['success' => true, 'booking' => $mockBooking]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Booking not found']);
            }
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

// Update booking details
function updateBooking() {
    global $conn;
    
    // Get POST data
    $bookingId = isset($_POST['bookingId']) ? intval($_POST['bookingId']) : 0;
    $clientName = isset($_POST['clientName']) ? $_POST['clientName'] : '';
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $status = isset($_POST['status']) ? $_POST['status'] : '';
    $checkInDate = isset($_POST['checkInDate']) ? $_POST['checkInDate'] : null;
    $checkOutDate = isset($_POST['checkOutDate']) ? $_POST['checkOutDate'] : null;
    $guests = isset($_POST['guests']) ? intval($_POST['guests']) : null;
    
    // Validate required fields
    if (empty($bookingId) || empty($clientName) || empty($email) || empty($status)) {
        echo json_encode(['success' => false, 'message' => 'Required fields are missing']);
        return;
    }
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email format']);
        return;
    }
    
    // Validate status
    $validStatuses = ['confirmed', 'pending', 'cancelled'];
    if (!in_array($status, $validStatuses)) {
        echo json_encode(['success' => false, 'message' => 'Invalid status value']);
        return;
    }
    
    // Check database connection
    if (!$conn) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        return;
    }
    
    try {
        // For demo agent, just return success
        if (isset($_POST['agentId']) && $_POST['agentId'] == 999) {
            // Create a mock updated booking
            $mockBooking = [
                'bookingId' => $bookingId,
                'clientName' => $clientName,
                'email' => $email,
                'service' => ($bookingId == 12345 ? 'Everest Base Camp Trek' : 
                            ($bookingId == 12346 ? 'Annapurna Circuit' : 'Langtang Valley Trek')),
                'dates' => ($checkInDate && $checkOutDate ? 
                           (new DateTime($checkInDate))->format('M d, Y') . ' - ' . 
                           (new DateTime($checkOutDate))->format('M d, Y') : 
                           ($bookingId == 12345 ? 'May 5 - May 15, 2023' : 
                           ($bookingId == 12346 ? 'June 10 - June 25, 2023' : 'July 3 - July 10, 2023'))),
                'checkInDate' => ($checkInDate ?: ($bookingId == 12345 ? '2023-05-05' : 
                                ($bookingId == 12346 ? '2023-06-10' : '2023-07-03'))),
                'checkOutDate' => ($checkOutDate ?: ($bookingId == 12345 ? '2023-05-15' : 
                                 ($bookingId == 12346 ? '2023-06-25' : '2023-07-10'))),
                'guests' => ($guests ?: ($bookingId == 12345 ? 2 : 
                           ($bookingId == 12346 ? 1 : 3))),
                'amount' => ($bookingId == 12345 ? 1200 : 
                           ($bookingId == 12346 ? 950 : 1500)),
                'status' => $status,
                'createdAt' => ($bookingId == 12345 ? '2023-04-10 10:30:00' : 
                              ($bookingId == 12346 ? '2023-05-01 14:45:00' : '2023-06-15 09:20:00'))
            ];
            
            echo json_encode(['success' => true, 'message' => 'Booking updated successfully', 'booking' => $mockBooking]);
            return;
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
            $sql = "SELECT b.*, l.listing_name 
                    FROM Bookings b
                    LEFT JOIN Listings l ON b.listing_id = l.listing_id
                    WHERE b.booking_id = ?";
            
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
                $booking = [
                    'bookingId' => $row['booking_id'],
                    'clientName' => $row['guest_name'],
                    'email' => $row['guest_email'],
                    'service' => $row['listing_name'] ?? 'Tour Package',
                    'dates' => $formattedDates,
                    'checkInDate' => $row['check_in_date'],
                    'checkOutDate' => $row['check_out_date'],
                    'guests' => $row['number_of_guests'],
                    'amount' => $row['total_amount'],
                    'status' => $row['booking_status'],
                    'createdAt' => $row['created_at']
                ];
                
                echo json_encode(['success' => true, 'message' => 'Booking updated successfully', 'booking' => $booking]);
            } else {
                echo json_encode(['success' => true, 'message' => 'Booking updated successfully']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update booking']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

// Get agent statistics
function getAgentStats() {
    global $conn;
    
    // Get agent ID
    $agentId = isset($_REQUEST['agentId']) ? intval($_REQUEST['agentId']) : 0;
    
    // Check if this is a demo agent
    $isDemo = ($agentId === 999);
    
    // For demo agent, return mock stats
    if ($isDemo) {
        // Create mock statistics
        $mockStats = [
            'activeBookings' => 12,
            'totalClients' => 28,
            'revenue' => 15750
        ];
        
        echo json_encode(['success' => true, 'stats' => $mockStats]);
        return;
    }
    
    // For regular agents, check database connection
    if (!$conn) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        return;
    }
    
    try {
        // Get active bookings count - using the new table structure
        $activeBookingsQuery = "SELECT COUNT(*) as count FROM Bookings WHERE booking_status = 'confirmed'";
        $stmt = $conn->prepare($activeBookingsQuery);
        if (!$stmt) {
            throw new Exception("Database error: " . $conn->error);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $activeBookings = $result->fetch_assoc()['count'] ?? 0;
        
        // Get total clients (unique guests)
        $totalClientsQuery = "SELECT COUNT(DISTINCT guest_email) as count FROM Bookings";
        $stmt = $conn->prepare($totalClientsQuery);
        if (!$stmt) {
            throw new Exception("Database error: " . $conn->error);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $totalClients = $result->fetch_assoc()['count'] ?? 0;
        
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
        $revenue = $revenueResult['revenue'] ? floatval($revenueResult['revenue']) : 0;
        
        echo json_encode([
            'success' => true, 
            'stats' => [
                'activeBookings' => $activeBookings,
                'totalClients' => $totalClients,
                'revenue' => $revenue
            ]
        ]);
    } catch (Exception $e) {
        // Log the error for server-side debugging
        error_log('Error in getAgentStats: ' . $e->getMessage());
        
        // Return a user-friendly error message
        echo json_encode([
            'success' => false, 
            'message' => 'Error retrieving statistics: ' . $e->getMessage()
        ]);
    }
}
// Get available listings
function getListings() {
    global $conn;
    
    try {
        // Check if database connection exists
        if (!$conn) {
            // Return hard-coded listings from the database if connection fails
            $listings = [
                [
                    'listing_id' => 1,
                    'property_name' => 'Peace Guest House',
                    'property_location' => 'Pokhara, Nepal',
                    'property_type' => 'guesthouse',
                    'nightly_rate' => 3500.00,
                    'image_url' => 'https://www.telegraph.co.uk/content/dam/Travel/hotels/asia/nepal/the-pavilions-himalayas-pool-p.jpg'
                ],
                [
                    'listing_id' => 2,
                    'property_name' => 'Mountain View Guest House',
                    'property_location' => 'Pokhara, Lakeside',
                    'property_type' => 'Guest House',
                    'nightly_rate' => 2500.00,
                    'image_url' => 'https://www.telegraph.co.uk/content/dam/Travel/hotels/asia/nepal/the-pavilions-himalayas-pool-p.jpg'
                ],
                [
                    'listing_id' => 3,
                    'property_name' => 'Lakeside Retreat',
                    'property_location' => 'Pokhara, Lakeside',
                    'property_type' => 'Guest House',
                    'nightly_rate' => 3000.00,
                    'image_url' => 'https://dynamic-media-cdn.tripadvisor.com/media/photo-o/10/05/b5/3b/kathmandu-guest-house.jpg?w=500&h=-1&s=1'
                ]
            ];
            
            echo json_encode(['success' => true, 'listings' => $listings]);
            return;
        }
        
        // Query to get all listings
        $query = "SELECT * FROM Listings ORDER BY property_name ASC";
        $result = $conn->query($query);
        
        if (!$result) {
            throw new Exception("Database query failed: " . $conn->error);
        }
        
        $listings = [];
        while ($row = $result->fetch_assoc()) {
            $listings[] = $row;
        }
        
        echo json_encode(['success' => true, 'listings' => $listings]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error retrieving listings: ' . $e->getMessage()]);
    }
}

// Create a new booking
function createBooking() {
    global $conn;
    
    // Get JSON data from request
    $json = file_get_contents('php://input');
    $data = json_decode($json, true);
    
    if (!$data) {
        echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
        return;
    }
    
    // Extract booking data
    $listing_id = isset($data['listing_id']) ? intval($data['listing_id']) : 0;
    $guest_name = isset($data['guest_name']) ? $data['guest_name'] : '';
    $guest_email = isset($data['guest_email']) ? $data['guest_email'] : '';
    $check_in_date = isset($data['check_in_date']) ? $data['check_in_date'] : '';
    $check_out_date = isset($data['check_out_date']) ? $data['check_out_date'] : '';
    $number_of_guests = isset($data['number_of_guests']) ? intval($data['number_of_guests']) : 0;
    $total_amount = isset($data['total_amount']) ? floatval($data['total_amount']) : 0.00;
    $booking_status = isset($data['booking_status']) ? $data['booking_status'] : 'pending';
    
    // Validate required fields
    if (!$listing_id || empty($guest_name) || empty($guest_email) || empty($check_in_date) || 
        empty($check_out_date) || !$number_of_guests || !$total_amount) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields']);
        return;
    }
    
    try {
        // Check if database connection exists
        if (!$conn) {
            // Return success for demo purposes
            echo json_encode([
                'success' => true, 
                'message' => 'Booking created successfully (demo mode)',
                'booking_id' => rand(1000, 9999)
            ]);
            return;
        }
        
        // Prepare SQL statement for insertion
        $stmt = $conn->prepare("INSERT INTO Bookings (listing_id, guest_name, guest_email, check_in_date, 
                              check_out_date, number_of_guests, total_amount, booking_status) 
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->bind_param("issssiis", $listing_id, $guest_name, $guest_email, $check_in_date, 
                         $check_out_date, $number_of_guests, $total_amount, $booking_status);
        
        if ($stmt->execute()) {
            $booking_id = $stmt->insert_id;
            echo json_encode([
                'success' => true, 
                'message' => 'Booking created successfully',
                'booking_id' => $booking_id
            ]);
        } else {
            throw new Exception("Database insertion failed: " . $stmt->error);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Error creating booking: ' . $e->getMessage()]);
    }
}
// Get client list for an agent
function getClientList() {
    global $conn;
    
    // Get agent ID from request
    $agentId = isset($_REQUEST['agentId']) ? intval($_REQUEST['agentId']) : 0;
    
    // Check if this is a demo agent
    $isDemo = ($agentId === 999);
    
    // For demo agent, return mock data
    if ($isDemo) {
        // Create mock clients data
        $mockClients = [
            [
                'id' => '1001',
                'name' => 'Smita Dhungel',
                'email' => 'smita@gmail.com',
                'phone' => '+977 9841234567',
                'bookingsCount' => 2,
                'totalSpent' => 7500
            ],
            [
                'id' => '1002',
                'name' => 'Rajesh Sharma',
                'email' => 'rajesh@example.com',
                'phone' => '+977 9851234567',
                'bookingsCount' => 3,
                'totalSpent' => 12800
            ],
            [
                'id' => '1003',
                'name' => 'Sarah Johnson',
                'email' => 'sarah@example.com',
                'phone' => '+1 555-123-4567',
                'bookingsCount' => 1,
                'totalSpent' => 6000
            ]
        ];
        
        echo json_encode(['success' => true, 'clients' => $mockClients]);
        return;
    }
    
    // For regular agents, check database connection
    if (!$conn) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        return;
    }
    
    try {
        // Query to get unique clients from the Bookings table
        // Exclude null names and group by email to avoid duplicates
        $query = "SELECT 
                    guest_name AS name, 
                    guest_email AS email, 
                    COUNT(booking_id) AS bookingsCount, 
                    SUM(total_amount) AS totalSpent 
                  FROM Bookings 
                  WHERE guest_name IS NOT NULL AND guest_name != '' 
                  GROUP BY guest_email 
                  ORDER BY guest_name ASC";
        
        $result = $conn->query($query);
        
        if (!$result) {
            throw new Exception("Database query failed: " . $conn->error);
        }
        
        $clients = [];
        while ($row = $result->fetch_assoc()) {
            // Generate a unique ID for each client based on email
            $clientId = md5($row['email']);
            
            $clients[] = [
                'id' => $clientId,
                'name' => $row['name'],
                'email' => $row['email'],
                'phone' => '', // Phone is not in the Bookings table
                'bookingsCount' => intval($row['bookingsCount']),
                'totalSpent' => floatval($row['totalSpent'])
            ];
        }
        
        echo json_encode(['success' => true, 'clients' => $clients]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

// Get reports data from Bookings and Listings tables
function getReportsData() {
    global $conn;
    
    // Get agent ID from request
    $agentId = isset($_REQUEST['agentId']) ? intval($_REQUEST['agentId']) : 0;
    
    // Check if this is a demo agent
    $isDemo = ($agentId === 999);
    
    // For demo agent, return mock data
    if ($isDemo) {
        // Create mock monthly revenue data
        $mockMonthlyRevenue = [
            ['month' => 'Jan', 'revenue' => 12500],
            ['month' => 'Feb', 'revenue' => 15800],
            ['month' => 'Mar', 'revenue' => 18200],
            ['month' => 'Apr', 'revenue' => 21000],
            ['month' => 'May', 'revenue' => 19550]
        ];
        
        // Create mock top destinations data
        $mockTopDestinations = [
            ['destination' => 'Everest Base Camp', 'bookings' => 12, 'revenue' => 28500],
            ['destination' => 'Annapurna Circuit', 'bookings' => 8, 'revenue' => 19200],
            ['destination' => 'Langtang Valley', 'bookings' => 6, 'revenue' => 14400],
            ['destination' => 'Upper Mustang', 'bookings' => 4, 'revenue' => 12000],
            ['destination' => 'Gokyo Lakes', 'bookings' => 3, 'revenue' => 7500]
        ];
        
        echo json_encode([
            'success' => true, 
            'monthlyRevenue' => $mockMonthlyRevenue, 
            'topDestinations' => $mockTopDestinations
        ]);
        return;
    }
    
    // For regular agents, check database connection
    if (!$conn) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        return;
    }
    
    try {
        // Get monthly revenue data from Bookings table
        $monthlyRevenueQuery = "SELECT 
                                  DATE_FORMAT(created_at, '%b') AS month, 
                                  SUM(total_amount) AS revenue 
                                FROM Bookings 
                                WHERE booking_status != 'cancelled' 
                                GROUP BY DATE_FORMAT(created_at, '%m') 
                                ORDER BY DATE_FORMAT(created_at, '%m') ASC";
        
        $monthlyResult = $conn->query($monthlyRevenueQuery);
        
        if (!$monthlyResult) {
            throw new Exception("Monthly revenue query failed: " . $conn->error);
        }
        
        $monthlyRevenue = [];
        while ($row = $monthlyResult->fetch_assoc()) {
            $monthlyRevenue[] = [
                'month' => $row['month'],
                'revenue' => floatval($row['revenue'])
            ];
        }
        
        // Get top destinations by listing_id from Bookings table
        // Since we don't have access to listing names, we'll use the listing_id as the destination
        $topDestinationsQuery = "SELECT 
                                  b.listing_id AS destination, 
                                  COUNT(b.booking_id) AS bookings, 
                                  SUM(b.total_amount) AS revenue 
                                FROM Bookings b 
                                WHERE b.booking_status != 'cancelled' 
                                GROUP BY b.listing_id 
                                ORDER BY COUNT(b.booking_id) DESC 
                                LIMIT 5";
        
        $destinationsResult = $conn->query($topDestinationsQuery);
        
        if (!$destinationsResult) {
            throw new Exception("Top destinations query failed: " . $conn->error);
        }
        
        $topDestinations = [];
        while ($row = $destinationsResult->fetch_assoc()) {
            $topDestinations[] = [
                'destination' => $row['destination'],
                'bookings' => intval($row['bookings']),
                'revenue' => floatval($row['revenue'])
            ];
        }
        
        echo json_encode([
            'success' => true, 
            'monthlyRevenue' => $monthlyRevenue, 
            'topDestinations' => $topDestinations
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
?>
