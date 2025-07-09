<?php
session_start();

require 'vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Database connection
try {
    $db = new PDO('mysql:host=localhost;dbname=registration_db', 'root', '');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Ensure visitors table exists
    $db->exec("CREATE TABLE IF NOT EXISTS visitors (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255),
        whatsapp VARCHAR(20) NOT NULL,
        coming_from VARCHAR(255) NOT NULL,
        meeting_to VARCHAR(255) NOT NULL,
        selfie_path VARCHAR(255) NOT NULL,
        receptionist_id INT(11) UNSIGNED,
        otp VARCHAR(6),
        FOREIGN KEY (receptionist_id) REFERENCES users1(id) ON DELETE SET NULL
    )");
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Check if user is logged in
$user_id = null;
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    $user_id = $_SESSION['user_id'];
} elseif (isset($_COOKIE['remember_user']) && isset($_COOKIE['remember_token'])) {
    $user_id = $_COOKIE['remember_user'];
    $token = $_COOKIE['remember_token'];
    $stmt = $db->prepare("SELECT user_id FROM user_tokens WHERE user_id = :user_id AND token = :token AND expires_at > NOW()");
    $stmt->execute([':user_id' => $user_id, ':token' => $token]);
    if ($stmt->rowCount() == 0) {
        setcookie('remember_user', '', time() - 3600, "/");
        setcookie('remember_token', '', time() - 3600, "/");
        header("Location: login.php");
        exit;
    }
    $stmt = $db->prepare("SELECT id, mobile, name, role FROM users1 WHERE id = :id");
    $stmt->execute([':id' => $user_id]);
    if ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['mobile'] = $user['mobile'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['user_type'] = $user['role'];
    } else {
        setcookie('remember_user', '', time() - 3600, "/");
        setcookie('remember_token', '', time() - 3600, "/");
        header("Location: login.php");
        exit;
    }
} else {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $whatsapp = $_POST['whatsapp'] ?? '';
    $coming_from = $_POST['coming_from'] ?? '';
    $meeting_to = $_POST['meeting_to'] ?? '';
    $selfie_data = $_POST['selfie_data'] ?? '';

    // Validate inputs
    if (empty($name) || empty($whatsapp) || empty($coming_from) || empty($meeting_to) || empty($selfie_data)) {
        die("All fields are required.");
    }
    if ($email && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Invalid email format.");
    }
    if (!preg_match("/^\+?[0-9]{10,15}$/", $whatsapp)) {
        die("Invalid WhatsApp number (10-15 digits, optional + prefix).");
    }

    // Process selfie
    if (preg_match('/^data:image\/(\w+);base64,/', $selfie_data, $type)) {
        $data = substr($selfie_data, strpos($selfie_data, ',') + 1);
        $type = strtolower($type[1]);
        if (!in_array($type, ['png', 'jpeg'])) {
            die("Unsupported image type: $type");
        }
        $data = base64_decode($data);
        if ($data === false) {
            die("Base64 decode failed.");
        }
        if (!file_exists('uploads/selfies')) {
            mkdir('uploads/selfies', 0755, true);
        }
        $selfie_path = 'uploads/selfies/selfie_' . time() . '.' . $type;
        file_put_contents($selfie_path, $data);
        if (!file_exists($selfie_path)) {
            die("Selfie file not found after saving.");
        }
    } else {
        die("Invalid selfie data.");
    }

    // static OTP
    $otp = "123456"; // e.g., 123456

    // Insert into visitors table
    try {
        $stmt = $db->prepare("INSERT INTO visitors (name, email, whatsapp, coming_from, meeting_to, selfie_path, receptionist_id, otp)
                              VALUES (:name, :email, :whatsapp, :coming_from, :meeting_to, :selfie_path, :receptionist_id, :otp)");
        $stmt->execute([
            ':name' => $name,
            ':email' => $email ?: null,
            ':whatsapp' => $whatsapp,
            ':coming_from' => $coming_from,
            ':meeting_to' => $meeting_to,
            ':selfie_path' => $selfie_path,
            ':receptionist_id' => $user_id,
            ':otp' => $otp
        ]);
        $id = $db->lastInsertId();
    } catch (PDOException $e) {
        die("Database error: " . $e->getMessage());
    }

    // Fetch inserted data
    $stmt = $db->prepare("SELECT * FROM visitors WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$data) {
        die("Failed to fetch visitor data.");
    }

    // Prepare selfie for PDF and email
    $selfie_content = file_get_contents($data['selfie_path']);
    if ($selfie_content === false) {
        die("Failed to read selfie file.");
    }
    $selfie_data_uri = 'data:image/' . $type . ';base64,' . base64_encode($selfie_content);
    if (!preg_match('/^data:image\/(png|jpeg);base64,/', $selfie_data_uri)) {
        die("Invalid Data URI format for selfie.");
    }

    // Generate PDF
    $options = new Options();
    $options->set('isRemoteEnabled', false);
    $options->set('chroot', dirname(__FILE__));
    $options->set('isHtml5ParserEnabled', true);
    $dompdf = new Dompdf($options);

    // Set dimensions to 100mm x 60mm
    $width_mm = 100;
    $height_mm = 60;
    $width_pt = $width_mm * 2.83465;
    $height_pt = $height_mm * 2.83465;

    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <style>
            @page {
                margin: 0;
                size: ' . $width_mm . 'mm ' . $height_mm . 'mm;
            }
            body {
                margin: 0;
                padding: 0;
                font-family: Arial, sans-serif;
                width: ' . $width_mm . 'mm;
                height: ' . $height_mm . 'mm;
                background-color: white;
                box-sizing: border-box;
            }
            .id-card {
                width: 100%;
                height: 100%;
                border: 0.5mm solid #4CAF50;
                box-sizing: border-box;
                position: relative;
                border-radius: 2mm;
                overflow: hidden;
            }
            .title-bg {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 10mm;
                background-color: #4CAF50;
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
                height: calc(' . $height_mm . 'mm - 12mm);
                display: block;
                padding: 0 3mm;
                box-sizing: border-box;
            }
            .selfie-container {
                width: 35%;
                float: left;
                text-align: center;
                padding: 3mm 0;
            }
            .selfie {
                width: 28mm;
                height: 35mm;
                object-fit: cover;
                border-radius: 1.5mm;
            }
            .details {
                width: 65%;
                float: left;
                padding: 3mm 0 3mm 3mm;
                font-size: 8.5pt;
                color: #333333;
                box-sizing: border-box;
            }
            .details div {
                margin-bottom: 2mm;
                line-height: 5.5mm;
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
            .content:after {
                content: "";
                display: table;
                clear: both;
            }
        </style>
    </head>
    <body>
        <div class="id-card">
            <div class="title-bg"></div>
            <div class="title">Visitor ID Card</div>
            <div class="content">
                <div class="selfie-container">
                    <img src="' . $selfie_data_uri . '" class="selfie" alt="Selfie" />
                </div>
                <div class="details">
                    <div><span class="detail-label">ID:</span> #' . $id . '</div>
                    <div><span class="detail-label">Name:</span> ' . htmlspecialchars(substr($data['name'], 0, 40)) . '</div>
                    ' . ($data['email'] ? '<div><span class="detail-label">Email:</span> ' . htmlspecialchars(substr($data['email'], 0, 40)) . '</div>' : '') . '
                    <div><span class="detail-label">WhatsApp:</span> ' . htmlspecialchars(substr($data['whatsapp'], 0, 40)) . '</div>
                    <div><span class="detail-label">From:</span> ' . htmlspecialchars(substr($data['coming_from'], 0, 40)) . '</div>
                    <div><span class="detail-label">Meeting:</span> ' . htmlspecialchars(substr($data['meeting_to'], 0, 40)) . '</div>
                    // <div><span class="detail-label">OTP:</span> ' . htmlspecialchars($otp) . '</div>
                </div>
            </div>
            <div class="footer">Valid for today\'s visit only · ' . date('d M Y') . '</div>
        </div>
    </body>
    </html>';

    $dompdf->loadHtml($html);
    $dompdf->setPaper(array(0, 0, $width_pt, $height_pt));
    $dompdf->render();

    if (!file_exists('id_cards')) {
        mkdir('id_cards', 0755, true);
    }
    $pdf_path = "id_cards/{$id}.pdf";
    file_put_contents($pdf_path, $dompdf->output());

    // Send email
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'lavanya.technotym@gmail.com';
        $mail->Password = 'huzcjhmtduunhrmp';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('lavanya.technotym@gmail.com', 'Registration System');
        $mail->addAddress('hegdelavanyap@gmail.com');
        $mail->addAttachment($pdf_path);

        $cid = 'selfie_' . uniqid();
        $mail->AddStringEmbeddedImage(
            file_get_contents($selfie_path),
            $cid,
            'selfie.' . $type,
            'base64',
            'image/' . $type
        );

        $mail->isHTML(true);
        $mail->Subject = 'New Visitor Registration - ID #' . $id;
        $mail->Body = "
            <h3 style='text-align: center; color: #4CAF50;'>New Visitor Notification</h3>
            <p>A new visitor has registered to visit your place. Below are the details:</p>
            <div style='max-width: 500px; margin: 0 auto; border: 2px solid #4CAF50; padding: 0; border-radius: 5px; font-family: Arial, sans-serif; overflow: hidden;'>
                <div style='background-color: #4CAF50; color: white; padding: 10px; text-align: center; text-transform: uppercase; letter-spacing: 1px; font-weight: bold;'>
                    Visitor ID Card
                </div>
                <div style='padding: 15px; display: flex;'>
                    <div style='width: 35%; text-align: center;'>
                        <img src='cid:{$cid}' alt='Visitor Selfie' style='width: 100%; max-width: 110px; border-radius: 5px;' />
                    </div>
                    <div style='width: 65%; padding-left: 15px;'>
                        <div style='margin: 5px 0;'><strong style='color: #388E3C; display: inline-block; width: 80px;'>ID:</strong> #{$id}</div>
                        <div style='margin: 5px 0;'><strong style='color: #388E3C; display: inline-block; width: 80px;'>Name:</strong> {$data['name']}</div>
                        " . ($data['email'] ? "<div style='margin: 5px 0;'><strong style='color: #388E3C; display: inline-block; width: 80px;'>Email:</strong> {$data['email']}</div>" : '') . "
                        <div style='margin: 5px 0;'><strong style='color: #388E3C; display: inline-block; width: 80px;'>WhatsApp:</strong> {$data['whatsapp']}</div>
                        <div style='margin: 5px 0;'><strong style='color: #388E3C; display: inline-block; width: 80px;'>From:</strong> {$data['coming_from']}</div>
                        <div style='margin: 5px 0;'><strong style='color: #388E3C; display: inline-block; width: 80px;'>Meeting:</strong> {$data['meeting_to']}</div>
                        <div style='margin: 5px 0;'><strong style='color: #388E3C; display: inline-block; width: 80px;'>OTP:</strong> {$otp}</div>
                    </div>
                </div>
                <div style='text-align: center; font-size: 12px; color: #757575; padding: 5px; border-top: 1px solid #eee;'>
                    Valid for today's visit only · " . date('d M Y') . "
                </div>
            </div>
            <p style='text-align: center;'>The ID card PDF is attached for your reference.</p>
            <p style='text-align: center; font-size: 12px; color: #777;'>Sent on " . date('Y-m-d H:i:s') . "</p>
        ";
        $mail->send();

        header("Location: display.php?id=$id");
        exit;
    } catch (Exception $e) {
        die("Email error: {$mail->ErrorInfo}");
    }
} else {
    die("Invalid request.");
}
?>