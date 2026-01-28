<?php
require_once '../config/database.php';
header('Content-Type: application/json');

$poll_id = $_GET['poll_id'] ?? '';

if (empty($poll_id)) {
    echo json_encode(['success' => false, 'message' => 'Poll ID is required']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id, question, created_at FROM polls WHERE id = ?");
    $stmt->execute([$poll_id]);
    $poll = $stmt->fetch();
    
    if (!$poll) {
        echo json_encode(['success' => false, 'message' => 'Poll not found']);
        exit;
    }
    
    // Get statistics if requested
    $stats = [];
    if (isset($_GET['stats']) && $_GET['stats'] === 'true') {
        $stmt = $pdo->prepare("SELECT COUNT(*) as word_count, SUM(vote_count) as total_votes FROM words WHERE poll_id = ?");
        $stmt->execute([$poll_id]);
        $stats = $stmt->fetch();
    }
    
    echo json_encode([
        'success' => true,
        'poll' => $poll,
        'stats' => $stats
    ]);
} catch(Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>