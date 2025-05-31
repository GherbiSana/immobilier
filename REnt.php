<?php
include 'header.php';
?>
<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Homeverse - Achetez la maison de vos rêves</title>

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
  <!-- Favicon -->
  <link rel="shortcut icon" href="./favicon.svg" type="image/svg+xml">
  
  <!-- CSS personnalisé -->
  <link rel="stylesheet" href="./assets/css/style.css">
  <link href="https://unpkg.com/boxicons@2.1.2/css/boxicons.min.css" rel="stylesheet">

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link
    href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@400;600;700&family=Poppins:wght@400;500;600;700&display=swap"
    rel="stylesheet">
</head>

<body>
 
  <main>
    <div class="container search-container">
         <?php
// استقبال وتنظيف معايير البحث من URL
function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

$wilaya = isset($_GET['wilaya']) ? cleanInput($_GET['wilaya']) : '';
$property_type = isset($_GET['property_type']) ? cleanInput($_GET['property_type']) : '';
$min_price = isset($_GET['min_price']) ? (int)$_GET['min_price'] : null;
$max_price = isset($_GET['max_price']) ? (int)$_GET['max_price'] : null;
$chambres = isset($_GET['chambres']) ? cleanInput($_GET['chambres']) : '';
?>
      <form id="search-form" class="search-bar active">
        <select name="find" id="find-select" class="select-highlight">
          <option value="Location" selected>Location</option>
          <option value="Achat">Achat</option>
        </select>
            
        <select name="wilaya" id="wilaya-select">
                    <option value="">Sélectionnez une wilaya</option>
        <option value="1" <?php echo (isset($_GET['wilaya']) && $_GET['wilaya'] == '1' ? 'selected' : ''); ?>>Adrar (1)</option>
        <option value="2" <?php echo (isset($_GET['wilaya']) && $_GET['wilaya'] == '2' ? 'selected' : ''); ?>>Chlef (2)</option>
        <option value="3" <?php echo (isset($_GET['wilaya']) && $_GET['wilaya'] == '3' ? 'selected' : ''); ?>>Laghouat (3)</option>
        <option value="4" <?php echo (isset($_GET['wilaya']) && $_GET['wilaya'] == '4' ? 'selected' : ''); ?>>Oum El Bouaghi (4)</option>
        <option value="5" <?php echo (isset($_GET['wilaya']) && $_GET['wilaya'] == '5' ? 'selected' : ''); ?>>Batna (5)</option>
        <option value="6" <?php echo (isset($_GET['wilaya']) && $_GET['wilaya'] == '6' ? 'selected' : ''); ?>>Béjaïa (6)</option>
        <option value="7" <?php echo (isset($_GET['wilaya']) && $_GET['wilaya'] == '7' ? 'selected' : ''); ?>>Biskra (7)</option>
        <option value="8" <?php echo (isset($_GET['wilaya']) && $_GET['wilaya'] == '8' ? 'selected' : '');?>>Béchar (8)</option>
        <option value="9" <?php echo (isset($_GET['wilaya']) && $_GET['wilaya'] == '9' ? 'selected' : ''); ?>>Blida (9)</option>
        <option value="10" <?php echo (isset($_GET['wilaya']) && $_GET['wilaya'] == '10' ? 'selected' : ''); ?>>Bouira (10)</option>
        <option value="11" <?php echo (isset($_GET['wilaya']) && $_GET['wilaya'] == '11' ? 'selected' : ''); ?>>Tamanrasset (11)</option>
        <option value="12" <?php echo (isset($_GET['wilaya']) && $_GET['wilaya'] == '12' ? 'selected' : ''); ?>>Tébessa (12)</option>
        <option value="13" <?php echo (isset($_GET['wilaya']) && $_GET['wilaya'] == '13' ? 'selected' : ''); ?>>Tlemcen (13)</option>
        <option value="14" <?php echo (isset($_GET['wilaya']) && $_GET['wilaya'] == '14' ? 'selected' : ''); ?>>Tiaret (14)</option>
        <option value="15" <?php echo (isset($_GET['wilaya']) && $_GET['wilaya'] == '15' ? 'selected' : ''); ?>>Tizi Ouzou (15)</option>
        <option value="16" <?php echo (isset($_GET['wilaya']) && $_GET['wilaya'] == '16' ? 'selected' : ''); ?>>Alger (16)</option>
        <option value="17" <?php echo (isset($_GET['wilaya']) && $_GET['wilaya'] == '17' ? 'selected' : ''); ?>>Djelfa (17)</option>
        <option value="18" <?php echo (isset($_GET['wilaya']) && $_GET['wilaya'] == '18' ? 'selected' : ''); ?>>Jijel (18)</option>
        <option value="19" <?php echo (isset($_GET['wilaya']) && $_GET['wilaya'] == '19' ? 'selected' : ''); ?>>Sétif (19)</option>
        <option value="20" <?php echo (isset($_GET['wilaya']) && $_GET['wilaya'] == '20' ? 'selected' : ''); ?>>Saïda (20)</option>
        <option value="21" <?php echo (isset($_GET['wilaya']) && $_GET['wilaya'] == '21' ? 'selected' : ''); ?>>Skikda (21)</option>
        <option value="22" <?php echo (isset($_GET['wilaya']) && $_GET['wilaya'] == '22' ? 'selected' : ''); ?>>Sidi Bel Abbès (22)</option>
        <option value="23" <?php echo (isset($_GET['wilaya']) && $_GET['wilaya'] == '23' ? 'selected' : ''); ?>>Annaba (23)</option>
        <option value="24" <?php echo (isset($_GET['wilaya']) && $_GET['wilaya'] == '24' ? 'selected' : ''); ?>>Guelma (24)</option>
        <option value="25" <?php echo (isset($_GET['wilaya']) && $_GET['wilaya'] == '25' ? 'selected' : ''); ?>>Constantine (25)</option>
        <option value="26" <?php echo (isset($_GET['wilaya']) && $_GET['wilaya'] == '26' ? 'selected' : ''); ?>>Médéa (26)</option>
        <option value="27" <?php echo (isset($_GET['wilaya']) && $_GET['wilaya'] == '27' ? 'selected' : ''); ?>>Mostaganem (27)</option>
        <option value="28" <?php echo (isset($_GET['wilaya']) && $_GET['wilaya'] == '28' ? 'selected' : ''); ?>>M'Sila (28)</option>
        <option value="29" <?php echo (isset($_GET['wilaya']) && $_GET['wilaya'] == '29' ? 'selected' : ''); ?>>Mascara (29)</option>
        <option value="30" <?php echo (isset($_GET['wilaya']) && $_GET['wilaya'] == '30' ? 'selected' : ''); ?>>Ouargla (30)</option>
        <option value="31" <?php echo (isset($_GET['wilaya']) && $_GET['wilaya'] == '31' ? 'selected' : ''); ?>>Oran (31)</option>
        <option value="32" <?php echo (isset($_GET['wilaya']) && $_GET['wilaya'] == '32' ? 'selected' : ''); ?>>El Bayadh (32)</option>
        <option value="33" <?php echo (isset($_GET['wilaya']) && $_GET['wilaya'] == '33' ? 'selected' : ''); ?>>Illizi (33)</option>
        <option value="34" <?php echo (isset($_GET['wilaya']) && $_GET['wilaya'] == '34' ? 'selected' : ''); ?>>Bordj Bou Arréridj (34)</option>
        <option value="35" <?php echo (isset($_GET['wilaya']) && $_GET['wilaya'] == '35' ? 'selected' : ''); ?>>Boumerdès (35)</option>
        <option value="36" <?php echo (isset($_GET['wilaya']) && $_GET['wilaya'] == '36' ? 'selected' : ''); ?>>El Tarf (36)</option>
        <option value="37" <?php echo (isset($_GET['wilaya']) && $_GET['wilaya'] == '37' ? 'selected' : ''); ?>>Tindouf (37)</option>
        <option value="38" <?php echo (isset($_GET['wilaya']) && $_GET['wilaya'] == '38' ? 'selected' : ''); ?>>Tissemsilt (38)</option>
        <option value="39" <?php echo (isset($_GET['wilaya']) && $_GET['wilaya'] == '39' ? 'selected' : ''); ?>>El Oued (39)</option>
        <option value="40" <?php echo (isset($_GET['wilaya']) && $_GET['wilaya'] == '40' ? 'selected' : ''); ?>>Khenchela (40)</option>
        <option value="41" <?php echo (isset($_GET['wilaya']) && $_GET['wilaya'] == '41' ? 'selected' : ''); ?>>Souk Ahras (41)</option>
        <option value="42" <?php echo (isset($_GET['wilaya']) && $_GET['wilaya'] == '42' ? 'selected' : ''); ?>>Tipaza (42)</option>
        <option value="43" <?php echo (isset($_GET['wilaya']) && $_GET['wilaya'] == '43' ? 'selected' : ''); ?>>Mila (43)</option>
        <option value="44" <?php echo (isset($_GET['wilaya']) && $_GET['wilaya'] == '44' ? 'selected' : ''); ?>>Aïn Defla (44)</option>
        <option value="45" <?php echo (isset($_GET['wilaya']) && $_GET['wilaya'] == '45' ? 'selected' : ''); ?>>Naâma (45)</option>
        <option value="46" <?php echo (isset($_GET['wilaya']) && $_GET['wilaya'] == '46' ? 'selected' : ''); ?>>Aïn Témouchent (46)</option>
        <option value="47" <?php echo (isset($_GET['wilaya']) && $_GET['wilaya'] == '47' ? 'selected' : ''); ?>>Ghardaïa (47)</option>
        <option value="48" <?php echo (isset($_GET['wilaya']) && $_GET['wilaya'] == '48' ? 'selected' : ''); ?>>Relizane (48)</option>
    </select>
      
       <select name="property_type" id="property-type-select">
        <option value="">Type de propriété</option>
        <option value="Appartement" <?php echo (isset($_GET['property_type']) && $_GET['property_type'] == 'Appartement' ? 'selected' : ''); ?>>Appartement</option>
        <option value="Maison" <?php echo (isset($_GET['property_type']) && $_GET['property_type'] == 'Maison' ? 'selected' : ''); ?>>Maison</option>
        <option value="Studio" <?php echo (isset($_GET['property_type']) && $_GET['property_type'] == 'Studio' ? 'selected' : ''); ?>>Studio</option>
        <option value="Villa" <?php echo (isset($_GET['property_type']) && $_GET['property_type'] == 'Villa' ? 'selected' : ''); ?>>Villa</option>
    </select>
     
    <!-- استبدال حقول الغرف والحمامات المخفية بـ select مباشر -->
    <select name="chambres" id="chambres-select" class="form-select">
        <option value="">Nombre de chambres</option>
        <option value="1" <?= (isset($_GET['chambres']) && $_GET['chambres'] == '1') ? 'selected' : '' ?>>1</option>
        <option value="2" <?= (isset($_GET['chambres']) && $_GET['chambres'] == '2') ? 'selected' : '' ?>>2</option>
        <option value="3" <?= (isset($_GET['chambres']) && $_GET['chambres'] == '3') ? 'selected' : '' ?>>3</option>
        <option value="4" <?= (isset($_GET['chambres']) && $_GET['chambres'] == '4') ? 'selected' : '' ?>>4</option>
        <option value="5" <?= (isset($_GET['chambres']) && $_GET['chambres'] == '5') ? 'selected' : '' ?>>5</option>
        <option value="6+" <?= (isset($_GET['chambres']) && $_GET['chambres'] == '6+') ? 'selected' : '' ?>>6+</option>
    </select>

