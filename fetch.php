<?php
// Database credentials
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "cattle_management_iot_server";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Get sorting parameters
    $sort = isset($_GET['sort']) ? $_GET['sort'] : 'id';
    $order = isset($_GET['order']) ? $_GET['order'] : 'ASC';
    
    // Prepare and execute query
    $sql = "SELECT * FROM distance_data ORDER BY $sort $order";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Distance Data</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f5f5f5;
        }

        .container {
            max-width: 1000px;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: white;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f8f9fa;
            font-weight: bold;
        }

        th a {
            color: #333;
            text-decoration: none;
            display: flex;
            align-items: center;
        }

        th a:hover {
            color: #007bff;
        }

        tr:hover {
            background-color: #f5f5f5;
        }

        .arrow {
            display: inline-block;
            margin-left: 5px;
            font-size: 12px;
        }

        .refresh-btn {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-bottom: 20px;
        }

        .refresh-btn:hover {
            background-color: #0056b3;
        }

        @media (max-width: 600px) {
            table {
                font-size: 14px;
            }

            th, td {
                padding: 8px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Distance Data</h1>
        
        <button class="refresh-btn" onclick="location.reload()">Refresh Data</button>

        <table>
            <thead>
                <tr>
                    <th>
                        <a href="?sort=id&order=<?php echo $sort === 'id' && $order === 'ASC' ? 'DESC' : 'ASC'; ?>">
                            ID 
                            <span class="arrow"><?php echo $sort === 'id' ? ($order === 'ASC' ? '↑' : '↓') : ''; ?></span>
                        </a>
                    </th>
                    <th>
                        <a href="?sort=distance&order=<?php echo $sort === 'distance' && $order === 'ASC' ? 'DESC' : 'ASC'; ?>">
                            Distance 
                            <span class="arrow"><?php echo $sort === 'distance' ? ($order === 'ASC' ? '↑' : '↓') : ''; ?></span>
                        </a>
                    </th>
                    <th>
                        <a href="?sort=timestamp&order=<?php echo $sort === 'timestamp' && $order === 'ASC' ? 'DESC' : 'ASC'; ?>">
                            Timestamp 
                            <span class="arrow"><?php echo $sort === 'timestamp' ? ($order === 'ASC' ? '↑' : '↓') : ''; ?></span>
                        </a>
                    </th>
                    <th>
                        <a href="?sort=status&order=<?php echo $sort === 'status' && $order === 'ASC' ? 'DESC' : 'ASC'; ?>">
                            Status 
                            <span class="arrow"><?php echo $sort === 'status' ? ($order === 'ASC' ? '↑' : '↓') : ''; ?></span>
                        </a>
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result) {
                    foreach($result as $row) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['distance']) . " cm</td>";
                        echo "<td>" . htmlspecialchars($row['timestamp']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['status']) . "</td>";
                        echo "</tr>";
                    }
                }
                ?>
            </tbody>
        </table>
    </div>
    <!-- Auto Reload Script -->
    <script>
        setInterval(function() {
            location.reload();
        }, 5000); // 5000 milliseconds = 5 seconds
    </script>
</body>
</html>