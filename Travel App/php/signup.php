<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Prepare and bind
    $stmt = $conn->prepare("INSERT INTO Users (full_name, email, username, password) 
                            VALUES (?, ?, ?, ?)");
    if ($stmt === false) {
        die('Prepare failed: ' . htmlspecialchars($conn->error));
    }

    $stmt->bind_param("ssss", $fullname, $email, $username, $password);

    // Execute the statement
    if ($stmt->execute()) {
        echo "Registration successful! Redirecting...";
        header("Location: http://localhost/Travel%20App/html/login.html");
        exit();
    } else {
        die('Execute failed: ' . htmlspecialchars($stmt->error));
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();
}
?>