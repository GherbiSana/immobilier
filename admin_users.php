<?php
ob_start();
require_once 'db_connect.php';

// التحقق البسيط من صلاحيات المشرف
session_start();
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

// معالجة طلبات الإدارة بدون أي تحقق
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_user':
                // إضافة مستخدم جديد بدون تحقق
                $stmt = $conn->prepare("INSERT INTO users 
                    (name, email, password, Numéro_de_téléphone, user_type) 
                    VALUES (?, ?, ?, ?, ?)");
                
                $stmt->bind_param("sssss", 
                    $_POST['name'],
                    $_POST['email'],
                    password_hash($_POST['password'], PASSWORD_DEFAULT),
                    $_POST['phone'],
                    $_POST['user_type']
                );
                
                if ($stmt->execute()) {
                    $_SESSION['message'] = "Utilisateur ajouté!";
                } else {
                    $_SESSION['error'] = "Erreur: " . $conn->error;
                }
                break;
                
            case 'delete_user':
                // حذف مستخدم بدون تحقق إضافي
                $stmt = $conn->prepare("DELETE FROM users WHERE user_id=?");
                $stmt->bind_param("i", $_POST['user_id']);
                $stmt->execute();
                $_SESSION['message'] = "Utilisateur supprimé!";
                break;
        }
        header("Location: admin_users.php");
        exit;
    }
}

// جلب جميع المستخدمين
$users = $conn->query("SELECT * FROM users ORDER BY user_id DESC")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion des Utilisateurs</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* نفس النمط السابق */
    </style>
</head>
<body>
    <div class="container">
        <h1><i class="fas fa-users-cog"></i> Gestion des Utilisateurs</h1>
        
        <?php if (isset($_SESSION['message'])): ?>
            <div class="message success"><?= $_SESSION['message'] ?></div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>
          <a href="compte.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Retour au compte
            </a>
        <button class="btn btn-success" onclick="openModal()">
            <i class="fas fa-user-plus"></i> Ajouter un Utilisateur
        </button>
        
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Téléphone</th>
                    <th>Type</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td><?= $user['user_id'] ?></td>
                    <td><?= htmlspecialchars($user['name']) ?></td>
                    <td><?= htmlspecialchars($user['email']) ?></td>
                    <td><?= htmlspecialchars($user['Numéro_de_téléphone']) ?></td>
                    <td><?= $user['user_type'] ?></td>
                    <td>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="action" value="delete_user">
                            <input type="hidden" name="user_id" value="<?= $user['user_id'] ?>">
                            <button type="submit" class="btn btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cet utilisateur?')">
                                <i class="fas fa-trash-alt"></i> Supprimer
                            </button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Modal pour إضافة المستخدمين -->
    <div id="userModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>Ajouter un Utilisateur</h2>
            <form method="POST" id="userForm">
                <input type="hidden" name="action" value="add_user">
                
                <div class="form-group">
                    <label for="name">Nom complet:</label>
                    <input type="text" id="name" name="name" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="phone">Numéro de téléphone:</label>
                    <input type="text" id="phone" name="phone" required maxlength="10">
                </div>
                
                <div class="form-group">
                    <label for="password">Mot de passe:</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <div class="form-group">
                    <label for="user_type">Type d'utilisateur:</label>
                    <select id="user_type" name="user_type" required>
                        <option value="User">Utilisateur normal</option>
                        <option value="Admin">Administrateur</option>
                    </select>
                </div>
                
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> Enregistrer
                </button>
            </form>
        </div>
    </div>
    
    <script>
        function openModal() {
            document.getElementById('userModal').style.display = 'block';
        }
        
        function closeModal() {
            document.getElementById('userModal').style.display = 'none';
        }
        
        // إغلاق النافذة عند النقر خارجها
        window.onclick = function(event) {
            const modal = document.getElementById('userModal');
            if (event.target === modal) {
                closeModal();
            }
        }
    </script>
     <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #333;
            border-bottom: 2px solid #eee;
            padding-bottom: 10px;
        }
        .message {
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        tr:hover {
            background-color: #f5f5f5;
        }
        .btn {
            padding: 6px 12px;
            border-radius: 4px;
            text-decoration: none;
            color: white;
            cursor: pointer;
            border: none;
            font-size: 14px;
        }
        .btn-primary {
            background-color: #007bff;
        }
        .btn-danger {
            background-color: #dc3545;
        }
        .btn-success {
            background-color: #28a745;
        }
        .btn i {
            margin-right: 5px;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 1;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.4);
        }
        .modal-content {
            background-color: #fefefe;
            margin: 10% auto;
            padding: 20px;
            border: 1px solid #888;
            width: 50%;
            border-radius: 8px;
        }
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            box-sizing: border-box;
        }
            
        .
            
        
        
        
    </style>
</body>
</html>