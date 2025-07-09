<?php
session_start();

// Database connection
try {
    $db = new PDO('mysql:host=localhost;dbname=registration_db', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Check if user is logged in or restore via cookies
if (!isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in'] || !isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
    if (isset($_COOKIE['remember_user']) && isset($_COOKIE['remember_token'])) {
        $user_id = $_COOKIE['remember_user'];
        $token = $_COOKIE['remember_token'];
        try {
            $stmt = $db->prepare("SELECT user_id FROM user_tokens WHERE user_id = ? AND token = ? AND expires_at > NOW()");
            $stmt->execute([$user_id, $token]);
            if ($stmt->rowCount() == 1) {
                $stmt = $db->prepare("SELECT id, mobile, name, role FROM users1 WHERE id = ?");
                $stmt->execute([$user_id]);
                if ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $_SESSION['admin_logged_in'] = true;
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['mobile'];
                    $_SESSION['name'] = $user['name'];
                    $_SESSION['user_type'] = $user['role'];
                } else {
                    setcookie('remember_user', '', time() - 3600, "/");
                    setcookie('remember_token', '', time() - 3600, "/");
                    header("Location: login.php");
                    exit;
                }
            } else {
                setcookie('remember_user', '', time() - 3600, "/");
                setcookie('remember_token', '', time() - 3600, "/");
                header("Location: login.php");
                exit;
            }
        } catch (PDOException $e) {
            $_SESSION['errors'] = ["Authentication error: " . $e->getMessage()];
            header("Location: login.php");
            exit;
        }
    } else {
        header("Location: login.php");
        exit;
    }
}

// Fetch visitors based on role
$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];

try {
    // Check if created_at exists
    $stmt = $db->query("SHOW COLUMNS FROM visitors LIKE 'created_at'");
    $has_created_at = $stmt->rowCount() > 0;
    $order_by = $has_created_at ? 'v.created_at DESC' : 'v.id DESC';

    if ($user_type === 'super_admin') {
        $stmt = $db->query("SELECT v.*, u.name AS receptionist_name 
                            FROM visitors v 
                            LEFT JOIN users1 u ON v.receptionist_id = u.id 
                            ORDER BY $order_by");
    } else {
        $stmt = $db->prepare("SELECT v.*, u.name AS receptionist_name 
                              FROM visitors v 
                              LEFT JOIN users1 u ON v.receptionist_id = u.id 
                              WHERE v.receptionist_id = ? 
                              ORDER BY $order_by");
        $stmt->execute([$user_id]);
    }
    $visitors = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['errors'] = ["Database error: " . $e->getMessage()];
    $visitors = [];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visitor Management System - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #0b3d91;
            --secondary-color: #1e88e5;
            --accent-color: #4CAF50;
            --light-bg: #f5f5f5;
            --dark-text: #333333;
            --border-radius: 12px;
        }

        body {
            background-color: var(--light-bg);
            font-family: Arial, sans-serif;
            min-height: 100vh;
        }

        .app-container {
            max-width: 1200px;
            margin: 0 auto;
            background-color: white;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            min-height: 100vh;
        }

        .app-header {
            background-color: var(--primary-color);
            color: white;
            padding: 20px;
            text-align: center;
            border-bottom: 4px solid var(--secondary-color);
        }

        .app-header h1 {
            font-size: 1.5rem;
            margin: 0;
            font-weight: 600;
        }

        .menu-bar {
            background-color: #002c6d;
            color: white;
            display: flex;
            justify-content: flex-end;
            padding: 8px 15px;
            font-size: 0.9rem;
        }

        .menu-bar a {
            color: white;
            text-decoration: none;
            margin-left: 15px;
            display: flex;
            align-items: center;
        }

        .menu-bar a:hover {
            color: #cccccc;
        }

        .menu-bar i {
            margin-right: 5px;
        }

        .app-body {
            padding: 20px;
        }

        .table-responsive {
            margin-top: 20px;
        }

        .table th, .table td {
            vertical-align: middle;
        }

        .visitor-photo {
            max-width: 50px;
            border-radius: 8px;
        }

        .alert {
            border-radius: var(--border-radius);
            margin-bottom: 20px;
        }

        .btn-sm {
            padding: 5px 10px;
            font-size: 0.875rem;
        }
    </style>
</head>
<body>
    <div class="app-container">
        <div class="app-header">
            <h1><i class="fas fa-tachometer-alt"></i> VISITOR MANAGEMENT SYSTEM</h1>
        </div>
        <div class="menu-bar">
            <a href="index.php"><i class="fas fa-id-card-alt"></i> Register Visitor</a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
        <div class="app-body">
            <?php if (isset($_SESSION['message'])): ?>
                <div class="alert alert-success" role="alert">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($_SESSION['message']); unset($_SESSION['message']); ?>
                </div>
            <?php endif; ?>
            <?php if (isset($_SESSION['errors'])): ?>
                <div class="alert alert-danger" role="alert">
                    <i class="fas fa-exclamation-circle"></i> <?php echo implode('<br>', array_map('htmlspecialchars', $_SESSION['errors'])); unset($_SESSION['errors']); ?>
                </div>
            <?php endif; ?>
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>WhatsApp</th>
                            <th>Email</th>
                            <th>Coming From</th>
                            <th>Meeting With</th>
                            <th>Photo</th>
                            <!-- <th>OTP</th> -->
                            <?php if ($user_type === 'super_admin'): ?>
                                <th>Receptionist</th>
                            <?php endif; ?>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($visitors)): ?>
                            <tr>
                                <td colspan="<?php echo $user_type === 'super_admin' ? 10 : 9; ?>" class="text-center">No registrations found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($visitors as $visitor): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($visitor['id']); ?></td>
                                    <td><?php echo htmlspecialchars($visitor['name']); ?></td>
                                    <td><?php echo htmlspecialchars($visitor['whatsapp']); ?></td>
                                    <td><?php echo htmlspecialchars($visitor['email'] ?: 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($visitor['coming_from']); ?></td>
                                    <td><?php echo htmlspecialchars($visitor['meeting_to']); ?></td>
                                    <td><img src="<?php echo htmlspecialchars($visitor['selfie_path']); ?>" class="visitor-photo" alt="Visitor Photo"></td>
                                    <!-- <td><?php echo htmlspecialchars($visitor['otp']); ?></td> -->
                                    <?php if ($user_type === 'super_admin'): ?>
                                        <td><?php echo htmlspecialchars($visitor['receptionist_name'] ?: 'N/A'); ?></td>
                                    <?php endif; ?>
                                    <td>
                                        <a href="display.php?id=<?php echo $visitor['id']; ?>" class="btn btn-primary btn-sm" title="View Details"><i class="fas fa-eye"></i></a>
                                        <a href="download.php?id=<?php echo $visitor['id']; ?>" class="btn btn-success btn-sm" title="Download PDF"><i class="fas fa-download"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>