<?php
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Include the connection file
require_once "connection.php";
// Use the global $conn and $pdo variables from connection.php instead of creating new connections

$u_id = $_SESSION["u_id"];
$emp_id = $_SESSION["emp_id"];
$fullname = $email = $emp_num = "";
$fullname_err = $email_err = $emp_num_err = "";
$password = $new_password = $confirm_password = "";
$password_err = $new_password_err = $confirm_password_err = "";
$success_message = $error_message = "";

// Get current user's email
$sql = "SELECT fullname, email, emp_num FROM employee WHERE emp_id = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $emp_id);
    if ($stmt->execute()) {
        $stmt->store_result();
        if ($stmt->num_rows == 1) {
            $stmt->bind_result($fullname, $email, $emp_num);
            $stmt->fetch();
        }
    }
    $stmt->close();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["update_profile"])) {
    if (empty(trim($_POST["fullname"]))) {
        $fullname_err = "Please enter your full name.";
    } else {
        $fullname = trim($_POST["fullname"]);
    }
    if (empty(trim($_POST["email"]))) {
        $email_err = "Please enter your email.";
    } else {
        $email = trim($_POST["email"]);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $email_err = "Please enter a valid email address.";
        }
    }
    if (empty(trim($_POST["emp_num"]))) {
        $emp_num_err = "Please enter your employee number.";
    } else {
        $emp_num = trim($_POST["emp_num"]);
    }
    if (empty($fullname_err) && empty($email_err) && empty($emp_num_err)) {
        $sql = "UPDATE employee SET fullname = ?, email = ?, emp_num = ? WHERE emp_id = ?";
        
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("sssi", $param_fullname, $param_email, $param_emp_num, $param_emp_id);
            $param_fullname = $fullname;
            $param_email = $email;
            $param_emp_num = $emp_num;
            $param_emp_id = $emp_id;
            if ($stmt->execute()) {
                $_SESSION["fullname"] = $fullname;
                $success_message = "Profile updated successfully!";
            } else {
                $error_message = "Oops! Something went wrong. Please try again later.";
            }
            $stmt->close();
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["change_password"])) {
    // Initialize variables
    $password_err = $new_password_err = $confirm_password_err = "";
    $password = $new_password = $confirm_password = "";
    $success_message = $error_message = "";

    // Validate current password
    if (empty(trim($_POST["current_password"]))) {
        $password_err = "Please enter your current password.";
    } else {
        $password = trim($_POST["current_password"]);
    }

    // Validate new password
    if (empty(trim($_POST["new_password"]))) {
        $new_password_err = "Please enter the new password.";     
    } elseif (strlen(trim($_POST["new_password"])) < 6) {
        $new_password_err = "Password must have at least 6 characters.";
    } else {
        $new_password = trim($_POST["new_password"]);
    }

    // Validate confirm password
    if (empty(trim($_POST["confirm_password"]))) {
        $confirm_password_err = "Please confirm the password.";     
    } else {
        $confirm_password = trim($_POST["confirm_password"]);
        if (empty($new_password_err) && ($new_password != $confirm_password)) {
            $confirm_password_err = "Password did not match.";
        }
    }

    // Proceed if no errors
    if (empty($password_err) && empty($new_password_err) && empty($confirm_password_err)) {
        $sql = "SELECT password FROM employee WHERE emp_id = ?";
        
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("i", $param_emp_id);
            $param_emp_id = $emp_id;
            
            if ($stmt->execute()) {
                $stmt->store_result();
                
                if ($stmt->num_rows == 1) {
                    $stmt->bind_result($stored_password);
                    if ($stmt->fetch()) {
                        // Verify current password
                        $passwordMatches = false;
                        
                        if (strpos($stored_password, '$2y$') === 0) {
                            $passwordMatches = password_verify($password, $stored_password);
                        } else {
                            $passwordMatches = ($password === $stored_password);
                        }
                        
                        if ($passwordMatches) {
                            // Check if new password is different from current password
                            if (password_verify($new_password, $stored_password)) {
                                $new_password_err = "New password cannot be the same as your current password.";
                            } else {
                                // Update password
                                $sql = "UPDATE employee SET password = ? WHERE emp_id = ?";
                                
                                if ($stmt_update = $conn->prepare($sql)) {
                                    $stmt_update->bind_param("si", $param_password, $param_emp_id);
                                    $param_password = password_hash($new_password, PASSWORD_DEFAULT);
                                    
                                    if ($stmt_update->execute()) {
                                        $success_message = "Password changed successfully!";
                                        $password = $new_password = $confirm_password = "";
                                    } else {
                                        $error_message = "Oops! Something went wrong. Please try again later.";
                                    }
                                    $stmt_update->close();
                                }
                            }
                        } else {
                            $password_err = "The current password you entered is not valid.";
                        }
                    }
                } else {
                    $error_message = "No account found with that employee ID.";
                }
            } else {
                $error_message = "Oops! Something went wrong. Please try again later.";
            }
            $stmt->close();
        }
    }
}

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

