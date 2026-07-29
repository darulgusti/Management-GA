<?php
$page_title = 'Pos 4 - Gate Pass System';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth_check.php';

check_role(['manager', 'secom']);
$logged_user = get_logged_user();
$can_input = ($logged_user['role'] === 'secom');

$active_tab = $_GET['tab'] ?? 'all';

// Auto Ensure Tables Exist & Schema Migrated
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS `logistic_gate_ins` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `nopol` VARCHAR(50) NOT NULL,
      `driver_name` VARCHAR(255) NOT NULL,
      `visitor_number` VARCHAR(100) NULL,
      `antree_number` VARCHAR(100) NULL,
      `transportir` VARCHAR(255) NOT NULL,
      `destination` VARCHAR(100) NOT NULL DEFAULT 'Kirim',
      `sim_type` VARCHAR(50) DEFAULT 'SIM B',
      `checklist_sim` TINYINT(1) DEFAULT 0,
      `checklist_stnk` TINYINT(1) DEFAULT 0,
      `checklist_kir` TINYINT(1) DEFAULT 0,
      `entry_time` DATETIME NOT NULL,
      `status` VARCHAR(50) NOT NULL DEFAULT 'Done',
      `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    try { $pdo->exec("ALTER TABLE `logistic_gate_ins` ADD COLUMN `visitor_number` VARCHAR(100) NULL AFTER `driver_name`"); } catch (Exception $e) {}
    try { $pdo->exec("ALTER TABLE `logistic_gate_ins` ADD COLUMN `antree_number` VARCHAR(100) NULL AFTER `visitor_number`"); } catch (Exception $e) {}
    try { $pdo->exec("ALTER TABLE `logistic_gate_ins` ADD COLUMN `sim_type` VARCHAR(50) DEFAULT 'SIM B' AFTER `destination`"); } catch (Exception $e) {}
    try { $pdo->exec("ALTER TABLE `logistic_gate_ins` ADD COLUMN `exit_time` DATETIME NULL AFTER `entry_time`"); } catch (Exception $e) {}

    $pdo->exec("CREATE TABLE IF NOT EXISTS `logistic_gate_outs` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `nopol` VARCHAR(50) NOT NULL,
      `driver_name` VARCHAR(255) NOT NULL,
      `do_number` VARCHAR(100) NOT NULL,
      `destination` VARCHAR(255) NOT NULL,
      `tonnage` VARCHAR(100) NOT NULL DEFAULT '-',
      `transportir` VARCHAR(255) NOT NULL,
      `document_photo` LONGTEXT NULL,
      `exit_time` DATETIME NOT NULL,
      `status` VARCHAR(50) NOT NULL DEFAULT 'Done',
      `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    try { $pdo->exec("ALTER TABLE `logistic_gate_outs` ADD COLUMN `document_photo` LONGTEXT NULL AFTER `transportir`"); } catch (Exception $e) {}

    $pdo->exec("CREATE TABLE IF NOT EXISTS `logistic_export_nex_mopors` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `mopor_number` VARCHAR(100) NOT NULL,
      `driver_name` VARCHAR(255) NOT NULL DEFAULT '-',
      `do_number` VARCHAR(100) NOT NULL,
      `container_number` VARCHAR(100) NOT NULL,
      `seal_number` VARCHAR(100) NOT NULL,
      `destination` VARCHAR(255) NOT NULL,
      `tonnage` VARCHAR(100) NOT NULL DEFAULT '-',
      `transportir` VARCHAR(255) NOT NULL DEFAULT '-',
      `document_photo` LONGTEXT NULL,
      `exit_time` DATETIME NOT NULL,
      `status` VARCHAR(50) NOT NULL DEFAULT 'Done',
      `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    try { $pdo->exec("ALTER TABLE `logistic_export_nex_mopors` ADD COLUMN `document_photo` LONGTEXT NULL AFTER `transportir`"); } catch (Exception $e) {}
} catch (Exception $e) {}

// Handle POST Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try { $pdo->exec("ALTER TABLE `logistic_gate_outs` MODIFY COLUMN `tonnage` VARCHAR(100) NOT NULL DEFAULT '-'"); } catch (Exception $e) {}
    try { $pdo->exec("ALTER TABLE `logistic_export_nex_mopors` MODIFY COLUMN `tonnage` VARCHAR(100) NOT NULL DEFAULT '-'"); } catch (Exception $e) {}

    $action = $_POST['action'] ?? '';

    if ($can_input) {
        if ($action === 'add_gate_in') {
            $nopol = trim($_POST['nopol'] ?? '');
            $driver_name = trim($_POST['driver_name'] ?? '');
            $visitor_number = trim($_POST['visitor_number'] ?? '');
            $antree_number = trim($_POST['antree_number'] ?? '');
            $transportir = trim($_POST['transportir'] ?? '');
            $destination = trim($_POST['destination'] ?? 'Kirim');
            $sim_type = trim($_POST['sim_type'] ?? 'SIM B');
            $sim = ($sim_type !== 'Tidak Ada') ? 1 : 0;
            $stnk = isset($_POST['checklist_stnk']) ? 1 : 0;
            $kir = isset($_POST['checklist_kir']) ? 1 : 0;
            $entry_time = !empty($_POST['entry_time']) ? date('Y-m-d H:i:s', strtotime($_POST['entry_time'])) : date('Y-m-d H:i:s');

            if (!empty($nopol) && !empty($driver_name)) {
                $stmt = $pdo->prepare("INSERT INTO logistic_gate_ins (nopol, driver_name, visitor_number, antree_number, transportir, destination, sim_type, checklist_sim, checklist_stnk, checklist_kir, entry_time, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Masuk')");
                $stmt->execute([$nopol, $driver_name, $visitor_number, $antree_number, $transportir, $destination, $sim_type, $sim, $stnk, $kir, $entry_time]);
                set_flash_message('success', 'Data Kendaraan Masuk (Gate In) berhasil disimpan.');
            } else {
                set_flash_message('danger', 'Mohon lengkapi field Nopol dan Nama Sopir!');
            }
        } elseif ($action === 'checkout_export_ajinex') {
            // Checkout untuk armada dengan tujuan Export Ajinex
            $gate_in_id = intval($_POST['gate_in_id'] ?? 0);
            $nopol = trim($_POST['nopol'] ?? '');
            $driver_name = trim($_POST['driver_name'] ?? '');
            $transportir = trim($_POST['transportir'] ?? '-');
            $tonnage = trim($_POST['tonnage'] ?? '');
            $destination = trim($_POST['destination'] ?? '');
            $document_photo = $_POST['document_photo'] ?? '';
            $exit_time = !empty($_POST['exit_time']) ? date('Y-m-d H:i:s', strtotime($_POST['exit_time'])) : date('Y-m-d H:i:s');

            if ($gate_in_id > 0 && !empty($nopol) && !empty($destination) && !empty($tonnage)) {
                $stmt = $pdo->prepare("INSERT INTO logistic_gate_outs (nopol, driver_name, do_number, destination, tonnage, transportir, document_photo, exit_time) VALUES (?, ?, '-', ?, ?, ?, ?, ?)");
                $stmt->execute([$nopol, $driver_name, $destination, $tonnage, $transportir, $document_photo, $exit_time]);

                $stmt = $pdo->prepare("UPDATE logistic_gate_ins SET status = 'Checked Out', exit_time = ? WHERE id = ?");
                $stmt->execute([$exit_time, $gate_in_id]);

                set_flash_message('success', 'Check-out armada Export Ajinex berhasil diproses.');
            } else {
                set_flash_message('danger', 'Mohon isi Total Nett Weight dan Alamat Kirim / Tujuan!');
            }
        } elseif ($action === 'checkout_edc') {
            // Checkout untuk armada dengan tujuan EDC
            $gate_in_id = intval($_POST['gate_in_id'] ?? 0);
            $nopol = trim($_POST['nopol'] ?? '');
            $driver_name = trim($_POST['driver_name'] ?? '');
            $transportir = trim($_POST['transportir'] ?? '-');
            $mopor_number = trim($_POST['mopor_number'] ?? '');
            $do_number = trim($_POST['do_number'] ?? '');
            $seal_number = trim($_POST['seal_number'] ?? '');
            $tonnage = trim($_POST['tonnage'] ?? '');
            $container_number = trim($_POST['container_number'] ?? '');
            $destination = trim($_POST['destination'] ?? '');
            $document_photo = $_POST['document_photo'] ?? '';
            $exit_time = !empty($_POST['exit_time']) ? date('Y-m-d H:i:s', strtotime($_POST['exit_time'])) : date('Y-m-d H:i:s');

            if ($gate_in_id > 0 && !empty($mopor_number) && !empty($do_number) && !empty($seal_number) && !empty($container_number) && !empty($destination)) {
                $stmt = $pdo->prepare("INSERT INTO logistic_export_nex_mopors (mopor_number, driver_name, do_number, container_number, seal_number, destination, tonnage, transportir, document_photo, exit_time) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$mopor_number, $driver_name, $do_number, $container_number, $seal_number, $destination, $tonnage, $transportir, $document_photo, $exit_time]);

                $stmt = $pdo->prepare("UPDATE logistic_gate_ins SET status = 'Checked Out', exit_time = ? WHERE id = ?");
                $stmt->execute([$exit_time, $gate_in_id]);

                set_flash_message('success', 'Check-out armada EDC berhasil diproses.');
            } else {
                set_flash_message('danger', 'Mohon lengkapi Nomor Ekspor, Nomor DO, Segel, Kontainer, dan Tujuan!');
            }
        } elseif ($action === 'delete_gate_in') {
            $id = intval($_POST['id'] ?? 0);
            if ($id > 0) {
                $stmt = $pdo->prepare("DELETE FROM logistic_gate_ins WHERE id = ?");
                $stmt->execute([$id]);
                set_flash_message('success', 'Data Gate In berhasil dihapus.');
            }
        } elseif ($action === 'delete_gate_out') {
            $id = intval($_POST['id'] ?? 0);
            if ($id > 0) {
                $stmt = $pdo->prepare("DELETE FROM logistic_gate_outs WHERE id = ?");
                $stmt->execute([$id]);
                set_flash_message('success', 'Data Gate Out berhasil dihapus.');
            }
        } elseif ($action === 'delete_export') {
            $id = intval($_POST['id'] ?? 0);
            if ($id > 0) {
                $stmt = $pdo->prepare("DELETE FROM logistic_export_nex_mopors WHERE id = ?");
                $stmt->execute([$id]);
                set_flash_message('success', 'Data Export NEX / NOPOR berhasil dihapus.');
            }
        }
    }

    if ($logged_user['role'] === 'manager') {
        if ($action === 'edit_gate_in') {
            $id = intval($_POST['id'] ?? 0);
            $nopol = trim($_POST['nopol'] ?? '');
            $driver_name = trim($_POST['driver_name'] ?? '');
            $visitor_number = trim($_POST['visitor_number'] ?? '');
            $antree_number = trim($_POST['antree_number'] ?? '');
            $transportir = trim($_POST['transportir'] ?? '');
            $destination = trim($_POST['destination'] ?? 'Kirim');
            $sim_type = trim($_POST['sim_type'] ?? 'SIM B');
            $stnk = isset($_POST['checklist_stnk']) ? 1 : 0;
            $kir = isset($_POST['checklist_kir']) ? 1 : 0;

            if ($id > 0 && !empty($nopol) && !empty($driver_name)) {
                $stmt = $pdo->prepare("UPDATE logistic_gate_ins SET nopol = ?, driver_name = ?, visitor_number = ?, antree_number = ?, transportir = ?, destination = ?, sim_type = ?, checklist_stnk = ?, checklist_kir = ? WHERE id = ?");
                $stmt->execute([$nopol, $driver_name, $visitor_number, $antree_number, $transportir, $destination, $sim_type, $stnk, $kir, $id]);
                set_flash_message('success', 'Data Gate In berhasil diperbarui oleh Manager.');
            }
        } elseif ($action === 'edit_gate_out') {
            $id = intval($_POST['id'] ?? 0);
            $nopol = trim($_POST['nopol'] ?? '');
            $driver_name = trim($_POST['driver_name'] ?? '');
            $destination = trim($_POST['destination'] ?? '');
            $tonnage = trim($_POST['tonnage'] ?? '');
            $transportir = trim($_POST['transportir'] ?? '');

            if ($id > 0 && !empty($nopol) && !empty($driver_name)) {
                $stmt = $pdo->prepare("UPDATE logistic_gate_outs SET nopol = ?, driver_name = ?, destination = ?, tonnage = ?, transportir = ? WHERE id = ?");
                $stmt->execute([$nopol, $driver_name, $destination, $tonnage, $transportir, $id]);
                set_flash_message('success', 'Data Keluar EDC berhasil diperbarui oleh Manager.');
            }
        } elseif ($action === 'edit_export') {
            $id = intval($_POST['id'] ?? 0);
            $mopor_number = trim($_POST['mopor_number'] ?? '');
            $do_number = trim($_POST['do_number'] ?? '');
            $container_number = trim($_POST['container_number'] ?? '');
            $seal_number = trim($_POST['seal_number'] ?? '');
            $destination = trim($_POST['destination'] ?? '');
            $tonnage = trim($_POST['tonnage'] ?? '');

            if ($id > 0 && !empty($mopor_number) && !empty($container_number)) {
                $stmt = $pdo->prepare("UPDATE logistic_export_nex_mopors SET mopor_number = ?, do_number = ?, container_number = ?, seal_number = ?, destination = ?, tonnage = ? WHERE id = ?");
                $stmt->execute([$mopor_number, $do_number, $container_number, $seal_number, $destination, $tonnage, $id]);
                set_flash_message('success', 'Data Export NEX berhasil diperbarui oleh Manager.');
            }
        }
    }

    header("Location: logistic.php?tab=" . urlencode($active_tab));
    exit();
}

$per_page = 10;

// Search & Pagination Gate In
$search_in = trim($_GET['search_in'] ?? '');
$page_in = max(1, intval($_GET['page_in'] ?? 1));
$count_query_in = "SELECT COUNT(*) FROM logistic_gate_ins WHERE 1=1";
$params_in = [];
if (!empty($search_in)) {
    $count_query_in .= " AND (LOWER(nopol) LIKE LOWER(?) OR LOWER(driver_name) LIKE LOWER(?) OR LOWER(visitor_number) LIKE LOWER(?) OR LOWER(antree_number) LIKE LOWER(?) OR LOWER(transportir) LIKE LOWER(?) OR LOWER(destination) LIKE LOWER(?))";
    $term = "%$search_in%";
    $params_in = [$term, $term, $term, $term, $term, $term];
}
$stmt = $pdo->prepare($count_query_in);
$stmt->execute($params_in);
$total_in_records = $stmt->fetchColumn();
$total_in_pages = ceil($total_in_records / $per_page);
$offset_in = ($page_in - 1) * $per_page;

$data_query_in = str_replace("SELECT COUNT(*)", "SELECT *", $count_query_in) . " ORDER BY entry_time DESC LIMIT $per_page OFFSET $offset_in";
$stmt = $pdo->prepare($data_query_in);
$stmt->execute($params_in);
$gate_ins = $stmt->fetchAll();

// Search & Pagination Gate Out
$search_out = trim($_GET['search_out'] ?? '');
$page_out = max(1, intval($_GET['page_out'] ?? 1));
$count_query_out = "SELECT COUNT(*) FROM logistic_gate_outs WHERE 1=1";
$params_out = [];
if (!empty($search_out)) {
    $count_query_out .= " AND (LOWER(nopol) LIKE LOWER(?) OR LOWER(driver_name) LIKE LOWER(?) OR LOWER(do_number) LIKE LOWER(?) OR LOWER(destination) LIKE LOWER(?) OR LOWER(transportir) LIKE LOWER(?))";
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

// Search & Pagination Export NEX/NOPOR
$search_exp = trim($_GET['search_exp'] ?? '');
$page_exp = max(1, intval($_GET['page_exp'] ?? 1));
$count_query_exp = "SELECT COUNT(*) FROM logistic_export_nex_mopors WHERE 1=1";
$params_exp = [];
if (!empty($search_exp)) {
    $count_query_exp .= " AND (LOWER(mopor_number) LIKE LOWER(?) OR LOWER(driver_name) LIKE LOWER(?) OR LOWER(container_number) LIKE LOWER(?) OR LOWER(seal_number) LIKE LOWER(?) OR LOWER(do_number) LIKE LOWER(?) OR LOWER(destination) LIKE LOWER(?) OR LOWER(transportir) LIKE LOWER(?))";
    $term = "%$search_exp%";
    $params_exp = [$term, $term, $term, $term, $term, $term, $term];
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

<!-- TAB FILTER SWITCHER -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
        <a href="logistic.php?tab=all" class="btn btn-sm <?= $active_tab === 'all' ? 'btn-primary' : 'btn-outline' ?>">
            📊 Semua Pos 4
        </a>
        <a href="logistic.php?tab=gate_in" class="btn btn-sm <?= $active_tab === 'gate_in' ? 'btn-primary' : 'btn-outline' ?>">
            📥 Buku Masuk (Gate In)
        </a>
        <a href="logistic.php?tab=gate_out" class="btn btn-sm <?= $active_tab === 'gate_out' ? 'btn-primary' : 'btn-outline' ?>">
            📤 Keluar EDC
        </a>
        <a href="logistic.php?tab=export_nex" class="btn btn-sm <?= $active_tab === 'export_nex' ? 'btn-primary' : 'btn-outline' ?>">
            🚢 Keluar Export NEX
        </a>
    </div>
    <div>
        <button type="button" onclick="window.print()" class="btn btn-outline btn-sm">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
            Cetak / Print
        </button>
    </div>
</div>

<?php if ($active_tab === 'all' || $active_tab === 'gate_in'): ?>
<!-- SECTION 1: BUKU MASUK (GATE IN) -->
<div id="sec-gatein" class="card" style="border-top: 4px solid var(--primary);">
    <div class="card-header" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h3 class="card-title">
                <span class="badge badge-primary" style="font-size: 0.85rem;"><?= number_format($total_in_records) ?> Kendaraan</span>
                1. Buku Masuk Kendaraan (Gate In Pos 4)
            </h3>
        </div>
        <?php if ($can_input): ?>
            <button type="button" onclick="openModal('modalGateIn')" class="btn btn-primary btn-sm">+ Input Gate In Baru</button>
        <?php endif; ?>
    </div>

    <!-- Search Bar Gate In -->
    <form method="GET" action="logistic.php" style="margin: 1rem 0;">
        <input type="hidden" name="tab" value="<?= htmlspecialchars($active_tab) ?>">
        <div style="display: flex; gap: 0.5rem; max-width: 540px; width: 100%; align-items: center;">
            <input type="text" name="search_in" class="form-control" placeholder="Cari Nopol, Sopir, Transportir, Tujuan..." value="<?= htmlspecialchars($search_in) ?>" style="flex: 1;">
            <button type="submit" class="btn btn-secondary" style="white-space: nowrap; padding: 0.6rem 1.1rem;">Cari</button>
            <?php if (!empty($search_in)): ?>
                <a href="logistic.php?tab=<?= urlencode($active_tab) ?>" class="btn btn-outline" style="white-space: nowrap; padding: 0.6rem 0.9rem;">Reset</a>
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
                    <th>Visitor No</th>
                    <th>Antre No</th>
                    <th>Transportir</th>
                    <th>Tujuan</th>
                    <th>Checklist Berkas</th>
                    <th>Waktu Masuk</th>
                    <th>Status</th>
                    <?php if ($can_input || $logged_user['role'] === 'manager'): ?><th style="text-align: center;">Aksi</th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (count($gate_ins) === 0): ?>
                    <tr>
                        <td colspan="<?= ($can_input || $logged_user['role'] === 'manager') ? '11' : '10' ?>" style="text-align: center; color: var(--text-muted); padding: 1.75rem;">Belum ada data transaksi kendaraan masuk.</td>
                    </tr>
                <?php else: ?>
                    <?php $no = $offset_in + 1; foreach ($gate_ins as $gi): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><code><strong><?= htmlspecialchars($gi['nopol']) ?></strong></code></td>
                            <td><?= htmlspecialchars($gi['driver_name']) ?></td>
                            <td><code><?= htmlspecialchars($gi['visitor_number'] ?: '-') ?></code></td>
                            <td><code><?= htmlspecialchars($gi['antree_number'] ?: '-') ?></code></td>
                            <td><?= htmlspecialchars($gi['transportir'] ?: '-') ?></td>
                            <td><span class="badge badge-info"><?= htmlspecialchars($gi['destination']) ?></span></td>
                            <td>
                                <div style="display: flex; gap: 0.25rem; flex-wrap: wrap;">
                                    <span class="badge badge-info" title="Jenis SIM"><?= htmlspecialchars($gi['sim_type'] ?? 'SIM B') ?></span>
                                    <span class="badge <?= $gi['checklist_stnk'] ? 'badge-success' : 'badge-secondary' ?>" title="STNK">STNK <?= $gi['checklist_stnk'] ? '✓' : '✗' ?></span>
                                    <span class="badge <?= $gi['checklist_kir'] ? 'badge-success' : 'badge-secondary' ?>" title="KIR">KIR <?= $gi['checklist_kir'] ? '✓' : '✗' ?></span>
                                    <?php if (!empty($gi['document_photo'])): ?>
                                        <button type="button" onclick="showDocumentPhoto('<?= htmlspecialchars($gi['document_photo']) ?>')" class="badge badge-warning" style="border:none; cursor:pointer;" title="Lihat Foto Dokumen">📷 Foto Dokumen</button>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td><?= date('d/m/Y H:i', strtotime($gi['entry_time'])) ?></td>
                            <td>
                                <?php if ($gi['status'] === 'Checked Out'): ?>
                                    <span class="badge badge-secondary" title="Waktu Keluar: <?= $gi['exit_time'] ? date('d/m/Y H:i', strtotime($gi['exit_time'])) : '-' ?>">Checked Out</span>
                                <?php else: ?>
                                    <span class="badge badge-success">Masih Masuk</span>
                                <?php endif; ?>
                            </td>
                            <?php if ($can_input || $logged_user['role'] === 'manager'): ?>
                                <td style="text-align: center; gap: 0.25rem;">
                                    <?php if ($can_input): ?>
                                        <?php if ($gi['status'] !== 'Checked Out' && ($gi['destination'] === 'Export Ajinex' || $gi['destination'] === 'EDC')): ?>
                                            <?php if ($gi['destination'] === 'Export Ajinex'): ?>
                                                <button type="button" class="btn btn-warning btn-sm" style="padding: 0.2rem 0.5rem; font-size: 0.75rem; margin-right: 0.2rem;" onclick='openCheckoutExportAjinex(<?= json_encode($gi) ?>)'>Check-out</button>
                                            <?php elseif ($gi['destination'] === 'EDC'): ?>
                                                <button type="button" class="btn btn-warning btn-sm" style="padding: 0.2rem 0.5rem; font-size: 0.75rem; margin-right: 0.2rem;" onclick='openCheckoutEDC(<?= json_encode($gi) ?>)'>Check-out</button>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                        <form method="POST" action="logistic.php?tab=<?= urlencode($active_tab) ?>" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?');" style="display: inline;">
                                            <input type="hidden" name="action" value="delete_gate_in">
                                            <input type="hidden" name="id" value="<?= $gi['id'] ?>">
                                            <button type="submit" class="btn btn-danger btn-sm" style="padding: 0.2rem 0.5rem; font-size: 0.75rem;">Hapus</button>
                                        </form>
                                    <?php endif; ?>
                                    <?php if ($logged_user['role'] === 'manager'): ?>
                                        <button type="button" class="btn btn-primary btn-sm" style="padding: 0.2rem 0.5rem; font-size: 0.75rem;" onclick='editGateIn(<?= json_encode($gi) ?>)'>Edit</button>
                                    <?php endif; ?>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?= render_pagination($page_in, $total_in_pages, ['tab' => $active_tab, 'search_in' => $search_in], 'page_in') ?>
</div>
<?php endif; ?>

<?php if ($active_tab === 'all' || $active_tab === 'gate_out'): ?>
<!-- SECTION 2: BUKU KELUAR (GATE OUT EDC) -->
<div id="sec-gateout" class="card" style="border-top: 4px solid var(--primary); margin-top: 2rem;">
    <div class="card-header" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h3 class="card-title">
                <span class="badge badge-primary" style="font-size: 0.85rem;"><?= number_format($total_out_records) ?> Armada</span>
                2. Keluar Export Ajinex
            </h3>
        </div>
    </div>

    <!-- Search Bar Gate Out -->
    <form method="GET" action="logistic.php" style="margin: 1rem 0;">
        <input type="hidden" name="tab" value="<?= htmlspecialchars($active_tab) ?>">
        <div style="display: flex; gap: 0.5rem; max-width: 540px; width: 100%; align-items: center;">
            <input type="text" name="search_out" class="form-control" placeholder="Cari Nopol, Sopir, No. DO, Tujuan, Transportir..." value="<?= htmlspecialchars($search_out) ?>" style="flex: 1;">
            <button type="submit" class="btn btn-secondary" style="white-space: nowrap; padding: 0.6rem 1.1rem;">Cari</button>
            <?php if (!empty($search_out)): ?>
                <a href="logistic.php?tab=<?= urlencode($active_tab) ?>" class="btn btn-outline" style="white-space: nowrap; padding: 0.6rem 0.9rem;">Reset</a>
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
                    <th>Total Nett Weight</th>
                    <th>Alamat Kirim / Tujuan</th>
                    <th>Transportir</th>
                    <th>Waktu Keluar</th>
                    <th>Status</th>
                    <?php if ($can_input || $logged_user['role'] === 'manager'): ?><th style="text-align: center;">Aksi</th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (count($gate_outs) === 0): ?>
                    <tr>
                        <td colspan="<?= ($can_input || $logged_user['role'] === 'manager') ? '9' : '8' ?>" style="text-align: center; color: var(--text-muted); padding: 1.75rem;">Belum ada data Keluar EDC.</td>
                    </tr>
                <?php else: ?>
                    <?php $no = $offset_out + 1; foreach ($gate_outs as $go): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td>
                                <code><strong><?= htmlspecialchars($go['nopol']) ?></strong></code>
                                <?php if (!empty($go['document_photo'])): ?>
                                    <br><button type="button" onclick="showDocumentPhoto('<?= htmlspecialchars($go['document_photo']) ?>')" class="badge badge-warning" style="border:none; cursor:pointer; margin-top:0.25rem;" title="Lihat Foto Dokumen">📷 Foto Surat Jalan</button>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($go['driver_name']) ?></td>
                            <td><strong><?= is_numeric($go['tonnage']) ? number_format((float)$go['tonnage'], 2) : htmlspecialchars($go['tonnage']) ?></strong></td>
                            <td><?= htmlspecialchars($go['destination']) ?></td>
                            <td><?= htmlspecialchars($go['transportir'] ?: '-') ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($go['exit_time'])) ?></td>
                            <td><span class="badge badge-success"><?= htmlspecialchars($go['status']) ?></span></td>
                            <?php if ($can_input || $logged_user['role'] === 'manager'): ?>
                                <td style="text-align: center; gap: 0.25rem;">
                                    <?php if ($can_input): ?>
                                        <form method="POST" action="logistic.php?tab=<?= urlencode($active_tab) ?>" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?');" style="display: inline;">
                                            <input type="hidden" name="action" value="delete_gate_out">
                                            <input type="hidden" name="id" value="<?= $go['id'] ?>">
                                            <button type="submit" class="btn btn-danger btn-sm" style="padding: 0.2rem 0.5rem; font-size: 0.75rem;">Hapus</button>
                                        </form>
                                    <?php endif; ?>
                                    <?php if ($logged_user['role'] === 'manager'): ?>
                                        <button type="button" class="btn btn-primary btn-sm" style="padding: 0.2rem 0.5rem; font-size: 0.75rem;" onclick='editGateOut(<?= json_encode($go) ?>)'>Edit</button>
                                    <?php endif; ?>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?= render_pagination($page_out, $total_out_pages, ['tab' => $active_tab, 'search_out' => $search_out], 'page_out') ?>
</div>
<?php endif; ?>

<?php if ($active_tab === 'all' || $active_tab === 'export_nex'): ?>
<!-- SECTION 3: EXPORT NEX / NOPOR -->
<div id="sec-export" class="card" style="border-top: 4px solid var(--primary); margin-top: 2rem;">
    <div class="card-header" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h3 class="card-title">
                <span class="badge badge-primary" style="font-size: 0.85rem;"><?= number_format($total_exp_records) ?> Kontainer</span>
                3. Keluar EDC (Kontainer Ekspor Pos 4)
            </h3>
        </div>
    </div>

    <!-- Search Bar Export NEX -->
    <form method="GET" action="logistic.php" style="margin: 1rem 0;">
        <input type="hidden" name="tab" value="<?= htmlspecialchars($active_tab) ?>">
        <div style="display: flex; gap: 0.5rem; max-width: 540px; width: 100%; align-items: center;">
            <input type="text" name="search_exp" class="form-control" placeholder="Cari No. NOPOR, No. DO, No. Kontainer, No. Segel, Tujuan..." value="<?= htmlspecialchars($search_exp) ?>" style="flex: 1;">
            <button type="submit" class="btn btn-secondary" style="white-space: nowrap; padding: 0.6rem 1.1rem;">Cari</button>
            <?php if (!empty($search_exp)): ?>
                <a href="logistic.php?tab=<?= urlencode($active_tab) ?>" class="btn btn-outline" style="white-space: nowrap; padding: 0.6rem 0.9rem;">Reset</a>
            <?php endif; ?>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>No. NOPOR</th>
                    <th>No. DO</th>
                    <th>No. Kontainer</th>
                    <th>No. Segel</th>
                    <th>Tujuan</th>
                    <th>Tonase (Ton)</th>
                    <th>Waktu Keluar</th>
                    <th>Status</th>
                    <?php if ($can_input || $logged_user['role'] === 'manager'): ?><th style="text-align: center;">Aksi</th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (count($exports) === 0): ?>
                    <tr>
                        <td colspan="<?= ($can_input || $logged_user['role'] === 'manager') ? '10' : '9' ?>" style="text-align: center; color: var(--text-muted); padding: 1.75rem;">Belum ada data ekspor kontainer.</td>
                    </tr>
                <?php else: ?>
                    <?php $no = $offset_exp + 1; foreach ($exports as $ex): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td>
                                <code><strong><?= htmlspecialchars($ex['mopor_number']) ?></strong></code>
                                <?php if (!empty($ex['document_photo'])): ?>
                                    <br><button type="button" onclick="showDocumentPhoto('<?= htmlspecialchars($ex['document_photo']) ?>')" class="badge badge-warning" style="border:none; cursor:pointer; margin-top:0.25rem;" title="Lihat Foto Dokumen">📷 Foto Surat Jalan</button>
                                <?php endif; ?>
                            </td>
                            <td><code><?= htmlspecialchars($ex['do_number'] ?: '-') ?></code></td>
                            <td><code><?= htmlspecialchars($ex['container_number']) ?></code></td>
                            <td><code><?= htmlspecialchars($ex['seal_number']) ?></code></td>
                            <td><?= htmlspecialchars($ex['destination'] ?: '-') ?></td>
                            <td><strong><?= is_numeric($ex['tonnage']) ? number_format((float)$ex['tonnage'], 2) : htmlspecialchars($ex['tonnage']) ?></strong></td>
                            <td><?= date('d/m/Y H:i', strtotime($ex['exit_time'])) ?></td>
                            <td><span class="badge badge-success"><?= htmlspecialchars($ex['status']) ?></span></td>
                            <?php if ($can_input || $logged_user['role'] === 'manager'): ?>
                                <td style="text-align: center; gap: 0.25rem;">
                                    <?php if ($can_input): ?>
                                        <form method="POST" action="logistic.php?tab=<?= urlencode($active_tab) ?>" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?');" style="display: inline;">
                                            <input type="hidden" name="action" value="delete_export">
                                            <input type="hidden" name="id" value="<?= $ex['id'] ?>">
                                            <button type="submit" class="btn btn-danger btn-sm" style="padding: 0.2rem 0.5rem; font-size: 0.75rem;">Hapus</button>
                                        </form>
                                    <?php endif; ?>
                                    <?php if ($logged_user['role'] === 'manager'): ?>
                                        <button type="button" class="btn btn-primary btn-sm" style="padding: 0.2rem 0.5rem; font-size: 0.75rem;" onclick='editExport(<?= json_encode($ex) ?>)'>Edit</button>
                                    <?php endif; ?>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?= render_pagination($page_exp, $total_exp_pages, ['tab' => $active_tab, 'search_exp' => $search_exp], 'page_exp') ?>
</div>
<?php endif; ?>

<!-- MODALS UNTUK INPUT DATA (STAF SECOM) -->
<?php if ($can_input): ?>
<!-- MODAL GATE IN -->
<div id="modalGateIn" class="modal-backdrop">
    <div class="modal-dialog" style="max-width: 760px; width: 95%;">
        <div class="modal-header">
            <h3 class="modal-title">Form Input Kendaraan Masuk (Gate In)</h3>
            <button type="button" class="modal-close" onclick="closeModal('modalGateIn')">&times;</button>
        </div>
        <form method="POST" action="logistic.php?tab=<?= urlencode($active_tab) ?>">
            <input type="hidden" name="action" value="add_gate_in">
            <div class="modal-body">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.25rem;">
                    
                    <div>
                        <div class="form-group">
                            <label class="form-label" style="font-weight: 600;">Nomor Polisi (Nopol) *</label>
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
                            <label class="form-label" style="font-weight: 600;">Tanggal &amp; Waktu Masuk *</label>
                            <input type="datetime-local" name="entry_time" required class="form-control" value="<?= date('Y-m-d\TH:i') ?>">
                        </div>
                    </div>

                    <div>
                        <div class="form-group">
                            <label class="form-label" style="font-weight: 600;">Nama Sopir *</label>
                            <input type="text" name="driver_name" required class="form-control" placeholder="Nama sopir / driver...">
                        </div>

                        <div class="form-group">
                            <label class="form-label" style="font-weight: 600;">Tujuan *</label>
                            <select name="destination" class="form-select" required>
                                <option value="">-- Pilih Tujuan --</option>
                                <option value="Kirim">Kirim</option>
                                <option value="Export Ajinex">Export Ajinex</option>
                                <option value="Transit">Transit</option>
                                <option value="Muatan Barang">Muatan Barang</option>
                                <option value="EDC">EDC</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label" style="font-weight: 600;">Jenis SIM Driver *</label>
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
                    </div>

                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalGateIn')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Gate In</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL GATE OUT (KELUAR EDC) -->
<div id="modalGateOut" class="modal-backdrop">
    <div class="modal-dialog" style="max-width: 760px; width: 95%;">
        <div class="modal-header">
            <h3 class="modal-title">Form Input Keluar EDC</h3>
            <button type="button" class="modal-close" onclick="closeModal('modalGateOut')">&times;</button>
        </div>
        <form method="POST" action="logistic.php?tab=<?= urlencode($active_tab) ?>">
            <input type="hidden" name="action" value="add_gate_out">
            <div class="modal-body">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.25rem;">
                    
                    <div>
                        <div class="form-group">
                            <label class="form-label" style="font-weight: 600;">Nomor Polisi (Nopol) *</label>
                            <input type="text" name="nopol" required class="form-control" placeholder="Masukkan Nopol (e.g. B 1234 XYZ)...">
                        </div>

                        <div class="form-group">
                            <label class="form-label" style="font-weight: 600;">Total Nett Weight (Kg / Ton) *</label>
                            <input type="text" name="tonnage" required class="form-control" placeholder="Total Nett Weight diisi manual...">
                        </div>

                        <div class="form-group">
                            <label class="form-label" style="font-weight: 600;">Nama Transportir *</label>
                            <input type="text" name="transportir" required class="form-control" placeholder="Nama Perusahaan / Transportir...">
                        </div>
                    </div>

                    <div>
                        <div class="form-group">
                            <label class="form-label" style="font-weight: 600;">Nama Sopir *</label>
                            <input type="text" name="driver_name" required class="form-control" placeholder="Nama sopir...">
                        </div>

                        <div class="form-group">
                            <label class="form-label" style="font-weight: 600;">Alamat Kirim / Tujuan *</label>
                            <input type="text" name="destination" required class="form-control" placeholder="Alamat Kirim / Tujuan...">
                        </div>

                        <div class="form-group">
                            <label class="form-label" style="font-weight: 600;">Tanggal &amp; Waktu Keluar *</label>
                            <input type="datetime-local" name="exit_time" required class="form-control" value="<?= date('Y-m-d\TH:i') ?>">
                        </div>
                    </div>

                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label class="form-label" style="font-weight: 600;">Upload Foto Surat Jalan / Dokumen (Opsional)</label>
                        <input type="file" accept="image/*" class="form-control" onchange="compressAndPreviewPhoto(this, 'photo_preview_container_edc', 'photo_preview_img_edc', 'document_photo_edc')">
                        <input type="hidden" name="document_photo" id="document_photo_edc" value="">
                        <div id="photo_preview_container_edc" style="display: none; margin-top: 0.5rem; text-align: center;">
                            <img id="photo_preview_img_edc" src="" style="max-height: 130px; border-radius: var(--radius-sm); border: 1px solid var(--border);">
                            <div style="font-size: 0.75rem; color: var(--success); font-weight: 600; margin-top: 0.25rem;">✓ Foto surat jalan berhasil dikompresi</div>
                        </div>
                    </div>

                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalGateOut')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Keluar EDC</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL EXPORT NEX / NOPOR -->
<div id="modalExport" class="modal-backdrop">
    <div class="modal-dialog" style="max-width: 760px; width: 95%;">
        <div class="modal-header">
            <h3 class="modal-title">Form Input Keluar Export NEX</h3>
            <button type="button" class="modal-close" onclick="closeModal('modalExport')">&times;</button>
        </div>
        <form method="POST" action="logistic.php?tab=<?= urlencode($active_tab) ?>">
            <input type="hidden" name="action" value="add_export">
            <div class="modal-body">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.25rem;">
                    
                    <div>
                        <div class="form-group">
                            <label class="form-label" style="font-weight: 600;">Nomor NOPOR *</label>
                            <input type="text" name="mopor_number" required class="form-control" placeholder="Masukkan NOPOR...">
                        </div>

                        <div class="form-group">
                            <label class="form-label" style="font-weight: 600;">Nomor DO *</label>
                            <input type="text" name="do_number" required class="form-control" placeholder="Nomor DO...">
                        </div>

                        <div class="form-group">
                            <label class="form-label" style="font-weight: 600;">Segel *</label>
                            <input type="text" name="seal_number" required class="form-control" placeholder="Nomor Segel (Seal)...">
                        </div>
                    </div>

                    <div>
                        <div class="form-group">
                            <label class="form-label" style="font-weight: 600;">Tonase (Ton) *</label>
                            <input type="text" name="tonnage" required class="form-control" placeholder="Jumlah Tonase diisi manual...">
                        </div>

                        <div class="form-group">
                            <label class="form-label" style="font-weight: 600;">Kontainer *</label>
                            <input type="text" name="container_number" required class="form-control" placeholder="Nomor Kontainer...">
                        </div>

                        <div class="form-group">
                            <label class="form-label" style="font-weight: 600;">Tujuan *</label>
                            <input type="text" name="destination" required class="form-control" placeholder="Pelabuhan / Negara Tujuan...">
                        </div>

                        <div class="form-group">
                            <label class="form-label" style="font-weight: 600;">Tanggal &amp; Waktu Keluar *</label>
                            <input type="datetime-local" name="exit_time" required class="form-control" value="<?= date('Y-m-d\TH:i') ?>">
                        </div>
                    </div>

                    <div class="form-group" style="grid-column: 1 / -1;">
                        <label class="form-label" style="font-weight: 600;">Upload Foto Surat Jalan / Dokumen (Opsional)</label>
                        <input type="file" accept="image/*" class="form-control" onchange="compressAndPreviewPhoto(this, 'photo_preview_container_nex', 'photo_preview_img_nex', 'document_photo_nex')">
                        <input type="hidden" name="document_photo" id="document_photo_nex" value="">
                        <div id="photo_preview_container_nex" style="display: none; margin-top: 0.5rem; text-align: center;">
                            <img id="photo_preview_img_nex" src="" style="max-height: 130px; border-radius: var(--radius-sm); border: 1px solid var(--border);">
                            <div style="font-size: 0.75rem; color: var(--success); font-weight: 600; margin-top: 0.25rem;">✓ Foto surat jalan berhasil dikompresi</div>
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
<?php endif; ?>

<!-- MODALS EDIT DATA UNTUK MANAGER -->
<?php if ($logged_user['role'] === 'manager'): ?>
<!-- MODAL EDIT GATE IN -->
<div id="modalEditGateIn" class="modal-backdrop">
    <div class="modal-dialog" style="max-width: 700px; width: 95%;">
        <div class="modal-header">
            <h3 class="modal-title">Edit Data Gate In</h3>
            <button type="button" class="modal-close" onclick="closeModal('modalEditGateIn')">&times;</button>
        </div>
        <form method="POST" action="logistic.php?tab=<?= urlencode($active_tab) ?>">
            <input type="hidden" name="action" value="edit_gate_in">
            <input type="hidden" name="id" id="edit_gi_id">
            <div class="modal-body">
                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600;">Nomor Polisi (Nopol) *</label>
                        <input type="text" name="nopol" id="edit_gi_nopol" required class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600;">Nama Sopir *</label>
                        <input type="text" name="driver_name" id="edit_gi_driver" required class="form-control">
                    </div>
                </div>
                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600;">Visitor Number</label>
                        <input type="text" name="visitor_number" id="edit_gi_visitor" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600;">Antre Number</label>
                        <input type="text" name="antree_number" id="edit_gi_antree" class="form-control">
                    </div>
                </div>
                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600;">Transportir</label>
                        <input type="text" name="transportir" id="edit_gi_transportir" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600;">Tujuan *</label>
                        <select name="destination" id="edit_gi_destination" class="form-select" required>
                            <option value="Kirim">Kirim</option>
                            <option value="Export Ajinex">Export Ajinex</option>
                            <option value="Transit">Transit</option>
                            <option value="Muatan Barang">Muatan Barang</option>
                            <option value="EDC">EDC</option>
                        </select>
                    </div>
                </div>
                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600;">Jenis SIM Driver</label>
                        <select name="sim_type" id="edit_gi_sim_type" class="form-select">
                            <option value="SIM A">SIM A</option>
                            <option value="SIM B">SIM B</option>
                            <option value="SIM B2">SIM B2</option>
                            <option value="Tidak Ada">Tidak Ada</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600;">Checklist Berkas</label>
                        <div style="display: flex; gap: 1rem; padding: 0.55rem 0.75rem; background: var(--bg-surface-alt); border-radius: var(--radius-sm); border: 1px solid var(--border);">
                            <label style="cursor: pointer; display: flex; align-items: center; gap: 0.35rem;"><input type="checkbox" name="checklist_stnk" id="edit_gi_stnk" value="1"> STNK</label>
                            <label style="cursor: pointer; display: flex; align-items: center; gap: 0.35rem;"><input type="checkbox" name="checklist_kir" id="edit_gi_kir" value="1"> KIR</label>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalEditGateIn')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL EDIT GATE OUT (EDC) -->
<div id="modalEditGateOut" class="modal-backdrop">
    <div class="modal-dialog" style="max-width: 600px; width: 95%;">
        <div class="modal-header">
            <h3 class="modal-title">Edit Data Keluar EDC</h3>
            <button type="button" class="modal-close" onclick="closeModal('modalEditGateOut')">&times;</button>
        </div>
        <form method="POST" action="logistic.php?tab=<?= urlencode($active_tab) ?>">
            <input type="hidden" name="action" value="edit_gate_out">
            <input type="hidden" name="id" id="edit_go_id">
            <div class="modal-body">
                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600;">Nomor Polisi (Nopol) *</label>
                        <input type="text" name="nopol" id="edit_go_nopol" required class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600;">Nama Sopir *</label>
                        <input type="text" name="driver_name" id="edit_go_driver" required class="form-control">
                    </div>
                </div>
                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600;">Total Nett Weight *</label>
                        <input type="text" name="tonnage" id="edit_go_tonnage" required class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600;">Nama Transportir *</label>
                        <input type="text" name="transportir" id="edit_go_transportir" required class="form-control">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" style="font-weight: 600;">Alamat Kirim / Tujuan *</label>
                    <input type="text" name="destination" id="edit_go_destination" required class="form-control">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalEditGateOut')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL EDIT EXPORT NEX -->
<div id="modalEditExport" class="modal-backdrop">
    <div class="modal-dialog" style="max-width: 600px; width: 95%;">
        <div class="modal-header">
            <h3 class="modal-title">Edit Data Export NEX</h3>
            <button type="button" class="modal-close" onclick="closeModal('modalEditExport')">&times;</button>
        </div>
        <form method="POST" action="logistic.php?tab=<?= urlencode($active_tab) ?>">
            <input type="hidden" name="action" value="edit_export">
            <input type="hidden" name="id" id="edit_ex_id">
            <div class="modal-body">
                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600;">Nomor NOPOR *</label>
                        <input type="text" name="mopor_number" id="edit_ex_mopor" required class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600;">Nomor DO *</label>
                        <input type="text" name="do_number" id="edit_ex_do" required class="form-control">
                    </div>
                </div>
                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600;">No. Kontainer *</label>
                        <input type="text" name="container_number" id="edit_ex_container" required class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600;">No. Segel *</label>
                        <input type="text" name="seal_number" id="edit_ex_seal" required class="form-control">
                    </div>
                </div>
                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600;">Tonase (Ton) *</label>
                        <input type="text" name="tonnage" id="edit_ex_tonnage" required class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600;">Tujuan *</label>
                        <input type="text" name="destination" id="edit_ex_destination" required class="form-control">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalEditExport')">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- MODALS CHECKOUT UNTUK SECOM -->
<?php if ($can_input): ?>
<!-- MODAL CHECKOUT EXPORT AJINEX -->
<div id="modalCheckoutExportAjinex" class="modal-backdrop">
    <div class="modal-dialog" style="max-width: 650px; width: 95%;">
        <div class="modal-header">
            <h3 class="modal-title">Form Check-out Keluar (Export Ajinex)</h3>
            <button type="button" class="modal-close" onclick="closeModal('modalCheckoutExportAjinex')">&times;</button>
        </div>
        <form method="POST" action="logistic.php?tab=<?= urlencode($active_tab) ?>">
            <input type="hidden" name="action" value="checkout_export_ajinex">
            <input type="hidden" name="gate_in_id" id="co_ajinex_gi_id">
            <input type="hidden" name="nopol" id="co_ajinex_nopol">
            <input type="hidden" name="transportir" id="co_ajinex_transportir">
            <div class="modal-body">
                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600;">Nama Sopir</label>
                        <input type="text" name="driver_name" id="co_ajinex_driver_name" readonly class="form-control" style="background-color: var(--bg-surface-alt); cursor: not-allowed; font-weight: 600;">
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600;">Tanggal &amp; Waktu Keluar *</label>
                        <input type="datetime-local" name="exit_time" id="co_ajinex_exit_time" required class="form-control">
                    </div>
                </div>
                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600;">Total Nett Weight (Kg / Ton) *</label>
                        <input type="text" name="tonnage" id="co_ajinex_tonnage" required class="form-control" placeholder="Masukkan total net weight...">
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600;">Alamat Kirim / Tujuan *</label>
                        <input type="text" name="destination" id="co_ajinex_destination" required class="form-control" placeholder="Masukkan alamat kirim / tujuan...">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" style="font-weight: 600;">Upload Foto Surat Jalan (Opsional)</label>
                    <input type="file" accept="image/*" class="form-control" onchange="compressAndPreviewPhoto(this, 'photo_preview_co_ajinex', 'photo_img_co_ajinex', 'photo_input_co_ajinex')">
                    <input type="hidden" name="document_photo" id="photo_input_co_ajinex" value="">
                    <div id="photo_preview_co_ajinex" style="display: none; margin-top: 0.5rem; text-align: center;">
                        <img id="photo_img_co_ajinex" src="" style="max-height: 130px; border-radius: var(--radius-sm); border: 1px solid var(--border);">
                        <div style="font-size: 0.75rem; color: var(--success); font-weight: 600; margin-top: 0.25rem;">✓ Foto surat jalan berhasil dikompresi</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalCheckoutExportAjinex')">Batal</button>
                <button type="submit" class="btn btn-warning">Proses Check-out</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL CHECKOUT EDC -->
<div id="modalCheckoutEDC" class="modal-backdrop">
    <div class="modal-dialog" style="max-width: 720px; width: 95%;">
        <div class="modal-header">
            <h3 class="modal-title">Form Check-out Keluar (EDC)</h3>
            <button type="button" class="modal-close" onclick="closeModal('modalCheckoutEDC')">&times;</button>
        </div>
        <form method="POST" action="logistic.php?tab=<?= urlencode($active_tab) ?>">
            <input type="hidden" name="action" value="checkout_edc">
            <input type="hidden" name="gate_in_id" id="co_edc_gi_id">
            <input type="hidden" name="nopol" id="co_edc_nopol">
            <input type="hidden" name="transportir" id="co_edc_transportir">
            <div class="modal-body">
                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600;">Nama Sopir</label>
                        <input type="text" name="driver_name" id="co_edc_driver_name" readonly class="form-control" style="background-color: var(--bg-surface-alt); cursor: not-allowed; font-weight: 600;">
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600;">Tanggal &amp; Waktu Keluar *</label>
                        <input type="datetime-local" name="exit_time" id="co_edc_exit_time" required class="form-control">
                    </div>
                </div>
                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600;">Nomor Ekspor (NOPOR) *</label>
                        <input type="text" name="mopor_number" id="co_edc_mopor" required class="form-control" placeholder="Masukkan Nomor Ekspor (NOPOR)...">
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600;">Nomor DO *</label>
                        <input type="text" name="do_number" id="co_edc_do" required class="form-control" placeholder="Masukkan Nomor DO...">
                    </div>
                </div>
                <div class="grid-3">
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600;">Segel *</label>
                        <input type="text" name="seal_number" id="co_edc_seal" required class="form-control" placeholder="Nomor Segel...">
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600;">Tonase (Ton) *</label>
                        <input type="text" name="tonnage" id="co_edc_tonnage" required class="form-control" placeholder="Jumlah Tonase...">
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600;">Kontainer *</label>
                        <input type="text" name="container_number" id="co_edc_container" required class="form-control" placeholder="Nomor Kontainer...">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label" style="font-weight: 600;">Alamat Kirim / Tujuan *</label>
                    <input type="text" name="destination" id="co_edc_destination" required class="form-control" placeholder="Alamat Kirim / Tujuan...">
                </div>
                <div class="form-group">
                    <label class="form-label" style="font-weight: 600;">Upload Foto Surat Jalan (Opsional)</label>
                    <input type="file" accept="image/*" class="form-control" onchange="compressAndPreviewPhoto(this, 'photo_preview_co_edc', 'photo_img_co_edc', 'photo_input_co_edc')">
                    <input type="hidden" name="document_photo" id="photo_input_co_edc" value="">
                    <div id="photo_preview_co_edc" style="display: none; margin-top: 0.5rem; text-align: center;">
                        <img id="photo_img_co_edc" src="" style="max-height: 130px; border-radius: var(--radius-sm); border: 1px solid var(--border);">
                        <div style="font-size: 0.75rem; color: var(--success); font-weight: 600; margin-top: 0.25rem;">✓ Foto surat jalan berhasil dikompresi</div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('modalCheckoutEDC')">Batal</button>
                <button type="submit" class="btn btn-warning">Proses Check-out</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- MODAL DOCUMENT PHOTO PREVIEW -->
<div id="modalDocumentPhoto" class="modal-backdrop">
    <div class="modal-dialog" style="max-width: 520px; width: 92%;">
        <div class="modal-header">
            <h3 class="modal-title">Foto Dokumen Lampiran</h3>
            <button type="button" class="modal-close" onclick="closeModal('modalDocumentPhoto')">&times;</button>
        </div>
        <div class="modal-body" style="padding: 1.25rem; text-align: center;">
            <img id="doc_photo_preview_src" src="" style="max-width: 100%; max-height: 70vh; border-radius: var(--radius-md); border: 1px solid var(--border); box-shadow: var(--shadow-sm);">
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeModal('modalDocumentPhoto')">Tutup</button>
        </div>
    </div>
</div>

<script>
function openModal(id) {
    document.getElementById(id).classList.add('show');
}
function closeModal(id) {
    document.getElementById(id).classList.remove('show');
}
function showDocumentPhoto(src) {
    document.getElementById('doc_photo_preview_src').src = src;
    openModal('modalDocumentPhoto');
}

function getNowDatetimeLocal() {
    const d = new Date();
    const pad = (n) => String(n).padStart(2, '0');
    return `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
}

