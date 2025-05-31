<?php
session_start();
require_once 'db_connect.php';

// التحقق من تسجيل الدخول
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // جمع بيانات النموذج
    $property_type = $_POST['property_type'];
    $transaction_type = $_POST['transaction_type'];
    $wilaya = $_POST['wilaya'];
    $commune = $_POST['commune'];
    $address = $_POST['address'];
    $surface = $_POST['surface'];
    $bedrooms = $_POST['bedrooms'];
    $bathrooms = $_POST['bathrooms'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $price_period = isset($_POST['price_period']) ? $_POST['price_period'] : null;
    $features = isset($_POST['features']) ? implode(',', $_POST['features']) : '';
    $contact_name = $_POST['contact_name'];
    $contact_phone = $_POST['contact_phone'];
    $contact_email = $_POST['contact_email'];
    
    // حفظ الإعلان في قاعدة البيانات بحالة "في انتظار الموافقة"
    $stmt = $conn->prepare("INSERT INTO annonces (user_id, property_type, transaction_type, wilaya, commune, address, surface, bedrooms, bathrooms, description, price, price_period, features, contact_name, contact_phone, contact_email, status, date_publication) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())");
    
    $stmt->bind_param("isssssiisissssss", $user_id, $property_type, $transaction_type, $wilaya, $commune, $address, $surface, $bedrooms, $bathrooms, $description, $price, $price_period, $features, $contact_name, $contact_phone, $contact_email);
    
    if ($stmt->execute()) {
        $annonce_id = $stmt->insert_id;
        
        // معالجة تحميل الصور
        if (!empty($_FILES['property_images']['name'][0])) {
            $upload_dir = 'uploads/annonces/' . $annonce_id . '/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $images = [];
            foreach ($_FILES['property_images']['tmp_name'] as $key => $tmp_name) {
                $file_name = $_FILES['property_images']['name'][$key];
                $file_ext = pathinfo($file_name, PATHINFO_EXTENSION);
                $new_file_name = uniqid() . '.' . $file_ext;
                $file_path = $upload_dir . $new_file_name;
                
                if (move_uploaded_file($tmp_name, $file_path)) {
                    $images[] = $file_path;
                }
            }
            
            // حفظ مسارات الصور في قاعدة البيانات
            if (!empty($images)) {
                $images_str = implode(',', $images);
                $update_stmt = $conn->prepare("UPDATE annonces SET image_url = ? WHERE id = ?");
                $update_stmt->bind_param("si", $images_str, $annonce_id);
                $update_stmt->execute();
            }
        }
        
        $_SESSION['success_message'] = "تم إرسال إعلانك بنجاح وهو في انتظار الموافقة من قبل الإدارة.";
        header('Location: account.php');
        exit();
    } else {
        $_SESSION['error_message'] = "حدث خطأ أثناء حفظ الإعلان. يرجى المحاولة مرة أخرى.";
        header('Location: publish.php');
        exit();
    }
}
?>