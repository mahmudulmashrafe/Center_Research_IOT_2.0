<?php
// Database credentials
$servername = "localhost"; 
$username = "root";        
$password = "";            
$dbname = "cattle_management_iot_server";  

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Update status to 1
$sql = "UPDATE distance_table_status SET status = 1 ORDER BY id DESC LIMIT 1"; // Assuming you want to update the most recent row
if ($conn->query($sql) === TRUE) {
    echo "Status changed to 1.";
} else {
    echo "Error updating status: " . $conn->error;
}

// Close connection
$conn->close();
?>
