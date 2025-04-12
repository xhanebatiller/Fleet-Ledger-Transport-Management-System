<?php
// clients.php
session_start();
require_once 'connection.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['addclient'])) {
        // Add new client
        $client = mysqli_real_escape_string($conn, $_POST['client']);
        $contact = mysqli_real_escape_string($conn, $_POST['contact']);
        $tell_no = mysqli_real_escape_string($conn, $_POST['tell_no']);
        
        if(!preg_match('/^[\d\+\-\(\)\s]{7,15}$/', $tell_no)) {
            $_SESSION['error_message'] = "Invalid contact number format";
            header("location: clients.php");
            exit();
        }
        
        $query = "INSERT INTO clients (client, contact, tell_no) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($conn, $query);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "sss", $client, $contact, $tell_no);
            
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['success_message'] = "Client added successfully";
            } else {
                $_SESSION['error_message'] = "Error: " . mysqli_stmt_error($stmt);
            }
        } else {
            $_SESSION['error_message'] = "Error: " . mysqli_error($conn);
        }
        
        header("location: clients.php");
        exit();
    }
    elseif (isset($_POST['updatedata'])) {
        // Update client
        $c_id = mysqli_real_escape_string($conn, $_POST['c_id']);
        $client = mysqli_real_escape_string($conn, $_POST['client']);
        $contact = mysqli_real_escape_string($conn, $_POST['contact']);
        $tell_no = mysqli_real_escape_string($conn, $_POST['tell_no']);
        
        if(!preg_match('/^[\d\+\-\(\)\s]{7,15}$/', $tell_no)) {
            $_SESSION['error_message'] = "Invalid contact number format";
            header("location: clients.php");
            exit();
        }
        
        $query = "UPDATE clients SET client = ?, contact = ?, tell_no = ? WHERE c_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "sssi", $client, $contact, $tell_no, $c_id);
            
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['success_message'] = "Client information updated successfully";
            } else {
                $_SESSION['error_message'] = "Error: " . mysqli_stmt_error($stmt);
            }
        } else {
            $_SESSION['error_message'] = "Error: " . mysqli_error($conn);
        }
        
        header("location: clients.php");
        exit();
    }
    elseif (isset($_POST['deletedata'])) {
        // Delete client
        $c_id = mysqli_real_escape_string($conn, $_POST['c_id']);
        
        $query = "DELETE FROM clients WHERE c_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $c_id);
            
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['success_message'] = "Client deleted successfully";
            } else {
                $_SESSION['error_message'] = "Error: " . mysqli_stmt_error($stmt);
            }
        } else {
            $_SESSION['error_message'] = "Error: " . mysqli_error($conn);
        }
        
        header("location: clients.php");
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

