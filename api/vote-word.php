<?php
require_once '../config/database.php';
session_start();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$poll_id = trim($_POST['poll_id'] ?? '');
$word = strtolower(trim($_POST['word'] ?? ''));

if (empty($poll_id) || empty($word)) {
    echo json_encode(['success' => false, 'message' => 'Poll ID and word are required']);
    exit;
}

try {
    // Find the word
    $stmt = $pdo->prepare("SELECT id, vote_count FROM words WHERE poll_id = ? AND word_text = ?");
    $stmt->execute([$poll_id, $word]);
    $existingWord = $stmt->fetch();
    
    if (!$existingWord) {
        echo json_encode(['success' => false, 'message' => 'Word not found in this poll']);
        exit;
    }
    
    // Increment vote count
    $new_count = $existingWord['vote_count'] + 1;
    $stmt = $pdo->prepare("UPDATE words SET vote_count = ? WHERE id = ?");
    $stmt->execute([$new_count, $existingWord['id']]);
    
    echo json_encode([
        'success' => true,
        'message' => 'Vote counted!',
        'word' => $word,
        'vote_count' => $new_count
    ]);
} catch(Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Failed to vote: ' . $e->getMessage()]);
}
?>