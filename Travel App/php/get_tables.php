<?php
// Include database connection
include 'config.php';

// Get all tables in the database
$sql = "SHOW TABLES FROM `Travel`";
$result = $conn->query($sql);

echo "<h2>Tables in Travel Database:</h2>";
echo "<ul>";

if ($result->num_rows > 0) {
    while($row = $result->fetch_row()) {
        echo "<li>" . $row[0] . "</li>";
        
        // Get table structure
        $table_name = $row[0];
        $structure_sql = "DESCRIBE `$table_name`";
        $structure_result = $conn->query($structure_sql);
        
        if ($structure_result->num_rows > 0) {
            echo "<ul>";
            while($structure_row = $structure_result->fetch_assoc()) {
                echo "<li>" . $structure_row['Field'] . " - " . $structure_row['Type'] . 
                     " (" . ($structure_row['Null'] == "YES" ? "NULL" : "NOT NULL") . ")" .
                     ($structure_row['Key'] == "PRI" ? " PRIMARY KEY" : "") . "</li>";
            }
            echo "</ul>";
        }
    }
} else {
    echo "<li>No tables found in the database</li>";
}

echo "</ul>";

$conn->close();
?>
