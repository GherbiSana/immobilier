<?php
session_start();
// تعيين رأس الاستجابة إلى JSON
header('Content-Type: application/json');

// تمكين سجل الأخطاء للتصحيح
ini_set('display_errors', 0); // إخفاء الأخطاء عن المستخدم
error_reporting(E_ALL);

// دالة للتسجيل في ملف للتصحيح
function logDebug($message) {
    $logFile = __DIR__ . '/login_debug.log';
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($logFile, "[$timestamp] $message" . PHP_EOL, FILE_APPEND);
}

logDebug("------ بدء محاولة تسجيل الدخول ------");

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
$email = '';
$password = '';
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
            $email = isset($data['email']) ? trim($data['email']) : '';
            $password = isset($data['password']) ? $data['password'] : '';
            logDebug("تم استخراج البيانات من JSON بنجاح");
        } else {
            logDebug("خطأ في تحليل JSON: " . json_last_error_msg());
        }
    }
} else {
    // قراءة البيانات من طلب POST
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    logDebug("تم استخراج البيانات من POST form: email=" . (empty($email) ? "فارغ" : "متوفر"));
}

// تنظيف البريد الإلكتروني
$email = filter_var($email, FILTER_SANITIZE_EMAIL);
logDebug("البريد الإلكتروني بعد التنظيف: $email");

// 3. التحقق من البيانات
// احذف هذا الجزء (التحقق من صحة الإيميل):
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    logDebug("البريد الإلكتروني غير صالح: $email");
    echo json_encode([
        'success' => false, 
        'message' => 'Email invalide',
        'errors' => ['email' => 'Format email invalide']
    ]);
    exit;
}
if (empty($password)) {
    logDebug("كلمة المرور فارغة");
    echo json_encode([
        'success' => false, 
        'message' => 'Mot de passe requis',
        'errors' => ['password' => 'Mot de passe requis']
    ]);
    exit;
}

// 4. البحث عن المستخدم
try {
    // البحث في الجدول بالأعمدة المناسبة
    $stmt = $conn->prepare("SELECT user_id, name, email, Numéro_de_téléphone, password, user_type, avatar FROM users WHERE email = ? LIMIT 1");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    logDebug("نتيجة البحث عن المستخدم: " . ($user ? "تم العثور على المستخدم" : "لم يتم العثور على المستخدم"));
    
    if (!$user) {
        echo json_encode([
            'success' => false, 
            'message' => 'Email ou mot de passe incorrect',
            'errors' => ['email' => 'Aucun compte trouvé avec cet email']
        ]);
        exit;
    }

    // 5. التحقق من كلمة المرور
    $authenticated = false;
    
    // لا نعرض كلمة المرور كاملة في السجل
    $passwordPreview = substr($user['password'], 0, 10) . '...';
    logDebug("نوع كلمة المرور في قاعدة البيانات: " . $passwordPreview);
    
    // الطريقة 1: استخدام password_verify للتحقق من كلمات المرور المشفرة بـ bcrypt
    if (substr($user['password'], 0, 4) === '$2y$' && password_verify($password, $user['password'])) {
        $authenticated = true;
        logDebug("تم التحقق من كلمة المرور باستخدام password_verify");
    }
    // الطريقة 2: المقارنة المباشرة (للكلمات غير المشفرة)
    elseif ($password === $user['password']) {
        $authenticated = true;
        logDebug("تم التحقق من كلمة المرور بالمقارنة المباشرة");
        
        // تشفير كلمة المرور لتحسين الأمان
        $newHash = password_hash($password, PASSWORD_DEFAULT);
        $updateStmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
        $updateStmt->execute([$newHash, $user['user_id']]);
        logDebug("تم تحديث كلمة المرور وتشفيرها");
    }
    // الطريقة 3: التحقق من تشفير MD5 (للتوافق مع الأنظمة القديمة)
    elseif (md5($password) === $user['password']) {
        $authenticated = true;
        logDebug("تم التحقق من كلمة المرور باستخدام MD5");
        
        // تحديث تشفير كلمة المرور
        $newHash = password_hash($password, PASSWORD_DEFAULT);
        $updateStmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
        $updateStmt->execute([$newHash, $user['user_id']]);
        logDebug("تم تحديث كلمة المرور وتشفيرها بطريقة آمنة");
    }

    logDebug("نتيجة التحقق من كلمة المرور: " . ($authenticated ? "ناجح" : "فاشل"));

    if (!$authenticated) {
        echo json_encode([
            'success' => false, 
            'message' => 'Email ou mot de passe incorrect',
            'errors' => ['password' => 'Mot de passe incorrect']
        ]);
        exit;
    }

    // 6. إنشاء جلسة للمستخدم
    $_SESSION['user'] = [
        'id' => $user['user_id'],
        'name' => $user['name'],
        'email' => $user['email'],
        'Numéro_de_téléphone' => $user['Numéro_de_téléphone'],
        'avatar' => $user['avatar'], // هذا هو مسار الصورة
        'user_type' => $user['user_type']
    ];
  
    logDebug("تم إنشاء جلسة للمستخدم بنجاح: " . $user['name']);

    // 7. إرسال الاستجابة الناجحة
    echo json_encode([
        'success' => true,
        'message' => 'Connexion réussie',
        'user' => [
            'id' => $user['user_id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'Numéro_de_téléphone' => $user['Numéro_de_téléphone'],
            'avatar' => $user['avatar'], // هذا هو مسار الصورة
            'user_type' => $user['user_type']
        ]
    ]);
    
    logDebug("تم إكمال عملية تسجيل الدخول بنجاح");

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

logDebug("------ نهاية محاولة تسجيل الدخول ------");
?> <?php
session_start();

// التحقق من تسجيل الدخول
if (!isset($_SESSION['user'])) {
  // إذا ماكانش المستخدم داخل، نوجهه لصفحة تسجيل الدخول أو الرئيسية
  header('Location: untitled.php'); // بدلها إذا تحب توجه لمكان آخر
  exit;
}



?>