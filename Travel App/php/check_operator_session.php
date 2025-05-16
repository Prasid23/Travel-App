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
    // Return operator data from session
    send_json_response(true, [
        'operator_id' => $_SESSION['operator_id'],
        'name' => $_SESSION['operator_name'],
        'email' => $_SESSION['operator_email'],
        'company_name' => $_SESSION['operator_company']
    ], 'Session active.');
} else {
    send_json_response(false, null, 'No active session found.');
}
?>
