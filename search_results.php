<?php
session_start();

// Cek apakah user sudah login
$is_logged_in = isset($_SESSION['user_id']);
$user_id = $is_logged_in ? $_SESSION['user_id'] : null;

require_once 'koneksi.php';

// Ambil parameter pencarian
$search_query = trim($_GET['q'] ?? '');
$search_type = $_GET['type'] ?? 'all';
$search_date = $_GET['date'] ?? '';

$results = [];
$total_results = 0;

if (!empty($search_query) && strlen($search_query) >= 3) {
    $search_term = "%{$search_query}%";
    
    // Search di Articles
    if ($search_type == 'all' || $search_type == 'article') {
        $sql = "SELECT id, title, content, category, created_at, 'article' as type 
                FROM articles 
                WHERE (title LIKE ? OR content LIKE ? OR category LIKE ?)";
        
        if ($is_logged_in) {
            $sql .= " AND user_id = ?";
        }
        
        if (!empty($search_date)) {
            $sql .= " AND DATE(created_at) = ?";
        }
        
        $sql .= " ORDER BY created_at DESC LIMIT 20";
        
        $stmt = $conn->prepare($sql);
        
        if ($is_logged_in && !empty($search_date)) {
            $stmt->bind_param("sssss", $search_term, $search_term, $search_term, $user_id, $search_date);
        } elseif ($is_logged_in) {
            $stmt->bind_param("sssi", $search_term, $search_term, $search_term, $user_id);
        } elseif (!empty($search_date)) {
            $stmt->bind_param("ssss", $search_term, $search_term, $search_term, $search_date);
        } else {
            $stmt->bind_param("sss", $search_term, $search_term, $search_term);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $results[] = $row;
        }
        $stmt->close();
    }
    
    // Search di Diary
    if ($search_type == 'all' || $search_type == 'diary') {
        $sql = "SELECT id, title, content, mood, diary_date as created_at, 'diary' as type 
                FROM diary 
                WHERE (title LIKE ? OR content LIKE ? OR mood LIKE ?)";
        
        if ($is_logged_in) {
            $sql .= " AND user_id = ?";
        }
        
        if (!empty($search_date)) {
            $sql .= " AND diary_date = ?";
        }
        
        $sql .= " ORDER BY diary_date DESC LIMIT 20";
        
        $stmt = $conn->prepare($sql);
        
        if ($is_logged_in && !empty($search_date)) {
            $stmt->bind_param("sssss", $search_term, $search_term, $search_term, $user_id, $search_date);
        } elseif ($is_logged_in) {
            $stmt->bind_param("sssi", $search_term, $search_term, $search_term, $user_id);
        } elseif (!empty($search_date)) {
            $stmt->bind_param("ssss", $search_term, $search_term, $search_term, $search_date);
        } else {
            $stmt->bind_param("sss", $search_term, $search_term, $search_term);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $results[] = $row;
        }
        $stmt->close();
    }
    
    // Search di Gallery
    if ($search_type == 'all' || $search_type == 'gallery') {
        $sql = "SELECT id, title, description as content, upload_date as created_at, image_path, 'gallery' as type 
                FROM gallery 
                WHERE (title LIKE ? OR description LIKE ?)";
        
        if ($is_logged_in) {
            $sql .= " AND user_id = ?";
        }
        
        if (!empty($search_date)) {
            $sql .= " AND DATE(upload_date) = ?";
        }
        
        $sql .= " ORDER BY upload_date DESC LIMIT 20";
        
        $stmt = $conn->prepare($sql);
        
        if ($is_logged_in && !empty($search_date)) {
            $stmt->bind_param("ssss", $search_term, $search_term, $user_id, $search_date);
        } elseif ($is_logged_in) {
            $stmt->bind_param("ssi", $search_term, $search_term, $user_id);
        } elseif (!empty($search_date)) {
            $stmt->bind_param("sss", $search_term, $search_term, $search_date);
        } else {
            $stmt->bind_param("ss", $search_term, $search_term);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $results[] = $row;
        }
        $stmt->close();
    }
    
    $total_results = count($results);
}

$conn->close();

