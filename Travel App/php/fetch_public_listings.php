<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Function to send JSON response
function send_json_response($success, $data = null, $message = '') {
    $response = [
        'success' => $success,
        'message' => $message
    ];
    if ($data !== null) {
        $response['listings'] = $data;
    }
    echo json_encode($response);
    exit();
}

try {
    require_once 'config.php';

    $sql = "SELECT 
            listing_id,
            property_name as name,
            property_location as location,
            property_type as type,
            property_description as description,
            property_amenities as amenities,
            nightly_rate,
            COALESCE(image_url, 'default_image.jpg') as image_url
            FROM Listings";

    if (!($stmt = $conn->prepare($sql))) {
        throw new Exception('Prepare failed: ' . $conn->error);
    }

    if (!$stmt->execute()) {
        throw new Exception('Execute failed: ' . $stmt->error);
    }

    $result = $stmt->get_result();
    $listings = [];

    while ($row = $result->fetch_assoc()) {
        $listings[] = $row;
    }

    // Log the results
    error_log('Fetched listings: ' . print_r($listings, true));

    send_json_response(true, $listings);

} catch (Exception $e) {
    error_log('Error in fetch_public_listings.php: ' . $e->getMessage());
    send_json_response(false, null, $e->getMessage());
}

if (isset($conn)) {
    $conn->close();
}
?>
