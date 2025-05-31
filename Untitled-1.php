<?php
// Ligne 1: Inclure d'abord le fichier des fonctions
require_once __DIR__ . '/properties.php';

// Ensuite inclure le header
include 'header.php';

// Maintenant vous pouvez utiliser les fonctions
$latestProperties = getAllProperties(6);


// ... le reste de votre code ...
?>
<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Homeverse - Trouvez la maison de vos rêves</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <!-- 
    - favicon
  -->
  <link rel="shortcut icon" href="./favicon.svg" type="image/svg+xml">
  <link rel="stylesheet" href="style.css">
  <!-- bn
    - lien CSS personnalisé
  -->
  <link rel="stylesheet" href="./assets/css/style.css">
  <link href="https://unpkg.com/boxicons@2.1.2/css/boxicons.min.css" rel="stylesheet">

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

  <main>
    <article>
     
      
        <!-- 
        - #SECTION PRINCIPALE
      -->
     
    
        <div class="hero-section">
            <div class="hero-text">
                <h1>Trouvez la propriété de vos rêves</h1>
            </div>
            <div class="search-container">
    
              <div class="tabs">
                <div class="tab active" id="rent-btn" onclick="showSearch('rent')">Location</div>
                <div class="tab" id="buy-btn" onclick="showSearch('buy')">Achat</div>
                <div class="tab" id="new-projects-btn" onclick="showSearch('new-projects')">Nouveaux projets</div>
            </div>
            
            <div id="search-rent" class="search-bar active">
            
            <form id="search-form" method="GET" action="http://localhost/immobilier/REnt.php">
                
              <select name="wilaya"  id="wilaya-select">
                <option value="">Sélectionnez une wilaya</option>
                <option value="1">Adrar (1)</option>
                <option value="2">Chlef (2)</option>
                <option value="3">Laghouat (3)</option>
                <option value="4">Oum El Bouaghi (4)</option>
                <option value="5">Batna (5)</option>
                <option value="6">Béjaïa (6)</option>
                <option value="7">Biskra (7)</option>
                <option value="8">Béchar (8)</option>
                <option value="9">Blida (9)</option>
                <option value="10">Bouira (10)</option>
                <option value="11">Tamanrasset (11)</option>
                <option value="12">Tébessa (12)</option>
                <option value="13">Tlemcen (13)</option>
                <option value="14">Tiaret (14)</option>
                <option value="15">Tizi Ouzou (15)</option>
                <option value="16">Alger (16)</option>
                <option value="17">Djelfa (17)</option>
                <option value="18">Jijel (18)</option>
                <option value="19">Sétif (19)</option>
                <option value="20">Saïda (20)</option>
                <option value="21">Skikda (21)</option>
                <option value="22">Sidi Bel Abbès (22)</option>
                <option value="23">Annaba (23)</option>
                <option value="24">Guelma (24)</option>
                <option value="25">Constantine (25)</option>
                <option value="26">Médéa (26)</option>
                <option value="27">Mostaganem (27)</option>
                <option value="28">M'Sila (28)</option>
                <option value="29">Mascara (29)</option>
                <option value="30">Ouargla (30)</option>
                <option value="31">Oran (31)</option>
                <option value="32">El Bayadh (32)</option>
                <option value="33">Illizi (33)</option>
                <option value="34">Bordj Bou Arréridj (34)</option>
                <option value="35">Boumerdès (35)</option>
                <option value="36">El Tarf (36)</option>
                <option value="37">Tindouf (37)</option>
                <option value="38">Tissemsilt (38)</option>
                <option value="39">El Oued (39)</option>
                <option value="40">Khenchela (40)</option>
                <option value="41">Souk Ahras (41)</option>
                <option value="42">Tipaza (42)</option>
                <option value="43">Mila (43)</option>
                <option value="44">Aïn Defla (44)</option>
                <option value="45">Naâma (45)</option>
                <option value="46">Aïn Témouchent (46)</option>
                <option value="47">Ghardaïa (47)</option>
                <option value="48">Relizane (48)</option>
            </select>
          
            <select name="property_type" id="property-type-select">
              <option value="">Type de propriété</option>
              <option value="Appartement">Appartement</option>
              <option value="Maison">Maison</option>
              <option value="Studio">Studio</option>
              <option value="Villa">Villa</option>
          </select>
           <!-- استبدال حقول الغرف والحمامات المخفية بـ select مباشر -->
    <select name="chambres" id="chambres-select" class="form-select">
        <option value="">Nombre de chambres</option>
        <option value="1">1</option>
        <option value="2">2</option>
        <option value="3">3</option>
        <option value="4">4</option>
        <option value="5">5</option>
        <option value="6+">6+</option>
    </select>
         
          
          <input type="number" name="min_price" id="min-price-rent" placeholder="Prix minimum">
          <input type="number" name="max_price" id="max-price-rent" placeholder="Prix maximum">
          
          <button type="submit">Rechercher</button>
            
              </form>
               
       
            </div>
             
            
            

       

              <div id="search-buy" class="search-bar">
             
                <form id="search-form" method="GET" action="http://localhost/immobilier/ACHert.php">
                    
                  <select name="wilaya" id="wilaya-select">
                    <option value="">Sélectionnez une wilaya</option>
                    <option value="1">Adrar (1)</option>
                    <option value="2">Chlef (2)</option>
                    <option value="3">Laghouat (3)</option>
                    <option value="4">Oum El Bouaghi (4)</option>
                    <option value="5">Batna (5)</option>
                    <option value="6">Béjaïa (6)</option>
                    <option value="7">Biskra (7)</option>
                    <option value="8">Béchar (8)</option>
                    <option value="9">Blida (9)</option>
                    <option value="10">Bouira (10)</option>
                    <option value="11">Tamanrasset (11)</option>
                    <option value="12">Tébessa (12)</option>
                    <option value="13">Tlemcen (13)</option>
                    <option value="14">Tiaret (14)</option>
                    <option value="15">Tizi Ouzou (15)</option>
                    <option value="16">Alger (16)</option>
                    <option value="17">Djelfa (17)</option>
                    <option value="18">Jijel (18)</option>
                    <option value="19">Sétif (19)</option>
                    <option value="20">Saïda (20)</option>
                    <option value="21">Skikda (21)</option>
                    <option value="22">Sidi Bel Abbès (22)</option>
                    <option value="23">Annaba (23)</option>
                    <option value="24">Guelma (24)</option>
                    <option value="25">Constantine (25)</option>
                    <option value="26">Médéa (26)</option>
                    <option value="27">Mostaganem (27)</option>
                    <option value="28">M'Sila (28)</option>
                    <option value="29">Mascara (29)</option>
                    <option value="30">Ouargla (30)</option>
                    <option value="31">Oran (31)</option>
                    <option value="32">El Bayadh (32)</option>
                    <option value="33">Illizi (33)</option>
                    <option value="34">Bordj Bou Arréridj (34)</option>
                    <option value="35">Boumerdès (35)</option>
                    <option value="36">El Tarf (36)</option>
                    <option value="37">Tindouf (37)</option>
                    <option value="38">Tissemsilt (38)</option>
                    <option value="39">El Oued (39)</option>
                    <option value="40">Khenchela (40)</option>
                    <option value="41">Souk Ahras (41)</option>
                    <option value="42">Tipaza (42)</option>
                    <option value="43">Mila (43)</option>
                    <option value="44">Aïn Defla (44)</option>
                    <option value="45">Naâma (45)</option>
                    <option value="46">Aïn Témouchent (46)</option>
                    <option value="47">Ghardaïa (47)</option>
                    <option value="48">Relizane (48)</option>
                </select>
              
                <select name="property_type" id="property-type-select">
                  <option value="">Type de propriété</option>
                  <option value="Appartement">Appartement</option>
                  <option value="Maison">Maison</option>
                  <option value="Studio">Studio</option>
                  <option value="Villa">Villa</option>
              </select>
              
              <select name="chambres" id="chambres-select" class="form-select">
        <option value="">Nombre de chambres</option>
        <option value="1">1</option>
        <option value="2">2</option>
        <option value="3">3</option>
        <option value="4">4</option>
        <option value="5">5</option>
        <option value="6+">6+</option>
    </select>
              <input type="number" name="min_price" id="min-price-input" placeholder="Prix minimum">
              <input type="number" name="max_price" id="max-price-input" placeholder="Prix maximum">
              
              <button type="submit">Rechercher</button>
                
            </form>

                   
           
                
          </div>
          
               
       
