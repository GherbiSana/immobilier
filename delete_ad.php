<?php
require_once 'db_connect.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'غير مسموح']);
    exit();
}

$ad_id = (int)$_POST['id'];

// التحقق من أن الإعلان يخص المستخدم
$stmt = $conn->prepare("SELECT user_id FROM annonces WHERE id = ?");
$stmt->bind_param("i", $ad_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'الإعلان غير موجود']);
    exit();
}

$ad = $result->fetch_assoc();

if ($ad['user_id'] != $_SESSION['user_id']) {
    echo json_encode(['success' => false, 'message' => 'غير مسموح']);
    exit();
}

// حذف الإعلان
$stmt = $conn->prepare("DELETE FROM annonces WHERE id = ?");
$stmt->bind_param("i", $ad_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'خطأ في الحذف']);
}