<?php
// يجب أن لا يوجد أي مسافات بيضاء قبل هذه العلامة
ob_start(); // بدء تخزين الإخراج

include 'header.php';
require_once 'db_connect.php';

// التحقق من الجلسة
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    ob_end_flush(); // تنظيف المخزن المؤقت
    exit();
}

    
function processImagePath($image_path) {
    $base_dir = 'uploads/properties/';
    $default_image = 'assets/images/default-property.jpg';
    
    if (empty($image_path)) {
        return $default_image;
    }
    
    // إذا كان مسار خارجي (http)
    if (strpos($image_path, 'http') === 0) {
        return $image_path;
    }
    
    // تنظيف المسار المحلي
    $clean_path = str_replace(['../', './'], '', $image_path);
    
    // إذا كان المسار يحتوي على مجلد uploads/properties
    if (strpos($clean_path, 'uploads/properties/') !== false) {
        $final_path = $clean_path;
    } else {
        $final_path = $base_dir . basename($clean_path);
    }
    
    // التحقق من وجود الملف
    return file_exists($final_path) ? $final_path : $default_image;
}

$user_id = $_SESSION['user_id'];
$errors = [];
$success = '';

// استعلام قاعدة البيانات
$stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if (!$user) {
    header('Location: login.php');
    ob_end_flush();
    exit();
}

// معالجة تحديث الملف الشخصي
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);

    // التحقق من صحة البيانات
    if (empty($name)) {
        $errors[] = "الاسم مطلوب";
    }

    if (empty($email)) {
        $errors[] = "البريد الإلكتروني مطلوب";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "البريد الإلكتروني غير صالح";
    }

    if (!empty($phone) && !preg_match('/^[0-9]{10}$/', $phone)) {
        $errors[] = "رقم الهاتف يجب أن يتكون من 10 أرقام";
    }

    // إذا لم تكن هناك أخطاء، قم بالتحديث
    if (empty($errors)) {
        $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, Numéro_de_téléphone = ? WHERE user_id = ?");
        $stmt->bind_param("sssi", $name, $email, $phone, $user_id);
        
        if ($stmt->execute()) {
            $success = "تم تحديث الملف الشخصي بنجاح";
            // تحديث بيانات الجلسة
            $_SESSION['user']['name'] = $name;
            $_SESSION['user']['email'] = $email;
            $_SESSION['user']['Numéro_de_téléphone'] = $phone;
            // إعادة جلب بيانات المستخدم
            $stmt = $conn->prepare("SELECT * FROM users WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
        } else {
            $errors[] = "حدث خطأ أثناء تحديث الملف الشخصي";
        }
    }
}

// معالجة تغيير كلمة المرور
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $current_password = trim($_POST['current_password']);
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    // التحقق من صحة البيانات
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $errors[] = "جميع حقول كلمة المرور مطلوبة";
    } elseif (!password_verify($current_password, $user['password'])) {
        $errors[] = "كلمة المرور الحالية غير صحيحة";
    } elseif ($new_password !== $confirm_password) {
        $errors[] = "كلمة المرور الجديدة غير متطابقة";
    } elseif (strlen($new_password) < 8) {
        $errors[] = "كلمة المرور يجب أن تكون على الأقل 8 أحرف";
    }
    // إذا لم تكن هناك أخطاء، قم بتغيير كلمة المرور
    if (empty($errors)) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
        $stmt->bind_param("si", $hashed_password, $user_id);
        
        if ($stmt->execute()) {
            $success = "تم تغيير كلمة المرور بنجاح";
        } else {
            $errors[] = "حدث خطأ أثناء تغيير كلمة المرور";
        }
    }
}
// معالجة رفع الصورة الشخصية
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['upload_avatar'])) {
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] == UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = $_FILES['avatar']['type'];
        
        if (in_array($file_type, $allowed_types)) {
            $upload_dir = 'uploads/avatars/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            $file_ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
            $file_name = 'avatar_' . $user_id . '_' . time() . '.' . $file_ext;
            $file_path = $upload_dir . $file_name;
            
            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $file_path)) {
                // حذف الصورة القديمة إذا كانت موجودة
                if (!empty($user['avatar']) && file_exists($user['avatar'])) {
                    unlink($user['avatar']);
                }
                
                // تحديث المسار في قاعدة البيانات
                $stmt = $conn->prepare("UPDATE users SET avatar = ? WHERE user_id = ?");
                $stmt->bind_param("si", $file_path, $user_id);
                
                if ($stmt->execute()) {
                    $success = "تم تحديث الصورة الشخصية بنجاح";
                    $user['avatar'] = $file_path;
                    $_SESSION['user']['avatar'] = $file_path;
                } else {
                    $errors[] = "حدث خطأ أثناء حفظ الصورة في قاعدة البيانات";
                }
            } else {
                $errors[] = "حدث خطأ أثناء رفع الصورة";
            }
        } else {
            $errors[] = "نوع الملف غير مسموح به. يرجى رفع صورة (JPEG, PNG, GIF)";
        }
    } else {
        $errors[] = "حدث خطأ أثناء رفع الملف";
    }
}
// دالة لجلب عدد الإعلانات المعلقة
function getPendingAdsCount($conn) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM annonces WHERE status = 'pending'");
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_row()[0];
}

