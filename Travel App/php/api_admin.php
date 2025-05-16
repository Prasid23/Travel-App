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
if (isset($_GET['action']) || isset($_POST['action'])) {
    $action = isset($_GET['action']) ? $_GET['action'] : $_POST['action'];
    
    switch ($action) {
        case 'adminLogin':
            handleAdminLogin();
            break;
        case 'getDashboardStats':
            getDashboardStats();
            break;
        case 'getUsers':
            getUsers();
            break;
        case 'getUserDetails':
            getUserDetails();
            break;
        case 'updateUser':
            updateUser();
            break;
        case 'deleteUser':
            deleteUser();
            break;
        case 'getBookings':
            getBookings();
            break;
        case 'getBookingDetails':
            getBookingDetails();
            break;
        case 'updateBooking':
            updateBooking();
            break;
        case 'getTransactions':
            getTransactions();
            break;
        case 'getRevenueData':
            getRevenueData();
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
}

// Handle admin login
function handleAdminLogin() {
    // Get POST data
    $username = isset($_POST['username']) ? $_POST['username'] : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    
    // For demo purposes, allow login with admin/admin
    if ($username === 'admin' && $password === 'admin') {
        $adminUser = [
            'id' => 1,
            'username' => 'admin',
            'name' => 'System Administrator',
            'email' => 'admin@easytrip.com',
            'role' => 'admin'
        ];
        
        echo json_encode([
            'success' => true, 
            'admin' => $adminUser,
            'token' => 'admin-token-' . time()
        ]);
        return;
    }
    
    // In a real app, check credentials against database
    echo json_encode(['success' => false, 'message' => 'Invalid username or password']);
}

// Get dashboard statistics
function getDashboardStats() {
    global $conn;
    
    if (!$conn) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        return;
    }
    
    try {
        // Get total users (regular users + agents + owners)
        $usersQuery = "SELECT COUNT(*) as total FROM Users";
        $usersResult = $conn->query($usersQuery);
        $regularUsers = 0;
        
        if ($usersResult && $row = $usersResult->fetch_assoc()) {
            $regularUsers = $row['total'];
        }
        
        // Get total agents
        $agentsQuery = "SELECT COUNT(*) as total FROM agents";
        $agentsResult = $conn->query($agentsQuery);
        $totalAgents = 0;
        
        if ($agentsResult && $row = $agentsResult->fetch_assoc()) {
            $totalAgents = $row['total'];
        }
        
        // Get total owners
        $ownersQuery = "SELECT COUNT(*) as total FROM Owners";
        $ownersResult = $conn->query($ownersQuery);
        $totalOwners = 0;
        
        if ($ownersResult && $row = $ownersResult->fetch_assoc()) {
            $totalOwners = $row['total'];
        }
        
        // Calculate total users
        $totalUsers = $regularUsers + $totalAgents + $totalOwners;
        
        // Get total accommodation bookings
        $bookingsQuery = "SELECT COUNT(*) as total FROM Bookings";
        $bookingsResult = $conn->query($bookingsQuery);
        $accommodationBookings = 0;
        
        if ($bookingsResult && $row = $bookingsResult->fetch_assoc()) {
            $accommodationBookings = $row['total'];
        }
        
        // Get total bus bookings
        $busBookingsQuery = "SELECT COUNT(*) as total FROM BusBookings";
        $busBookingsResult = $conn->query($busBookingsQuery);
        $busBookings = 0;
        
        if ($busBookingsResult && $row = $busBookingsResult->fetch_assoc()) {
            $busBookings = $row['total'];
        }
        
        $totalBookings = $accommodationBookings + $busBookings;
        
        // Get total accommodation revenue
        $accommodationRevenueQuery = "SELECT SUM(total_amount) as total FROM Bookings WHERE booking_status != 'cancelled'";
        $accommodationRevenueResult = $conn->query($accommodationRevenueQuery);
        $accommodationRevenue = 0;
        
        if ($accommodationRevenueResult && $row = $accommodationRevenueResult->fetch_assoc()) {
            $accommodationRevenue = $row['total'] ? $row['total'] : 0;
        }
        
        // Get total bus booking revenue
        $busRevenueQuery = "SELECT SUM(total_amount) as total FROM BusBookings WHERE booking_status != 'cancelled'";
        $busRevenueResult = $conn->query($busRevenueQuery);
        $busRevenue = 0;
        
        if ($busRevenueResult && $row = $busRevenueResult->fetch_assoc()) {
            $busRevenue = $row['total'] ? $row['total'] : 0;
        }
        
        $totalRevenue = $accommodationRevenue + $busRevenue;
        
        // Get total listings (combine property listings and bus services)
        $listingsQuery = "SELECT COUNT(*) as total FROM Listings";
        $listingsResult = $conn->query($listingsQuery);
        $propertyListings = 0;
        
        if ($listingsResult && $row = $listingsResult->fetch_assoc()) {
            $propertyListings = $row['total'];
        }
        
        $guestHousesQuery = "SELECT COUNT(*) as total FROM GuestHouses";
        $guestHousesResult = $conn->query($guestHousesQuery);
        $guestHouses = 0;
        
        if ($guestHousesResult && $row = $guestHousesResult->fetch_assoc()) {
            $guestHouses = $row['total'];
        }
        
        $busServicesQuery = "SELECT COUNT(*) as total FROM BusServices";
        $busServicesResult = $conn->query($busServicesQuery);
        $busServices = 0;
        
        if ($busServicesResult && $row = $busServicesResult->fetch_assoc()) {
            $busServices = $row['total'];
        }
        
        $totalListings = $propertyListings + $guestHouses + $busServices;
        
        // Get recent users (last 5)
        $recentUsersQuery = "SELECT user_id, username, email, created_at FROM Users ORDER BY created_at DESC LIMIT 5";
        $recentUsersResult = $conn->query($recentUsersQuery);
        $recentUsers = [];
        
        if ($recentUsersResult) {
            while ($row = $recentUsersResult->fetch_assoc()) {
                $recentUsers[] = $row;
            }
        }
        
        // Get recent accommodation bookings (last 3)
        $recentAccommodationBookingsQuery = "SELECT 
            b.booking_id as id, 
            u.username, 
            l.property_name as property_name, 
            b.check_in_date, 
            b.check_out_date, 
            b.total_amount, 
            b.booking_status as status, 
            b.created_at,
            'accommodation' as booking_type
        FROM Bookings b
        LEFT JOIN Users u ON b.user_id = u.user_id
        LEFT JOIN Listings l ON b.listing_id = l.listing_id
        ORDER BY b.created_at DESC LIMIT 3";
        
        $recentAccommodationBookingsResult = $conn->query($recentAccommodationBookingsQuery);
        $recentAccommodationBookings = [];
        
        if ($recentAccommodationBookingsResult) {
            while ($row = $recentAccommodationBookingsResult->fetch_assoc()) {
                $recentAccommodationBookings[] = $row;
            }
        }
        
        // Get recent bus bookings (last 3)
        $recentBusBookingsQuery = "SELECT 
            bb.booking_id as id, 
            u.username, 
            bs.bus_name as property_name, 
            bb.journey_date as check_in_date, 
            bb.journey_date as check_out_date, 
            bb.total_amount, 
            bb.booking_status as status, 
            bb.created_at,
            'bus' as booking_type
        FROM BusBookings bb
        LEFT JOIN Users u ON bb.user_id = u.user_id
        LEFT JOIN BusServices bs ON bb.bus_service_id = bs.bus_service_id
        ORDER BY bb.created_at DESC LIMIT 3";
        
        $recentBusBookingsResult = $conn->query($recentBusBookingsQuery);
        $recentBusBookings = [];
        
        // Prepare the response data
        $stats = [
            'totalUsers' => (int)$totalUsers,
            'totalBookings' => (int)$totalBookings,
            'totalRevenue' => (int)$totalRevenue,
            'totalListings' => (int)$totalListings
        ];
        
        // Debug info
        error_log("Dashboard Stats: " . json_encode($stats));
        
        // Combine accommodation and bus bookings for recent bookings
        $recentBookings = array_merge($recentAccommodationBookings, $recentBusBookings);
        
        // Sort by created_at
        usort($recentBookings, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
        
        // Limit to 5 most recent
        $recentBookings = array_slice($recentBookings, 0, 5);
        
        // If no data is available, provide demo data
        if ($totalUsers == 0 && $totalBookings == 0 && $totalRevenue == 0) {
            $stats = [
                'totalUsers' => 4,
                'totalBookings' => 16,
                'totalRevenue' => 148120,
                'totalListings' => 13
            ];
            
            // Demo recent users
            $recentUsers = [
                ['user_id' => 1, 'username' => 'john_doe', 'email' => 'john@example.com', 'created_at' => '2023-05-15'],
                ['user_id' => 2, 'username' => 'jane_smith', 'email' => 'jane@example.com', 'created_at' => '2023-05-10']
            ];
            
            // Demo recent bookings
            $recentBookings = [
                ['id' => 101, 'username' => 'john_doe', 'property_name' => 'Luxury Villa', 'check_in_date' => '2023-06-01', 'check_out_date' => '2023-06-05', 'total_amount' => 25000, 'status' => 'confirmed', 'booking_type' => 'accommodation'],
                ['id' => 102, 'username' => 'jane_smith', 'property_name' => 'Deluxe Bus', 'departure_date' => '2023-06-10', 'total_amount' => 1200, 'status' => 'confirmed', 'booking_type' => 'bus']
            ];
        }
        
        echo json_encode([
            'success' => true,
            'stats' => $stats,
            'recentUsers' => $recentUsers,
            'recentBookings' => $recentBookings
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

// Get all users
function getUsers() {
    global $conn;
    
    if (!$conn) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        return;
    }
    
    try {
        // Get regular users
        $query = "SELECT 
            u.user_id, 
            u.username, 
            u.email, 
            u.created_at,
            COALESCE(r.role_name, 'User') as role,
            'active' as status
        FROM Users u
        LEFT JOIN UserRoles ur ON u.user_id = ur.user_id
        LEFT JOIN Roles r ON ur.role_id = r.role_id
        ORDER BY u.created_at DESC";
        
        $result = $conn->query($query);
        $users = [];
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                // Add user type
                $row['user_type'] = 'regular';
                $users[] = $row;
            }
        }
        
        // Get agents
        $agentsQuery = "SELECT 
            a.id as user_id,
            a.fullName as username,
            a.email,
            a.created_at,
            'Agent' as role,
            'active' as status,
            'agent' as user_type
        FROM agents a
        ORDER BY a.created_at DESC";
        
        $agentsResult = $conn->query($agentsQuery);
        
        if ($agentsResult) {
            while ($row = $agentsResult->fetch_assoc()) {
                $users[] = $row;
            }
        }
        
        // Get guest house owners
        $ownersQuery = "SELECT 
            o.owner_id as user_id,
            o.full_name as username,
            o.email,
            o.created_at,
            'Owner' as role,
            'active' as status,
            'owner' as user_type
        FROM Owners o
        ORDER BY o.created_at DESC";
        
        $ownersResult = $conn->query($ownersQuery);
        
        if ($ownersResult) {
            while ($row = $ownersResult->fetch_assoc()) {
                $users[] = $row;
            }
        }
        
        // Sort all users by created_at
        usort($users, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
        
        echo json_encode(['success' => true, 'users' => $users]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

// Get user details
function getUserDetails() {
    global $conn;
    
    $userId = isset($_REQUEST['userId']) ? intval($_REQUEST['userId']) : 0;
    
    if (!$conn) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        return;
    }
    
    if ($userId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
        return;
    }
    
    try {
        // Get user details
        $userQuery = "SELECT user_id, username, email, created_at FROM Users WHERE user_id = ?";
        $stmt = $conn->prepare($userQuery);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $user = $result->fetch_assoc()) {
            // Get user's bookings
            $bookingsQuery = "SELECT b.booking_id, l.property_name, b.check_in_date, b.check_out_date, 
                             b.total_amount, b.booking_status, b.created_at 
                             FROM Bookings b 
                             JOIN Listings l ON b.listing_id = l.listing_id 
                             WHERE b.user_id = ? 
                             ORDER BY b.created_at DESC";
            $stmt = $conn->prepare($bookingsQuery);
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $bookingsResult = $stmt->get_result();
            
            $bookings = [];
            if ($bookingsResult) {
                while ($row = $bookingsResult->fetch_assoc()) {
                    $bookings[] = $row;
                }
            }
            
            echo json_encode([
                'success' => true,
                'user' => $user,
                'bookings' => $bookings
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'User not found']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

// Update user
function updateUser() {
    global $conn;
    
    $userId = isset($_POST['userId']) ? intval($_POST['userId']) : 0;
    $username = isset($_POST['username']) ? $_POST['username'] : '';
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    
    if (!$conn) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        return;
    }
    
    if ($userId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
        return;
    }
    
    if (empty($username) || empty($email)) {
        echo json_encode(['success' => false, 'message' => 'Username and email are required']);
        return;
    }
    
    try {
        $query = "UPDATE Users SET username = ?, email = ? WHERE user_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssi", $username, $email, $userId);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'User updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update user']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

// Delete user
function deleteUser() {
    global $conn;
    
    $userId = isset($_POST['userId']) ? intval($_POST['userId']) : 0;
    
    if (!$conn) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        return;
    }
    
    if ($userId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
        return;
    }
    
    try {
        // Start transaction
        $conn->begin_transaction();
        
        // Delete user's bookings
        $deleteBookingsQuery = "DELETE FROM Bookings WHERE user_id = ?";
        $stmt = $conn->prepare($deleteBookingsQuery);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        
        // Delete user
        $deleteUserQuery = "DELETE FROM Users WHERE user_id = ?";
        $stmt = $conn->prepare($deleteUserQuery);
        $stmt->bind_param("i", $userId);
        
        if ($stmt->execute()) {
            $conn->commit();
            echo json_encode(['success' => true, 'message' => 'User deleted successfully']);
        } else {
            $conn->rollback();
            echo json_encode(['success' => false, 'message' => 'Failed to delete user']);
        }
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

// Get all bookings
function getBookings() {
    global $conn;
    
    if (!$conn) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        return;
    }
    
    try {
        // Get accommodation bookings
        $accommodationQuery = "SELECT 
            b.booking_id as id, 
            u.username, 
            COALESCE(l.property_name, gh.name) as property_name, 
            b.check_in_date, 
            b.check_out_date, 
            b.number_of_guests, 
            b.total_amount, 
            b.booking_status as status, 
            b.created_at,
            'accommodation' as booking_type
        FROM Bookings b
        LEFT JOIN Users u ON b.user_id = u.user_id
        LEFT JOIN Listings l ON b.listing_id = l.listing_id
        LEFT JOIN GuestHouses gh ON b.listing_id = gh.id
        ORDER BY b.created_at DESC";
        
        $accommodationResult = $conn->query($accommodationQuery);
        $accommodationBookings = [];
        
        if ($accommodationResult) {
            while ($row = $accommodationResult->fetch_assoc()) {
                $accommodationBookings[] = $row;
            }
        }
        
        // Get bus bookings
        $busQuery = "SELECT 
            bb.booking_id as id, 
            u.username, 
            bs.bus_name as property_name, 
            bb.journey_date as check_in_date, 
            bb.journey_date as check_out_date, 
            bb.num_seats as number_of_guests, 
            bb.total_amount, 
            bb.booking_status as status, 
            bb.created_at,
            'bus' as booking_type
        FROM BusBookings bb
        LEFT JOIN Users u ON bb.user_id = u.user_id
        LEFT JOIN BusServices bs ON bb.bus_service_id = bs.bus_service_id
        ORDER BY bb.created_at DESC";
        
        $busResult = $conn->query($busQuery);
        $busBookings = [];
        
        if ($busResult) {
            while ($row = $busResult->fetch_assoc()) {
                $busBookings[] = $row;
            }
        }
        
        // Combine and sort all bookings
        $allBookings = array_merge($accommodationBookings, $busBookings);
        
        // Sort by created_at in descending order
        usort($allBookings, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
        
        echo json_encode(['success' => true, 'bookings' => $allBookings]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

// Get booking details
function getBookingDetails() {
    global $conn;
    
    $bookingId = isset($_REQUEST['bookingId']) ? intval($_REQUEST['bookingId']) : 0;
    
    if (!$conn) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        return;
    }
    
    if ($bookingId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid booking ID']);
        return;
    }
    
    try {
        $query = "SELECT b.booking_id, u.user_id, u.username, u.email, l.listing_id, l.property_name, 
                 l.property_location, b.check_in_date, b.check_out_date, b.number_of_guests, 
                 b.room_type, b.special_requests, b.nightly_rate, b.total_amount, 
                 b.booking_status, b.created_at 
                 FROM Bookings b 
                 JOIN Users u ON b.user_id = u.user_id 
                 JOIN Listings l ON b.listing_id = l.listing_id 
                 WHERE b.booking_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $bookingId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result && $booking = $result->fetch_assoc()) {
            // Get transaction details if any
            $transactionQuery = "SELECT transaction_id, amount, status, created_at 
                                FROM Transactions 
                                WHERE booking_id = ?";
            $stmt = $conn->prepare($transactionQuery);
            $stmt->bind_param("i", $bookingId);
            $stmt->execute();
            $transactionResult = $stmt->get_result();
            
            $transaction = null;
            if ($transactionResult && $row = $transactionResult->fetch_assoc()) {
                $transaction = $row;
            }
            
            echo json_encode([
                'success' => true,
                'booking' => $booking,
                'transaction' => $transaction
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Booking not found']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

// Update booking
function updateBooking() {
    global $conn;
    
    $bookingId = isset($_POST['bookingId']) ? intval($_POST['bookingId']) : 0;
    $status = isset($_POST['status']) ? $_POST['status'] : '';
    
    if (!$conn) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        return;
    }
    
    if ($bookingId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid booking ID']);
        return;
    }
    
    if (empty($status)) {
        echo json_encode(['success' => false, 'message' => 'Status is required']);
        return;
    }
    
    try {
        $query = "UPDATE Bookings SET booking_status = ? WHERE booking_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("si", $status, $bookingId);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Booking updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update booking']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

// Get all transactions
function getTransactions() {
    global $conn;
    
    if (!$conn) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        return;
    }
    
    try {
        // Get regular transactions
        $query = "SELECT 
            t.transaction_id as id, 
            t.booking_id, 
            u.username, 
            t.amount as total_amount, 
            t.status, 
            t.created_at,
            'accommodation' as transaction_type 
        FROM Transactions t 
        JOIN Bookings b ON t.booking_id = b.booking_id 
        JOIN Users u ON b.user_id = u.user_id 
        ORDER BY t.created_at DESC";
        
        $result = $conn->query($query);
        $regularTransactions = [];
        
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $regularTransactions[] = $row;
            }
        }
        
        // Get transaction monitoring data
        $monitoringQuery = "SELECT 
            tm.monitoring_id as id, 
            tm.transaction_id as booking_id, 
            u.username, 
            t.amount as total_amount, 
            tm.status, 
            t.created_at,
            'monitoring' as transaction_type 
        FROM TransactionMonitoring tm 
        LEFT JOIN Transactions t ON tm.transaction_id = t.transaction_id
        LEFT JOIN Bookings b ON t.booking_id = b.booking_id
        LEFT JOIN Users u ON b.user_id = u.user_id
        ORDER BY t.created_at DESC";
        
        $monitoringResult = $conn->query($monitoringQuery);
        $monitoringTransactions = [];
        
        if ($monitoringResult) {
            while ($row = $monitoringResult->fetch_assoc()) {
                $monitoringTransactions[] = $row;
            }
        }
        
        // Combine all transactions
        $allTransactions = array_merge($regularTransactions, $monitoringTransactions);
        
        // Sort by created_at in descending order
        usort($allTransactions, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
        
        echo json_encode(['success' => true, 'transactions' => $allTransactions]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}

// Get revenue data for reports
function getRevenueData() {
    global $conn;
    
    if (!$conn) {
        echo json_encode(['success' => false, 'message' => 'Database connection failed']);
        return;
    }
    
    try {
        // Get monthly accommodation revenue
        $accommodationMonthlyQuery = "SELECT DATE_FORMAT(created_at, '%b') AS month, 
                        DATE_FORMAT(created_at, '%Y-%m') AS yearMonth,
                        SUM(total_amount) AS revenue 
                        FROM Bookings 
                        WHERE booking_status != 'cancelled' 
                        GROUP BY yearMonth 
                        ORDER BY yearMonth ASC 
                        LIMIT 12";
        $accommodationMonthlyResult = $conn->query($accommodationMonthlyQuery);
        $accommodationMonthlyRevenue = [];
        
        if ($accommodationMonthlyResult) {
            while ($row = $accommodationMonthlyResult->fetch_assoc()) {
                $accommodationMonthlyRevenue[$row['yearMonth']] = [
                    'month' => $row['month'],
                    'revenue' => floatval($row['revenue'])
                ];
            }
        }
        
        // Get monthly bus booking revenue
        $busMonthlyQuery = "SELECT DATE_FORMAT(created_at, '%b') AS month, 
                        DATE_FORMAT(created_at, '%Y-%m') AS yearMonth,
                        SUM(total_amount) AS revenue 
                        FROM BusBookings 
                        WHERE booking_status != 'cancelled' 
                        GROUP BY yearMonth 
                        ORDER BY yearMonth ASC 
                        LIMIT 12";
        $busMonthlyResult = $conn->query($busMonthlyQuery);
        $busMonthlyRevenue = [];
        
        if ($busMonthlyResult) {
            while ($row = $busMonthlyResult->fetch_assoc()) {
                $busMonthlyRevenue[$row['yearMonth']] = [
                    'month' => $row['month'],
                    'revenue' => floatval($row['revenue'])
                ];
            }
        }
        
        // Combine monthly revenue data
        $allMonths = array_unique(array_merge(array_keys($accommodationMonthlyRevenue), array_keys($busMonthlyRevenue)));
        sort($allMonths);
        
        $combinedMonthlyRevenue = [];
        foreach ($allMonths as $yearMonth) {
            $month = isset($accommodationMonthlyRevenue[$yearMonth]) ? $accommodationMonthlyRevenue[$yearMonth]['month'] : 
                   (isset($busMonthlyRevenue[$yearMonth]) ? $busMonthlyRevenue[$yearMonth]['month'] : '');
            
            $accommodationRev = isset($accommodationMonthlyRevenue[$yearMonth]) ? $accommodationMonthlyRevenue[$yearMonth]['revenue'] : 0;
            $busRev = isset($busMonthlyRevenue[$yearMonth]) ? $busMonthlyRevenue[$yearMonth]['revenue'] : 0;
            
            $combinedMonthlyRevenue[] = [
                'month' => $month,
                'revenue' => $accommodationRev + $busRev
            ];
        }
        
        // Get revenue by property type
        $propertyQuery = "SELECT 
            COALESCE(l.property_type, 'Accommodation') as type,
            COUNT(b.booking_id) AS bookings, 
            SUM(b.total_amount) AS revenue 
            FROM Bookings b 
            LEFT JOIN Listings l ON b.listing_id = l.listing_id 
            WHERE b.booking_status != 'cancelled' 
            GROUP BY type 
            ORDER BY revenue DESC";
        $propertyResult = $conn->query($propertyQuery);
        $propertyRevenue = [];
        
        if ($propertyResult) {
            while ($row = $propertyResult->fetch_assoc()) {
                $propertyRevenue[] = [
                    'type' => $row['type'],
                    'bookings' => intval($row['bookings']),
                    'revenue' => floatval($row['revenue'])
                ];
            }
        }
        
        // Add bus booking revenue as a separate type
        $busRevenueQuery = "SELECT 
            'Bus' as type,
            COUNT(booking_id) AS bookings, 
            SUM(total_amount) AS revenue 
            FROM BusBookings 
            WHERE booking_status != 'cancelled'";
        $busRevenueResult = $conn->query($busRevenueQuery);
        
        if ($busRevenueResult && $row = $busRevenueResult->fetch_assoc()) {
            $propertyRevenue[] = [
                'type' => $row['type'],
                'bookings' => intval($row['bookings']),
                'revenue' => floatval($row['revenue'])
            ];
        }
        
        // Get top accommodation properties by revenue
        $topPropertiesQuery = "SELECT 
            l.property_name as property,
            COUNT(b.booking_id) AS bookings,
            SUM(b.total_amount) AS revenue,
            'accommodation' as type
            FROM Bookings b
            LEFT JOIN Listings l ON b.listing_id = l.listing_id
            WHERE b.booking_status != 'cancelled' AND l.property_name IS NOT NULL
            GROUP BY property
            ORDER BY revenue DESC
            LIMIT 5";
        $topPropertiesResult = $conn->query($topPropertiesQuery);
        $topProperties = [];
        
        if ($topPropertiesResult) {
            while ($row = $topPropertiesResult->fetch_assoc()) {
                $topProperties[] = [
                    'property' => $row['property'],
                    'bookings' => intval($row['bookings']),
                    'revenue' => floatval($row['revenue']),
                    'type' => $row['type']
                ];
            }
        }
        
        // Get top bus services by revenue
        $topBusQuery = "SELECT 
            bs.bus_name as property,
            COUNT(bb.booking_id) AS bookings,
            SUM(bb.total_amount) AS revenue,
            'bus' as type
            FROM BusBookings bb
            LEFT JOIN BusServices bs ON bb.bus_service_id = bs.bus_service_id
            WHERE bb.booking_status != 'cancelled'
            GROUP BY property
            ORDER BY revenue DESC
            LIMIT 5";
        $topBusResult = $conn->query($topBusQuery);
        
        if ($topBusResult) {
            while ($row = $topBusResult->fetch_assoc()) {
                $topProperties[] = [
                    'property' => $row['property'],
                    'bookings' => intval($row['bookings']),
                    'revenue' => floatval($row['revenue']),
                    'type' => $row['type']
                ];
            }
        }
        
        // Sort top properties by revenue
        usort($topProperties, function($a, $b) {
            return $b['revenue'] - $a['revenue'];
        });
        
        // Limit to top 5 overall
        $topProperties = array_slice($topProperties, 0, 5);
        
        // If no data is available, provide demo data
        if (empty($combinedMonthlyRevenue) && empty($propertyRevenue) && empty($topProperties)) {
            // Demo monthly revenue data
            $combinedMonthlyRevenue = [
                ['month' => 'Jan', 'revenue' => 182000],
                ['month' => 'Feb', 'revenue' => 215000],
                ['month' => 'Mar', 'revenue' => 248000],
                ['month' => 'Apr', 'revenue' => 275000],
                ['month' => 'May', 'revenue' => 325000],
                ['month' => 'Jun', 'revenue' => 345000]
            ];
            
            // Demo property revenue data
            $propertyRevenue = [
                ['type' => 'Luxury Villa', 'bookings' => 125, 'revenue' => 625000],
                ['type' => 'Bus Service', 'bookings' => 223, 'revenue' => 423120],
                ['type' => 'Resort', 'bookings' => 98, 'revenue' => 196000],
                ['type' => 'Guest House', 'bookings' => 52, 'revenue' => 104000]
            ];
            
            // Demo top properties data
            $topProperties = [
                ['property' => 'Luxury Villa Goa', 'type' => 'accommodation', 'bookings' => 85, 'revenue' => 425000],
                ['property' => 'Delhi to Mumbai Express', 'type' => 'bus', 'bookings' => 138, 'revenue' => 276000],
                ['property' => 'Mountain Resort Shimla', 'type' => 'accommodation', 'bookings' => 68, 'revenue' => 204000],
                ['property' => 'Bangalore to Chennai Deluxe', 'type' => 'bus', 'bookings' => 85, 'revenue' => 170000],
                ['property' => 'Beach House Kerala', 'type' => 'accommodation', 'bookings' => 42, 'revenue' => 168000]
            ];
        }
        
        // Debug info
        error_log("Revenue Data: " . json_encode([
            'monthlyRevenue' => count($combinedMonthlyRevenue),
            'propertyRevenue' => count($propertyRevenue),
            'topProperties' => count($topProperties)
        ]));
        
        echo json_encode([
            'success' => true,
            'monthlyRevenue' => $combinedMonthlyRevenue,
            'propertyRevenue' => $propertyRevenue,
            'topProperties' => $topProperties
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
}
?>
