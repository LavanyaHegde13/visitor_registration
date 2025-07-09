<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || !isset($_SESSION['admin_logged_in']) || !$_SESSION['admin_logged_in']) {
    header("Location: admin_login.php");
    exit();
}

// Database connection
try {
    $db = new PDO('mysql:host=localhost;dbname=registration_db', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    $_SESSION['errors'] = ["Database error: " . $e->getMessage()];
    header("Location: admin_dashboard.php");
    exit();
}

// Get ID from URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['errors'] = ["Invalid ID"];
    header("Location: admin_dashboard.php");
    exit();
}
$id = (int)$_GET['id'];

// Fetch record with receptionist name
$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];

try {
    if ($user_type === 'super_admin') {
        $stmt = $db->prepare("SELECT v.*, u.name AS receptionist_name 
                             FROM visitors v 
                             LEFT JOIN users1 u ON v.receptionist_id = u.id 
                             WHERE v.id = :id");
        $stmt->execute([':id' => $id]);
    } else {
        $stmt = $db->prepare("SELECT v.*, u.name AS receptionist_name 
                             FROM visitors v 
                             LEFT JOIN users1 u ON v.receptionist_id = u.id 
                             WHERE v.id = :id AND v.receptionist_id = :user_id");
        $stmt->execute([':id' => $id, ':user_id' => $user_id]);
    }
    
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$data) {
        $_SESSION['errors'] = ["Record not found or access denied"];
        header("Location: admin_dashboard.php");
        exit();
    }

    // Verify selfie exists
    $selfie_path = $data['selfie_path'];
    if (!file_exists($selfie_path) || !is_readable($selfie_path)) {
        $selfie_path = 'Uploads/selfies/default.png'; // Fallback image
    }
} catch (PDOException $e) {
    $_SESSION['errors'] = ["Database error: " . $e->getMessage()];
    header("Location: admin_dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visitor ID Card - ID #<?php echo $id; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #0b3d91;
            --secondary-color: #1e88e5;
            --accent-color: #4CAF50;
            --light-bg: #f5f5f5;
        }

        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            background-color: var(--light-bg);
            font-family: Arial, sans-serif;
        }

        .id-card-container {
            max-width: 100mm;
            margin: 20px;
        }

        .id-card {
            width: 100mm;
            height: 60mm;
            background-color: white;
            border: 0.5mm solid var(--accent-color);
            padding: 2mm;
            box-sizing: border-box;
            position: relative;
            box-shadow: 0 3px 6px rgba(0,0,0,0.16);
            border-radius: 2mm;
            overflow: hidden;
        }

        .title-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 10mm;
            background-color: var(--accent-color);
            border-bottom: 0.3mm solid #388E3C;
        }

        .title {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            text-align: center;
            color: white;
            font-size: 11pt;
            font-weight: bold;
            line-height: 10mm;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.5mm;
        }

        .content {
            position: absolute;
            top: 12mm;
            left: 0;
            width: 100%;
            height: calc(60mm - 12mm);
            display: flex;
            align-items: center;
            padding: 0 3mm;
            box-sizing: border-box;
        }

        .selfie-container {
            width: 35%;
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 3mm 0;
        }

        .selfie {
            width: 28mm;
            height: 35mm;
            border: none;
            object-fit: cover;
            border-radius: 1.5mm;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }

        .details {
            width: 65%;
            height: 100%;
            padding: 3mm 0 3mm 3mm;
            font-size: 8.5pt;
            color: #333333;
            line-height: 5.5mm;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .details div {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            margin-bottom: 2mm;
            position: relative;
        }

        .detail-label {
            font-weight: bold;
            color: #388E3C;
            display: inline-block;
            width: 18mm;
        }

        .footer {
            position: absolute;
            bottom: 2mm;
            left: 0;
            width: 100%;
            text-align: center;
            font-size: 6pt;
            color: #757575;
        }

        .nav-buttons {
            margin-top: 10px;
            text-align: center;
        }

        @media (max-width: 400px) {
            .id-card {
                transform: scale(0.8);
                transform-origin: top center;
            }
        }
    </style>
</head>
<body>
    <div class="id-card-container">
        <div class="id-card">
            <div class="title-bg"></div>
            <div class="title">Visitor ID Card</div>
            <div class="content">
                <div class="selfie-container">
                    <img src="<?php echo htmlspecialchars($selfie_path); ?>" alt="Selfie" class="selfie">
                </div>
                <div class="details">
                    <div><span class="detail-label">ID:</span> #<?php echo $id; ?></div>
                    <div><span class="detail-label">Name:</span> <?php echo htmlspecialchars(substr($data['name'], 0, 40)); ?></div>
                    <?php if ($data['email']): ?>
                        <div><span class="detail-label">Email:</span> <?php echo htmlspecialchars(substr($data['email'], 0, 40)); ?></div>
                    <?php endif; ?>
                    <div><span class="detail-label">WhatsApp:</span> <?php echo htmlspecialchars(substr($data['whatsapp'], 0, 40)); ?></div>
                    <div><span class="detail-label">From:</span> <?php echo htmlspecialchars(substr($data['coming_from'], 0, 40)); ?></div>
                    <div><span class="detail-label">Meeting:</span> <?php echo htmlspecialchars(substr($data['meeting_to'], 0, 40)); ?></div>
                    <!-- <div><span class="detail-label">OTP:</span> <?php echo htmlspecialchars($data['otp']); ?></div> -->
                    <?php if ($user_type === 'super_admin'): ?>
                        <div><span class="detail-label">Receptionist:</span> <?php echo htmlspecialchars($data['receptionist_name'] ?: 'N/A'); ?></div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="footer">Valid for today's visit only · <?php echo date('d M Y'); ?></div>
        </div>
        <div class="nav-buttons">
            <a href="admin_dashboard.php" class="btn btn-primary btn-sm"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
            <a href="download.php?id=<?php echo $id; ?>" class="btn btn-success btn-sm"><i class="fas fa-download"></i> Download PDF</a>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>