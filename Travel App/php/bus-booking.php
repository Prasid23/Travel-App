<?php
// bus-booking.php
include 'config.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $bus_service_id = $_POST['bus_service_id'];
    $date = $_POST['journey_date'];
    $seats = $_POST['seats'];

    $sql = "INSERT INTO Bookings (user_id, bus_service_id, booking_date, seat_numbers) 
            VALUES ('$user_id', '$bus_service_id', '$date', '$seats')";

    if ($conn->query($sql) === TRUE) {
        $booking_id = $conn->insert_id;
        echo "Booking successful! Redirecting...";
        header("Refresh:2; url=payment.html?booking_id=$booking_id");
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
    $conn->close();
}
?>