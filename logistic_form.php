<?php
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth_check.php';
$logged_user = get_logged_user();
if ($logged_user) {
    set_flash_message('danger', 'Akun terautentikasi (Manager / SECOM) hanya untuk monitoring data. Pengisian form dilakukan melalui portal publik.');
    header("Location: logistic.php");
    exit();
}

$type = $_GET['type'] ?? 'gate_in';
$error_msg = '';
$success_msg = get_flash_message('success');

// Handle Public Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_gate_in') {
        $nopol = trim($_POST['nopol'] ?? '');
        $driver_name = trim($_POST['driver_name'] ?? '');
        $visitor_number = trim($_POST['visitor_number'] ?? '');
        $antree_number = trim($_POST['antree_number'] ?? '');
        $transportir = trim($_POST['transportir'] ?? '');
        $destination = trim($_POST['destination'] ?? 'Kirim');
        $sim_type = trim($_POST['sim_type'] ?? 'SIM B');
        $sim_number = trim($_POST['sim_number'] ?? '');
        $document_photo = $_POST['document_photo'] ?? '';
        $sim = ($sim_type !== 'Tidak Ada') ? 1 : 0;
        $stnk = isset($_POST['checklist_stnk']) ? 1 : 0;
        $kir = isset($_POST['checklist_kir']) ? 1 : 0;
        $entry_time = !empty($_POST['entry_time']) ? date('Y-m-d H:i:s', strtotime($_POST['entry_time'])) : date('Y-m-d H:i:s');

        if (empty($nopol) || empty($driver_name) || empty($transportir) || empty($destination) || empty($sim_type) || empty($sim_number)) {
            $error_msg = "Mohon lengkapi Nopol, Driver, Nomor SIM, Transportir, dan Tujuan Gate In!";
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO logistic_gate_ins (nopol, driver_name, visitor_number, antree_number, transportir, destination, sim_type, sim_number, document_photo, checklist_sim, checklist_stnk, checklist_kir, entry_time) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$nopol, $driver_name, $visitor_number, $antree_number, $transportir, $destination, $sim_type, $sim_number, $document_photo, $sim, $stnk, $kir, $entry_time]);
                header("Location: success.php?type=gate_in");
                exit();
            } catch (Exception $e) {
                $error_msg = "Gagal menyimpan data Gate In: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Input Pos 4 Gate Pass - GA Management</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="portal-body">

    <header class="portal-navbar" style="background: #ffffff; padding: 0.75rem 1.25rem; display: flex; align-items: center; justify-content: space-between; border-bottom: 2px solid var(--primary); box-shadow: 0 2px 8px rgba(0,0,0,0.05); flex-wrap: nowrap; gap: 0.5rem;">
        <div style="display: flex; align-items: center; gap: 0.6rem; min-width: 0; flex: 1;">
            <div class="sidebar-logo-icon" style="background: var(--primary); color: #ffffff; flex-shrink: 0;">GA</div>
            <div style="min-width: 0; overflow: hidden;">
                <strong style="font-size: 1rem; color: var(--primary); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: block;">Form Pos 4 - Gate Pass System</strong>
                <div style="font-size: 0.72rem; color: var(--text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: block;">General Affairs Fleet &amp; Container Logistics Registration</div>
            </div>
        </div>
        <div style="flex-shrink: 0; margin-left: 0.5rem;">
            <a href="login.php" class="btn btn-sm btn-primary">Login</a>
        </div>
    </header>

    <div class="portal-container" style="max-width: 840px; margin-top: 2rem; margin-bottom: 3rem;">
        
        <?php if ($success_msg): ?>
            <div class="alert alert-success" style="margin-bottom: 1.5rem;">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                <?= htmlspecialchars($success_msg) ?>
            </div>
        <?php endif; ?>

        <?php if ($error_msg): ?>
            <div class="alert alert-danger" style="margin-bottom: 1.5rem;">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <?= htmlspecialchars($error_msg) ?>
            </div>
        <?php endif; ?>

        <div class="card" style="border-top: 4px solid var(--primary);">
            
            <div style="margin-bottom: 1rem;">
                <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--primary); margin-bottom: 0.25rem;">Form Buku Masuk Kendaraan (Gate In)</h2>
            </div>

            <form method="POST" action="logistic_form.php?type=gate_in">
                <input type="hidden" name="action" value="add_gate_in">

                <!-- INFORMASI UTAMA & WAJIB DIISI -->
                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600;">Nomor Polisi (Nopol) <small style="color: var(--primary); font-weight: 600;">(wajib diisi)</small></label>
                        <input type="text" name="nopol" required class="form-control" placeholder="Masukkan Nopol (e.g. B 1234 XYZ)..." value="<?= htmlspecialchars($_POST['nopol'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600;">Nama Sopir <small style="color: var(--primary); font-weight: 600;">(wajib diisi)</small></label>
                        <input type="text" name="driver_name" required class="form-control" placeholder="Nama sopir / driver..." value="<?= htmlspecialchars($_POST['driver_name'] ?? '') ?>">
                    </div>
                </div>

                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600;">Transportir / Perusahaan <small style="color: var(--primary); font-weight: 600;">(wajib diisi)</small></label>
                        <input type="text" name="transportir" required class="form-control" placeholder="Nama Perusahaan / Transportir..." value="<?= htmlspecialchars($_POST['transportir'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600;">Tujuan <small style="color: var(--primary); font-weight: 600;">(wajib diisi)</small></label>
                        <select name="destination" class="form-select" required>
                            <option value="">-- Pilih Tujuan --</option>
                            <option value="Kirim" <?= ($_POST['destination'] ?? '') === 'Kirim' ? 'selected' : '' ?>>Kirim</option>
                            <option value="Export Ajinex" <?= ($_POST['destination'] ?? '') === 'Export Ajinex' ? 'selected' : '' ?>>Export Ajinex</option>
                            <option value="Transit" <?= ($_POST['destination'] ?? '') === 'Transit' ? 'selected' : '' ?>>Transit</option>
                            <option value="Muatan Barang" <?= ($_POST['destination'] ?? '') === 'Muatan Barang' ? 'selected' : '' ?>>Muatan Barang</option>
                            <option value="EDC" <?= ($_POST['destination'] ?? '') === 'EDC' ? 'selected' : '' ?>>EDC</option>
                        </select>
                    </div>
                </div>

                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600;">Jenis SIM Driver <small style="color: var(--primary); font-weight: 600;">(wajib diisi)</small></label>
                        <select name="sim_type" class="form-select" required>
                            <option value="SIM A" <?= ($_POST['sim_type'] ?? '') === 'SIM A' ? 'selected' : '' ?>>SIM A</option>
                            <option value="SIM B" <?= ($_POST['sim_type'] ?? 'SIM B') === 'SIM B' ? 'selected' : '' ?>>SIM B</option>
                            <option value="SIM B2" <?= ($_POST['sim_type'] ?? '') === 'SIM B2' ? 'selected' : '' ?>>SIM B2</option>
                            <option value="Tidak Ada" <?= ($_POST['sim_type'] ?? '') === 'Tidak Ada' ? 'selected' : '' ?>>Tidak Ada</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600;">Nomor SIM Driver <small style="color: var(--primary); font-weight: 600;">(wajib diisi)</small></label>
                        <input type="text" name="sim_number" required class="form-control" placeholder="Nomor SIM Driver..." value="<?= htmlspecialchars($_POST['sim_number'] ?? '') ?>">
                    </div>
                </div>

                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600;">Kelengkapan Berkas (STNK &amp; KIR) <small style="color: var(--primary); font-weight: 600;">(wajib diisi)</small></label>
                        <div style="display: flex; gap: 1.5rem; padding: 0.6rem 0.85rem; background: var(--bg-surface-alt); border-radius: var(--radius-sm); border: 1px solid var(--border);">
                            <label style="cursor: pointer; display: flex; align-items: center; gap: 0.35rem; font-weight: 500;"><input type="checkbox" name="checklist_stnk" value="1" checked> STNK</label>
                            <label style="cursor: pointer; display: flex; align-items: center; gap: 0.35rem; font-weight: 500;"><input type="checkbox" name="checklist_kir" value="1" checked> KIR</label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600;">Tanggal &amp; Waktu Masuk</label>
                        <input type="datetime-local" name="entry_time" class="form-control" value="<?= date('Y-m-d\TH:i') ?>">
                    </div>
                </div>

                <!-- BIDANG OPSIONAL -->
                <div class="grid-2" style="margin-top: 0.5rem; border-top: 1px dashed var(--border); padding-top: 1rem;">
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600;">Visitor Number (Opsional)</label>
                        <input type="text" name="visitor_number" class="form-control" placeholder="Visitor Card Number (Opsional)..." value="<?= htmlspecialchars($_POST['visitor_number'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600;">Antre Number (Opsional)</label>
                        <input type="text" name="antree_number" class="form-control" placeholder="Nomor Antre (Opsional)..." value="<?= htmlspecialchars($_POST['antree_number'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" style="font-weight: 600;">Upload Foto Dokumen <small style="color: var(--text-muted); font-weight: 400;">(Opsional)</small></label>
                    <input type="file" id="document_file_input" accept="image/*" class="form-control" onchange="compressAndPreviewPhoto(this)">
                    <input type="hidden" name="document_photo" id="document_photo_input" value="<?= htmlspecialchars($_POST['document_photo'] ?? '') ?>">
                    <div id="photo_preview_container" style="display: none; margin-top: 0.5rem; text-align: center;">
                        <img id="photo_preview_img" src="" style="max-height: 130px; border-radius: var(--radius-sm); border: 1px solid var(--border); box-shadow: var(--shadow-sm);">
                        <div style="font-size: 0.75rem; color: var(--success); font-weight: 600; margin-top: 0.25rem;">✓ Foto berhasil dikompresi (siap simpan)</div>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-lg btn-block" style="margin-top: 1.5rem;">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    Simpan Data Gate In
                </button>
            </form>

        </div>

    </div>

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

    function compressAndPreviewPhoto(input, containerId = 'photo_preview_container', imgId = 'photo_preview_img', inputId = 'document_photo_input') {
        if (!input.files || !input.files[0]) return;
        const file = input.files[0];
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = new Image();
            img.onload = function() {
                const canvas = document.createElement('canvas');
                let width = img.width;
                let height = img.height;
                const max_size = 1200;
                if (width > height) {
                    if (width > max_size) {
                        height = Math.round((height * max_size) / width);
                        width = max_size;
                    }
                } else {
                    if (height > max_size) {
                        width = Math.round((width * max_size) / height);
                        height = max_size;
                    }
                }
                canvas.width = width;
                canvas.height = height;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, width, height);
                const compressedBase64 = canvas.toDataURL('image/jpeg', 0.75);
                if (document.getElementById(inputId)) document.getElementById(inputId).value = compressedBase64;
                if (document.getElementById(imgId)) document.getElementById(imgId).src = compressedBase64;
                if (document.getElementById(containerId)) document.getElementById(containerId).style.display = 'block';
            };
            img.src = e.target.result;
        };
        reader.readAsDataURL(file);
    }
    </script>
    <script src="js/auto_dismiss_alerts.js"></script>
</body>
</html>
