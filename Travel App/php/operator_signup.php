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
$required = ['name', 'email', 'password', 'company_name'];
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
    
    $name = $data['name'];
    $email = $data['email'];
    $password = $data['password']; // In a real app, hash this password
    $company_name = $data['company_name'];
    
    // Check if email already exists
    $stmt = $conn->prepare("SELECT operator_id FROM BusOperators WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->rowCount() > 0) {
        send_json_response(false, null, 'Email already registered. Please use a different email.');
    }
    
    // Insert new operator
    $stmt = $conn->prepare("INSERT INTO BusOperators (name, email, password, company_name) VALUES (?, ?, ?, ?)");
    
    if (!$stmt->execute([$name, $email, $password, $company_name])) {
        throw new Exception('Failed to register operator');
    }
    
    $operator_id = $conn->lastInsertId();
    
    // Set session variables
    $_SESSION['operator_id'] = $operator_id;
    $_SESSION['operator_name'] = $name;
    $_SESSION['operator_email'] = $email;
    $_SESSION['operator_company'] = $company_name;
    
    // Return operator data
    send_json_response(true, [
        'operator_id' => $operator_id,
        'name' => $name,
        'email' => $email,
        'company_name' => $company_name
    ], 'Registration successful!');

} catch (PDOException $e) {
    send_json_response(false, null, 'Database error: ' . $e->getMessage());
} catch (Exception $e) {
    send_json_response(false, null, $e->getMessage());
} finally {
    // Close connection
    $conn = null;
}
?>
