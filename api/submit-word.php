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

if (strlen($word) > 50) {
    echo json_encode(['success' => false, 'message' => 'Word is too long (max 50 characters)']);
    exit;
}

if (!preg_match('/^[a-zA-Z0-9\s\-]+$/', $word)) {
    echo json_encode(['success' => false, 'message' => 'Only letters, numbers, spaces and hyphens allowed']);
    exit;
}

try {
    // Check if poll exists and is active
    $stmt = $pdo->prepare("SELECT id FROM polls WHERE id = ? AND is_active = 1");
    $stmt->execute([$poll_id]);
    
    if (!$stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Poll not found or inactive']);
        exit;
    }
    
    // Check if word already exists
    $stmt = $pdo->prepare("SELECT id, vote_count FROM words WHERE poll_id = ? AND word_text = ?");
    $stmt->execute([$poll_id, $word]);
    $existingWord = $stmt->fetch();
    
    if ($existingWord) {
        // Increment vote count
        $new_count = $existingWord['vote_count'] + 1;
        $stmt = $pdo->prepare("UPDATE words SET vote_count = ? WHERE id = ?");
        $stmt->execute([$new_count, $existingWord['id']]);
        $message = 'Vote added to existing word';
    } else {
        // Insert new word
        $stmt = $pdo->prepare("INSERT INTO words (poll_id, word_text, vote_count) VALUES (?, ?, 1)");
        $stmt->execute([$poll_id, $word]);
        $message = 'New word added';
    }
    
    // Track vote (optional)
    $session_id = session_id();
    $ip = getClientIP();
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    echo json_encode([
        'success' => true,
        'message' => $message,
        'word' => $word
    ]);
} catch(Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Failed to submit word: ' . $e->getMessage()]);
}
?>