// Get user position - using existing connection
$position = "Unknown";
$sql = "SELECT position FROM usertype WHERE u_id = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $u_id);
    if ($stmt->execute()) {
        $stmt->bind_result($position);
        $stmt->fetch();
    }
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PCL - MY PROFILE</title>
    <link rel="icon" href="assets/img/pcl.png" type="image/x-icon">
    <link rel="stylesheet" href="assets/css/landingPage.css">
    <style>
        :root {
            --primary-color: rgb(103, 0, 0);
            --primary-hover: rgb(179, 0, 0);
            --text-color: #333;
            --border-color: #ddd;
            --light-gray: #f8f9fa;
            --error-color: #dc3545;
            --success-color: #28a745;
        }
        
        * {
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            color: var(--text-color);
            margin: 0;
            padding: 0;
        }
        
        .profile-container {
            background-color: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            padding: 10px;
            margin: 20px auto;
            width: 90%;
        }
        
        .profile-sections {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-top: 20px;
        }
        
        .profile-section {
            flex: 1;
            min-width: 300px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            padding: 20px;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .profile-section:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .section-header {
            position: relative;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--light-gray);
        }
        
        .section-header h2 {
            font-size: 1.4rem;
            color: var(--primary-color);
            margin: 0;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            position: relative;
            animation: fadeIn 0.5s;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #555;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid var(--border-color);
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.3s, box-shadow 0.3s;
        }
        
        .form-group input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.25);
            outline: none;
        }
        
        .form-group .invalid-feedback {
            color: var(--error-color);
            font-size: 14px;
            margin-top: 5px;
        }
        
        .btn {
            padding: 12px 20px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            transition: background-color 0.3s, transform 0.2s;
            width: 100%;
        }
        
        .btn:hover {
            background-color: var(--primary-hover);
            transform: translateY(-2px);
        }
        
        .btn:active {
            transform: translateY(0);
        }
        
        .user-info-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .user-info-table tr {
            border-bottom: 1px solid var(--border-color);
        }
        
        .user-info-table tr:last-child {
            border-bottom: none;
        }
        
        .user-info-table th, .user-info-table td {
            padding: 12px 8px;
            text-align: left;
        }
        
        .user-info-table th {
            color: #555;
            font-weight: 600;
            width: 40%;
        }
        
        .user-info-table td {
            color: var(--text-color);
        }
        
        @media (max-width: 992px) {
            .profile-sections {
                flex-direction: column;
            }
            
            .profile-section {
                width: 100%;
            }
        }
        
        @media (max-width: 768px) {
            .profile-container {
                padding: 20px 15px;
                margin: 10px;
            }
            
            .section-header h2 {
                font-size: 1.2rem;
            }
        }
        
        @media (max-width: 480px) {
            .form-group input, .btn {
                padding: 10px;
                font-size: 14px;
            }
        }
        
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
            justify-content: center;
            align-items: center;
        }
        
        .modal-content {
            background-color: #fefefe;
            padding: 20px;
            border-radius: 10px;
            width: 650px;
            max-width: 90%;
            text-align: center;
            position: relative;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        }
        
        .close-modal {
            position: absolute;
            top: 10px;
            right: 10px;
            cursor: pointer;
            font-size: 20px;
            font-weight: bold;
            color: #666;
        }
        
        /* Enhanced OTP Input Styles */
        .otp-container {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin: 20px 0;
        }
        
        .otp-input {
            width: 40px;
            height: 50px;
            text-align: center;
            font-size: 24px;
            border: 2px solid #ddd;
            border-radius: 5px;
            outline: none;
            transition: all 0.3s;
        }
        
        .otp-input:focus {
            border-color: #800000;
            box-shadow: 0 0 5px rgba(128, 0, 0, 0.5);
        }
        
        /* Maroon Dark Button */
        .btn-maroon {
            background-color: #800000;
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
            width: 100%;
        }
        
        .btn-maroon:hover {
            background-color: #600000;
        }
        
        /* Modal Header */
        .modal-header {
            color: #800000;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .modal-header h2 {
            margin: 0;
            font-size: 1.5rem;
        }
        
        /* Email Display */
        .email-display {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: bold;
            word-break: break-all;
        }
        
        .loading-screen {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.59);
            display: none;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            z-index: 9999;
        }

        .loader {
            border: 5px solid rgb(0, 0, 0);
            border-top: 5px solid #800000;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin-bottom: 10px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
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
            <div class="role">Position: <?php echo htmlspecialchars($position); ?></div>
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
        
        <div class="profile-container">
            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success"><?php echo $success_message; ?></div>
            <?php endif; ?>
            
            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger"><?php echo $error_message; ?></div>
            <?php endif; ?>
            
            <div class="profile-sections">
                <!-- Current User Information -->
                <div class="profile-section">
                    <div class="section-header">
                        <h2>Current Information</h2>
                    </div>
                    <table class="user-info-table">
                        <tr>
                            <th>Employee ID</th>
                            <td><?php echo htmlspecialchars($emp_id); ?></td>
                        </tr>
                        <tr>
                            <th>Full Name</th>
                            <td><?php echo htmlspecialchars($fullname); ?></td>
                        </tr>
                        <tr>
                            <th>Employee Number</th>
                            <td><?php echo htmlspecialchars($emp_num); ?></td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td><?php echo htmlspecialchars($email); ?></td>
                        </tr>
                        <tr>
                            <th>Position</th>
                            <td><?php echo htmlspecialchars($position); ?></td>
                        </tr>
                    </table>
                </div>
                
                <!-- Update Profile Form -->
                <div class="profile-section">
                    <div class="section-header">
                        <h2>Update Profile</h2>
                    </div>
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                        <div class="form-group">
                            <label>Full Name</label>
                            <input type="text" name="fullname" value="<?php echo htmlspecialchars($fullname); ?>">
                            <span class="invalid-feedback"><?php echo $fullname_err; ?></span>
                        </div>    
                        <div class="form-group">
                            <label>Employee Number</label>
                            <input type="text" name="emp_num" value="<?php echo htmlspecialchars($emp_num); ?>">
                            <span class="invalid-feedback"><?php echo $emp_num_err; ?></span>
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" value="<?php echo htmlspecialchars($email); ?>">
                            <span class="invalid-feedback"><?php echo $email_err; ?></span>
                        </div>
                        <div class="form-group">
                            <input type="submit" class="btn" name="update_profile" value="Update Profile">
                        </div>
                    </form>
                </div>
                
                <!-- Change Password Form -->
                <div class="profile-section">
                    <div class="section-header">
                        <h2>Change Password</h2>
                    </div>
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                        <div class="form-group">
                            <label>Current Password</label>
                            <input type="password" name="current_password">
                            <span class="invalid-feedback"><?php echo $password_err; ?></span>
                        </div>
                        <div class="form-group">
                            <label>New Password</label>
                            <input type="password" name="new_password">
                            <span class="invalid-feedback"><?php echo $new_password_err; ?></span>
                        </div>
                        <div class="form-group">
                            <label>Confirm Password</label>
                            <input type="password" name="confirm_password">
                            <span class="invalid-feedback"><?php echo $confirm_password_err; ?></span>
                        </div>
                        <div class="form-group">
                            <input type="submit" class="btn" name="change_password" value="Change Password">
                            <div style="margin-top: 10px;">
                                Do you want to send an OTP to
                                <a href="#" id="forgotLink" 
                                style="color: #000000; text-decoration: none; font-weight: bold;" 
                                onmouseover="this.style.color='#f00000'; this.style.textDecoration='underline';" 
                                onmouseout="this.style.color='#800000'; this.style.textDecoration='none';">
                                Change Password?
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Forgot Password Modal - Auto-filled with logged-in user's email -->
    <div id="forgotPasswordModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal('forgotPasswordModal')">&times;</span>
            <div class="modal-header">
                <h2>Forgot Password</h2>
            </div>
            <div class="email-display">
                OTP will be sent to: <?php echo htmlspecialchars($email); ?>
            </div>
            <form id="forgotPasswordForm">
                <input type="hidden" id="forgotEmail" name="email" value="<?php echo htmlspecialchars($email); ?>">
                <button type="submit" class="btn-maroon">Send OTP</button>
            </form>
        </div>
    </div>

    <!-- Enhanced OTP Verification Modal with 6-digit input -->
    <div id="otpModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal('otpModal')">&times;</span>
            <div class="modal-header">
                <h2>OTP Verification</h2>
            </div>
            <div class="email-display">
                Enter OTP sent to: <?php echo htmlspecialchars($email); ?>
            </div>
            <form id="otpVerificationForm">
                <div class="otp-container">
                    <input type="text" class="otp-input" maxlength="1" data-index="1" autocomplete="off">
                    <input type="text" class="otp-input" maxlength="1" data-index="2" autocomplete="off">
                    <input type="text" class="otp-input" maxlength="1" data-index="3" autocomplete="off">
                    <input type="text" class="otp-input" maxlength="1" data-index="4" autocomplete="off">
                    <input type="text" class="otp-input" maxlength="1" data-index="5" autocomplete="off">
                    <input type="text" class="otp-input" maxlength="1" data-index="6" autocomplete="off">
                </div>
                <input type="hidden" id="otpInput" name="otp">
                <button type="submit" class="btn-maroon">Verify OTP</button>
            </form>
        </div>
    </div>
    
    <!-- New Password Modal -->
    <div id="newPasswordModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal('newPasswordModal')">&times;</span>
            <div class="modal-header">
                <h2>Reset Password</h2>
            </div>
            <form id="resetPasswordForm">
                <input type="hidden" id="resetEmail" name="email" value="<?php echo htmlspecialchars($email); ?>">
                <div class="form-group">
                    <label>New Password</label>
                    <input type="password" id="newPassword" placeholder="New Password" required>
                </div>
                <div class="form-group">
                    <label>Confirm New Password</label>
                    <input type="password" id="confirmPassword" placeholder="Confirm New Password" required>
                </div>
                <button type="submit" class="btn-maroon">Reset Password</button>
            </form>
        </div>
    </div>

    <script>
        
        // Update your existing JavaScript with this
document.addEventListener('DOMContentLoaded', function() {
    const mobileToggle = document.querySelector('.mobile-toggle');
    const sidebar = document.querySelector('.sidebar');
    const overlay = document.querySelector('.overlay');
    
    // Toggle sidebar when mobile toggle is clicked
    mobileToggle.addEventListener('click', function() {
        sidebar.classList.toggle('active');
        overlay.classList.toggle('active');
    });
    
    // Close sidebar when overlay is clicked
    overlay.addEventListener('click', function() {
        sidebar.classList.remove('active');
        overlay.classList.remove('active');
    });
    
    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(e) {
        if (window.innerWidth <= 768) {
            if (!sidebar.contains(e.target) && e.target !== mobileToggle) {
                sidebar.classList.remove('active');
                overlay.classList.remove('active');
            }
        }
    });
    
    // Handle sidebar navigation clicks
    const metricSections = document.querySelectorAll('.metric-section');
    metricSections.forEach(section => {
        section.addEventListener('click', function() {
            const href = this.getAttribute('data-href');
            if (href) {
                window.location.href = href;
            }
        });
    });
});
        
        // Auto-hide success message after 3 seconds
        const alertSuccess = document.querySelector('.alert-success');
        if (alertSuccess) {
            setTimeout(function() {
                alertSuccess.style.opacity = '0';
                alertSuccess.style.transition = 'opacity 1s';
                setTimeout(function() {
                    alertSuccess.style.display = 'none';
                }, 1000);
            }, 3000);
        }
        
        // Form input focus effects
        const formInputs = document.querySelectorAll('.form-group input');
        formInputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.querySelector('label').style.color = '#007bff';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.querySelector('label').style.color = '#555';
            });
        });

        // Modal Functions
        function openModal(modalId) {
            document.getElementById(modalId).style.display = 'flex';
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        // Forgot Password Link
        document.getElementById('forgotLink').addEventListener('click', function(e) {
            e.preventDefault();
            openModal('forgotPasswordModal');
        });

        // Enhanced OTP Input Handling
        const otpInputs = document.querySelectorAll('.otp-input');
        
        otpInputs.forEach(input => {
            // Handle input
            input.addEventListener('input', function() {
                const value = this.value;
                const nextIndex = parseInt(this.dataset.index) + 1;
                const nextInput = document.querySelector(`.otp-input[data-index="${nextIndex}"]`);
                
                if (value.length === 1 && nextInput) {
                    nextInput.focus();
                }
                
                updateOTPValue();
            });
            
            // Handle backspace
            input.addEventListener('keydown', function(e) {
                if (e.key === 'Backspace' && this.value === '') {
                    const prevIndex = parseInt(this.dataset.index) - 1;
                    const prevInput = document.querySelector(`.otp-input[data-index="${prevIndex}"]`);
                    
                    if (prevInput) {
                        prevInput.focus();
                    }
                }
            });
            
            // Prevent paste
            input.addEventListener('paste', function(e) {
                e.preventDefault();
            });
        });
        
        function updateOTPValue() {
            let otp = '';
            otpInputs.forEach(input => {
                otp += input.value;
            });
            document.getElementById('otpInput').value = otp;
        }

        // Forgot Password Form Submission
        document.getElementById('forgotPasswordForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const email = document.getElementById('forgotEmail').value;
            
            // Show loading
            document.getElementById('loading-screen').style.display = 'flex';
            
            fetch('send-otp.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'email=' + encodeURIComponent(email)
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('loading-screen').style.display = 'none';
                if (data.success) {
                    closeModal('forgotPasswordModal');
                    openModal('otpModal');
                    // Clear OTP inputs when showing the modal
                    otpInputs.forEach(input => {
                        input.value = '';
                    });
                    // Focus first OTP input
                    otpInputs[0].focus();
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                document.getElementById('loading-screen').style.display = 'none';
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            });
        });

        // OTP Verification Form Submission
        document.getElementById('otpVerificationForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const otp = document.getElementById('otpInput').value;
            const email = document.getElementById('forgotEmail').value;
            
            if (otp.length !== 6) {
                alert('Please enter a complete 6-digit OTP');
                return;
            }

            // Show loading
            document.getElementById('loading-screen').style.display = 'flex';
            
            fetch('verify-otp.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'otp=' + encodeURIComponent(otp) + '&email=' + encodeURIComponent(email)
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('loading-screen').style.display = 'none';
                if (data.success) {
                    closeModal('otpModal');
                    document.getElementById('resetEmail').value = email;
                    openModal('newPasswordModal');
                } else {
                    alert(data.message || 'Invalid OTP. Please try again.');
                    // Clear OTP inputs on failure
                    otpInputs.forEach(input => {
                        input.value = '';
                    });
                    otpInputs[0].focus();
                }
            })
            .catch(error => {
                document.getElementById('loading-screen').style.display = 'none';
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            });
        });

        // Reset Password Form Submission
        document.getElementById('resetPasswordForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const email = document.getElementById('resetEmail').value;
            const newPassword = document.getElementById('newPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;

            if (newPassword !== confirmPassword) {
                alert('Passwords do not match!');
                return;
            }

            if (newPassword.length < 6) {
                alert('Password must be at least 6 characters long!');
                return;
            }

            // Show loading
            document.getElementById('loading-screen').style.display = 'flex';
            
            fetch('update-password.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'email=' + encodeURIComponent(email) + 
                      '&newPassword=' + encodeURIComponent(newPassword) + 
                      '&confirmPassword=' + encodeURIComponent(confirmPassword)
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('loading-screen').style.display = 'none';
                if (data.success) {
                    alert(data.message);
                    closeModal('newPasswordModal');
                    // Optionally redirect to login or refresh page
                    window.location.reload();
                } else {
                    alert(data.message || 'Failed to reset password. Please try again.');
                }
            })
            .catch(error => {
                document.getElementById('loading-screen').style.display = 'none';
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            });
        });
    </script>
</body>
</html>