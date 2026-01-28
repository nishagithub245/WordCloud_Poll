<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

$poll_id = $_GET['id'] ?? '';
$token = $_GET['token'] ?? '';
$poll = [];
$is_admin = false;

if ($poll_id) {
    $poll = getPollDetails($poll_id);
    if ($token) {
        $is_admin = isAdmin($poll_id, $token);
    }
}

$page_title = $poll ? htmlspecialchars($poll['question']) . ' - Word Cloud Poll' : 'Poll Not Found';
require_once 'includes/header.php';

if (!$poll):
?>
    <div class="error-state">
        <div class="error-icon">🔍</div>
        <h2>Poll Not Found</h2>
        <p>The poll you're looking for doesn't exist or has been deleted.</p>
        <a href="create.php" class="btn btn-primary">Create a New Poll</a>
        <a href="index.php" class="btn btn-secondary">Go to Home</a>
    </div>
<?php else: ?>
    <div class="poll-view-container">
        <div class="poll-header">
            <h1 id="pollQuestion"><?php echo htmlspecialchars($poll['question']); ?></h1>
            <div class="poll-info">
                <span class="poll-id">Poll ID: <code><?php echo htmlspecialchars($poll_id); ?></code></span>
                <span class="poll-date">Created: <?php echo date('M j, Y', strtotime($poll['created_at'])); ?></span>
                <?php if($is_admin): ?>
                    <span class="admin-badge">Admin Mode</span>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="poll-main">
            <div class="wordcloud-section">
                <div id="wordcloud-container">
                    <svg id="wordcloud-svg" width="100%" height="500"></svg>
                </div>
                <div id="wordcloud-message">
                    <div class="loading-words">
                        <div class="spinner small"></div>
                        <p>Loading word cloud...</p>
                    </div>
                </div>
            </div>
            
            <div class="poll-controls">
                <div class="input-section">
                    <h3>Add Your Word</h3>
                    <div class="input-group">
                        <input type="text" id="wordInput" 
                               placeholder="Type a word and press Enter..." 
                               maxlength="50"
                               autocomplete="off">
                        <button id="submitWordBtn" class="btn btn-primary">
                            <span class="btn-icon">➕</span> Submit
                        </button>
                    </div>
                    <div class="hint">
                        <p>💡 <strong>Tip:</strong> Click on any word in the cloud to vote for it!</p>
                        <p>Words become larger as they get more votes.</p>
                    </div>
                    <div id="errorMessage" class="error-message"></div>
                    <div id="successMessage" class="success-message"></div>
                </div>
                
                <div class="word-list-section">
                    <div class="section-header">
                        <h3>All Words (<span id="wordCount">0</span>)</h3>
                        <div class="sort-controls">
                            <button class="sort-btn active" data-sort="votes">Most Votes</button>
                            <button class="sort-btn" data-sort="alphabetical">A-Z</button>
                        </div>
                    </div>
                    <div id="wordList" class="word-list"></div>
                </div>
            </div>
        </div>
        
        <?php if($is_admin): ?>
        <div class="admin-panel">
            <h3>🔧 Admin Controls</h3>
            <div class="admin-buttons">
                <button onclick="exportPollData()" class="btn btn-secondary">
                    <span class="btn-icon">📊</span> Export Data (CSV)
                </button>
                <button onclick="deletePoll()" class="btn btn-danger">
                    <span class="btn-icon">🗑️</span> Delete Poll
                </button>
            </div>
            <div class="admin-stats">
                <h4>📈 Live Statistics</h4>
                <div class="stats-grid">
                    <div class="stat-box">
                        <div class="stat-value" id="totalWords">0</div>
                        <div class="stat-label">Total Words</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-value" id="totalVotes">0</div>
                        <div class="stat-label">Total Votes</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-value" id="topWord">-</div>
                        <div class="stat-label">Top Word</div>
                    </div>
                    <div class="stat-box">
                        <div class="stat-value" id="avgVotes">0</div>
                        <div class="stat-label">Avg Votes</div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="share-section">
            <h3>🔗 Share This Poll</h3>
            <div class="share-buttons">
                <button onclick="sharePoll()" class="share-btn">
                    <span class="share-icon">📤</span> Share Poll Link
                </button>
                <button onclick="copyPollLink()" class="share-btn">
                    <span class="share-icon">📋</span> Copy Link
                </button>
            </div>
        </div>
    </div>
    
    <script>
    const currentPollId = "<?php echo htmlspecialchars($poll_id, ENT_QUOTES); ?>";
    const isAdmin = <?php echo $is_admin ? 'true' : 'false'; ?>;
    const adminToken = "<?php echo $is_admin ? htmlspecialchars($token, ENT_QUOTES) : ''; ?>";
    
    let wordsData = [];
    let refreshInterval;
    let currentSort = 'votes';
    
    // Initialize poll
    document.addEventListener('DOMContentLoaded', function() {
        fetchWords();
        startAutoRefresh();
        setupEventListeners();
        
        if (isAdmin) {
            loadAdminStats();
        }
    });
    
    // Setup event listeners
    function setupEventListeners() {
        const wordInput = document.getElementById('wordInput');
        const submitBtn = document.getElementById('submitWordBtn');
        
        if (wordInput) {
            wordInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    submitWord();
                }
            });
            
            if (submitBtn) {
                submitBtn.addEventListener('click', submitWord);
            }
        }
        
        // Sort buttons
        document.querySelectorAll('.sort-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.sort-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                currentSort = this.dataset.sort;
                renderWordList();
            });
        });
    }
    
    // Fetch words from API
    async function fetchWords() {
        try {
            const response = await fetch(`api/get-words.php?poll_id=${currentPollId}`);
            const result = await response.json();
            
            if (result.success) {
                wordsData = result.words || [];
                updateWordCount();
                renderWordCloud();
                renderWordList();
                
                if (isAdmin) {
                    updateAdminStats();
                }
            }
        } catch (error) {
            console.error('Error fetching words:', error);
        }
    }
    
    // Submit word
    async function submitWord() {
        const wordInput = document.getElementById('wordInput');
        const word = wordInput.value.trim().toLowerCase();
        
        if (!word) {
            showError('Please enter a word');
            return;
        }
        
        if (word.length > 50) {
            showError('Word is too long (max 50 characters)');
            return;
        }
        
        if (!/^[a-zA-Z0-9\s\-]+$/.test(word)) {
            showError('Only letters, numbers, spaces and hyphens allowed');
            return;
        }
        
        try {
            const formData = new FormData();
            formData.append('poll_id', currentPollId);
            formData.append('word', word);
            
            const response = await fetch('api/submit-word.php', {
                method: 'POST',
                body: formData
            });
            
            const result = await response.json();
            
            if (result.success) {
                showSuccess(result.message || 'Word submitted!');
                wordInput.value = '';
                await fetchWords();
            } else {
                showError(result.message || 'Failed to submit word');
            }
        } catch (error) {
            showError('Network error. Please try again.');
        }
    }
    
    // Render word cloud
    function renderWordCloud() {
        const container = document.getElementById('wordcloud-svg');
        if (!container) return;
        
        container.innerHTML = '';
        
        if (wordsData.length === 0) {
            const messageDiv = document.getElementById('wordcloud-message');
            if (messageDiv) {
                messageDiv.innerHTML = `
                    <div class="empty-state">
                        <div class="empty-icon">📝</div>
                        <h3>No words yet!</h3>
                        <p>Be the first to add a word to this poll.</p>
                    </div>
                `;
            }
            return;
        }
        
        const width = container.clientWidth;
        const height = 500;
        
        const wordData = wordsData.map(word => ({
            text: word.word_text,
            size: 20 + (word.vote_count * 3),
            votes: word.vote_count
        }));
        
        const layout = d3.layout.cloud()
            .size([width, height])
            .words(wordData)
            .padding(8)
            .rotate(() => Math.random() > 0.7 ? 90 : 0)
            .font("Arial")
            .fontSize(d => d.size)
            .on("end", draw);
        
        layout.start();
        
        function draw(words) {
            d3.select("#wordcloud-svg")
                .attr("width", width)
                .attr("height", height)
                .append("g")
                .attr("transform", `translate(${width/2},${height/2})`)
                .selectAll("text")
                .data(words)
                .enter().append("text")
                .style("font-size", d => `${d.size}px`)
                .style("font-family", "Arial")
                .style("fill", () => {
                    const colors = ["#667eea", "#764ba2", "#f56565", "#ed8936", "#48bb78"];
                    return colors[Math.floor(Math.random() * colors.length)];
                })
                .attr("text-anchor", "middle")
                .attr("transform", d => `translate(${[d.x, d.y]})rotate(${d.rotate})`)
                .text(d => d.text)
                .attr("class", "wordcloud-word")
                .on("click", function(event, d) {
                    document.getElementById('wordInput').value = d.text;
                    submitWord();
                });
        }
    }
    
    // Update word count
    function updateWordCount() {
        const wordCountSpan = document.getElementById('wordCount');
        if (wordCountSpan) {
            wordCountSpan.textContent = wordsData.length;
        }
    }
    
    // Render word list
    function renderWordList() {
        const wordList = document.getElementById('wordList');
        if (!wordList) return;
        
        wordList.innerHTML = '';
        
        let sortedWords = [...wordsData];
        
        if (currentSort === 'votes') {
            sortedWords.sort((a, b) => b.vote_count - a.vote_count);
        } else if (currentSort === 'alphabetical') {
            sortedWords.sort((a, b) => a.word_text.localeCompare(b.word_text));
        }
        
        sortedWords.forEach(word => {
            const wordEl = document.createElement('div');
            wordEl.className = 'word-tag';
            wordEl.innerHTML = `
                <span>${word.word_text}</span>
                <span class="vote-count">${word.vote_count}</span>
            `;
            
            wordEl.addEventListener('click', () => {
                document.getElementById('wordInput').value = word.word_text;
                submitWord();
            });
            
            wordList.appendChild(wordEl);
        });
    }
    
    // Auto-refresh
    function startAutoRefresh() {
        if (refreshInterval) {
            clearInterval(refreshInterval);
        }
        
        refreshInterval = setInterval(() => {
            fetchWords();
        }, 25000);
    }
    
    // Update admin stats
    function updateAdminStats() {
        const totalVotes = wordsData.reduce((sum, word) => sum + word.vote_count, 0);
        const totalWords = wordsData.length;
        const avgVotes = totalWords > 0 ? (totalVotes / totalWords).toFixed(1) : 0;
        const topWord = wordsData.length > 0 ? wordsData[0].word_text : '-';
        
        document.getElementById('totalWords').textContent = totalWords;
        document.getElementById('totalVotes').textContent = totalVotes;
        document.getElementById('topWord').textContent = topWord;
        document.getElementById('avgVotes').textContent = avgVotes;
    }
    
    // Admin functions
    function exportPollData() {
        if (!adminToken) {
            showError('Admin token not found');
            return;
        }
        
        window.open(`api/export-poll.php?poll_id=${currentPollId}&token=${adminToken}`, '_blank');
    }
    
    function deletePoll() {
        if (!confirm('Are you sure? This will permanently delete the poll and all its data!')) {
            return;
        }
        
        const formData = new FormData();
        formData.append('poll_id', currentPollId);
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
                showError(result.message || 'Failed to delete poll');
            }
        })
        .catch(error => {
            showError('Network error');
        });
    }
    
    // Share functions
    function sharePoll() {
        const url = window.location.href.split('?')[0] + `?id=${currentPollId}`;
        
        if (navigator.share) {
            navigator.share({
                title: document.getElementById('pollQuestion').textContent,
                text: 'Check out this word cloud poll!',
                url: url
            });
        } else {
            copyToClipboardInput(url, 'Poll link copied to clipboard!');
        }
    }
    
    function copyPollLink() {
        const url = window.location.href.split('?')[0] + `?id=${currentPollId}`;
        copyToClipboardInput(url, 'Poll link copied to clipboard!');
    }
    
    function copyToClipboardInput(text, successMessage) {
        const tempInput = document.createElement('input');
        tempInput.value = text;
        document.body.appendChild(tempInput);
        tempInput.select();
        document.execCommand('copy');
        document.body.removeChild(tempInput);
        showSuccess(successMessage);
    }
    
    // Load admin stats
    function loadAdminStats() {
        fetch(`api/get-poll.php?poll_id=${currentPollId}&stats=true`)
            .then(response => response.json())
            .then(result => {
                if (result.success && result.stats) {
                    updateAdminStats();
                }
            });
    }
    </script>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>