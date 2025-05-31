<?php
include 'header.php';
?>
<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Homeverse - Trouvez la maison de vos rêves</title>

  <!-- 
    - favicon
  -->
  <link rel="shortcut icon" href="./favicon.svg" type="image/svg+xml">
  <link rel="stylesheet" href="style.css">
  <!-- 
    - lien CSS personnalisé
  -->
  <link rel="stylesheet" href="./assets/css/style.css">
  <link href="https://unpkg.com/boxicons@2.1.2/css/boxicons.min.css" rel="stylesheet">
  <!-- Font Awesome pour les icônes -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <!-- 
    - lien Google Fonts
  -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@400;600;700&family=Poppins:wght@400;500;600;700&display=swap"
    rel="stylesheet">
</head>
<body>
    
<?php
// تضمين ملف الاتصال بقاعدة البيانات
require 'db_connect.php';

// التحقق من وجود معرّف العقار في URL
if(isset($_GET['id'])){
    $property_id = intval($_GET['id']);
    
  
$sql = "SELECT 
        a.*, 
        COALESCE(a.contact_tel, u.Numéro_de_téléphone, 'Non disponible') AS contact_phone,
        GROUP_CONCAT(
            CONCAT(pf.feature_name, ':', pf.is_available) 
            SEPARATOR '|'
        ) AS features_data
    FROM annonces a 
    LEFT JOIN users u ON a.user_id = u.user_id 
    LEFT JOIN property_features pf ON a.id = pf.annonce_id
    WHERE a.id = ?
    GROUP BY a.id";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $property_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $property = $result->fetch_assoc();
        
        // دالة مساعدة لمعالجة مسارات الصور (نفس الدالة من الملف الأول)
        function processImagePath($image_path) {
            $base_dir = 'uploads/properties/';
            $default_image = 'assets/images/default-property.jpg';
            
            // إذا كان مسار خارجي (http)
            if (strpos($image_path, 'http') === 0) {
                return $image_path;
            }
            
            // تنظيف المسار المحلي
            $clean_path = str_replace(['../', './'], '', $image_path);
            
            // إذا كان المسار يحتوي على مجلد uploads
            if (strpos($clean_path, 'uploads/properties/') !== false) {
                $final_path = $clean_path;
            } else {
                $final_path = $base_dir . basename($clean_path);
            }
            
            // التحقق من وجود الملف
            return file_exists($final_path) ? $final_path : $default_image;
        }

        // معالجة الصور بنفس الطريقة المستخدمة في البطاقات
        $raw_image = $property['image_url'] ?? '';
        $image_url = processImagePath($raw_image);

        // إذا لم تجد الصورة الرئيسية، ابحث في الصور المتعددة
        if ($image_url === 'assets/images/default-property.jpg' && isset($property['images'])) {
            $images_array = json_decode($property['images'], true);
            if (!empty($images_array) && is_array($images_array)) {
                foreach ($images_array as $img) {
                    $temp_img = processImagePath($img);
                    if ($temp_img !== 'assets/images/default-property.jpg') {
                        $image_url = $temp_img;
                        break;
                    }
                }
            }
        }

        // بناء مصفوفة جميع الصور المتاحة
        $images = [];

        // إضافة الصورة الرئيسية إذا كانت متاحة
        if ($image_url !== 'assets/images/default-property.jpg') {
            $images[] = $image_url;
        }

        // معالجة الصور الإضافية من حقل images
        if (!empty($property['images'])) {
            // محاولة فك تشفير JSON أولاً
            $additional_images = json_decode($property['images'], true);
            
            // إذا فشل JSON، استخدم الفاصلة كما في الملف الأول
            if (!is_array($additional_images)) {
                $additional_images = explode(',', $property['images']);
            }
            
            if (!empty($additional_images)) {
                foreach ($additional_images as $img) {
                    $processed_img = processImagePath(trim($img));
                    
                    // تجنب التكرار وإضافة الصور الصالحة فقط
                    if ($processed_img !== 'assets/images/default-property.jpg' && 
                        !in_array($processed_img, $images)) {
                        $images[] = $processed_img;
                    }
                }
            }
        }

        // إذا لم توجد أي صور، استخدم الصورة الافتراضية
        if (empty($images)) {
            $images[] = 'assets/images/default-property.jpg';
        }
        
        // تعيين المتغيرات لاستخدامها في الصفحة
        $titre = htmlspecialchars($property['titre'] ?? 'Sans titre');
        $type_bien = htmlspecialchars($property['type_bien'] ?? 'Type inconnu');
        $prix = isset($property['prix']) ? number_format($property['prix'], 0, ',', ' ') : 'N/A';
        $wilaya = htmlspecialchars($property['wilaya'] ?? 'Wilaya inconnue');
        $adresse = htmlspecialchars($property['adresse'] ?? 'Adresse non spécifiée');
        $chambres = htmlspecialchars($property['chambres'] ?? 'N/A');
        $salles_de_bain = htmlspecialchars($property['salles_de_bain'] ?? 'N/A');
        $surface = htmlspecialchars($property['surface'] ?? 'N/A');
        $description = htmlspecialchars($property['description'] ?? 'Pas de description disponible');
        $contact_nom = htmlspecialchars($property['contact_nom'] ?? 'Non spécifié');
        $contact_phone = htmlspecialchars($property['contact_phone'] ?? 'Non disponible');
        $date_poste = htmlspecialchars($property['date_publication'] ?? 'Date inconnue');
        
    } else {
        header("Location: ACHert.php");
        exit();
    }
} else {
    header("Location: ACHert.php");
    exit();
}