<input type="number" name="min_price" id="min-price-input" 
       placeholder="Prix minimum" 
       value="<?php echo isset($_GET['min_price']) ? htmlspecialchars($_GET['min_price']) : ''; ?>">
       
<input type="number" name="max_price" id="max-price-input" 
       placeholder="Prix maximum" 
       value="<?php echo isset($_GET['max_price']) ? htmlspecialchars($_GET['max_price']) : ''; ?>">
    
        <button type="submit">Rechercher</button>
      </form>
    </div>
    
    <div class="container results-section">
      <div id="search-results-header">
        <h3>Résultats de recherche pour l'achat</h3>
        <div id="search-criteria" class="search-tags"></div>
      </div>
      
      <div class="view-toggle">
        <button class="toggle-btn active" id="list-view-btn">
          <i class="fas fa-list"></i> Vue liste
        </button>
        <button class="toggle-btn" id="map-view-btn">
          <i class="fas fa-map-marker-alt"></i> Vue carte
        </button>
      </div>

      <div class="map-view" id="map-view">
        <div class="map-placeholder">
          <i class="fas fa-map-marked-alt"></i>
          Carte interactive des propriétés à vendre en Algérie
        </div>
      </div>

      <div id="results-container">
 <?php
require 'db_connect.php';
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // معالجة معايير البحث
    $wilaya = $_GET['wilaya'] ?? '';
    $property_type = $_GET['property_type'] ?? '';
    $min_price = $_GET['min_price'] ?? '';
    $max_price = $_GET['max_price'] ?? '';
    $chambres = $_GET['chambres'] ?? '';
    $transaction_type = $_GET['find'] ?? 'Location';

    // بناء الاستعلام الأساسي مع JOIN لجلب بيانات المستخدم
    $sql = "SELECT a.*, u.name as user_name, u.avatar, u.Numéro_de_téléphone as user_phone 
            FROM annonces a 
            LEFT JOIN users u ON a.user_id = u.user_id 
            WHERE a.type_annonce = 'Location'";
    $conditions = [];

    // إضافة شروط البحث
    if (!empty($wilaya)) {
        $conditions[] = "a.wilaya = '" . $conn->real_escape_string($wilaya) . "'";
    }
    
    if (!empty($property_type)) {
        $conditions[] = "a.type_bien = '" . $conn->real_escape_string($property_type) . "'";
    }
    
    if (!empty($min_price) && is_numeric($min_price)) {
        $conditions[] = "a.prix >= " . intval($min_price);
    }
    
    if (!empty($max_price) && is_numeric($max_price)) {
        $conditions[] = "a.prix <= " . intval($max_price);
    }
    
    // معالجة عدد الغرف
    if (!empty($chambres)) {
        if ($chambres === '6+') {
            $conditions[] = "(a.chambres >= 6 AND a.chambres IS NOT NULL)";
        } elseif (is_numeric($chambres)) {
            $conditions[] = "(a.chambres = " . intval($chambres) . " AND a.chambres IS NOT NULL)";
        }
    }

    // إضافة الشروط إلى الاستعلام
    if (!empty($conditions)) {
        $sql .= " AND " . implode(" AND ", $conditions);
    }

    // ترتيب النتائج
    $sql .= " ORDER BY a.date_publication DESC";

    // تنفيذ الاستعلام
    $result = $conn->query($sql);

    // بناء معايير البحث للعرض
    $search_criteria = [];
    if (!empty($wilaya)) {
        // الحصول على اسم الولاية
        $wilaya_names = [
            '1' => 'Adrar', '2' => 'Chlef', '3' => 'Laghouat', '4' => 'Oum El Bouaghi',
            '5' => 'Batna', '6' => 'Béjaïa', '7' => 'Biskra', '8' => 'Béchar',
            '9' => 'Blida', '10' => 'Bouira', '11' => 'Tamanrasset', '12' => 'Tébessa',
            '13' => 'Tlemcen', '14' => 'Tiaret', '15' => 'Tizi Ouzou', '16' => 'Alger',
            '17' => 'Djelfa', '18' => 'Jijel', '19' => 'Sétif', '20' => 'Saïda',
            '21' => 'Skikda', '22' => 'Sidi Bel Abbès', '23' => 'Annaba', '24' => 'Guelma',
            '25' => 'Constantine', '26' => 'Médéa', '27' => 'Mostaganem', '28' => 'M\'Sila',
            '29' => 'Mascara', '30' => 'Ouargla', '31' => 'Oran', '32' => 'El Bayadh',
            '33' => 'Illizi', '34' => 'Bordj Bou Arréridj', '35' => 'Boumerdès', '36' => 'El Tarf',
            '37' => 'Tindouf', '38' => 'Tissemsilt', '39' => 'El Oued', '40' => 'Khenchela',
            '41' => 'Souk Ahras', '42' => 'Tipaza', '43' => 'Mila', '44' => 'Aïn Defla',
            '45' => 'Naâma', '46' => 'Aïn Témouchent', '47' => 'Ghardaïa', '48' => 'Relizane'
        ];
        $wilaya_name = $wilaya_names[$wilaya] ?? $wilaya;
        $search_criteria[] = "Wilaya: " . htmlspecialchars($wilaya_name);
    }
    
    if (!empty($property_type)) {
        $search_criteria[] = "Type: " . htmlspecialchars($property_type);
    }
    
    if (!empty($min_price)) {
        $search_criteria[] = "Prix min: " . number_format($min_price, 0, ',', ' ') . " DA";
    }
    
    if (!empty($max_price)) {
        $search_criteria[] = "Prix max: " . number_format($max_price, 0, ',', ' ') . " DA";
    }
    
    if (!empty($chambres)) {
        $search_criteria[] = "Chambres: " . htmlspecialchars($chambres);
    }

    // عرض معايير البحث
    if (!empty($search_criteria)) {
        echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchCriteria = document.getElementById('search-criteria');
            if (searchCriteria) {
                searchCriteria.innerHTML = '" . addslashes(implode('', array_map(function($c) {
                    return "<span class='search-tag'>$c</span>";
                }, $search_criteria))) . "';
            }
        });
        </script>";
    }
       
    // دالة مساعدة لمعالجة مسارات الصور
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

    // دالة محسنة لمعالجة صورة المستخدم (avatar)
    function processUserAvatar($avatar_path, $user_id) {
        $default_avatar = 'assets/images/default-user.png';
        
        if (empty($avatar_path)) {
            return $default_avatar;
        }
        
        // إذا كان مسار خارجي (http)
        if (strpos($avatar_path, 'http') === 0) {
            return $avatar_path;
        }
        
        // تنظيف المسار المحلي
        $clean_path = str_replace(['../', './'], '', $avatar_path);
        
        // تحديد المسار الصحيح بناء على هيكل الملفات
        $possible_paths = [
            'uploads/avatars/' . basename($clean_path),
            'uploads/avatars/' . $user_id . '/' . basename($clean_path),
            'uploads/users/' . $user_id . '/' . basename($clean_path),
            $clean_path
        ];
        
        // البحث عن أول مسار موجود
        foreach ($possible_paths as $path) {
            if (file_exists($path)) {
                return $path;
            }
        }
        
        return $default_avatar;
    }

    // عرض النتائج
    if ($result && $result->num_rows > 0) {
        echo "<div class='properties-grid'>";
        
        while ($row = $result->fetch_assoc()) {
            // تعريف جميع المتغيرات بقيم افتراضية قبل استخدامها
            $id = $row['id'] ?? 'N/A';
            $user_id = $row['user_id'] ?? 0;
            $titre = $row['titre'] ?? 'Sans titre';
            $type_bien = $row['type_bien'] ?? 'Type inconnu';
            $prix = isset($row['prix']) ? number_format($row['prix'], 0, ',', ' ') : 'N/A';
            $wilaya_display = $row['wilaya'] ?? 'Wilaya inconnue';
            $adresse = $row['adresse'] ?? 'Adresse non spécifiée';
            $chambres_display = $row['chambres'] ?? 'N/A';
            $salles_de_bain = $row['salles_de_bain'] ?? 'N/A';
            $surface = $row['surface'] ?? 'N/A';
            $contact_nom = $row['contact_nom'] ?? ($row['user_name'] ?? 'Non spécifié');
            $contact_tel = $row['contact_tel'] ?? ($row['user_phone'] ?? '');
            $date_poste = $row['date_publication'] ?? 'Date inconnue';

            // معالجة الصورة
            $raw_image = $row['image_url'] ?? '';
            $image_url = processImagePath($raw_image);
            
            if ($image_url === 'assets/images/default-property.jpg' && isset($row['images'])) {
                $images_array = json_decode($row['images'], true);
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

            // معالجة صورة المستخدم (avatar) - النسخة المحسنة
            $user_avatar = processUserAvatar($row['avatar'] ?? '', $user_id);

            echo "<div class='property-card' data-id='{$id}'>
                    <div class='property-image-container'>
                        <img src='{$image_url}' 
                             alt='Image du bien' 
                             class='property-image'
                             onerror=\"this.src='assets/images/default-property.jpg'\">
                        <div class='property-actions'>
                            <div class='action-btn favorite-btn' data-property-id='{$id}'>
                                <i class='far fa-heart'></i>
                            </div>
                        </div>
                    </div>
                   
                    <div class='property-content'>
                        <div class='property-price'>{$prix} DA</div>
                        <div class='property-title'>
                            <a href='home.php?id={$id}'>{$type_bien} {$titre}</a>
                        </div>
                        <div class='property-location'>
                            <i class='fas fa-map-marker-alt'></i> {$wilaya_display}, {$adresse}
                        </div>
                        <div class='property-features'>
                            <div class='feature'><i class='fas fa-bed'></i> {$chambres_display}</div>
                            <div class='feature'><i class='fas fa-bath'></i> {$salles_de_bain}</div>
                            <div class='feature'><i class='fas fa-vector-square'></i> {$surface} m²</div>
                        </div>
                    </div>
                    <div class='property-footer'>
                        <div class='property-agent'>
                            <img src='{$user_avatar}' 
                                 alt='Avatar utilisateur' 
                                 class='agent-avatar'
                                 onerror=\"this.src='assets/images/default-user.png'\">
                            <div class='agent-info'>
                                <div class='agent-name'>{$contact_nom}</div>
                                " . (!empty($contact_tel) ? "<div class='agent-phone'><i class='fas fa-phone'></i> {$contact_tel}</div>" : "") . "
                            </div>
                        </div>
                        <div class='property-date'>Publié le {$date_poste}</div>
                    </div>
                </div>";
        }
        echo "</div>"; // إغلاق div.properties-grid
    } else {
        echo "<div class='no-results'>
                <i class='fas fa-home'></i>
                <h4>Aucun résultat trouvé</h4>
                <p>Aucune propriété ne correspond à vos critères de recherche. Essayez de modifier vos filtres pour obtenir plus de résultats.</p>
              </div>";
    }
    
    $conn->close();
}
?>
    
      </div>
      
      <div class="pagination">
        <ul class="pagination-list" id="pagination">
          <!-- Pagination générée par JavaScript -->
        </ul>