// دالة لجلب عدد المستخدمين الجدد (آخر 7 أيام)
function getNewUsersCount($conn) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_row()[0];
}
// تحقق من المفاتيح
$name = isset($user['name']) ? $user['name'] : 'Nom inconnu';
$email = isset($user['email']) ? $user['email'] : 'Email inconnu';
$phone = isset($user['Numéro_de_téléphone']) ? $user['Numéro_de_téléphone'] : '';
$avatar = isset($user['avatar']) ? $user['avatar'] : '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Homeverse - Mon Compte</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="shortcut icon" href="./favicon.svg" type="image/svg+xml">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="./assets/css/style.css">
    <link href="https://unpkg.com/boxicons@2.1.2/css/boxicons.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@400;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        /* أنماط لوحة التحكم */
.admin-stats {
    display: flex;
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    flex: 1;
    background: #f8f9fa;
    border-radius: 8px;
    padding: 20px;
    text-align: center;
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

.stat-card h3 {
    color: #2c3e50;
    margin-bottom: 10px;
    font-size: 16px;
}

.stat-card p {
    font-size: 24px;
    font-weight: bold;
    color: #3498db;
    margin-bottom: 10px;
}

.stat-card .view-all {
    color: #7f8c8d;
    font-size: 14px;
    text-decoration: none;
}

.quick-actions {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
}

.action-btn {
    flex: 1;
    min-width: 200px;
    background: #3498db;
    color: white;
    padding: 15px;
    border-radius: 8px;
    text-decoration: none;
    text-align: center;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    transition: all 0.3s;
}

.action-btn:hover {
    background: #2980b9;
    transform: translateY(-2px);
}

.action-btn i {
    font-size: 18px;
}
         * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f5f5f5;
            color: #333;
        }

        .account-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        /* En-tête */
        .account-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #ddd;
            margin-bottom: 30px;
        }

        .account-header h1 {
            color: #2c3e50;
            font-size: 24px;
        }

        .account-header nav a {
            margin-left: 20px;
            text-decoration: none;
            color: #7f8c8d;
            font-weight: 500;
        }

        .account-header nav a.active {
            color: #3498db;
            font-weight: 600;
        }

        /* Contenu principal */
        .account-main {
            display: flex;
            gap: 30px;
        }

        .account-sidebar {
            width: 250px;
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .account-content {
            flex: 1;
            background: white;
            border-radius: 8px;
            padding: 25px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        /* Profil utilisateur */
        .user-profile {
            text-align: center;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
            margin-bottom: 20px;
        }

        .avatar {
            width: 80px;
            height: 80px;
            background-color: #3498db;
            color: white;
            border-radius: 50%;
            margin: 0 auto 15px;
            position: relative;
            overflow: hidden;
            cursor: pointer;
            transition: all 0.3s;
        }

        .avatar:hover {
            transform: scale(1.05);
            box-shadow: 0 0 10px rgba(0,0,0,0.2);
        }

        #avatar-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: none;
        }

        .avatar-initials {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 32px;
            font-weight: bold;
        }

        .avatar.has-image .avatar-initials {
            display: none;
        }

        .avatar.has-image #avatar-image {
            display: block;
        }

        .user-profile h2 {
            font-size: 18px;
            margin-bottom: 5px;
        }

        .user-profile p {
            color: #7f8c8d;
            font-size: 14px;
        }

        /* Menu compte */
        .account-menu {
            display: flex;
            flex-direction: column;
        }

        .account-menu a {
            padding: 12px 15px;
            text-decoration: none;
            color: #34495e;
            border-radius: 5px;
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            transition: all 0.3s;
            cursor: pointer;
        }

        .account-menu a i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }

        .account-menu a:hover {
            background-color: #f8f9fa;
            color: #3498db;
        }

        .account-menu a.active {
            background-color: #e8f4fc;
            color: #3498db;
            font-weight: 500;
        }

        /* Sections de contenu */
        .content-section {
            display: none;
        }

        .content-section.active {
            display: block;
        }

        .content-header {
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .content-header h2 {
            color: #2c3e50;
            font-size: 20px;
            display: flex;
            align-items: center;
        }

        .content-header h2 i {
            margin-right: 10px;
            color: #3498db;
        }

        /* Formulaire de profil */
        .profile-form .form-group {
            margin-bottom: 20px;
        }

        .profile-form label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #34495e;
        }

        .profile-form input {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }

        /* Liste des annonces et favoris */
        .ads-list, .favorites-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .ad-item, .favorite-item {
            display: flex;
            gap: 20px;
            padding: 15px;
            border: 1px solid #eee;
            border-radius: 8px;
            transition: all 0.3s;
        }

        .ad-item:hover, .favorite-item:hover {
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .ad-image, .favorite-image {
            width: 120px;
            height: 100px;
            background-size: cover;
            background-position: center;
            border-radius: 5px;
        }

        .ad-details, .favorite-details {
            flex: 1;
        }

        .ad-details h3, .favorite-details h3 {
            margin-bottom: 5px;
        }

        .ad-price, .favorite-price {
            color: #e74c3c;
            font-weight: bold;
            margin: 5px 0;
        }

        .ad-date {
            color: #7f8c8d;
            font-size: 14px;
        }

        .ad-actions {
            margin-top: 10px;
            display: flex;
            gap: 10px;
        }

        .edit-btn, .delete-btn, .remove-favorite {
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .edit-btn {
            background-color: #3498db;
            color: white;
        }

        .delete-btn {
            background-color: #e74c3c;
            color: white;
        }

        .remove-favorite {
            background-color: #f39c12;
            color: white;
            margin-top: 10px;
        }

        /* Paramètres */
        .settings-form .form-group {
            margin-bottom: 20px;
        }

        .settings-form label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #34495e;
        }

        .settings-form input, .settings-form select {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }

        /* Boutons */
        .form-actions {
            margin-top: 20px;
        }

        .save-btn {
            background-color: #2ecc71;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .account-main {
                flex-direction: column;
            }
            
            .account-sidebar {
                width: 100%;
            }
            
            .ad-item, .favorite-item {
                flex-direction: column;
            }
            
            .ad-image, .favorite-image {
                width: 100%;
                height: 150px;
            }
        }

        /* نمط زر البروفيل في الهيدر */
        .avatar-mode {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
        }

        .profile-avatar {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background-color: #003366;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 14px;
        }

        /* حجم أكبر للصورة الرمزية في صفحة الحساب */
        .profile-avatar.large {
            width: 80px;
            height: 80px;
            font-size: 32px;
        }

        /* إخفاء أيقونة الشخص عند وجود صورة رمزية */
        .avatar-mode ion-icon {
            display: none;
        }

        /* تنسيق إضافي لصفحة الحساب */
        .user-profile {
            text-align: center;
            padding: 20px;
        }

        .avatar-container {
            margin-bottom: 15px;
            display: flex;
            justify-content: center;
        }

        .user-profile h2 {
            margin: 10px 0 5px;
            color: #003366;
        }

        .user-profile p {
            color: #666;
            margin-bottom: 20px;
        }

        .favorite-item {
            display: flex;
            gap: 20px;
            padding: 15px;
            border: 1px solid #eee;
            border-radius: 8px;
            margin-bottom: 15px;
            transition: all 0.3s;
        }

        .favorite-image {
            width: 150px;
            height: 100px;
            background-size: cover;
            background-position: center;
            border-radius: 5px;
        }

        .favorite-details {
            flex: 1;
        }

        .favorite-details h3 {
            margin-bottom: 5px;
            color: #333;
        }

        .favorite-price {
            color: #e74c3c;
            font-weight: bold;
            margin: 5px 0;
        }

        .favorite-location {
            color: #7f8c8d;
            font-size: 14px;
        }

        .remove-favorite {
            background-color: #e74c3c;
            color: white;
            border: none;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
            margin-top: 10px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        /* إضافة نمط للتأكد من ظهور المحتوى */
        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #34495e;
        }

        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
        }

        /* رسالة عدم وجود بيانات */
        .no-data-message {
            text-align: center;
            padding: 40px;
            color: #7f8c8d;
            font-style: italic;
        }

        .no-data-message i {
            font-size: 48px;
            margin-bottom: 15px;
            display: block;
            color: #bdc3c7;
        }
        /* جميع الأنماط السابقة تبقى كما هي */
        /* يمكنك إضافة أنماط إضافية هنا إذا لزم الأمر */
        
        .avatar-upload {
            position: relative;
            margin: 0 auto 15px;
            width: 120px;
        }
        
        .avatar-upload .avatar-preview {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            overflow: hidden;
            border: 3px solid #fff;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        
        .avatar-upload .avatar-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .avatar-upload .upload-btn {
            position: absolute;
            bottom: 0;
            right: 0;
            background: #3498db;
            color: white;
            border-radius: 50%;
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 0 5px rgba(0,0,0,0.2);
        }
        
        .avatar-upload input[type="file"] {
            display: none;
        }
        
        .error-message {
            color: #e74c3c;
            background-color: #fdecea;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            display: none;
        }
        
        .success-message {
            color: #2ecc71;
            background-color: #e8f8f0;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 15px;
            display: none;
        }
        /* أنماط قسم إعلاناتي */
.ad-item {
    display: flex;
    gap: 20px;
    padding: 15px;
    border: 1px solid #eee;
    border-radius: 8px;
    margin-bottom: 15px;
    transition: all 0.3s;
}

.ad-item:hover {
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.ad-image {
    width: 150px;
    height: 100px;
    background-size: cover;
    background-position: center;
    border-radius: 5px;
}

.ad-details {
    flex: 1;
}

.ad-details h3 {
    margin-bottom: 5px;
    color: #333;
}

.ad-meta {
    display: flex;
    gap: 15px;
    align-items: center;
    margin: 5px 0;
}

.ad-price {
    color: #e74c3c;
    font-weight: bold;
}

.ad-location {
    color: #7f8c8d;
    font-size: 14px;
}

.ad-date {
    color: #7f8c8d;
    font-size: 13px;
}

.status-badge {
    padding: 3px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: bold;
}

.status-badge.pending {
    background-color: #f39c12;
    color: white;
}

.status-badge.approved {
    background-color: #2ecc71;
    color: white;
}

.status-badge.rejected {
    background-color: #e74c3c;
    color: white;
}

.ad-actions {
    margin-top: 10px;
    display: flex;
    gap: 10px;
}

.edit-btn, .delete-btn {
    padding: 5px 10px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 5px;
}

.edit-btn {
    background-color: #3498db;
    color: white;
}

.delete-btn {
    background-color: #e74c3c;
    color: white;
}

.no-data-message {
    text-align: center;
    padding: 40px;
    color: #7f8c8d;
}

.no-data-message i {
    font-size: 48px;
    margin-bottom: 15px;
    display: block;
    color: #bdc3c7;
}

.no-data-message .btn {
    margin-top: 15px;
    display: inline-block;
    padding: 8px 20px;
    background: #3498db;
    color: white;
    border-radius: 4px;
    text-decoration: none;
}


.ad-image img, .favorite-image img {
    width: 100%;
    height: 200px;
    object-fit: cover;
    border-radius: 8px;
}

.favorite-image {
    width: 150px;
    height: 120px;
    border-radius: 8px;
    overflow: hidden;
    background-color: #f5f5f5;
}

.ad-image {
    width: 200px;
    height: 150px;
    border-radius: 8px;
    overflow: hidden;
    background-color: #f5f5f5;
}

.no-image {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100%;
    background-color: #f0f0f0;
    color: #666;
    font-size: 14px;
}
    </style>
</head>
<body>
    <div class="account-container">
        <?php if (!empty($errors)): ?>
            <div class="error-message" id="error-message">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="success-message" id="success-message">
                <p><?php echo htmlspecialchars($success); ?></p>
            </div>
        <?php endif; ?>
        
        <main class="account-main">
            <div class="account-sidebar">
                <div class="user-profile">
                    <div class="avatar-upload">
                        <div class="avatar-preview">
                            <?php if (!empty($avatar)): ?>
                                <img src="<?php echo htmlspecialchars($avatar); ?>" alt="صورة المستخدم">
                            <?php else: ?>
                                <div style="background-color: #3498db; width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; color: white; font-size: 48px; font-weight: bold;">
                                    <?php echo strtoupper(substr($name, 0, 1)); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <label class="upload-btn" for="avatar-input">
                            <i class="fas fa-camera"></i>
                        </label>
                        <form id="avatar-form" method="post" enctype="multipart/form-data">
                            <input type="file" id="avatar-input" name="avatar" accept="image/*">
                            <input type="hidden" name="upload_avatar" value="1">
                        </form>
                    </div>
                    <h2><?php echo htmlspecialchars($name); ?></h2>
                    <p><?php echo htmlspecialchars($email); ?></p>
                </div>
                <nav class="account-menu">
                    <a href="javascript:void(0)" class="menu-link active" data-section="profile">
                        <i class="fas fa-user"></i> Profil
                    </a>
                    <a href="javascript:void(0)" class="menu-link" data-section="my-ads">
                        <i class="fas fa-home"></i> Mes Annonces
                    </a>
                    <a href="javascript:void(0)" class="menu-link" data-section="favorites">
                        <i class="fas fa-heart"></i> Favoris
                    </a>
                   <?php if (isset($user['user_type']) && strtolower($user['user_type']) === 'admin'): ?>
                    <a href="javascript:void(0)" class="menu-link" data-section="admin-panel">
                        <i class="fas fa-shield-alt"></i> Admin Panel
                    </a>
                    <?php endif; ?>
                    <a href="javascript:void(0)" class="menu-link" data-section="settings">
                        <i class="fas fa-cog"></i> Paramètres
                    </a>
                    <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
                </nav>
            </div>

            <div class="account-content">
                <!-- Section Profil -->
                <div class="content-section active" id="profile-section">
                    <div class="content-header">
                        <h2><i class="fas fa-user"></i> Mon Profil</h2>
                    </div>
                    
                    <form class="profile-form" id="profile-form" method="post">
                        <div class="form-group">
                            <label for="name">Nom complet</label>
                            <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="phone">Téléphone</label>
                            <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($phone); ?>">
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" name="update_profile" class="save-btn">Enregistrer les modifications</button>
                        </div>
                    </form>
                </div>

                <!-- Section Mes Annonces -->
<!-- Section Mes Annonces -->
<div class="content-section" id="my-ads-section">
    <div class="content-header">
        <h2><i class="fas fa-home"></i> Mes annonces</h2>
    </div>
    
    <div class="ads-list" id="ads-list">
        <?php
        // استعلام لجلب إعلانات المستخدم
        $ads_query = "SELECT * FROM annonces WHERE user_id = ? ORDER BY date_publication DESC";
        $stmt_ads = $conn->prepare($ads_query);
        $stmt_ads->bind_param("i", $user_id);
        $stmt_ads->execute();
        $user_ads = $stmt_ads->get_result();
        
        if ($user_ads->num_rows > 0) {
            while ($ad = $user_ads->fetch_assoc()) {
                $status_badge = '';
                switch($ad['status']) {
                    case 'pending':
                        $status_badge = '<span class="status-badge pending">En attente de révision</span>';
                        
                        break;
                    case 'approved':
                        $status_badge = '<span class="status-badge approved">approved</span>';
                        break;
                    case 'rejected':
                        $status_badge = '<span class="status-badge rejected">rejected</span>';
                        break;
                }
                ?>
                <div class="ad-item" data-id="<?php echo $ad['id']; ?>">
                   <div class="ad-image">
    <?php
    $images = json_decode($ad['images'] ?? '[]', true);
    if (!empty($images) && is_array($images)) {
        // معالجة الصورة الأولى
        $first_image = processImagePath($images[0]);
        echo '<img src="'.htmlspecialchars($first_image).'" alt="صورة الإعلان">';
    } elseif (!empty($ad['image_url'])) {
        // معالجة image_url
        $processed_image = processImagePath($ad['image_url']);
        echo '<img src="'.htmlspecialchars($processed_image).'" alt="صورة الإعلان">';
    } else {
        echo '<div class="no-image">Aucune image</div>';
    }
    ?>
</div>
                    <div class="ad-details">
                        <h3><?php echo htmlspecialchars($ad['titre'] ?? ''); ?></h3>
                        <div class="ad-meta">
                            <span class="ad-price"><?php echo htmlspecialchars($ad['prix'] ?? ''); ?> DA</span>
                            <span class="ad-location"><?php echo htmlspecialchars($ad['wilaya'] ?? ''); ?></span>
                        </div>
                        <div class="ad-date"><?php echo htmlspecialchars($ad['date_publication'] ?? ''); ?></div>
                        <?php echo $status_badge; ?>
                       
                          <div class="ad-actions">
                         <button class="edit-btn" onclick="modifierAnnonce(<?php echo $ad['id']; ?>)">
    Modifier
</button>

<button class="delete-btn" onclick="supprimerAnnonce(<?php echo $ad['id']; ?>)">
    Supprimer
</button>

                        </div>
                    </div>
                </div>
                <?php
            }
        } else {
            echo '
            <div class="no-data-message">
                <i class="fas fa-home"></i>
                <p>Aucune publicité. Vous n avez encore publié aucune annonce.</p>
                <a href="publish.php" class="btn">Publier une nouvelle annonce</a>
            </div>';
        }
        ?>
    </div>
</div>

                <!-- Section Favoris -->
                <div class="content-section" id="favorites-section">
                    <div class="content-header">
                        <h2><i class="fas fa-heart"></i> Mes Favoris</h2>
                    </div>
                    
                    <div class="favorites-list" id="favorites-list">
                     <?php
if (isset($user_id)) {
    $sql = "SELECT a.* FROM annonces a 
            JOIN favorites f ON a.id = f.property_id 
            WHERE f.user_id = ? 
            ORDER BY f.date_added DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        while ($property = $result->fetch_assoc()) {
            // معالجة الصورة
            $property_image = '';
            if (!empty($property['images'])) {
                $images = json_decode($property['images'], true);
                if (!empty($images) && is_array($images)) {
                    $property_image = processImagePath($images[0]);
                }
            } elseif (!empty($property['image_url'])) {
                $property_image = processImagePath($property['image_url']);
            } else {
                $property_image = 'assets/images/default-property.jpg';
            }
            
            echo '<div class="favorite-item" data-id="'.$property['id'].'">
                  <a href="home.php?id='.$property['id'].'" class="favorite-link">
                    <div class="favorite-image">
                        <img src="'.htmlspecialchars($property_image).'" alt="صورة العقار" style="width: 100%; height: 100%; object-fit: cover;">
                    </div>
                    <div class="favorite-details">
                        <h3>'.htmlspecialchars($property['titre'] ?? '').'</h3>
                        <div class="favorite-price">'.htmlspecialchars($property['prix'] ?? '').' DA</div>
                        <div class="favorite-location">'.htmlspecialchars($property['wilaya'] ?? '').', '.htmlspecialchars($property['commune'] ?? '').'</div>
                        <button class="remove-favorite" onclick="supprimerFavori('.$property['id'].')">
                            <i class="fas fa-trash"></i> Retirer
                        </button>
                    </div>
                  </a>
                  </div>';
        }
    } else {
        echo '<div class="no-data-message">
                <i class="fas fa-heart"></i>
                <p>Aucune propriété dans vos favoris pour le moment.</p>
              </div>';
    }
}
?>
                    </div>
                </div>
                <!-- لوحة التحكم للإدمن -->
<?php if (isset($user['user_type']) && strtolower($user['user_type']) === 'admin'): ?>
<div class="content-section" id="admin-panel-section">
    <div class="content-header">
        <h2><i class="fas fa-shield-alt"></i> Admin Panel</h2>
    </div>
    
 
    <div class="quick-actions">
   <a href="admin_ads.php" class="action-btn">
    <i class="fas fa-home"></i> Gestion des annonces
</a>
<a href="admin_users.php" class="action-btn">
    <i class="fas fa-users"></i> Gestion des utilisateurs
</a>
</div>
</div>
<?php endif; ?>

                <!-- Section Paramètres -->
                <div class="content-section" id="settings-section">
                    <div class="content-header">
                        <h2><i class="fas fa-cog"></i> Paramètres</h2>
                    </div>
                    
                    <form class="settings-form" id="settings-form" method="post">
                        <div class="form-group">
                            <label for="current_password">Mot de passe actuel</label>
                            <input type="password" id="current_password" name="current_password" placeholder="Entrez votre mot de passe actuel" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="new_password">Nouveau mot de passe</label>
                            <input type="password" id="new_password" name="new_password" placeholder="Entrez votre nouveau mot de passe" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">Confirmer le mot de passe</label>
                            <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirmez votre nouveau mot de passe" required>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" name="change_password" class="save-btn">Enregistrer les paramètres</button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
     
    <script>
// Script amélioré pour la navigation dynamique
document.addEventListener('DOMContentLoaded', function() {
    console.log('Page chargée - Initialisation de la navigation');
    
    // Afficher les messages d'erreur/succès
    afficherMessages();
    
    // Initialiser la navigation
    initialiserNavigation();
    
    // Initialiser le téléchargement d'avatar
    initialiserUploadAvatar();
    
    // Charger le contenu par défaut
    chargerContenuParDefaut();
});

// Fonction pour afficher les messages
function afficherMessages() {
    const messageErreur = document.getElementById('error-message');
    const messageSucces = document.getElementById('success-message');
    
    if (messageErreur && messageErreur.innerHTML.trim()) {
        messageErreur.style.display = 'block';
        setTimeout(() => {
            messageErreur.style.display = 'none';
        }, 5000);
    }
    
    if (messageSucces && messageSucces.innerHTML.trim()) {
        messageSucces.style.display = 'block';
        setTimeout(() => {
            messageSucces.style.display = 'none';
        }, 5000);
    }
}

// Initialisation de la navigation
function initialiserNavigation() {
    console.log('Initialisation de la navigation');
    
    // Récupérer tous les liens du menu
    const liensMenu = document.querySelectorAll('.menu-link');
    console.log(`Nombre de liens trouvés : ${liensMenu.length}`);
    
    // Ajouter un écouteur d'événements à chaque lien
    liensMenu.forEach((lien, index) => {
        const section = lien.getAttribute('data-section');
        console.log(`Configuration du lien ${index + 1}: ${section}`);
        
        lien.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            const sectionCible = this.getAttribute('data-section');
            console.log(`Clic sur : ${sectionCible}`);
            
            if (sectionCible) {
                changerSection(sectionCible);
            }
        });
    });
}

