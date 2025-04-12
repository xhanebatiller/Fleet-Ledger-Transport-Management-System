<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Database configuration
require_once "connection.php"; // Make sure this file contains your PDO connection

try {
    // Create connection if it doesn't exist
    if (!isset($pdo)) {
        $pdo = new PDO("mysql:host=" . DB_SERVER . ";dbname=" . DB_NAME, DB_USERNAME, DB_PASSWORD);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    // Get counts of available resources
    $availableTrucks = 0;
    $availableDrivers = 0;
    $availableHelpers = 0;

    // Get total counts
    $totalTrucks = $pdo->query("SELECT COUNT(*) FROM truck WHERE status = 'ACTIVE'")->fetchColumn();
    $totalDrivers = $pdo->query("SELECT COUNT(*) FROM driver WHERE status = 'ACTIVE'")->fetchColumn();
    $totalHelpers = $pdo->query("SELECT COUNT(*) FROM helper1 WHERE status = 'ACTIVE'")->fetchColumn() + 
                    $pdo->query("SELECT COUNT(*) FROM helper2 WHERE status = 'ACTIVE'")->fetchColumn();

    // Get assigned counts from customerservice where date is today or future
    $assignedTrucks = $pdo->query("SELECT COUNT(DISTINCT truck_id) FROM customerservice WHERE date >= CURDATE()")->fetchColumn();
    $assignedDrivers = $pdo->query("SELECT COUNT(DISTINCT driver) FROM customerservice WHERE date >= CURDATE()")->fetchColumn();
    $assignedHelpers = $pdo->query("SELECT COUNT(DISTINCT helper1) + COUNT(DISTINCT helper2) FROM customerservice WHERE date >= CURDATE()")->fetchColumn();

    // Calculate available counts
    $availableTrucks = $totalTrucks - $assignedTrucks;
    $availableDrivers = $totalDrivers - $assignedDrivers;
    $availableHelpers = $totalHelpers - $assignedHelpers;

} catch (PDOException $e) {
    // Handle error
    error_log("Database error: " . $e->getMessage());
    // Set all counts to 0 if there's an error
    $availableTrucks = 0;
    $availableDrivers = 0;
    $availableHelpers = 0;
}

$u_id = $_SESSION["u_id"];
$permissions = [
    1 => ["all_access" => true], 
    2 => ["waybill.php" => true, "dispatcher.php" => true, "viewsheet.php" => true],
    3 => ["pod.php" => true],
    4 => ["pod.php" => true, "ar.php" => true, "viewsheet.php" => true],
    5 => ["queries.php" => true, "viewsheet.php" => true],
    6 => ["budget.php" => true, "viewsheet.php" => true],
    7 => ["waybill.php" => true, "dispatcher.php" => true, "viewsheet.php" => true],
    8 => ["dispatcher.php" => true],
    9 => ["pod.php" => true]
];

function hasAccess($u_id, $page, $permissions) {
    return isset($permissions[$u_id]["all_access"]) || 
           (isset($permissions[$u_id][$page]) && $permissions[$u_id][$page]);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PCL - AVAILABLE TDH</title>
    <link rel="icon" href="assets/img/pcl.png" type="image/x-icon">
    <link rel="stylesheet" href="assets/css/landingPage.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="assets/js/landingPage.js"></script>
    <style>
        .availability-chart {
            width: 100%;
            max-width: 300px;
            margin: 0 auto;
        }
        .menu-item .count-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: #2ecc71;
            color: white;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: bold;
        }
        .menu-item {
            position: relative;
        }
        .error-message {
            color: red;
            text-align: center;
            margin: 10px 0;
        }
    </style>
</head>
<body oncontextmenu="return false" controlslist="nodownload">
<div class="mobile-toggle">☰</div>
    <div class="overlay"></div>
    
    <div class="loading-screen" id="loading-screen">
        <div class="loader"></div>
        <span>Loading...</span>
    </div>
    
    <div class="sidebar">
        <div class="user-info">
            <div class="name"><?php echo htmlspecialchars($_SESSION["fullname"]); ?></div>
            <div class="role">Position: 
            <?php 
                $conn = new mysqli($servername, $username, $password, $dbname);
                if ($conn->connect_error) {
                    die("Connection failed: " . $conn->connect_error);
                }
                
                $sql = "SELECT position FROM usertype WHERE u_id = " . $_SESSION["u_id"];
                $result = $conn->query($sql);
                
                if ($result->num_rows > 0) {
                    $row = $result->fetch_assoc();
                    echo htmlspecialchars($row["position"]);
                } else {
                    echo "Unknown"; // Fallback if position not found
                }
                
                $conn->close();
            ?>
            </div>
        </div>
        
        <div>
            <div class="metric-section" data-href="landingPage.php">
                <div class="chart-container">
                    <div class="pie-chart">
                        <div class="pie-slice"></div>
                    </div>
                </div>
                <div class="metric-title">MAIN</div>
            </div>
            <div class="metric-section" data-href="available.php">
                <div class="bar-container">
                    <div class="bar bar-1"></div>
                    <div class="bar bar-2"></div>
                    <div class="bar bar-3"></div>
                </div>
                <div class="metric-title">AVAILABLE TDH</div>
            </div>
            <div class="metric-section" data-href="references.php">
                <div class="chart-container">
                    <div class="people-icon">
                        <div class="people-head"></div>
                        <div class="people-body"></div>
                    </div>
                </div>
                <div class="metric-title">REFERENCES</div>
            </div>
        </div>
        
        <a href="logout.php" class="logout-link" id="logout-link">
            <div class="logout-section">
                <div class="logout-icon">←</div>
                <span>Log Out</span>
            </div>
        </a>
    </div>
    <div class="main-content">
        <div class="logo-container">
            <img src="assets/img/logo.png" alt="PCL Logo" style="margin-right: 10px; width: 320px; height: auto;">
        </div>
        
        <!-- Display error message if counts are 0 (which might indicate a database error) -->
        <?php if ($availableTrucks === 0 && $availableDrivers === 0 && $availableHelpers === 0): ?>
            <div class="error-message">
                Unable to load availability data. Please try again later.
            </div>
        <?php else: ?>
            <!-- Availability Chart -->
            <div class="availability-chart">
                <canvas id="availabilityChart"></canvas>
            </div>
        <?php endif; ?>
        <br>
        <div class="menu-grid">
            <a href="trucks.php" class="menu-item" data-href="topsheet2.html">
                <div class="menu-icon truck">
                    <div class="wheel-left"></div>
                    <div class="wheel-right"></div>
                </div>
                <div class="menu-label">Trucks</div>
                <div class="count-badge"><?php echo $availableTrucks; ?></div>
            </a>
            <a href="drivers.php" class="menu-item" data-href="topsheet2.html">
                <div class="menu-icon account"></div>
                <div class="menu-label">Drivers</div>
                <div class="count-badge"><?php echo $availableDrivers; ?></div>
            </a>
            <a href="helpers.php" class="menu-item" data-href="topsheet2.html">
                <div class="menu-icon account"></div>
                <div class="menu-label">Helpers</div>
                <div class="count-badge"><?php echo $availableHelpers; ?></div>
            </a>
        </div>
        <div style="text-align: center; margin-top: 20px; font-weight: bold;">
            Here are the numbers of active trucks, drivers, and helpers:
        </div>
    </div>

    <script>
        // Chart.js implementation - only if we have data
        <?php if (!($availableTrucks === 0 && $availableDrivers === 0 && $availableHelpers === 0)): ?>
        document.addEventListener('DOMContentLoaded', function() {
            const ctx = document.getElementById('availabilityChart').getContext('2d');
            const availabilityChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Trucks', 'Drivers', 'Helpers'],
                    datasets: [{
                        label: 'Available Resources',
                        data: [
                            <?php echo $availableTrucks; ?>,
                            <?php echo $availableDrivers; ?>,
                            <?php echo $availableHelpers; ?>
                        ],
                        backgroundColor: [
                            'rgba(54, 162, 235, 0.7)',
                            'rgba(255, 99, 132, 0.7)',
                            'rgba(75, 192, 192, 0.7)'
                        ],
                        borderColor: [
                            'rgba(54, 162, 235, 1)',
                            'rgba(255, 99, 132, 1)',
                            'rgba(75, 192, 192, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Available Count'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Resource Type'
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.parsed.y + ' available';
                                }
                            }
                        }
                    }
                }
            });
        });
        <?php endif; ?>
    </script>
</body>
</html>