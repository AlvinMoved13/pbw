<?php
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'koneksi.php';

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$full_name = $_SESSION['full_name'];

$message = '';
$error = '';

// Handle Create, Update, Delete
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'create') {
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $category = trim($_POST['category'] ?? 'General');
        
        if (!empty($title) && !empty($content)) {
            $stmt = $conn->prepare("INSERT INTO articles (user_id, title, content, category) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("isss", $user_id, $title, $content, $category);
            
            if ($stmt->execute()) {
                $message = "Artikel berhasil dibuat!";
            } else {
                $error = "Gagal membuat artikel!";
            }
            $stmt->close();
        } else {
            $error = "Judul dan konten harus diisi!";
        }
    }
    elseif ($_POST['action'] == 'update') {
        $id = $_POST['id'];
        $title = trim($_POST['title'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $category = trim($_POST['category'] ?? 'General');
        
        if (!empty($title) && !empty($content)) {
            $stmt = $conn->prepare("UPDATE articles SET title = ?, content = ?, category = ? WHERE id = ? AND user_id = ?");
            $stmt->bind_param("sssii", $title, $content, $category, $id, $user_id);
            
            if ($stmt->execute()) {
                $message = "Artikel berhasil diupdate!";
            } else {
                $error = "Gagal mengupdate artikel!";
            }
            $stmt->close();
        } else {
            $error = "Judul dan konten harus diisi!";
        }
    }
    elseif ($_POST['action'] == 'delete') {
        $id = $_POST['id'];
        
        $stmt = $conn->prepare("DELETE FROM articles WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $id, $user_id);
        
        if ($stmt->execute()) {
            $message = "Artikel berhasil dihapus!";
        } else {
            $error = "Gagal menghapus artikel!";
        }
        $stmt->close();
    }
}

// Read - Ambil semua artikel milik user
$filter_category = $_GET['category'] ?? '';
if ($filter_category) {
    $stmt = $conn->prepare("SELECT * FROM articles WHERE user_id = ? AND category = ? ORDER BY created_at DESC");
    if ($stmt) {
        $stmt->bind_param("is", $user_id, $filter_category);
        $stmt->execute();
        $result = $stmt->get_result();
        $articles = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    } else {
        $articles = [];
    }
} else {
    $stmt = $conn->prepare("SELECT * FROM articles WHERE user_id = ? ORDER BY created_at DESC");
    if ($stmt) {
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $articles = $result->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    } else {
        $articles = [];
    }
}

// Ambil semua kategori untuk filter
$categories = [];
$categories_stmt = $conn->prepare("SELECT DISTINCT category FROM articles WHERE user_id = ? ORDER BY category");
if ($categories_stmt) {
    $categories_stmt->bind_param("i", $user_id);
    $categories_stmt->execute();
    $categories_result = $categories_stmt->get_result();
    $categories = $categories_result->fetch_all(MYSQLI_ASSOC);
    $categories_stmt->close();
}

// Ambil data untuk edit jika ada
$edit_data = null;
if (isset($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM articles WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $edit_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_data = $result->fetch_assoc();
    $stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Article - Daily Journal</title>
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
        
        .message {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .message.success {
            background: #d4edda;
            color: #155724;
            border-left: 3px solid #28a745;
        }
        
        .message.error {
            background: #f8d7da;
            color: #721c24;
            border-left: 3px solid #dc3545;
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
        
        .filter-section {
            margin-bottom: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
        }
        
        .filter-section select {
            padding: 8px 15px;
            border: 2px solid #e1e1e1;
            border-radius: 5px;
            font-size: 14px;
            cursor: pointer;
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
        
        .form-group input[type="text"],
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e1e1e1;
            border-radius: 5px;
            font-size: 14px;
            font-family: inherit;
        }
        
        .form-group textarea {
            min-height: 200px;
            resize: vertical;
        }
        
        .form-group input:focus,
        .form-group select:focus,
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
        
        .btn-secondary {
            background: #6c757d;
            color: white;
            padding: 8px 20px;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-left: 10px;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
        }
        
        .articles-list {
            margin-top: 20px;
        }
        
        .article-card {
            background: white;
            border: 2px solid #e1e1e1;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .article-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .article-card h3 {
            color: #333;
            margin-bottom: 10px;
            font-size: 20px;
        }
        
        .article-meta {
            display: flex;
            gap: 20px;
            font-size: 13px;
            color: #999;
            margin-bottom: 15px;
        }
        
        .article-category {
            display: inline-block;
            padding: 4px 12px;
            background: #667eea;
            color: white;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .article-content {
            color: #666;
            line-height: 1.8;
            margin-bottom: 15px;
        }
        
        .article-actions {
            display: flex;
            gap: 10px;
        }
        
        .btn-edit {
            background: #ffc107;
            color: #333;
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        
        .btn-edit:hover {
            background: #e0a800;
        }
        
        .btn-delete {
            background: #dc3545;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            cursor: pointer;
        }
        
        .btn-delete:hover {
            background: #c82333;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>📰 Article</h1>
        <div class="navbar-right">
            <div class="nav-menu">
                <a href="dashboard.php">Dashboard</a>
                <a href="article.php" class="active">Article</a>
                <a href="diary.php">Diary</a>
                <a href="gallery.php">Gallery</a>
            </div>
            <span>👤 <?php echo htmlspecialchars($full_name); ?></span>
            <a href="logout.php" class="btn-logout">Logout</a>
        </div>
    </div>
    
    <div class="container">
        <?php if ($message): ?>
        <div class="message success">✓ <?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
        <div class="message error">✗ <?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <div class="section">
            <h2><?php echo $edit_data ? 'Edit Artikel' : 'Buat Artikel Baru'; ?></h2>
            <form method="POST" action="">
                <input type="hidden" name="action" value="<?php echo $edit_data ? 'update' : 'create'; ?>">
                <?php if ($edit_data): ?>
                <input type="hidden" name="id" value="<?php echo $edit_data['id']; ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="title">Judul Artikel</label>
                    <input type="text" id="title" name="title" value="<?php echo $edit_data ? htmlspecialchars($edit_data['title']) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="category">Kategori</label>
                    <input type="text" id="category" name="category" value="<?php echo $edit_data ? htmlspecialchars($edit_data['category']) : 'General'; ?>" placeholder="Contoh: Technology, Health, Lifestyle">
                </div>
                
                <div class="form-group">
                    <label for="content">Isi Artikel</label>
                    <textarea id="content" name="content" required><?php echo $edit_data ? htmlspecialchars($edit_data['content']) : ''; ?></textarea>
                </div>
                
                <button type="submit" class="btn-primary">
                    <?php echo $edit_data ? '💾 Update Artikel' : '📝 Simpan Artikel'; ?>
                </button>
                
                <?php if ($edit_data): ?>
                <a href="article.php" class="btn-secondary">Batal</a>
                <?php endif; ?>
            </form>
        </div>
        
        <div class="section">
            <h2>Artikel Saya (<?php echo count($articles); ?>)</h2>
            
            <?php if (!empty($categories)): ?>
            <div class="filter-section">
                <label>Filter by Category: </label>
                <select onchange="window.location.href='article.php?category=' + this.value">
                    <option value="">Semua Kategori</option>
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo htmlspecialchars($cat['category']); ?>" <?php echo $filter_category == $cat['category'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($cat['category']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php endif; ?>
            
            <?php if (empty($articles)): ?>
            <div class="empty-state">
                <p style="font-size: 48px;">📰</p>
                <p>Belum ada artikel<?php echo $filter_category ? ' di kategori ini' : ''; ?>. Buat artikel pertama Anda!</p>
            </div>
            <?php else: ?>
            <div class="articles-list">
                <?php foreach ($articles as $article): ?>
                <div class="article-card">
                    <h3><?php echo htmlspecialchars($article['title']); ?></h3>
                    <div class="article-meta">
                        <span class="article-category"><?php echo htmlspecialchars($article['category']); ?></span>
                        <span>📅 <?php echo date('d M Y, H:i', strtotime($article['created_at'])); ?></span>
                        <?php if ($article['updated_at'] != $article['created_at']): ?>
                        <span>✏️ Updated: <?php echo date('d M Y, H:i', strtotime($article['updated_at'])); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="article-content">
                        <?php echo nl2br(htmlspecialchars($article['content'])); ?>
                    </div>
                    <div class="article-actions">
                        <a href="article.php?edit=<?php echo $article['id']; ?>" class="btn-edit">✏️ Edit</a>
                        <form method="POST" action="" style="display: inline;" onsubmit="return confirm('Yakin ingin menghapus artikel ini?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?php echo $article['id']; ?>">
                            <button type="submit" class="btn-delete">🗑️ Hapus</button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>