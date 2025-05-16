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

if (!isset($_SESSION['owner_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit();
}

$owner_id = $_SESSION['owner_id'];
$listing_id = $_POST['listing_id'] ?? null;
$property_name = filter_input(INPUT_POST, 'property_name', FILTER_SANITIZE_STRING);
$property_location = filter_input(INPUT_POST, 'property_location', FILTER_SANITIZE_STRING);
$property_type = filter_input(INPUT_POST, 'property_type', FILTER_SANITIZE_STRING);
$property_description = filter_input(INPUT_POST, 'property_description', FILTER_SANITIZE_STRING);
$property_amenities = filter_input(INPUT_POST, 'property_amenities', FILTER_SANITIZE_STRING);
$nightly_rate = filter_input(INPUT_POST, 'nightly_rate', FILTER_VALIDATE_FLOAT);

if (!$property_name || !$property_location || !$property_type || !$property_description || !$property_amenities || !$nightly_rate) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit();
}

try {
    $conn->begin_transaction();

    if ($listing_id) {
        // Update existing listing
        $verify_sql = "SELECT owner_id FROM Listings WHERE listing_id = ?";
        $verify_stmt = $conn->prepare($verify_sql);
        $verify_stmt->bind_param("i", $listing_id);
        $verify_stmt->execute();
        $verify_result = $verify_stmt->get_result();

        if ($verify_result->num_rows === 0 || $verify_result->fetch_assoc()['owner_id'] !== $owner_id) {
            throw new Exception('Unauthorized access to listing');
        }
        $verify_stmt->close();

        $sql = "UPDATE Listings SET 
                property_name = ?,
                property_location = ?,
                property_type = ?,
                property_description = ?,
                property_amenities = ?,
                nightly_rate = ?
                WHERE listing_id = ? AND owner_id = ?";
                
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssdii", 
            $property_name,
            $property_location,
            $property_type,
            $property_description,
            $property_amenities,
            $nightly_rate,
            $listing_id,
            $owner_id
        );
    } else {
        // Create new listing
        $sql = "INSERT INTO Listings (owner_id, property_name, property_location, property_type, property_description, property_amenities, nightly_rate) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
                
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issssssd", 
            $owner_id,
            $property_name,
            $property_location,
            $property_type,
            $property_description,
            $property_amenities,
            $nightly_rate
        );
    }

    if (!$stmt->execute()) {
        throw new Exception($stmt->error);
    }

    $affected_id = $listing_id ?? $conn->insert_id;
    $stmt->close();

    // Fetch the updated/created listing
    $select_sql = "SELECT * FROM Listings WHERE listing_id = ?";
    $select_stmt = $conn->prepare($select_sql);
    $select_stmt->bind_param("i", $affected_id);
    $select_stmt->execute();
    $result = $select_stmt->get_result();
    $listing = $result->fetch_assoc();
    $select_stmt->close();

    $conn->commit();

    echo json_encode([
        'success' => true,
        'message' => $listing_id ? 'Listing updated successfully' : 'Listing created successfully',
        'listing' => [
            'id' => $listing['listing_id'],
            'name' => $listing['property_name'],
            'location' => $listing['property_location'],
            'type' => $listing['property_type'],
            'description' => $listing['property_description'],
            'amenities' => $listing['property_amenities'],
            'rate' => $listing['nightly_rate']
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
