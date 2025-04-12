<?php
// ar.php
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

$topsheets = [];
$waybillCount = 0;
$sources = [];

foreach ($trips as $trip) {
    $ts_id = $trip['ts_id'] ? $trip['ts_id'] : "No topsheet";
    if (!isset($topsheets[$ts_id])) {
        $topsheets[$ts_id] = 0;
    }
    $topsheets[$ts_id]++;
    
    $waybillCount++;
    
    if (!empty($trip['source'])) {
        $sources[$trip['source']] = true;
    }
}

$uniqueTopsheets = count($topsheets);
$uniqueSources = count($sources);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PCL - AR</title>
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
            padding: 0px;
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
        .summary-card {
            background-color: #f5f5f5;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
        }
        .summary-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .summary-value {
            font-weight: bold;
            font-size: 18px;
        }
        .reset-btn {
            background-color: #f5f5f5;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 8px 12px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 14px;
        }
        .reset-btn:hover {
            background-color: #e0e0e0;
        }
        /* User info display */
        .user-info {
            color: white;
            text-align: center;
            padding: 10px;
            margin-bottom: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        .user-info .name {
            font-size: 18px;
            font-weight: bold;
        }

        .user-info .role {
            font-size: 14px;
            opacity: 0.8;
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
            <img src="assets/img/logo.png" alt="PCL Logo" style="width: 200px; height: auto;">
        </div>
        
        <div class="table-grid">
            <div class="pending-trip-section">
                <div class="trip-header">
                    <h2>AR</h2>
                </div>
                
                <!-- Summary Statistics Section -->
                <div class="summary-card">
                    <div class="summary-item">
                        <span>Total Topsheets</span>
                        <span class="summary-value"><?php echo $uniqueTopsheets; ?></span>
                    </div>
                    <div class="summary-item">
                        <span>Total Waybills</span>
                        <span class="summary-value"><?php echo $waybillCount; ?></span>
                    </div>
                    <div class="summary-item">
                        <span>Unique Sources</span>
                        <span class="summary-value"><?php echo $uniqueSources; ?></span>
                    </div>
                </div>
                
                <!-- Filter Section -->
                <div class="filter-section">
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
                    <button id="reset-filters" class="submit-btn" style="padding: 8px 12px;">
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
                                <th>POD Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="tripTableBody">
                            <?php
                            if (count($trips) > 0) {
                                foreach ($trips as $trip) {
                                    echo "<tr data-id='" . $trip['cs_id'] . "' data-topsheet='" . ($trip['ts_id'] ? htmlspecialchars($trip['ts_id']) : "No topsheet") . "'>";
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
                                echo "<tr><td colspan='16'>No trips with complete POD found</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Update Trip Modal - with forced hide -->
    <div class="modal-overlay" id="updateTripModal">
        <div class="modal-container">
            <div class="modal-header">
                <h3>Update Trip</h3>
                <span class="close-modal">&times;</span>
            </div>
            <div class="modal-body">
                <!-- Topsheet Display Section -->
                <div class="topsheet-display">
                    <span class="topsheet-label">TOPSHEET:</span>
                    <span class="topsheet-value" id="topsheet-value"></span>
                </div>
                
                <form id="updateTripForm" method="post">
                    <input type="hidden" id="update_id" name="cs_id">
                    <input type="hidden" id="update_topsheet" name="ts_id">
                    
                    <!-- Row 1 -->
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
                    
                    <!-- Row 2 -->
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
                    
                    <!-- Row 3 -->
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
                    
                    <!-- Row 4 -->
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
                    
                    <!-- Row 5 -->
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

                    <!-- Row 6 -->
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

                    <!-- Row 7 -->
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

                    <!-- Row 8 -->
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

                    <!-- Row 9 -->
                    <div class="form-row">
                        <div class="form-group">
                            <label style="color: maroon; font-weight: bold;">Accounts Receivable</label>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="invoice_number">Invoice No.:</label>
                            <input type="number" id="invoice_number" name="invoice_number">
                        </div>
                        <div class="form-group">
                            <label for="ar_date_received">Date Received:</label>
                            <input type="date" id="ar_date_received" name="ar_date_received">
                        </div>
                        <div class="form-group">
                            <label for="remarks">Remarks:</label>
                            <select id="remarks" name="remarks">
                                <option value="">Select Remarks</option>
                                <option value="Waiting for approval (client)">Waiting for approval (client)</option>
                                <option value="Missing Docs">Missing Docs</option>
                                <option value="Done">Done</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="submit-btn">Update AR Details</button>
                        <button type="button" class="cancel-btn">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const topsheetFilter = document.getElementById('topsheet-filter');
        const resetFiltersBtn = document.getElementById('reset-filters');
        
        function applyFilters() {
            const selectedTopsheet = topsheetFilter.value;
            
            document.querySelectorAll('#tripTableBody tr').forEach(row => {
                const rowTopsheet = row.getAttribute('data-topsheet');
                
                if (selectedTopsheet === 'all' || rowTopsheet === selectedTopsheet) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }
        
        topsheetFilter.addEventListener('change', applyFilters);
        
        resetFiltersBtn.addEventListener('click', function() {
            topsheetFilter.value = 'all';
            applyFilters();
        });

        const updateButtons = document.querySelectorAll('.update-btn');
        const updateTripModal = document.getElementById('updateTripModal');
        const closeModalButtons = document.querySelectorAll('.close-modal, .cancel-btn');

        const updateTripForm = document.getElementById('updateTripForm');
        updateTripForm.addEventListener('submit', function(e) {
            e.preventDefault();
            document.getElementById("loading-screen").style.display = "flex";

            const formData = new FormData();
            const inputs = updateTripForm.querySelectorAll('input, select');
            inputs.forEach(input => {
                if (input.name && (input.value || input.value === '0')) {
                    formData.append(input.name, input.value);
                }
            });

            fetch('insert_ar.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById("loading-screen").style.display = "none";
                if (data.success) {
                    alert('AR details saved successfully!');
                    updateTripModal.style.display = 'none';
                    location.reload();
                } else {
                    alert('Error: ' + (data.message || 'Unknown error occurred'));
                }
            })
            .catch(error => {
                document.getElementById("loading-screen").style.display = "none";
                console.error('Error:', error);
                alert('An error occurred while submitting AR details.');
            });
        });

        updateButtons.forEach(button => {
            button.addEventListener('click', function() {
                const csId = this.getAttribute('data-id');
                const topsheet = this.closest('tr').getAttribute('data-topsheet');
                
                document.getElementById("loading-screen").style.display = "flex";

                document.getElementById('topsheet-value').textContent = topsheet;
                document.getElementById('update_topsheet').value = topsheet;

                fetch(`get_trip_details.php?cs_id=${csId}`)
                .then(response => response.json())
                .then(trip => {
                    document.getElementById("loading-screen").style.display = "none";

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

                    updateTripModal.style.display = 'flex';
                })
                .catch(error => {
                    document.getElementById("loading-screen").style.display = "none";
                    console.error('Error:', error);
                    alert('Failed to fetch trip details.');
                });
            });
        });

        closeModalButtons.forEach(button => {
            button.addEventListener('click', function() {
                updateTripModal.style.display = 'none';
            });
        });

        window.addEventListener('click', function(event) {
            if (event.target === updateTripModal) {
                updateTripModal.style.display = 'none';
            }
        });

        
        const logoutLink = document.getElementById('logout-link');
        
        if (logoutLink) {
            logoutLink.addEventListener('click', function(e) {
                e.preventDefault();
                const logoutButton = document.querySelector('.logout-section');
                logoutButton.style.backgroundColor = 'rgba(255, 255, 255, 0.2)';
                document.getElementById("loading-screen").style.display = "flex";
                setTimeout(() => {
                    logoutButton.style.backgroundColor = '';
                    window.location.href = this.getAttribute('href');
                }, 2000);
            });
        }
        
        const pieSlice = document.querySelector('.pie-slice');
        setTimeout(() => {
            pieSlice.style.transition = 'transform 1s ease-out';
            pieSlice.style.transform = 'rotate(135deg)';
        }, 500);
        
        const bars = document.querySelectorAll('.bar');
        bars.forEach((bar, index) => {
            const heights = ['30%', '70%', '50%'];
            bar.style.height = '0';
            setTimeout(() => {
                bar.style.transition = 'height 1s ease-out';
                bar.style.height = heights[index % 3];
            }, 300 + (index * 100));
        });
    });
    </script>
</body>
</html>