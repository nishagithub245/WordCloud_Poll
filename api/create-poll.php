<?php
require_once '../config/database.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$question = trim($_POST['question'] ?? '');

if (empty($question)) {
    echo json_encode(['success' => false, 'message' => 'Question is required']);
    exit;
}

$poll_id = generatePollId();
$admin_token = generateAdminToken();

try {
    $stmt = $pdo->prepare("INSERT INTO polls (id, question, admin_token) VALUES (?, ?, ?)");
    $stmt->execute([$poll_id, $question, $admin_token]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Poll created successfully',
        'poll_id' => $poll_id,
        'admin_token' => $admin_token
    ]);
} catch(Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Failed to create poll: ' . $e->getMessage()]);
}
?>