<?php
// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "Travel";

try {
    // Create connection using PDO
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    // Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // SQL to create BusOperators table
    $sql = "CREATE TABLE IF NOT EXISTS BusOperators (
        operator_id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        company_name VARCHAR(100) NOT NULL,
        phone VARCHAR(20),
        address TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    // Execute query
    $conn->exec($sql);
    echo "BusOperators table created successfully<br>";
    
    // Insert a sample operator for testing
    $name = "Smita Operator";
    $email = "smita@example.com";
    $password = "1234"; // In a real app, this would be hashed
    $company_name = "Travel Services";
    
    // Check if the sample operator already exists
    $check = $conn->prepare("SELECT operator_id FROM BusOperators WHERE email = ?");
    $check->execute([$email]);
    
    if ($check->rowCount() == 0) {
        // Insert sample operator
        $stmt = $conn->prepare("INSERT INTO BusOperators (name, email, password, company_name) VALUES (?, ?, ?, ?)");
        
        if ($stmt->execute([$name, $email, $password, $company_name])) {
            echo "Sample operator created successfully<br>";
        } else {
            echo "Error creating sample operator<br>";
        }
    } else {
        echo "Sample operator already exists<br>";
    }
    
    // Check if BusServices table exists
    $tableCheck = $conn->query("SHOW TABLES LIKE 'BusServices'");
    
    if ($tableCheck->rowCount() > 0) {
        // Check if operator_id column exists in BusServices table
        $columnCheck = $conn->query("SHOW COLUMNS FROM BusServices LIKE 'operator_id'");
        
        if ($columnCheck->rowCount() == 0) {
            // Add operator_id column to BusServices table
            $alterSql = "ALTER TABLE BusServices ADD COLUMN operator_id INT DEFAULT 1";
            $conn->exec($alterSql);
            echo "operator_id column added to BusServices table<br>";
        } else {
            echo "operator_id column already exists in BusServices table<br>";
        }
    } else {
        echo "BusServices table does not exist yet<br>";
    }
    
    echo "<strong>All operations completed successfully!</strong>";
    
} catch(PDOException $e) {
    echo "<strong>Database Error:</strong> " . $e->getMessage() . "<br>";
} finally {
    // Close connection
    $conn = null;
}
?>
