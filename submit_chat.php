<?php
header('Content-Type: application/json');
require_once __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
    exit;
}

// Support both JSON content type and standard POST form fields
$input = json_decode(file_get_contents('php://input'), true);
$name = trim($input['name'] ?? $_POST['name'] ?? '');
$email = trim($input['email'] ?? $_POST['email'] ?? '');
$message = trim($input['message'] ?? $_POST['message'] ?? '');

if (empty($name) || empty($email) || empty($message)) {
    echo json_encode(['success' => false, 'error' => 'Please fill in all fields.']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'error' => 'Please enter a valid email address.']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO contact_messages (name, email, subject, message) VALUES (:name, :email, 'Chat Widget Lead', :message)");
    $stmt->execute([
        'name' => $name,
        'email' => $email,
        'message' => $message
    ]);
    echo json_encode(['success' => true]);
} catch (\PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error. Please try again.']);
}
?>