function processPropertyFeatures($features_data) {
    $default_features = [
        'Ascenseur' => false,
        'Gardiennage' => false,
        'Terrain de jeux' => false,
        'Espaces verts' => false,
        'Eau courante' => true,
        'Groupe électrogène' => false,
        'Parking' => false,
        'Salle de sport' => false,
        'Climatisation' => false,
        'Chauffage' => false,
        'Internet/WiFi' => false,
        'Sécurité 24h/24' => false
    ];
    
    if (!empty($features_data)) {
        $features_array = explode('|', $features_data);
        foreach ($features_array as $feature) {
            $feature_parts = explode(':', $feature);
            if (count($feature_parts) == 2) {
                $feature_name = trim($feature_parts[0]);
                $is_available = $feature_parts[1] == '1';
                $default_features[$feature_name] = $is_available;
            }
        }
    }
    
    return $default_features;
}

// =============================================================================
// 4. تحسين قسم عرض المميزات في HTML
// =============================================================================

// استبدال قسم amenities-section بهذا الكود:
?>



<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Homeverse - <?php echo $titre; ?></title>

  <!-- Favicon -->
  <link rel="shortcut icon" href="./favicon.svg" type="image/svg+xml">
  
  <!-- CSS personnalisé -->
  <link rel="stylesheet" href="./assets/css/style.css">
  <link href="https://unpkg.com/boxicons@2.1.2/css/boxicons.min.css" rel="stylesheet">

  <!-- Font Awesome pour les icônes -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@400;600;700&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>

<section class="view-property">
    <div class="details">
        
<div class="thumb">
    <div class="big-image">
        <img src="<?php echo $images[0]; ?>" 
             alt="<?php echo $titre; ?>" 
             class="main-property-img"
             onerror="this.src='assets/images/default-property.jpg'">
        <div class="image-badge">En Vedette</div>
    </div>
    
    <div class="small-images">
        <?php foreach($images as $index => $img): ?>
            <img src="<?php echo $img; ?>" 
                 alt="<?php echo $titre . ' - Image ' . ($index + 1); ?>" 
                 class="thumbnail <?php echo $index === 0 ? 'active' : ''; ?>"
                 onerror="this.src='assets/images/default-property.jpg'"
                 onclick="updateMainImage('<?php echo addslashes($img); ?>', '<?php echo addslashes($titre . ' - Image ' . ($index + 1)); ?>')">
        <?php endforeach; ?>

        <?php if(count($images) > 4): ?>
            <button class="view-more-btn">+<?php echo count($images) - 4; ?> Plus</button>
        <?php endif; ?>
    </div>
    
    <div class="more-images-modal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <h3>Toutes les images du bien</h3>
            <div class="modal-images">
                <?php foreach($images as $index => $img): ?>
                    <img src="<?php echo $img; ?>" 
                         alt="<?php echo $titre . ' - Image ' . ($index + 1); ?>"
                         onerror="this.src='assets/images/default-property.jpg'"
                         onclick="selectImageFromModal('<?php echo addslashes($img); ?>', '<?php echo addslashes($titre . ' - Image ' . ($index + 1)); ?>')">
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
          
        <div class="property-header">
           <h1 class="name"><?php echo $titre; ?></h1>
           
            <p class="location">
                <i class="fas fa-map-marker-alt"></i>
                <span><?php echo $wilaya . ', ' . $adresse; ?></span>
                <a href="#" class="view-map">Voir sur la carte</a>
            </p>
        </div>
        
        <div class="price-section">
            <div class="price-info">
                <span class="price"><?php echo $prix; ?> DA</span>
                <span class="price-label">Prix Total</span>
            </div>
            <div class="action-buttons">
                <a href="https://wa.me/<?php echo $contact_phone; ?>" class="whatsapp-btn" title="Contactez-nous sur WhatsApp">
                    <i class="fab fa-whatsapp"></i>
                </a>
                <div class="phone-number">
    <i class="fas fa-phone-alt"></i>
    <?php if (!empty($contact_phone) && $contact_phone !== 'Non disponible'): ?>
        <a href="tel:<?= preg_replace('/[^0-9+]/', '', $contact_phone) ?>">
            <?= $contact_phone ?>
        </a>
    <?php else: ?>
        <span class="no-phone">Non disponible</span>
    <?php endif; ?>
