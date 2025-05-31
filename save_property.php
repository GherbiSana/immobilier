<?php
// اختبار بسيط أولاً
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
header('Content-Type: application/json');

// تسجيل محاولة الوصول
file_put_contents('debug.log', date('Y-m-d H:i:s') . " - تم الوصول للملف\n", FILE_APPEND);

try {
    // التحقق من الطلب
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('طريقة الطلب غير صحيحة');
    }
    
    // تسجيل البيانات المرسلة
    file_put_contents('debug.log', "POST data: " . print_r($_POST, true) . "\n", FILE_APPEND);
    file_put_contents('debug.log', "SESSION data: " . print_r($_SESSION, true) . "\n", FILE_APPEND);
    
    // التحقق من تسجيل الدخول
    if (!isset($_SESSION['user']) && !isset($_SESSION['user_id'])) {
        throw new Exception('يجب تسجيل الدخول أولاً');
    }
    
    // الحصول على user_id
    $user_id = null;
    if (isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
    } elseif (isset($_SESSION['user']['id'])) {
        $user_id = $_SESSION['user']['id'];
    }
    
    if (!$user_id) {
        throw new Exception('لا يمكن تحديد هوية المستخدم');
    }
    
    // الحصول على بيانات الطلب
    $property_id = isset($_POST['property_id']) ? intval($_POST['property_id']) : 0;
    $action = isset($_POST['action']) ? $_POST['action'] : 'add';
    
    if ($property_id <= 0) {
        throw new Exception('معرف العقار غير صحيح');
    }
    
    // محاولة الاتصال بقاعدة البيانات
    $servername = "localhost";
    $username = "root";  // غير هذا حسب إعداداتك
    $password = "";      // غير هذا حسب إعداداتك
    $dbname = "immobilier";  // غير هذا حسب اسم قاعدة البيانات
    
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    if ($conn->connect_error) {
        throw new Exception("خطأ في الاتصال بقاعدة البيانات: " . $conn->connect_error);
    }
    
    // تنفيذ العملية
    if ($action === 'remove') {
        $stmt = $conn->prepare("DELETE FROM favorites WHERE user_id = ? AND property_id = ?");
        $stmt->bind_param("ii", $user_id, $property_id);
        
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'تم إزالة العقار من المفضلة']);
        } else {
            throw new Exception('خطأ في إزالة العقار');
        }
    } else {
        // التحقق من وجود العقار في المفضلة
        $check_stmt = $conn->prepare("SELECT id FROM favorites WHERE user_id = ? AND property_id = ?");
        $check_stmt->bind_param("ii", $user_id, $property_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        
        if ($result->num_rows > 0) {
            echo json_encode(['status' => 'info', 'message' => 'العقار موجود في المفضلة بالفعل']);
        } else {
            $stmt = $conn->prepare("INSERT INTO favorites (user_id, property_id, date_added) VALUES (?, ?, NOW())");
            $stmt->bind_param("ii", $user_id, $property_id);
            
            if ($stmt->execute()) {
                echo json_encode(['status' => 'success', 'message' => 'تم إضافة العقار للمفضلة']);
            } else {
                throw new Exception('خطأ في إضافة العقار للمفضلة: ' . $conn->error);
            }
        }
    }
    
    $conn->close();
    
} catch (Exception $e) {
    file_put_contents('debug.log', "Error: " . $e->getMessage() . "\n", FILE_APPEND);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>