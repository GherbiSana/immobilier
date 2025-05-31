 <?php
session_start();
// تعيين رأس الاستجابة إلى JSON
header('Content-Type: application/json');

// تمكين سجل الأخطاء للتصحيح
ini_set('display_errors', 0); // إخفاء الأخطاء عن المستخدم
error_reporting(E_ALL);

// دالة للتسجيل في ملف للتصحيح
function logDebug($message) {
    $logFile = __DIR__ . '/register_debug.log';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message" . PHP_EOL, FILE_APPEND);
}

logDebug("------ بدء عملية التسجيل ------");

// 1. الاتصال بقاعدة البيانات
try {
    $conn = new PDO('mysql:host=localhost;dbname=immobilier;charset=utf8mb4', 'root', '');
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    logDebug("تم الاتصال بقاعدة البيانات بنجاح");
} catch (PDOException $e) {
    logDebug("خطأ في الاتصال بقاعدة البيانات: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Erreur de connexion à la base de données',
        'debug' => $e->getMessage()
    ]);
    exit;
}

// 2. الحصول على البيانات (دعم طريقتي الإرسال: POST أو JSON)
$fname = '';
$lname = '';
$email = '';
$password = '';
$phone = '';
$avatar_path = null;
$rawInput = '';

// التحقق من نوع الطلب
$contentType = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';
logDebug("نوع المحتوى: " . $contentType);

if (strpos($contentType, 'application/json') !== false) {
    // قراءة البيانات من طلب JSON
    $rawInput = file_get_contents('php://input');
    logDebug("بيانات JSON الواردة: " . $rawInput);

    if (!empty($rawInput)) {
        $data = json_decode($rawInput, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $fname = isset($data['fname']) ? trim($data['fname']) : '';
            $lname = isset($data['lname']) ? trim($data['lname']) : '';
            $email = isset($data['email']) ? trim($data['email']) : '';
            $password = isset($data['password']) ? $data['password'] : '';
            $phone = isset($data['phone_number']) ? trim($data['phone_number']) : '';
            logDebug("تم استخراج البيانات من JSON بنجاح");
        } else {
            logDebug("خطأ في تحليل JSON: " . json_last_error_msg());
        }
    }
} else {
    // قراءة البيانات من طلب POST
    $fname = isset($_POST['fname']) ? trim($_POST['fname']) : '';
    $lname = isset($_POST['lname']) ? trim($_POST['lname']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    logDebug("تم استخراج البيانات من POST form");

    // معالجة الصورة الرمزية إذا تم تحميلها
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/avatars/';
        
        // إنشاء المجلد إذا لم يكن موجودًا
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        // الحصول على معلومات الملف
        $file_info = pathinfo($_FILES['avatar']['name']);
        $file_extension = strtolower($file_info['extension']);
        
        // التحقق من نوع الملف
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($file_extension, $allowed_extensions)) {
            // إنشاء اسم فريد للملف
            $new_file_name = uniqid('avatar_') . '.' . $file_extension;
            $upload_path = $upload_dir . $new_file_name;
            
            // نقل الملف
            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $upload_path)) {
                $avatar_path = $upload_path;
                logDebug("تم تحميل الصورة الرمزية بنجاح: $avatar_path");
            } else {
                logDebug("فشل في تحميل الصورة الرمزية");
            }
        } else {
            logDebug("نوع ملف غير مسموح به: $file_extension");
        }
    }
}

// تنظيف البيانات
$fname = filter_var($fname, FILTER_SANITIZE_STRING);
$lname = filter_var($lname, FILTER_SANITIZE_STRING);
$email = filter_var($email, FILTER_SANITIZE_EMAIL);
$phone = filter_var($phone, FILTER_SANITIZE_STRING);

// 3. التحقق من البيانات
$errors = [];

if (empty($fname)) {
    $errors['fname'] = 'Prénom requis';
}

if (empty($lname)) {
    $errors['lname'] = 'Nom requis';
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'Format email invalide';
}

if (empty($password) || strlen($password) < 8) {
    $errors['password'] = 'Mot de passe requis (8 caractères minimum)';
}

// التحقق من تنسيق رقم الهاتف إذا تم إدخاله
if (!empty($phone) && !preg_match('/^\d{10}$/', $phone)) {
    $errors['phone'] = 'Numéro de téléphone doit contenir 10 chiffres';
}

logDebug("نتائج التحقق من البيانات: " . (empty($errors) ? "بيانات صالحة" : "توجد أخطاء"));

// إذا كانت هناك أخطاء، أرسل رسالة خطأ
if (!empty($errors)) {
    echo json_encode([
        'success' => false,
        'message' => 'Veuillez corriger les erreurs',
        'errors' => $errors
    ]);
    exit;
}

// 4. التحقق من وجود البريد الإلكتروني مسبقًا
try {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $emailExists = (int)$stmt->fetchColumn() > 0;
    
    if ($emailExists) {
        logDebug("البريد الإلكتروني موجود بالفعل: $email");
        echo json_encode([
            'success' => false,
            'message' => 'Email déjà utilisé',
            'errors' => ['email' => 'Cette adresse email est déjà utilisée']
        ]);
        exit;
    }
    
    // 5. إنشاء حساب المستخدم
    // تشفير كلمة المرور
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    // دمج الاسم الأول والاسم الأخير
    $full_name = trim($fname . ' ' . $lname);
    
    // إدراج المستخدم في قاعدة البيانات
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, Numéro_de_téléphone, avatar, user_type) 
                           VALUES (?, ?, ?, ?, ?, 'User')");
    $stmt->execute([$full_name, $email, $hashed_password, $phone, $avatar_path]);
    $user_id = $conn->lastInsertId();
    
    logDebug("تم إنشاء حساب المستخدم بنجاح. معرف المستخدم: $user_id");
    
    // 6. إنشاء جلسة للمستخدم
    $_SESSION['user'] = [
        'id' => $user_id,
        'name' => $full_name,
        'email' => $email,
        'Numéro_de_téléphone' => $phone,
        'avatar' => $avatar_path,
        'user_type' => 'User'
    ];
    
    logDebug("تم إنشاء جلسة للمستخدم بنجاح");
    
    // 7. إرسال الاستجابة الناجحة
    echo json_encode([
        'success' => true,
        'message' => 'Inscription réussie',
        'user' => [
            'id' => $user_id,
            'name' => $full_name,
            'email' => $email,
            'Numéro_de_téléphone' => $phone,
            'avatar' => $avatar_path,
            'user_type' => 'User'
        ]
    ]);
    
    logDebug("تم إكمال عملية التسجيل بنجاح");
    
} catch (PDOException $e) {
    logDebug("خطأ في قاعدة البيانات: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Une erreur est survenue. Veuillez réessayer.',
        'debug' => $e->getMessage()
    ]);
} catch (Exception $e) {
    logDebug("خطأ عام: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Une erreur est survenue. Veuillez réessayer.',
        'debug' => $e->getMessage()
    ]);
}

logDebug("------ نهاية عملية التسجيل ------");
?>