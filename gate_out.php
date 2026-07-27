<?php
$page_title = 'Buku Keluar (Gate Out) - Logistik';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth_check.php';

check_role(['manager', 'secom']);
$logged_user = get_logged_user();
$can_input = ($logged_user['role'] === 'secom');

// Auto Ensure Table Exists
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS `logistic_gate_outs` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `nopol` VARCHAR(50) NOT NULL,
      `driver_name` VARCHAR(255) NOT NULL,
      `do_number` VARCHAR(100) NOT NULL,
      `destination` VARCHAR(255) NOT NULL,
      `tonnage` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
      `transportir` VARCHAR(255) NOT NULL,
      `exit_time` DATETIME NOT NULL,
      `status` VARCHAR(50) NOT NULL DEFAULT 'Done',
      `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
} catch (Exception $e) {}

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$can_input) {
        set_flash_message('danger', 'Hanya Staf Secom yang memiliki wewenang untuk menambah atau menghapus data logistik!');
        header("Location: gate_out.php");
        exit();
    }

    $action = $_POST['action'] ?? '';

    if ($action === 'add_gate_out') {
        $nopol = trim($_POST['nopol'] ?? '');
        $driver_name = trim($_POST['driver_name'] ?? '');
        $do_number = trim($_POST['do_number'] ?? '');
        $destination = trim($_POST['destination'] ?? '');
        $tonnage = floatval($_POST['tonnage'] ?? 0);
        $transportir = trim($_POST['transportir'] ?? '');
        $exit_time = !empty($_POST['exit_time']) ? date('Y-m-d H:i:s', strtotime($_POST['exit_time'])) : date('Y-m-d H:i:s');

        if (!empty($nopol) && !empty($driver_name) && !empty($do_number) && !empty($destination)) {
            $stmt = $pdo->prepare("INSERT INTO logistic_gate_outs (nopol, driver_name, do_number, destination, tonnage, transportir, exit_time) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$nopol, $driver_name, $do_number, $destination, $tonnage, $transportir, $exit_time]);
            set_flash_message('success', 'Data Kendaraan Keluar (Gate Out) berhasil disimpan.');
        } else {
            set_flash_message('danger', 'Mohon lengkapi Nopol, Driver, No. DO, dan Tujuan!');
        }
    } elseif ($action === 'delete_gate_out') {
        $id = intval($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $pdo->prepare("DELETE FROM logistic_gate_outs WHERE id = ?");
            $stmt->execute([$id]);
            set_flash_message('success', 'Data Gate Out berhasil dihapus.');
        }
    }

    header("Location: gate_out.php");
    exit();
}

$per_page = 10;

// Search & Pagination Gate Out
$search_out = trim($_GET['search_out'] ?? '');
$page_out = max(1, intval($_GET['page_out'] ?? 1));
$count_query_out = "SELECT COUNT(*) FROM logistic_gate_outs WHERE 1=1";
$params_out = [];
if (!empty($search_out)) {
    $count_query_out .= " AND (nopol LIKE ? OR driver_name LIKE ? OR do_number LIKE ? OR destination LIKE ? OR transportir LIKE ?)";
    $term = "%$search_out%";
    $params_out = [$term, $term, $term, $term, $term];
}
$stmt = $pdo->prepare($count_query_out);
$stmt->execute($params_out);
$total_out_records = $stmt->fetchColumn();
$total_out_pages = ceil($total_out_records / $per_page);
$offset_out = ($page_out - 1) * $per_page;

$data_query_out = str_replace("SELECT COUNT(*)", "SELECT *", $count_query_out) . " ORDER BY exit_time DESC LIMIT $per_page OFFSET $offset_out";
$stmt = $pdo->prepare($data_query_out);
$stmt->execute($params_out);
$gate_outs = $stmt->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<?php if (!$can_input): ?>
    <div class="alert alert-info" style="margin-bottom: 1.5rem; display: flex; align-items: center; justify-content: space-between;">
        <div>
            <strong>👁️ Mode Manager (View-Only / Hanya Lihat Data):</strong> Anda sedang melihat data pemantauan logistik. Penginputan data hanya dapat dilakukan oleh akun Staf Secom.
        </div>
        <span class="badge badge-secondary">Read Only</span>
    </div>
<?php endif; ?>

<!-- TAB FILTER SWITCHER -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
        <a href="gate_in.php" class="btn btn-sm btn-outline">
            📥 Buku Masuk (Gate In)
        </a>
        <a href="gate_out.php" class="btn btn-sm btn-primary">
            📤 Buku Keluar (Gate Out)
        </a>
        <a href="export_nex.php" class="btn btn-sm btn-outline">
            🚢 Export NEX / MOPOR
        </a>
    </div>
    <div>
        <button type="button" onclick="window.print()" class="btn btn-outline btn-sm">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
            Cetak / Print
        </button>
    </div>
</div>

<!-- SECTION: BUKU KELUAR (GATE OUT) -->
<div class="card" style="border-top: 4px solid var(--primary);">
    <div class="card-header" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h3 class="card-title">
                <span class="badge badge-primary" style="font-size: 0.85rem;"><?= number_format($total_out_records) ?> Armada</span>
                Buku Keluar Kendaraan (Gate Out)
            </h3>
            <small style="color: var(--text-muted);">Pencatatan keberangkatan armada pengiriman barang non-ekspor</small>
        </div>
        <?php if ($can_input): ?>
            <button type="button" onclick="openModal('modalGateOut')" class="btn btn-primary btn-sm">+ Input Gate Out (Keluar)</button>
        <?php endif; ?>
    </div>

    <!-- Search Bar Gate Out -->
    <form method="GET" action="gate_out.php" style="margin: 1rem 0;">
        <div style="display: flex; gap: 0.5rem; max-width: 540px; width: 100%; align-items: center;">
            <input type="text" name="search_out" class="form-control" placeholder="Cari Nopol, Sopir, No. DO, Tujuan, Transportir..." value="<?= htmlspecialchars($search_out) ?>" style="flex: 1;">
            <button type="submit" class="btn btn-secondary" style="white-space: nowrap; padding: 0.6rem 1.1rem;">Cari</button>
            <?php if (!empty($search_out)): ?>
                <a href="gate_out.php" class="btn btn-outline" style="white-space: nowrap; padding: 0.6rem 0.9rem;">Reset</a>
            <?php endif; ?>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nopol</th>
                    <th>Nama Sopir</th>
                    <th>No. DO</th>
                    <th>Tujuan</th>
                    <th>Tonase (Ton)</th>
                    <th>Transportir</th>
                    <th>Waktu Keluar</th>
                    <th>Status</th>
                    <?php if ($can_input): ?><th style="text-align: center;">Aksi</th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (count($gate_outs) === 0): ?>
                    <tr>
                        <td colspan="<?= $can_input ? '10' : '9' ?>" style="text-align: center; color: var(--text-muted); padding: 1.75rem;">Belum ada data kendaraan keluar.</td>
                    </tr>
                <?php else: ?>
                    <?php $no = $offset_out + 1; foreach ($gate_outs as $go): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><code><strong><?= htmlspecialchars($go['nopol']) ?></strong></code></td>
                            <td><?= htmlspecialchars($go['driver_name']) ?></td>
                            <td><code><?= htmlspecialchars($go['do_number']) ?></code></td>
                            <td><?= htmlspecialchars($go['destination']) ?></td>
                            <td><strong><?= number_format($go['tonnage'], 2) ?></strong></td>
                            <td><?= htmlspecialchars($go['transportir'] ?: '-') ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($go['exit_time'])) ?></td>
                            <td><span class="badge badge-success"><?= htmlspecialchars($go['status']) ?></span></td>
                            <?php if ($can_input): ?>
                                <td style="text-align: center;">
                                    <form method="POST" action="gate_out.php" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?');" style="display: inline;">
                                        <input type="hidden" name="action" value="delete_gate_out">
                                        <input type="hidden" name="id" value="<?= $go['id'] ?>">
                                        <button type="submit" class="btn btn-danger btn-sm" style="padding: 0.2rem 0.5rem; font-size: 0.75rem;">Hapus</button>
                                    </form>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?= render_pagination($page_out, $total_out_pages, ['search_out' => $search_out], 'page_out') ?>
</div>

<!-- MODAL GATE OUT -->
<?php if ($can_input): ?>
<div id="modalGateOut" class="modal-backdrop">
    <div class="modal-dialog" style="max-width: 760px; width: 95%;">
        <div class="modal-header">
            <h3 class="modal-title">Form Input Kendaraan Keluar (Gate Out)</h3>
            <button type="button" class="modal-close" onclick="closeModal('modalGateOut')">&times;</button>
        </div>
        <form method="POST" action="gate_out.php">
            <input type="hidden" name="action" value="add_gate_out">
            <div class="modal-body">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.25rem;">
                    
                    <!-- LEFT COLUMN -->
                    <div>
                        <div class="form-group">
                            <label class="form-label" style="font-weight: 600;">Nomor Polisi (Nopol) *</label>
                            <input type="text" name="nopol" required class="form-control" placeholder="Masukkan Nopol (e.g. B 1234 XYZ)...">
                        </div>

                        <div class="form-group">
                            <label class="form-label" style="font-weight: 600;">Nomor Delivery Order (DO) *</label>
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
                            <label class="form-label" style="font-weight: 600;">Nama Sopir *</label>
                            <input type="text" name="driver_name" required class="form-control" placeholder="Nama sopir...">
                        </div>

                        <div class="form-group">
                            <label class="form-label" style="font-weight: 600;">Tujuan Pengiriman *</label>
                            <input type="text" name="destination" required class="form-control" placeholder="Tujuan Pengiriman / Alamat...">
                        </div>

                        <div class="form-group">
                            <label class="form-label" style="font-weight: 600;">Tanggal &amp; Waktu Keluar *</label>
                            <input type="datetime-local" name="exit_time" required class="form-control" value="<?= date('Y-m-d\TH:i') ?>">
                        </div>
                    </div>

                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalGateOut')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Gate Out</button>
            </div>
        </form>
    </div>
</div>

<script>
function openModal(id) {
    document.getElementById(id).classList.add('show');
}
function closeModal(id) {
    document.getElementById(id).classList.remove('show');
}
</script>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