// Fetch all clients from the database
$query = "SELECT * FROM clients ORDER BY c_id";
$result = mysqli_query($conn, $query);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PCL - CLIENTS</title>
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
        .client-table-container {
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
        
        .add-client-btn {
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
        
        .add-client-btn:hover {
            background-color: #218838;
        }
        
        .client-table {
            width: 100%;
            border-collapse: collapse;
            box-shadow: 0 2px 3px rgba(0, 0, 0, 0.1);
        }
        
        .client-table th {
            background-color: maroon;
            color: white;
            font-weight: bold;
            text-align: center;
            padding: 12px 15px;
        }
        
        .client-table td {
            padding: 10px 15px;
            text-align: center;
            border-bottom: 1px solid #e0e0e0;
            word-break: break-word;
        }
        
        .client-table tr:hover {
            background-color: #f5f5f5;
        }
        
        .client-table tr:nth-child(even) {
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
        
        .btn-primary:hover {
            background-color: #0069d9;
        }
        
        .btn-danger {
            background-color: #dc3545;
            color: white;
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

        .client-table thead {
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
            .client-table-container {
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
            .client-table-container {
                width: 98%;
                margin: 10px auto;
            }
            
            .client-table th, 
            .client-table td {
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
            .client-table th, 
            .client-table td {
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
            
            .add-client-btn {
                padding: 6px 12px;
                font-size: 0.9rem;
            }
            
            .modal-content {
                margin: 0 10px;
            }
        }
        
        @media screen and (max-width: 400px) {
            .client-table td {
                padding: 4px 6px;
            }
            
            .btn {
                display: block;
                width: 100%;
                margin: 5px 0;
            }
            
            .action-buttons {
                display: flex;
                flex-direction: column;
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
        
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <div class="client-table-container">
            <div class="table-header">
                <span>Client Information</span>
                <button type="button" class="add-client-btn" data-toggle="modal" data-target="#addClientModal">
                    <i class="fas fa-plus"></i> Add New Client
                </button>
            </div>
            <div class="table-responsive">
                <table class="client-table">
                    <thead>
                        <tr>
                            <th>Client Name</th>
                            <th>Contact no.</th>
                            <th>Tell no.</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if($result && mysqli_num_rows($result) > 0) {
                            while($row = mysqli_fetch_assoc(result: $result)) { 
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['client']); ?></td>
                            <td><?php echo htmlspecialchars($row['contact']); ?></td>
                            <td><?php echo htmlspecialchars($row['tell_no']); ?></td>
                            <td class="action-buttons">
                                <button type="button" class="btn btn-primary editbtn" data-toggle="modal" data-target="#editmodal" data-id="<?php echo $row['c_id']; ?>">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button type="button" class="btn btn-danger deletebtn" data-toggle="modal" data-target="#deletemodal" data-id="<?php echo $row['c_id']; ?>">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </td>
                        </tr>
                        <?php 
                            }
                        } else {
                            echo '<tr><td colspan="4" class="text-center">No clients found</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Add Client Modal -->
    <div class="modal fade" id="addClientModal" tabindex="-1" role="dialog" aria-labelledby="addModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="clients.php" method="POST" id="addClientForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addModalLabel">Add New Client</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="add_client">Client Name</label>
                            <input type="text" name="client" id="add_client" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="add_contact">Contact no.</label>
                            <input type="number" name="contact" id="add_contact" class="form-control" required
                                oninput="validateContactNo1()">
                            <div class="error-message" id="add_contact-error">Please enter a valid contact number (starts with 09 and 11 digits).</div>
                        </div>

                        <div class="form-group">
                            <label for="add_tell_no">Tell no.</label>
                            <input type="number" name="tell_no" id="add_tell_no" class="form-control" required
                                oninput="validateTellNo1()">
                            <div class="error-message" id="add_tell_no-error">Please enter a valid tell number (7-15 digits, may include + - ( ) and spaces).</div>
                        </div>

                        <script>
                            function validateContactNo1() {
                                const contactNo = document.getElementById('add_contact').value;  
                                const contactError = document.getElementById('add_contact-error');
                                
                                const contactPattern = /^09\d{9}$/;
                                
                                if (!contactPattern.test(contactNo)) {
                                    contactError.style.display = 'block';
                                } else {
                                    contactError.style.display = 'none';
                                }
                            }

                            function validateTellNo1() {
                                const tellNo = document.getElementById('add_tell_no').value; 
                                const tellNoError = document.getElementById('add_tell_no-error');
                                
                                const tellNoPattern = /^[\+\-\(\)\d\s]{7,15}$/;
                                
                                if (!tellNoPattern.test(tellNo)) {
                                    tellNoError.style.display = 'block';
                                } else {
                                    tellNoError.style.display = 'none';
                                }
                            }
                        </script>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" name="addclient">Add Client</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Edit Modal -->
    <div class="modal fade" id="editmodal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="clients.php" method="POST" id="editClientForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editModalLabel">Edit Client Information</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="c_id" id="c_id">
                        
                        <div class="form-group">
                            <label for="client">Client Name</label>
                            <input type="text" name="client" id="client" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label for="contact">Contact no.</label>
                            <input type="number" name="contact" id="contact" class="form-control" required
                            oninput="validateContactNo()">
                            <div class="error-message" id="contact-error" style="display: none;">Please enter a valid contact number (starts with 09 and 11 digits).</div>
                        </div>

                        <div class="form-group">
                            <label for="tell_no">Tell no.</label>
                            <input type="number" name="tell_no" id="tell_no" class="form-control" required
                            oninput="validateTellNo()">
                            <div class="error-message" id="tell_no-error" style="display: none;">Please enter a valid contact number (7-15 digits, may include + - ( ) and spaces).</div>
                        </div>

                        <script>
                            function validateContactNo() {
                                const contactNo = document.getElementById('contact').value; 
                                const contactError = document.getElementById('contact-error');
                                const contactPattern = /^09\d{9}$/;
                                
                                if (!contactPattern.test(contactNo)) {
                                    contactError.style.display = 'block';
                                } else {
                                    contactError.style.display = 'none';
                                }
                            }

                            function validateTellNo() {
                                const tellNo = document.getElementById('tell_no').value;  
                                const tellNoError = document.getElementById('tell_no-error');
                                const tellNoPattern = /^[\+\-\(\)\d\s]{7,15}$/;
                                if (!tellNoPattern.test(tellNo)) {
                                    tellNoError.style.display = 'block';
                                } else {
                                    tellNoError.style.display = 'none';
                                }
                            }
                        </script>
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
                <form action="clients.php" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteModalLabel">Delete Client</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="c_id" id="delete_c_id">
                        <p>Are you sure you want to delete this client?</p>
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
            $('#editmodal').modal('show');

            $tr = $(this).closest('tr');
            var data = $tr.children("td").map(function(){
                return $(this).text();
            }).get();
            
            $('#c_id').val($(this).data('id'));
            $('#client').val(data[0]);
            $('#contact').val(data[1]);
            $('#tell_no').val(data[2]);
        });
        
        // Delete button click handler
        $('.deletebtn').on('click', function(){
            var c_id = $(this).data('id');
            $('#delete_c_id').val(c_id);
        });

        // Contact number validation
        function validatePhoneNumber(input) {
            var phoneRegex = /^[\d\+\-\(\)\s]{7,15}$/;
            
            if (!phoneRegex.test(input.val())) {
                $('#' + input.attr('id') + '-error').show();
                return false;
            } else {
                $('#' + input.attr('id') + '-error').hide();
                return true;
            }
        }

        $('#tell_no, #add_tell_no').on('input', function() {
            validatePhoneNumber($(this));
        });

        // Form submission validation
        $('#editClientForm').on('submit', function(e) {
            if (!validatePhoneNumber($('#tell_no'))) {
                e.preventDefault();
                $('#tell_no').focus();
            }
        });
        
        $('#addClientForm').on('submit', function(e) {
            if (!validatePhoneNumber($('#add_tell_no'))) {
                e.preventDefault();
                $('#add_tell_no').focus();
            }
        });
        
    });
    </script>
</body>
</html>