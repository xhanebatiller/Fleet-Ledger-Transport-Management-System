<?php
// employee.php
session_start();
require_once 'connection.php';

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['addemployee'])) {
        // Add new employee
        $emp_num = mysqli_real_escape_string($conn, $_POST['emp_num']);
        $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $position = mysqli_real_escape_string($conn, $_POST['position']);
        
        // Validate email
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error_message'] = "Invalid email format";
            header("location: employee.php");
            exit();
        }
        
        $query = "INSERT INTO employee (emp_num, fullname, email, u_id) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $query);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "sssi", $emp_num, $fullname, $email, $position);
            
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['success_message'] = "Employee added successfully";
            } else {
                $_SESSION['error_message'] = "Error: " . mysqli_stmt_error($stmt);
            }
        } else {
            $_SESSION['error_message'] = "Error: " . mysqli_error($conn);
        }
        
        header("location: employee.php");
        exit();
    }
    elseif (isset($_POST['updatedata'])) {
        // Update employee
        $emp_id = mysqli_real_escape_string($conn, $_POST['emp_id']);
        $emp_num = mysqli_real_escape_string($conn, $_POST['emp_num']);
        $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $position = mysqli_real_escape_string($conn, $_POST['position']);
        
        // Validate email
        if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error_message'] = "Invalid email format";
            header("location: employee.php");
            exit();
        }
        
        $query = "UPDATE employee SET emp_num = ?, fullname = ?, email = ?, u_id = ? WHERE emp_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "sssii", $emp_num, $fullname, $email, $position, $emp_id);
            
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['success_message'] = "Employee information updated successfully";
            } else {
                $_SESSION['error_message'] = "Error: " . mysqli_stmt_error($stmt);
            }
        } else {
            $_SESSION['error_message'] = "Error: " . mysqli_error($conn);
        }
        
        header("location: employee.php");
        exit();
    }
    elseif (isset($_POST['deletedata'])) {
        // Delete employee
        $emp_id = mysqli_real_escape_string($conn, $_POST['emp_id']);
        
        $query = "DELETE FROM employee WHERE emp_id = ?";
        $stmt = mysqli_prepare($conn, $query);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $emp_id);
            
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['success_message'] = "Employee deleted successfully";
            } else {
                $_SESSION['error_message'] = "Error: " . mysqli_stmt_error($stmt);
            }
        } else {
            $_SESSION['error_message'] = "Error: " . mysqli_error($conn);
        }
        
        header("location: employee.php");
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

$query = "SELECT e.*, u.position FROM employee e 
          JOIN usertype u ON e.u_id = u.u_id 
          ORDER BY e.emp_id";
