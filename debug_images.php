<?php
// ملف منفصل لفحص مشكلة الصور: debug_images.php

require 'db_connect.php';

echo "<h2>فحص مشكلة الصور</h2>";

// 1. فحص بيانات قاعدة البيانات
$sql = "SELECT id, titre, image_url, images FROM annonces LIMIT 10";
$result = $conn->query($sql);

echo "<h3>1. بيانات الصور في قاعدة البيانات:</h3>";
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>ID</th><th>العنوان</th><th>image_url</th><th>images</th><th>الملف موجود؟</th></tr>";

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $image_url = $row['image_url'];
        $file_exists = 'لا';
        
        if (!empty($image_url) && $image_url != 'NULL') {
            // إزالة ./ من بداية المسار
            $clean_path = ltrim($image_url, './');
            
            if (file_exists($clean_path)) {
                $file_exists = '✅ نعم';
            } else {
                $file_exists = '❌ لا - المسار: ' . $clean_path;
            }
        }
        
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['titre']) . "</td>";
        echo "<td>" . htmlspecialchars($image_url) . "</td>";
        echo "<td>" . (strlen($row['images']) > 50 ? substr($row['images'], 0, 50) . '...' : $row['images']) . "</td>";
        echo "<td>" . $file_exists . "</td>";
        echo "</tr>";
    }
} else {
    echo "<tr><td colspan='5'>لا توجد بيانات</td></tr>";
}
echo "</table>";

// 2. فحص مجلد الصور
echo "<h3>2. فحص مجلد assets/images:</h3>";
$image_dir = 'assets/images/';

if (is_dir($image_dir)) {
    echo "✅ المجلد موجود<br>";
    $files = scandir($image_dir);
    echo "الملفات الموجودة:<br>";
    foreach ($files as $file) {
        if ($file != '.' && $file != '..') {
            echo "- " . $file . "<br>";
        }
    }
} else {
    echo "❌ المجلد غير موجود<br>";
    echo "محاولة إنشاء المجلد...<br>";
    if (mkdir($image_dir, 0755, true)) {
        echo "✅ تم إنشاء المجلد بنجاح<br>";
    } else {
        echo "❌ فشل في إنشاء المجلد<br>";
    }
}

// 3. إنشاء صورة افتراضية إذا لم تكن موجودة
$default_image = 'assets/images/default-property.jpg';
if (!file_exists($default_image)) {
    echo "<h3>3. إنشاء صورة افتراضية:</h3>";
    
    // إنشاء صورة بسيطة باستخدام GD
    if (extension_loaded('gd')) {
        $width = 400;
        $height = 300;
        $image = imagecreate($width, $height);
        
        // ألوان
        $bg_color = imagecolorallocate($image, 240, 240, 240);
        $text_color = imagecolorallocate($image, 100, 100, 100);
        
        // نص
        $text = 'Image non disponible';
        $font_size = 5;
        $text_width = imagefontwidth($font_size) * strlen($text);
        $text_height = imagefontheight($font_size);
        $x = ($width - $text_width) / 2;
        $y = ($height - $text_height) / 2;
        
        imagestring($image, $font_size, $x, $y, $text, $text_color);
        
        if (imagejpeg($image, $default_image)) {
            echo "✅ تم إنشاء الصورة الافتراضية<br>";
        } else {
            echo "❌ فشل في إنشاء الصورة الافتراضية<br>";
        }
        
        imagedestroy($image);
    } else {
        echo "❌ مكتبة GD غير مثبتة<br>";
    }
}

// 4. اقتراحات الحلول
echo "<h3>4. الحلول المقترحة:</h3>";
echo "<ol>";
echo "<li>تأكد من أن مجلد assets/images موجود وله صلاحيات الكتابة</li>";
echo "<li>تحقق من مسارات الصور في قاعدة البيانات</li>";
echo "<li>ارفع صورة افتراضية باسم default-property.jpg</li>";
echo "<li>استخدم الكود المحدث في الملف الرئيسي</li>";
echo "</ol>";

// 5. تحديث قاعدة البيانات (اختياري)
echo "<h3>5. تحديث قاعدة البيانات:</h3>";
echo "<form method='post'>";
echo "<button type='submit' name='update_empty_images'>تحديث الصور الفارغة بالصورة الافتراضية</button>";
echo "</form>";

if (isset($_POST['update_empty_images'])) {
    $update_sql = "UPDATE annonces SET image_url = 'assets/images/default-property.jpg' 
                   WHERE image_url IS NULL OR image_url = '' OR image_url = 'NULL'";
    
    if ($conn->query($update_sql)) {
        echo "✅ تم تحديث " . $conn->affected_rows . " سجل<br>";
    } else {
        echo "❌ خطأ في التحديث: " . $conn->error . "<br>";
    }
}

$conn->close();
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
table { margin: 10px 0; }
th, td { padding: 8px; text-align: left; }
th { background-color: #f2f2f2; }
h2, h3 { color: #333; }
ol, ul { margin: 10px 0; }
button { padding: 10px 15px; background: #007cba; color: white; border: none; cursor: pointer; }
button:hover { background: #005a87; }
</style>