// Function untuk highlight search term
function highlightSearchTerm($text, $search) {
    if (empty($search)) return htmlspecialchars($text);
    return preg_replace('/(' . preg_quote($search, '/') . ')/i', '<mark>$1</mark>', htmlspecialchars($text));
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Pencarian - Daily Journal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        body {
            background: #f5f5f5;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .search-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px 0;
        }
        
        .result-card {
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .result-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .result-type {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .type-article { background: #667eea; color: white; }
        .type-diary { background: #20c997; color: white; }
        .type-gallery { background: #ffc107; color: #333; }
        
        mark {
            background-color: #fff59d;
            padding: 2px 4px;
            border-radius: 2px;
        }
        
        .gallery-thumb {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="search-header">
        <div class="container">
            <h1 class="mb-3"><i class="bi bi-search"></i> Hasil Pencarian</h1>
            <p class="mb-0">Menampilkan hasil untuk: <strong>"<?php echo htmlspecialchars($search_query); ?>"</strong></p>
            <?php if ($total_results > 0): ?>
            <p class="mb-0"><small>Ditemukan <?php echo $total_results; ?> hasil</small></p>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="container my-5">
        <div class="row">
            <div class="col-12 mb-4">
                <div class="card">
                    <div class="card-body">
                        <form method="GET" action="" class="row g-3">
                            <div class="col-12 col-md-6">
                                <input type="text" class="form-control" name="q" placeholder="Cari..." value="<?php echo htmlspecialchars($search_query); ?>" required>
                            </div>
                            <div class="col-12 col-md-3">
                                <select class="form-select" name="type">
                                    <option value="all" <?php echo $search_type == 'all' ? 'selected' : ''; ?>>Semua</option>
                                    <option value="article" <?php echo $search_type == 'article' ? 'selected' : ''; ?>>Artikel</option>
                                    <option value="diary" <?php echo $search_type == 'diary' ? 'selected' : ''; ?>>Diary</option>
                                    <option value="gallery" <?php echo $search_type == 'gallery' ? 'selected' : ''; ?>>Gallery</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-3">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-search"></i> Cari
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
            <?php if (empty($search_query)): ?>
            <div class="col-12">
                <div class="alert alert-info text-center">
                    <i class="bi bi-info-circle"></i> Masukkan kata kunci pencarian (minimal 3 karakter)
                </div>
            </div>
            <?php elseif ($total_results == 0): ?>
            <div class="col-12">
                <div class="alert alert-warning text-center">
                    <i class="bi bi-exclamation-triangle"></i> Tidak ada hasil yang ditemukan untuk "<strong><?php echo htmlspecialchars($search_query); ?></strong>"
                    <br><small>Coba gunakan kata kunci yang berbeda</small>
                </div>
            </div>
            <?php else: ?>
            
            <?php foreach ($results as $item): ?>
            <div class="col-12 mb-3">
                <div class="card result-card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="card-title mb-0">
                                <?php echo highlightSearchTerm($item['title'], $search_query); ?>
                            </h5>
                            <span class="result-type type-<?php echo $item['type']; ?>">
                                <?php echo ucfirst($item['type']); ?>
                            </span>
                        </div>
                        
                        <div class="d-flex gap-3">
                            <?php if ($item['type'] == 'gallery' && !empty($item['image_path'])): ?>
                            <img src="<?php echo htmlspecialchars($item['image_path']); ?>" class="gallery-thumb" alt="Thumbnail">
                            <?php endif; ?>
                            
                            <div class="flex-grow-1">
                                <p class="card-text text-muted">
                                    <?php 
                                    $content = $item['content'] ?? '';
                                    $excerpt = mb_substr($content, 0, 200);
                                    echo highlightSearchTerm($excerpt, $search_query); 
                                    echo strlen($content) > 200 ? '...' : '';
                                    ?>
                                </p>
                                
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        <i class="bi bi-calendar3"></i> 
                                        <?php echo date('d M Y', strtotime($item['created_at'])); ?>
                                    </small>
                                    
                                    <?php if ($is_logged_in): ?>
                                    <a href="<?php echo $item['type']; ?>.php?id=<?php echo $item['id']; ?>" class="btn btn-sm btn-outline-primary">
                                        Lihat Detail <i class="bi bi-arrow-right"></i>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
            
            <?php endif; ?>
            
            <div class="col-12 mt-4 text-center">
                <a href="index.html" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Kembali ke Beranda
                </a>
                <?php if ($is_logged_in): ?>
                <a href="dashboard.php" class="btn btn-primary">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
