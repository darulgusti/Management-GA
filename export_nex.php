<?php
$page_title = 'Export NEX / MOPOR - Logistik';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth_check.php';

check_role(['manager', 'secom']);
$logged_user = get_logged_user();
$can_input = ($logged_user['role'] === 'secom');

// Auto Ensure Table Exists
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS `logistic_export_nex_mopors` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `mopor_number` VARCHAR(100) NOT NULL,
      `driver_name` VARCHAR(255) NOT NULL,
      `do_number` VARCHAR(100) NOT NULL,
      `container_number` VARCHAR(100) NOT NULL,
      `seal_number` VARCHAR(100) NOT NULL,
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
        header("Location: export_nex.php");
        exit();
    }

    $action = $_POST['action'] ?? '';

    if ($action === 'add_export') {
        $mopor_number = trim($_POST['mopor_number'] ?? '');
        $driver_name = trim($_POST['driver_name'] ?? '');
        $do_number = trim($_POST['do_number'] ?? '');
        $container_number = trim($_POST['container_number'] ?? '');
        $seal_number = trim($_POST['seal_number'] ?? '');
        $destination = trim($_POST['destination'] ?? '');
        $tonnage = floatval($_POST['tonnage'] ?? 0);
        $transportir = trim($_POST['transportir'] ?? '');
        $exit_time = !empty($_POST['exit_time']) ? date('Y-m-d H:i:s', strtotime($_POST['exit_time'])) : date('Y-m-d H:i:s');

        if (!empty($mopor_number) && !empty($driver_name) && !empty($container_number)) {
            $stmt = $pdo->prepare("INSERT INTO logistic_export_nex_mopors (mopor_number, driver_name, do_number, container_number, seal_number, destination, tonnage, transportir, exit_time) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$mopor_number, $driver_name, $do_number, $container_number, $seal_number, $destination, $tonnage, $transportir, $exit_time]);
            set_flash_message('success', 'Data Export NEX / MOPOR berhasil disimpan.');
        } else {
            set_flash_message('danger', 'Mohon lengkapi No. MOPOR, Driver, dan No. Kontainer!');
        }
    } elseif ($action === 'delete_export') {
        $id = intval($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $pdo->prepare("DELETE FROM logistic_export_nex_mopors WHERE id = ?");
            $stmt->execute([$id]);
            set_flash_message('success', 'Data Export NEX/MOPOR berhasil dihapus.');
        }
    }

    header("Location: export_nex.php");
    exit();
}

$per_page = 10;

// Search & Pagination Export NEX/MOPOR
$search_exp = trim($_GET['search_exp'] ?? '');
$page_exp = max(1, intval($_GET['page_exp'] ?? 1));
$count_query_exp = "SELECT COUNT(*) FROM logistic_export_nex_mopors WHERE 1=1";
$params_exp = [];
if (!empty($search_exp)) {
    $count_query_exp .= " AND (mopor_number LIKE ? OR driver_name LIKE ? OR container_number LIKE ? OR seal_number LIKE ? OR do_number LIKE ?)";
    $term = "%$search_exp%";
    $params_exp = [$term, $term, $term, $term, $term];
}
$stmt = $pdo->prepare($count_query_exp);
$stmt->execute($params_exp);
$total_exp_records = $stmt->fetchColumn();
$total_exp_pages = ceil($total_exp_records / $per_page);
$offset_exp = ($page_exp - 1) * $per_page;

$data_query_exp = str_replace("SELECT COUNT(*)", "SELECT *", $count_query_exp) . " ORDER BY exit_time DESC LIMIT $per_page OFFSET $offset_exp";
$stmt = $pdo->prepare($data_query_exp);
$stmt->execute($params_exp);
$exports = $stmt->fetchAll();

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
        <a href="gate_out.php" class="btn btn-sm btn-outline">
            📤 Buku Keluar (Gate Out)
        </a>
        <a href="export_nex.php" class="btn btn-sm btn-primary">
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