</div>
            </div>
        </div>
        
        <div class="quick-info">
            <div class="info-item">
                <i class="fas fa-tag"></i>
                <div>
                    <span class="label">Prix</span>
                    <span class="value"><?php echo $prix; ?> DA</span>
                </div>
            </div>
            <div class="info-item">
                <i class="fas fa-user"></i>
                <div>
                    <span class="label">Propriétaire</span>
                    <span class="value"><?php echo $contact_nom; ?></span>
                </div>
            </div>
            <div class="info-item">
                <i class="fas fa-building"></i>
                <div>
                    <span class="label">Type</span>
                    <span class="value"><?php echo $type_bien; ?></span>
                </div>
            </div>
            <div class="info-item">
                <i class="fas fa-calendar"></i>
                <div>
                    <span class="label">Publié le</span>
                    <span class="value"><?php echo $date_poste; ?></span>
                </div>
            </div>
        </div>
        
      <div class="property-details">
    <h2 class="section-title">Détails du bien</h2>
    <div class="details-grid">
        <?php if(isset($property['type_annonce'])): ?>
        <div class="detail-item">
            <span class="detail-label">Type d'annonce</span>
            <span class="detail-value"><?php echo $property['type_annonce']; ?></span>
        </div>
        <?php endif; ?>
        
        <?php if(isset($property['type_bien'])): ?>
        <div class="detail-item">
            <span class="detail-label">Type de bien</span>
            <span class="detail-value"><?php echo $property['type_bien']; ?></span>
        </div>
        <?php endif; ?>
        
        <?php if(isset($property['surface'])): ?>
        <div class="detail-item">
            <span class="detail-label">Surface</span>
            <span class="detail-value"><?php echo $property['surface']; ?> m²</span>
        </div>
        <?php endif; ?>
        
        <?php if(isset($property['chambres'])): ?>
        <div class="detail-item">
            <span class="detail-label">Chambres</span>
            <span class="detail-value"><?php echo $property['chambres']; ?></span>
        </div>
        <?php endif; ?>
        
        <?php if(isset($property['salles_de_bain'])): ?>
        <div class="detail-item">
            <span class="detail-label">Salles de bain</span>
            <span class="detail-value"><?php echo $property['salles_de_bain']; ?></span>
        </div>
        <?php endif; ?>
        
        <?php if(isset($property['wilaya'])): ?>
        <div class="detail-item">
            <span class="detail-label">Wilaya</span>
            <span class="detail-value"><?php echo $property['wilaya']; ?></span>
        </div>
        <?php endif; ?>
        
        <?php if(isset($property['commune'])): ?>
        <div class="detail-item">
            <span class="detail-label">Commune</span>
            <span class="detail-value"><?php echo $property['commune']; ?></span>
        </div>
        <?php endif; ?>
        
        <?php if(isset($property['date_publication'])): ?>
        <div class="detail-item">
            <span class="detail-label">Date de publication</span>
            <span class="detail-value"><?php echo date('d/m/Y', strtotime($property['date_publication'])); ?></span>
        </div>
        <?php endif; ?>
    </div>
</div>
   

<div class="amenities-section">
    <h2 class="section-title">Équipements</h2>
    <div class="amenities-grid">
        <?php 
        $property_features = processPropertyFeatures($property['features_data'] ?? '');
        foreach($property_features as $feature_name => $is_available): 
        ?>
            <div class="amenity-item <?php echo $is_available ? 'available' : 'not-available'; ?>">
                <i class="fas <?php echo $is_available ? 'fa-check-circle' : 'fa-times-circle'; ?>"></i>
                <span><?php echo htmlspecialchars($feature_name); ?></span>
            </div>
        <?php endforeach; ?>
    </div>
</div>


        
        <div class="description-section">
            <h2 class="section-title">Description</h2>
            <p class="description-text">
                <?php echo nl2br($description); ?>
            </p>
        </div>
        
        <div class="property-actions">
            <button class="btn save-btn" onclick="toggleSaveProperty(<?php echo $property_id; ?>)">
                <i class="far fa-heart"></i> <span class="btn-text">Sauvegarder</span>
            </button>
            <button class="btn share-btn" onclick="shareProperty()">
                <i class="fas fa-share-alt"></i> <span class="btn-text">Partager</span>
            </button>
        </div>
    </div>
</section>
 <?php include('footer.php'); ?>
