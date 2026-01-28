<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Word Cloud Poll'; ?></title>
    <link rel="stylesheet" href="styles.css">
    <script src="https://cdn.jsdelivr.net/npm/d3@7.8.5/dist/d3.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/d3-cloud@1.2.5/build/d3.layout.cloud.min.js"></script>
    <style>
        /* Basic styles that will be overridden by styles.css if it loads */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f0f2f5; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .navbar { background: white; padding: 20px; border-radius: 10px; margin-bottom: 20px; }
        .navbar a { margin-right: 20px; text-decoration: none; color: #333; }
        .navbar a:hover { color: #667eea; }
    </style>
</head>
<body>
    <div class="navbar">
        <a href="index.php">🏠 Home</a>
        <a href="create.php">➕ Create Poll</a>
        <?php if(isset($_GET['id']) && isset($_GET['token'])): ?>
            <a href="admin.php?id=<?php echo htmlspecialchars($_GET['id']); ?>&token=<?php echo htmlspecialchars($_GET['token']); ?>">🔧 Admin</a>
        <?php endif; ?>
    </div>
    <div class="container"></div>