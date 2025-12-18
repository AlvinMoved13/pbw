<?php
session_start();

// Cek apakah user sudah login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'koneksi.php';

$user_id = $_SESSION['user_id'];
$message = '';
$error = '';

// Handle upload
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'gif'];
        $filename = $_FILES['image']['name'];
        $filetype = pathinfo($filename, PATHINFO_EXTENSION);
        $filesize = $_FILES['image']['size'];
        
        // Validasi tipe file
        if (!in_array(strtolower($filetype), $allowed)) {
            $error = "Tipe file tidak diizinkan! Hanya JPG, JPEG, PNG, GIF.";
        }
        // Validasi ukuran file (max 5MB)
        elseif ($filesize > 5242880) {
            $error = "Ukuran file terlalu besar! Maksimal 5MB.";
        }
        else {
            // Buat folder uploads jika belum ada
            if (!file_exists('uploads')) {
                mkdir('uploads', 0755, true);
            }
            
            $newname = uniqid() . '.' . $filetype;
            $upload_path = 'uploads/' . $newname;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                $stmt = $conn->prepare("INSERT INTO gallery (user_id, title, description, image_path) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("isss", $user_id, $title, $description, $upload_path);
                
                if ($stmt->execute()) {
                    $message = "Foto berhasil diupload!";
                    header("Location: gallery.php?success=uploaded");
                    exit();
                } else {
                    $error = "Gagal menyimpan ke database!";
                    // Hapus file yang sudah diupload
                    unlink($upload_path);
                }
                $stmt->close();
            } else {
                $error = "Gagal mengupload file!";
            }
        }
    } else {
        $error = "Pilih file gambar terlebih dahulu!";
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Foto - Daily Journal</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        
        .upload-container {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 500px;
        }
        
        .upload-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .upload-header h1 {
            color: #333;
            font-size: 28px;
            margin-bottom: 5px;
        }
        
        .upload-header p {
            color: #666;
            font-size: 14px;
        }
        
        .message {
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .message.error {
            background: #fee;
            color: #c33;
            border-left: 3px solid #c33;
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
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e1e1e1;
            border-radius: 5px;
            font-size: 14px;
            font-family: inherit;
        }
        
        .form-group textarea {
            min-height: 80px;
            resize: vertical;
        }
        
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .file-upload {
            border: 2px dashed #667eea;
            border-radius: 5px;
            padding: 30px;
            text-align: center;
            cursor: pointer;
            transition: background 0.3s;
        }
        
        .file-upload:hover {
            background: #f8f9fa;
        }
        
        .file-upload input[type="file"] {
            display: none;
        }
        
        .file-upload-label {
            cursor: pointer;
            color: #667eea;
            font-weight: 500;
        }
        
        .file-info {
            font-size: 12px;
            color: #999;
            margin-top: 10px;
        }
        
        .preview-image {
            max-width: 100%;
            margin-top: 15px;
            border-radius: 5px;
            display: none;
        }
        
        .btn-primary {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
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
            width: 100%;
            padding: 10px;
            background: #6c757d;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            cursor: pointer;
            text-decoration: none;
            display: block;
            text-align: center;
            margin-top: 10px;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
        }
    </style>
</head>
<body>
    <div class="upload-container">
        <div class="upload-header">
            <h1>📤 Upload Foto</h1>
            <p>Tambahkan foto ke gallery Anda</p>
        </div>
        
        <?php if ($error): ?>
        <div class="message error">
            ✗ <?php echo htmlspecialchars($error); ?>
        </div>
        <?php endif; ?>
        
        <form method="POST" action="" enctype="multipart/form-data">
            <div class="form-group">
                <label for="title">Judul Foto *</label>
                <input type="text" id="title" name="title" required>
            </div>
            
            <div class="form-group">
                <label for="description">Deskripsi</label>
                <textarea id="description" name="description"></textarea>
            </div>
            
            <div class="form-group">
                <label>Pilih Gambar *</label>
                <div class="file-upload" onclick="document.getElementById('image').click()">
                    <label class="file-upload-label">
                        📷 Klik untuk memilih gambar
                    </label>
                    <div class="file-info">
                        Format: JPG, JPEG, PNG, GIF | Maksimal: 5MB
                    </div>
                    <input type="file" id="image" name="image" accept="image/*" required onchange="previewImage(event)">
                    <img id="preview" class="preview-image" alt="Preview">
                </div>
            </div>
            
            <button type="submit" class="btn-primary">📤 Upload Foto</button>
            <a href="gallery.php" class="btn-secondary">← Kembali ke Gallery</a>
        </form>
    </div>
    
    <script>
        function previewImage(event) {
            const file = event.target.files[0];
            const preview = document.getElementById('preview');
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(file);
            }
        }
    </script>
</body>
</html>
