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

// Handle Create & Update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] == 'create') {
        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        
        // Handle file upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['image']['name'];
            $filetype = pathinfo($filename, PATHINFO_EXTENSION);
            
            if (in_array(strtolower($filetype), $allowed)) {
                $newname = uniqid() . '.' . $filetype;
                $upload_path = 'uploads/' . $newname;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                    $stmt = $conn->prepare("INSERT INTO gallery (user_id, title, description, image_path) VALUES (?, ?, ?, ?)");
                    $stmt->bind_param("isss", $user_id, $title, $description, $upload_path);
                    
                    if ($stmt->execute()) {
                        $message = "Foto berhasil diupload!";
                    } else {
                        $error = "Gagal menyimpan ke database!";
                    }
                    $stmt->close();
                } else {
                    $error = "Gagal mengupload file!";
                }
            } else {
                $error = "Tipe file tidak diizinkan! Hanya JPG, JPEG, PNG, GIF.";
            }
        } else {
            $error = "Pilih file gambar terlebih dahulu!";
        }
    } 
    elseif ($_POST['action'] == 'update') {
        $id = $_POST['id'];
        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';
        
        // Jika ada gambar baru
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $filename = $_FILES['image']['name'];
            $filetype = pathinfo($filename, PATHINFO_EXTENSION);
            
            if (in_array(strtolower($filetype), $allowed)) {
                // Hapus gambar lama
                $stmt = $conn->prepare("SELECT image_path FROM gallery WHERE id = ? AND user_id = ?");
                $stmt->bind_param("ii", $id, $user_id);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($row = $result->fetch_assoc()) {
                    if (file_exists($row['image_path'])) {
                        unlink($row['image_path']);
                    }
                }
                $stmt->close();
                
                // Upload gambar baru
                $newname = uniqid() . '.' . $filetype;
                $upload_path = 'uploads/' . $newname;
                
                if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                    $stmt = $conn->prepare("UPDATE gallery SET title = ?, description = ?, image_path = ? WHERE id = ? AND user_id = ?");
                    $stmt->bind_param("sssii", $title, $description, $upload_path, $id, $user_id);
                    
                    if ($stmt->execute()) {
                        $message = "Data berhasil diupdate!";
                    }
                    $stmt->close();
                }
            }
        } else {
            // Update tanpa gambar baru
            $stmt = $conn->prepare("UPDATE gallery SET title = ?, description = ? WHERE id = ? AND user_id = ?");
            $stmt->bind_param("ssii", $title, $description, $id, $user_id);
            
            if ($stmt->execute()) {
                $message = "Data berhasil diupdate!";
            }
            $stmt->close();
        }
    }
    elseif ($_POST['action'] == 'delete') {
        $id = $_POST['id'];
        
        // Hapus file gambar
        $stmt = $conn->prepare("SELECT image_path FROM gallery WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            if (file_exists($row['image_path'])) {
                unlink($row['image_path']);
            }
        }
        $stmt->close();
        
        // Hapus dari database
        $stmt = $conn->prepare("DELETE FROM gallery WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $id, $user_id);
        
        if ($stmt->execute()) {
            $message = "Foto berhasil dihapus!";
        }
        $stmt->close();
    }
}

