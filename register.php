<?php
// Database connection parameters
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "registration_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Initialize variables
$name = $mobile = $password = $confirm_password = $role = "";
$name_err = $mobile_err = $password_err = $confirm_password_err = $role_err = $register_err = $success_message = "";

// Process form data when submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate name
    if (empty(trim($_POST["name"]))) {
        $name_err = "Please enter your full name.";
    } else {
        $name = trim($_POST["name"]);
    }

    // Validate mobile
    if (empty(trim($_POST["mobile"]))) {
        $mobile_err = "Please enter your mobile number.";
    } elseif (!preg_match("/^\+?[0-9]{10,15}$/", trim($_POST["mobile"]))) {
        $mobile_err = "Invalid mobile number (10-15 digits, optional + prefix).";
    } else {
        // Check if mobile exists
        $sql = "SELECT id FROM users1 WHERE mobile = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $param_mobile);
            $param_mobile = trim($_POST["mobile"]);
            $stmt->execute();
            $stmt->store_result();
            if ($stmt->num_rows == 1) {
                $mobile_err = "This mobile number is already registered.";
            } else {
                $mobile = trim($_POST["mobile"]);
            }
            $stmt->close();
        }
    }

    // Validate password
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter a password.";
    } elseif (strlen(trim($_POST["password"])) < 6) {
        $password_err = "Password must have at least 6 characters.";
    } else {
        $password = trim($_POST["password"]);
    }

    // Validate confirm password
    if (empty(trim($_POST["confirm_password"]))) {
        $confirm_password_err = "Please confirm your password.";
    } elseif (trim($_POST["password"]) !== trim($_POST["confirm_password"])) {
        $confirm_password_err = "Passwords do not match.";
    }

    // Validate role
    if (empty(trim($_POST["role"]))) {
        $role_err = "Please select a role.";
    } elseif (!in_array(trim($_POST["role"]), ['receptionist', 'super_admin'])) {
        $role_err = "Invalid role.";
    } else {
        $role = trim($_POST["role"]);
    }

    // Check for errors before inserting
    if (empty($name_err) && empty($mobile_err) && empty($password_err) && empty($confirm_password_err) && empty($role_err)) {
        $sql = "INSERT INTO users1 (mobile, password, name, role) VALUES (?, ?, ?, ?)";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ssss", $param_mobile, $param_password, $param_name, $param_role);
            $param_mobile = $mobile;
            $param_password = password_hash($password, PASSWORD_DEFAULT);
            $param_name = $name;
            $param_role = $role;
            if ($stmt->execute()) {
                $success_message = "Registration successful! Please log in.";
                // Clear form fields
                $name = $mobile = $password = $confirm_password = $role = "";
            } else {
                $register_err = "Something went wrong. Please try again later.";
            }
            $stmt->close();
        } else {
            $register_err = "Database error. Please try again later.";
        }
    }
}

