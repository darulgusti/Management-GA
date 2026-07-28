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

        if (empty($nopol) || empty($driver_name)) {
            $error_msg = "Nomor Polisi (Nopol) dan Nama Sopir wajib diisi!";
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
    } elseif ($action === 'add_gate_out') {
        $nopol = trim($_POST['nopol'] ?? '');
        $driver_name = trim($_POST['driver_name'] ?? '');
        $do_number = trim($_POST['do_number'] ?? '');
        $destination = trim($_POST['destination'] ?? '');
        $tonnage = floatval($_POST['tonnage'] ?? 0);
        $transportir = trim($_POST['transportir'] ?? '');
        $exit_time = !empty($_POST['exit_time']) ? date('Y-m-d H:i:s', strtotime($_POST['exit_time'])) : date('Y-m-d H:i:s');

        if (empty($nopol) || empty($driver_name) || empty($do_number) || empty($destination)) {
            $error_msg = "Mohon lengkapi Nopol, Driver, No. DO, dan Tujuan Pengiriman!";
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO logistic_gate_outs (nopol, driver_name, do_number, destination, tonnage, transportir, exit_time) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$nopol, $driver_name, $do_number, $destination, $tonnage, $transportir, $exit_time]);
                header("Location: success.php?type=gate_out");
                exit();
            } catch (Exception $e) {
                $error_msg = "Gagal menyimpan data Gate Out: " . $e->getMessage();
            }
        }
    } elseif ($action === 'add_export') {
        $mopor_number = trim($_POST['mopor_number'] ?? '');
        $driver_name = trim($_POST['driver_name'] ?? '');
        $do_number = trim($_POST['do_number'] ?? '');
        $container_number = trim($_POST['container_number'] ?? '');
        $seal_number = trim($_POST['seal_number'] ?? '');
        $destination = trim($_POST['destination'] ?? '');
        $tonnage = floatval($_POST['tonnage'] ?? 0);
        $transportir = trim($_POST['transportir'] ?? '');
        $exit_time = !empty($_POST['exit_time']) ? date('Y-m-d H:i:s', strtotime($_POST['exit_time'])) : date('Y-m-d H:i:s');

        if (empty($mopor_number) || empty($driver_name) || empty($container_number)) {
            $error_msg = "Mohon lengkapi No. NOPOR, Driver, dan No. Kontainer!";
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO logistic_export_nex_mopors (mopor_number, driver_name, do_number, container_number, seal_number, destination, tonnage, transportir, exit_time) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$mopor_number, $driver_name, $do_number, $container_number, $seal_number, $destination, $tonnage, $transportir, $exit_time]);
                header("Location: success.php?type=export");
                exit();
            } catch (Exception $e) {
                $error_msg = "Gagal menyimpan data Export NEX: " . $e->getMessage();
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

    <header class="portal-navbar" style="background: #ffffff; padding: 0.9rem 1.5rem; display: flex; align-items: center; justify-content: space-between; border-bottom: 2px solid var(--primary); box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <div class="sidebar-logo-icon" style="background: var(--primary); color: #ffffff;">GA</div>
            <div>
                <strong style="font-size: 1.1rem; color: var(--primary);">Form Pos 4 - Gate Pass System</strong>
                <div style="font-size: 0.75rem; color: var(--text-muted);">General Affairs Fleet &amp; Container Logistics Registration</div>
            </div>
        </div>
        <div>
            <a href="login.php" class="btn btn-sm btn-primary">Login</a>
        </div>
    </header>

    <div class="portal-container" style="max-width: 840px; margin-top: 2rem; margin-bottom: 3rem;">
        
        <div style="margin-bottom: 1.5rem;">
            <a href="index.php" class="btn-back">
                ← Kembali ke Portal Utama
            </a>
        </div>

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
            
            <!-- FORM TYPE SWITCHER BUTTONS AT THE TOP -->
            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border); padding-bottom: 1rem;">
                <a href="logistic_form.php?type=gate_in" class="btn btn-sm <?= $type === 'gate_in' ? 'btn-primary' : 'btn-outline' ?>" style="font-weight: 600;">
                    📥 Buku Masuk (Gate In)
                </a>
                <a href="logistic_form.php?type=gate_out" class="btn btn-sm <?= $type === 'gate_out' ? 'btn-primary' : 'btn-outline' ?>" style="font-weight: 600;">
                    📤 Buku Keluar (Gate Out)
                </a>
                <a href="logistic_form.php?type=export_nex" class="btn btn-sm <?= $type === 'export_nex' ? 'btn-primary' : 'btn-outline' ?>" style="font-weight: 600;">
                    🚢 Export NEX / NOPOR
                </a>
            </div>

            <!-- FORM 1: BUKU MASUK (GATE IN) -->
            <?php if ($type === 'gate_in'): ?>
                <div style="margin-bottom: 1rem;">
                    <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--primary); margin-bottom: 0.25rem;">Form Buku Masuk Kendaraan (Gate In)</h2>
                    <p style="color: var(--text-muted); font-size: 0.85rem;">Input data armada kendaraan yang baru tiba di pos gerbang logistik pabrik.</p>
                </div>

                <form method="POST" action="logistic_form.php?type=gate_in">
                    <input type="hidden" name="action" value="add_gate_in">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.25rem;">
                        
                        <!-- LEFT COLUMN -->
                        <div>
                            <div class="form-group">
                                <label class="form-label" style="font-weight: 600;">Nomor Polisi (Nopol) <small style="color: var(--primary); font-weight: 600;">(wajib diisi)</small></label>
                                <input type="text" name="nopol" required class="form-control" placeholder="Masukkan Nopol (e.g. B 1234 XYZ)...">
                            </div>

                            <div class="form-group">
                                <label class="form-label" style="font-weight: 600;">Visitor Number (Opsional)</label>
                                <input type="text" name="visitor_number" class="form-control" placeholder="Visitor Card Number (Opsional)...">
                            </div>

                            <div class="form-group">
                                <label class="form-label" style="font-weight: 600;">Antre Number (Opsional)</label>
                                <input type="text" name="antree_number" class="form-control" placeholder="Nomor Antre (Opsional)...">
                            </div>

                            <div class="form-group">
                                <label class="form-label" style="font-weight: 600;">Tanggal &amp; Waktu Masuk <small style="color: var(--primary); font-weight: 600;">(wajib diisi)</small></label>
                                <input type="datetime-local" name="entry_time" required class="form-control" value="<?= date('Y-m-d\TH:i') ?>">
                            </div>

                            <div class="form-group">
                                <label class="form-label" style="font-weight: 600;">Nomor SIM Driver (Opsional)</label>
                                <input type="text" name="sim_number" class="form-control" placeholder="Nomor SIM Driver...">
                            </div>
                        </div>

                        <!-- RIGHT COLUMN -->
                        <div>
                            <div class="form-group">
                                <label class="form-label" style="font-weight: 600;">Nama Sopir <small style="color: var(--primary); font-weight: 600;">(wajib diisi)</small></label>
                                <input type="text" name="driver_name" required class="form-control" placeholder="Nama sopir / driver...">
                            </div>

                            <div class="form-group">
                                <label class="form-label" style="font-weight: 600;">Tujuan <small style="color: var(--primary); font-weight: 600;">(wajib diisi)</small></label>
                                <select name="destination" class="form-select" required>
                                    <option value="">-- Pilih Tujuan --</option>
                                    <option value="Kirim">Kirim</option>
                                    <option value="Export Ajinex">Export Ajinex</option>
                                    <option value="Umbal-umbal">Umbal-umbal</option>
                                    <option value="Muat">Muat</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="form-label" style="font-weight: 600;">Jenis SIM Driver <small style="color: var(--primary); font-weight: 600;">(wajib diisi)</small></label>
                                <select name="sim_type" class="form-select" required>
                                    <option value="SIM A">SIM A</option>
                                    <option value="SIM B" selected>SIM B</option>
                                    <option value="SIM B2">SIM B2</option>
                                    <option value="Tidak Ada">Tidak Ada</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label class="form-label" style="font-weight: 600;">Kelengkapan Berkas (STNK &amp; KIR)</label>
                                <div style="display: flex; gap: 1rem; padding: 0.55rem 0.75rem; background: var(--bg-surface-alt); border-radius: var(--radius-sm); border: 1px solid var(--border);">
                                    <label style="cursor: pointer; display: flex; align-items: center; gap: 0.35rem;"><input type="checkbox" name="checklist_stnk" value="1" checked> STNK</label>
                                    <label style="cursor: pointer; display: flex; align-items: center; gap: 0.35rem;"><input type="checkbox" name="checklist_kir" value="1" checked> KIR</label>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="form-label" style="font-weight: 600;">Transportir / Perusahaan</label>
                                <input type="text" name="transportir" class="form-control" placeholder="Nama Perusahaan / Transportir (Opsional)...">
                            </div>

                            <div class="form-group">
                                <label class="form-label" style="font-weight: 600;">Upload Foto SIM / Dokumen (Opsional)</label>
                                <input type="file" id="document_file_input" accept="image/*" class="form-control" onchange="compressAndPreviewPhoto(this)">
                                <input type="hidden" name="document_photo" id="document_photo_input">
                                <div id="photo_preview_container" style="display: none; margin-top: 0.5rem; text-align: center;">
                                    <img id="photo_preview_img" src="" style="max-height: 130px; border-radius: var(--radius-sm); border: 1px solid var(--border); box-shadow: var(--shadow-sm);">
                                    <div style="font-size: 0.75rem; color: var(--success); font-weight: 600; margin-top: 0.25rem;">✓ Foto berhasil dikompresi (siap simpan)</div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <button type="submit" class="btn btn-primary btn-lg btn-block" style="margin-top: 1.5rem;">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Simpan Data Gate In
                    </button>
                </form>

            <!-- FORM 2: BUKU KELUAR (GATE OUT) -->
            <?php elseif ($type === 'gate_out'): ?>
                <div style="margin-bottom: 1rem;">
                    <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--primary); margin-bottom: 0.25rem;">Form Buku Keluar Kendaraan (Gate Out)</h2>
                    <p style="color: var(--text-muted); font-size: 0.85rem;">Input data armada kendaraan pengiriman non-ekspor yang keluar dari gerbang.</p>
                </div>

                <form method="POST" action="logistic_form.php?type=gate_out">
                    <input type="hidden" name="action" value="add_gate_out">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.25rem;">
                        
                        <!-- LEFT COLUMN -->
                        <div>
                            <div class="form-group">
                                <label class="form-label" style="font-weight: 600;">Nomor Polisi (Nopol) <small style="color: var(--primary); font-weight: 600;">(wajib diisi)</small></label>
                                <input type="text" name="nopol" required class="form-control" placeholder="Masukkan Nopol (e.g. B 1234 XYZ)...">
                            </div>

                            <div class="form-group">
                                <label class="form-label" style="font-weight: 600;">Nomor Delivery Order (DO) <small style="color: var(--primary); font-weight: 600;">(wajib diisi)</small></label>
                                <input type="text" name="do_number" required class="form-control" placeholder="Nomor Delivery Order...">
                            </div>

                            <div class="form-group">
                                <label class="form-label" style="font-weight: 600;">Tonase / Jumlah Muatan (Ton)</label>
                                <input type="number" step="0.01" name="tonnage" class="form-control" placeholder="Jumlah Tonase (Ton)...">
                            </div>

                            <div class="form-group">
                                <label class="form-label" style="font-weight: 600;">Transportir / Perusahaan</label>
                                <input type="text" name="transportir" class="form-control" placeholder="Nama Transportir (Opsional)...">
                            </div>
                        </div>

                        <!-- RIGHT COLUMN -->
                        <div>
                            <div class="form-group">
                                <label class="form-label" style="font-weight: 600;">Nama Sopir <small style="color: var(--primary); font-weight: 600;">(wajib diisi)</small></label>
                                <input type="text" name="driver_name" required class="form-control" placeholder="Nama sopir...">
                            </div>

                            <div class="form-group">
                                <label class="form-label" style="font-weight: 600;">Tujuan Pengiriman <small style="color: var(--primary); font-weight: 600;">(wajib diisi)</small></label>
                                <input type="text" name="destination" required class="form-control" placeholder="Tujuan Pengiriman / Alamat...">
                            </div>

                            <div class="form-group">
                                <label class="form-label" style="font-weight: 600;">Tanggal &amp; Waktu Keluar <small style="color: var(--primary); font-weight: 600;">(wajib diisi)</small></label>
                                <input type="datetime-local" name="exit_time" required class="form-control" value="<?= date('Y-m-d\TH:i') ?>">
                            </div>
                        </div>

                    </div>

                    <button type="submit" class="btn btn-primary btn-lg btn-block" style="margin-top: 1.5rem;">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Simpan Data Gate Out
                    </button>
                </form>

            <!-- FORM 3: EXPORT NEX / NOPOR -->
            <?php elseif ($type === 'export_nex'): ?>
                <div style="margin-bottom: 1rem;">
                    <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--primary); margin-bottom: 0.25rem;">Form Input Export NEX / NOPOR</h2>
                    <p style="color: var(--text-muted); font-size: 0.85rem;">Input data khusus armada kontainer logistik ekspor &amp; NOPOR.</p>
                </div>

                <form method="POST" action="logistic_form.php?type=export_nex">
                    <input type="hidden" name="action" value="add_export">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.25rem;">
                        
                        <!-- LEFT COLUMN -->
                        <div>
                            <div class="form-group">
                                <label class="form-label" style="font-weight: 600;">Nomor NOPOR <small style="color: var(--primary); font-weight: 600;">(wajib diisi)</small></label>
                                <input type="text" name="mopor_number" required class="form-control" placeholder="Masukkan NOPOR...">
                            </div>

                            <div class="form-group">
                                <label class="form-label" style="font-weight: 600;">Nomor DO</label>
                                <input type="text" name="do_number" class="form-control" placeholder="Nomor DO...">
                            </div>

                            <div class="form-group">
                                <label class="form-label" style="font-weight: 600;">Segel <small style="color: var(--primary); font-weight: 600;">(wajib diisi)</small></label>
                                <input type="text" name="seal_number" required class="form-control" placeholder="Nomor Segel (Seal)...">
                            </div>

                            <div class="form-group">
                                <label class="form-label" style="font-weight: 600;">Tonase (Ton)</label>
                                <input type="number" step="0.01" name="tonnage" class="form-control" placeholder="Jumlah Tonase (Ton)...">
                            </div>

                            <div class="form-group">
                                <label class="form-label" style="font-weight: 600;">Transportir</label>
                                <input type="text" name="transportir" class="form-control" placeholder="Transportir / Ekspedisi (Opsional)...">
                            </div>
                        </div>

                        <!-- RIGHT COLUMN -->
                        <div>
                            <div class="form-group">
                                <label class="form-label" style="font-weight: 600;">Sopir <small style="color: var(--primary); font-weight: 600;">(wajib diisi)</small></label>
                                <input type="text" name="driver_name" required class="form-control" placeholder="Nama sopir...">
                            </div>

                            <div class="form-group">
                                <label class="form-label" style="font-weight: 600;">Kontainer <small style="color: var(--primary); font-weight: 600;">(wajib diisi)</small></label>
                                <input type="text" name="container_number" required class="form-control" placeholder="Nomor Kontainer...">
                            </div>

                            <div class="form-group">
                                <label class="form-label" style="font-weight: 600;">Tujuan</label>
                                <input type="text" name="destination" class="form-control" placeholder="Pelabuhan / Negara Tujuan...">
                            </div>

                            <div class="form-group">
                                <label class="form-label" style="font-weight: 600;">Tanggal &amp; Waktu Keluar <small style="color: var(--primary); font-weight: 600;">(wajib diisi)</small></label>
                                <input type="datetime-local" name="exit_time" required class="form-control" value="<?= date('Y-m-d\TH:i') ?>">
                            </div>
                        </div>

                    </div>

                    <button type="submit" class="btn btn-primary btn-lg btn-block" style="margin-top: 1.5rem;">
                        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Simpan Data Export NEX / NOPOR
                    </button>
                </form>
            <?php endif; ?>

        </div>

    </div>

    <script>
    function compressAndPreviewPhoto(input) {
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
                document.getElementById('document_photo_input').value = compressedBase64;
                document.getElementById('photo_preview_img').src = compressedBase64;
                document.getElementById('photo_preview_container').style.display = 'block';
            };
            img.src = e.target.result;
        };
        reader.readAsDataURL(file);
    }
    </script>
</body>
</html>
