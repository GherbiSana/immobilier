<?php
ob_start();
include 'header.php';
require_once 'db_connect.php';

// معالجة الطلبات قبل أي إخراج
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ad_id = (int)($_POST['ad_id'] ?? 0);
    
    if ($ad_id > 0) {
        if (isset($_POST['approve_ad'])) {
            // 1. تحديث حالة الإعلان إلى "approved"
            $stmt = $conn->prepare("UPDATE annonces SET status = 'approved' WHERE id = ?");
            $stmt->bind_param("i", $ad_id);
            
            if ($stmt->execute()) {
                // 2. الحصول على معلومات الإعلان
                $stmt_info = $conn->prepare("SELECT * FROM annonces WHERE id = ?");
                $stmt_info->bind_param("i", $ad_id);
                $stmt_info->execute();
                $ad_data = $stmt_info->get_result()->fetch_assoc();
                
                // 3. إنشاء مجلد الإعلانات المعتمدة إذا لم يكن موجوداً
                $upload_dir = 'uploads/approved_ads/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                // 4. معالجة الصور المتعددة
                $processed_images = [];
                if (!empty($ad_data['images'])) {
                    $images = json_decode($ad_data['images'], true);
                    
                    // تأكد أن $images هي مصفوفة
                    if (is_array($images)) {
                        foreach ($images as $image) {
                            // تنظيف اسم الملف لأغراض الأمان
                            $clean_image = basename($image);
                            
                            // البحث عن الملف في المجلدات المختلفة
                            $possible_sources = [
                                'uploads/properties/' . $clean_image,
                                'uploads/' . $clean_image,
                                'uploads/avatars/' . $clean_image
                            ];
                            
                            $source_found = false;
                            foreach ($possible_sources as $source_path) {
                                if (file_exists($source_path)) {
                                    $new_path = $upload_dir . $clean_image;
                                    if (copy($source_path, $new_path)) {
                                        $processed_images[] = $clean_image;
                                        $source_found = true;
                                        break;
                                    }
                                }
                            }
                            
                            // إذا لم نجد الملف، احتفظ بالاسم فقط
                            if (!$source_found) {
                                $processed_images[] = $clean_image;
                            }
                        }
                    }
                    
                    // 5. تحديث مسارات الصور في قاعدة البيانات
                    if (!empty($processed_images)) {
                        $stmt_update = $conn->prepare("UPDATE annonces SET images = ? WHERE id = ?");
                        $new_images_json = json_encode($processed_images);
                        $stmt_update->bind_param("si", $new_images_json, $ad_id);
                        $stmt_update->execute();
                    }
                }
                
                $_SESSION['success_message'] = "L'annonce #$ad_id a été approuvée avec succès";
                
                // 7. الحصول على بيانات المالك
                $stmt_user = $conn->prepare("SELECT user_id FROM annonces WHERE id = ?");
                $stmt_user->bind_param("i", $ad_id);
                $stmt_user->execute();
                $ad_owner = $stmt_user->get_result()->fetch_assoc();
                
                // 8. التوجيه إلى صفحة المستخدم
                ob_end_clean();
                header("Location: compte.php?user_id=".$ad_owner['user_id']."&ad_id=$ad_id#my-ads-section");
                exit();
            }
        }
        elseif (isset($_POST['reject_ad'])) {
            $reason = htmlspecialchars($_POST['reject_reason'] ?? '', ENT_QUOTES, 'UTF-8');
            $stmt = $conn->prepare("UPDATE annonces SET status = 'rejected', admin_notes = ? WHERE id = ?");
            $stmt->bind_param("si", $reason, $ad_id);
            if ($stmt->execute()) {
                $_SESSION['success_message'] = "L'annonce #$ad_id a été rejetée";
                header("Location: admin_ads.php");
                exit();
            }
        }
    }
}

// جلب الإعلانات المعلقة مع معلومات المالك
$query = "SELECT a.*, u.name as user_name, u.Numéro_de_téléphone as user_phone,
          (SELECT COUNT(*) FROM annonces WHERE user_id = a.user_id AND status = 'approved') as user_ads_count
          FROM annonces a 
          JOIN users u ON a.user_id = u.user_id 
          WHERE a.status = 'pending' 
          ORDER BY a.date_publication DESC";

