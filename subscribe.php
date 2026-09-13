<?php
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if (empty($email)) {
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL) . '?subscribed=empty');
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL) . '?subscribed=invalid');
        exit;
    }

    // Default name if not provided
    if (empty($name)) {
        $name = 'Valued Subscriber';
    }

    try {
        // Insert or ignore if duplicate email
        $stmt = $pdo->prepare("
            INSERT INTO subscribers (name, email) 
            VALUES (:name, :email)
            ON DUPLICATE KEY UPDATE name = :name_update
        ");
        $stmt->execute([
            'name' => $name,
            'email' => $email,
            'name_update' => $name
        ]);

        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL) . '?subscribed=success');
        exit;
    } catch (\PDOException $e) {
        header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? BASE_URL) . '?subscribed=error');
        exit;
    }
} else {
    header('Location: ' . BASE_URL);
    exit;
}
?>
