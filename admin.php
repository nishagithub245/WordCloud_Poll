<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

$poll_id = $_GET['id'] ?? '';
$token = $_GET['token'] ?? '';

if (!$poll_id || !$token) {
    header('Location: index.php');
    exit;
}

if (!isAdmin($poll_id, $token)) {
    die('Access denied. Invalid admin token.');
}

$poll = getPollDetails($poll_id);
$page_title = "Admin Dashboard - " . htmlspecialchars($poll['question']);
require_once 'includes/header.php';

$stats = getPollStats($poll_id);
$words = getPollWords($poll_id);
?>

<div class="admin-dashboard">
    <h1>🔧 Poll Administration</h1>
    
    <div class="poll-info-card">
        <h2><?php echo htmlspecialchars($poll['question']); ?></h2>
        <div class="poll-meta">
            <span>Poll ID: <code><?php echo htmlspecialchars($poll_id); ?></code></span>
            <span>Created: <?php echo date('F j, Y g:i A', strtotime($poll['created_at'])); ?></span>
            <span>Admin Token: <code><?php echo substr($token, 0, 16); ?>...</code></span>
        </div>
    </div>
    
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-value"><?php echo $stats['total_words']; ?></div>
            <div class="stat-label">Total Words</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-value"><?php echo $stats['total_votes']; ?></div>
            <div class="stat-label">Total Votes</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-value">
                <?php echo $stats['total_words'] > 0 ? 
                    round($stats['total_votes'] / $stats['total_words'], 1) : 0; ?>
            </div>
            <div class="stat-label">Avg Votes per Word</div>
        </div>
        
        <div class="stat-card">
            <div class="stat-value">
                <?php echo $words ? $words[0]['vote_count'] : 0; ?>
            </div>
            <div class="stat-label">Top Word Votes</div>
        </div>
    </div>
    
    <div class="admin-controls">
        <h2>Management Tools</h2>
        
        <div class="control-buttons">
            <button onclick="exportData()" class="btn btn-secondary">
                <span class="btn-icon">📊</span> Export Data (CSV)
            </button>
            
            <button onclick="viewLivePoll()" class="btn">
                <span class="btn-icon">👁️</span> View Live Poll
            </button>
            
            <button onclick="deletePoll()" class="btn btn-danger">
                <span class="btn-icon">🗑️</span> Delete Poll
            </button>
        </div>
    </div>
    
    <div class="word-list-admin">
        <h2>All Words (<?php echo count($words); ?>)</h2>
        
        <div class="table-container">
            <table class="words-table">
                <thead>
                    <tr>
                        <th>Word</th>
                        <th>Votes</th>
                        <th>Percentage</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $total_votes = $stats['total_votes'];
                    foreach($words as $word): 
                        $percentage = $total_votes > 0 ? 
                            round(($word['vote_count'] / $total_votes) * 100, 1) : 0;
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($word['word_text']); ?></td>
                        <td><?php echo $word['vote_count']; ?></td>
                        <td><?php echo $percentage; ?>%</td>
                        <td>
                            <button onclick="removeWord('<?php echo htmlspecialchars($word['word_text']); ?>')" 
                                    class="btn-small btn-danger">
                                Remove
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    
                    <?php if(empty($words)): ?>
                    <tr>
                        <td colspan="4" class="empty-table">No words submitted yet</td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <div class="share-links">
        <h2>Poll Links</h2>
        
        <div class="link-group">
            <label>Participant Link:</label>
            <div class="link-box">
                <input type="text" id="participantLink" 
                       value="<?php echo "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/poll.php?id=" . $poll_id; ?>"
                       readonly>
                <button onclick="copyToClipboard('participantLink')" class="btn-copy">
                    📋 Copy
                </button>
            </div>
        </div>
        
        <div class="link-group">
            <label>Admin Link:</label>
            <div class="link-box">
                <input type="text" id="adminLink" 
                       value="<?php echo "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>"
                       readonly>
                <button onclick="copyToClipboard('adminLink')" class="btn-copy">
                    📋 Copy
                </button>
            </div>
        </div>
    </div>
</div>

<script>
const pollId = "<?php echo htmlspecialchars($poll_id, ENT_QUOTES); ?>";
const adminToken = "<?php echo htmlspecialchars($token, ENT_QUOTES); ?>";

function exportData() {
    window.open(`api/export-poll.php?poll_id=${pollId}&token=${adminToken}`, '_blank');
}

function viewLivePoll() {
    window.location.href = `poll.php?id=${pollId}`;
}

function deletePoll() {
    if (!confirm('⚠️ WARNING: This will permanently delete the poll and all its data!\n\nThis action cannot be undone.')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('poll_id', pollId);
    formData.append('token', adminToken);
    
    fetch('api/delete-poll.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            alert('Poll deleted successfully!');
            window.location.href = 'index.php';
        } else {
            alert(' Error: ' + (result.message || 'Failed to delete poll'));
        }
    })
    .catch(error => {
        alert(' Network error');
    });
}

function removeWord(word) {
    if (!confirm(`Remove the word "${word}"?`)) {
        return;
    }
    
   
    showError('Word removal feature not implemented yet');
}
</script>

<?php require_once 'includes/footer.php'; ?>