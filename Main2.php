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

// Toggle status when button is pressed
if (isset($_GET['toggle'])) {
    // Get the current status from temp_humidity_table_status
    $result = $conn->query("SELECT status FROM temp_humidity_table_status ORDER BY id DESC LIMIT 1");
    $currentStatus = ($result->num_rows > 0) ? $result->fetch_assoc()['status'] : 0;
    
    // Toggle the status
    $newStatus = ($currentStatus == 1) ? 0 : 1;

    // Insert new status into the table
    $conn->query("INSERT INTO temp_humidity_table_status (status) VALUES ($newStatus)");
    echo $newStatus; // Send back the updated status
}

// Get latest status for ESP8266
if (isset($_GET['getStatus'])) {
    $result = $conn->query("SELECT status FROM temp_humidity_table_status ORDER BY id DESC LIMIT 1");
    $currentStatus = ($result->num_rows > 0) ? $result->fetch_assoc()['status'] : 0;
    echo $currentStatus; // Return the current status
}

// Insert temperature and humidity data if status is 1
if (isset($_GET['temperature']) && isset($_GET['humidity'])) {
    // Get current status
    $result = $conn->query("SELECT status FROM temp_humidity_table_status ORDER BY id DESC LIMIT 1");
    $currentStatus = ($result->num_rows > 0) ? $result->fetch_assoc()['status'] : 0;

    if ($currentStatus == 1) {
        $temperature = floatval($_GET['temperature']);
        $humidity = floatval($_GET['humidity']);
        $sql = "INSERT INTO temp_humidity_data (temperature, humidity) VALUES ($temperature, $humidity)";
        echo ($conn->query($sql) === TRUE) ? "Data successfully inserted." : "Error: " . $conn->error;
    } else {
        echo "Status is 0, no data recorded.";
    }
}

$conn->close();
?>
