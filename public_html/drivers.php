<?php 
// drivers.php
session_start();

require_once 'connection.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['adddriver'])) {
        // Add new driver
        $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
        $contact = mysqli_real_escape_string($conn, $_POST['contact']);
        $status = mysqli_real_escape_string($conn, $_POST['status']);
        
        // Validate contact number (must start with 09 and be 11 digits)
        if(!preg_match('/^09\d{9}$/', $contact)) {
            $_SESSION['error_message'] = "Invalid contact number format (must start with 09 and be 11 digits)";
            header("location: drivers.php");
            exit();
        }
        
        $query = "INSERT INTO driver (fullname, contact, status) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($conn, $query);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "sss", $fullname, $contact, $status);
            
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['success_message'] = "Driver added successfully";
            } else {
                $_SESSION['error_message'] = "Error: " . mysqli_stmt_error($stmt);
            }
        } else {
            $_SESSION['error_message'] = "Error: " . mysqli_error($conn);
        }
        
        header("location: drivers.php");
        exit();
    }
    elseif (isset($_POST['updatedata'])) {
        // Update driver
        $driver_id = mysqli_real_escape_string($conn, $_POST['driver_id']);
        $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
        $contact = mysqli_real_escape_string($conn, $_POST['contact']);
        $status = mysqli_real_escape_string($conn, $_POST['status']);
        
        if(!preg_match('/^09\d{9}$/', $contact)) {
            $_SESSION['error_message'] = "Invalid contact number format (must start with 09 and be 11 digits)";
            header("location: drivers.php");
            exit();
        }
        
        $query = "UPDATE driver SET fullname = ?, contact = ?, status = ? WHERE driver_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "sssi", $fullname, $contact, $status, $driver_id);
            
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['success_message'] = "Driver information updated successfully";
            } else {
                $_SESSION['error_message'] = "Error: " . mysqli_stmt_error($stmt);
            }
        } else {
            $_SESSION['error_message'] = "Error: " . mysqli_error($conn);
        }
        
        header("location: drivers.php");
        exit();
    }
    elseif (isset($_POST['deletedata'])) {
        // Delete driver
        $driver_id = mysqli_real_escape_string($conn, $_POST['driver_id']);
        
        $query = "DELETE FROM driver WHERE driver_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $driver_id);
            
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['success_message'] = "Driver deleted successfully";
            } else {
                $_SESSION['error_message'] = "Error: " . mysqli_stmt_error($stmt);
            }
        } else {
            $_SESSION['error_message'] = "Error: " . mysqli_error($conn);
        }
        
        header("location: drivers.php");
        exit();
    }
}

// Initialize messages
$success_message = '';
$error_message = '';

// Get messages from session
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
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

