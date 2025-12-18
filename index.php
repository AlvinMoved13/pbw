<?php
require_once 'koneksi.php';

// Ambil 3 artikel terbaru
$articles = [];
$stmt = $conn->prepare("SELECT id, title, content, created_at FROM articles ORDER BY created_at DESC LIMIT 3");
if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    $articles = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// Ambil 3 gallery terbaru
$galleries = [];
$stmt = $conn->prepare("SELECT id, title, description, image_path, upload_date FROM gallery ORDER BY upload_date DESC LIMIT 3");
if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    $galleries = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// Ambil 3 diary terbaru
$diaries = [];
$stmt = $conn->prepare("SELECT id, title, content, mood, created_at FROM diary ORDER BY created_at DESC LIMIT 3");
if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    $diaries = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// Ambil semua data untuk autocomplete
$all_articles = [];
$stmt = $conn->prepare("SELECT id, title FROM articles ORDER BY created_at DESC");
if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    $all_articles = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

$all_diaries = [];
$stmt = $conn->prepare("SELECT id, title FROM diary ORDER BY created_at DESC");
if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    $all_diaries = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

$all_galleries = [];
$stmt = $conn->prepare("SELECT id, title FROM gallery ORDER BY upload_date DESC");
if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    $all_galleries = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

$conn->close();

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
?>
<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Journal - Alvin Mufidha Ahmad</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="icon" href="https://cdn-icons-png.flaticon.com/512/3652/3652191.png">
    <style>
        /* make clickable cards obvious */
        .clickable-article{ cursor: pointer; }
        .clickable-article:hover{ box-shadow: 0 6px 18px rgba(0,0,0,.12); transform: translateY(-3px); transition: .18s; }

        /* detail view - hidden by default */
        #article-detail{ display: none; }
        #article-detail.show{ display: block; }
        .detail-img{ max-width:100%; height:auto; border-radius:6px; }
        
        /* hover effect for diary cards */
        .hover-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .hover-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0,0,0,.15);
        }
        
        /* Autocomplete styling */
        .autocomplete-items {
            position: absolute;
            border: 1px solid #d4d4d4;
            border-bottom: none;
            border-top: none;
            z-index: 99;
            top: 100%;
            left: 0;
            right: 0;
            max-height: 300px;
            overflow-y: auto;
            background-color: white;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .autocomplete-items div {
            padding: 10px;
            cursor: pointer;
            background-color: #fff;
            border-bottom: 1px solid #d4d4d4;
        }
        .autocomplete-items div:hover {
            background-color: #e9e9e9;
        }
        .autocomplete-active {
            background-color: #007bff !important;
            color: #ffffff;
        }
        .autocomplete-type {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 11px;
            margin-left: 8px;
            font-weight: 600;
        }
        .type-article {
            background-color: #007bff;
            color: white;
        }
        .type-diary {
            background-color: #28a745;
            color: white;
        }
        .type-gallery {
            background-color: #ffc107;
            color: #333;
        }
    </style>
    <!-- project stylesheet -->
    <!-- Google Fonts: Poppins -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg bg-body-tertiary sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="#">My Daily Journal</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto text-dark">
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="#">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#article">Artikel</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#diary">Diary</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#gallery">Galeri</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#schedule">Jadwal</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about">Tentang Saya</a>
                    </li>
                </ul>
            </div>
            <!-- Theme buttons: Bright and Dark -->
            <div class="d-flex align-items-center gap-2 ms-3">
                <button id="btn-bright" class="btn btn-sm btn-outline-secondary" title="Bright mode">Bright</button>
                <button id="btn-dark" class="btn btn-sm btn-dark" title="Dark mode">Dark</button>
                <a href="login.php" class="btn btn-sm btn-primary ms-2">
                    <i class="bi bi-box-arrow-in-right"></i> Login
                </a>
            </div>
        </div>
    </nav>
    <!-- End Navbar -->

    <!-- Hero Section -->
    <section id="hero" class="text-center p-5 bg-danger-subtle text-sm-start min-vh-100 d-flex align-items-center">
        <div class="container">
            <div class="d-sm-flex flex-sm-row-reverse align-items-center">
                <img src="https://images.unsplash.com/photo-1517842645767-c639042777db?w=500&h=500&fit=crop"
                    class="img-fluid" width="300" alt="Journal">
                <div>
                    <h1 class="fw-bold display-4">My Daily Journal</h1>
                    <p class="mt-3">Mencatat semua kegiatan sehari-hari</p>
                    <!-- Clock and date -->
                    <div id="clock" class="mt-3 text-muted" aria-live="polite">
                        <strong id="time">--:--:--</strong>
                        <div><small id="date">--</small></div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- End Hero -->

    <!-- Search Section -->
    <section id="search" class="p-5 bg-light min-vh-100 d-flex align-items-center">
        <div class="container">
            <div class="row justify-content-center align-items-center">
                <div class="col-12 col-lg-8">
                    <div class="card shadow-lg border-0">
                        <div class="card-body p-5">
                            <h2 class="text-center mb-4 fw-bold">
                                <i class="bi bi-search"></i> Cari Konten
                            </h2>
                            <form action="search_results.php" method="GET" class="needs-validation" novalidate>
                                <div class="row g-4">
                                    <div class="col-12">
                                        <div class="position-relative">
                                            <div class="input-group input-group-lg">
                                                <span class="input-group-text bg-primary text-white">
                                                    <i class="bi bi-search"></i>
                                                </span>
                                                <input type="text" class="form-control" name="q" id="searchInput"
                                                       placeholder="Cari artikel, diary, atau galeri..." 
                                                       required minlength="1" autocomplete="off">
                                                <button class="btn btn-primary px-4" type="submit">
                                                    <i class="bi bi-search"></i> Cari
                                                </button>
                                            </div>
                                            <div id="autocomplete-list" class="autocomplete-items"></div>
                                        </div>
                                    </div>
                                    
                                    <div class="col-12 col-md-6">
                                        <label for="search-type" class="form-label fw-semibold">
                                            <i class="bi bi-funnel"></i> Tipe Konten
                                        </label>
                                        <select class="form-select form-select-lg" id="search-type" name="type">
                                            <option value="all" selected>Semua</option>
                                            <option value="article">Artikel</option>
                                            <option value="diary">Diary</option>
                                            <option value="gallery">Gallery</option>
                                        </select>
                                    </div>
                                    
                                    <div class="col-12 col-md-6">
                                        <label for="search-date" class="form-label fw-semibold">
                                            <i class="bi bi-calendar"></i> Filter Tanggal
                                        </label>
                                        <input type="date" class="form-select form-select-lg" id="search-date" name="date">
                                    </div>
                                </div>
                            </form>
                            
                            <!-- Statistics Summary -->
                            <div class="mt-5">
                                <h5 class="mb-3 fw-semibold">
                                    <i class="bi bi-database"></i> Hasil Data Tersedia
                                </h5>
                                <div class="row g-3 text-center">
                                    <div class="col-4">
                                        <div class="card bg-primary bg-gradient text-white">
                                            <div class="card-body">
                                                <h2 class="mb-0"><?php echo count($all_articles); ?></h2>
                                                <p class="mb-0 small">Artikel</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="card bg-success bg-gradient text-white">
                                            <div class="card-body">
                                                <h2 class="mb-0"><?php echo count($all_diaries); ?></h2>
                                                <p class="mb-0 small">Diary</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="card bg-warning bg-gradient text-white">
                                            <div class="card-body">
                                                <h2 class="mb-0"><?php echo count($all_galleries); ?></h2>
                                                <p class="mb-0 small">Galeri</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- End Search -->

    <!-- Article Section (two-column: article list + gallery) -->
    <section id="article" class="p-5 min-vh-100">
        <div class="container">
            <h1 class="fw-bold display-4 pb-3 text-center">Artikel</h1>
            <div class="row g-4 align-items-start">
                <div class="col-12 col-sm-8">
                    <div class="card p-4">
                        <ol class="article-list list-unstyled mb-0">
                            <?php if (empty($articles)): ?>
                                <li class="text-center text-muted py-4">
                                    <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                                    <p class="mt-3">Belum ada artikel tersedia.</p>
                                </li>
                            <?php else: ?>
                                <?php $counter = 1; foreach ($articles as $article): ?>
                                <li class="mb-4 article-item clickable-article" 
                                    data-title="<?php echo htmlspecialchars($article['title']); ?>" 
                                    data-date="<?php echo date('l, d F Y', strtotime($article['created_at'])); ?>" 
                                    data-image="https://picsum.photos/seed/article<?php echo $article['id']; ?>/800/400" 
                                    data-body="<?php echo htmlspecialchars($article['content']); ?>" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#articleModal">
                                    <h5><strong><?php echo $counter++; ?>. <?php echo htmlspecialchars($article['title']); ?></strong></h5>
                                    <p class="mb-0"><?php echo htmlspecialchars(substr($article['content'], 0, 100)); ?><?php echo strlen($article['content']) > 100 ? '...' : ''; ?></p>
                                    <small class="text-primary"><i class="bi bi-eye"></i> Klik untuk detail</small>
                                </li>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </ol>
                    </div>
                </div>
                <div class="col-12 col-sm-4">
                    <div class="card p-3 text-center">
                        <h5 class="mb-3">Galeri</h5>
                        <div class="d-flex flex-column align-items-center gap-3">
                            <?php if (empty($galleries)): ?>
                                <p class="text-muted">Belum ada foto</p>
                            <?php else: ?>
                                <?php foreach ($galleries as $gallery): ?>
                                    <img src="<?php echo htmlspecialchars($gallery['image_path']); ?>" 
                                         class="img-fluid rounded" 
                                         alt="<?php echo htmlspecialchars($gallery['title']); ?>" 
                                         style="width:120px; height:auto;">
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- End Article -->

    <!-- Diary Section -->
    <section id="diary" class="p-5 bg-light min-vh-100">
        <div class="container">
            <h1 class="fw-bold display-4 pb-3 text-center">📔 Diary Saya</h1>
            <div class="row g-4">
                <?php if (empty($diaries)): ?>
                    <div class="col-12 text-center text-muted py-5">
                        <i class="bi bi-journal-x" style="font-size: 4rem;"></i>
                        <p class="mt-3 h5">Belum ada diary tersedia.</p>
                        <a href="diary.php" class="btn btn-primary mt-3">Buat Diary Pertama</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($diaries as $diary): ?>
                    <div class="col-12 col-md-4">
                        <div class="card h-100 shadow-sm hover-card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <span class="badge bg-primary"><?php echo getMoodEmoji($diary['mood']); ?> <?php echo htmlspecialchars($diary['mood']); ?></span>
                                    <small class="text-muted">
                                        <i class="bi bi-calendar3"></i> <?php echo date('d M Y', strtotime($diary['created_at'])); ?>
                                    </small>
                                </div>
                                <h5 class="card-title"><?php echo htmlspecialchars($diary['title']); ?></h5>
                                <p class="card-text text-muted">
                                    <?php echo htmlspecialchars(substr($diary['content'], 0, 150)); ?><?php echo strlen($diary['content']) > 150 ? '...' : ''; ?>
                                </p>
                                <div class="d-flex justify-content-between align-items-center mt-3">
                                    <small class="text-muted">
                                        <i class="bi bi-clock"></i> <?php echo date('H:i', strtotime($diary['created_at'])); ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>
    <!-- End Diary -->


    <!-- Gallery Section -->
    <section id="gallery" class="text-center p-5 bg-danger-subtle min-vh-100 d-flex align-items-center">
        <div class="container">
            <h1 class="fw-bold display-4 pb-3">Galeri</h1>
            <?php if (empty($galleries)): ?>
                <div class="alert alert-info">
                    <i class="bi bi-images" style="font-size: 3rem;"></i>
                    <p class="mt-3">Belum ada foto di galeri.</p>
                    <a href="gallery.php" class="btn btn-primary">Upload Foto</a>
                </div>
            <?php else: ?>
            <div id="carouselExample" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-inner">
                    <?php $first = true; foreach ($galleries as $gallery): ?>
                    <div class="carousel-item <?php echo $first ? 'active' : ''; ?>">
                        <img src="<?php echo htmlspecialchars($gallery['image_path']); ?>"
                            class="d-block w-100" alt="<?php echo htmlspecialchars($gallery['title']); ?>" style="max-height: 500px; object-fit: cover;">
                        <div class="carousel-caption d-none d-md-block bg-dark bg-opacity-50 rounded p-3">
                            <h5><?php echo htmlspecialchars($gallery['title']); ?></h5>
                            <p><?php echo htmlspecialchars($gallery['description']); ?></p>
                        </div>
                    </div>
                    <?php $first = false; endforeach; ?>
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#carouselExample"
                    data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#carouselExample"
                    data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
            </div>
            <?php endif; ?>
        </div>
    </section>
    <!-- End Gallery -->

    <!-- Schedule Section -->
    <section id="schedule" class="p-5 min-vh-100 d-flex align-items-center">
        <div class="container">
            <h1 class="fw-bold display-5 pb-3 text-center">Schedule</h1>
            <div class="row g-4 align-items-stretch">
                <div class="col-12 col-md-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title text-center mb-3 ">Hari</h5>
                            <ul class="list-unstyled mb-0 small flex-grow-1">
                                <li class="py-2 border-bottom">Senin</li>
                                <li class="py-2 border-bottom">Jumat</li>
                                <li class="py-2">Jumat</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title text-center mb-3">Waktu</h5>
                            <ul class="list-unstyled mb-0 small flex-grow-1">
                                <li class="py-2 border-bottom">14.30</li>
                                <li class="py-2 border-bottom">08.40</li>
                                <li class="py-2">13.00</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title text-center mb-3">Kegiatan dan Lokasi</h5>
                            <ul class="list-unstyled mb-0 small flex-grow-1">
                                <li class="py-2 border-bottom">Bimbingan Tugas Akhir 2 — di H.2, Ruang Dosen</li>
                                <li class="py-2 border-bottom">Mata Kuliah Pemrograman Berbasis Web — di D.2.J</li>
                                <li class="py-2">Mata Kuliah Logika Informatika — di H.3.8</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- End Schedule -->

    <!-- Tentang Saya Section -->
    <section id="about" class="p-5 bg-body-tertiary">
        <div class="container">
            <h1 class="fw-bold display-5 pb-3 text-center">Tentang Saya</h1>
            <div class="row g-4 align-items-center">
                <div class="col-12 col-md-4 text-center">
                    <img src="https://www.qalvin.dev/static/media/about.234f5c3b989650998b16.png" alt="Foto Profil" class="img-fluid rounded-circle" style="width:180px; height:180px; object-fit:cover;">
                </div>
                <div class="col-12 col-md-8">
                    <div class="card border-0">
                        <div class="card-body p-3 about-details text-center text-md-start">
                            <h5 class="mb-2">Alvin Mufidha Ahmad</h5>
                            <ul class="list-unstyled mb-3">
                                <li><strong>NIM:</strong> A11.2020.13071</li>
                                <li><strong>Program Studi:</strong> Teknik Informatika</li>
                                <li><strong>Universitas:</strong> <a href="https://www.udinus.ac.id" target="_blank" rel="noopener" class="link-primary">Universitas Dian Nuswantoro (UDINUS)</a></li>
                            </ul>
                            <p class="text-muted mb-3">Mahasiswa yang antusias di bidang pengembangan web.</p>

                            <div class="d-flex gap-2 justify-content-center justify-content-md-start">
                                <a href="#gallery" class="btn btn-outline-primary btn-sm"><i class="bi bi-image"></i> Lihat Galeri</a>
                                <a href="#schedule" class="btn btn-primary btn-sm"><i class="bi bi-calendar3"></i> Lihat Jadwal</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- End Tentang Saya -->

    <!-- Footer -->
    <footer id="footer" class="text-center p-5">
        <div>
            <a href="https://instagram.com" class="text-dark"><i class="bi bi-instagram h2 p-2"></i></a>
            <a href="https://twitter.com" class="text-dark"><i class="bi bi-twitter h2 p-2"></i></a>
            <a href="https://wa.me" class="text-dark"><i class="bi bi-whatsapp h2 p-2"></i></a>
        </div>
        <div>
            <p class="mt-3">&copy; 2025 My Daily Journal. All Rights Reserved.</p>
        </div>
    </footer>
    <!-- End Footer -->

    <!-- Modal untuk Detail Artikel (Bootstrap Modal Component) -->
    <div class="modal fade" id="articleModal" tabindex="-1" aria-labelledby="articleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="articleModalLabel">Detail Artikel</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <img id="modalImage" src="" class="img-fluid rounded detail-img" alt="Article Image" style="max-height: 400px;">
                    </div>
                    <h4 id="modalTitle" class="mb-2"></h4>
                    <p class="text-muted"><small><i class="bi bi-calendar3"></i> <span id="modalDate"></span></small></p>
                    <hr>
                    <p id="modalBody" class="lead"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i> Tutup
                    </button>
                    <button type="button" class="btn btn-primary" onclick="shareArticle()">
                        <i class="bi bi-share"></i> Bagikan
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Toast Notification (Bootstrap Toast Component) -->
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
        <div id="welcomeToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="3000">
            <div class="toast-header bg-primary text-white">
                <i class="bi bi-info-circle me-2"></i>
                <strong class="me-auto">Selamat Datang!</strong>
                <small>Baru saja</small>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                Terima kasih telah mengunjungi My Daily Journal. Jelajahi artikel dan galeri menarik!
            </div>
        </div>
        
        <div id="shareToast" class="toast" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="2000">
            <div class="toast-header bg-success text-white">
                <i class="bi bi-check-circle me-2"></i>
                <strong class="me-auto">Berhasil!</strong>
                <small>Baru saja</small>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">
                Link artikel telah disalin ke clipboard!
            </div>
        </div>
    </div>

    <script>
        // Autocomplete data from PHP
        const autocompleteData = {
            articles: <?php echo json_encode($all_articles); ?>,
            diaries: <?php echo json_encode($all_diaries); ?>,
            galleries: <?php echo json_encode($all_galleries); ?>
        };

        // Main DOM ready handler: article detail, theme toggle, and live clock
        document.addEventListener('DOMContentLoaded', function(){
            /* === Autocomplete functionality === */
            const searchInput = document.getElementById('searchInput');
            const autocompleteList = document.getElementById('autocomplete-list');
            let currentFocus = -1;

            // Show all items when input is focused
            searchInput.addEventListener('focus', function() {
                showAllItems();
            });

            // Filter items as user types
            searchInput.addEventListener('input', function() {
                const val = this.value.toLowerCase();
                closeAllLists();
                if (!val) {
                    showAllItems();
                    return;
                }
                filterItems(val);
            });

            function showAllItems() {
                closeAllLists();
                currentFocus = -1;
                
                // Add articles
                autocompleteData.articles.forEach(item => {
                    addAutocompleteItem(item.title, 'article', 'Artikel');
                });
                
                // Add diaries
                autocompleteData.diaries.forEach(item => {
                    addAutocompleteItem(item.title, 'diary', 'Diary');
                });
                
                // Add galleries
                autocompleteData.galleries.forEach(item => {
                    addAutocompleteItem(item.title, 'gallery', 'Galeri');
                });
            }

            function filterItems(val) {
                currentFocus = -1;
                
                // Filter articles
                autocompleteData.articles.forEach(item => {
                    if (item.title.toLowerCase().includes(val)) {
                        addAutocompleteItem(item.title, 'article', 'Artikel');
                    }
                });
                
                // Filter diaries
                autocompleteData.diaries.forEach(item => {
                    if (item.title.toLowerCase().includes(val)) {
                        addAutocompleteItem(item.title, 'diary', 'Diary');
                    }
                });
                
                // Filter galleries
                autocompleteData.galleries.forEach(item => {
                    if (item.title.toLowerCase().includes(val)) {
                        addAutocompleteItem(item.title, 'gallery', 'Galeri');
                    }
                });
            }

            function addAutocompleteItem(title, type, typeLabel) {
                const div = document.createElement('div');
                const typeClass = type === 'article' ? 'type-article' : type === 'diary' ? 'type-diary' : 'type-gallery';
                div.innerHTML = `${title}<span class="autocomplete-type ${typeClass}">${typeLabel}</span>`;
                div.addEventListener('click', function() {
                    searchInput.value = title;
                    closeAllLists();
                });
                autocompleteList.appendChild(div);
            }

            function closeAllLists() {
                autocompleteList.innerHTML = '';
            }

            // Close autocomplete when clicking outside
            document.addEventListener('click', function(e) {
                if (e.target !== searchInput) {
                    closeAllLists();
                }
            });

            // Keyboard navigation
            searchInput.addEventListener('keydown', function(e) {
                let items = autocompleteList.getElementsByTagName('div');
                if (e.keyCode === 40) { // Down arrow
                    currentFocus++;
                    addActive(items);
                } else if (e.keyCode === 38) { // Up arrow
                    currentFocus--;
                    addActive(items);
                } else if (e.keyCode === 13) { // Enter
                    e.preventDefault();
                    if (currentFocus > -1 && items[currentFocus]) {
                        items[currentFocus].click();
                    }
                }
            });

            function addActive(items) {
                if (!items) return false;
                removeActive(items);
                if (currentFocus >= items.length) currentFocus = 0;
                if (currentFocus < 0) currentFocus = items.length - 1;
                items[currentFocus].classList.add('autocomplete-active');
                items[currentFocus].scrollIntoView({ block: 'nearest' });
            }

            function removeActive(items) {
                for (let i = 0; i < items.length; i++) {
                    items[i].classList.remove('autocomplete-active');
                }
            }

            /* === Bootstrap Form Validation (Materi 1) === */
            const forms = document.querySelectorAll('.needs-validation');
            Array.from(forms).forEach(form => {
                form.addEventListener('submit', event => {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });

            /* === Bootstrap Modal untuk Article Detail (Materi 2) === */
            const articleItems = document.querySelectorAll('.article-item');
            const modalTitle = document.getElementById('modalTitle');
            const modalDate = document.getElementById('modalDate');
            const modalBody = document.getElementById('modalBody');
            const modalImage = document.getElementById('modalImage');

            articleItems.forEach(item => {
                item.addEventListener('click', function() {
                    const title = this.getAttribute('data-title');
                    const date = this.getAttribute('data-date');
                    const body = this.getAttribute('data-body');
                    const image = this.getAttribute('data-image');

                    modalTitle.textContent = title;
                    modalDate.textContent = date;
                    modalBody.textContent = body;
                    modalImage.src = image;
                });
            });

            /* === Bootstrap Toast Notification (Materi 3) === */
            // Show welcome toast when page loads
            const welcomeToast = new bootstrap.Toast(document.getElementById('welcomeToast'));
            setTimeout(() => {
                welcomeToast.show();
            }, 1000);

            const articleSection = document.getElementById('article');

            /* === theme toggle (bright/dark) === */
            const btnDark = document.getElementById('btn-dark');
            const btnBright = document.getElementById('btn-bright');
            const themeKey = 'preferred-theme';

            function applyTheme(theme){
                if(theme === 'dark'){
                    document.documentElement.classList.add('theme-dark');
                    btnDark.classList.add('active');
                    btnBright.classList.remove('active');
                } else {
                    document.documentElement.classList.remove('theme-dark');
                    btnDark.classList.remove('active');
                    btnBright.classList.add('active');
                }
            }

            // load saved preference
            const saved = localStorage.getItem(themeKey) || 'bright';
            applyTheme(saved);

            btnDark.addEventListener('click', function(){
                applyTheme('dark');
                localStorage.setItem(themeKey, 'dark');
            });
            btnBright.addEventListener('click', function(){
                applyTheme('bright');
                localStorage.setItem(themeKey, 'bright');
            });

            /* === live clock & date in hero === */
            const timeEl = document.getElementById('time');
            const dateEl = document.getElementById('date');

            function updateClock(){
                const now = new Date();
                // Indonesian locale formatting
                const timeStr = now.toLocaleTimeString('id-ID');
                const dateStr = now.toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
                if(timeEl) timeEl.textContent = timeStr;
                if(dateEl) dateEl.textContent = dateStr;
            }
            updateClock();
            setInterval(updateClock, 1000);
        });

        /* === Function untuk Share Article dengan Toast === */
        function shareArticle() {
            const title = document.getElementById('modalTitle').textContent;
            const url = window.location.href;
            
            // Copy to clipboard
            const textToCopy = `${title} - ${url}`;
            navigator.clipboard.writeText(textToCopy).then(() => {
                // Show success toast
                const shareToast = new bootstrap.Toast(document.getElementById('shareToast'));
                shareToast.show();
            }).catch(err => {
                alert('Gagal menyalin link');
            });
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>