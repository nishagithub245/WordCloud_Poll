<?php
require_once '../config/database.php';

$poll_id = $_GET['poll_id'] ?? '';
$token = $_GET['token'] ?? '';

if (empty($poll_id) || empty($token)) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Poll ID and token are required']);
    exit;
}

try {
    // Verify admin token
    $stmt = $pdo->prepare("SELECT question FROM polls WHERE id = ? AND admin_token = ?");
    $stmt->execute([$poll_id, $token]);
    $poll = $stmt->fetch();
    
    if (!$poll) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid poll ID or admin token']);
        exit;
    }
    
    // Get all words
    $stmt = $pdo->prepare("SELECT word_text, vote_count, created_at FROM words WHERE poll_id = ? ORDER BY vote_count DESC");
    $stmt->execute([$poll_id]);
    $words = $stmt->fetchAll();
    
    // Generate CSV
    $csv = "Word Cloud Poll Export\n";
    $csv .= "Poll ID: $poll_id\n";
    $csv .= "Question: " . $poll['question'] . "\n";
    $csv .= "Export Date: " . date('Y-m-d H:i:s') . "\n";
    $csv .= "Total Words: " . count($words) . "\n\n";
    
    $total_votes = array_sum(array_column($words, 'vote_count'));
    $csv .= "Word,Votes,Percentage,First Added\n";
    
    foreach ($words as $word) {
        $percentage = $total_votes > 0 ? round(($word['vote_count'] / $total_votes) * 100, 2) : 0;
        $csv .= "\"" . str_replace('"', '""', $word['word_text']) . "\",";
        $csv .= $word['vote_count'] . ",";
        $csv .= $percentage . "%,";
        $csv .= $word['created_at'] . "\n";
    }
    
    // Output CSV
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="wordcloud_poll_' . $poll_id . '.csv"');
    echo $csv;
    
} catch(Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Failed to export data: ' . $e->getMessage()]);
}
?>