// Close connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | Visitor Registration</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap">
    <style>
        :root {
            --primary-color: #4361ee;
            --primary-hover: #3a56d4;
            --secondary-color: #4cc9f0;
            --dark-color: #2b2d42;
            --light-color: #f8f9fa;
            --danger-color: #ef476f;
            --success-color: #06d6a0;
            --box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            color: var(--dark-color);
        }
        .register-container {
            max-width: 420px;
            width: 90%;
            background-color: #fff;
            border-radius: 12px;
            box-shadow: var(--box-shadow);
            overflow: hidden;
            position: relative;
        }
        .register-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 30px 25px;
            text-align: center;
        }
        .register-header h2 {
            margin: 0;
            font-weight: 600;
            letter-spacing: 1px;
        }
        .register-header p {
            margin: 10px 0 0;
            opacity: 0.8;
            font-size: 14px;
        }
        .register-form {
            padding: 30px 25px;
        }
        .form-group {
            margin-bottom: 20px;
            position: relative;
        }
        .form-control {
            width: 100%;
            padding: 12px 15px 12px 40px;
            border: 1px solid #e1e5eb;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
            background-color: #f9fafc;
        }
        .form-control:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.1);
            background-color: #fff;
        }
        .form-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #a0aec0;
        }
        select.form-control {
            padding-left: 15px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            color: #4a5568;
            font-weight: 500;
            font-size: 14px;
        }
        .btn {
            width: 100%;
            padding: 12px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 500;
            transition: all 0.3s ease;
            font-family: 'Poppins', sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
        }
        .btn:hover {
            background-color: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(67, 97, 238, 0.2);
        }
        .btn:active {
            transform: translateY(0);
        }
        .error-message {
            color: var(--danger-color);
            margin-top: 5px;
            font-size: 13px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .success-message {
            color: var(--success-color);
            margin-bottom: 20px;
            font-size: 13px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .login-link {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e1e5eb;
            font-size: 14px;
            color: #4a5568;
        }
        .login-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .login-link a:hover {
            color: var(--primary-hover);
            text-decoration: underline;
        }
        @media (max-width: 480px) {
            .register-container {
                width: 95%;
                border-radius: 10px;
            }
            .register-header {
                padding: 20px 15px;
            }
            .register-form {
                padding: 20px 15px;
            }
        }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }
        .shake {
            animation: shake 0.82s cubic-bezier(.36,.07,.19,.97) both;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-header">
            <h2>CREATE ACCOUNT</h2>
            <p>Register to access the Visitor Management System</p>
        </div>
        <div class="register-form">
            <?php if (!empty($success_message)): ?>
                <div class="success-message"><i class="fas fa-check-circle"></i><?php echo htmlspecialchars($success_message); ?></div>
            <?php endif; ?>
            <?php if (!empty($register_err)): ?>
                <div class="error-message shake"><i class="fas fa-exclamation-circle"></i><?php echo htmlspecialchars($register_err); ?></div>
            <?php endif; ?>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <i class="fas fa-user form-icon"></i>
                    <input type="text" id="name" class="form-control" name="name" placeholder="Enter your full name" value="<?php echo htmlspecialchars($name); ?>">
                    <?php if (!empty($name_err)): ?>
                        <span class="error-message"><i class="fas fa-exclamation-circle"></i><?php echo htmlspecialchars($name_err); ?></span>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label for="mobile">Mobile Number</label>
                    <i class="fas fa-phone form-icon"></i>
                    <input type="tel" id="mobile" class="form-control" name="mobile" placeholder="Enter your mobile number" value="<?php echo htmlspecialchars($mobile); ?>">
                    <?php if (!empty($mobile_err)): ?>
                        <span class="error-message"><i class="fas fa-exclamation-circle"></i><?php echo htmlspecialchars($mobile_err); ?></span>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <i class="fas fa-lock form-icon"></i>
                    <input type="password" id="password" class="form-control" name="password" placeholder="Enter your password">
                    <?php if (!empty($password_err)): ?>
                        <span class="error-message"><i class="fas fa-exclamation-circle"></i><?php echo htmlspecialchars($password_err); ?></span>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm Password</label>
                    <i class="fas fa-lock form-icon"></i>
                    <input type="password" id="confirm_password" class="form-control" name="confirm_password" placeholder="Confirm your password">
                    <?php if (!empty($confirm_password_err)): ?>
                        <span class="error-message"><i class="fas fa-exclamation-circle"></i><?php echo htmlspecialchars($confirm_password_err); ?></span>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label for="role">Role</label>
                    <select id="role" class="form-control" name="role">
                        <option value="">Select role</option>
                        <option value="receptionist" <?php echo $role === 'receptionist' ? 'selected' : ''; ?>>Receptionist</option>
                        <option value="super_admin" <?php echo $role === 'super_admin' ? 'selected' : ''; ?>>Super Admin</option>
                    </select>
                    <?php if (!empty($role_err)): ?>
                        <span class="error-message"><i class="fas fa-exclamation-circle"></i><?php echo htmlspecialchars($role_err); ?></span>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn">
                        <i class="fas fa-user-plus"></i> Register
                    </button>
                </div>
            </form>
            <div class="login-link">
                <p>Already have an account? <a href="login.php">Sign In</a></p>
            </div>
        </div>
    </div>
</body>
</html>