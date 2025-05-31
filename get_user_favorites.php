<?php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header('HTTP/1.1 401 Unauthorized');
    exit(json_encode(['error' => 'غير مسموح']));
}

$user_id = $_SESSION['user_id'];
$query = "SELECT a.* FROM annonces a 
          JOIN favorites f ON a.id = f.property_id 
          WHERE f.user_id = ? 
          ORDER BY f.date_added DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$favorites = [];
while ($row = $result->fetch_assoc()) {
    $favorites[] = $row;
}

header('Content-Type: application/json');
echo json_encode($favorites);
?>