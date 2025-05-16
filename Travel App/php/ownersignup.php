<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'config.php';

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

// Sanitize and validate input
$fullName = filter_input(INPUT_POST, 'fullName', FILTER_SANITIZE_STRING);
$email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
$username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
$password = $_POST['password'] ?? null;

// Validate inputs
if (!$fullName || !$email || !$username || !$password) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit();
}

if (strlen($password) < 8) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters']);
    exit();
}

try {
    $conn->begin_transaction();

    // Check if email already exists
    $check_sql = "SELECT owner_id FROM Owners WHERE email = ? OR username = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ss", $email, $username);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        throw new Exception('Email or username already exists');
    }
    $check_stmt->close();

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Insert new owner
    $insert_sql = "INSERT INTO Owners (full_name, email, username, password) VALUES (?, ?, ?, ?)";
    $insert_stmt = $conn->prepare($insert_sql);
    $insert_stmt->bind_param("ssss", $fullName, $email, $username, $hashedPassword);
    
    if (!$insert_stmt->execute()) {
        throw new Exception('Failed to create account');
    }

    $owner_id = $conn->insert_id;
    $insert_stmt->close();

    // Set session variables
    $_SESSION['owner_id'] = $owner_id;
    $_SESSION['email'] = $email;
    $_SESSION['username'] = $username;
    $_SESSION['last_activity'] = time();

    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => 'Account created successfully!',
        'user' => [
            'id' => $owner_id,
            'name' => $fullName,
            'email' => $email,
            'username' => $username
        ]
    ]);

} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    error_log($e->getMessage());
} finally {
    if (isset($conn)) $conn->close();
}
?>