// Changer de section
function changerSection(nomSection) {
    console.log(`Changement vers la section : ${nomSection}`);
    
    // 1. Mettre à jour l'état des liens du menu
    mettreAJourLiensMenu(nomSection);
    
    // 2. Masquer toutes les sections
    masquerToutesSections();
    
    // 3. Afficher la section cible
    afficherSectionCible(nomSection);
    
    // 4. Charger le contenu de la section si nécessaire
    chargerContenuSection(nomSection);
}

// Mettre à jour les liens du menu
function mettreAJourLiensMenu(sectionActive) {
    const liensMenu = document.querySelectorAll('.menu-link');
    
    liensMenu.forEach(lien => {
        lien.classList.remove('active');
        
        if (lien.getAttribute('data-section') === sectionActive) {
            lien.classList.add('active');
        }
    });
}

// Masquer toutes les sections
function masquerToutesSections() {
    const sections = document.querySelectorAll('.content-section');
    
    sections.forEach(section => {
        section.classList.remove('active');
    });
}

// Afficher la section cible
function afficherSectionCible(nomSection) {
    let sectionCible = null;
    
    // Méthode 1: Recherche par ID avec suffixe -section
    const idSection = nomSection + '-section';
    sectionCible = document.getElementById(idSection);
    
    if (sectionCible) {
        console.log(`Section trouvée (méthode 1) : ${idSection}`);
    } else {
        // Méthode 2: Recherche directe par nom
        sectionCible = document.getElementById(nomSection);
        
        if (sectionCible) {
            console.log(`Section trouvée (méthode 2) : ${nomSection}`);
        } else {
            console.error(`Section non trouvée : ${nomSection}`);
            console.log('Sections disponibles :', 
                Array.from(document.querySelectorAll('.content-section')).map(s => s.id)
            );
            return;
        }
    }
    
    // Afficher la section
    sectionCible.classList.add('active');
    console.log(`Section affichée : ${sectionCible.id}`);
}