$ads = [];
if ($result = $conn->query($query)) {
    $ads = $result->fetch_all(MYSQLI_ASSOC);
    
    // فك تشفير صور كل إعلان لعرضها
    foreach ($ads as &$ad) {
        if (!empty($ad['images'])) {
            $ad['images'] = json_decode($ad['images'], true);
            if (!is_array($ad['images'])) {
                $ad['images'] = [];
            }
        } else {
            $ad['images'] = [];
        }
    }
    unset($ad); // كسر المرجع
} else {
    die("خطأ في استعلام قاعدة البيانات: " . $conn->error);
}

// استبدل دالة getDisplayImage() الموجودة بهذه النسخة المحسنة
function getDisplayImage($ad) {
    $default_image = 'assets/images/default-property.jpg';
    
    // 1. أولاً: البحث في image_url إذا كان موجود ومختلف عن الافتراضي
    if (!empty($ad['image_url']) && $ad['image_url'] !== './assets/images/default-property.jpg') {
        $processed_url = processImagePath($ad['image_url']);
        if ($processed_url !== $default_image) {
            return $processed_url;
        }
    }
    
    // 2. ثانياً: البحث في مصفوفة الصور (images JSON)
    if (!empty($ad['images'])) {
        $images = is_string($ad['images']) ? json_decode($ad['images'], true) : $ad['images'];
        
        if (is_array($images) && !empty($images)) {
            foreach ($images as $image) {
                $processed_image = processImagePath($image);
                if ($processed_image !== $default_image) {
                    return $processed_image;
                }
            }
        }
    }
    
    // 3. إرجاع الصورة الافتراضية
    return file_exists($default_image) ? $default_image : null;
}
function processImagePath($image_path) {
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
    $clean_path = ltrim($clean_path, '/');
    
    // قائمة المسارات المحتملة للبحث (مرتبة حسب الأولوية)
    $possible_paths = [
        // للإعلانات المعتمدة
        'uploads/approved_ads/' . basename($clean_path),
        
        // للإعلانات العادية
        'uploads/properties/' . basename($clean_path),
        
        // مسارات أخرى محتملة
        'uploads/' . basename($clean_path),
        'uploads/avatars/' . basename($clean_path),
        
        // المسار الكامل كما هو
        $clean_path,
        
        // إذا كان المسار يحتوي على مجلد uploads
        strpos($clean_path, 'uploads/') !== false ? $clean_path : 'uploads/properties/' . basename($clean_path),
        
        // محاولة مع امتدادات مختلفة
        pathinfo($clean_path, PATHINFO_DIRNAME) . '/' . pathinfo($clean_path, PATHINFO_FILENAME) . '.jpg',
        pathinfo($clean_path, PATHINFO_DIRNAME) . '/' . pathinfo($clean_path, PATHINFO_FILENAME) . '.jpeg',
        pathinfo($clean_path, PATHINFO_DIRNAME) . '/' . pathinfo($clean_path, PATHINFO_FILENAME) . '.png',
        pathinfo($clean_path, PATHINFO_DIRNAME) . '/' . pathinfo($clean_path, PATHINFO_FILENAME) . '.webp'
    ];
    
    // البحث عن أول مسار موجود
    foreach ($possible_paths as $path) {
        if (!empty($path) && file_exists($path) && is_file($path)) {
            return $path;
        }
    }
    
    return file_exists($default_image) ? $default_image : null;
}