function openCheckoutExportAjinex(data) {
    document.getElementById('co_ajinex_gi_id').value = data.id;
    document.getElementById('co_ajinex_nopol').value = data.nopol || '';
    document.getElementById('co_ajinex_driver_name').value = data.driver_name || '';
    document.getElementById('co_ajinex_transportir').value = data.transportir || '';
    document.getElementById('co_ajinex_exit_time').value = getNowDatetimeLocal();
    document.getElementById('co_ajinex_tonnage').value = '';
    document.getElementById('co_ajinex_destination').value = '';
    document.getElementById('photo_input_co_ajinex').value = '';
    document.getElementById('photo_preview_co_ajinex').style.display = 'none';
    openModal('modalCheckoutExportAjinex');
}

function openCheckoutEDC(data) {
    document.getElementById('co_edc_gi_id').value = data.id;
    document.getElementById('co_edc_nopol').value = data.nopol || '';
    document.getElementById('co_edc_driver_name').value = data.driver_name || '';
    document.getElementById('co_edc_transportir').value = data.transportir || '';
    document.getElementById('co_edc_exit_time').value = getNowDatetimeLocal();
    document.getElementById('co_edc_mopor').value = '';
    document.getElementById('co_edc_do').value = '';
    document.getElementById('co_edc_seal').value = '';
    document.getElementById('co_edc_tonnage').value = '';
    document.getElementById('co_edc_container').value = '';
    document.getElementById('co_edc_destination').value = '';
    document.getElementById('photo_input_co_edc').value = '';
    document.getElementById('photo_preview_co_edc').style.display = 'none';
    openModal('modalCheckoutEDC');
}