$result = mysqli_query($conn, $query);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PCL - EMPLOYEES</title>
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
        .employee-table-container {
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
        
        .add-employee-btn {
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
        
        .add-employee-btn:hover {
            background-color: #218838;
        }
        
        .employee-table {
            width: 100%;
            border-collapse: collapse;
            box-shadow: 0 2px 3px rgba(0, 0, 0, 0.1);
        }
        
        .employee-table th {
            background-color: maroon;
            color: white;
            font-weight: bold;
            text-align: center;
            padding: 12px 15px;
        }
        
        .employee-table td {
            padding: 10px 15px;
            text-align: center;
            border-bottom: 1px solid #e0e0e0;
            word-break: break-word;
        }

        .employee-table td.tall-cell {
            height: 170px;
            vertical-align: top;
        }
        
        .employee-table tr:hover {
            background-color: #f5f5f5;
        }
        
        .employee-table tr:nth-child(even) {
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

        .employee-table thead {
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
            .employee-table-container {
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
            .employee-table-container {
                width: 98%;
                margin: 10px auto;
            }
            
            .employee-table th, 
            .employee-table td {
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
            .employee-table th, 
            .employee-table td {
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
            
            .add-employee-btn {
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
            .employee-table td {
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
        
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <div class="employee-table-container">
            <div class="table-header">
                <span>Employee Information</span>
                <button type="button" class="add-employee-btn" data-toggle="modal" data-target="#addEmployeeModal">
                    <i class="fas fa-plus"></i> Add New Employee
                </button>
            </div>
            <div class="table-responsive">
                <table class="employee-table">
                    <thead>
                        <tr>
                            <th>Employee ID</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Position</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($result && mysqli_num_rows($result) > 0): ?>
                            <?php while($row = mysqli_fetch_assoc($result)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['emp_num']); ?></td>
                                <td><?php echo htmlspecialchars($row['fullname']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><?php echo htmlspecialchars($row['position']); ?></td>
                                <td class="action-buttons">
                                    <button type="button" class="btn btn-success editbtn" data-toggle="modal" data-target="#editmodal" data-id="<?php echo $row['emp_id']; ?>">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <button type="button" class="btn btn-danger deletebtn" data-toggle="modal" data-target="#deletemodal" data-id="<?php echo $row['emp_id']; ?>">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center">No employees found</td>
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

    <!-- Add Employee Modal -->
    <div class="modal fade" id="addEmployeeModal" tabindex="-1" role="dialog" aria-labelledby="addModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="employee.php" method="POST" id="addEmployeeForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addModalLabel">Add New Employee</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="add_emp_num">Employee Number</label>
                            <input type="text" name="emp_num" id="add_emp_num" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="add_fullname">Full Name</label>
                            <input type="text" name="fullname" id="add_fullname" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="add_email">Email</label>
                            <input type="email" name="email" id="add_email" class="form-control" required>
                            <div class="error-message" id="add_email-error">Please enter a valid email address</div>
                        </div>
                        
                        <div class="form-group">
                            <label for="add_position">Position</label>
                            <select name="position" id="add_position" class="form-control" required>
                                <?php
                                $positionQuery = "SELECT u_id, position FROM usertype";
                                $positionResult = mysqli_query($conn, $positionQuery);
                                while($position = mysqli_fetch_assoc($positionResult)) {
                                    echo '<option value="'.$position['u_id'].'">'.$position['position'].'</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary" name="addemployee">Add Employee</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editmodal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="employee.php" method="POST" id="editEmployeeForm">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editModalLabel">Edit Employee Information</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="emp_id" id="emp_id">
                        
                        <div class="form-group">
                            <label for="edit_emp_num">Employee Number</label>
                            <input type="text" name="emp_num" id="edit_emp_num" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_fullname">Full Name</label>
                            <input type="text" name="fullname" id="edit_fullname" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_email">Email</label>
                            <input type="email" name="email" id="edit_email" class="form-control" required>
                            <div class="error-message" id="edit_email-error">Please enter a valid email address</div>
                        </div>
                        
                        <div class="form-group">
                            <label for="edit_position">Position</label>
                            <select name="position" id="edit_position" class="form-control" required>
                                <?php
                                $positionQuery = "SELECT u_id, position FROM usertype";
                                $positionResult = mysqli_query($conn, $positionQuery);
                                while($position = mysqli_fetch_assoc($positionResult)) {
                                    echo '<option value="'.$position['u_id'].'">'.$position['position'].'</option>';
                                }
                                ?>
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
                <form action="employee.php" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteModalLabel">Delete Employee</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="emp_id" id="delete_emp_id">
                        <p>Are you sure you want to delete this employee?</p>
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
            var emp_id = $(this).data('id');
            $('#emp_id').val(emp_id);
            
            // Get the row data
            $tr = $(this).closest('tr');
            var data = $tr.children("td").map(function(){
                return $(this).text();
            }).get();
            
            $('#edit_emp_num').val(data[0]);
            $('#edit_fullname').val(data[1]);
            $('#edit_email').val(data[2]);
            
            // Find the position in the dropdown
            var positionText = data[3].trim();
            $('#edit_position option').each(function() {
                if ($(this).text() === positionText) {
                    $('#edit_position').val($(this).val());
                    return false;
                }
            });
        });

        // Delete button click handler
        $('.deletebtn').on('click', function(){
            var emp_id = $(this).data('id');
            $('#delete_emp_id').val(emp_id);
        });

        // Email validation for add form
        $('#add_email').on('input', function() {
            validateEmail($(this), 'add_email-error');
        });

        // Email validation for edit form
        $('#edit_email').on('input', function() {
            validateEmail($(this), 'edit_email-error');
        });
        
        // Email validation function
        function validateEmail(input, errorId) {
            var email = input.val();
            var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            if (!emailRegex.test(email)) {
                $('#' + errorId).show();
                return false;
            } else {
                $('#' + errorId).hide();
                return true;
            }
        }

        // Form submission validation for add form
        $('#addEmployeeForm').on('submit', function(e) {
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
            
            // Validate email
            if (!validateEmail($('#add_email'), 'add_email-error')) {
                isValid = false;
            }
            
            if (!isValid) {
                e.preventDefault();
                showNotification('Please fill all required fields correctly', 'error');
            }
        });
        
        // Form submission validation for edit form
        $('#editEmployeeForm').on('submit', function(e) {
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
            
            // Validate email
            if (!validateEmail($('#edit_email'), 'edit_email-error')) {
                isValid = false;
                e.preventDefault();
                showNotification('Please fill all required fields correctly', 'error');
            }
        });
    });
    </script>
</body>
</html>