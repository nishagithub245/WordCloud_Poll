<?php
require_once __DIR__ . '/../config/database.php';

// Check if poll exists and is active
function pollExists($poll_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT id FROM polls WHERE id = ? AND is_active = 1");
    $stmt->execute([$poll_id]);
    return $stmt->fetch() !== false;
}

// Get poll details
function getPollDetails($poll_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT id, question, created_at FROM polls WHERE id = ?");
    $stmt->execute([$poll_id]);
    return $stmt->fetch();
}

// Check if admin
function isAdmin($poll_id, $token) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT id FROM polls WHERE id = ? AND admin_token = ?");
    $stmt->execute([$poll_id, $token]);
    return $stmt->fetch() !== false;
}

// Get all words for poll
function getPollWords($poll_id) {
    global $pdo;
    $stmt = $pdo->prepare("SELECT word_text, vote_count FROM words WHERE poll_id = ? ORDER BY vote_count DESC");
    $stmt->execute([$poll_id]);
    return $stmt->fetchAll();
}

// Get poll statistics
function getPollStats($poll_id) {
    global $pdo;
    
    $stats = [
        'total_words' => 0,
        'total_votes' => 0,
        'unique_voters' => 0
    ];
    
    // Get word stats
    $stmt = $pdo->prepare("SELECT COUNT(*) as count, SUM(vote_count) as votes FROM words WHERE poll_id = ?");
    $stmt->execute([$poll_id]);
    $result = $stmt->fetch();
    
    if ($result) {
        $stats['total_words'] = $result['count'] ?? 0;
        $stats['total_votes'] = $result['votes'] ?? 0;
    }
    
    return $stats;
}
?>