<?php
require_once 'config/database.php';
require_once 'includes/functions.php';

$page_title = "Word Cloud Poll - Create Interactive Polls";
require_once 'includes/header.php';
?>

<div class="hero">
    <h1>Create Interactive Word Cloud Polls</h1>
    <p class="subtitle">Engage your audience with real-time word clouds</p>
</div>
<!--- <div class="features-grid">
    <div class="feature-card">
        <div class="feature-icon">🚀</div>
        <h3>Easy to Create</h3>
        <p>Create a poll in seconds with just a question</p>
    </div>
    
    <div class="feature-card">
        <div class="feature-icon">⚡</div>
        <h3>Real-time Updates</h3>
        <p>See words appear and grow in real-time</p>
    </div>
    
    <div class="feature-card">
        <div class="feature-icon">👥</div>
        <h3>No Login Required</h3>
        <p>Participants can join without registration</p>
    </div>
    
    <div class="feature-card">
        <div class="feature-icon">📊</div>
        <h3>Analytics</h3>
        <p>Export data and see voting patterns</p>
    </div>
</div>
 -->

<div class="cta-section">
    <h2>Ready to Create Your First Poll?</h2>
    <a href="create.php" class="btn btn-primary btn-large">
        <span class="btn-icon">➕</span> Create a Poll Now
    </a>
    <!--- <div class="demo-link">
        <p>Or try a demo:</p>
        <a href="poll.php?id=DEMO123" class="btn btn-secondary">
            <span class="btn-icon">👀</span> View Demo Poll
        </a>
    </div> -->
    
</div>

<div class="how-it-works">
    <h2>How It Works</h2>
    <div class="steps">
        <div class="step">
            <div class="step-number">1</div>
            <div class="step-content">
                <h3>Create Poll</h3>
                <p>Enter your question and create a unique poll</p>
            </div>
        </div>
        
        <div class="step">
            <div class="step-number">2</div>
            <div class="step-content">
                <h3>Share Link</h3>
                <p>Share the link with your participants</p>
            </div>
        </div>
        
        <div class="step">
            <div class="step-number">3</div>
            <div class="step-content">
                <h3>Collect Responses</h3>
                <p>Watch the word cloud grow as people submit words</p>
            </div>
        </div>
        
        <div class="step">
            <div class="step-number">4</div>
            <div class="step-content">
                <h3>Analyze Results</h3>
                <p>See which words are most popular</p>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>