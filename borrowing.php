<?php
$page_title = 'Peminjaman Barang & Kunci';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth_check.php';

check_role(['manager', 'secom']);
$logged_user = get_logged_user();

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'return_item' && $logged_user['role'] === 'secom') {
        $borrow_id = intval($_POST['borrow_id'] ?? 0);
        $return_condition = trim($_POST['return_condition'] ?? 'Baik');

        if ($borrow_id > 0) {
            $now = date('Y-m-d H:i:s');
            $stmt = $pdo->prepare("UPDATE item_borrowings SET return_time = ?, return_condition = ?, status = 'returned' WHERE id = ? AND status = 'borrowed'");
            $stmt->execute([$now, $return_condition, $borrow_id]);
            set_flash_message('success', 'Pengembalian barang/kunci berhasil diproses.');
        }
    } elseif ($action === 'edit_borrowing' && $logged_user['role'] === 'manager') {
        $borrow_id = intval($_POST['borrow_id'] ?? 0);
        $borrower_name = trim($_POST['borrower_name'] ?? '');
        $department = trim($_POST['department'] ?? '');
        $item_name = trim($_POST['item_name'] ?? '');
        $item_code = trim($_POST['item_code'] ?? '');
        $key_number = trim($_POST['key_number'] ?? '');
        $quantity = intval($_POST['quantity'] ?? 1);

        if ($borrow_id > 0 && !empty($borrower_name) && !empty($item_name)) {
            $stmt = $pdo->prepare("UPDATE item_borrowings SET borrower_name = ?, department = ?, item_name = ?, item_code = ?, key_number = ?, quantity = ? WHERE id = ?");
            $stmt->execute([$borrower_name, $department, $item_name, $item_code, $key_number, $quantity, $borrow_id]);
            set_flash_message('success', 'Data peminjaman berhasil diperbarui oleh Manager.');
        }
    }
    header("Location: borrowing.php");
    exit();
}

$active_tab = $_GET['tab'] ?? 'all';
$category_filter_sql = "";
$category_filter_params = [];
if ($active_tab === 'ga') {
    $category_filter_sql = " AND category = 'GA'";
} elseif ($active_tab === 'secom') {
    $category_filter_sql = " AND category = 'SECOM'";
}

$per_page = 5;

// 1. QUERY TABEL ATAS: Barang/Kunci Sedang Dipinjam (Aktif) dengan Search & Pagination
$active_search = trim($_GET['active_search'] ?? '');
$active_page = max(1, intval($_GET['active_page'] ?? 1));

$active_count_query = "SELECT COUNT(*) FROM item_borrowings WHERE status = 'borrowed' $category_filter_sql";
$active_params = $category_filter_params;

if (!empty($active_search)) {
    $active_count_query .= " AND (LOWER(borrower_name) LIKE LOWER(?) OR LOWER(department) LIKE LOWER(?) OR LOWER(item_name) LIKE LOWER(?) OR LOWER(item_code) LIKE LOWER(?) OR LOWER(key_number) LIKE LOWER(?))";
    $term = "%$active_search%";
    array_push($active_params, $term, $term, $term, $term, $term);
}

$stmt = $pdo->prepare($active_count_query);
$stmt->execute($active_params);
$total_active_records = $stmt->fetchColumn();
$total_active_pages = ceil($total_active_records / $per_page);
$active_offset = ($active_page - 1) * $per_page;

$active_data_query = str_replace("SELECT COUNT(*)", "SELECT id, borrower_name, category, department, item_name, item_code, key_number, quantity, borrow_time, return_time, initial_condition, return_condition, signature, status", $active_count_query) . " ORDER BY borrow_time DESC LIMIT $per_page OFFSET $active_offset";
$stmt = $pdo->prepare($active_data_query);
$stmt->execute($active_params);
$active_borrowings = $stmt->fetchAll();

// 2. QUERY TABEL BAWAH: Riwayat Peminjaman (Sudah Dikembalikan) dengan Search & Pagination
$search = trim($_GET['search'] ?? '');
$history_page = max(1, intval($_GET['history_page'] ?? 1));

$count_query = "SELECT COUNT(*) FROM item_borrowings WHERE status = 'returned' $category_filter_sql";
$params = $category_filter_params;

if (!empty($search)) {
    $count_query .= " AND (LOWER(borrower_name) LIKE LOWER(?) OR LOWER(department) LIKE LOWER(?) OR LOWER(item_name) LIKE LOWER(?) OR LOWER(item_code) LIKE LOWER(?) OR LOWER(key_number) LIKE LOWER(?))";
    $searchTerm = "%$search%";
    array_push($params, $searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm);
}