</div>
          
           
         
          
          

          <div id="search-new-projects" class="search-bar">
            <form id="new-projects-form" action="search-results.html" method="GET">
              <select name="project_type" id="project-type-select">
                <option value="">Type de projet</option>
                <option value="Residential">Résidentiel</option>
                <option value="Commercial">Commercial</option>
                <option value="Mixed">Mixte</option>
                <option value="Industrial">Industriel</option>
              </select>
          
              <select name="project_phase" id="project-phase-select">
                <option value="">Phase du projet</option>
                <option value="planning">En planification</option>
                <option value="construction">En construction</option>
                <option value="final-phase">Phase finale</option>
                <option value="completed">Terminé</option>
              </select>
          
              <input type="number" name="min_price" placeholder="Prix min (DA)" id="project-min-price">
          
              <input type="number" name="max_price" placeholder="Prix max (DA)" id="project-max-price">
          
              <select name="delivery_date" id="delivery-date-select">
                <option value="">Date de livraison</option>
                
                <option value="2025">2025</option>
                <option value="2026">2026</option>
                <option value="2027+">2027 et plus</option>
              </select>
          
              <button type="submit">Rechercher</button>
            </form>
          </div>
          
        </div>
    </div>
    <!-- 
        - #À PROPOS
      -->
      <section class="about" id="about">
        <div class="container">
      
          <figure class="about-banner">
            <img src="photo2.png" alt="Maison en Algérie">
          </figure>
      
          <div class="about-content">
      
            <p class="section-subtitle">À propos de nous</p>
      
            <h2 class="h2 section-title">Plateforme pour la vente et la location de biens immobiliers en Algérie</h2>
      
            <p class="about-text">
              Nous mettons à votre disposition une plateforme pour publier et consulter des annonces immobilières facilement. Les propriétaires peuvent proposer leurs biens à la vente ou à la location, et les visiteurs peuvent rechercher selon la wilaya, le type de bien ou le budget.
            </p>
      
            <ul class="about-list">
      
              <li class="about-item">
                <div class="about-item-icon">
                  <ion-icon name="home-outline"></ion-icon>
                </div>
                <p class="about-item-text">Annonces immobilières variées</p>
              </li>
      
              <li class="about-item">
                <div class="about-item-icon">
                  <ion-icon name="location-outline"></ion-icon>
                </div>
                <p class="about-item-text">Biens disponibles dans toutes les wilayas d’Algérie</p>
              </li>
      
              <li class="about-item">
                <div class="about-item-icon">
                  <ion-icon name="add-circle-outline"></ion-icon>
                </div>
                <p class="about-item-text">Ajout d’annonce rapide et simple</p>
              </li>
      
              <li class="about-item">
                <div class="about-item-icon">
                  <ion-icon name="eye-outline"></ion-icon>
                </div>
                <p class="about-item-text">Consultation gratuite sans inscription</p>
              </li>
      
            </ul>
      
            <p class="callout">
              "Inscrivez-vous, publiez votre annonce ou trouvez le bien qui vous convient facilement"
            </p>
      
            <a href="#service" class="btn">Nos services</a>
      
          </div>
      
        </div>
      </section>
      
      <section class="service" id="service">
        <div class="container">
      
          <p class="section-subtitle">Nos Services</p>
          <h2 class="h2 section-title">Ce que vous pouvez faire sur notre plateforme</h2>
      
          <ul class="service-list">
      
            <li>
              <div class="service-card">
                <div class="card-icon">
                  <img src="photo3.png" alt="Icône achat">
                </div>
                <h3 class="h3 card-title">
                  <a href="http://localhost/immobilier/ACHert.php"">Acheter un bien</a>
                </h3>
                <p class="card-text">
                  Parcourez des centaines d’annonces de maisons, appartements et terrains à vendre dans toute l’Algérie.
                </p>
                <a href="http://localhost/immobilier/ACHert.php" class="card-link">
                  <span>Voir les annonces</span>
                  <ion-icon name="arrow-forward-outline"></ion-icon>
                </a>
              </div>
            </li>
      
            <li>
              <div class="service-card">
                <div class="card-icon">
                  <img src="photo5.png" alt="Icône location">
                </div>
                <h3 class="h3 card-title">
                  <a href="">Louer un bien</a>
                </h3>
                <p class="card-text">
                  Découvrez les offres de location de biens immobiliers mises en ligne par des propriétaires partout en Algérie.
                </p>
                <a href="http://localhost/immobilier/REnt.php" class=" class="card-link">
                  <span>Explorer les offres</span>
                  <ion-icon name="arrow-forward-outline"></ion-icon>
                </a>
              </div>
            </li>
      
            <li>
              <div class="service-card">
                <div class="card-icon">
                  <img src="photo4.png" alt="Icône publier">
                </div>
                <h3 class="h3 card-title">
                  <a href="http://localhost/immobilier/bub.php">Publier une annonce</a>
                </h3>
                <p class="card-text">
                  Vous avez un bien à vendre ou à louer ? Créez facilement votre annonce et touchez des milliers de visiteurs.
                </p>
                <a href="http://localhost/immobilier/bub.php" class="card-link">
                  <span>Ajouter une annonce</span>
                  <ion-icon name="arrow-forward-outline"></ion-icon>
                </a>
              </div>
            </li>
      
          </ul>
      
        </div>