<script>
    // تعريف المتغيرات الأساسية
    const thumbnails = document.querySelectorAll('.thumbnail');
    const mainImage = document.querySelector('.main-property-img');
    const viewMoreBtn = document.querySelector('.view-more-btn');
    const moreImagesModal = document.querySelector('.more-images-modal');
    const closeModal = document.querySelector('.close-modal');
    const modalImages = document.querySelectorAll('.modal-images img');
    
    // تبديل الصور المصغرة
    thumbnails.forEach(thumb => {
        thumb.addEventListener('click', function() {
            // إزالة الفعال من جميع الصور المصغرة
            thumbnails.forEach(t => t.classList.remove('active'));
            
            // إضافة الفعال للصورة المحددة
            this.classList.add('active');
        });
    });
    
    // عرض الصور الإضافية
    if(viewMoreBtn) {
        viewMoreBtn.addEventListener('click', function() {
            moreImagesModal.style.display = 'block';
            document.body.style.overflow = 'hidden';
        });
    }
    
    // إغلاق نافذة الصور الإضافية
    closeModal.addEventListener('click', function() {
        moreImagesModal.style.display = 'none';
        document.body.style.overflow = 'auto';
    });
    
    // إغلاق النافذة عند النقر خارجها
    window.addEventListener('click', function(event) {
        if (event.target === moreImagesModal) {
            moreImagesModal.style.display = 'none';
            document.body.style.overflow = 'auto';
        }
    });
    
    // وظيفة لتحديث الصورة الرئيسية
    function updateMainImage(src, alt) {
        mainImage.style.opacity = '0';
        setTimeout(() => {
            mainImage.src = src;
            mainImage.alt = alt;
            mainImage.style.opacity = '1';
        }, 200);
    }
    
    // اختيار صورة من النافذة المنبثقة
    function selectImageFromModal(src, alt) {
        updateMainImage(src, alt);
        
        // تحديث الصور المصغرة النشطة
        thumbnails.forEach(t => t.classList.remove('active'));
        const clickedThumb = Array.from(thumbnails).find(t => t.src === src);
        if (clickedThumb) clickedThumb.classList.add('active');
        
        // إغلاق النافذة
        moreImagesModal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
    
    // حفظ العقار
    function toggleSaveProperty(propertyId) {
        const saveBtn = document.querySelector('.save-btn');
        const isSaved = saveBtn.classList.contains('saved');
        
        if(isSaved) {
            // إزالة من المحفوظات
            saveBtn.innerHTML = '<i class="far fa-heart"></i> <span class="btn-text">Sauvegarder</span>';
            saveBtn.classList.remove('saved');
            // يمكنك هنا إضافة كود لإزالة العقار من المحفوظات في قاعدة البيانات
        } else {
            // إضافة إلى المحفوظات
            saveBtn.innerHTML = '<i class="fas fa-heart"></i> <span class="btn-text">Sauvegardé</span>';
            saveBtn.classList.add('saved');
            // يمكنك هنا إضافة كود لحفظ العقار في قاعدة البيانات
        }
        
        // إرسال طلب AJAX لتحديث حالة الحفظ
        const xhr = new XMLHttpRequest();
        xhr.open('POST', 'save_property.php', true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.send(`property_id=${propertyId}&action=${isSaved ? 'remove' : 'add'}`);
    }
    
    // مشاركة العقار
    function shareProperty() {
        if(navigator.share) {
            navigator.share({
                title: document.querySelector('.name').textContent,
                text: 'Découvrez ce bien immobilier sur Homeverse: ' + document.querySelector('.description-text').textContent.substring(0, 100) + '...',
                url: window.location.href
            }).catch(err => {
                console.log('Error sharing:', err);
            });
        } else {
            // Fallback للعرض على أجهزة لا تدعم واجهة مشاركة المتصفح
            alert('Copiez ce lien pour partager ce bien: ' + window.location.href);
        }
    }
</script>

</body>

  <script src="./assets/js/script.js"></script>

  <!-- 
    - ionicon link
  -->
  <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
  <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>

<style>
    :root {
    --primary-color: #4a6bff;
    --secondary-color: #f8f9fa;
    --text-color: #333;
    --light-text: #666;
    --border-color: #e0e0e0;
    --success-color: #28a745;
    --danger-color: #dc3545;
    --warning-color: #ffc107;
    --white: #fff;
    --black: #000;
    --box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
}

.view-property {
    max-width: 1200px;
    margin: 2rem auto;
    padding: 0 1rem;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.view-property .details {
    background-color: var(--white);
    box-shadow: var(--box-shadow);
    border-radius: 10px;
    overflow: hidden;
    padding: 2rem;
}

/* صور العقار */
.view-property .thumb {
    position: relative;
    margin-bottom: 2rem;
}

.view-property .big-image {
    height: 450px;
    border-radius: 8px;
    overflow: hidden;
    position: relative;
}

.view-property .big-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.view-property .big-image:hover img {
    transform: scale(1.02);
}

.image-badge {
    position: absolute;
    top: 15px;
    left: 15px;
    background-color: var(--primary-color);
    color: white;
    padding: 5px 10px;
    border-radius: 4px;
    font-size: 0.9rem;
    font-weight: bold;
}
.view-property .small-images {
    display: flex;
    gap: 10px;
    margin-top: 15px;
    flex-wrap: wrap;
}

.view-property .small-images img {
    width: 80px;
    height: 60px;
    object-fit: cover;
    border-radius: 5px;
    cursor: pointer;
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.view-property .small-images img:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.view-property .small-images img.active {
    border-color: var(--primary-color);
}

.view-more-btn {
    background-color: var(--primary-color);
    color: white;
    border: none;
    padding: 0 15px;
    border-radius: 5px;
    cursor: pointer;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    transition: background-color 0.3s;
}

.view-more-btn:hover {
    background-color: #3a5bd9;
}

/* عنوان العقار */
.property-header {
    margin-bottom: 1.5rem;
}

.property-header .name {
    font-size: 1.8rem;
    color: var(--text-color);
    margin-bottom: 0.5rem;
    font-weight: 700;
}

.rating {
    color: var(--warning-color);
    margin-bottom: 0.8rem;
    display: flex;
    align-items: center;
    gap: 5px;
}

.rating span {
    color: var(--light-text);
    font-size: 0.9rem;
    margin-left: 5px;
}

.location {
    display: flex;
    align-items: center;
    gap: 8px;
    color: var(--light-text);
    font-size: 1rem;
    margin-bottom: 1rem;
}

.location i {
    color: var(--primary-color);
}

.view-map {
    color: var(--primary-color);
    text-decoration: none;
    margin-left: 10px;
    font-size: 0.9rem;
}

.view-map:hover {
    text-decoration: underline;
}

/* قسم السعر */
.price-section {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.5rem;
    background-color: var(--secondary-color);
    border-radius: 8px;
    margin-bottom: 2rem;
}

.price-info .price {
    font-size: 1.8rem;
    font-weight: bold;
    color: var(--primary-color);
}

.price-info .price-label {
    display: block;
    color: var(--light-text);
    font-size: 0.9rem;
}

.action-buttons {
    display: flex;
    gap: 15px;
    align-items: center;
}

.whatsapp-btn {
    background-color: #25D366;
    color: white;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
    transition: all 0.3s;
}

.whatsapp-btn:hover {
    transform: scale(1.1);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.phone-number {
    display: flex;
    align-items: center;
    gap: 8px;
    background-color: var(--primary-color);
    color: white;
    padding: 10px 15px;
    border-radius: 6px;
    font-weight: 600;
}

.phone-number i {
    font-size: 1.1rem;
}

.phone-number span {
    color: white;
    text-decoration: none;
    transition: all 0.3s;
}

.phone-number:hover {
    background-color: #3a5bd9;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

@media (max-width: 768px) {
    .action-buttons {
        flex-direction: column;
        width: 100%;
    }
    
    .phone-number {
        width: 100%;
        justify-content: center;
    }
}

/* المعلومات السريعة */
.quick-info {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 2rem;
}

.info-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px;
    background-color: var(--secondary-color);
    border-radius: 8px;
}

.info-item i {
    font-size: 1.2rem;
    color: var(--primary-color);
}

.info-item .label {
    display: block;
    color: var(--light-text);
    font-size: 0.8rem;
    margin-bottom: 3px;
}

.info-item .value {
    font-weight: 600;
    color: var(--text-color);
}

/* تفاصيل العقار */
.section-title {
    font-size: 1.5rem;
    color: var(--text-color);
    margin-bottom: 1.5rem;
    padding-bottom: 0.8rem;
    border-bottom: 1px solid var(--border-color);
    position: relative;
}

.section-title::after {
    content: '';
    position: absolute;
    bottom: -1px;
    left: 0;
    width: 80px;
    height: 2px;
    background-color: var(--primary-color);
}

.details-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-bottom: 2rem;
}

.detail-item {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
    border-bottom: 1px dashed var(--border-color);
}

.detail-label {
    color: var(--light-text);
}

.detail-value {
    font-weight: 600;
}

/* وسائل الراحة */
.amenities-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-bottom: 2rem;
}

.amenity-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 0;
}

.amenity-item i {
    font-size: 1.1rem;
}

.amenity-item.available {
    color: var(--success-color);
}

.amenity-item.not-available {
    color: var(--danger-color);
    opacity: 0.7;
}

/* الوصف */
.description-text {
    color: var(--light-text);
    line-height: 1.7;
    margin-bottom: 2rem;
}

/* أزرار العمل */
.property-actions {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
    margin-top: 2rem;
}

.btn {
    padding: 10px 20px;
    border-radius: 6px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s;
    border: none;
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 0.95rem;
}

.save-btn {
    background-color: transparent;
    border: 1px solid var(--primary-color);
    color: var(--primary-color);
}

.save-btn:hover {
    background-color: rgba(74, 107, 255, 0.1);
}

.save-btn.saved {
    background-color: #ffeef1;
    color: #ff4d6d;
    border-color: #ff4d6d;
}

.save-btn.saved i {
    color: #ff4d6d;
}

.share-btn {
    background-color: var(--secondary-color);
    color: var(--text-color);
}

.btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

/* تصميم متجاوب */
@media (max-width: 768px) {
    .price-section {
        flex-direction: column;
        gap: 15px;
        align-items: flex-start;
    }
    
    .action-buttons {
        width: 100%;
    }
    
    .btn {
        flex: 1;
        justify-content: center;
    }
    
    .view-property .big-image {
        height: 300px;
    }
    
    .details-grid, .amenities-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 480px) {
    .view-property .details {
        padding: 1rem;
    }
    
    .details-grid, .amenities-grid {
        grid-template-columns: 1fr;
    }
    
    .property-actions {
        flex-direction: column;
    }
    
    .btn {
        width: 100%;
    }
    
    .btn-text {
        display: none;
    }
    
    .btn {
        justify-content: center;
        width: 45px;
        height: 45px;
        border-radius: 50%;
        padding: 0;
    }
}

/* إضافة هذه الأنماط للصور المخفية والنافذة المنبثقة */
.hidden-image {
        display: none;
    }
    
    .more-images-modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.9);
        overflow: auto;
        padding-top: 60px;
    }
    
    .modal-content {
        background-color: #fefefe;
        margin: auto;
        padding: 20px;
        border-radius: 8px;
        max-width: 900px;
        position: relative;
    }
    
    .close-modal {
        position: absolute;
        top: 10px;
        right: 20px;
        font-size: 30px;
        font-weight: bold;
        color: #aaa;
        cursor: pointer;
    }
    
    .close-modal:hover {
        color: #333;
    }
    
    .modal-images {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 15px;
        margin-top: 20px;
    }
    
    .modal-images img {
        width: 100%;
        height: 200px;
        object-fit: cover;
        border-radius: 5px;
        cursor: pointer;
        transition: transform 0.3s;
    }
    
    .modal-images img:hover {
        transform: scale(1.05);
    }
    
    @media (max-width: 768px) {
        .modal-images {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    
    @media (max-width: 480px) {
        .modal-images {
            grid-template-columns: 1fr;
        }
    }

</style>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // تعريف المتغيرات الأساسية
        const thumbnails = document.querySelectorAll('.thumbnail');
        const mainImage = document.querySelector('.main-property-img');
        const viewMoreBtn = document.querySelector('.view-more-btn');
        const moreImagesModal = document.querySelector('.more-images-modal');
        const closeModal = document.querySelector('.close-modal');
        const modalImages = document.querySelectorAll('.modal-images img');
        const saveBtn = document.querySelector('.save-btn');
        
        // تبديل الصور المصغرة
        thumbnails.forEach(thumb => {
            thumb.addEventListener('click', function() {
                // إزالة الفعال من جميع الصور المصغرة
                thumbnails.forEach(t => t.classList.remove('active'));
                
                // إضافة الفعال للصورة المحددة
                this.classList.add('active');
                
                // تغيير الصورة الرئيسية
                updateMainImage(this.src, this.alt);
            });
        });
        
        // زر حفظ العقار
        saveBtn.addEventListener('click', function(e) {
            e.preventDefault();
            this.classList.toggle('saved');
            if (this.classList.contains('saved')) {
                this.innerHTML = '<i class="fas fa-heart"></i> <span class="btn-text">Sauvegardé</span>';
            } else {
                this.innerHTML = '<i class="far fa-heart"></i> <span class="btn-text">Sauvegarder</span>';
            }
        });
        
        // عرض الصور الإضافية
        viewMoreBtn.addEventListener('click', function() {
            moreImagesModal.style.display = 'block';
            document.body.style.overflow = 'hidden';
        });
        
        // إغلاق نافذة الصور الإضافية
        closeModal.addEventListener('click', function() {
            moreImagesModal.style.display = 'none';
            document.body.style.overflow = 'auto';
        });
        
        // إغلاق النافذة عند النقر خارجها
        window.addEventListener('click', function(event) {
            if (event.target === moreImagesModal) {
                moreImagesModal.style.display = 'none';
                document.body.style.overflow = 'auto';
            }
        });
        
        // اختيار صورة من النافذة المنبثقة
        modalImages.forEach(img => {
            img.addEventListener('click', function() {
                // تغيير الصورة الرئيسية
                updateMainImage(this.src, this.alt);
                
                // تحديث الصور المصغرة النشطة
                thumbnails.forEach(t => t.classList.remove('active'));
                const clickedThumb = Array.from(thumbnails).find(t => t.src === this.src);
                if (clickedThumb) clickedThumb.classList.add('active');
                
                // إغلاق النافذة
                moreImagesModal.style.display = 'none';
                document.body.style.overflow = 'auto';
            });
        });
        
        // مشاركة العقار
        const shareBtn = document.querySelector('.share-btn');
        shareBtn.addEventListener('click', function() {
            if(navigator.share) {
                navigator.share({
                    title: 'Modern Flats and Apartments in Andheri',
                    text: 'Check out this beautiful property for sale!',
                    url: window.location.href
                }).catch(err => {
                    console.log('Error sharing:', err);
                });
            } else {
                alert('Share this property using your preferred app. URL: ' + window.location.href);
            }
        });
        
        // وظيفة مساعدة لتحديث الصورة الرئيسية
        function updateMainImage(src, alt) {
            mainImage.style.opacity = '0';
            setTimeout(() => {
                mainImage.src = src;
                mainImage.alt = alt;
                mainImage.style.opacity = '1';
            }, 200);
        }
    });
    
    

  