$stmt = $pdo->prepare($count_query);
$stmt->execute($params);
$total_history_records = $stmt->fetchColumn();
$total_history_pages = ceil($total_history_records / $per_page);
$history_offset = ($history_page - 1) * $per_page;

$data_query = str_replace("SELECT COUNT(*)", "SELECT id, borrower_name, category, department, item_name, item_code, key_number, quantity, borrow_time, return_time, initial_condition, return_condition, signature, status", $count_query) . " ORDER BY return_time DESC LIMIT $per_page OFFSET $history_offset";
$stmt = $pdo->prepare($data_query);
$stmt->execute($params);
$history_borrowings = $stmt->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;">
    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
        <a href="borrowing.php?tab=all" class="btn btn-sm <?= $active_tab === 'all' ? 'btn-primary' : 'btn-outline' ?>">
            📊 Semua Peminjaman
        </a>
        <a href="borrowing.php?tab=ga" class="btn btn-sm <?= $active_tab === 'ga' ? 'btn-primary' : 'btn-outline' ?>">
            🏢 Inventaris GA
        </a>
        <a href="borrowing.php?tab=secom" class="btn btn-sm <?= $active_tab === 'secom' ? 'btn-primary' : 'btn-outline' ?>">
            🔑 Kunci SECOM
        </a>
    </div>
</div>

<!-- TABEL 1: BARANG / KUNCI SEDANG DIPINJAM (AKTIF) -->
<div class="card" style="border-top: 4px solid var(--warning);">
    <div class="card-header">
        <div>
            <h2 class="card-title" style="color: #92400e;">
                <span class="badge badge-warning" style="font-size: 0.85rem;"><?= number_format($total_active_records) ?> Dipinjam</span>
                Daftar Barang & Kunci Sedang Dipinjam
            </h2>
            <small style="color: var(--text-muted); display: block; margin-top: 0.2rem;">Aset GA & Kunci SECOM yang saat ini sedang dipinjam</small>
        </div>
    </div>

    <!-- Search Bar khusus Peminjaman Aktif -->
    <form method="GET" action="borrowing.php" style="display: flex; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 1rem;">
        <input type="hidden" name="tab" value="<?= htmlspecialchars($active_tab) ?>">
        <input type="text" name="active_search" class="form-control" placeholder="Cari nama, barang/kunci, dept, atau no kunci..." value="<?= htmlspecialchars($active_search) ?>" style="flex: 1; min-width: 220px; margin: 0;">
        <button type="submit" class="btn btn-primary" style="margin: 0;">Cari</button>
        <?php if (!empty($active_search)): ?>
            <a href="borrowing.php?tab=<?= htmlspecialchars($active_tab) ?>" class="btn btn-outline" style="margin: 0;">Reset</a>
        <?php endif; ?>
    </form>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Peminjam</th>
                    <th>Dept</th>
                    <th>Item & Kode / No. Kunci</th>
                    <th>Qty</th>
                    <th>Waktu Pinjam</th>
                    <th>Status</th>
                    <?php if ($logged_user['role'] === 'secom' || $logged_user['role'] === 'manager'): ?>
                        <th style="text-align: center;">Aksi</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (count($active_borrowings) === 0): ?>
                    <tr>
                        <td colspan="<?= ($logged_user['role'] === 'secom' || $logged_user['role'] === 'manager') ? '8' : '7' ?>" style="text-align: center; color: var(--text-muted); padding: 1.75rem;">Saat ini tidak ada data barang/kunci dipinjam yang sesuai.</td>
                    </tr>
                <?php else: ?>
                    <?php $no = $active_offset + 1; foreach ($active_borrowings as $item): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td class="col-name">
                                <strong><?= htmlspecialchars($item['borrower_name']) ?></strong>
                                <?php if (!empty($item['signature'])): ?>
                                    <div style="font-size: 0.725rem; color: var(--primary); font-weight: 500;">✓ Ada Ttd Digital</div>
                                <?php endif; ?>
                            </td>
                            <td class="col-nowrap"><span class="badge badge-secondary"><?= htmlspecialchars($item['department']) ?></span></td>
                            <td class="col-nowrap">
                                <strong><?= htmlspecialchars($item['item_name']) ?></strong>
                                <?php if (!empty($item['key_number'])): ?>
                                    <div><span class="badge badge-info">🔑 No Kunci: <?= htmlspecialchars($item['key_number']) ?></span></div>
                                <?php elseif (!empty($item['item_code'])): ?>
                                    <div><code><?= htmlspecialchars($item['item_code']) ?></code></div>
                                <?php endif; ?>
                            </td>
                            <td class="col-nowrap"><strong><?= $item['quantity'] ?></strong></td>
                            <td class="col-date"><?= date('d/m/Y H:i', strtotime($item['borrow_time'])) ?></td>
                            <td class="col-nowrap"><span class="badge badge-warning">Sedang Dipinjam</span></td>
                            <?php if ($logged_user['role'] === 'secom' || $logged_user['role'] === 'manager'): ?>
                                <td class="col-nowrap" style="text-align: center; gap: 0.25rem;">
                                    <?php if ($logged_user['role'] === 'secom'): ?>
                                        <button type="button" class="btn btn-sm btn-success" onclick="openReturnModal(<?= $item['id'] ?>, '<?= htmlspecialchars($item['item_name'], ENT_QUOTES) ?>', '<?= htmlspecialchars($item['borrower_name'], ENT_QUOTES) ?>')">
                                            Kembalikan
                                        </button>
                                    <?php endif; ?>
                                    <?php if ($logged_user['role'] === 'manager'): ?>
                                        <button type="button" class="btn btn-sm btn-primary" onclick='editBorrowing(<?= json_encode($item) ?>)'>Edit</button>
                                    <?php endif; ?>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination Tabel Barang Dipinjam (5 Data Per Halaman) -->
    <?= render_pagination($active_page, $total_active_pages, ['active_search' => $active_search, 'tab' => $active_tab], 'active_page') ?>