function compressAndPreviewPhoto(input, containerId, imgId, inputId) {
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

function editGateIn(data) {
    document.getElementById('edit_gi_id').value = data.id;
    document.getElementById('edit_gi_nopol').value = data.nopol || '';
    document.getElementById('edit_gi_driver').value = data.driver_name || '';
    document.getElementById('edit_gi_visitor').value = data.visitor_number || '';
    document.getElementById('edit_gi_antree').value = data.antree_number || '';
    document.getElementById('edit_gi_transportir').value = data.transportir || '';
    document.getElementById('edit_gi_destination').value = data.destination || 'Kirim';
    document.getElementById('edit_gi_sim_type').value = data.sim_type || 'SIM B';
    document.getElementById('edit_gi_stnk').checked = data.checklist_stnk == 1;
    document.getElementById('edit_gi_kir').checked = data.checklist_kir == 1;
    openModal('modalEditGateIn');
}

function editGateOut(data) {
    document.getElementById('edit_go_id').value = data.id;
    document.getElementById('edit_go_nopol').value = data.nopol || '';
    document.getElementById('edit_go_driver').value = data.driver_name || '';
    document.getElementById('edit_go_tonnage').value = data.tonnage || '';
    document.getElementById('edit_go_transportir').value = data.transportir || '';
    document.getElementById('edit_go_destination').value = data.destination || '';
    openModal('modalEditGateOut');
}

function editExport(data) {
    document.getElementById('edit_ex_id').value = data.id;
    document.getElementById('edit_ex_mopor').value = data.mopor_number || '';
    document.getElementById('edit_ex_do').value = data.do_number || '';
    document.getElementById('edit_ex_container').value = data.container_number || '';
    document.getElementById('edit_ex_seal').value = data.seal_number || '';
    document.getElementById('edit_ex_tonnage').value = data.tonnage || '';
    document.getElementById('edit_ex_destination').value = data.destination || '';
    openModal('modalEditExport');
}
</script>
<?php include __DIR__ . '/includes/footer.php'; ?>

