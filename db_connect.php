<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "immobilier";

// إنشاء الاتصال
$conn = new mysqli($servername, $username, $password, $dbname);

// التحقق من الاتصال
if ($conn->connect_error) {
    die("فشل الاتصال بقاعدة البيانات: " . $conn->connect_error);
}

// الاتصال ناجح، لا حاجة لطباعة أي شيء
?>
