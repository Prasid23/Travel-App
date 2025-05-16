<?php
// Disable error display, we'll handle errors in JSON
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Function to send JSON response
function send_json_response($success, $data, $message = '') {
    $response = [
        'success' => $success,
        'message' => $message
    ];
    if ($success) {
        $response['listing'] = $data;
    }
    echo json_encode($response);
    exit();
}

// Log all incoming requests
file_put_contents('fetch_listing_details_log.txt', date('[Y-m-d H:i:s] ') . 'Incoming request: ' . print_r($_GET, true) . "\n", FILE_APPEND);

require_once 'config.php';

if (!isset($_GET['id'])) {
    file_put_contents('fetch_listing_details_log.txt', date('[Y-m-d H:i:s] ') . 'No ID provided\n', FILE_APPEND);
    send_json_response(false, null, 'Listing ID is required');
}

$listing_id = intval($_GET['id']);

try {
    // Include database configuration
    require_once 'config.php';
    
    // Log the SQL query and parameters
    $log_message = "Attempting to fetch listing with ID: $listing_id\n";
    file_put_contents('fetch_listing_details_log.txt', date('[Y-m-d H:i:s] ') . $log_message, FILE_APPEND);

    $sql = "SELECT listing_id, property_name as name, property_location as location, property_type as type, 
            property_description as description, property_amenities as amenities, nightly_rate, 
            COALESCE(image_url, 'default_image.jpg') as image_url
            FROM Listings 
            WHERE listing_id = ?";
            
    if (!($stmt = $conn->prepare($sql))) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }

    if (!$stmt->bind_param("i", $listing_id)) {
        throw new Exception('Binding parameters failed: ' . $stmt->error);
    }

    if (!$stmt->execute()) {
        throw new Exception('Execute failed: ' . $stmt->error);
    }

    $result = $stmt->get_result();
    $listing = $result->fetch_assoc();

    if ($listing) {
        file_put_contents('fetch_listing_details_log.txt', date('[Y-m-d H:i:s] ') . 'Listing found: ' . print_r($listing, true) . "\n", FILE_APPEND);
        send_json_response(true, $listing);
    } else {
        file_put_contents('fetch_listing_details_log.txt', date('[Y-m-d H:i:s] ') . 'No listing found for ID: ' . $listing_id . "\n", FILE_APPEND);
        send_json_response(false, null, 'Listing not found');
    }
} catch (Exception $e) {
    file_put_contents('fetch_listing_details_log.txt', date('[Y-m-d H:i:s] ') . 'Error: ' . $e->getMessage() . "\n", FILE_APPEND);
    send_json_response(false, null, $e->getMessage());
}

if (isset($conn)) {
    $conn->close();
}
?>