</section>
<?php
require 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Traitement des critères de recherche
    $wilaya = $_GET['wilaya'] ?? '';
    $property_type = $_GET['property_type'] ?? '';
    $min_price = $_GET['min_price'] ?? '';
    $max_price = $_GET['max_price'] ?? '';
    $chambres = $_GET['chambres'] ?? '';
    $transaction_type = $_GET['find'] ?? 'Achat';

    // Requête SQL de base
    $sql = "SELECT * FROM annonces WHERE type_annonce = 'Vente'";
    $conditions = [];

    // Ajout des conditions de recherche
    if (!empty($wilaya)) {
        $conditions[] = "wilaya = '" . $conn->real_escape_string($wilaya) . "'";
    }
    
    if (!empty($property_type)) {
        $conditions[] = "type_bien = '" . $conn->real_escape_string($property_type) . "'";
    }
    
    if (!empty($min_price) && is_numeric($min_price)) {
        $conditions[] = "prix >= " . intval($min_price);
    }
    
    if (!empty($max_price) && is_numeric($max_price)) {
        $conditions[] = "prix <= " . intval($max_price);
    }
    
    if (!empty($chambres)) {
        if ($chambres === '6+') {
            $conditions[] = "(chambres >= 6 AND chambres IS NOT NULL)";
        } elseif (is_numeric($chambres)) {
            $conditions[] = "(chambres = " . intval($chambres) . " AND chambres IS NOT NULL)";
        }
    }

    if (!empty($conditions)) {
        $sql .= " AND " . implode(" AND ", $conditions);
    }

    $sql .= " ORDER BY date_publication DESC";
    $result = $conn->query($sql);

   function traiterImage($image_path, $user_id = null) {
    // المسارات الأساسية
    $base_upload_dir = 'uploads/';
    $properties_dir = $base_upload_dir . 'properties/';
    $default_image = 'assets/images/default-property.jpg';
    
    // إذا كان المسار فارغاً
    if (empty($image_path)) {
        return $default_image;
    }
    
    // إذا كان رابطاً خارجياً (http أو https)
    if (filter_var($image_path, FILTER_VALIDATE_URL)) {
        return $image_path;
    }
    
    // تنظيف المسار من أي محاولات تجاوز للمجلدات
    $clean_path = str_replace(['../', './', '..\\', '.\\'], '', $image_path);
    
    // قائمة بالمسارات المحتملة للبحث عن الصورة
    $possible_paths = [
        $clean_path, // المسار كما هو
        $properties_dir . basename($clean_path), // في مجلد العقارات الرئيسي
    ];
    
    // إذا كان هناك معرّف مستخدم، نضيف مسارات إضافية
    if ($user_id !== null) {
        $possible_paths[] = $properties_dir . $user_id . '/' . basename($clean_path);
        $possible_paths[] = $base_upload_dir . 'users/' . $user_id . '/' . basename($clean_path);
    }
    
    // البحث عن أول مسار موجود فعلياً
    foreach ($possible_paths as $path) {
        if (file_exists($path)) {
            return $path;
        }
    }
    
    // إذا لم يتم العثور على الصورة في أي مسار
    return $default_image;
}
?>

