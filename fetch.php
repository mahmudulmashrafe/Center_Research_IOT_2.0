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

// Fetch latest distance data
$result = $conn->query("SELECT distance FROM distance_data_2 ORDER BY id DESC LIMIT 1");
$distance = ($result->num_rows > 0) ? $result->fetch_assoc()['distance'] : "N/A";

// Fetch latest temperature and humidity data
$result = $conn->query("SELECT temperature, humidity FROM temp_humidity_data ORDER BY id DESC LIMIT 1");
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $temperature = $row['temperature'];
    $humidity = $row['humidity'];
} else {
    $temperature = "N/A";
    $humidity = "N/A";
}

// Fetch Sensor 1 (Distance Sensor) status
$result = $conn->query("SELECT status FROM distance_table_status ORDER BY id DESC LIMIT 1");
$sensor1_status = ($result->num_rows > 0) ? $result->fetch_assoc()['status'] : -1;

// Fetch Sensor 2 (DHT11 Sensor) status
$result = $conn->query("SELECT status FROM temp_humidity_table_status ORDER BY id DESC LIMIT 1");
$sensor2_status = ($result->num_rows > 0) ? $result->fetch_assoc()['status'] : -1;

// Convert status to text
$sensor1_status_text = ($sensor1_status == 1) ? "On" : "Off";
$sensor2_status_text = ($sensor2_status == 1) ? "On" : "Off";

// Close connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sensor Data Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .status-on { background-color: #28a745; color: white; }
        .status-off { background-color: #dc3545; color: white; }
        .card { border-radius: 15px; box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.1); }
        .container { margin-top: 50px; }
    </style>
    <script>
        setTimeout(function() {
            location.reload();
        }, 5000); // Refresh the page every 5 seconds
    </script>
</head>
<body class="bg-light">
    <div class="container">
        <h2 class="text-center mb-4">📊 Sensor Data Dashboard</h2>

        <div class="row justify-content-center">
            <!-- Distance Data -->
            <div class="col-md-4">
                <div class="card text-center p-3">
                    <h5>📏 Distance</h5>
                    <h2 class="text-primary"><?= $distance ?> cm</h2>
                </div>
            </div>

            <!-- Temperature Data -->
            <div class="col-md-4">
                <div class="card text-center p-3">
                    <h5>🌡 Temperature</h5>
                    <h2 class="text-danger"><?= $temperature ?> °C</h2>
                </div>
            </div>

            <!-- Humidity Data -->
            <div class="col-md-4">
                <div class="card text-center p-3">
                    <h5>💧 Humidity</h5>
                    <h2 class="text-info"><?= $humidity ?> %</h2>
                </div>
            </div>
        </div>

        <div class="row justify-content-center mt-4">
            <!-- Sensor 1 Status -->
            <div class="col-md-5">
                <div class="card text-center p-3 <?= ($sensor1_status == 1) ? 'status-on' : 'status-off' ?>">
                    <h5>🟢 Sensor 1 (Distance Sensor)</h5>
                    <h2><?= $sensor1_status_text ?></h2>
                </div>
            </div>

            <!-- Sensor 2 Status -->
            <div class="col-md-5">
                <div class="card text-center p-3 <?= ($sensor2_status == 1) ? 'status-on' : 'status-off' ?>">
                    <h5>🟡 Sensor 2 (DHT11 Sensor)</h5>
                    <h2><?= $sensor2_status_text ?></h2>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