document.addEventListener('DOMContentLoaded', function() {
    // جلب بيانات العقار من localStorage
    const property = JSON.parse(localStorage.getItem('currentProperty'));
    
    // إذا لم توجد بيانات، نعود إلى الصفحة الرئيسية
   

    // تعبئة البيانات في الصفحة
    document.querySelector('.name').textContent = property.title;
    document.querySelector('.price').textContent = property.price;
    document.querySelector('.location span').textContent = property.location;
    document.querySelector('.description-text').textContent = property.description;
    
    // تعبئة الصورة الرئيسية
    const mainImage = document.querySelector('.main-property-img');
    mainImage.src = property.images[0];
    mainImage.alt = property.title;
    
    // تعبئة الصور المصغرة
    const thumbnailsContainer = document.querySelector('.small-images');
    thumbnailsContainer.innerHTML = '';
    
    property.images.forEach((img, index) => {
        const thumbnail = document.createElement('img');
        thumbnail.src = img;
        thumbnail.alt = `${property.title} - Image ${index + 1}`;
        thumbnail.className = 'thumbnail' + (index === 0 ? ' active' : '');
        thumbnail.onclick = function() {
            updateMainImage(img, this.alt);
            document.querySelectorAll('.thumbnail').forEach(t => t.classList.remove('active'));
            this.classList.add('active');
        };
        thumbnailsContainer.appendChild(thumbnail);
    });
    
    // تعبئة معلومات العقار السريعة
    document.querySelector('.quick-info .info-item:nth-child(1) .value').textContent = property.price;
    document.querySelector('.quick-info .info-item:nth-child(2) .value').textContent = property.agent.name;
    document.querySelector('.quick-info .info-item:nth-child(3) .value').textContent = property.type;
    document.querySelector('.quick-info .info-item:nth-child(4) .value').textContent = property.date;
    
    // تعبئة تفاصيل العقار
    document.querySelector('.details-grid').innerHTML = `
        <div class="detail-item">
            <span class="detail-label">Pièces</span>
            <span class="detail-value">${property.bedrooms} Pièces</span>
        </div>
        <div class="detail-item">
            <span class="detail-label">Chambres</span>
            <span class="detail-value">${property.bedrooms}</span>
        </div>
        <div class="detail-item">
            <span class="detail-label">Salles de bain</span>
            <span class="detail-value">${property.bathrooms}</span>
        </div>
        <div class="detail-item">
            <span class="detail-label">Balcon</span>
            <span class="detail-value">1</span>
        </div>
        <div class="detail-item">
            <span class="detail-label">Surface</span>
            <span class="detail-value">${property.area}</span>
        </div>
        <div class="detail-item">
            <span class="detail-label">Ancienneté</span>
            <span class="detail-value">3 ans</span>
        </div>
        <div class="detail-item">
            <span class="detail-label">Étage</span>
            <span class="detail-value">3/8</span>
        </div>
        <div class="detail-item">
            <span class="detail-label">Meublé</span>
            <span class="detail-value">Semi-meublé</span>
        </div>
    `;
    
    // تعبئة المرافق
    const amenitiesContainer = document.querySelector('.amenities-grid');
    amenitiesContainer.innerHTML = '';
    
    property.amenities.forEach(amenity => {
        const amenityItem = document.createElement('div');
        amenityItem.className = 'amenity-item available';
        amenityItem.innerHTML = `<i class="fas fa-check-circle"></i><span>${amenity}</span>`;
        amenitiesContainer.appendChild(amenityItem);
    });
    
    // تعبئة معلومات الوكيل
    document.querySelector('.agent-name').textContent = property.agent.name;
    document.querySelector('.agent-avatar').src = property.agent.avatar;
    document.querySelector('.phone-number a').textContent = property.agent.phone;
    document.querySelector('.phone-number a').href = `tel:${property.agent.phone}`;
    
    // تعبئة العلامة
    document.querySelector('.image-badge').textContent = property.badge;
});