// Charger le contenu des sections
function chargerContenuSection(nomSection) {
    switch(nomSection) {
        case 'mes-annonces':
            chargerMesAnnonces();
            break;
        case 'favoris':
            chargerFavoris();
            break;
        case 'panel-admin':
            chargerPanelAdmin();
            break;
        default:
            console.log(`Aucun chargement supplémentaire nécessaire pour : ${nomSection}`);
    }
}

// Charger le contenu par défaut
function chargerContenuParDefaut() {
    // Trouver le lien actif
    let lienActif = document.querySelector('.menu-link.active');
    
    if (lienActif) {
        const sectionActive = lienActif.getAttribute('data-section');
        console.log(`Lien actif trouvé : ${sectionActive}`);
        changerSection(sectionActive);
    } else {
        // Si aucun lien actif, activer le premier
        const premierLien = document.querySelector('.menu-link');
        if (premierLien) {
            const premiereSection = premierLien.getAttribute('data-section');
            console.log(`Activation de la première section : ${premiereSection}`);
            changerSection(premiereSection);
        }
    }
}

// Initialiser l'upload d'avatar
function initialiserUploadAvatar() {
    const inputAvatar = document.getElementById('avatar-input');
    
    if (inputAvatar) {
        inputAvatar.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                const fichier = this.files[0];
                const lecteur = new FileReader();
                
                lecteur.onload = function(e) {
                    const apercu = document.querySelector('.avatar-preview');
                    if (apercu) {
                        apercu.innerHTML = `<img src="${e.target.result}" alt="Aperçu avatar">`;
                    }
                };
                
                lecteur.readAsDataURL(fichier);
                
                // Soumettre le formulaire automatiquement
                const formulaire = document.getElementById('avatar-form');
                if (formulaire) {
                    formulaire.submit();
                }
            }
        });
    }
}