// دالة محسنة للحصول على جميع صور الإعلان
function getAllImages($ad) {
    $all_images = [];
    
    // 1. إضافة الصورة الرئيسية إذا كانت موجودة
    if (!empty($ad['image_url']) && $ad['image_url'] !== './assets/images/default-property.jpg') {
        $processed_url = processImagePath($ad['image_url']);
        if ($processed_url && $processed_url !== 'assets/images/default-property.jpg') {
            $all_images[] = $processed_url;
        }
    }
    
    // 2. إضافة الصور من JSON
    if (!empty($ad['images'])) {
        $images = is_string($ad['images']) ? json_decode($ad['images'], true) : $ad['images'];
        
        if (is_array($images)) {
            foreach ($images as $image) {
                $processed_image = processImagePath($image);
                if ($processed_image && $processed_image !== 'assets/images/default-property.jpg') {
                    // تجنب التكرار
                    if (!in_array($processed_image, $all_images)) {
                        $all_images[] = $processed_image;
                    }
                }
            }
        }
    }
    
    return $all_images;
}

?>
<!DOCTYPE html>
<html lang="ar" dir="lrt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panneau de configuration - Gestion des annonces</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
            margin: 0;
            padding: 0;
            color: #333;
        }
        
        .admin-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
        }
        
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 15px;
            border-bottom: 1px solid #ddd;
        }
        
        .admin-header h1 {
            color: #2c3e50;
            margin: 0;
        }
        
        .back-btn {
            background-color: #3498db;
            color: white;
            padding: 8px 15px;
            border-radius: 4px;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .ads-list {
            display: grid;
            gap: 20px;
        }
        
        .ad-card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .ad-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            background-color: #f8f9fa;
            border-bottom: 1px solid #eee;
        }
        
        .ad-title {
            font-size: 18px;
            font-weight: 600;
            margin: 0;
            color: #2c3e50;
        }
        
        .ad-user {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #7f8c8d;
        }
        
        .ad-content {
            display: flex;
            padding: 15px;
            gap: 20px;
        }
        
        .ad-image-container {
            position: relative;
            width: 200px;
            height: 150px;
        }
        
        .ad-image {
            width: 100%;
            height: 100%;
            border-radius: 5px;
            object-fit: cover;
            border: 2px solid #ddd;
        }
        
        .image-error {
            width: 100%;
            height: 100%;
            background-color: #f8f9fa;
            border: 2px dashed #ddd;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            border-radius: 5px;
            color: #666;
            font-size: 12px;
            text-align: center;
            padding: 10px;
        }
        
        .image-count {
            position: absolute;
            bottom: 5px;
            right: 5px;
            background-color: rgba(0,0,0,0.7);
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 12px;
        }
        
        .ad-details {
            flex: 1;
        }
        
        .ad-price {
            font-size: 20px;
            font-weight: bold;
            color: #e74c3c;
            margin: 5px 0;
        }
        
        .ad-meta {
            display: flex;
            gap: 15px;
            margin: 10px 0;
            color: #7f8c8d;
            flex-wrap: wrap;
        }
        
        .ad-meta-item {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .ad-description {
            margin: 10px 0;
            line-height: 1.6;
            max-height: 100px;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .ad-actions {
            display: flex;
            gap: 10px;
            padding: 15px;
            border-top: 1px solid #eee;
        }
        
        .approve-btn, .reject-btn {
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: all 0.3s ease;
        }
        
        .approve-btn {
            background-color: #2ecc71;
            color: white;
        }
        
        .approve-btn:hover {
            background-color: #27ae60;
        }
        
        .reject-btn {
            background-color: #e74c3c;
            color: white;
        }
        
        .reject-btn:hover {
            background-color: #c0392b;
        }
        
        .reject-form {
            display: none;
            padding: 15px;
            background-color: #f8f9fa;
            border-top: 1px solid #eee;
        }
        
        .reject-form textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 10px;
            resize: vertical;
            min-height: 80px;
        }
        
        .reject-form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }
        
        .no-ads {
            text-align: center;
            padding: 40px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .no-ads i {
            font-size: 48px;
            color: #bdc3c7;
            margin-bottom: 15px;
        }
        
        .status-message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .success-message {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .debug-info {
            font-size: 10px;
            color: #999;
            margin-top: 5px;
        }
        
        @media (max-width: 768px) {
            .ad-content {
                flex-direction: column;
            }
            
            .ad-image-container {
                width: 100%;
                height: 200px;
            }
            
            .ad-actions {
                flex-direction: column;
            }
            
            .ad-meta {
                flex-direction: column;
                gap: 8px;
            }
        }

        
.image-gallery {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.9);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
}

.image-gallery img {
    max-width: 90%;
    max-height: 80%;
    object-fit: contain;
    border-radius: 8px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
}

.gallery-controls {
    position: absolute;
    bottom: 20px;
    display: flex;
    gap: 10px;
    align-items: center;
}

.gallery-btn {
    background-color: rgba(255, 255, 255, 0.2);
    color: white;
    border: none;
    padding: 10px 15px;
    border-radius: 50%;
    cursor: pointer;
    font-size: 16px;
    transition: background-color 0.3s;
}

.gallery-btn:hover {
    background-color: rgba(255, 255, 255, 0.4);
}

.close-gallery {
    position: absolute;
    top: 20px;
    right: 20px;
    background-color: rgba(255, 255, 255, 0.2);
    color: white;
    border: none;
    padding: 10px 15px;
    border-radius: 50%;
    cursor: pointer;
    font-size: 20px;
}

.image-counter {
    position: absolute;
    top: 20px;
    left: 20px;
    color: white;
    background-color: rgba(0, 0, 0, 0.5);
    padding: 5px 10px;
    border-radius: 15px;
}

.ad-image:hover {
    transform: scale(1.02);
    transition: transform 0.3s ease;
}
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <h1><i class="fas fa-home"></i> Gestion des annonces en attente</h1>
            <a href="compte.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Retour au compte
            </a>
        </div>
        
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="status-message success-message">
                <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (empty($ads)): ?>
            <div class="no-ads">
                <i class="fas fa-home"></i>
                <h3>Aucune annonce en attente de modération</h3>
                <p>Toutes les annonces ont été examinées et traitées.</p>
            </div>
        <?php else: ?>
            <div class="ads-list">
                <?php foreach ($ads as $ad): ?>
                    <div class="ad-card" id="ad-<?php echo $ad['id']; ?>">
                        <div class="ad-header">
                            <h3 class="ad-title"><?php echo htmlspecialchars($ad['titre']); ?></h3>
                            <div class="ad-user">
                                <i class="fas fa-user"></i>
                                <?php echo htmlspecialchars($ad['user_name']); ?>
                                <small>(<?php echo $ad['user_ads_count']; ?> annonces précédentes)</small>
                            </div>
                        </div>
                        
                        <div class="ad-content">
                           
<!-- استبدل قسم عرض الصورة في HTML بهذا الكود -->
<div class="ad-image-container">
    <?php 
    $all_images = getAllImages($ad);
    $display_image = !empty($all_images) ? $all_images[0] : null;
    
    if ($display_image && file_exists($display_image)):
    ?>
        <!-- عرض الصورة الرئيسية -->
        <img src="<?php echo htmlspecialchars($display_image); ?>" 
             alt="Photo du bien" class="ad-image"
             onclick="showImageGallery(<?php echo $ad['id']; ?>)"
             style="cursor: pointer;"
             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
        
        <!-- عداد الصور -->
        <?php if (count($all_images) > 1): ?>
            <div class="image-count">
                <i class="fas fa-images"></i> <?php echo count($all_images); ?>
            </div>
        <?php endif; ?>
        
        <!-- رسالة خطأ احتياطية -->
        <div class="image-error" style="display: none;">
            <i class="fas fa-image"></i>
            <div>Erreur de chargement de l'image</div>
        </div>
        
    <?php else: ?>
        <div class="image-error">
            <i class="fas fa-image"></i>
            <div>Aucune image disponible</div>
            <div class="debug-info">
                Images trouvées: <?php echo count($all_images); ?>
                <?php if (!empty($all_images)): ?>
                    <br>Chemins: <?php echo implode(', ', array_map('basename', $all_images)); ?>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
    
    <!-- معرض الصور المخفي -->
    <?php if (count($all_images) > 1): ?>
        <div id="gallery-<?php echo $ad['id']; ?>" class="image-gallery" style="display: none;">
            <?php foreach ($all_images as $index => $image): ?>
                <img src="<?php echo htmlspecialchars($image); ?>" 
                     alt="Image <?php echo $index + 1; ?>" 
                     data-index="<?php echo $index; ?>">
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

                            
                            <div class="ad-details">
                                <div class="ad-price"><?php echo number_format($ad['prix']); ?> DZD</div>
                                
                                <div class="ad-meta">
                                    <?php if (!empty($ad['surface'])): ?>
                                    <div class="ad-meta-item">
                                        <i class="fas fa-ruler-combined"></i>
                                        <?php echo $ad['surface']; ?> m²
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($ad['chambres'])): ?>
                                    <div class="ad-meta-item">
                                        <i class="fas fa-bed"></i>
                                        <?php echo $ad['chambres']; ?> chambres
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($ad['salles_de_bain'])): ?>
                                    <div class="ad-meta-item">
                                        <i class="fas fa-bath"></i>
                                        <?php echo $ad['salles_de_bain']; ?> salles de bain
                                    </div>
                                    <?php endif; ?>
                                    
                                    <div class="ad-meta-item">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <?php echo htmlspecialchars($ad['wilaya']); ?>, <?php echo htmlspecialchars($ad['commune']); ?>
                                    </div>
                                    
                                    <div class="ad-meta-item">
                                        <i class="fas fa-calendar"></i>
                                        <?php echo date('Y-m-d H:i', strtotime($ad['date_publication'])); ?>
                                    </div>
                                </div>
                                
                                <div class="ad-description">
                                    <?php echo nl2br(htmlspecialchars($ad['description'])); ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="ad-actions">
                            <form method="post" style="display: inline;">
                                <input type="hidden" name="ad_id" value="<?php echo $ad['id']; ?>">
                                <button type="submit" name="approve_ad" class="approve-btn">
                                    <i class="fas fa-check"></i> Approuver
                                </button>
                            </form>
                            
                            <button class="reject-btn" onclick="showRejectForm(<?php echo $ad['id']; ?>)">
                                <i class="fas fa-times"></i> Rejeter
                            </button>
                        </div>
                        
                        <div class="reject-form" id="reject-form-<?php echo $ad['id']; ?>">
                            <form method="post">
                                <input type="hidden" name="ad_id" value="<?php echo $ad['id']; ?>">
                                <textarea name="reject_reason" placeholder="Motif du rejet (optionnel)" rows="3"></textarea>
                                <div class="reject-form-actions">
                                    <button type="button" class="reject-btn" onclick="hideRejectForm(<?php echo $ad['id']; ?>)">
                                        Annuler
                                    </button>
                                    <button type="submit" name="reject_ad" class="approve-btn">
                                        Confirmer le rejet
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        function showRejectForm(adId) {
            document.getElementById(`reject-form-${adId}`).style.display = 'block';
        }
        
        function hideRejectForm(adId) {
            document.getElementById(`reject-form-${adId}`).style.display = 'none';
        }
        
        function handleImageError(img, adId) {
            console.log('خطأ في تحميل الصورة للإعلان:', adId);
            console.log('المسار الذي فشل:', img.src);
            
            // إزالة الصورة وعرض رسالة خطأ
            img.style.display = 'none';
            img.parentElement.innerHTML = `
                <div class="image-error">
                    <i class="fas fa-image"></i>
                    <div>Image non disponible</div>
                    <div class="debug-info">ID: ${adId}</div>
                </div>
            `;
        }
        
        // Masquer les messages d'alerte après 5 secondes
        setTimeout(() => {
            const messages = document.querySelectorAll('.status-message');
            messages.forEach(msg => {
                msg.style.display = 'none';
            });
        }, 5000);
        
        // طباعة معلومات تشخيصية في console
        console.log('Current page URL:', window.location.href);
        console.log('Directory structure check:');
        console.log('- uploads/properties/ exists:', <?php echo file_exists('uploads/properties/') ? 'true' : 'false'; ?>);
        console.log('- uploads/approved_ads/ exists:', <?php echo file_exists('uploads/approved_ads/') ? 'true' : 'false'; ?>);
        console.log('- uploads/avatars/ exists:', <?php echo file_exists('uploads/avatars/') ? 'true' : 'false'; ?>);
    
