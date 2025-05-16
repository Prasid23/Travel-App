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

// Validate request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json_response(false, null, 'Invalid request method.');
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

// Validate required parameters
$required = ['email', 'password'];
foreach ($required as $field) {
    if (!isset($data[$field])) {
        send_json_response(false, null, "Missing required field: $field");
    }
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
    
    $email = $data['email'];
    $password = $data['password'];
    
    // Check if operator exists
    $stmt = $conn->prepare("SELECT * FROM BusOperators WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->rowCount() === 0) {
        send_json_response(false, null, 'Invalid email or password.');
    }
    
    $operator = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Verify password (in a real app, use password_verify with hashed passwords)
    if ($password !== $operator['password']) {
        send_json_response(false, null, 'Invalid email or password.');
    }
    
    // Set session variables
    $_SESSION['operator_id'] = $operator['operator_id'];
    $_SESSION['operator_name'] = $operator['name'];
    $_SESSION['operator_email'] = $operator['email'];
    $_SESSION['operator_company'] = $operator['company_name'];
    
    // Return operator data
    send_json_response(true, [
        'operator_id' => $operator['operator_id'],
        'name' => $operator['name'],
        'email' => $operator['email'],
        'company_name' => $operator['company_name']
    ], 'Login successful!');

} catch (PDOException $e) {
    send_json_response(false, null, 'Database error: ' . $e->getMessage());
} catch (Exception $e) {
    send_json_response(false, null, $e->getMessage());
} finally {
    // Close connection
    $conn = null;
}
?>