$query = "SELECT * FROM driver ORDER BY driver_id";
$result = mysqli_query($conn, $query);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PCL - DRIVERS</title>
    <link rel="icon" href="assets/img/pcl.png" type="image/x-icon">
    <link rel="stylesheet" href="assets/css/landingPage.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <script src="assets/js/landingPage.js"></script>
    <style>
        /* Base styles */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow-x: hidden;
        }
        
        /* Alert styles */
        .alert {
            padding: 15px;
            margin: 20px auto;
            width: 90%;
            max-width: 1200px;
            border: 1px solid transparent;
            border-radius: 4px;
            text-align: center;
        }
        .alert-success {
            color: #3c763d;
            background-color: #dff0d8;
            border-color: #d6e9c6;
        }
        .alert-danger {
            color: #a94442;
            background-color: #f2dede;
            border-color: #ebccd1;
        }
        
        /* Table container */
        .driver-table-container {
            margin: 20px auto;
            width: 90%;
            max-width: 1500px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
            background-color: #fff;
        }
        
        .table-header {
            background-color: rgba(106, 0, 11, 0.79);
            color: white;
            padding: 15px;
            text-align: center;
            font-size: 1.5rem;
            font-weight: bold;
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
        }
        
        .add-driver-btn {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            white-space: nowrap;
            margin-top: 10px;
        }
        
        .add-driver-btn:hover {
            background-color: #218838;
        }
        
        .driver-table {
            width: 100%;
            border-collapse: collapse;
            box-shadow: 0 2px 3px rgba(0, 0, 0, 0.1);
        }
        
        .driver-table th {
            background-color: maroon;
            color: white;
            font-weight: bold;
            text-align: center;
            padding: 12px 15px;
        }
        
        .driver-table td {
            padding: 10px 15px;
            text-align: center;
            border-bottom: 1px solid #e0e0e0;
            word-break: break-word;
        }

        .driver-table td.tall-cell {
            height: 170px;
            vertical-align: top;
        }

        
        .driver-table tr:hover {
            background-color: #f5f5f5;
        }
        
        .driver-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            text-decoration: none;
            display: inline-block;
            margin: 2px;
            white-space: nowrap;
        }
        
        .btn-primary {
            background-color: #007bff;
            color: white;
        }
        
        .btn-success {
            background-color: #28a745;
            color: white;
        }
        
        .btn-danger {
            background-color: #dc3545;
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #0069d9;
        }
        
        .btn-success:hover {
            background-color: #218838;
        }
        
        .btn-danger:hover {
            background-color: #c82333;
        }
        
        .table-responsive {
            overflow-x: auto;
            border-radius: 0px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            max-height: 900px;
            overflow-y: auto;
        }

        .driver-table thead {
            position: sticky;
            top: 0;
            background-color: maroon;
            color: white;
            z-index: 10;
        }

        /* Error message styling */
        .error-message {
            color: red;
            font-size: 0.8rem;
            margin-top: 5px;
            display: none;
        }
        
        /* Modal styles */
        .modal-content {
            border-radius: 10px;
        }
        
        .modal-header {
            background-color: rgba(106, 0, 11, 0.79);
            color: white;
            border-bottom: none;
        }
        
        .modal-title {
            font-weight: bold;
        }
        
        .close {
            color: white;
            opacity: 1;
        }
        
        .form-group label {
            font-weight: 500;
        }
        
        /* Notification styles */
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 25px;
            border-radius: 5px;
            color: white;
            z-index: 1000;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            animation: slideIn 0.5s, fadeOut 0.5s 2.5s forwards;
        }

        .notification.success {
            background-color: #28a745;
        }

        .notification.error {
            background-color: #dc3545;
        }

        @keyframes slideIn {
            from { right: -300px; opacity: 0; }
            to { right: 20px; opacity: 1; }
        }

        @keyframes fadeOut {
            from { opacity: 1; }
            to { opacity: 0; }
        }
        
        /* Responsive adjustments */
        @media screen and (max-width: 992px) {
            .driver-table-container {
                width: 95%;
            }
            
            .table-header {
                flex-direction: column;
                align-items: stretch;
            }
            
            .table-header span {
                margin-bottom: 10px;
            }
        }
        
        @media screen and (max-width: 768px) {
            .driver-table-container {
                width: 98%;
                margin: 10px auto;
            }
            
            .driver-table th, 
            .driver-table td {
                padding: 8px 10px;
                font-size: 0.9rem;
            }
            
            .btn {
                padding: 5px 8px;
                font-size: 0.8rem;
            }
            
            .modal-dialog {
                margin: 10px auto;
            }
        }
        
        @media screen and (max-width: 576px) {
            .driver-table th, 
            .driver-table td {
                padding: 6px 8px;
                font-size: 0.85rem;
            }
            
            .btn {
                padding: 4px 6px;
                font-size: 0.75rem;
                margin: 1px;
            }
            
            .table-header {
                padding: 10px;
                font-size: 1.2rem;
            }
            
            .add-driver-btn {
                padding: 6px 12px;
                font-size: 0.9rem;
            }
            
            .modal-content {
                margin: 0 10px;
            }
            
            .action-buttons {
                display: flex;
                flex-direction: column;
            }
        }
        
        @media screen and (max-width: 400px) {
            .driver-table td {
                padding: 4px 6px;
            }
            
            .btn {
                display: block;
                width: 100%;
                margin: 5px 0;
            }
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
                $sql = "SELECT position FROM usertype WHERE u_id = " . $_SESSION["u_id"];
                $positionResult = mysqli_query($conn, $sql);
                
                if ($positionResult && mysqli_num_rows($positionResult) > 0) {
                    $row = mysqli_fetch_assoc($positionResult);
                    echo htmlspecialchars($row["position"]);
                } else {
                    echo "Unknown";
                }
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
        
        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <div class="driver-table-container">
            <div class="table-header">
                <span>Driver Information</span>
                <button type="button" class="add-driver-btn" data-toggle="modal" data-target="#addDriverModal">
                    <i class="fas fa-plus"></i> Add New Driver
                </button>
            </div>
            <div class="table-responsive">
                <table class="driver-table">
                    <thead>
                        <tr>
                            <th>Driver ID</th>
                            <th>Full Name</th>
                            <th>Contact</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($result && mysqli_num_rows($result) > 0): ?>
                            <?php while($row = mysqli_fetch_assoc($result)): 
                                // Format the contact number for display
                                $contact_display = $row['contact'];
                                // If it's stored as a number without leading 0, add it
                                if (is_numeric($contact_display) && strlen($contact_display) == 10) {
                                    $contact_display = '0' . $contact_display;
                                }
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['driver_id']); ?></td>
                                <td><?php echo htmlspecialchars($row['fullname']); ?></td>
                                <td><?php echo htmlspecialchars($contact_display); ?></td>
                                <td><?php echo htmlspecialchars($row['status']); ?></td>
                                <td class="action-buttons">
                                    <button type="button" class="btn btn-success editbtn" data-toggle="modal" data-target="#editmodal" data-id="<?php echo $row['driver_id']; ?>">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <button type="button" class="btn btn-danger deletebtn" data-toggle="modal" data-target="#deletemodal" data-id="<?php echo $row['driver_id']; ?>">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center">No drivers found</td>
                            </tr>
                        <?php endif; ?>
                        <tr>
                            <td class="tall-cell"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Add Driver Modal -->
    <div class="modal fade" id="addDriverModal" tabindex="-1" role="dialog" aria-labelledby="addModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="drivers.php" method="POST" id="addDriverForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addModalLabel">Add New Driver</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="add_fullname">Full Name</label>
                            <input type="text" name="fullname" id="add_fullname" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="add_contact">Contact Number</label>
                            <input type="text" name="contact" id="add_contact" class="form-control" 
                                pattern="^09\d{9}$" 
                                title="Please enter a number starting with 09 and exactly 11 digits" 
                                required>
                            <div class="error-message" id="add_contact-error">Please enter a valid contact number starting with 09 and exactly 11 digits</div>
                        </div>
                        
                        <div class="form-group">
                            <label for="add_status">Status</label>
                            <select name="status" id="add_status" class="form-control" required>
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" name="adddriver">Add Driver</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editmodal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="drivers.php" method="POST" id="editDriverForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editModalLabel">Edit Driver Information</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="driver_id" id="driver_id">
                        
                        <div class="form-group">
                            <label for="edit_fullname">Full Name</label>
                            <input type="text" name="fullname" id="edit_fullname" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_contact">Contact Number</label>
                            <input type="text" name="contact" id="edit_contact" class="form-control" 
                                pattern="^09\d{9}$" 
                                title="Please enter a number starting with 09 and exactly 11 digits" 
                                required>
                            <div class="error-message" id="edit_contact-error">Please enter a valid contact number starting with 09 and exactly 11 digits</div>
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_status">Status</label>
                            <select name="status" id="edit_status" class="form-control" required>
                                <option value="Active">Active</option>
                                <option value="Inactive">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" name="updatedata">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Delete Modal -->
    <div class="modal fade" id="deletemodal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="drivers.php" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteModalLabel">Delete Driver</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="driver_id" id="delete_driver_id">
                        <p>Are you sure you want to delete this driver?</p>
                        <p class="text-danger">This action cannot be undone.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger" name="deletedata">Delete</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
    $(document).ready(function(){
        <?php if (!empty($success_message)): ?>
            showNotification('<?php echo addslashes($success_message); ?>', 'success');
        <?php endif; ?>
        
        <?php if (!empty($error_message)): ?>
            showNotification('<?php echo addslashes($error_message); ?>', 'error');
        <?php endif; ?>

        // Notification function
        function showNotification(message, type) {
            var notification = $('<div class="notification ' + type + '">' + message + '</div>');
            $('body').append(notification);
            
            // Remove notification after animation
            setTimeout(function() {
                notification.remove();
            }, 3000);
        }

        // Edit button click handler
        $('.editbtn').on('click', function(){
            var driver_id = $(this).data('id');
            $('#driver_id').val(driver_id);
            
            // Get the row data
            $tr = $(this).closest('tr');
            var data = $tr.children("td").map(function(){
                return $(this).text();
            }).get();
            
            $('#edit_fullname').val(data[1]);
            
            // Ensure contact number has proper format
            var contact = data[2].trim();
            if (!contact.startsWith('09') && contact.length === 10) {
                contact = '0' + contact;
            }
            $('#edit_contact').val(contact);
            
            $('#edit_status').val(data[3].trim());
        });

        // Delete button click handler
        $('.deletebtn').on('click', function(){
            var driver_id = $(this).data('id');
            $('#delete_driver_id').val(driver_id);
        });

        // Contact number validation for both add and edit forms
        function validateContact(input, errorId) {
            // Remove any non-digit characters
            var value = input.val().replace(/\D/g, '');
            
            // Ensure it starts with 09
            if (!value.startsWith('09')) {
                value = '09' + value.replace(/^09?/, '');
            }
            
            // Limit to 11 digits
            value = value.substring(0, 11);
            
            input.val(value);
            
            // Validate and show error if needed
            if (!/^09\d{9}$/.test(value)) {
                $('#' + errorId).show();
                return false;
            } else {
                $('#' + errorId).hide();
                return true;
            }
        }

        // Contact number validation for add form
        $('#add_contact').on('input', function() {
            validateContact($(this), 'add_contact-error');
        });

        // Contact number validation for edit form
        $('#edit_contact').on('input', function() {
            validateContact($(this), 'edit_contact-error');
        });

        // Form submission validation for add form
        $('#addDriverForm').on('submit', function(e) {
            var isValid = true;
            
            // Check required fields
            $(this).find('[required]').each(function() {
                if (!$(this).val()) {
                    isValid = false;
                    $(this).addClass('is-invalid');
                } else {
                    $(this).removeClass('is-invalid');
                }
            });
            
            // Validate contact number
            if (!validateContact($('#add_contact'), 'add_contact-error')) {
                isValid = false;
            }
            
            if (!isValid) {
                e.preventDefault();
                showNotification('Please fill all required fields correctly', 'error');
            }
        });
        
        // Form submission validation for edit form
        $('#editDriverForm').on('submit', function(e) {
            var isValid = true;
            
            // Check required fields
            $(this).find('[required]').each(function() {
                if (!$(this).val()) {
                    isValid = false;
                    $(this).addClass('is-invalid');
                } else {
                    $(this).removeClass('is-invalid');
                }
            });
            
            // Validate contact number
            if (!validateContact($('#edit_contact'), 'edit_contact-error')) {
                isValid = false;
            }
            
            if (!isValid) {
                e.preventDefault();
                showNotification('Please fill all required fields correctly', 'error');
            }
        });
    });
    </script>                        
</body>
</html>