</div>

<!-- TABEL 2: RIWAYAT PEMINJAMAN (SUDAH DIKEMBALIKAN) -->
<div class="card" style="margin-top: 2rem;">
    <div class="card-header">
        <div>
            <h2 class="card-title">Riwayat Peminjaman (Sudah Dikembalikan)</h2>
            <small style="color: var(--text-muted);">Arsip barang/kunci yang telah selesai dikembalikan (Total: <?= number_format($total_history_records) ?> data)</small>
        </div>
    </div>

    <!-- Search Bar khusus Riwayat -->
    <form method="GET" action="borrowing.php" style="display: flex; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 1rem;">
        <input type="hidden" name="tab" value="<?= htmlspecialchars($active_tab) ?>">
        <input type="text" name="search" class="form-control" placeholder="Cari nama, barang/kunci, dept, atau no kunci di riwayat..." value="<?= htmlspecialchars($search) ?>" style="flex: 1; min-width: 220px; margin: 0;">
        <button type="submit" class="btn btn-primary" style="margin: 0;">Cari</button>
        <?php if (!empty($search)): ?>
            <a href="borrowing.php?tab=<?= htmlspecialchars($active_tab) ?>" class="btn btn-outline" style="margin: 0;">Reset</a>
        <?php endif; ?>
    </form>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Peminjam</th>
                    <th>Dept</th>
                    <th>Item & Kode / No. Kunci</th>
                    <th>Qty</th>
                    <th>Waktu Pinjam</th>
                    <th>Waktu Kembali</th>
                    <th>Status</th>
                    <?php if ($logged_user['role'] === 'manager'): ?>
                        <th style="text-align: center;">Aksi</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (count($history_borrowings) === 0): ?>
                    <tr>
                        <td colspan="<?= $logged_user['role'] === 'manager' ? '9' : '8' ?>" style="text-align: center; color: var(--text-muted); padding: 1.75rem;">Belum ada data riwayat pengembalian barang yang sesuai.</td>
                    </tr>
                <?php else: ?>
                    <?php $no = $history_offset + 1; foreach ($history_borrowings as $b): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td class="col-name"><strong><?= htmlspecialchars($b['borrower_name']) ?></strong></td>
                            <td class="col-nowrap"><span class="badge badge-secondary"><?= htmlspecialchars($b['department']) ?></span></td>
                            <td class="col-nowrap">
                                <strong><?= htmlspecialchars($b['item_name']) ?></strong>
                                <?php if (!empty($b['key_number'])): ?>
                                    <div><span class="badge badge-info">🔑 No Kunci: <?= htmlspecialchars($b['key_number']) ?></span></div>
                                <?php elseif (!empty($b['item_code'])): ?>
                                    <div><code><?= htmlspecialchars($b['item_code']) ?></code></div>
                                <?php endif; ?>
                            </td>
                            <td class="col-nowrap"><?= $b['quantity'] ?></td>
                            <td class="col-date"><?= date('d/m/Y H:i', strtotime($b['borrow_time'])) ?></td>
                            <td class="col-date"><?= date('d/m/Y H:i', strtotime($b['return_time'])) ?></td>
                            <td class="col-nowrap"><span class="badge badge-success">Sudah Dikembalikan</span></td>
                            <?php if ($logged_user['role'] === 'manager'): ?>
                                <td class="col-nowrap" style="text-align: center;">
                                    <button type="button" class="btn btn-sm btn-primary" onclick='editBorrowing(<?= json_encode($b) ?>)'>Edit</button>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination Tabel Riwayat Peminjaman (5 Data Per Halaman) -->
    <?= render_pagination($history_page, $total_history_pages, ['search' => $search, 'tab' => $active_tab], 'history_page') ?>
