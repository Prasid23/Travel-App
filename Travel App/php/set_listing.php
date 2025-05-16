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

// Get POST data
$listing_id = $_POST['listing_id'] ?? null;

if (!$listing_id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Listing ID is required']);
    exit();
}

try {
    // Verify listing ownership
    $sql = "SELECT owner_id FROM Listings WHERE listing_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $listing_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        throw new Exception('Listing not found');
    }

    $listing = $result->fetch_assoc();
    if ($listing['owner_id'] !== $_SESSION['owner_id']) {
        throw new Exception('Unauthorized access to listing');
    }

    // Set the listing ID in session
    $_SESSION['listing_id'] = $listing_id;

    echo json_encode([
        'success' => true,
        'message' => 'Listing set successfully'
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    error_log($e->getMessage());
} finally {
    if (isset($stmt)) $stmt->close();
    if (isset($conn)) $conn->close();
}
?>