// دالة لتحديث الصورة الرئيسية
function updateMainImage(src, alt) {
    const mainImage = document.querySelector('.main-property-img');
    mainImage.style.opacity = '0';
    setTimeout(() => {
        mainImage.src = src;
        mainImage.alt = alt;
        mainImage.style.opacity = '1';
    }, 200);
}

// دالة لعرض المزيد من الصور
document.querySelector('.view-more-btn').addEventListener('click', function() {
    document.querySelector('.more-images-modal').style.display = 'block';
    document.body.style.overflow = 'hidden';
});

// دالة لإغلاق نافذة الصور
document.querySelector('.close-modal').addEventListener('click', function() {
    document.querySelector('.more-images-modal').style.display = 'none';
    document.body.style.overflow = 'auto';
});

// إغلاق النافذة عند النقر خارجها
window.addEventListener('click', function(event) {
    if (event.target === document.querySelector('.more-images-modal')) {
        document.querySelector('.more-images-modal').style.display = 'none';
        document.body.style.overflow = 'auto';
    }
});

// زر الحفظ
document.querySelector('.save-btn').addEventListener('click', function(e) {
    e.preventDefault();
    this.innerHTML = '<i class="fas fa-heart"></i> Sauvegardé';
    this.style.backgroundColor = '#ffeef1';
    this.style.color = '#ff4d6d';
    this.style.borderColor = '#ff4d6d';
});

