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
        $mood = trim($_POST['mood'] ?? 'Normal');
        $diary_date = $_POST['diary_date'] ?? date('Y-m-d');
        
        if (!empty($title) && !empty($content)) {
            $stmt = $conn->prepare("INSERT INTO diary (user_id, title, content, mood, diary_date) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("issss", $user_id, $title, $content, $mood, $diary_date);
            
            if ($stmt->execute()) {
                $message = "Diary berhasil dibuat!";
            } else {
                $error = "Gagal membuat diary!";
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
        $mood = trim($_POST['mood'] ?? 'Normal');
        $diary_date = $_POST['diary_date'] ?? date('Y-m-d');
        
        if (!empty($title) && !empty($content)) {
            $stmt = $conn->prepare("UPDATE diary SET title = ?, content = ?, mood = ?, diary_date = ? WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ssssii", $title, $content, $mood, $diary_date, $id, $user_id);
            
            if ($stmt->execute()) {
                $message = "Diary berhasil diupdate!";
            } else {
                $error = "Gagal mengupdate diary!";
            }
            $stmt->close();
        } else {
            $error = "Judul dan konten harus diisi!";
        }
    }
    elseif ($_POST['action'] == 'delete') {
        $id = $_POST['id'];
        
        $stmt = $conn->prepare("DELETE FROM diary WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $id, $user_id);
        
        if ($stmt->execute()) {
            $message = "Diary berhasil dihapus!";
        } else {
            $error = "Gagal menghapus diary!";
        }
        $stmt->close();
    }
}

// Read - Ambil semua diary milik user
$filter_mood = $_GET['mood'] ?? '';
$filter_month = $_GET['month'] ?? '';

if ($filter_mood && $filter_month) {
    $stmt = $conn->prepare("SELECT * FROM diary WHERE user_id = ? AND mood = ? AND DATE_FORMAT(diary_date, '%Y-%m') = ? ORDER BY diary_date DESC");
    $stmt->bind_param("iss", $user_id, $filter_mood, $filter_month);
} elseif ($filter_mood) {
    $stmt = $conn->prepare("SELECT * FROM diary WHERE user_id = ? AND mood = ? ORDER BY diary_date DESC");
    $stmt->bind_param("is", $user_id, $filter_mood);
} elseif ($filter_month) {
    $stmt = $conn->prepare("SELECT * FROM diary WHERE user_id = ? AND DATE_FORMAT(diary_date, '%Y-%m') = ? ORDER BY diary_date DESC");
    $stmt->bind_param("is", $user_id, $filter_month);
} else {
    $stmt = $conn->prepare("SELECT * FROM diary WHERE user_id = ? ORDER BY diary_date DESC");
    $stmt->bind_param("i", $user_id);
}
$stmt->execute();
$result = $stmt->get_result();
$diaries = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Ambil semua mood untuk filter
$moods = ['Happy', 'Sad', 'Excited', 'Angry', 'Normal', 'Grateful', 'Anxious', 'Peaceful'];

// Ambil data untuk edit jika ada
$edit_data = null;
if (isset($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM diary WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $edit_id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $edit_data = $result->fetch_assoc();
    $stmt->close();
}

$conn->close();

// Fungsi untuk emoji mood
function getMoodEmoji($mood) {
    $emojis = [
        'Happy' => '😊',
        'Sad' => '😢',
        'Excited' => '🤩',
        'Angry' => '😠',
        'Normal' => '😐',
        'Grateful' => '🙏',
        'Anxious' => '😰',
        'Peaceful' => '😌'
    ];
    return $emojis[$mood] ?? '😐';
}

function getMoodColor($mood) {
    $colors = [
        'Happy' => '#ffc107',
        'Sad' => '#6c757d',
        'Excited' => '#ff6b6b',
        'Angry' => '#dc3545',
        'Normal' => '#17a2b8',
        'Grateful' => '#28a745',
        'Anxious' => '#fd7e14',
        'Peaceful' => '#20c997'
    ];
    return $colors[$mood] ?? '#17a2b8';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diary - Daily Journal</title>
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
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: center;
        }
        
        .filter-section select,
        .filter-section input[type="month"] {
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
        .form-group input[type="date"],
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
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
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
        
        .diary-list {
            margin-top: 20px;
        }
        
        .diary-card {
            background: white;
            border: 2px solid #e1e1e1;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            transition: transform 0.2s, box-shadow 0.2s;
            position: relative;
            overflow: hidden;
        }
        
        .diary-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .diary-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
            background: var(--mood-color);
        }
        
        .diary-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 15px;
        }
        
        .diary-card h3 {
            color: #333;
            font-size: 20px;
            margin-bottom: 5px;
        }
        
        .diary-meta {
            display: flex;
            gap: 15px;
            font-size: 13px;
            color: #999;
            margin-bottom: 15px;
        }
        
        .diary-mood {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 6px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
            color: white;
        }
        
        .diary-content {
            color: #666;
            line-height: 1.8;
            margin-bottom: 15px;
            padding-left: 15px;
        }
        
        .diary-actions {
            display: flex;
            gap: 10px;
            padding-left: 15px;
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
        
        .btn-filter {
            background: #667eea;
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>📔 Diary</h1>
        <div class="navbar-right">
            <div class="nav-menu">
                <a href="dashboard.php">Dashboard</a>
                <a href="article.php">Article</a>
                <a href="diary.php" class="active">Diary</a>
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
            <h2><?php echo $edit_data ? 'Edit Diary' : 'Tulis Diary Baru'; ?></h2>
            <form method="POST" action="">
                <input type="hidden" name="action" value="<?php echo $edit_data ? 'update' : 'create'; ?>">
                <?php if ($edit_data): ?>
                <input type="hidden" name="id" value="<?php echo $edit_data['id']; ?>">
                <?php endif; ?>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="title">Judul Diary</label>
                        <input type="text" id="title" name="title" value="<?php echo $edit_data ? htmlspecialchars($edit_data['title']) : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="diary_date">Tanggal</label>
                        <input type="date" id="diary_date" name="diary_date" value="<?php echo $edit_data ? $edit_data['diary_date'] : date('Y-m-d'); ?>" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="mood">Mood Hari Ini</label>
                    <select id="mood" name="mood" required>
                        <?php foreach ($moods as $mood_option): ?>
                        <option value="<?php echo $mood_option; ?>" <?php echo ($edit_data && $edit_data['mood'] == $mood_option) ? 'selected' : ''; ?>>
                            <?php echo getMoodEmoji($mood_option) . ' ' . $mood_option; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="content">Isi Diary</label>
                    <textarea id="content" name="content" required><?php echo $edit_data ? htmlspecialchars($edit_data['content']) : ''; ?></textarea>
                </div>
                
                <button type="submit" class="btn-primary">
                    <?php echo $edit_data ? '💾 Update Diary' : '📝 Simpan Diary'; ?>
                </button>
                
                <?php if ($edit_data): ?>
                <a href="diary.php" class="btn-secondary">Batal</a>
                <?php endif; ?>
            </form>
        </div>
        
        <div class="section">
            <h2>Diary Saya (<?php echo count($diaries); ?>)</h2>
            
            <div class="filter-section">
                <label>Filter:</label>
                <select id="mood-filter">
                    <option value="">Semua Mood</option>
                    <?php foreach ($moods as $mood_option): ?>
                    <option value="<?php echo $mood_option; ?>" <?php echo $filter_mood == $mood_option ? 'selected' : ''; ?>>
                        <?php echo getMoodEmoji($mood_option) . ' ' . $mood_option; ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                
                <input type="month" id="month-filter" value="<?php echo $filter_month; ?>">
                
                <button class="btn-filter" onclick="applyFilter()">Terapkan Filter</button>
                <button class="btn-secondary" onclick="window.location.href='diary.php'">Reset</button>
            </div>
            
            <?php if (empty($diaries)): ?>
            <div class="empty-state">
                <p style="font-size: 48px;">📔</p>
                <p>Belum ada diary. Tulis diary pertama Anda hari ini!</p>
            </div>
            <?php else: ?>
            <div class="diary-list">
                <?php foreach ($diaries as $diary): ?>
                <div class="diary-card" style="--mood-color: <?php echo getMoodColor($diary['mood']); ?>">
                    <div class="diary-header">
                        <div>
                            <h3><?php echo htmlspecialchars($diary['title']); ?></h3>
                            <div class="diary-meta">
                                <span>📅 <?php echo date('d M Y', strtotime($diary['diary_date'])); ?></span>
                                <span>🕐 <?php echo date('H:i', strtotime($diary['created_at'])); ?></span>
                            </div>
                        </div>
                        <div class="diary-mood" style="background: <?php echo getMoodColor($diary['mood']); ?>">
                            <?php echo getMoodEmoji($diary['mood']) . ' ' . $diary['mood']; ?>
                        </div>
                    </div>
                    <div class="diary-content">
                        <?php echo nl2br(htmlspecialchars($diary['content'])); ?>
                    </div>
                    <div class="diary-actions">
                        <a href="diary.php?edit=<?php echo $diary['id']; ?>" class="btn-edit">✏️ Edit</a>
                        <form method="POST" action="" style="display: inline;" onsubmit="return confirm('Yakin ingin menghapus diary ini?');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?php echo $diary['id']; ?>">
                            <button type="submit" class="btn-delete">🗑️ Hapus</button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script>
        function applyFilter() {
            const mood = document.getElementById('mood-filter').value;
            const month = document.getElementById('month-filter').value;
            let url = 'diary.php?';
            if (mood) url += 'mood=' + mood + '&';
            if (month) url += 'month=' + month;
            window.location.href = url;
        }
    </script>
</body>
</html>