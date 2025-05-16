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
if (isset($_SESSION['operator_id'])) {
    // Clear operator session variables
    unset($_SESSION['operator_id']);
    unset($_SESSION['operator_name']);
    unset($_SESSION['operator_email']);
    unset($_SESSION['operator_company']);
    
    // Destroy the session
    session_destroy();
    
    send_json_response(true, null, 'Logged out successfully.');
} else {
    send_json_response(false, null, 'No active session found.');
}
?>