</div>
    </div>
  </main>
  

  <style>
    :root {
      --primary-color: #006633; /* Vert algérien */
      --secondary-color: #D21034; /* Rouge algérien */
      --dark-color: #333;
      --light-color: #f8f9fa;
      --border-color: #e0e0e0;
      --text-color: #333;
      --text-light: #666;
      --white: #fff;
      --box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
      --transition: all 0.3s ease;
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    body {
      background-color: var(--light-color);
      color: var(--text-color);
      line-height: 1.6;
    }

    .container {
      width: 90%;
      max-width: 1200px;
      margin: 0 auto;
      padding: 0 15px;
    }

    /* Barre de recherche améliorée */
    .search-container {
      background: rgba(255, 255, 255, 0.95);
      padding: 20px;
      border-radius: 10px;
      box-shadow: var(--box-shadow);
      margin: 20px auto;
    }

    .search-bar {
      display: flex;
      justify-content: center;
      align-items: center;
      flex-wrap: wrap;
      gap: 10px;
      width: 100%;
    }

    .search-bar select,
    .search-bar input,
    .form-select {
      border: 1px solid var(--border-color);
      padding: 12px 15px;
      border-radius: 6px;
      font-size: 15px;
      flex: 1;
      min-width: 150px;
      background-color: var(--white);
    }

    .search-bar button {
      background: var(--secondary-color);
      color: white;
      border: none;
      padding: 12px 25px;
      border-radius: 6px;
      cursor: pointer;
      font-weight: bold;
      transition: background 0.3s;
    }

    .search-bar button:hover {
      background: #b01029;
    }

    /* Section des résultats */
    .results-section {
      margin-top: 30px;
    }

    #search-results-header {
      margin: 20px 0;
      padding: 15px;
      background: var(--white);
      border-radius: 8px;
      box-shadow: var(--box-shadow);
    }

    #search-results-header h3 {
      color: var(--primary-color);
      margin-bottom: 10px;
    }

    #search-criteria {
      display: flex;
      flex-wrap: wrap;
      gap: 10px;
    }

    .search-tag {
      background-color: #f0f0f0;
      border-radius: 20px;
      padding: 5px 15px;
      font-size: 14px;
      display: flex;
      align-items: center;
      gap: 5px;
    }

    .search-tag i {
      color: var(--secondary-color);
      cursor: pointer;
    }

    /* Grille des propriétés */
    .properties-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
      gap: 25px;
      margin-bottom: 40px;
    }

    .property-card {
      background-color: var(--white);
      border-radius: 10px;
      overflow: hidden;
      box-shadow: var(--box-shadow);
      transition: var(--transition);
      display: flex;
      flex-direction: column;
      height: 100%;
    }

    .property-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
    }

    .property-image-container {
      position: relative;
      height: 200px;
      overflow: hidden;
    }

    .property-image {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: var(--transition);
    }

    .property-card:hover .property-image {
      transform: scale(1.05);
    }

    .property-actions {
      position: absolute;
      top: 15px;
      right: 15px;
      display: flex;
      gap: 8px;
      z-index: 2;
    }

    .action-btn {
      width: 32px;
      height: 32px;
      background-color: rgba(255, 255, 255, 0.9);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--dark-color);
      cursor: pointer;
      transition: var(--transition);
    }

    .action-btn:hover {
      background-color: var(--primary-color);
      color: var(--white);
    }

    .property-content {
      padding: 20px;
      display: flex;
      flex-direction: column;
      flex-grow: 1;
    }

    .property-price {
      font-size: 22px;
      font-weight: 700;
      color: var(--primary-color);
      margin-bottom: 10px;
    }

    .property-title {
      font-size: 18px;
      font-weight: 600;
      margin-bottom: 10px;
      line-height: 1.3;
    }

    .property-title a {
      color: var(--dark-color);
      text-decoration: none;
      transition: var(--transition);
    }

    .property-title a:hover {
      color: var(--primary-color);
    }

    .property-location {
      display: flex;
      align-items: center;
      gap: 8px;
      color: var(--text-light);
      font-size: 14px;
      margin-bottom: 15px;
    }

    .property-features {
      display: flex;
      gap: 15px;
      margin-top: auto;
      padding-top: 15px;
      border-top: 1px solid var(--border-color);
    }

    .feature {
      display: flex;
      align-items: center;
      gap: 5px;
      font-size: 14px;
      color: var(--text-light);
    }

    .feature i {
      color: var(--primary-color);
    }

    .property-footer {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 12px 20px;
      border-top: 1px solid var(--border-color);
      background-color: #f9f9f9;
    }

    .property-agent {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .agent-avatar {
      width: 32px;
      height: 32px;
      border-radius: 50%;
      object-fit: cover;
    }

    .agent-name {
      font-size: 14px;
      font-weight: 500;
      color: var(--text-light);
    }

    .property-date {
      font-size: 12px;
      color: var(--text-light);
    }

    /* Toggle entre les vues */
    .view-toggle {
      display: flex;
      justify-content: flex-end;
      margin-bottom: 20px;
    }

    .toggle-btn {
      padding: 8px 15px;
      background-color: var(--white);
      border: 1px solid var(--border-color);
      cursor: pointer;
      transition: var(--transition);
    }

    .toggle-btn:first-child {
      border-radius: 6px 0 0 6px;
    }

    .toggle-btn:last-child {
      border-radius: 0 6px 6px 0;
    }

    .toggle-btn.active {
      background-color: var(--primary-color);
      color: var(--white);
      border-color: var(--primary-color);
    }

    /* Vue carte */
    .map-view {
      display: none;
      height: 500px;
      background-color: #eee;
      border-radius: 8px;
      margin-bottom: 30px;
      overflow: hidden;
    }

    .map-placeholder {
      width: 100%;
      height: 100%;
      display: flex;
      align-items: center;
      justify-content: center;
      background-color: #e9e9e9;
      color: var(--text-light);
      font-size: 18px;
      flex-direction: column;
      gap: 15px;
    }
    
    .map-placeholder i {
      font-size: 40px;
      color: var(--primary-color);
    }

    /* Options de chambres et salles de bains */
    .form-group {
      position: relative;
      flex: 1;
      min-width: 150px;
    }

    .beds-baths-options {
      position: absolute;
      top: 100%;
      left: 0;
      right: 0;
      width: 400px;
      max-width: 100vw;
      background: white;
      border: 1px solid #ddd;
      border-radius: 8px;
      padding: 20px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
      z-index: 9999;
      display: none;
      gap: 20px;
    }

    .beds-baths-options.show {
      display: flex;
    }

    .options-section {
      flex: 1;
      display: flex;
      flex-direction: column;
    }

    .options-section h4 {
      margin-bottom: 10px;
      font-size: 16px;
      color: var(--dark-color);
    }

    .options-row {
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
    }

    .option {
      padding: 8px 12px;
      background: #f5f5f5;
      border-radius: 6px;
      cursor: pointer;
      font-size: 14px;
      transition: all 0.2s;
      border: 1px solid #ddd;
    }

    .option:hover {
      background: #e0e0e0;
    }

    .option.selected {
      background: var(--primary-color);
      color: white;
      border-color: var(--primary-color);
    }

    /* Loading spinner */
    .loading {
      text-align: center;
      padding: 40px 0;
      grid-column: 1 / -1;
    }

    .loading-spinner {
      border: 5px solid #f3f3f3;
      border-top: 5px solid var(--primary-color);
      border-radius: 50%;
      width: 40px;
      height: 40px;
      animation: spin 1s linear infinite;
      margin: 0 auto 15px;
    }

    @keyframes spin {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }

    /* Pagination */
    .pagination {
      display: flex;
      justify-content: center;
      margin: 30px 0;
    }

    .pagination-list {
      display: flex;
      gap: 5px;
      list-style: none;
    }

    .pagination-item {
      width: 40px;
      height: 40px;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 6px;
      background-color: var(--white);
      border: 1px solid var(--border-color);
      cursor: pointer;
      transition: var(--transition);
    }

    .pagination-item:hover {
      background-color: #f5f5f5;
    }

    .pagination-item.active {
      background-color: var(--primary-color);
      color: var(--white);
      border-color: var(--primary-color);
    }

    /* Responsive */
    @media (max-width: 992px) {
      .properties-grid {
        grid-template-columns: repeat(2, 1fr);
      }
      
      .search-bar select,
      .search-bar input,
      .form-select {
        min-width: 120px;
      }
    }

    @media (max-width: 768px) {
      .properties-grid {
        grid-template-columns: 1fr;
      }
      
      .search-bar {
        flex-direction: column;
        align-items: stretch;
      }
      
      .search-bar select,
      .search-bar input,
      .form-select,
      .search-bar button {
        width: 100%;
      }
      
      .beds-baths-options {
        width: 100%;
        flex-direction: column;
      }
    }

    /* Surligner l'option sélectionnée */
    .select-highlight {
      border: 2px solid var(--primary-color) !important;
      outline: none;
    }
    
    /* Style quand il n'y a pas de résultats */
    .no-results {
      grid-column: 1 / -1;
      text-align: center;
      padding: 40px 0;
      background: white;
      border-radius: 8px;
      box-shadow: var(--box-shadow);
    }
    
    .no-results i {
      font-size: 50px;
      color: var(--text-light);
      margin-bottom: 15px;
    }
    
    .no-results h4 {
      font-size: 20px;
      margin-bottom: 10px;
      color: var(--dark-color);
    }
    
    .no-results p {
      color: var(--text-light);
      max-width: 500px;
      margin: 0 auto;
    }


    
.search-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  align-items: center;
}

