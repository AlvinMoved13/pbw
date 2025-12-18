<?php
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'koneksi.php';

// Ambil data user
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$full_name = $_SESSION['full_name'];

// Ambil data diary
$diaries = [];
$stmt = $conn->prepare("SELECT id, title, content, mood, created_at FROM diary WHERE user_id = ? ORDER BY created_at DESC");
if ($stmt) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $diaries = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// Ambil data gallery
$galleries = [];
$stmt = $conn->prepare("SELECT id, title, description, image_path, upload_date as created_at FROM gallery WHERE user_id = ? ORDER BY upload_date DESC");
if ($stmt) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $galleries = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// Ambil artikel milik user
$articles = [];
$stmt = $conn->prepare("SELECT id, title, content, created_at, updated_at FROM articles WHERE user_id = ? ORDER BY created_at DESC");
if ($stmt) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $articles = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// Helper function untuk emoji mood
function getMoodEmoji($mood) {
    $moods = [
        'Happy' => '😊',
        'Sad' => '😢',
        'Excited' => '🤩',
        'Angry' => '😠',
        'Normal' => '😐',
        'Grateful' => '🙏',
        'Anxious' => '😰',
        'Peaceful' => '😌'
    ];
    return $moods[$mood] ?? '😊';
}

// Handle delete actions
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'delete_article') {
        $article_id = $_POST['article_id'] ?? 0;
        $stmt = $conn->prepare("DELETE FROM articles WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $article_id, $user_id);
        $stmt->execute();
        $stmt->close();
        header("Location: dashboard.php?success=deleted");
        exit();
    } elseif ($_POST['action'] == 'delete_diary') {
        $diary_id = $_POST['diary_id'] ?? 0;
        $stmt = $conn->prepare("DELETE FROM diary WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $diary_id, $user_id);
        $stmt->execute();
        $stmt->close();
        header("Location: dashboard.php?success=deleted");
        exit();
    } elseif ($_POST['action'] == 'delete_gallery') {
        $gallery_id = $_POST['gallery_id'] ?? 0;
        // Get image path to delete file
        $stmt = $conn->prepare("SELECT image_path FROM gallery WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $gallery_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            if (file_exists($row['image_path'])) {
                unlink($row['image_path']);
            }
        }
        $stmt->close();
        // Delete from database
        $stmt = $conn->prepare("DELETE FROM gallery WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $gallery_id, $user_id);
        $stmt->execute();
        $stmt->close();
        header("Location: dashboard.php?success=deleted");
        exit();
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Daily Journal</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f5f5;
        }
        
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .navbar h1 {
            font-size: 24px;
        }
        
        .navbar-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .nav-menu {
            display: flex;
            gap: 15px;
        }
        
        .nav-menu a {
            color: white;
            text-decoration: none;
            padding: 8px 15px;
            border-radius: 5px;
            transition: background 0.3s;
        }
        
        .nav-menu a:hover,
        .nav-menu a.active {
            background: rgba(255, 255, 255, 0.2);
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .btn-logout {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 8px 20px;
            border: 1px solid white;
            border-radius: 5px;
            text-decoration: none;
            transition: background 0.3s;
        }
        
        .btn-logout:hover {
            background: rgba(255, 255, 255, 0.3);
        }
        
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 3px solid #28a745;
        }
        
        .section {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
        }
        
        .section h2 {
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #667eea;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #333;
            font-weight: 500;
        }
        
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e1e1e1;
            border-radius: 5px;
            font-size: 14px;
            font-family: inherit;
        }
        
        .form-group textarea {
            min-height: 150px;
            resize: vertical;
        }
        
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .content-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .content-card {
            background: white;
            border: 2px solid #e1e1e1;
            border-radius: 8px;
            padding: 20px;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .content-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .content-card h3 {
            color: #333;
            margin-bottom: 10px;
            font-size: 18px;
        }
        
        .content-card p {
            color: #666;
            line-height: 1.6;
            margin-bottom: 15px;
        }
        
        .content-meta {
            font-size: 12px;
            color: #999;
            margin-bottom: 15px;
        }
        
        .gallery-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 5px;
            margin-bottom: 10px;
        }
        
        .mood-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            margin-bottom: 10px;
            background: #f0f0f0;
        }
        
        .btn-delete {
            background: #dc3545;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .btn-delete:hover {
            background: #c82333;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #999;
        }
        
        .empty-state svg {
            width: 80px;
            height: 80px;
            margin-bottom: 20px;
            opacity: 0.5;
        }
        
        .row {
            display: flex;
            flex-wrap: wrap;
            margin: 0 -10px;
        }
        
        .col-md-4 {
            flex: 0 0 33.333333%;
            max-width: 33.333333%;
            padding: 0 10px;
        }
        
        .g-4 {
            margin-bottom: 20px;
        }
        
        .card.bg-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
        }
        
        .card.bg-success {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%) !important;
        }
        
        .card.bg-warning {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%) !important;
            color: white !important;
        }
        
        .card .btn-light {
            background: white;
            color: #333;
            border: none;
        }
        
        .card .btn-dark {
            background: #333;
            color: white;
            border: none;
        }
        
        @media (max-width: 768px) {
            .col-md-4 {
                flex: 0 0 100%;
                max-width: 100%;
                margin-bottom: 15px;
            }
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>📔 Daily Journal</h1>
        <div class="navbar-right">
            <div class="nav-menu">
                <a href="dashboard.php" class="active">Dashboard</a>
                <a href="article.php">Article</a>
                <a href="diary.php">Diary</a>
                <a href="gallery.php">Gallery</a>
            </div>
            <div class="user-info">
                <span>👤 <?php echo htmlspecialchars($full_name); ?></span>
            </div>
            <a href="logout.php" class="btn-logout">Logout</a>
        </div>
    </div>
    
    <div class="container">
        <?php if (isset($_GET['success'])): ?>
        <div class="success-message">
            <?php 
            if ($_GET['success'] == 'deleted') {
                echo "✓ Data berhasil dihapus!";
            }
            ?>
        </div>
        <?php endif; ?>
        
        <!-- Diary Section -->
        <div class="section">
            <h2>📔 Diary Saya (<?php echo count($diaries); ?>)</h2>
            
            <?php if (empty($diaries)): ?>
            <div class="empty-state">
                <p>Belum ada diary. <a href="diary.php">Buat diary pertama Anda!</a></p>
            </div>
            <?php else: ?>
            <div class="content-grid">
                <?php foreach ($diaries as $diary): ?>
                <div class="content-card">
                    <div class="mood-badge"><?php echo getMoodEmoji($diary['mood']); ?> <?php echo htmlspecialchars($diary['mood']); ?></div>
                    <h3><?php echo htmlspecialchars($diary['title']); ?></h3>
                    <div class="content-meta">
                        📅 <?php echo date('d M Y, H:i', strtotime($diary['created_at'])); ?>
                    </div>
                    <p><?php echo nl2br(htmlspecialchars(substr($diary['content'], 0, 150))); ?><?php echo strlen($diary['content']) > 150 ? '...' : ''; ?></p>
                    <form method="POST" action="" style="display: inline;" onsubmit="return confirm('Yakin ingin menghapus diary ini?');">
                        <input type="hidden" name="action" value="delete_diary">
                        <input type="hidden" name="diary_id" value="<?php echo $diary['id']; ?>">
                        <button type="submit" class="btn-delete">🗑️ Hapus</button>
                    </form>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Gallery Section -->
        <div class="section">
            <h2>🖼️ Galeri Saya (<?php echo count($galleries); ?>)</h2>
            
            <?php if (empty($galleries)): ?>
            <div class="empty-state">
                <p>Belum ada foto. <a href="gallery.php">Upload foto pertama Anda!</a></p>
            </div>
            <?php else: ?>
            <div class="content-grid">
                <?php foreach ($galleries as $gallery): ?>
                <div class="content-card">
                    <img src="<?php echo htmlspecialchars($gallery['image_path']); ?>" alt="<?php echo htmlspecialchars($gallery['title']); ?>" class="gallery-image">
                    <h3><?php echo htmlspecialchars($gallery['title']); ?></h3>
                    <div class="content-meta">
                        📅 <?php echo date('d M Y, H:i', strtotime($gallery['created_at'])); ?>
                    </div>
                    <p><?php echo htmlspecialchars($gallery['description']); ?></p>
                    <form method="POST" action="" style="display: inline;" onsubmit="return confirm('Yakin ingin menghapus foto ini?');">
                        <input type="hidden" name="action" value="delete_gallery">
                        <input type="hidden" name="gallery_id" value="<?php echo $gallery['id']; ?>">
                        <button type="submit" class="btn-delete">🗑️ Hapus</button>
                    </form>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Articles Section -->
        <div class="section">
            <h2>📝 Artikel Saya (<?php echo count($articles); ?>)</h2>
            
            <?php if (empty($articles)): ?>
            <div class="empty-state">
                <p>Belum ada artikel. <a href="article.php">Buat artikel pertama Anda!</a></p>
            </div>
            <?php else: ?>
            <div class="content-grid">
                <?php foreach ($articles as $article): ?>
                <div class="content-card">
                    <h3><?php echo htmlspecialchars($article['title']); ?></h3>
                    <div class="content-meta">
                        📅 <?php echo date('d M Y, H:i', strtotime($article['created_at'])); ?>
                    </div>
                    <p><?php echo nl2br(htmlspecialchars(substr($article['content'], 0, 150))); ?><?php echo strlen($article['content']) > 150 ? '...' : ''; ?></p>
                    <form method="POST" action="" style="display: inline;" onsubmit="return confirm('Yakin ingin menghapus artikel ini?');">
                        <input type="hidden" name="action" value="delete_article">
                        <input type="hidden" name="article_id" value="<?php echo $article['id']; ?>">
                        <button type="submit" class="btn-delete">🗑️ Hapus</button>
                    </form>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
