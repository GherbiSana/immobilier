<?php
session_start();
header('Content-Type: application/json');

$response = [
    'loggedIn' => false,
    'user' => null
];

if (isset($_SESSION['user']['id'])) {
    $uid = $_SESSION['user']['id'];
    // …
}
{
    // الاتصال بقاعدة البيانات
    $conn = new mysqli("localhost", "root", "", "immobilier");
    
    if ($conn->connect_error) {
        die(json_encode(['error' => 'Database connection failed']));
    }

    $stmt = $conn->prepare("SELECT user_id, name, email, user_type FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $response = [
            'loggedIn' => true,
            'user' => [
                'user_id' => $user['user_id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'user_type' => $user['user_type']
            ]
        ];
    }
    
    $stmt->close();
    $conn->close();
}

echo json_encode($response);
?>