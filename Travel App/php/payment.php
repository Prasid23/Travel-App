<?php
// payment.php
include 'config.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $booking_id = $_POST['booking_id'];
    $amount = $_POST['amount'];
    $payment_method = $_POST['payment_method'];

    $sql = "INSERT INTO Transactions (booking_id, user_id, amount, payment_method, status) 
            VALUES ('$booking_id', '$user_id', '$amount', '$payment_method', 'completed')";

    if ($conn->query($sql) === TRUE) {
        $transaction_id = $conn->insert_id;
        $update_booking = "UPDATE Bookings SET status = 'confirmed' WHERE booking_id = '$booking_id'";
        $conn->query($update_booking);
        
        echo "Payment successful! Redirecting...";
        header("Refresh:2; url=booking-confirmation.html");
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    $conn->close();
}
?>