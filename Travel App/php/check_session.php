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

if (!isset($_SESSION['user_id'])) {
    send_json_response(false, null, 'Not logged in');
} else {
    send_json_response(true, [
        'user_id' => $_SESSION['user_id'],
        'username' => $_SESSION['username']
    ]);
}
?>
