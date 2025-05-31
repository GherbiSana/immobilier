<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

if (!isset($_FILES['avatar'])) {
    echo json_encode(['success' => false, 'message' => 'No file uploaded']);
    exit;
}

$file = $_FILES['avatar'];

// التحقق من أن الملف صورة
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
if (!in_array($file['type'], $allowedTypes)) {
    echo json_encode(['success' => false, 'message' => 'Invalid file type']);
    exit;
}

// إنشاء مجلد التحميل إذا لم يكن موجوداً
$uploadDir = 'uploads/avatars/';
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// إنشاء اسم فريد للملف
$fileName = uniqid('avatar_') . '.' . pathinfo($file['name'], PATHINFO_EXTENSION);
$filePath = $uploadDir . $fileName;

if (move_uploaded_file($file['tmp_name'], $filePath)) {
    // تحديث قاعدة البيانات بمسار الصورة الجديد
    try {
        $conn = new PDO('mysql:host=localhost;dbname=immobilier;charset=utf8mb4', 'root', '');
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $stmt = $conn->prepare("UPDATE users SET avatar = ? WHERE user_id = ?");
        $stmt->execute([$filePath, $_SESSION['user']['id']]);
        
        // تحديث جلسة المستخدم
        $_SESSION['user']['avatar'] = $filePath;
        
        echo json_encode([
            'success' => true,
            'avatarPath' => $filePath,
            'message' => 'Avatar updated successfully'
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to upload file']);
}
?>