// Charger les annonces de l'utilisateur
function chargerMesAnnonces() {
    const listeAnnonces = document.getElementById('ads-list');
    
    if (!listeAnnonces) {
        console.log('La liste des annonces est absente');
        return;
    }
    
    // Vérifier si le contenu est déjà chargé
    if (listeAnnonces.innerHTML.includes('ad-item')) {
        console.log('Annonces déjà chargées');
        return;
    }
    
    console.log('Chargement des annonces utilisateur...');
    listeAnnonces.innerHTML = '<div class="loading-message"><i class="fas fa-spinner fa-spin"></i> Chargement en cours...</div>';
    
    // Simulation de chargement (remplacer par un vrai fetch)
    setTimeout(() => {
        listeAnnonces.innerHTML = `
            <div class="no-data-message">
                <i class="fas fa-home"></i>
                <p>Aucune annonce disponible. Vous n'avez pas encore publié d'annonces.</p>
            </div>
        `;
    }, 1000);
}

// Charger les favoris
function chargerFavoris() {
    const listeFavoris = document.getElementById('favorites-list');
    
    if (!listeFavoris) {
        console.log('Élément des favoris absent');
        return;
    }
    
    // Vérifier si le contenu est déjà chargé
    if (listeFavoris.innerHTML.includes('favorite-item') && 
        !listeFavoris.innerHTML.includes('loading-message')) {
        console.log('Favoris déjà chargés');
        return;
    }
    
    console.log('Mise à jour des favoris...');
}

