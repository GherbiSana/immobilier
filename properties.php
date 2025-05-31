<?php
// properties.php - Fonctions pour gérer les propriétés immobilières

require_once 'db_connect.php'; // Fichier de connexion à la base de données

/**
 * Récupère toutes les annonces approuvées
 * @param int|null $limit Nombre maximum d'annonces à retourner
 * @return array Liste des annonces
 */
function getAllProperties($limit = null) {
    global $conn;
    
    $sql = "SELECT * FROM annonces WHERE status = 'approved' ORDER BY date_publication DESC";
    
    if ($limit) {
        $sql .= " LIMIT " . (int)$limit;
    }
    
    $result = mysqli_query($conn, $sql);
    
    if (!$result) {
        die("Erreur de requête: " . mysqli_error($conn));
    }
    
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

/**
 * Récupère les annonces par type (Vente/Location)
 * @param string $type Type d'annonce ('Vente' ou 'Location')
 * @param int|null $limit Nombre maximum d'annonces
 * @return array Liste des annonces
 */
function getPropertiesByType($type, $limit = null) {
    global $conn;
    
    $type = mysqli_real_escape_string($conn, $type);
    $sql = "SELECT * FROM annonces WHERE type_annonce = '$type' AND status = 'approved' ORDER BY date_publication DESC";
    
    if ($limit) {
        $sql .= " LIMIT " . (int)$limit;
    }
    
    $result = mysqli_query($conn, $sql);
    
    if (!$result) {
        die("Erreur de requête: " . mysqli_error($conn));
    }
    
    $properties = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $properties[] = $row;
    }
    
    return $properties;
}

/**
 * Récupère une annonce par son ID
 * @param int $id ID de l'annonce
 * @return array|null Données de l'annonce ou null si non trouvée
 */
function getPropertyById($id) {
    global $conn;
    
    $id = (int)$id;
    $sql = "SELECT * FROM annonces WHERE id = $id AND status = 'approved'";
    $result = mysqli_query($conn, $sql);
    
    if (!$result) {
        die("Erreur de requête: " . mysqli_error($conn));
    }
    
    return mysqli_fetch_assoc($result);
}

/**
 * Formate le prix pour l'affichage
 * @param float $price Prix à formater
 * @param string|null $periode Période de location (optionnel)
 * @return string Prix formaté
 */
function formatPrice($price, $periode = null) {
    $formatted = number_format($price, 0, ',', ' ') . ' €';
    
    if ($periode) {
        $formatted .= '/' . ucfirst($periode);
    }
    
    return $formatted;
}

/**
 * Génère un badge pour le type d'annonce
 * @param string $type Type d'annonce
 * @return string Code HTML du badge
 */

/**
 * Traite les images multiples d'une annonce
 * @param string $images_json JSON des images
 * @return array Liste des images
 */
function getPropertyImages($images_json) {
    if (empty($images_json)) {
        return [];
    }
    
    $images = json_decode($images_json, true);
    return is_array($images) ? $images : [];
}
?>