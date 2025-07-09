<?php
session_start();

// Redirect if already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: admin_dashboard.php");
    exit;
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "registration_db";
$conn = new mysqli($servername, $username, $password);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database
$sql = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($sql) !== TRUE) {
    die("Error creating database: " . $conn->error);
}
$conn->select_db($dbname);

// Create users table
$sql = "CREATE TABLE IF NOT EXISTS users1 (
    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    mobile VARCHAR(15) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    role ENUM('receptionist', 'super_admin') NOT NULL DEFAULT 'receptionist',
    reg_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if ($conn->query($sql) !== TRUE) {
    die("Error creating table: " . $conn->error);
}

// Create user_tokens table
$sql = "CREATE TABLE IF NOT EXISTS user_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at DATETIME NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users1(id) ON DELETE CASCADE
)";
if ($conn->query($sql) !== TRUE) {
    die("Error creating table: " . $conn->error);
}

// Initialize variables
$mobile = $password = "";
$mobile_err = $password_err = $login_err = "";

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate mobile
    if (empty(trim($_POST["mobile"]))) {
        $mobile_err = "Please enter your mobile number.";
    } elseif (!preg_match("/^\+?[0-9]{10,15}$/", trim($_POST["mobile"]))) {
        $mobile_err = "Invalid mobile number (10-15 digits, optional + prefix).";
    } else {
        $mobile = trim($_POST["mobile"]);
    }
    
    // Validate password
    if (empty(trim($_POST["password"]))) {
        $password_err = "Please enter your password.";
    } else {
        $password = trim($_POST["password"]);
    }
    
    // Validate credentials
    if (empty($mobile_err) && empty($password_err)) {
        $sql = "SELECT id, mobile, password, name, role FROM users1 WHERE mobile = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $param_mobile);
            $param_mobile = $mobile;
            if ($stmt->execute()) {
                $stmt->store_result();
                if ($stmt->num_rows == 1) {
                    $stmt->bind_result($id, $mobile, $hashed_password, $name, $role);
                    if ($stmt->fetch() && password_verify($password, $hashed_password)) {
                        session_start();
                        $_SESSION['admin_logged_in'] = true;
                        $_SESSION['user_id'] = $id;
                        $_SESSION['mobile'] = $mobile;
                        $_SESSION['name'] = $name;
                        $_SESSION['user_type'] = $role; // Use role as user_type for consistency

                        // Handle "Remember me"
                        if (isset($_POST['remember'])) {
                            $token = bin2hex(random_bytes(32));
                            $expires_at = date('Y-m-d H:i:s', strtotime('+30 days'));
                            $stmt = $conn->prepare("DELETE FROM user_tokens WHERE user_id = ?");
                            $stmt->bind_param("i", $id);
                            $stmt->execute();
                            $stmt = $conn->prepare("INSERT INTO user_tokens (user_id, token, expires_at) VALUES (?, ?, ?)");
                            $stmt->bind_param("iss", $id, $token, $expires_at);
                            $stmt->execute();
                            setcookie('remember_user', $id, time() + (86400 * 30), "/");
                            setcookie('remember_token', $token, time() + (86400 * 30), "/");
                        }

                        header("Location: admin_dashboard.php");
                        exit;
                    } else {
                        $login_err = "Invalid mobile number or password.";
                    }
                } else {
                    $login_err = "Invalid mobile number or password.";
                }
            } else {
                $login_err = "Oops! Something went wrong.";
            }
            $stmt->close();
        }
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Visitor Registration</title>
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
        .login-container {
            max-width: 420px;
            width: 90%;
            background-color: #fff;
            border-radius: 12px;
            box-shadow: var(--box-shadow);
            overflow: hidden;
            position: relative;
        }
        .login-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 30px 25px;
            text-align: center;
        }
        .login-header h2 {
            margin: 0;
            font-weight: 600;
            letter-spacing: 1px;
        }
        .login-header p {
            margin: 10px 0 0;
            opacity: 0.8;
            font-size: 14px;
        }
        .login-form {
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
        .register-link {
            text-align: center;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e1e5eb;
            font-size: 14px;
            color: #4a5568;
        }
        .register-link a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .register-link a:hover {
            color: var(--primary-hover);
            text-decoration: underline;
        }
        .remember-me {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        .remember-me input {
            margin-right: 8px;
        }
        .forgot-password {
            text-align: right;
            margin-bottom: 20px;
        }
        .forgot-password a {
            color: #718096;
            font-size: 13px;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        .forgot-password a:hover {
            color: var(--primary-color);
        }
        @media (max-width: 480px) {
            .login-container {
                width: 95%;
                border-radius: 10px;
            }
            .login-header {
                padding: 20px 15px;
            }
            .login-form {
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
        .success-message {
            color: var(--success-color);
            margin-top: 5px;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h2>VISITOR REGISTRATION</h2>
            <p>Sign in to access your account</p>
        </div>
        <div class="login-form">
            <?php if (!empty($login_err)): ?>
                <div class="error-message shake"><i class="fas fa-exclamation-circle"></i><?php echo htmlspecialchars($login_err); ?></div>
            <?php endif; ?>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
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
                <div class="remember-me">
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember" style="display: inline; margin: 0;">Remember me</label>
                </div>
                <div class="forgot-password">
                    <a href="forgot-password.php">Forgot password?</a>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn">
                        <i class="fas fa-sign-in-alt"></i> Sign In
                    </button>
                </div>
            </form>
            <div class="register-link">
                <p>Don't have an account? <a href="register.php">Create Account</a></p>
            </div>
        </div>
    </div>
</body>
</html>