// Charger le panel admin
function chargerPanelAdmin() {
    const sectionAdmin = document.getElementById('admin-panel-section');
    
    if (!sectionAdmin) {
        console.log('Section admin absente');
        return;
    }
    
    console.log('Chargement des données admin...');
}

// Supprimer des favoris
function supprimerFavori(idPropriete) {
    if (!confirm('Voulez-vous vraiment retirer cette propriété des favoris ?')) {
        return;
    }
    
    console.log(`Retrait de la propriété ${idPropriete} des favoris`);
    
    // Envoyer la requête de suppression
    fetch('save_property.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `property_id=${idPropriete}&action=remove`
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            // Supprimer l'élément de l'interface
            const elementFavori = document.querySelector(`.favorite-item[data-id="${idPropriete}"]`);
            if (elementFavori) {
                elementFavori.remove();
            }
            
            // Vérifier s'il reste des éléments
            const listeFavoris = document.getElementById('favorites-list');
            const elementsRestants = listeFavoris.querySelectorAll('.favorite-item');
            
            if (elementsRestants.length === 0) {
                listeFavoris.innerHTML = `
                    <div class="no-data-message">
                        <i class="fas fa-heart"></i>
                        <p>Aucune propriété dans vos favoris.</p>
                    </div>
                `;
            }
            
            console.log('Propriété supprimée avec succès');
        } else {
            alert('Erreur lors de la suppression de la propriété des favoris');
        }
    })
    .catch(error => {
        console.error('Erreur de suppression:', error);
        alert('Erreur de connexion. Veuillez réessayer.');
    });
}

