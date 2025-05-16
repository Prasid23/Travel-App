<?php
// Set headers for JSON response
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET');
header('Access-Control-Allow-Headers: Content-Type');

// Include database connection
include 'config.php';

// Get POST data
$email = isset($_POST['email']) ? $_POST['email'] : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';
$action = isset($_POST['action']) ? $_POST['action'] : '';

// Only handle login requests
if ($action !== 'login') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid action'
    ]);
    exit;
}

// For demo purposes, always allow login with demo credentials as a fallback
if (strpos(strtolower($email), 'demo') !== false || $email === 'agent@example.com') {
    $mockAgent = [
        'id' => 999,
        'fullName' => 'Demo Agent',
        'email' => $email,
        'agencyName' => 'Demo Travel Agency',
        'agencyId' => 'DEMO123'
    ];
    echo json_encode([
        'success' => true, 
        'agent' => $mockAgent, 
        'demo' => true
    ]);
    exit;
}

// Check if credentials are empty
if (empty($email) || empty($password)) {
    echo json_encode([
        'success' => false, 
        'message' => 'Email and password are required'
    ]);
    exit;
}

// Try to authenticate using the agents table
try {
    // Check database connection
    if (!$conn) {
        throw new Exception("Database connection failed");
    }
    
    // Prepare SQL statement
    $stmt = $conn->prepare("SELECT id, fullName, email, agencyName, agencyId, password FROM agents WHERE email = ?");
    if (!$stmt) {
        throw new Exception("Database error: " . $conn->error);
    }
    
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $agent = $result->fetch_assoc();
        
        // Verify password (in a real app, use password_verify with hashed passwords)
        if ($password === $agent['password']) { // For demo purposes only
            // Remove password from response
            unset($agent['password']);
            
            echo json_encode([
                'success' => true, 
                'agent' => $agent
            ]);
            exit;
        } else {
            echo json_encode([
                'success' => false, 
                'message' => 'Invalid email or password'
            ]);
            exit;
        }
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Invalid email or password'
        ]);
        exit;
    }
} catch (Exception $e) {
    // Log the error for server-side debugging
    error_log('Error in login: ' . $e->getMessage());
    
    // Return a user-friendly error message
    echo json_encode([
        'success' => false, 
        'message' => 'An error occurred during login. Please try again.'
    ]);
    exit;
}

?>
