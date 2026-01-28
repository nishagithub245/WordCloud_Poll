<?php
require_once 'config/database.php';
$page_title = "Create Word Cloud Poll";
require_once 'includes/header.php';
?>

<div class="create-poll-container">
    <h1>Create New Poll</h1>
    
    <div class="form-card">
        <form id="createPollForm">
            <div class="form-group">
                <label for="question">Enter your poll question:</label>
                <input type="text" id="question" name="question" 
                       placeholder="e.g., What comes to mind when you think of our company?"
                       required>
                <small class="hint">This question will be shown to all participants</small>
            </div>
            
            <div class="form-options">
                <h3>Optional Settings:</h3>
                <div class="option">
                    <input type="checkbox" id="case_sensitive" name="case_sensitive">
                    <label for="case_sensitive">Case-sensitive words (e.g., "Apple" ≠ "apple")</label>
                </div>
                <div class="option">
                    <input type="checkbox" id="allow_duplicates" name="allow_duplicates" checked>
                    <label for="allow_duplicates">Allow multiple votes per word</label>
                </div>
            </div>
            
            <button type="submit" class="btn btn-primary btn-large">
                <span class="btn-icon">🚀</span> Create Poll
            </button>
        </form>
    </div>
    
    <div id="pollLinks" class="links-card" style="display: none;">
        <h2>🎉 Your Poll is Ready!</h2>
        
        <div class="link-section">
            <h3>👥 Participant Link</h3>
            <p>Share this link with your audience:</p>
            <div class="link-box">
                <input type="text" id="participantLink" readonly>
                <button onclick="copyToClipboard('participantLink')" class="btn-copy">
                    <span class="copy-icon">📋</span> Copy
                </button>
            </div>
        </div>
        
        <div class="link-section">
            <h3>🔒 Admin Link (Keep Private)</h3>
            <p>Use this link to manage your poll:</p>
            <div class="link-box">
                <input type="text" id="adminLink" readonly>
                <button onclick="copyToClipboard('adminLink')" class="btn-copy">
                    <span class="copy-icon">📋</span> Copy
                </button>
            </div>
        </div>
        
        <div class="next-steps">
            <h3>Next Steps:</h3>
            <ol>
                <li>Share the participant link with your audience</li>
                <li>Bookmark the admin link for future access</li>
                <li>Monitor responses in real-time</li>
                <li>Export data when you're done</li>
            </ol>
        </div>
    </div>
</div>

<script>
document.getElementById('createPollForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.append('action', 'create_poll');
    
    showLoading(true);
    
    try {
        const response = await fetch('api/create-poll.php', {
            method: 'POST',
            body: formData
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Show the links
            const baseUrl = window.location.origin + window.location.pathname.replace('create.php', '');
            
            document.getElementById('participantLink').value = 
                `${baseUrl}poll.php?id=${result.poll_id}`;
            document.getElementById('adminLink').value = 
                `${baseUrl}poll.php?id=${result.poll_id}&token=${result.admin_token}`;
            
            document.getElementById('pollLinks').style.display = 'block';
            
            // Scroll to links
            document.getElementById('pollLinks').scrollIntoView({ behavior: 'smooth' });
            
            // Show success message
            showSuccess('Poll created successfully!');
            
            // Clear form
            document.getElementById('createPollForm').reset();
            
        } else {
            showError(result.message || 'Failed to create poll');
        }
    } catch (error) {
        showError('Network error. Please try again.');
    } finally {
        showLoading(false);
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>