// Modifier une annonce
function modifierAnnonce(idAnnonce) {
    // Redirection vers la page de publication avec l'ID
    window.location.href = 'bub.php?edit=' + idAnnonce;
}

// Supprimer une annonce
function supprimerAnnonce(idAnnonce) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette annonce ? Cette action est irréversible.')) {
        fetch('delete_ad.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'id=' + idAnnonce
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Supprimer l'annonce de la liste
                const elementAnnonce = document.querySelector(`.ad-item[data-id="${idAnnonce}"]`);
                if (elementAnnonce) {
                    elementAnnonce.remove();
                }
                
                // Vérifier s'il reste des annonces
                const listeAnnonces = document.getElementById('ads-list');
                if (listeAnnonces.querySelectorAll('.ad-item').length === 0) {
                    listeAnnonces.innerHTML = `
                        <div class="no-data-message">
                            <i class="fas fa-home"></i>
                            <p>Aucune annonce disponible. Vous n'avez pas encore publié d'annonces.</p>
                            <a href="publier.php" class="btn">Publier une nouvelle annonce</a>
                        </div>
                    `;
                }
            } else {
                alert('Erreur lors de la suppression : ' + (data.message || ''));
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert('Erreur de connexion au serveur.');
        });
    }
}

// Exporter les fonctions pour utilisation globale
window.supprimerFavori = supprimerFavori;
window.modifierAnnonce = modifierAnnonce;
window.supprimerAnnonce = supprimerAnnonce;