</div>

<?php if ($logged_user['role'] === 'secom'): ?>
<!-- MODAL PROSES PENGEMBALIAN BARANG -->
<div id="modal-return-item" class="modal-backdrop">
    <div class="modal-dialog">
        <div class="modal-header">
            <h3 class="modal-title">Proses Pengembalian Barang / Kunci</h3>
            <button type="button" class="modal-close" onclick="toggleModal('modal-return-item', false)">&times;</button>
        </div>
        <form action="borrowing.php" method="POST">
            <div class="modal-body">
                <input type="hidden" name="action" value="return_item">
                <input type="hidden" name="borrow_id" id="return_borrow_id">

                <p style="margin-bottom: 1rem;" id="return_info_text"></p>

                <div class="form-group">
                    <label class="form-label">Kondisi Barang Saat Dikembalikan *</label>
                    <select name="return_condition" class="form-select">
                        <option value="Baik / Sesuai Semula">Baik / Sesuai Semula</option>
                        <option value="Ada Kerusakan">Ada Kerusakan / Cacat Baru</option>
                        <option value="Hilang / Tidak Lengkap">Hilang / Tidak Lengkap</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="toggleModal('modal-return-item', false)">Batal</button>
                <button type="submit" class="btn btn-success">Konfirmasi Pengembalian</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- MODAL EDIT PEMINJAMAN (MANAGER ONLY) -->
<?php if ($logged_user['role'] === 'manager'): ?>
<div id="modalEditBorrowing" class="modal-backdrop">
    <div class="modal-dialog" style="max-width: 600px; width: 95%;">
        <div class="modal-header">
            <h3 class="modal-title">Edit Data Peminjaman</h3>
            <button type="button" class="modal-close" onclick="toggleModal('modalEditBorrowing', false)">&times;</button>
        </div>
        <form method="POST" action="borrowing.php">
            <input type="hidden" name="action" value="edit_borrowing">
            <input type="hidden" name="borrow_id" id="edit_borrow_id">
            <div class="modal-body">
                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600;">Nama Peminjam *</label>
                        <input type="text" name="borrower_name" id="edit_borrower_name" required class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600;">Departemen *</label>
                        <input type="text" name="department" id="edit_department" required class="form-control">
                    </div>
                </div>
                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600;">Nama Barang / Kunci *</label>
                        <input type="text" name="item_name" id="edit_item_name" required class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600;">Kode Barang</label>
                        <input type="text" name="item_code" id="edit_item_code" class="form-control">
                    </div>
                </div>
                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600;">Nomor Kunci (khusus SECOM)</label>
                        <input type="text" name="key_number" id="edit_key_number" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="font-weight: 600;">Jumlah (Qty) *</label>
                        <input type="number" name="quantity" id="edit_quantity" required min="1" class="form-control">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="toggleModal('modalEditBorrowing', false)">Batal</button>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<script>
function toggleModal(modalId, show) {
    const el = document.getElementById(modalId);
    if (show) {
        el.classList.add('show');
    } else {
        el.classList.remove('show');
    }
}

function openReturnModal(id, itemName, borrowerName) {
    document.getElementById('return_borrow_id').value = id;
    document.getElementById('return_info_text').innerHTML = 'Pengembalian barang <strong>' + itemName + '</strong> dipinjam oleh <strong>' + borrowerName + '</strong>.';
    toggleModal('modal-return-item', true);
}

function editBorrowing(data) {
    document.getElementById('edit_borrow_id').value = data.id;
    document.getElementById('edit_borrower_name').value = data.borrower_name || '';
    document.getElementById('edit_department').value = data.department || '';
    document.getElementById('edit_item_name').value = data.item_name || '';
    document.getElementById('edit_item_code').value = data.item_code || '';
    document.getElementById('edit_key_number').value = data.key_number || '';
    document.getElementById('edit_quantity').value = data.quantity || 1;
    toggleModal('modalEditBorrowing', true);
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
