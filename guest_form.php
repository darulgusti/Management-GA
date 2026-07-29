<?php
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth_check.php';
$error_msg = '';

$logged_user = get_logged_user();
if ($logged_user) {
    set_flash_message('danger', 'Akun terautentikasi (Manager / SECOM) hanya untuk monitoring data. Pengisian form dilakukan melalui portal publik.');
    header("Location: guest.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $category = trim($_POST['guest_category'] ?? '');
    if ($category === 'Lainnya') {
        $other = trim($_POST['other_guest_category'] ?? '');
        $category = !empty($other) ? "Lainnya: " . $other : 'Lainnya';
    }
    $institution = trim($_POST['institution'] ?? '');
    $purpose = trim($_POST['purpose'] ?? '');
    $person_to_meet = trim($_POST['person_to_meet'] ?? '');
    $id_type = trim($_POST['id_type'] ?? '');
    $visitor_card = trim($_POST['visitor_card_number'] ?? '');
    $document_photo = $_POST['document_photo'] ?? '';
    $signature = $_POST['signature'] ?? '';

    if (empty($name) || empty($category) || empty($institution) || empty($person_to_meet) || empty($purpose)) {
        $error_msg = "Mohon lengkapi seluruh kolom wajib pendaftaran tamu!";
    } else {
        try {
            $now = date('Y-m-d H:i:s');
            $stmt = $pdo->prepare("INSERT INTO guests (name, guest_category, institution, purpose, person_to_meet, id_type, visitor_card_number, document_photo, time_in, signature) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $category, $institution, $purpose, $person_to_meet, $id_type, $visitor_card, $document_photo, $now, $signature]);
            
            header("Location: success.php?type=guest");
            exit();
        } catch (Exception $e) {
            $error_msg = "Gagal menyimpan data tamu: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Registrasi Tamu - GA Management</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="portal-body">

    <header class="portal-navbar" style="background: #ffffff; padding: 0.75rem 1.25rem; display: flex; align-items: center; justify-content: space-between; border-bottom: 2px solid var(--primary); box-shadow: 0 2px 8px rgba(0,0,0,0.05); flex-wrap: nowrap; gap: 0.5rem;">
        <div style="display: flex; align-items: center; gap: 0.6rem; min-width: 0; flex: 1;">
            <div class="sidebar-logo-icon" style="background: var(--primary); color: #ffffff; flex-shrink: 0;">GA</div>
            <div style="min-width: 0; overflow: hidden;">
                <strong style="font-size: 1rem; color: var(--primary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: block;">Form Buku Tamu Digital</strong>
                <div style="font-size: 0.72rem; color: var(--text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: block;">General Affairs Visitor Registration</div>
            </div>
        </div>
        <div style="flex-shrink: 0; margin-left: 0.5rem;">
            <a href="login.php" class="btn btn-sm btn-primary">Login</a>
        </div>
    </header>

    <div class="portal-container" style="max-width: 800px; margin-top: 2rem; margin-bottom: 3rem;">
        
        <?php if ($error_msg): ?>
            <div class="alert alert-danger">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <?= htmlspecialchars($error_msg) ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <div>
                    <h2 class="card-title">Form Registrasi Tamu Baru</h2>
                </div>
            </div>

            <form action="guest_form.php" method="POST">
                <input type="hidden" name="signature" id="guest_signature_input">

                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label">Nama Lengkap Tamu <small style="color: var(--primary); font-weight: 600;">(wajib diisi)</small></label>
                        <input type="text" name="name" required class="form-control" placeholder="Contoh: Budi Santoso" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Kategori Tamu <small style="color: var(--primary); font-weight: 600;">(wajib diisi)</small></label>
                        <select name="guest_category" id="guest_category_select" class="form-select" required onchange="toggleOtherCategory(this)">
                            <option value="" disabled <?= empty($_POST['guest_category']) ? 'selected' : '' ?>>-- Pilih Kategori --</option>
                            <option value="Tamu Kedinasan / Instansi" <?= ($_POST['guest_category'] ?? '') === 'Tamu Kedinasan / Instansi' ? 'selected' : '' ?>>Tamu Kedinasan / Instansi</option>
                            <option value="Tamu Kunjungan Industri" <?= ($_POST['guest_category'] ?? '') === 'Tamu Kunjungan Industri' ? 'selected' : '' ?>>Tamu Kunjungan Industri</option>
                            <option value="Tamu Vendor / Menemui Karyawan" <?= ($_POST['guest_category'] ?? '') === 'Tamu Vendor / Menemui Karyawan' ? 'selected' : '' ?>>Tamu Vendor / Menemui Karyawan</option>
                            <option value="Tamu Kontraktor" <?= ($_POST['guest_category'] ?? '') === 'Tamu Kontraktor' ? 'selected' : '' ?>>Tamu Kontraktor</option>
                            <option value="Tamu PKL (No Card)" <?= ($_POST['guest_category'] ?? '') === 'Tamu PKL (No Card)' ? 'selected' : '' ?>>Tamu PKL (No Card)</option>
                            <option value="Lainnya" <?= ($_POST['guest_category'] ?? '') === 'Lainnya' ? 'selected' : '' ?>>Lainnya...</option>
                        </select>

                        <div id="other_category_container" style="display: none; margin-top: 0.5rem;">
                            <input type="text" name="other_guest_category" id="other_guest_category" class="form-control" placeholder="Tuliskan kategori tamu lainnya...">
                        </div>
                    </div>
                </div>

                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label">Instansi / Perusahaan Asal <small style="color: var(--primary); font-weight: 600;">(wajib diisi)</small></label>
                        <input type="text" name="institution" required class="form-control" placeholder="PT / Instansi Asal" value="<?= htmlspecialchars($_POST['institution'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Orang yang Ditemui (Karyawan) <small style="color: var(--primary); font-weight: 600;">(wajib diisi)</small></label>
                        <input type="text" name="person_to_meet" required class="form-control" placeholder="Nama Karyawan / Departemen" value="<?= htmlspecialchars($_POST['person_to_meet'] ?? '') ?>">
                    </div>
                </div>

                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label">Jenis Kartu Identitas <small style="color: var(--primary); font-weight: 600;">(wajib diisi)</small></label>
                        <select name="id_type" class="form-select" required>
                            <option value="KTP">KTP</option>
                            <option value="SIM">SIM</option>
                            <option value="PASPOR">Paspor / ID Card</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Tujuan Kunjungan <small style="color: var(--primary); font-weight: 600;">(wajib diisi)</small></label>
                        <input type="text" name="purpose" required class="form-control" placeholder="Jelaskan keperluan/tujuan kunjungan Anda..." value="<?= htmlspecialchars($_POST['purpose'] ?? '') ?>">
                    </div>
                </div>

                <!-- BIDANG OPSIONAL -->
                <div class="grid-2" style="margin-top: 0.5rem; border-top: 1px dashed var(--border); padding-top: 1rem;">
                    <div class="form-group">
                        <label class="form-label">Nomor Kartu Akses Tamu (Visitor Card) (Opsional)</label>
                        <input type="text" name="visitor_card_number" class="form-control" placeholder="Contoh: V-012 (Opsional)..." value="<?= htmlspecialchars($_POST['visitor_card_number'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Foto Dokumen <small style="color: var(--text-muted); font-weight: 400;">(Opsional)</small></label>
                        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 0.5rem;">
                            <button type="button" class="btn btn-secondary btn-sm" onclick="document.getElementById('camera_file_input').click();">
                                📷 Ambil Kamera
                            </button>
                            <button type="button" class="btn btn-outline btn-sm" onclick="document.getElementById('gallery_file_input').click();">
                                📁 Pilih Galeri
                            </button>
                        </div>
                        <input type="file" id="camera_file_input" accept="image/*" capture="environment" style="display:none;" onchange="compressAndPreviewPhoto(this)">
                        <input type="file" id="gallery_file_input" accept="image/*" style="display:none;" onchange="compressAndPreviewPhoto(this)">
                        <input type="hidden" name="document_photo" id="document_photo_input" value="<?= htmlspecialchars($_POST['document_photo'] ?? '') ?>">
                        <div id="photo_preview_container" style="display: none; margin-top: 0.5rem; text-align: center;">
                            <img id="photo_preview_img" src="" style="max-height: 130px; border-radius: var(--radius-sm); border: 1px solid var(--border); box-shadow: var(--shadow-sm);">
                            <div style="font-size: 0.75rem; color: var(--success); font-weight: 600; margin-top: 0.25rem;">✓ Foto dokumen berhasil dikompresi (siap simpan)</div>
                            <button type="button" class="btn btn-sm btn-danger" style="margin-top: 0.35rem; padding: 0.2rem 0.6rem; font-size: 0.75rem;" onclick="clearPhotoPreview()">Hapus Foto</button>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Tanda Tangan Digital Tamu <small style="color: var(--primary); font-weight: 600;">(wajib diisi)</small></label>
                    <div class="signature-container">
                        <canvas id="guest_signature_canvas" class="signature-canvas"></canvas>
                        <div class="signature-controls">
                            <small style="color: #64748b;">Gunakan mouse atau layar sentuh untuk menggambar tanda tangan</small>
                            <button type="button" id="clear_guest_signature" class="btn btn-sm btn-secondary">Hapus Canvas</button>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-lg btn-block" style="margin-top: 1rem;">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    Simpan & Proses Check-in Tamu
                </button>
            </form>
        </div>

    </div>

    <script src="js/signature_pad.js"></script>
    <script>
    function clearPhotoPreview() {
        document.getElementById('document_photo_input').value = '';
        document.getElementById('photo_preview_img').src = '';
        document.getElementById('photo_preview_container').style.display = 'none';
        const cam = document.getElementById('camera_file_input');
        const gal = document.getElementById('gallery_file_input');
        if (cam) cam.value = '';
        if (gal) gal.value = '';
    }

    function toggleOtherCategory(selectEl) {
        const container = document.getElementById('other_category_container');
        const input = document.getElementById('other_guest_category');
        if (selectEl.value === 'Lainnya') {
            container.style.display = 'block';
            input.setAttribute('required', 'required');
            input.focus();
        } else {
            container.style.display = 'none';
            input.removeAttribute('required');
        }
    }

    function compressAndPreviewPhoto(fileInput) {
        const file = fileInput.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = function(e) {
            const img = new Image();
            img.onload = function() {
                const canvas = document.createElement('canvas');
                let width = img.width;
                let height = img.height;
                const maxDimension = 1200;

                if (width > height && width > maxDimension) {
                    height = Math.round((height * maxDimension) / width);
                    width = maxDimension;
                } else if (height > maxDimension) {
                    width = Math.round((width * maxDimension) / height);
                    height = maxDimension;
                }

                canvas.width = width;
                canvas.height = height;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, width, height);

                const compressedDataUrl = canvas.toDataURL('image/jpeg', 0.7);
                document.getElementById('document_photo_input').value = compressedDataUrl;
                document.getElementById('photo_preview_img').src = compressedDataUrl;
                document.getElementById('photo_preview_container').style.display = 'block';
            };
            img.src = e.target.result;
        };
        reader.readAsDataURL(file);
    }
    </script>
    <script src="js/auto_dismiss_alerts.js"></script>
</body>
</html>