let currentGallery = null;
let currentImageIndex = 0;

function showImageGallery(adId) {
    const gallery = document.getElementById(`gallery-${adId}`);
    if (!gallery) return;
    
    const images = gallery.querySelectorAll('img');
    if (images.length === 0) return;
    
    currentGallery = gallery;
    currentImageIndex = 0;
    
    // إنشاء عارض الصور
    const viewer = document.createElement('div');
    viewer.className = 'image-gallery';
    viewer.innerHTML = `
        <button class="close-gallery" onclick="closeGallery()">
            <i class="fas fa-times"></i>
        </button>
        
        <div class="image-counter">
            <span id="current-image-num">1</span> / ${images.length}
        </div>
        
        <img id="gallery-main-image" src="${images[0].src}" alt="Image">
        
        ${images.length > 1 ? `
        <div class="gallery-controls">
            <button class="gallery-btn" onclick="previousImage()">
                <i class="fas fa-chevron-left"></i>
            </button>
            <button class="gallery-btn" onclick="nextImage()">
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>
        ` : ''}
    `;
    
    document.body.appendChild(viewer);
    document.body.style.overflow = 'hidden';
    
    // إضافة مستمع للوحة المفاتيح
    document.addEventListener('keydown', galleryKeyHandler);
}

function closeGallery() {
    const galleries = document.querySelectorAll('.image-gallery');
    galleries.forEach(gallery => gallery.remove());
    document.body.style.overflow = 'auto';
    document.removeEventListener('keydown', galleryKeyHandler);
    currentGallery = null;
}