.search-tag {
  background-color: #f0f0f0;
  border-radius: 20px;
  padding: 5px 15px;
  font-size: 14px;
  display: flex;
  align-items: center;
  gap: 5px;
  white-space: nowrap;
}

@media (max-width: 768px) {
  .results-title-container {
    flex-direction: column;
    align-items: flex-start;
  }
}












.favorite-btn {
    cursor: pointer;
    transition: all 0.3s;
}

.favorite-btn:hover {
    transform: scale(1.1);
}

.favorite-btn i.fas {
    color: #e74c3c;
}

.no-results {
    text-align: center;
    padding: 20px;
    color: #7f8c8d;
    font-style: italic;
}
</style>
<script>

document.addEventListener('DOMContentLoaded', function() {
    const searchForm = document.getElementById('search-form');
    
    // إرسال النموذج عبر إعادة تحميل الصفحة
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            return true;
        });
    }
    
    // تبديل بين عرض القائمة والعرض على الخريطة
    const listViewBtn = document.getElementById('list-view-btn');
    const mapViewBtn = document.getElementById('map-view-btn');
    const mapView = document.getElementById('map-view');
    const propertiesGrid = document.querySelector('.properties-grid');
    
    if (listViewBtn) {
        listViewBtn.addEventListener('click', function() {
            this.classList.add('active');
            if (mapViewBtn) mapViewBtn.classList.remove('active');
            if (mapView) mapView.style.display = 'none';
            if (propertiesGrid) propertiesGrid.style.display = 'grid';
        });
    }
    
    if (mapViewBtn) {
        mapViewBtn.addEventListener('click', function() {
            this.classList.add('active');
            if (listViewBtn) listViewBtn.classList.remove('active');
            if (mapView) mapView.style.display = 'block';
            if (propertiesGrid) propertiesGrid.style.display = 'none';
        });
    }

    // ربط أحداث أزرار المفضلة
    initializeFavoriteButtons();
    
    // منع اختيار "Location" في select
    const findSelect = document.getElementById('find-select');
    if (findSelect) {
        findSelect.addEventListener('change', function() {
            if (this.value === 'Achat') {
                this.value = 'Location';
            }
        });
    }
});