// Réinitialiser la navigation (pour appel externe)
window.reinitialiserNavigation = function() {
    console.log('Réinitialisation de la navigation');
    initialiserNavigation();
    chargerContenuParDefaut();
};

// Logs de diagnostic
console.log('Script JavaScript chargé avec succès');
console.log('Sections disponibles :', 
    Array.from(document.querySelectorAll('.content-section')).map(s => s.id)
);
console.log('Liens de menu disponibles :', 
    Array.from(document.querySelectorAll('.menu-link')).map(l => l.getAttribute('data-section'))
);

// Navigation vers une annonce spécifique au chargement
document.addEventListener('DOMContentLoaded', function() {
    const parametresURL = new URLSearchParams(window.location.search);
    const idAnnonce = parametresURL.get('ad_id');
    
    if (idAnnonce) {
        // Aller à la section "Mes annonces"
        changerSection('mes-annonces');
        
        // Mettre en évidence l'annonce après chargement
        setTimeout(() => {
            const annonceCible = document.querySelector(`.ad-item[data-id="${idAnnonce}"]`);
            if (annonceCible) {
                // Mise en évidence
                annonceCible.style.border = '3px solid #4CAF50';
                annonceCible.style.boxShadow = '0 0 15px rgba(76, 175, 80, 0.5)';
                
                // Défilement vers l'élément
                annonceCible.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
            }
        }, 500);
    }
});
</script>
</body>
</html>