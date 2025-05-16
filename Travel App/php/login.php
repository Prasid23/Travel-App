<?php
// login.php
include 'config.php';

session_start();

// For debugging purposes
$debug_mode = true;
$debug_output = "";

function debug_log($message) {
    global $debug_mode, $debug_output;
    if ($debug_mode) {
        $debug_output .= "<div class='debug-info'>$message</div>";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize inputs
    $login = mysqli_real_escape_string($conn, $_POST['username']);
    $password = $_POST['password'];
    
    debug_log("Attempting login with username/email: $login");
    
    // Let's first check if any users exist in the database
    $check_sql = "SELECT COUNT(*) as total FROM Users";
    $check_result = $conn->query($check_sql);
    $row = $check_result->fetch_assoc();
    $total_users = $row['total'];
    debug_log("Total users in database: $total_users");
    
    // List all usernames for debugging
    $list_sql = "SELECT username, email FROM Users";
    $list_result = $conn->query($list_sql);
    debug_log("Available users:");
    while ($user_row = $list_result->fetch_assoc()) {
        debug_log("Username: {$user_row['username']}, Email: {$user_row['email']}");
    }
    
    // Check if the input is an email or username (case insensitive)
    $sql = "SELECT * FROM Users WHERE LOWER(username) = LOWER(?) OR LOWER(email) = LOWER(?)";
    debug_log("SQL Query: $sql with parameters: $login, $login");
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $login, $login);
    $stmt->execute();
    $result = $stmt->get_result();
    
    debug_log("Query returned " . $result->num_rows . " results");

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        debug_log("User found: ID={$user['user_id']}, Username={$user['username']}");
        
        if (password_verify($password, $user['password'])) {
            debug_log("Password verification successful");
            
            // Set session variables
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['email'] = $user['email'];
            
            echo "Login successful! Redirecting...";
            header("Refresh:2; url=../html/main-home-page.html");
        } else {
            debug_log("Password verification failed");
            echo "Invalid password. Please try again.";
        }
    } else {
        debug_log("No user found with username/email: $login");
        echo "User not found. Please check your username or email.";
    }
    $stmt->close();
    $conn->close();
}

// Display debug information at the end if in debug mode
if (isset($debug_mode) && $debug_mode) {
    echo "<div style='margin-top: 20px; padding: 10px; border: 1px solid #ccc; background-color: #f8f8f8;'>";
    echo "<h3>Debug Information</h3>";
    echo $debug_output;
    echo "</div>";
}
?>