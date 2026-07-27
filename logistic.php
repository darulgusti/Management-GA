<?php
$page_title = 'Logistic Gate Pass System';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth_check.php';

check_role(['manager', 'secom']);
$logged_user = get_logged_user();
$can_input = ($logged_user['role'] === 'secom');

// Auto Ensure Tables Exist
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS `logistic_gate_ins` (
      `id` INT AUTO_INCREMENT PRIMARY KEY,
      `nopol` VARCHAR(50) NOT NULL,
      `driver_name` VARCHAR(255) NOT NULL,
      `visitor_number` VARCHAR(100) NULL,
      `antree_number` VARCHAR(100) NULL,
      `transportir` VARCHAR(255) NOT NULL,
      `destination` VARCHAR(100) NOT NULL DEFAULT 'Kirim',
      `checklist_sim` TINYINT(1) DEFAULT 0,
      `checklist_stnk` TINYINT(1) DEFAULT 0,
      `checklist_kir` TINYINT(1) DEFAULT 0,
      `entry_time` DATETIME NOT NULL,
      `status` VARCHAR(50) NOT NULL DEFAULT 'Done',
      `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    try { $pdo->exec("ALTER TABLE `logistic_gate_ins` ADD COLUMN `visitor_number` VARCHAR(100) NULL AFTER `driver_name`"); } catch (Exception $e) {}
    try { $pdo->exec("ALTER TABLE `logistic_gate_ins` ADD COLUMN `antree_number` VARCHAR(100) NULL AFTER `visitor_number`"); } catch (Exception $e) {}
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

// Handle POST actions (Hanya Secom yang diperbolehkan menginput/menghapus data)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$can_input) {
        set_flash_message('danger', 'Hanya Staf Secom yang memiliki wewenang untuk menambah atau menghapus data logistik!');
        header("Location: logistic.php");
        exit();
    }

    $action = $_POST['action'] ?? '';

    if ($action === 'add_gate_in') {
        $nopol = trim($_POST['nopol'] ?? '');
        $driver_name = trim($_POST['driver_name'] ?? '');
        $visitor_number = trim($_POST['visitor_number'] ?? '');
        $antree_number = trim($_POST['antree_number'] ?? '');
        $transportir = trim($_POST['transportir'] ?? '');
        $destination = trim($_POST['destination'] ?? 'Kirim');
        $sim = isset($_POST['checklist_sim']) ? 1 : 0;
        $stnk = isset($_POST['checklist_stnk']) ? 1 : 0;
        $kir = isset($_POST['checklist_kir']) ? 1 : 0;
        $entry_time = date('Y-m-d H:i:s');

        if (!empty($nopol) && !empty($driver_name)) {
            $stmt = $pdo->prepare("INSERT INTO logistic_gate_ins (nopol, driver_name, visitor_number, antree_number, transportir, destination, checklist_sim, checklist_stnk, checklist_kir, entry_time) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$nopol, $driver_name, $visitor_number, $antree_number, $transportir, $destination, $sim, $stnk, $kir, $entry_time]);
            set_flash_message('success', 'Data Kendaraan Masuk (Gate In) berhasil disimpan.');
        } else {
            set_flash_message('danger', 'Mohon lengkapi field Nopol dan Nama Sopir!');
        }
    } elseif ($action === 'delete_gate_in') {
        $id = intval($_POST['id'] ?? 0);
        if ($id > 0) {
            $stmt = $pdo->prepare("DELETE FROM logistic_gate_ins WHERE id = ?");
            $stmt->execute([$id]);
            set_flash_message('success', 'Data Gate In berhasil dihapus.');
        }
    } elseif ($action === 'add_gate_out') {
        $nopol = trim($_POST['nopol'] ?? '');
        $driver_name = trim($_POST['driver_name'] ?? '');
        $do_number = trim($_POST['do_number'] ?? '');
        $destination = trim($_POST['destination'] ?? '');
        $tonnage = floatval($_POST['tonnage'] ?? 0);
        $transportir = trim($_POST['transportir'] ?? '');
        $exit_time = date('Y-m-d H:i:s');

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
    } elseif ($action === 'add_export') {
        $mopor_number = trim($_POST['mopor_number'] ?? '');
        $driver_name = trim($_POST['driver_name'] ?? '');
        $do_number = trim($_POST['do_number'] ?? '');
        $container_number = trim($_POST['container_number'] ?? '');
        $seal_number = trim($_POST['seal_number'] ?? '');
        $destination = trim($_POST['destination'] ?? '');
        $tonnage = floatval($_POST['tonnage'] ?? 0);
        $transportir = trim($_POST['transportir'] ?? '');
        $exit_time = date('Y-m-d H:i:s');

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

    header("Location: logistic.php");
    exit();
}

$per_page = 5;

// Search & Pagination Gate In
$search_in = trim($_GET['search_in'] ?? '');
$page_in = max(1, intval($_GET['page_in'] ?? 1));
$count_query_in = "SELECT COUNT(*) FROM logistic_gate_ins WHERE 1=1";
$params_in = [];
if (!empty($search_in)) {
    $count_query_in .= " AND (nopol LIKE ? OR driver_name LIKE ? OR visitor_number LIKE ? OR antree_number LIKE ? OR transportir LIKE ? OR destination LIKE ?)";
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

$active_tab = $_GET['tab'] ?? 'all';

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
        <a href="logistic.php?tab=all" class="btn btn-sm <?= $active_tab === 'all' ? 'btn-primary' : 'btn-outline' ?>">
            📋 Semua Logistik
        </a>
        <a href="logistic.php?tab=gate_in" class="btn btn-sm <?= $active_tab === 'gate_in' ? 'btn-primary' : 'btn-outline' ?>">
            📥 Buku Masuk (Gate In)
        </a>
        <a href="logistic.php?tab=gate_out" class="btn btn-sm <?= $active_tab === 'gate_out' ? 'btn-primary' : 'btn-outline' ?>">
            📤 Buku Keluar (Gate Out)
        </a>
        <a href="logistic.php?tab=export_nex" class="btn btn-sm <?= $active_tab === 'export_nex' ? 'btn-primary' : 'btn-outline' ?>">
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

<?php if ($active_tab === 'all' || $active_tab === 'gate_in'): ?>
<!-- SECTION 1: BUKU MASUK (GATE IN) -->
<div id="sec-gate-in" class="card" style="margin-top: 1.5rem;">
    <div class="card-header" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h3 class="card-title">
                <span class="badge badge-primary" style="font-size: 0.85rem;"><?= number_format($total_in_records) ?> Armada</span>
                1. Buku Masuk Kendaraan (Gate In)
            </h3>
            <small style="color: var(--text-muted);">Pencatatan kendaraan armada yang tiba di pos gerbang</small>
        </div>
        <?php if ($can_input): ?>
            <button type="button" onclick="openModal('modalGateIn')" class="btn btn-primary btn-sm">+ Input Gate In (Masuk)</button>
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
                    <th>Antree No</th>
                    <th>Transportir</th>
                    <th>Tujuan</th>
                    <th>Checklist Berkas</th>
                    <th>Waktu Masuk</th>
                    <th>Status</th>
                    <?php if ($can_input): ?><th style="text-align: center;">Aksi</th><?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (count($gate_ins) === 0): ?>
                    <tr>
                        <td colspan="<?= $can_input ? '11' : '10' ?>" style="text-align: center; color: var(--text-muted); padding: 1.75rem;">Belum ada data transaksi kendaraan masuk.</td>
                    </tr>
                <?php else: ?>
                    <?php $no = $offset_in + 1; foreach ($gate_ins as $gi): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><code><strong><?= htmlspecialchars($gi['nopol']) ?></strong></code></td>
                            <td><?= htmlspecialchars($gi['driver_name']) ?></td>
                            <td><code><?= htmlspecialchars($gi['visitor_number'] ?: '-') ?></code></td>
                            <td><code><?= htmlspecialchars($gi['antree_number'] ?: '-') ?></code></td>
                            <td><?= htmlspecialchars($gi['transportir']) ?></td>
                            <td><span class="badge badge-info"><?= htmlspecialchars($gi['destination']) ?></span></td>
                            <td>
                                <div style="display: flex; gap: 0.25rem;">
                                    <span class="badge <?= $gi['checklist_sim'] ? 'badge-success' : 'badge-secondary' ?>" title="SIM">SIM <?= $gi['checklist_sim'] ? '✓' : '✗' ?></span>
                                    <span class="badge <?= $gi['checklist_stnk'] ? 'badge-success' : 'badge-secondary' ?>" title="STNK">STNK <?= $gi['checklist_stnk'] ? '✓' : '✗' ?></span>
                                    <span class="badge <?= $gi['checklist_kir'] ? 'badge-success' : 'badge-secondary' ?>" title="KIR">KIR <?= $gi['checklist_kir'] ? '✓' : '✗' ?></span>
                                </div>
                            </td>
                            <td><?= date('d/m/Y H:i', strtotime($gi['entry_time'])) ?></td>
                            <td><span class="badge badge-success"><?= htmlspecialchars($gi['status']) ?></span></td>
                            <?php if ($can_input): ?>
                                <td style="text-align: center;">
                                    <form method="POST" action="logistic.php" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?');" style="display: inline;">
                                        <input type="hidden" name="action" value="delete_gate_in">
                                        <input type="hidden" name="id" value="<?= $gi['id'] ?>">
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
    <?= render_pagination($page_in, $total_in_pages, ['tab' => $active_tab, 'search_in' => $search_in], 'page_in') ?>
</div>
<?php endif; ?>

<?php if ($active_tab === 'all' || $active_tab === 'gate_out'): ?>
<!-- SECTION 2: BUKU KELUAR (GATE OUT) -->
<div id="sec-gate-out" class="card" style="margin-top: 2rem;">
    <div class="card-header" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h3 class="card-title">
                <span class="badge badge-primary" style="font-size: 0.85rem;"><?= number_format($total_out_records) ?> Armada</span>
                2. Buku Keluar Kendaraan (Gate Out)
            </h3>
            <small style="color: var(--text-muted);">Pencatatan keberangkatan armada pengiriman barang non-ekspor</small>
        </div>
        <?php if ($can_input): ?>
            <button type="button" onclick="openModal('modalGateOut')" class="btn btn-primary btn-sm">+ Input Gate Out (Keluar)</button>
        <?php endif; ?>
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
                            <td><?= htmlspecialchars($go['transportir']) ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($go['exit_time'])) ?></td>
                            <td><span class="badge badge-success"><?= htmlspecialchars($go['status']) ?></span></td>
                            <?php if ($can_input): ?>
                                <td style="text-align: center;">
                                    <form method="POST" action="logistic.php" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?');" style="display: inline;">
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
    <?= render_pagination($page_out, $total_out_pages, ['tab' => $active_tab, 'search_out' => $search_out], 'page_out') ?>
</div>
<?php endif; ?>

<?php if ($active_tab === 'all' || $active_tab === 'export_nex'): ?>
<!-- SECTION 3: EXPORT NEX / MOPOR -->
<div id="sec-export" class="card" style="margin-top: 2rem;">
    <div class="card-header" style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h3 class="card-title">
                <span class="badge badge-primary" style="font-size: 0.85rem;"><?= number_format($total_exp_records) ?> Kontainer</span>
                3. Export NEX / MOPOR (Kontainer Logistik Ekspor)
            </h3>
            <small style="color: var(--text-muted);">Pencatatan khusus armada kontainer logistik ekspor &amp; MOPOR</small>
        </div>
        <?php if ($can_input): ?>
            <button type="button" onclick="openModal('modalExport')" class="btn btn-primary btn-sm">+ Input Export NEX/MOPOR</button>
        <?php endif; ?>
    </div>

    <!-- Search Bar Export -->
    <form method="GET" action="logistic.php" style="margin: 1rem 0;">
        <input type="hidden" name="tab" value="<?= htmlspecialchars($active_tab) ?>">
        <div style="display: flex; gap: 0.5rem; max-width: 540px; width: 100%; align-items: center;">
            <input type="text" name="search_exp" class="form-control" placeholder="Cari No. MOPOR, Sopir, No. Kontainer, No. Segel, No. DO..." value="<?= htmlspecialchars($search_exp) ?>" style="flex: 1;">
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
                            <td><code><?= htmlspecialchars($ex['do_number']) ?></code></td>
                            <td><code><?= htmlspecialchars($ex['container_number']) ?></code></td>
                            <td><code><?= htmlspecialchars($ex['seal_number']) ?></code></td>
                            <td><?= htmlspecialchars($ex['destination']) ?></td>
                            <td><strong><?= number_format($ex['tonnage'], 2) ?></strong></td>
                            <td><?= htmlspecialchars($ex['transportir']) ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($ex['exit_time'])) ?></td>
                            <td><span class="badge badge-success"><?= htmlspecialchars($ex['status']) ?></span></td>
                            <?php if ($can_input): ?>
                                <td style="text-align: center;">
                                    <form method="POST" action="logistic.php" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?');" style="display: inline;">
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
    <?= render_pagination($page_exp, $total_exp_pages, ['tab' => $active_tab, 'search_exp' => $search_exp], 'page_exp') ?>
</div>
<?php endif; ?>

<!-- MODALS UNTUK INPUT DATA (HANYA STAF SECOM) -->
<?php if ($can_input): ?>
<!-- MODAL GATE IN -->
<div id="modalGateIn" class="modal-backdrop">
    <div class="modal-dialog" style="max-width: 760px; width: 95%;">
        <div class="modal-header">
            <h3 class="modal-title">Form Input Kendaraan Masuk (Gate In)</h3>
            <button type="button" class="modal-close" onclick="closeModal('modalGateIn')">&times;</button>
        </div>
        <form method="POST" action="logistic.php">
            <input type="hidden" name="action" value="add_gate_in">
            <div class="modal-body">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.25rem;">
                    
                    <!-- LEFT COLUMN -->
                    <div>
                        <div class="form-group">
                            <label class="form-label" style="font-weight: 600;">Nomor Polisi (Nopol) *</label>
                            <input type="text" name="nopol" required class="form-control" placeholder="Masukkan Nopol (e.g. B 1234 XYZ)...">
                        </div>

                        <div class="form-group">
                            <label class="form-label" style="font-weight: 600;">Visitor Number</label>
                            <input type="text" name="visitor_number" class="form-control" placeholder="Visitor Card Number...">
                        </div>

                        <div class="form-group">
                            <label class="form-label" style="font-weight: 600;">Antree Number</label>
                            <input type="text" name="antree_number" class="form-control" placeholder="Nomor Antrian...">
                        </div>
                    </div>

                    <!-- RIGHT COLUMN -->
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
                                <option value="Umbal-umbal">Umbal-umbal</option>
                                <option value="Muat">Muat</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label class="form-label" style="font-weight: 600;">SIM &amp; Kelengkapan Dokumen</label>
                            <div style="display: flex; gap: 1rem; padding: 0.55rem 0.75rem; background: var(--bg-surface-alt); border-radius: var(--radius-sm); border: 1px solid var(--border);">
                                <label style="cursor: pointer; display: flex; align-items: center; gap: 0.35rem;"><input type="checkbox" name="checklist_sim" value="1" checked> SIM</label>
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

<!-- MODAL GATE OUT -->
<div id="modalGateOut" class="modal-backdrop">
    <div class="modal-dialog" style="max-width: 760px; width: 95%;">
        <div class="modal-header">
            <h3 class="modal-title">Form Input Kendaraan Keluar (Gate Out)</h3>
            <button type="button" class="modal-close" onclick="closeModal('modalGateOut')">&times;</button>
        </div>
        <form method="POST" action="logistic.php">
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

<!-- MODAL EXPORT NEX / MOPOR -->
<div id="modalExport" class="modal-backdrop">
    <div class="modal-dialog" style="max-width: 760px; width: 95%;">
        <div class="modal-header">
            <h3 class="modal-title">Form Input Export NEX / MOPOR</h3>
            <button type="button" class="modal-close" onclick="closeModal('modalExport')">&times;</button>
        </div>
        <form method="POST" action="logistic.php">
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