// دالة تهيئة أزرار المفضلة
function initializeFavoriteButtons() {
    const favoriteButtons = document.querySelectorAll('.favorite-btn');
    
    favoriteButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            handleFavoriteClick(this);
        });
    });
}

// دالة معالجة النقر على المفضلة
function handleFavoriteClick(button) {
    // التحقق من تسجيل الدخول (يجب تمرير هذه المعلومة من PHP)
  
const isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>; // يجب تعيين هذا من PHP
    
    if (!isLoggedIn) {
        if(confirm('يجب تسجيل الدخول أولاً لتتمكن من إضافة العقارات إلى المفضلة. هل تريد الانتقال إلى صفحة تسجيل الدخول؟')) {
            window.location.href = 'login.php?redirect=' + encodeURIComponent(window.location.href);
        }
        return;
    }
    
    const propertyId = button.getAttribute('data-property-id');
    if (!propertyId) {
        console.error('Property ID not found');
        return;
    }
    
    toggleFavorite(propertyId, button);
}

// دالة تبديل حالة المفضلة
function toggleFavorite(propertyId, button) {
    const icon = button.querySelector('i');
    if (!icon) return;
    
    const isFavorite = icon.classList.contains('fas');
    const action = isFavorite ? 'remove' : 'add';
    
    // تغيير الشكل مؤقتاً قبل الإرسال
    button.style.opacity = '0.5';
    button.style.pointerEvents = 'none';
    
    fetch('handle_favorite.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: `property_id=${encodeURIComponent(propertyId)}&action=${encodeURIComponent(action)}`
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.status === 'success') {
            if (isFavorite) {
                icon.classList.replace('fas', 'far');
                button.title = 'إضافة إلى المفضلة';
                showNotification('تم إزالة العقار من المفضلة', 'success');
            } else {
                icon.classList.replace('far', 'fas');
                button.title = 'إزالة من المفضلة';
                showNotification('تم إضافة العقار إلى المفضلة', 'success');
            }
        } else {
            showNotification(data.message || 'حدث خطأ أثناء العملية', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('حدث خطأ في الاتصال بالخادم', 'error');
    })
    .finally(() => {
        // إعادة تفعيل الزر
        button.style.opacity = '1';
        button.style.pointerEvents = 'auto';
    });
}

// دالة عرض الإشعارات
function showNotification(message, type = 'info') {
    // إنشاء عنصر الإشعار
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    
    // إضافة الأنماط
    Object.assign(notification.style, {
        position: 'fixed',
        top: '20px',
        right: '20px',
        padding: '15px 20px',
        borderRadius: '6px',
        color: 'white',
        fontSize: '14px',
        zIndex: '10000',
        opacity: '0',
        transform: 'translateX(100%)',
        transition: 'all 0.3s ease',
        maxWidth: '300px',
        wordWrap: 'break-word'
    });
    
    // تحديد لون الإشعار حسب النوع
    switch(type) {
        case 'success':
            notification.style.backgroundColor = '#27ae60';
            break;
        case 'error':
            notification.style.backgroundColor = '#e74c3c';
            break;
        default:
            notification.style.backgroundColor = '#3498db';
    }
    
    // إضافة الإشعار إلى الصفحة
    document.body.appendChild(notification);
    
    // إظهار الإشعار
    setTimeout(() => {
        notification.style.opacity = '1';
        notification.style.transform = 'translateX(0)';
    }, 100);
    
    // إخفاء الإشعار بعد 3 ثوان
    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, 3000);
}
</script>
</html> 