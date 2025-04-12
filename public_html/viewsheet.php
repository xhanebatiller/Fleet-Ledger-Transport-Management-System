<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
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

require_once 'fetch_pod.php';
$trips = getPendingTrips();

// Filter trips to only show those with AR remarks = "Done"
$filteredTrips = array_filter($trips, function($trip) {
    return isset($trip['ar']['remarks']) && $trip['ar']['remarks'] === 'Done';
});

// Calculate statistics for filtering
$topsheets = [];
$sources = [];
$months = [];

foreach ($filteredTrips as $trip) {
    // Topsheet data
    $ts_id = $trip['ts_id'] ? $trip['ts_id'] : "No topsheet";
    if (!isset($topsheets[$ts_id])) {
        $topsheets[$ts_id] = 0;
    }
    $topsheets[$ts_id]++;
    
    // Source data
    if (!empty($trip['source']) && !in_array($trip['source'], $sources)) {
        $sources[] = $trip['source'];
    }
    
    // Month data (format: "F Y" for display, "Y-m" for value)
    $monthYear = date("F Y", strtotime($trip['date']));
    $monthValue = date("Y-m", strtotime($trip['date']));
    if (!isset($months[$monthValue])) {
        $months[$monthValue] = $monthYear;
    }
}

// Sort months in descending order (newest first)
krsort($months);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PCL - VIEW SHEET</title>
    <link rel="icon" href="assets/img/pcl.png" type="image/x-icon">
    <link rel="stylesheet" href="assets/css/waybill.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="assets/js/budget.js"></script>
    <style>
        #updateTripModal {
            display: none !important;
        }
        .budgeted-row {
            background-color: #f0f0f0;
            color: #666;
        }
        .budgeted-row .update-btn {
            opacity: 0.7;
        }
        .filter-section {
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 5px;
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: center;
        }
        .filter-control {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .filter-control label {
            font-weight: bold;
            white-space: nowrap;
        }
        .filter-select {
            padding: 8px 12px;
            border-radius: 4px;
            border: 1px solid #ddd;
            min-width: 200px;
        }
        .topsheet-display {
            background-color: #f7f7f7;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            border: 1px solid #ddd;
            font-weight: bold;
            font-size: 16px;
            text-align: center;
        }
        .topsheet-label {
            color: #333;
            margin-right: 10px;
        }
        .topsheet-value {
            color: #c01818;
            font-size: 18px;
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
    
    <div class="logo-wrapper">
        <img src="assets/img/logo.png" alt="PCL Logo" style="width: 150px; height: auto;">
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
                    echo "Unknown";
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
            <img src="assets/img/logo.png" alt="PCL Logo" style="width: 200px; height: auto;">
        </div>
        
        <div class="table-grid">
            <div class="pending-trip-section">
                <div class="trip-header">
                    <h2>VIEW SHEET</h2>
                </div>
                
                <!-- Filter Section -->
                <div class="filter-section">
                    <div class="filter-control">
                        <label for="month-filter"><i class="fas fa-calendar"></i> Filter by Month:</label>
                        <select id="month-filter" class="filter-select">
                            <option value="all">All Months</option>
                            <?php foreach ($months as $monthValue => $monthYear): ?>
                                <option value="<?php echo htmlspecialchars($monthValue); ?>">
                                    <?php echo htmlspecialchars($monthYear); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-control">
                        <label for="source-filter"><i class="fas fa-building"></i> Filter by Source:</label>
                        <select id="source-filter" class="filter-select">
                            <option value="all">All Sources</option>
                            <?php foreach ($sources as $source): ?>
                                <option value="<?php echo htmlspecialchars($source); ?>">
                                    <?php echo htmlspecialchars($source); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-control">
                        <label for="topsheet-filter"><i class="fas fa-filter"></i> Filter by Topsheet:</label>
                        <select id="topsheet-filter" class="filter-select">
                            <option value="all">All Topsheets</option>
                            <?php foreach ($topsheets as $ts_id => $count): ?>
                                <option value="<?php echo htmlspecialchars($ts_id); ?>">
                                    <?php echo htmlspecialchars($ts_id); ?> 
                                    (<?php echo $count; ?> trips)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <button id="reset-filters" class="submit-btn">
                        <i class="fas fa-sync-alt"></i> Reset Filters
                    </button>
                </div>
                
                <div class="table-responsive">
                    <table class="trip-table">
                        <thead>
                            <tr>
                                <th>Waybill No.</th>
                                <th>Date</th>
                                <th>FO/PO/STO</th>
                                <th>Delivery Type</th>
                                <th>Amount</th>
                                <th>Source</th>
                                <th>Pick Up</th>
                                <th>Drop Off</th>
                                <th>Rate</th>
                                <th>Call Time</th>
                                <th>Situation</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="tripTableBody">
                            <?php
                            if (count($filteredTrips) > 0) {
                                foreach ($filteredTrips as $trip) {
                                    $monthValue = date("Y-m", strtotime($trip['date']));
                                    echo "<tr data-id='" . $trip['cs_id'] . "' data-topsheet='" . ($trip['ts_id'] ? htmlspecialchars($trip['ts_id']) : "No topsheet") . "' data-month='" . $monthValue . "' data-source='" . htmlspecialchars($trip['source']) . "'>";
                                    echo "<td>" . $trip['waybill'] . "</td>";
                                    echo "<td>" . date("F j, Y", strtotime($trip['date'])) . "</td>";
                                    echo "<td>" . $trip['status'] . "</td>";
                                    echo "<td>" . $trip['delivery_type'] . "</td>";
                                    echo "<td> ₱ " . (isset($trip['amount']) ? $trip['amount'] : '') . "</td>";
                                    echo "<td>" . $trip['source'] . "</td>";
                                    echo "<td>" . $trip['pickup'] . "</td>";
                                    echo "<td>" . $trip['dropoff'] . "</td>";
                                    echo "<td>" . $trip['rate'] . "</td>";
                                    echo "<td>" . date("h:i A", strtotime($trip['call_time'])) . "</td>";
                                    echo "<td style='color: green; font-weight: bold;'>" . $trip['pod_status'] . "</td>";
                                    echo "<td><button class='update-btn' data-id='" . $trip['cs_id'] . "'>View Details</button></td>";
                                    echo "</tr>";
                                }
                            } else {
                                echo "<tr><td colspan='12'>No trips with AR status 'Done' found</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Update Trip Modal -->
    <div class="modal-overlay" id="updateTripModal">
        <div class="modal-container">
            <div class="modal-header">
                <h3>Update Trip</h3>
                <span class="close-modal">&times;</span>
            </div>
            <div class="modal-body">
                <div class="topsheet-display">
                    <span class="topsheet-label">TOPSHEET:</span>
                    <span class="topsheet-value" id="topsheet-value"></span>
                </div>
                
                <form id="updateTripForm" method="post">
                    <input type="hidden" id="update_id" name="cs_id">
                    <input type="hidden" id="update_topsheet" name="ts_id">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="update_waybill">Waybill No:</label>
                            <input type="number" id="update_waybill" name="waybill" readonly>
                        </div>
                        <div class="form-group">
                            <label for="update_date">Date:</label>
                            <input type="date" id="update_date" name="date" readonly>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="update_status">FO/PO/STO:</label>
                            <select id="update_status" name="status" readonly>
                                <option value="">Select Type</option>
                                <option value="Freight Order">Freight Order</option>
                                <option value="Purchase Order">Purchase Order</option>
                                <option value="Stock Transfer Order">Stock Transfer Order</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="update_delivery_type">Delivery Type:</label>
                            <input type="text" id="update_delivery_type" name="delivery_type" readonly>
                        </div>
                        <div class="form-group">
                            <label for="update_amount">Amount:</label>
                            <input type="text" id="update_amount" name="amount" readonly>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="update_source">Source:</label>
                            <input type="text" id="update_source" name="source" readonly>
                        </div>
                        <div class="form-group">
                            <label for="update_pickup">Pick Up:</label>
                            <input type="text" id="update_pickup" name="pickup" readonly>
                        </div>
                        <div class="form-group">
                            <label for="update_dropoff">Drop Off:</label>
                            <input type="text" id="update_dropoff" name="dropoff" readonly>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="update_rate">Rate:</label>
                            <input type="text" id="update_rate" name="rate" readonly>
                        </div>
                        <div class="form-group">
                            <label for="update_call_time">Call Time:</label>
                            <input type="time" id="update_call_time" name="call_time" readonly>
                        </div>
                        <div class="form-group">
                            <label for="update_truck_id">Truck Type:</label>
                            <select id="update_truck_id" name="truck_id" readonly>
                                <option value="">Select Truck</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="update_driver">Driver:</label>
                            <select id="update_driver" name="driver" readonly>
                                <option value="">Select Driver</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="update_helper1">Helper 1:</label>
                            <select id="update_helper1" name="helper1" readonly>
                                <option value="">Select Helper 1</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="update_helper2">Helper 2:</label>
                            <select id="update_helper2" name="helper2" readonly>
                                <option value="">Select Helper 2</option>
                            </select>
                        </div>
                    </div>
                    <hr style="margin-bottom: 15px;">

                    <div class="form-row">
                        <div class="form-group">
                            <label style="color: maroon; font-weight: bold;">Budget</label>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="fuelfee">Fuel Fee:</label>
                            <input type="number" id="fuelfee" name="fuelfee" readonly>
                        </div>
                        <div class="form-group">
                            <label for="tollfee">Toll Fee:</label>
                            <input type="number" id="tollfee" name="tollfee" readonly>
                        </div>
                        <div class="form-group">
                            <label for="parkingfee">Parking Fee:</label>
                            <input type="number" id="parkingfee" name="parkingfee" readonly>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="rorofarefee">Roro Fare:</label>
                            <input type="number" id="rorofarefee" name="rorofarefee" readonly>
                        </div>
                        <div class="form-group">
                            <label for="budgetrelease">Total Budget Release:</label>
                            <input type="number" id="budgetrelease" name="budgetrelease" readonly>
                        </div>
                    </div>

                    <hr style="margin-bottom: 15px;">

                    <div class="form-row">
                        <div class="form-group">
                            <label style="color: maroon; font-weight: bold;">Proof of Delivery</label>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="pod_status">POD Status:</label>
                            <select id="pod_status" name="pod_status" readonly>
                                <option value="">Select Status</option>
                                <option value="Complete">Complete</option>
                                <option value="Incomplete">Incomplete</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="date_received">Date Received:</label>
                            <input type="date" id="date_received" name="date_received" readonly>
                        </div>
                        <div class="form-group">
                            <label for="Remarks">Remarks:</label>
                            <select id="Remarks" name="Remarks" readonly>
                                <option value="">Select Remarks</option>
                                <option value="No Stamp">No Stamp</option>
                                <option value="No Sign">No Sign</option>
                                <option value="No Counter">No Counter</option>
                                <option value="Missing Docs">Missing Docs</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="pod_transmittal">POD Transmittal:</label>
                            <input type="text" id="pod_transmittal" name="pod_transmittal" readonly>
                        </div>
                        <div class="form-group">
                            <label for="date_transmitted">Date Transmitted:</label>
                            <input type="date" id="date_transmitted" name="date_transmitted" readonly>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label style="color: maroon; font-weight: bold;">Mileage</label>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="odo_out">Mileage Out:</label>
                            <input type="number" id="odo_out" name="odo_out" readonly>
                        </div>
                        <div class="form-group">
                            <label for="odo_in">Mileage In:</label>
                            <input type="number" id="odo_in" name="odo_in" readonly>
                        </div>
                        <div class="form-group">
                            <label for="odo_total">Total Mileage:</label> 
                            <input type="number" id="odo_total" name="odo_total" readonly>
                        </div>
                    </div>

                    <hr style="margin-bottom: 15px;">

                    <div class="form-row">
                        <div class="form-group">
                            <label style="color: maroon; font-weight: bold;">A R</label>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="invoice_number">Invoice No.:</label>
                            <input type="number" id="invoice_number" name="invoice_number" readonly>
                        </div>
                        <div class="form-group">
                            <label for="date_received">Date Received:</label>
                            <input type="date" id="ar_date_received" name="date_received" readonly>
                        </div>
                        <div class="form-group">
                            <label for="remarks">Remarks:</label>
                            <select id="remarks" name="remarks" readonly>
                                <option value="">Select Remarks</option>
                                <option value="Waiting for approval (client)">Waiting for approval (client)</option>
                                <option value="Missing Docs">Missing Docs</option>
                                <option value="Done">Done</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="button" class="cancel-btn">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Get filter elements
        const monthFilter = document.getElementById('month-filter');
        const sourceFilter = document.getElementById('source-filter');
        const topsheetFilter = document.getElementById('topsheet-filter');
        const resetFiltersBtn = document.getElementById('reset-filters');
        
        function applyFilters() {
            const selectedMonth = monthFilter.value;
            const selectedSource = sourceFilter.value;
            const selectedTopsheet = topsheetFilter.value;
            
            document.querySelectorAll('#tripTableBody tr').forEach(row => {
                const rowMonth = row.getAttribute('data-month');
                const rowSource = row.getAttribute('data-source');
                const rowTopsheet = row.getAttribute('data-topsheet');
                
                const monthMatch = selectedMonth === 'all' || rowMonth === selectedMonth;
                const sourceMatch = selectedSource === 'all' || rowSource === selectedSource;
                const topsheetMatch = selectedTopsheet === 'all' || rowTopsheet === selectedTopsheet;
                
                if (monthMatch && sourceMatch && topsheetMatch) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
        
        // Add event listeners for all filters
        monthFilter.addEventListener('change', applyFilters);
        sourceFilter.addEventListener('change', applyFilters);
        topsheetFilter.addEventListener('change', applyFilters);
        
        // Reset filters functionality
        resetFiltersBtn.addEventListener('click', function() {
            monthFilter.value = 'all';
            sourceFilter.value = 'all';
            topsheetFilter.value = 'all';
            applyFilters();
        });

        // Modal functionality
        const updateButtons = document.querySelectorAll('.update-btn');
        const updateTripModal = document.getElementById('updateTripModal');
        const closeModalButtons = document.querySelectorAll('.close-modal, .cancel-btn');

        updateButtons.forEach(button => {
            button.addEventListener('click', function() {
                const csId = this.getAttribute('data-id');
                const topsheet = this.closest('tr').getAttribute('data-topsheet');
                
                // Show loading screen
                document.getElementById("loading-screen").style.display = "flex";

                // Update topsheet display immediately
                document.getElementById('topsheet-value').textContent = topsheet;
                document.getElementById('update_topsheet').value = topsheet;

                // Fetch trip details
                fetch(`get_trip_details.php?cs_id=${csId}`)
                .then(response => response.json())
                .then(trip => {
                    // Hide loading screen
                    document.getElementById("loading-screen").style.display = "none";

                    // Populate form fields
                    document.getElementById('update_id').value = trip.cs_id;
                    document.getElementById('update_waybill').value = trip.waybill || '';
                    document.getElementById('update_date').value = trip.date || '';
                    document.getElementById('update_status').value = trip.status || '';
                    document.getElementById('update_delivery_type').value = trip.delivery_type || '';
                    document.getElementById('update_amount').value = trip.amount || '';
                    document.getElementById('update_source').value = trip.source || '';
                    document.getElementById('update_pickup').value = trip.pickup || '';
                    document.getElementById('update_dropoff').value = trip.dropoff || '';
                    document.getElementById('update_rate').value = trip.rate || '';
                    document.getElementById('update_call_time').value = trip.call_time || '';

                    // Fetch and populate truck options
                    fetch('get_trucks.php')
                        .then(response => response.json())
                        .then(trucks => {
                            const truckSelect = document.getElementById('update_truck_id');
                            truckSelect.innerHTML = '<option value="">Select Truck</option>';
                            trucks.forEach(truck => {
                                const option = document.createElement('option');
                                option.value = truck.id;
                                option.textContent = truck.name;
                                option.selected = truck.id == trip.truck_id;
                                truckSelect.appendChild(option);
                            });
                        });

                    // Check if budget details exist and pre-fill
                    if (trip.budget) {
                        document.getElementById('fuelfee').value = trip.budget.fuelfee || '';
                        document.getElementById('tollfee').value = trip.budget.tollfee || '';
                        document.getElementById('parkingfee').value = trip.budget.parkingfee || '';
                        document.getElementById('rorofarefee').value = trip.budget.rorofarefee || '';
                        document.getElementById('budgetrelease').value = trip.budget.budgetrelease || '';
                    }

                    if (trip.pod) {
                        document.getElementById('pod_status').value = trip.pod.pod_status || '';
                        document.getElementById('date_received').value = trip.pod.date_received || '';
                        document.getElementById('Remarks').value = trip.pod.Remarks || '';
                        document.getElementById('pod_transmittal').value = trip.pod.pod_transmittal || '';
                        document.getElementById('date_transmitted').value = trip.pod.date_transmitted || '';
                    }

                    if (trip.pod) {
                        document.getElementById('odo_out').value = trip.pod.odo_out || '';
                        document.getElementById('odo_in').value = trip.pod.odo_in || '';
                        document.getElementById('odo_total').value = trip.pod.odo_total || '';
                    }

                    if (trip.ar) {
                        document.getElementById('invoice_number').value = trip.ar.invoice_number || '';
                        document.getElementById('ar_date_received').value = trip.ar.date_received || '';
                        document.getElementById('remarks').value = trip.ar.remarks || '';
                    }

                    // Show the modal
                    updateTripModal.style.display = 'flex';
                })
                .catch(error => {
                    document.getElementById("loading-screen").style.display = "none";
                    console.error('Error:', error);
                    alert('Failed to fetch trip details.');
                });
            });
        });

        // Close modal buttons
        closeModalButtons.forEach(button => {
            button.addEventListener('click', function() {
                updateTripModal.style.display = 'none';
            });
        });

        // Close modal when clicking outside
        window.addEventListener('click', function(event) {
            if (event.target === updateTripModal) {
                updateTripModal.style.display = 'none';
            }
        });

    });
    </script>
</body>
</html>