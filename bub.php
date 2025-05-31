<?php  
ob_start(); 
require_once 'db_connect.php'; 
include 'header.php';  

// التحقق من تسجيل الدخول 
if (!isset($_SESSION['user_id'])) {     
    header('Location: login.php');     
    exit(); 
}  

// التحقق من وجود معاملات التعديل
$is_editing = false;
$ad_data = null;

if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $is_editing = true;
    
    // استعلام لجلب بيانات الإعلان للتعديل
    $edit_query = "SELECT * FROM annonces WHERE id = ? AND user_id = ?";
    $stmt_edit = $conn->prepare($edit_query);
    $stmt_edit->bind_param("ii", $edit_id, $_SESSION['user_id']);
    $stmt_edit->execute();
    $result = $stmt_edit->get_result();
    
    if ($result->num_rows > 0) {
        $ad_data = $result->fetch_assoc();
        // فك تشفير الصور إذا كانت موجودة
        if (!empty($ad_data['images'])) {
            $ad_data['images_array'] = json_decode($ad_data['images'], true);
        }
    } else {
        // إذا لم يتم العثور على الإعلان أو المستخدم غير مخول
        $_SESSION['error_message'] = "الإعلان غير موجود أو غير مصرح لك بتعديله.";
        header("Location: compte.php");
        exit();
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {     
    // جمع بيانات النموذج     
    $titre = $_POST['titre'] ?? '';     
    $type_annonce = $_POST['transaction_type'];     
    $type_bien = $_POST['property_type'];     
    $wilaya = $_POST['wilaya'];     
    $commune = $_POST['commune'];     
    $adresse = $_POST['address'];     
    $surface = $_POST['surface'];     
    $chambres = $_POST['bedrooms'];     
    $salles_de_bain = $_POST['bathrooms'];     
    $description = $_POST['description'];     
    $prix = $_POST['price'];     
    $periode_location = $_POST['price_period'] ?? NULL;     
    $contact_nom = $_POST['contact_name'];     
    $contact_tel = $_POST['contact_phone'];     
    $contact_email = $_POST['contact_email'];     
    $user_id = $_SESSION['user_id'];          
    
    // معالجة الصور     
    $upload_dir = 'uploads/properties/';     
    if (!is_dir($upload_dir)) {         
        mkdir($upload_dir, 0777, true);     
    }      
    
    $image_urls = [];
    
    // في حالة التعديل، احتفظ بالصور القديمة إذا لم يتم رفع صور جديدة
    if ($is_editing && isset($_POST['ad_id'])) {
        $existing_images = json_decode($ad_data['images'] ?? '[]', true);
        $image_urls = $existing_images;
    }
    
    // معالجة الصور المرفوعة الجديدة
    if (!empty($_FILES['images']['name'][0])) {         
        $image_urls = []; // مسح الصور القديمة إذا تم رفع صور جديدة
        foreach ($_FILES['images']['tmp_name'] as $key => $tmp_name) {             
            $file_name = time() . '_' . basename($_FILES['images']['name'][$key]);             
            $target_file = $upload_dir . $file_name;                          
            if (move_uploaded_file($tmp_name, $target_file)) {                 
                $image_urls[] = $target_file;             
            }         
        }     
    }      
    
    // إذا لم يتم رفع أي صور ولا توجد صور سابقة، استخدم الصورة الافتراضية     
    if (empty($image_urls)) {         
        $image_urls[] = './assets/images/default-property.jpg';     
    }      
    
    // تحويل مصفوفة الصور إلى JSON     
    $images_json = json_encode($image_urls);          
    
    // التحقق من نوع العملية (إدراج أو تحديث)
    if ($is_editing && isset($_POST['ad_id'])) {
        // تحديث الإعلان الموجود
        $ad_id = intval($_POST['ad_id']);
        
        $stmt = $conn->prepare("UPDATE annonces SET 
            titre = ?, type_annonce = ?, type_bien = ?, wilaya = ?, commune = ?, adresse = ?,
            surface = ?, chambres = ?, salles_de_bain = ?, description = ?, prix = ?,
            periode_location = ?, contact_nom = ?, contact_tel = ?, contact_email = ?, images = ?
            WHERE id = ? AND user_id = ?");
            
        $stmt->bind_param("ssssssiiisdssssii",
            $titre, $type_annonce, $type_bien, $wilaya, $commune, $adresse,
            $surface, $chambres, $salles_de_bain, $description, $prix,
            $periode_location, $contact_nom, $contact_tel, $contact_email,
            $images_json, $ad_id, $user_id);
            
        if ($stmt->execute()) {
            $_SESSION['success_message'] = "تم تحديث إعلانك بنجاح.";
            header('Location: compte.php');
            exit();
        } else {
            $_SESSION['error_message'] = "حدث خطأ أثناء تحديث الإعلان. يرجى المحاولة مرة أخرى.";
        }
    } else {
        // إدخال إعلان جديد
        $stmt = $conn->prepare("INSERT INTO annonces (         
            titre, type_annonce, type_bien, wilaya, commune, adresse,          
            surface, chambres, salles_de_bain, description, prix,          
            periode_location, contact_nom, contact_tel, contact_email,          
            images, status, date_publication, user_id     
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW(), ?)");          
        
        $stmt->bind_param("ssssssiiisdsssssi",          
            $titre, $type_annonce, $type_bien, $wilaya, $commune, $adresse,         
            $surface, $chambres, $salles_de_bain, $description, $prix,         
            $periode_location, $contact_nom, $contact_tel, $contact_email,         
            $images_json, $user_id);          
        
        if ($stmt->execute()) {         
            $_SESSION['success_message'] = "تم إرسال إعلانك بنجاح وهو في انتظار الموافقة من قبل الإدارة.";         
            header('Location: compte.php');         
            exit();     
        } else {         
            $_SESSION['error_message'] = "حدث خطأ أثناء حفظ الإعلان. يرجى المحاولة مرة أخرى.";
        }
    }
} 
?>
<!DOCTYPE html>
<html lang="fr" dir="trl">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Homeverse - Publier une annonce</title>
  
  <style>
    /* Styles principaux */
    :root {
      --primary: #00ae69;
      --primary-dark: #008a54;
      --text-dark: #333;
      --text-medium: #555;
      --text-light: #777;
      --border-color: #e0e0e0;
      --bg-light: #f5f7fa;
      --warning-color: #e74c3c;
      --success-color: #27ae60;
    }
    
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      line-height: 1.6;
      color: var(--text-dark);
      background-color: #f9f9f9;
    }
    
    /* Conteneur du formulaire */
    .property-form-section {
      padding: 40px 20px;
      background-color: #f9f9f9;
    }
    
    .property-form-wrapper {
      max-width: 900px;
      margin: 0 auto;
      background: #fff;
      padding: 30px;
      border-radius: 8px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.05);
      border: 1px solid var(--border-color);
    }
    
    .section-title {
      font-size: 24px;
      font-weight: 700;
      margin-bottom: 15px;
      color: var(--text-dark);
    }
    
    .section-subtitle {
      color: var(--text-medium);
      margin-bottom: 30px;
      font-size: 16px;
    }
    
    /* Sections du formulaire */
    .property-form {
      display: flex;
      flex-direction: column;
      gap: 30px;
    }
    
    .form-section {
      padding: 25px;
      border-radius: 8px;
      background: var(--bg-light);
      border-right: 4px solid var(--primary);
    }
    
    .form-section-title {
      font-size: 18px;
      font-weight: 600;
      margin-bottom: 20px;
      color: var(--text-dark);
    }
    
    /* Éléments du formulaire */
    .form-group {
      margin-bottom: 20px;
    }
    
    .form-group label {
      display: block;
      margin-bottom: 8px;
      font-weight: 500;
      color: var(--text-medium);
    }
    
    .form-group input,
    .form-group select,
    .form-group textarea {
      width: 100%;
      padding: 12px 15px;
      border: 1px solid var(--border-color);
      border-radius: 4px;
      font-size: 16px;
      transition: all 0.3s;
      direction: rtl;
    }
    
    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
      border-color: var(--primary);
      outline: none;
      box-shadow: 0 0 0 3px rgba(0, 174, 105, 0.1);
    }
    
    .form-group textarea {
      min-height: 120px;
      resize: vertical;
    }
    
    /* Mise en page du formulaire */
    .form-row {
      display: flex;
      gap: 20px;
    }
    
    .form-row .form-group {
      flex: 1;
    }
    
    /* Grille des caractéristiques */
    .features-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
      gap: 15px;
    }
    
    .feature-checkbox {
      display: flex;
      align-items: center;
      gap: 8px;
    }
    
    .feature-checkbox input {
      width: auto;
    }
    
    /* Section de téléchargement d'images */
    .image-upload-section {
      border: 2px dashed var(--border-color);
      border-radius: 8px;
      padding: 20px;
      text-align: center;
      transition: all 0.3s;
    }
    
    .image-upload-section:hover {
      border-color: var(--primary);
      background-color: rgba(0, 174, 105, 0.05);
    }
    
    .image-upload-section.dragover {
      border-color: var(--primary);
      background-color: rgba(0, 174, 105, 0.1);
    }
    
    .upload-icon {
      font-size: 48px;
      color: var(--primary);
      margin-bottom: 15px;
    }
    
    .upload-text {
      font-size: 16px;
      color: var(--text-medium);
      margin-bottom: 10px;
    }
    
    .upload-note {
      font-size: 14px;
      color: var(--text-light);
    }
    
    /* Aperçu des images */
    .image-preview {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
      gap: 15px;
      margin-top: 20px;
    }
    
    .preview-item {
      position: relative;
      width: 120px;
      height: 120px;
      border-radius: 8px;
      overflow: hidden;
      border: 2px solid var(--border-color);
      transition: all 0.3s;
    }
    
    .preview-item:hover {
      border-color: var(--primary);
    }
    
    .preview-item.main-image {
      border-color: var(--primary);
    }
    
    .preview-item img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }
    
    .image-overlay {
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: rgba(0, 0, 0, 0.5);
      display: flex;
      align-items: center;
      justify-content: center;
      opacity: 0;
      transition: opacity 0.3s;
    }
    
    .preview-item:hover .image-overlay {
      opacity: 1;
    }
    
    .image-number {
      position: absolute;
      top: 8px;
      right: 8px;
      background: var(--primary);
      color: white;
      width: 24px;
      height: 24px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 12px;
      font-weight: bold;
    }
    
    .main-badge {
      position: absolute;
      bottom: 8px;
      left: 8px;
      background: var(--success-color);
      color: white;
      padding: 4px 8px;
      border-radius: 4px;
      font-size: 10px;
      font-weight: bold;
    }
    
    .remove-image {
      background: var(--warning-color);
      color: white;
      border: none;
      width: 30px;
      height: 30px;
      border-radius: 50%;
      cursor: pointer;
      font-size: 16px;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    
    /* Messages de statut */
    .status-message {
      padding: 12px 16px;
      border-radius: 4px;
      margin-bottom: 20px;
      font-weight: 500;
    }
    
    .status-message.warning {
      background-color: #fff3cd;
      border: 1px solid #ffeaa7;
      color: #856404;
    }
    
    .status-message.success {
      background-color: #d4edda;
      border: 1px solid #c3e6cb;
      color: #155724;
    }
    
    .status-message.error {
      background-color: #f8d7da;
      border: 1px solid #f5c6cb;
      color: #721c24;
    }
    
    /* Bouton de soumission */
    .form-submit {
      text-align: center;
      margin-top: 30px;
    }
    
    .form-submit .btn {
      background-color: var(--primary);
      color: white;
      border: none;
      padding: 15px 40px;
      border-radius: 4px;
      font-size: 16px;
      font-weight: 600;
      cursor: pointer;
      transition: background-color 0.3s;
    }
    
    .form-submit .btn:hover {
      background-color: var(--primary-dark);
    }
    
    .form-submit .btn:disabled {
      background-color: #ccc;
      cursor: not-allowed;
    }
    
    .form-note {
      font-size: 14px;
      color: var(--text-light);
      margin-top: 10px;
    }
    
    /* Conception responsive */
    @media (max-width: 768px) {
      .property-form-wrapper {
        padding: 20px;
      }
      
      .form-row {
        flex-direction: column;
        gap: 0;
      }
      
      .features-grid {
        grid-template-columns: repeat(2, 1fr);
      }
      
      .image-preview {
        grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
      }
      
      .preview-item {
        width: 100px;
        height: 100px;
      }
    }
    
    @media (max-width: 480px) {
      .property-form-section {
        padding: 20px 10px;
      }
      
      .form-section {
        padding: 20px 15px;
      }
      
      .section-title {
        font-size: 20px;
      }
      
      .features-grid {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>

<body>
  <main>
    <section class="property-form-section">
      <div class="property-form-wrapper">
        <h2 class="section-title">
            <?php echo $is_editing ? 'تعديل الإعلان العقاري' : 'Publier une annonce immobilière'; ?>
        </h2>
        <p class="section-subtitle">
            <?php echo $is_editing ? 'قم بتعديل تفاصيل العقار' : 'Remplissez les détails de votre propriété'; ?>
        </p>

       <form id="property-form" method="POST" enctype="multipart/form-data" action="<?php echo $_SERVER['PHP_SELF'] . ($is_editing ? '?edit=' . $ad_data['id'] : ''); ?>">
          
          <?php if ($is_editing): ?>
            <input type="hidden" name="ad_id" value="<?php echo $ad_data['id']; ?>">
          <?php endif; ?>
          
          <!-- Section Type d'annonce -->
          <div class="form-section">
            <h3 class="form-section-title">1. Type d'annonce</h3>
            <div class="form-group">
              <label for="property-type">Type de propriété*</label>
              <select id="property-type" name="property_type" required>
                <option value="">Sélectionnez le type de propriété</option>
                <option value="Appartement" <?php echo ($is_editing && $ad_data['type_bien'] == 'Appartement') ? 'selected' : ''; ?>>Appartement</option>
                <option value="Maison" <?php echo ($is_editing && $ad_data['type_bien'] == 'Maison') ? 'selected' : ''; ?>>Maison</option>
                <option value="Villa" <?php echo ($is_editing && $ad_data['type_bien'] == 'Villa') ? 'selected' : ''; ?>>Villa</option>
                <option value="Studio" <?php echo ($is_editing && $ad_data['type_bien'] == 'Studio') ? 'selected' : ''; ?>>Studio</option>
                <option value="Terrain" <?php echo ($is_editing && $ad_data['type_bien'] == 'Terrain') ? 'selected' : ''; ?>>Terrain</option>
                <option value="Bureau" <?php echo ($is_editing && $ad_data['type_bien'] == 'Bureau') ? 'selected' : ''; ?>>Bureau</option>
                <option value="Local commercial" <?php echo ($is_editing && $ad_data['type_bien'] == 'Local commercial') ? 'selected' : ''; ?>>Local commercial</option>
              </select>
            </div>

            <div class="form-group">
              <label for="transaction-type">Type de transaction*</label>
              <select id="transaction-type" name="transaction_type" required>
                <option value="">Sélectionnez le type de transaction</option>
                <option value="Vente" <?php echo ($is_editing && $ad_data['type_annonce'] == 'Vente') ? 'selected' : ''; ?>>À vendre</option>
                <option value="Location" <?php echo ($is_editing && $ad_data['type_annonce'] == 'Location') ? 'selected' : ''; ?>>À louer</option>
              </select>
            </div>
          </div>

          <!-- Section Localisation -->
          <div class="form-section">
            <h3 class="form-section-title">2. Localisation</h3>
            <div class="form-group">
              <label for="wilaya">Wilaya*</label>
              <select id="wilaya" name="wilaya" required>
                <option value="">Sélectionnez la wilaya</option>
                <option value="Alger" <?php echo ($is_editing && $ad_data['wilaya'] == 'Alger') ? 'selected' : ''; ?>>Alger</option>
                <option value="Oran" <?php echo ($is_editing && $ad_data['wilaya'] == 'Oran') ? 'selected' : ''; ?>>Oran</option>
                <option value="Constantine" <?php echo ($is_editing && $ad_data['wilaya'] == 'Constantine') ? 'selected' : ''; ?>>Constantine</option>
                <option value="Annaba" <?php echo ($is_editing && $ad_data['wilaya'] == 'Annaba') ? 'selected' : ''; ?>>Annaba</option>
                <!-- Ajouter d'autres wilayas -->
              </select>
            </div>

            <div class="form-group">
              <label for="commune">Commune*</label>
              <input type="text" id="commune" name="commune" placeholder="Nom de la commune" 
                     value="<?php echo $is_editing ? htmlspecialchars($ad_data['commune'] ?? '') : ''; ?>" required>
            </div>

            <div class="form-group">
              <label for="address">Adresse complète</label>
              <textarea id="address" name="address" rows="2" placeholder="Rue, numéro, etc..."><?php echo $is_editing ? htmlspecialchars($ad_data['adresse'] ?? '') : ''; ?></textarea>
            </div>
          </div>

          <!-- Section Détails de la propriété -->
          <div class="form-section">
            <h3 class="form-section-title">3. Détails de la propriété</h3>
            <div class="form-row">
              <div class="form-group">
                <label for="surface">Superficie (m²)*</label>
                <input type="number" id="surface" name="surface" min="1" 
                       value="<?php echo $is_editing ? $ad_data['surface'] ?? '' : ''; ?>" required>
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label for="bedrooms">Chambres*</label>
                <select id="bedrooms" name="bedrooms" required>
                  <option value="">Sélectionnez</option>
                  <option value="0" <?php echo ($is_editing && $ad_data['chambres'] == '0') ? 'selected' : ''; ?>>Studio</option>
                  <option value="1" <?php echo ($is_editing && $ad_data['chambres'] == '1') ? 'selected' : ''; ?>>1</option>
                  <option value="2" <?php echo ($is_editing && $ad_data['chambres'] == '2') ? 'selected' : ''; ?>>2</option>
                  <option value="3" <?php echo ($is_editing && $ad_data['chambres'] == '3') ? 'selected' : ''; ?>>3</option>
                  <option value="4" <?php echo ($is_editing && $ad_data['chambres'] == '4') ? 'selected' : ''; ?>>4</option>
                  <option value="5+" <?php echo ($is_editing && $ad_data['chambres'] == '5+') ? 'selected' : ''; ?>>5+</option>
                </select>
              </div>

              <div class="form-group">
                <label for="bathrooms">Salles de bain*</label>
                <select id="bathrooms" name="bathrooms" required>
                  <option value="">Sélectionnez</option>
                  <option value="1" <?php echo ($is_editing && $ad_data['salles_de_bain'] == '1') ? 'selected' : ''; ?>>1</option>
                  <option value="2" <?php echo ($is_editing && $ad_data['salles_de_bain'] == '2') ? 'selected' : ''; ?>>2</option>
                  <option value="3" <?php echo ($is_editing && $ad_data['salles_de_bain'] == '3') ? 'selected' : ''; ?>>3</option>
                  <option value="4+" <?php echo ($is_editing && $ad_data['salles_de_bain'] == '4+') ? 'selected' : ''; ?>>4+</option>
                </select>
              </div>
            </div>

            <div class="form-group">
              <label for="description">Description détaillée*</label>
              <textarea id="description" name="description" rows="5" required placeholder="Rédigez une description détaillée de la propriété..."><?php echo $is_editing ? htmlspecialchars($ad_data['description'] ?? '') : ''; ?></textarea>
            </div>
          </div>

          <!-- Section Prix -->
          <div class="form-section">
            <h3 class="form-section-title">4. Prix</h3>
            <div class="form-group">
              <label for="price">Prix*</label>
              <input type="number" id="price" name="price" min="0" 
                     value="<?php echo $is_editing ? $ad_data['prix'] ?? '' : ''; ?>" required>
            </div>

            <div class="form-group" id="price-period-container" style="display: <?php echo ($is_editing && $ad_data['type_annonce'] == 'Location') ? 'block' : 'none'; ?>;">
              <label for="price-period">Période de location</label>
              <select id="price-period" name="price_period">
                <option value="month" <?php echo ($is_editing && $ad_data['periode_location'] == 'month') ? 'selected' : ''; ?>>Mensuel</option>
                <option value="day" <?php echo ($is_editing && $ad_data['periode_location'] == 'day') ? 'selected' : ''; ?>>Journalier</option>
                <option value="year" <?php echo ($is_editing && $ad_data['periode_location'] == 'year') ? 'selected' : ''; ?>>Annuel</option>
              </select>
            </div>
          </div>

          <!-- Section Caractéristiques -->
          <div class="form-section">
            <h3 class="form-section-title">5. Caractéristiques</h3>
            <div class="features-grid">
              <div class="feature-checkbox">
                <input type="checkbox" id="feature-balcony" name="features[]" value="Balcon">
                <label for="feature-balcony">Balcon</label>
              </div>
              <div class="feature-checkbox">
                <input type="checkbox" id="feature-parking" name="features[]" value="Parking">
                <label for="feature-parking">Parking</label>
              </div>
              <div class="feature-checkbox">
                <input type="checkbox" id="feature-garden" name="features[]" value="Jardin">
                <label for="feature-garden">Jardin</label>
              </div>
              <div class="feature-checkbox">
                <input type="checkbox" id="feature-pool" name="features[]" value="Piscine">
                <label for="feature-pool">Piscine</label>
              </div>
              <div class="feature-checkbox">
                <input type="checkbox" id="feature-elevator" name="features[]" value="Ascenseur">
                <label for="feature-elevator">Ascenseur</label>
              </div>
              <div class="feature-checkbox">
                <input type="checkbox" id="feature-furnished" name="features[]" value="Meublé">
                <label for="feature-furnished">Meublé</label>
              </div>
              <div class="feature-checkbox">
                <input type="checkbox" id="feature-ac" name="features[]" value="Climatisation">
                <label for="feature-ac">Climatisation</label>
              </div>
              <div class="feature-checkbox">
                <input type="checkbox" id="feature-heating" name="features[]" value="Chauffage">
                <label for="feature-heating">Chauffage</label>
              </div>
            </div>
          </div>

          <!-- Section Images -->
          <div class="form-section">
            <h3 class="form-section-title">6. Images</h3>
            
            <?php if ($is_editing && !empty($ad_data['images_array'])): ?>
            <div class="existing-images">
              <h4>الصور الحالية:</h4>
              <div class="existing-images-preview">
                <?php foreach ($ad_data['images_array'] as $image): ?>
                  <div class="existing-image-item">
                    <img src="<?php echo htmlspecialchars($image); ?>" alt="صورة موجودة">
                  </div>
                <?php endforeach; ?>
              </div>
              <p class="note">ملاحظة: إذا قمت برفع صور جديدة، ستحل محل الصور الحالية</p>
            </div>
            <?php endif; ?>
            
            <div class="form-group">
              <div class="image-upload-section" id="image-upload-area">
                <div class="upload-icon">📷</div>
                <div class="upload-text">Glissez-déposez les images ici ou cliquez pour sélectionner</div>
                <div class="upload-note">
                  <?php if (!$is_editing): ?>
                    Minimum 3 images, maximum 10 images
                  <?php else: ?>
                    اختياري - اترك فارغاً للاحتفاظ بالصور الحالية
                  <?php endif; ?>
                </div>
                <input type="file" id="property-images" name="images[]" multiple accept="image/*" style="display: none;" <?php echo !$is_editing ? 'required' : ''; ?>>
              </div>
              
              <div id="image-status" class="status-message" style="display: none;"></div>
              <div class="image-preview" id="image-preview"></div>
            </div>
          </div>

          <!-- Section Informations de contact -->
          <div class="form-section">
            <h3 class="form-section-title">7. Informations de contact</h3>
            <div class="form-group">
              <label for="contact-name">Nom*</label>
              <input type="text" id="contact-name" name="contact_name" 
                     value="<?php echo $is_editing ? htmlspecialchars($ad_data['contact_nom'] ?? '') : ''; ?>" required>
            </div>

            <div class="form-group">
              <label for="contact-phone">Téléphone*</label>
              <input type="tel" id="contact-phone" name="contact_phone" 
                     value="<?php echo $is_editing ? htmlspecialchars($ad_data['contact_tel'] ?? '') : ''; ?>" required>
            </div>

            <div class="form-group">
              <label for="contact-email">Email*</label>
              <input type="email" id="contact-email" name="contact_email" 
                     value="<?php echo $is_editing ? htmlspecialchars($ad_data['contact_email'] ?? '') : ''; ?>" required>
            </div>
          </div>

          <div class="form-submit">
            <button type="submit" class="btn" id="submit-btn">
              <?php echo $is_editing ? 'تحديث الإعلان' : 'Publier l\'annonce'; ?>
            </button>
            <?php if ($is_editing): ?>
              <a href="compte.php" class="btn btn-secondary">إلغاء</a>
            <?php endif; ?>
            <p class="form-note">En publiant cette annonce, vous acceptez nos <a href="#">Conditions d'utilisation</a></p>
          </div>
        </form>
      </div>
    </section>

<script>
    // Variables globales
    let selectedImages = [];
    let imageCounter = 0;
    const isEditing = <?php echo $is_editing ? 'true' : 'false'; ?>;

    document.addEventListener('DOMContentLoaded', function() {
        // عناصر DOM
        const imageInput = document.getElementById('property-images');
        const imagePreview = document.getElementById('image-preview');
        const imageUploadArea = document.getElementById('image-upload-area');
        const imageStatus = document.getElementById('image-status');
        const propertyForm = document.getElementById('property-form');
        const submitBtn = document.getElementById('submit-btn');
        
        // إدارة نوع المعاملة (بيع/إيجار)
        const transactionType = document.getElementById('transaction-type');
        const pricePeriodContainer = document.getElementById('price-period-container');
        
        transactionType.addEventListener('change', function() {
            pricePeriodContainer.style.display = this.value === 'Location' ? 'block' : 'none';
        });

        // إعدادات تحميل الصور
        function setupImageUpload() {
            // حدث النقر على منطقة التحميل
            imageUploadArea.addEventListener('click', function() {
                imageInput.click();
            });

            // أحداث السحب والإفلات
            imageUploadArea.addEventListener('dragover', function(e) {
                e.preventDefault();
                this.classList.add('dragover');
            });

            imageUploadArea.addEventListener('dragleave', function(e) {
                e.preventDefault();
                this.classList.remove('dragover');
            });

            imageUploadArea.addEventListener('drop', function(e) {
                e.preventDefault();
                this.classList.remove('dragover');
                handleFileSelection(Array.from(e.dataTransfer.files));
            });

            // حدث اختيار الملفات
            imageInput.addEventListener('change', function() {
                if (this.files && this.files.length > 0) {
                    handleFileSelection(Array.from(this.files));
                }
            });
        }

        // معالجة الملفات المختارة
        function handleFileSelection(files) {
            const imageFiles = files.filter(file => file.type.match('image.*'));
            
            if (imageFiles.length === 0) {
                showImageStatus('Veuillez sélectionner des fichiers image valides', 'error');
                return;
            }

            if (selectedImages.length + imageFiles.length > 10) {
                showImageStatus(`Maximum 10 images autorisées (${selectedImages.length} déjà ajoutées)`, 'warning');
                return;
            }

            imageFiles.forEach((file, index) => {
                const imageId = ++imageCounter;
                selectedImages.push({
                    id: imageId,
                    file: file,
                    isMain: selectedImages.length === 0 && index === 0
                });
                createImagePreview(imageId, file);
            });

            updateImageStatus();
            updateSubmitButton();
        }

        // إنشاء معاينة الصورة
        function createImagePreview(imageId, file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const previewItem = document.createElement('div');
                previewItem.className = 'preview-item';
                previewItem.dataset.imageId = imageId;
                previewItem.innerHTML = `
                    <img src="${e.target.result}" alt="Preview">
                    <div class="image-overlay">
                        <button type="button" class="remove-image" onclick="removeImage(${imageId})">×</button>
                    </div>
                `;
                imagePreview.appendChild(previewItem);
            };
            reader.readAsDataURL(file);
        }

        // إزالة الصورة
        window.removeImage = function(imageId) {
            selectedImages = selectedImages.filter(img => img.id !== imageId);
            document.querySelector(`.preview-item[data-image-id="${imageId}"]`).remove();
            updateImageStatus();
            updateSubmitButton();
        };

        // تحديث حالة الصور
        function updateImageStatus() {
            const count = selectedImages.length;
            if (!isEditing && count < 3) {
                showImageStatus(`Ajoutez ${3 - count} images supplémentaires`, 'warning');
            } else {
                showImageStatus('Prêt à publier', 'success');
            }
        }

        // تحديث زر الإرسال
        function updateSubmitButton() {
            if (isEditing) {
                // في وضع التعديل، الإرسال مسموح حتى بدون صور جديدة
                submitBtn.disabled = false;
            } else {
                // في وضع الإنشاء، يجب أن يكون هناك 3 صور على الأقل
                submitBtn.disabled = selectedImages.length < 3;
            }
        }

        // عرض رسالة الحالة
        function showImageStatus(message, type) {
            imageStatus.textContent = message;
            imageStatus.className = `status-message ${type}`;
            imageStatus.style.display = 'block';
        }

        // إرسال النموذج
        propertyForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (!isEditing && selectedImages.length < 3) {
                showImageStatus('Minimum 3 images requises', 'error');
                return;
            }

            const formData = new FormData(propertyForm);
            
            // إضافة الصور المحددة إلى FormData
            selectedImages.forEach((image, index) => {
                formData.append('images[]', image.file);
            });

            // إرسال البيانات
            submitBtn.disabled = true;
            submitBtn.textContent = isEditing ? 'جاري التحديث...' : 'جاري النشر...';

            fetch(propertyForm.action, {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                // إعادة توجيه أو معالجة الاستجابة
                window.location.href = 'compte.php';
            })
            .catch(error => {
                console.error('Error:', error);
                showImageStatus('حدث خطأ أثناء الإرسال', 'error');
                submitBtn.disabled = false;
                submitBtn.textContent = isEditing ? 'تحديث الإعلان' : 'Publier l\'annonce';
            });
        });

        // تهيئة تحميل الصور
        setupImageUpload();
        
        // تحديث زر الإرسال في البداية
        updateSubmitButton();
    });
</script>
  </body>
</html>