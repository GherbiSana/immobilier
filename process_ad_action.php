<?php
require_once 'db_connect.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('HTTP/1.1 403 Forbidden');
    exit();
}

$response = ['success' => false, 'message' => ''];

try {
    $adId = intval($_POST['ad_id']);
    $action = $_POST['action'];
    
    // التحقق من وجود الإعلان
    $stmt = $conn->prepare("SELECT * FROM annonces WHERE id = ?");
    $stmt->bind_param("i", $adId);
    $stmt->execute();
    $ad = $stmt->get_result()->fetch_assoc();
    
    if (!$ad) {
        throw new Exception("الإعلان غير موجود");
    }
    
    // معالجة الإجراء
    if ($action === 'approve') {
        $stmt = $conn->prepare("UPDATE annonces SET status = 'approved', approved_at = NOW(), approved_by = ? WHERE id = ?");
        $stmt->bind_param("ii", $_SESSION['user_id'], $adId);
        $stmt->execute();
        
        $response['message'] = "تمت الموافقة على الإعلان بنجاح";
    } 
    elseif ($action === 'reject') {
        $notes = $_POST['notes'] ?? '';
        
        $stmt = $conn->prepare("UPDATE annonces SET status = 'rejected', rejected_at = NOW(), rejected_by = ?, rejection_notes = ? WHERE id = ?");
        $stmt->bind_param("isi", $_SESSION['user_id'], $notes, $adId);
        $stmt->execute();
        
        $response['message'] = "تم رفض الإعلان بنجاح";
    }
    
    $response['success'] = true;
    
} catch (Exception $e) {
    $response['message'] = $e->getMessage();
}

header('Content-Type: application/json');
echo json_encode($response);
?>