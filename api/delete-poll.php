<?php
require_once '../config/database.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$poll_id = trim($_POST['poll_id'] ?? '');
$token = trim($_POST['token'] ?? '');

if (empty($poll_id) || empty($token)) {
    echo json_encode(['success' => false, 'message' => 'Poll ID and token are required']);
    exit;
}

try {
    // Verify admin token
    $stmt = $pdo->prepare("SELECT id FROM polls WHERE id = ? AND admin_token = ?");
    $stmt->execute([$poll_id, $token]);
    
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Invalid poll ID or admin token']);
        exit;
    }
    
    // Delete poll (cascade delete will handle words)
    $stmt = $pdo->prepare("DELETE FROM polls WHERE id = ?");
    $stmt->execute([$poll_id]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Poll deleted successfully'
    ]);
} catch(Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Failed to delete poll: ' . $e->getMessage()]);
}
?>