function nextImage() {
    if (!currentGallery) return;
    const images = currentGallery.querySelectorAll('img');
    currentImageIndex = (currentImageIndex + 1) % images.length;
    updateGalleryImage(images);
}

function previousImage() {
    if (!currentGallery) return;
    const images = currentGallery.querySelectorAll('img');
    currentImageIndex = currentImageIndex === 0 ? images.length - 1 : currentImageIndex - 1;
    updateGalleryImage(images);
}

function updateGalleryImage(images) {
    const mainImage = document.getElementById('gallery-main-image');
    const counter = document.getElementById('current-image-num');
    
    if (mainImage && images[currentImageIndex]) {
        mainImage.src = images[currentImageIndex].src;
    }
    
    if (counter) {
        counter.textContent = currentImageIndex + 1;
    }
}

function galleryKeyHandler(e) {
    switch(e.key) {
        case 'Escape':
            closeGallery();
            break;
        case 'ArrowLeft':
            previousImage();
            break;
        case 'ArrowRight':
            nextImage();
            break;
    }
}

// تحسين معالج خطأ الصورة
function handleImageError(img, adId, imagePath) {
    console.log('خطأ في تحميل الصورة:', {
        adId: adId,
        imagePath: imagePath,
        element: img
    });
    
    // إخفاء الصورة وإظهار رسالة الخطأ
    img.style.display = 'none';
    
    const errorDiv = img.parentElement.querySelector('.image-error');
    if (errorDiv) {
        errorDiv.style.display = 'flex';
        errorDiv.innerHTML = `
            <i class="fas fa-exclamation-triangle"></i>
            <div>Image non disponible</div>
            <div class="debug-info">ID: ${adId}<br>Chemin: ${imagePath}</div>
        `;
    }
}

// فحص الصور عند تحميل الصفحة
document.addEventListener('DOMContentLoaded', function() {
    const images = document.querySelectorAll('.ad-image');
    
    images.forEach(img => {
        // فحص ما إذا كانت الصورة محملة بالفعل
        if (img.complete) {
            if (img.naturalWidth === 0) {
                handleImageError(img, img.dataset.adId || 'unknown', img.src);
            }
        }
    });
    
    console.log('فحص الصور مكتمل. تم العثور على', images.length, 'صورة');
});
    </script>
</body>
</html>