<section class="property" id="property">
    <div class="container">
        <p class="section-subtitle">Résultats de recherche</p>
        <h2 class="h2 section-title">Annonces disponibles</h2>

        <ul class="property-list has-scrollbar">
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <?php
                    $id = $row['id'] ?? 'N/A';
                    $titre = $row['titre'] ?? 'Sans titre';
                    $type_bien = $row['type_bien'] ?? 'Type inconnu';
                    $prix = isset($row['prix']) ? number_format($row['prix'], 0, ',', ' ') : 'N/A';
                    $wilaya_display = $row['wilaya'] ?? 'Wilaya inconnue';
                    $commune = $row['commune'] ?? '';
                    $chambres_display = $row['chambres'] ?? 'N/A';
                    $salles_de_bain = $row['salles_de_bain'] ?? 'N/A';
                    $surface = $row['surface'] ?? 'N/A';
                    $description = $row['description'] ?? '';
                    $date_poste = $row['date_publication'] ?? 'Date inconnue';
                    
                    // Traitement des images
                    $images = [];
                    if (!empty($row['images'])) {
                        $images = json_decode($row['images'], true);
                    }
                    $image_principale = !empty($row['image_url']) ? traiterImage($row['image_url']) : 
                                      (!empty($images) ? traiterImage($images[0]) : 'assets/images/default-property.jpg');
                    ?>
                    
                    <li>
                        <div class="property-card">
                            <figure class="card-banner">
                                <a href="property-details.php?id=<?php echo $id; ?>">
                                    <img src="<?php echo $image_principale; ?>" 
                                         alt="<?php echo htmlspecialchars($titre); ?>" 
                                         class="w-100"
                                         onerror="this.src='assets/images/default-property.jpg'">
                                </a>

                                <div class="banner-actions">
                                    <button class="banner-actions-btn">
                                        <ion-icon name="location"></ion-icon>
                                        <address><?php echo htmlspecialchars($commune . ', ' . $wilaya_display); ?></address>
                                    </button>

                                    <?php if (!empty($images) && count($images) > 0): ?>
                                    <button class="banner-actions-btn">
                                        <ion-icon name="camera"></ion-icon>
                                        <span><?php echo count($images); ?></span>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </figure>

                            <div class="card-content">
                                <div class="card-price">
                                    <strong><?php echo $prix; ?> DA</strong>
                                </div>

                                <h3 class="h3 card-title">
                                    <a href="property-details.php?id=<?php echo $id; ?>">
                                        <?php echo htmlspecialchars($titre); ?>
                                    </a>
                                </h3>

                                <p class="card-text">
                                    <?php echo htmlspecialchars(substr($description, 0, 100)) . '...'; ?>
                                </p>

                                <ul class="card-list">
                                    <?php if (!empty($chambres_display) && $chambres_display != 'N/A'): ?>
                                    <li class="card-item">
                                        <strong><?php echo $chambres_display; ?></strong>
                                        <ion-icon name="bed-outline"></ion-icon>
                                        <span>Chambres</span>
                                    </li>
                                    <?php endif; ?>

                                    <?php if (!empty($salles_de_bain) && $salles_de_bain != 'N/A'): ?>
                                    <li class="card-item">
                                        <strong><?php echo $salles_de_bain; ?></strong>
                                        <ion-icon name="man-outline"></ion-icon>
                                        <span>Salles de bain</span>
                                    </li>
                                    <?php endif; ?>

                                    <?php if (!empty($surface) && $surface != 'N/A'): ?>
                                    <li class="card-item">
                                        <strong><?php echo $surface; ?></strong>
                                        <ion-icon name="square-outline"></ion-icon>
                                        <span>m²</span>
                                    </li>
                                    <?php endif; ?>
                                </ul>
                            </div>

                            <div class="card-footer">
                                <div class="property-date">Publié le <?php echo $date_poste; ?></div>
                            </div>
                        </div>
                    </li>
                <?php endwhile; ?>
            <?php else: ?>
                <li>
                    <div class="no-properties">
                        <i class="fas fa-home"></i>
                        <h4>Aucun résultat</h4>
                        <p>Aucune annonce ne correspond à vos critères de recherche.</p>
                    </div>
                </li>
            <?php endif; ?>
        </ul>
    </div>