<!-- SECTION: EXPORT NEX / MOPOR -->
<div class="card" style="border-top: 4px solid var(--primary);">
    <div class="card-header" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h3 class="card-title">
                <span class="badge badge-primary" style="font-size: 0.85rem;"><?= number_format($total_exp_records) ?> Kontainer</span>
                Export NEX / MOPOR (Kontainer Logistik Ekspor)
            </h3>
            <small style="color: var(--text-muted);">Pencatatan khusus armada kontainer logistik ekspor &amp; MOPOR</small>
        </div>
        <?php if ($can_input): ?>
            <button type="button" onclick="openModal('modalExport')" class="btn btn-primary btn-sm">+ Input Export NEX/MOPOR</button>
        <?php endif; ?>
    </div>

    <!-- Search Bar Export -->
    <form method="GET" action="export_nex.php" style="margin: 1rem 0;">
        <div style="display: flex; gap: 0.5rem; max-width: 540px; width: 100%; align-items: center;">
            <input type="text" name="search_exp" class="form-control" placeholder="Cari No. MOPOR, Sopir, No. Kontainer, No. Segel, No. DO..." value="<?= htmlspecialchars($search_exp) ?>" style="flex: 1;">
            <button type="submit" class="btn btn-secondary" style="white-space: nowrap; padding: 0.6rem 1.1rem;">Cari</button>
            <?php if (!empty($search_exp)): ?>
                <a href="export_nex.php" class="btn btn-outline" style="white-space: nowrap; padding: 0.6rem 0.9rem;">Reset</a>
            <?php endif; ?>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>No. MOPOR</th>
                    <th>Nama Sopir</th>
                    <th>No. DO</th>
                    <th>No. Kontainer</th>
                    <th>No. Segel</th>
                    <th>Tujuan</th>
                    <th>Tonase (Ton)</th>
                    <th>Transportir</th>
                    <th>Waktu Keluar</th>
                    <th>Status</th>
                    <?php if ($can_input): ?><th style="text-align: center;">Aksi</th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (count($exports) === 0): ?>
                    <tr>
                        <td colspan="<?= $can_input ? '12' : '11' ?>" style="text-align: center; color: var(--text-muted); padding: 1.75rem;">Belum ada data ekspor kontainer.</td>
                    </tr>
                <?php else: ?>
                    <?php $no = $offset_exp + 1; foreach ($exports as $ex): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><code><strong><?= htmlspecialchars($ex['mopor_number']) ?></strong></code></td>
                            <td><?= htmlspecialchars($ex['driver_name']) ?></td>
                            <td><code><?= htmlspecialchars($ex['do_number'] ?: '-') ?></code></td>
                            <td><code><?= htmlspecialchars($ex['container_number']) ?></code></td>
                            <td><code><?= htmlspecialchars($ex['seal_number']) ?></code></td>
                            <td><?= htmlspecialchars($ex['destination'] ?: '-') ?></td>
                            <td><strong><?= number_format($ex['tonnage'], 2) ?></strong></td>
                            <td><?= htmlspecialchars($ex['transportir'] ?: '-') ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($ex['exit_time'])) ?></td>
                            <td><span class="badge badge-success"><?= htmlspecialchars($ex['status']) ?></span></td>
                            <?php if ($can_input): ?>
                                <td style="text-align: center;">
                                    <form method="POST" action="export_nex.php" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?');" style="display: inline;">
                                        <input type="hidden" name="action" value="delete_export">
                                        <input type="hidden" name="id" value="<?= $ex['id'] ?>">
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
    <?= render_pagination($page_exp, $total_exp_pages, ['search_exp' => $search_exp], 'page_exp') ?>
</div>

<!-- MODAL EXPORT NEX / MOPOR -->
<?php if ($can_input): ?>
<div id="modalExport" class="modal-backdrop">
    <div class="modal-dialog" style="max-width: 760px; width: 95%;">
        <div class="modal-header">
            <h3 class="modal-title">Form Input Export NEX / MOPOR</h3>
            <button type="button" class="modal-close" onclick="closeModal('modalExport')">&times;</button>
        </div>
        <form method="POST" action="export_nex.php">
            <input type="hidden" name="action" value="add_export">
            <div class="modal-body">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.25rem;">
                    
                    <!-- LEFT COLUMN -->
                    <div>
                        <div class="form-group">
                            <label class="form-label" style="font-weight: 600;">Nomor MOPOR *</label>
                            <input type="text" name="mopor_number" required class="form-control" placeholder="Masukkan MOPOR...">
                        </div>

                        <div class="form-group">
                            <label class="form-label" style="font-weight: 600;">Nomor DO</label>
                            <input type="text" name="do_number" class="form-control" placeholder="Nomor DO...">
                        </div>

                        <div class="form-group">
                            <label class="form-label" style="font-weight: 600;">Segel *</label>
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
                            <label class="form-label" style="font-weight: 600;">Sopir *</label>
                            <input type="text" name="driver_name" required class="form-control" placeholder="Nama sopir...">
                        </div>

                        <div class="form-group">
                            <label class="form-label" style="font-weight: 600;">Kontainer *</label>
                            <input type="text" name="container_number" required class="form-control" placeholder="Nomor Kontainer...">
                        </div>

                        <div class="form-group">
                            <label class="form-label" style="font-weight: 600;">Tujuan</label>
                            <input type="text" name="destination" class="form-control" placeholder="Pelabuhan / Negara Tujuan...">
                        </div>

                        <div class="form-group">
                            <label class="form-label" style="font-weight: 600;">Tanggal &amp; Waktu Keluar *</label>
                            <input type="datetime-local" name="exit_time" required class="form-control" value="<?= date('Y-m-d\TH:i') ?>">
                        </div>
                    </div>

                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalExport')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Export</button>
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