// زر المشاركة
document.querySelector('.share-btn').addEventListener('click', function() {
    if(navigator.share) {
        navigator.share({
            title: document.querySelector('.name').textContent,
            text: 'Check out this beautiful property!',
            url: window.location.href
        }).catch(err => {
            console.log('Error sharing:', err);
        });
    } else {
        alert('Share this property using your preferred app. URL: ' + window.location.href);
    }
});












function toggleSaveProperty(propertyId) {
    // التحقق من تسجيل الدخول
    <?php if (!isset($_SESSION['user']) || !isset($_SESSION['user_id'])): ?>
        alert('يجب تسجيل الدخول أولاً');
        window.location.href = 'login.php';
        return;
    <?php endif; ?>
    
    const saveBtn = document.querySelector(`button[onclick="toggleSaveProperty(${propertyId})"]`);
    const heart = saveBtn.querySelector('i');
    const btnText = saveBtn.querySelector('.btn-text');
    
    // تعطيل الزر مؤقتاً
    saveBtn.disabled = true;
    btnText.textContent = 'جاري المعالجة...';
    
    // تحديد العملية (إضافة أو إزالة)
    const isCurrentlySaved = heart.classList.contains('fas');
    const action = isCurrentlySaved ? 'remove' : 'add';
    
    fetch('save_property.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `property_id=${propertyId}&action=${action}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            // تحديث شكل الزر
            if (action === 'add') {
                heart.className = 'fas fa-heart'; // قلب ممتلئ
                btnText.textContent = 'محفوظ';
                saveBtn.style.color = '#e74c3c';
            } else {
                heart.className = 'far fa-heart'; // قلب فارغ
                btnText.textContent = 'حفظ';
                saveBtn.style.color = '';
            }
            
            // عرض رسالة نجاح
            showNotification(data.message, 'success');
        } else {
            showNotification(data.message || 'حدث خطأ', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('حدث خطأ في الاتصال', 'error');
    })
    .finally(() => {
        // إعادة تفعيل الزر
        saveBtn.disabled = false;
        if (btnText.textContent === 'جاري المعالجة...') {
            btnText.textContent = 'حفظ';
        }
    });
}

// دالة لعرض الإشعارات
function showNotification(message, type) {
    // إنشاء عنصر الإشعار
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.innerHTML = `
        <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
        ${message}
    `;
    
    // إضافة الأنماط
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'success' ? '#2ecc71' : '#e74c3c'};
        color: white;
        padding: 15px 20px;
        border-radius: 5px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        z-index: 10000;
        display: flex;
        align-items: center;
        gap: 10px;
        animation: slideIn 0.3s ease;
    `;
    
    // إضافة CSS للأنيميشن
    if (!document.querySelector('#notification-styles')) {
        const style = document.createElement('style');
        style.id = 'notification-styles';
        style.textContent = `
            @keyframes slideIn {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
            @keyframes slideOut {
                from { transform: translateX(0); opacity: 1; }
                to { transform: translateX(100%); opacity: 0; }
            }
        `;
        document.head.appendChild(style);
    }
    
    document.body.appendChild(notification);
    
    // إزالة الإشعار بعد 3 ثوان
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, 3000);
}
</script>
</body>
</html>
