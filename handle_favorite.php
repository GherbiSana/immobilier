<?php
session_start();
require_once 'db_connect.php';

header('Content-Type: application/json; charset=utf-8');

// التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode([
        'status' => 'error',
        'message' => 'يجب تسجيل الدخول أولاً'
    ]);
    exit;
}

// التحقق من طريقة الطلب
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'طريقة الطلب غير مسموحة'
    ]);
    exit;
}

// التحقق من وجود البيانات المطلوبة
if (!isset($_POST['property_id']) || !isset($_POST['action'])) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'بيانات الطلب ناقصة'
    ]);
    exit;
}

// تنظيف وفلترة المدخلات
$user_id = (int)$_SESSION['user_id'];
$property_id = filter_input(INPUT_POST, 'property_id', FILTER_VALIDATE_INT);
$action = trim($_POST['action']);

// التحقق من صحة البيانات
if ($user_id <= 0 || $property_id <= 0) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'معرّفات غير صالحة'
    ]);
    exit;
}

if (!in_array($action, ['add', 'remove'])) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'إجراء غير معروف'
    ]);
    exit;
}

try {
    // التحقق من وجود العقار
    $check_property = $conn->prepare("SELECT id FROM annonces WHERE id = ?");
    $check_property->bind_param("i", $property_id);
    $check_property->execute();
    
    if ($check_property->get_result()->num_rows === 0) {
        http_response_code(404);
        echo json_encode([
            'status' => 'error',
            'message' => 'العقار غير موجود'
        ]);
        exit;
    }

    if ($action === 'add') {
        // التحقق من عدم وجود العقار في المفضلة مسبقاً
        $check_favorite = $conn->prepare("SELECT id FROM favorites WHERE user_id = ? AND property_id = ?");
        $check_favorite->bind_param("ii", $user_id, $property_id);
        $check_favorite->execute();
        
        if ($check_favorite->get_result()->num_rows > 0) {
            echo json_encode([
                'status' => 'info',
                'message' => 'العقار موجود بالفعل في المفضلة'
            ]);
            exit;
        }

        // إضافة إلى المفضلة
        $add_favorite = $conn->prepare("INSERT INTO favorites (user_id, property_id, date_added) VALUES (?, ?, NOW())");
        $add_favorite->bind_param("ii", $user_id, $property_id);
        
        if ($add_favorite->execute()) {
            echo json_encode([
                'status' => 'success',
                'message' => 'تمت الإضافة إلى المفضلة بنجاح',
                'data' => [
                    'favorite_id' => $conn->insert_id
                ]
            ]);
        } else {
            throw new Exception('فشل في إضافة العقار إلى المفضلة');
        }
    } else {
        // إزالة من المفضلة
        $remove_favorite = $conn->prepare("DELETE FROM favorites WHERE user_id = ? AND property_id = ?");
        $remove_favorite->bind_param("ii", $user_id, $property_id);
        
        if ($remove_favorite->execute()) {
            if ($remove_favorite->affected_rows > 0) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'تمت الإزالة من المفضلة بنجاح'
                ]);
            } else {
                echo json_encode([
                    'status' => 'info',
                    'message' => 'العقار غير موجود في المفضلة'
                ]);
            }
        } else {
            throw new Exception('فشل في إزالة العقار من المفضلة');
        }
    }
    
} catch (Exception $e) {
    http_response_code(500);
    error_log("Favorite Error: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'حدث خطأ في الخادم'
    ]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
?>s