</section>

<?php
    $conn->close();
}
?>
    </article>
  </main>
  <!-- 
    - #PIED DE PAGE
  -->
  <?php include('footer.php'); ?>


  <!-- 
    - ionicon link
  -->
  <script type="module" src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.esm.js"></script>
  <script nomodule src="https://unpkg.com/ionicons@5.5.2/dist/ionicons/ionicons.js"></script>

</body>

  <script>
   
  
  function showSearch(type) {
    // إخفاء جميع حقول البحث
    document.querySelectorAll('.search-bar').forEach(function(searchBar) {
        searchBar.classList.remove('active');
    });
    
    // إظهار الحقل المحدد
    document.getElementById(`search-${type}`).classList.add('active');
    
    // تحديث الأزرار النشطة
    document.querySelectorAll('.tab').forEach(function(tab) {
        tab.classList.remove('active');
    });
    document.getElementById(`${type}-btn`).classList.add('active');
}
  
  
  
  document.addEventListener("DOMContentLoaded", function() {
    // تفعيل علامة التبويب "الإيجار" بشكل افتراضي
    showSearch('rent');
});

function showSearch(type) {
    document.querySelectorAll('.search-bar').forEach(searchBar => {
        searchBar.classList.remove('active');
    });
    document.getElementById(`search-${type}`).classList.add('active');
    
    document.querySelectorAll('.tab').forEach(tab => {
        tab.classList.remove('active');
    });
    document.getElementById(`${type}-btn`).classList.add('active');
}
  
































































// property-card.js
document.addEventListener('DOMContentLoaded', function() {
    // إضافة الإعجاب
    document.querySelectorAll('.favorite-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const propertyId = this.dataset.propertyId;
            toggleFavorite(propertyId, this);
        });
    });
});

function toggleFavorite(propertyId, button) {
    fetch('handle_favorite.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            property_id: propertyId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const icon = button.querySelector('ion-icon');
            icon.name = data.is_favorite ? 'heart' : 'heart-outline';
        }
    });
}
</script>

</html> 