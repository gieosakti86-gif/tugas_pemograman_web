<?php
session_start();
require_once __DIR__ . '/db_config.php';
$loggedIn = !empty($_SESSION['user_id']);
$username = $_SESSION['username'] ?? 'Guest';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-DocSmart | Manajemen Dokumen & TTD Digital</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <nav class="col-md-2 d-none d-md-block sidebar text-white p-3">
                <h4 class="text-center mb-4"><i class="fa-solid fa-file-signature text-warning"></i> E-DocSmart</h4>
                <div class="text-center mb-4 p-2 bg-secondary rounded text-xs">
                    <small><i class="fa-solid fa-user-circle"></i> Login sebagai: <strong id="userStatus"><?php echo htmlspecialchars($username); ?></strong></small>
                </div>
                <hr>
                <ul class="nav flex-column gap-2">
                    <li class="nav-item"><a class="nav-link text-white active" href="#"><i class="fa-solid fa-gauge me-2"></i> Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="#" id="btnAddDocumentSidebar"><i class="fa-solid fa-cloud-arrow-up me-2"></i> Upload Dokumen</a></li>
                    <li class="nav-item"><a class="nav-link text-white" href="#" data-bs-toggle="modal" data-bs-target="#documentModal"><i class="fa-solid fa-pen-nib me-2"></i> TTD Digital</a></li>
                    <li class="nav-item mt-4"><a class="nav-link text-danger" href="logout.php" id="logoutBtn"><i class="fa-solid fa-right-from-bracket me-2"></i> Logout</a></li>
                </ul>
            </nav>

            <main class="col-md-10 ms-sm-auto px-md-4 py-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <div>
                        <h1 class="h2">Dashboard Manajemen Dokumen</h1>
                        <p class="text-muted mb-0">Sistem manajemen arsip terintegrasi multimedia dan tanda tangan digital.</p>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card shadow-sm media-card">
                            <div class="card-header"><i class="fa-solid fa-video me-2"></i> Video Panduan Pengisian</div>
                            <div class="card-body p-0 video-card">
                                <div class="ratio ratio-16x9">
                                    <iframe src="https://www.youtube.com/embed/97dkzVU4p-M" title="Panduan Pengisian" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                                </div>
                            </div>
                            <div class="card-footer bg-white">
                                <small class="text-muted">Video tutorial responsif untuk mengedukasi user.</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card shadow-sm h-100">
                            <div class="card-header bg-warning text-dark fw-bold"><i class="fa-solid fa-circle-info me-2"></i> Ringkasan Fitur</div>
                            <div class="card-body">
                                <ul>
                                    <li>Sistem autentikasi dengan <strong>session</strong>.</li>
                                    <li>CRUD dokumen dengan <strong>multiple file upload</strong> dan validasi file.</li>
                                    <li>DataTable interaktif dengan <strong>search</strong>, <strong>sorting</strong>, dan <strong>pagination</strong>.</li>
                                    <li>HTML5 Canvas untuk tanda tangan digital dalam form.</li>
                                    <li>Modal Bootstrap untuk tambah, edit, dan konfirmasi hapus.</li>
                                    <li>Feedback visual + audio sukses untuk aksi berhasil.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                        <span><i class="fa-solid fa-table me-2"></i> Data Dokumen Aktif</span>
                        <button class="btn btn-sm btn-light text-primary" id="btnAddDocument"><i class="fa-solid fa-plus me-1"></i> Tambah Data</button>
                    </div>
                    <div class="card-body">
                        <table id="documentTable" class="table table-striped table-hover w-100"></table>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <div class="modal fade" id="loginModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-lg">
                <div class="modal-header bg-dark text-white text-center">
                    <h5 class="modal-title w-100" id="loginModalLabel"><i class="fa-solid fa-lock me-2 text-warning"></i> Login Gateway E-DocSmart</h5>
                </div>
                <form id="loginForm">
                    <div class="modal-body p-4">
                        <div id="loginAlert"></div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Username / Email</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa-solid fa-user"></i></span>
                                <input type="text" name="username" class="form-control" placeholder="Masukkan username atau email" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Password</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fa-solid fa-key"></i></span>
                                <input type="password" name="password" class="form-control" placeholder="Masukkan password" required>
                            </div>
                        </div>
                        <div class="alert alert-info py-2">Gunakan akun default: <strong>admin</strong> / <strong>Password123!</strong> setelah migrasi.</div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="submit" class="btn btn-primary w-100 fw-bold"><i class="fa-solid fa-right-to-bracket me-2"></i> Masuk Sistem</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="documentModal" tabindex="-1" aria-labelledby="documentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="documentModalLabel"><i class="fa-solid fa-file-circle-plus me-2"></i> Tambah Dokumen</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="documentForm" enctype="multipart/form-data">
                    <input type="hidden" id="documentId" name="document_id" value="">
                    <div class="modal-body">
                        <div id="documentAlert"></div>
                        <div class="row gy-3">
                            <div class="col-md-6">
                                <label class="form-label">Nama Dokumen</label>
                                <input type="text" id="title" name="title" class="form-control" placeholder="Contoh: Laporan Tugas" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Kategori Dokumen</label>
                                <select id="category" name="category" class="form-select" required>
                                    <option value="Akademik">Akademik</option>
                                    <option value="Administrasi">Administrasi</option>
                                    <option value="Keuangan">Keuangan</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Pemilik Dokumen</label>
                                <input type="text" id="owner" name="owner" class="form-control" placeholder="Nama pemilik / penanggung jawab" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Upload Lampiran (Bisa pilih lebih dari 1 file)</label>
                                <input type="file" name="attachments[]" multiple class="form-control">
                                <div class="form-text">Format yang diizinkan: JPG, PNG, PDF. Maks 2MB per file.</div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Lampiran Saat Ini</label>
                                <div id="existingFiles" class="d-flex flex-wrap gap-2"></div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Tanda Tangan Digital</label>
                                <div class="canvas-container mb-3">
                                    <canvas id="sig-canvas"></canvas>
                                </div>
                                <div class="d-flex gap-2 mb-2">
                                    <button type="button" class="btn btn-outline-secondary" id="clearSignature"><i class="fa-solid fa-eraser me-1"></i> Bersihkan</button>
                                    <span class="form-text">Gunakan mouse atau layar sentuh untuk menandatangani.</span>
                                </div>
                                <div id="signaturePreview" class="border rounded p-3 bg-white"></div>
                                <input type="hidden" id="signature_data_url" name="signature_data_url" value="">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success"><i class="fa-solid fa-save me-1"></i> Simpan Data</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title" id="deleteModalLabel"><i class="fa-solid fa-triangle-exclamation me-2"></i> Konfirmasi Hapus</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menghapus dokumen ini beserta semua lampiran dan tanda tangan terkait?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-danger" id="deleteConfirmButton"><i class="fa-solid fa-trash-can me-1"></i> Hapus Sekarang</button>
                </div>
            </div>
        </div>
    </div>

    <div id="toastContainer" class="position-fixed top-0 end-0 p-3" style="z-index: 1055;"></div>
    <div id="lottieSuccess" class="lottie-float position-fixed bottom-0 start-50 translate-middle-x mb-4"></div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lottie-web/5.9.6/lottie.min.js"></script>
    <script>
        window.pageConfig = {
            loggedIn: <?php echo json_encode($loggedIn); ?>,
            username: <?php echo json_encode($username); ?>
        };
    </script>
    <script src="assets/js/app.js"></script>
</body>
</html>
