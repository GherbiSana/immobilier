<?php
include('db_connection.php'); // تأكد من الاتصال بقاعدة البيانات

$find = mysqli_real_escape_string($conn, $_GET['find'] ?? '');
$wilaya = mysqli_real_escape_string($conn, $_GET['wilaya'] ?? '');
$type = mysqli_real_escape_string($conn, $_GET['property_type'] ?? '');
$min_price = mysqli_real_escape_string($conn, $_GET['min_price'] ?? '');
$max_price = mysqli_real_escape_string($conn, $_GET['max_price'] ?? '');

// تحديد الصفحة الحالية وعدد النتائج في كل صفحة
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$results_per_page = 10; // عدد النتائج في كل صفحة
$start_from = ($page - 1) * $results_per_page;

// SQL للاستعلام عن النتائج بناءً على المعايير المحددة
$query = "SELECT * FROM properties WHERE 1";
if ($find) $query .= " AND find LIKE '%$find%'";
if ($wilaya) $query .= " AND wilaya_id = '$wilaya'";
if ($type) $query .= " AND property_type = '$type'";
if ($min_price) $query .= " AND price >= $min_price";
if ($max_price) $query .= " AND price <= $max_price";

// إضافة التحديد لعدد النتائج
$query .= " LIMIT $start_from, $results_per_page";
$result = mysqli_query($conn, $query);

// عدد النتائج الكلي لتحديد عدد الصفحات
$count_query = "SELECT COUNT(*) FROM properties WHERE 1";
if ($find) $count_query .= " AND find LIKE '%$find%'";
if ($wilaya) $count_query .= " AND wilaya_id = '$wilaya'";
if ($type) $count_query .= " AND property_type = '$type'";
if ($min_price) $count_query .= " AND price >= $min_price";
if ($max_price) $count_query .= " AND price <= $max_price";
$count_result = mysqli_query($conn, $count_query);
$total_rows = mysqli_fetch_array($count_result)[0];
$total_pages = ceil($total_rows / $results_per_page);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Résultats de recherche</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <main>
    <div class="container">
      <h3>Résultats de recherche</h3>
      <div class="properties-grid">
        <?php if (mysqli_num_rows($result) > 0) : ?>
          <?php while ($row = mysqli_fetch_assoc($result)) : ?>
            <div class="property-card">
              <div class="property-image-container">
                <img src="<?= $row['image'] ?>" alt="Image" class="property-image">
              </div>
              <div class="property-content">
                <div class="property-price"><?= $row['price'] ?> DZD</div>
                <div class="property-title"><?= $row['title'] ?></div>
                <div class="property-location"><?= $row['location'] ?></div>
                <div class="property-features">
                  <div><?= $row['beds'] ?> chambres</div>
                  <div><?= $row['baths'] ?> salles de bain</div>
                </div>
              </div>
            </div>
          <?php endwhile; ?>
        <?php else : ?>
          <p>Aucune propriété trouvée pour votre recherche.</p>
        <?php endif; ?>
      </div>

      <!-- Pagination -->
      <div class="pagination">
        <?php if ($page > 1) : ?>
          <a href="?page=<?= $page - 1 ?>&find=<?= $find ?>&wilaya=<?= $wilaya ?>&property_type=<?= $type ?>&min_price=<?= $min_price ?>&max_price=<?= $max_price ?>">Précédent</a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $total_pages; $i++) : ?>
          <a href="?page=<?= $i ?>&find=<?= $find ?>&wilaya=<?= $wilaya ?>&property_type=<?= $type ?>&min_price=<?= $min_price ?>&max_price=<?= $max_price ?>" class="<?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>

        <?php if ($page < $total_pages) : ?>
          <a href="?page=<?= $page + 1 ?>&find=<?= $find ?>&wilaya=<?= $wilaya ?>&property_type=<?= $type ?>&min_price=<?= $min_price ?>&max_price=<?= $max_price ?>">Suivant</a>
        <?php endif; ?>
      </div>
    </div>
  </main>
</body>
</html>