// Read - Ambil semua gallery milik user
$stmt = $conn->prepare("SELECT * FROM gallery WHERE user_id = ? ORDER BY upload_date DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$galleries = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Ambil data untuk edit jika ada
$edit_data = null;
if (isset($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    $stmt = $conn->prepare("SELECT * FROM gallery WHERE id = ? AND user_id = ?");
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
    <title>Gallery - Daily Journal</title>
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
        .form-group textarea,
        .form-group input[type="file"] {
            width: 100%;
            padding: 12px;
            border: 2px solid #e1e1e1;
            border-radius: 5px;
            font-size: 14px;
            font-family: inherit;
        }
        
        .form-group textarea {
            min-height: 100px;
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
        
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .gallery-item {
            background: white;
            border: 2px solid #e1e1e1;
            border-radius: 8px;
            overflow: hidden;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        
        .gallery-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .gallery-item img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        
        .gallery-info {
            padding: 15px;
        }
        
        .gallery-info h3 {
            color: #333;
            margin-bottom: 8px;
            font-size: 16px;
        }
        
        .gallery-info p {
            color: #666;
            font-size: 14px;
            margin-bottom: 10px;
            line-height: 1.5;
        }
        
        .gallery-date {
            font-size: 12px;
            color: #999;
            margin-bottom: 10px;
        }
        
        .gallery-actions {
            display: flex;
            gap: 10px;
        }
        
        .btn-edit {
            background: #ffc107;
            color: #333;
            padding: 6px 12px;
            border: none;
            border-radius: 5px;
            font-size: 13px;
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
            padding: 6px 12px;
            border: none;
            border-radius: 5px;
            font-size: 13px;
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
        
        .preview-image {
            max-width: 200px;
            margin-top: 10px;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="navbar">
        <h1>📷 Gallery</h1>
        <div class="navbar-right">
            <div class="nav-menu">
                <a href="dashboard.php">Dashboard</a>
                <a href="article.php">Article</a>
                <a href="diary.php">Diary</a>
                <a href="gallery.php" class="active">Gallery</a>
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
            <h2><?php echo $edit_data ? 'Edit Foto' : 'Upload Foto Baru'; ?></h2>
            <form method="POST" action="" enctype="multipart/form-data">
                <input type="hidden" name="action" value="<?php echo $edit_data ? 'update' : 'create'; ?>">
                <?php if ($edit_data): ?>
                <input type="hidden" name="id" value="<?php echo $edit_data['id']; ?>">
                <?php endif; ?>
                
                <div class="form-group">
                    <label for="title">Judul Foto</label>
                    <input type="text" id="title" name="title" value="<?php echo $edit_data ? htmlspecialchars($edit_data['title']) : ''; ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="description">Deskripsi</label>
                    <textarea id="description" name="description"><?php echo $edit_data ? htmlspecialchars($edit_data['description']) : ''; ?></textarea>
                </div>
                
                <div class="form-group">
                    <label for="image">Pilih Gambar (JPG, JPEG, PNG, GIF)</label>
                    <input type="file" id="image" name="image" accept="image/*" <?php echo $edit_data ? '' : 'required'; ?>>
                    <?php if ($edit_data && $edit_data['image_path']): ?>
                    <img src="<?php echo htmlspecialchars($edit_data['image_path']); ?>" class="preview-image" alt="Current">
                    <p style="font-size: 12px; color: #666; margin-top: 5px;">Gambar saat ini. Biarkan kosong jika tidak ingin mengubah.</p>
                    <?php endif; ?>
                </div>
                
                <button type="submit" class="btn-primary">
                    <?php echo $edit_data ? '💾 Update Foto' : '📤 Upload Foto'; ?>
                </button>
                
                <?php if ($edit_data): ?>
                <a href="gallery.php" class="btn-secondary">Batal</a>
                <?php endif; ?>
            </form>
        </div>
        
        <div class="section">
            <h2>Gallery Saya (<?php echo count($galleries); ?> Foto)</h2>
            
            <?php if (empty($galleries)): ?>
            <div class="empty-state">
                <p style="font-size: 48px;">📷</p>
                <p>Belum ada foto. Upload foto pertama Anda!</p>
            </div>
            <?php else: ?>
            <div class="gallery-grid">
                <?php foreach ($galleries as $item): ?>
                <div class="gallery-item">
                    <img src="<?php echo htmlspecialchars($item['image_path']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>">
                    <div class="gallery-info">
                        <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                        <?php if ($item['description']): ?>
                        <p><?php echo htmlspecialchars($item['description']); ?></p>
                        <?php endif; ?>
                        <div class="gallery-date">
                            📅 <?php echo date('d M Y, H:i', strtotime($item['upload_date'])); ?>
                        </div>
                        <div class="gallery-actions">
                            <a href="gallery.php?edit=<?php echo $item['id']; ?>" class="btn-edit">✏️ Edit</a>
                            <form method="POST" action="" style="display: inline;" onsubmit="return confirm('Yakin ingin menghapus foto ini?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                                <button type="submit" class="btn-delete">🗑️ Hapus</button>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>