<?php
session_start();
require_once 'connection.php';

$email = $password = "";
$emailErr = $passwordErr = $loginErr = "";
$success = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty($_POST["email"])) {
        $emailErr = "Email is required";
    } else {
        $email = trim($_POST["email"]);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $emailErr = "Invalid email format";
        }
    }
    
    if (empty($_POST["password"])) {
        $passwordErr = "Password is required";
    } else {
        $password = trim($_POST["password"]);
    }
    
    if (empty($emailErr) && empty($passwordErr)) {
        // Check if user is already logged in elsewhere
        $stmt = $conn->prepare("SELECT emp_id, fullname, email, password, u_id, current_session_id FROM employee WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();
            
            // Check if password is hashed (assuming bcrypt hash starts with $2y$)
            if (strpos($row["password"], '$2y$') === 0) {
                // Verify with password_verify if it's a hashed password
                $passwordMatch = password_verify($password, $row["password"]);
            } else {
                // Plain text comparison if not hashed
                $passwordMatch = ($password === $row["password"]);
            }
            
            if ($passwordMatch) {
                // Check if there's an active session
                if (!empty($row['current_session_id'])) {
                    // OPTION 1: Prevent login (recommended)
                    $loginErr = "This account is already logged in on another device.";
                    
                    // OPTION 2: Force logout the previous session (uncomment if preferred)
                    /*
                    $update_stmt = $conn->prepare("UPDATE employee SET current_session_id = NULL WHERE emp_id = ?");
                    $update_stmt->bind_param("i", $row["emp_id"]);
                    $update_stmt->execute();
                    $update_stmt->close();
                    */
                }
                
                // Only proceed if there's no active session or we're forcing logout
                if (empty($loginErr)) {
                    session_regenerate_id(true);
                    
                    $_SESSION["loggedin"] = true;
                    $_SESSION["emp_id"] = $row["emp_id"];
                    $_SESSION["fullname"] = $row["fullname"];
                    $_SESSION["email"] = $row["email"];
                    $_SESSION["u_id"] = $row["u_id"];
                    
                    // Update the session in database
                    $update_stmt = $conn->prepare("UPDATE employee SET current_session_id = ?, last_session_activity = NOW() WHERE emp_id = ?");
                    $update_stmt->bind_param("si", session_id(), $row["emp_id"]);
                    $update_stmt->execute();
                    $update_stmt->close();
                    
                    $success = true;
                }
            } else {
                $loginErr = "Invalid email or password";
            }
        } else {
            $loginErr = "Invalid email or password";
        }
        
        $stmt->close();
    }
}

if (isset($conn) && $conn) {
    $conn->close();
}
?>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PCL - Login</title>
    <link rel="icon" href="assets/img/pcl.png" type="image/x-icon">
    <link rel="stylesheet" href="assets/css/logins.css">
    <style>
        /* Enhanced Modal Styles */
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
            width: 350px;
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
        
        /* Form Inputs */
        .modal-content input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        .modal-content input:focus {
            border-color: #800000;
            outline: none;
            box-shadow: 0 0 0 3px rgba(128, 0, 0, 0.1);
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
        

        
        .loader {
            border: 5px solid #f3f3f3;
            border-top: 5px solid #800000;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin-bottom: 10px;
        }
        
        .alert {
            background: linear-gradient(90deg, rgb(122, 0, 0), rgb(80, 0, 0));
            color: rgb(255, 255, 255);
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 15px;
            width: 100%;
            max-width: 400px;
            text-align: center;
            font-weight: bold;
            font-size: 18px;
        }
                
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        /* Forgot Password Link */
        #forgotLink {
            color: #800000;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s;
        }
        
        #forgotLink:hover {
            color: #a00000;
            text-decoration: underline;
        }
    </style>
