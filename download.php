<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type']) || !$_SESSION['admin_logged_in']) {
    header("Location: admin_login.php");
    exit();
}

// MySQL Database Connection
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

// Fetch record with role-based access
$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];

try {
    if ($user_type === 'super_admin') {
        $stmt = $db->prepare("SELECT * FROM visitors WHERE id = :id");
        $stmt->execute([':id' => $id]);
    } else {
        $stmt = $db->prepare("SELECT * FROM visitors WHERE id = :id AND receptionist_id = :user_id");
        $stmt->execute([':id' => $id, ':user_id' => $user_id]);
    }
    
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$data) {
        $_SESSION['errors'] = ["Record not found or access denied"];
        header("Location: admin_dashboard.php");
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['errors'] = ["Database error: " . $e->getMessage()];
    header("Location: admin_dashboard.php");
    exit();
}

// Path to the PDF file
$pdf_path = "id_cards/{$id}.pdf";

if (!file_exists($pdf_path)) {
    $_SESSION['errors'] = ["Document not found"];
    header("Location: admin_dashboard.php");
    exit();
}

// Serve the PDF file for download
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="Visitor_ID_Card_' . $id . '.pdf"');
header('Content-Length: ' . filesize($pdf_path));
header('Cache-Control: no-cache');
readfile($pdf_path);
exit;
?>