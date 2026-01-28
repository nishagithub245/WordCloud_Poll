<?php
require_once '../config/database.php';
header('Content-Type: application/json');

$poll_id = $_GET['poll_id'] ?? '';

if (empty($poll_id)) {
    echo json_encode(['success' => false, 'message' => 'Poll ID is required']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT word_text, vote_count FROM words WHERE poll_id = ? ORDER BY vote_count DESC");
    $stmt->execute([$poll_id]);
    $words = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'words' => $words,
        'count' => count($words)
    ]);
} catch(Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error', 'words' => []]);
}
?>