</head>
<body oncontextmenu="return false" controlslist="nodownload">
    <video autoplay muted loop class="video-background">
        <source src="assets/vid/clip1.mp4" type="video/mp4">
        Your browser does not support the video tag.
    </video>

    <div class="loading-screen" id="loading-screen">
        <div class="loader"></div>
        <span>Loading...</span>
    </div>

    <div class="container">
        <div class="logo-container">
            <img src="assets/img/pcl.png" alt="PCL Logo">
            <h1>FLEET LEDGER</h1>
        </div>
        
        <?php if (!empty($loginErr)): ?>
            <div class="alert"><?php echo htmlspecialchars($loginErr); ?></div>
        <?php endif; ?>
        
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" autocomplete="off" onsubmit="showLoading()">            
            <div class="parent-container">
                <div class="login-container">
                    <div class="input-container">
                        <div class="input-wrapper">
                            <input type="email" placeholder="Email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>">
                            <img src="assets/img/log_icon.png" class="input-icon" alt="Email icon">
                        </div>
                        <span class="error"><?php echo $emailErr; ?></span>
                    </div>

                    <div class="input-container">
                        <div class="input-wrapper">
                            <input type="password" placeholder="Password" id="password" name="password">
                            <img src="assets/img/pas_icon.png" class="input-icon" alt="Password icon">
                        </div>
                        <span class="error"><?php echo $passwordErr; ?></span>
                    </div>
                    <a href="#" id="forgotLink" style="color: white;">Forgot Password?</a>

                    <button class="login-button" type="submit">Login</button>
                </div>
            </div>
        </form>
    </div>

    <?php if ($success): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById("loading-screen").style.display = "flex";
            setTimeout(function() {
                window.location.href = "landingPage.php";
            }, 500); 
        });
    </script>
    <?php endif; ?>

    <!-- Enhanced Forgot Password Modal -->
    <div id="forgotPasswordModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal('forgotPasswordModal')">&times;</span>
            <div class="modal-header">
                <h2>Forgot Password</h2>
            </div>
            <div class="email-display" id="forgotEmailDisplay">
                Enter your email address
            </div>
            <form id="forgotPasswordForm">
                <input type="email" id="forgotEmail" name="email" placeholder="Your email address" required>
                <button type="submit" class="btn-maroon">Send OTP</button>
            </form>
        </div>
    </div>

    <!-- Enhanced OTP Verification Modal -->
    <div id="otpModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal('otpModal')">&times;</span>
            <div class="modal-header">
                <h2>OTP Verification</h2>
            </div>
            <div class="email-display" id="otpEmailDisplay"></div>
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

    <!-- Enhanced New Password Modal -->
    <div id="newPasswordModal" class="modal">
        <div class="modal-content">
            <span class="close-modal" onclick="closeModal('newPasswordModal')">&times;</span>
            <div class="modal-header">
                <h2>Reset Password</h2>
            </div>
            <div class="email-display" id="resetEmailDisplay"></div>
            <form id="resetPasswordForm">
                <input type="hidden" id="resetEmail" name="email">
                <input type="password" id="newPassword" placeholder="New Password" required>
                <input type="password" id="confirmPassword" placeholder="Confirm New Password" required>
                <button type="submit" class="btn-maroon">Reset Password</button>
            </form>
        </div>
    </div>

    <script>
        function showLoading() {
    document.getElementById("loading-screen").style.display = "flex";
}
        // Loading screen control
        window.addEventListener('load', function() {
            document.getElementById('loading-screen').style.display = 'none';
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
            document.getElementById('forgotEmailDisplay').textContent = "Enter your email address";
            document.getElementById('forgotEmail').value = "";
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
            
            if (!email) {
                alert('Please enter your email address');
                return;
            }
            
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
                    // Store email for later use
                    localStorage.setItem('resetEmail', email);
                    
                    // Update UI
                    document.getElementById('otpEmailDisplay').textContent = "OTP sent to: " + email;
                    document.getElementById('resetEmailDisplay').textContent = "Reset password for: " + email;
                    document.getElementById('resetEmail').value = email;
                    
                    // Clear any previous OTP inputs
                    otpInputs.forEach(input => {
                        input.value = '';
                    });
                    
                    // Switch modals
                    closeModal('forgotPasswordModal');
                    openModal('otpModal');
                    otpInputs[0].focus();
                } else {
                    alert(data.message || 'Failed to send OTP. Please try again.');
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
            const email = localStorage.getItem('resetEmail');
            
            if (!email) {
                alert('Email is missing. Please start the process again.');
                return;
            }
            
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
                    localStorage.removeItem('resetEmail');
                    window.location.href = 'login.php';
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