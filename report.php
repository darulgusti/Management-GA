<?php
$page_title = 'Rekap Laporan GA';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth_check.php';

check_role(['manager', 'secom']);

// Filter Defaults
$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date   = $_GET['end_date'] ?? date('Y-m-d');
$type       = $_GET['type'] ?? 'all';
$per_page   = 5;

// Current Pages for pagination
$guest_page   = max(1, intval($_GET['g_page'] ?? 1));
$borrow_page  = max(1, intval($_GET['b_page'] ?? 1));
$gin_page     = max(1, intval($_GET['gin_page'] ?? 1));
$gout_page    = max(1, intval($_GET['gout_page'] ?? 1));
$exp_page     = max(1, intval($_GET['exp_page'] ?? 1));

// Filter Datetime Ranges
$start_datetime = $start_date . ' 00:00:00';
$end_datetime   = $end_date . ' 23:59:59';

// Totals for Summary Cards
$stmt = $pdo->prepare("SELECT COUNT(*) FROM guests WHERE time_in >= ? AND time_in <= ?");
$stmt->execute([$start_datetime, $end_datetime]);
$total_guest_records = $stmt->fetchColumn();

$stmt = $pdo->prepare("SELECT COUNT(*) FROM item_borrowings WHERE borrow_time >= ? AND borrow_time <= ?");
$stmt->execute([$start_datetime, $end_datetime]);
$total_borrow_records = $stmt->fetchColumn();

$total_gin_records = 0;
$total_gout_records = 0;
$total_exp_records = 0;

try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM logistic_gate_ins WHERE entry_time >= ? AND entry_time <= ?");
    $stmt->execute([$start_datetime, $end_datetime]);
    $total_gin_records = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM logistic_gate_outs WHERE exit_time >= ? AND exit_time <= ?");
    $stmt->execute([$start_datetime, $end_datetime]);
    $total_gout_records = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM logistic_export_nex_mopors WHERE exit_time >= ? AND exit_time <= ?");
    $stmt->execute([$start_datetime, $end_datetime]);
    $total_exp_records = $stmt->fetchColumn();
} catch (Exception $e) {}

// Fetch Guests
$guests = [];
$guest_total_pages = 0;
$guest_offset = 0;
if ($type === 'all' || $type === 'guest') {
    $guest_total_pages = ceil($total_guest_records / $per_page);
    $guest_offset = ($guest_page - 1) * $per_page;
    $stmt = $pdo->prepare("SELECT * FROM guests WHERE time_in >= ? AND time_in <= ? ORDER BY time_in DESC LIMIT $per_page OFFSET $guest_offset");
    $stmt->execute([$start_datetime, $end_datetime]);
    $guests = $stmt->fetchAll();
}

// Fetch Item Borrowings
$borrowings = [];
$borrow_total_pages = 0;
$borrow_offset = 0;
if ($type === 'all' || $type === 'borrowing') {
    $borrow_total_pages = ceil($total_borrow_records / $per_page);
    $borrow_offset = ($borrow_page - 1) * $per_page;
    $stmt = $pdo->prepare("SELECT * FROM item_borrowings WHERE borrow_time >= ? AND borrow_time <= ? ORDER BY borrow_time DESC LIMIT $per_page OFFSET $borrow_offset");
    $stmt->execute([$start_datetime, $end_datetime]);
    $borrowings = $stmt->fetchAll();
}

// Fetch Gate Ins
$gate_ins = [];
$gin_total_pages = 0;
$gin_offset = 0;
if ($type === 'all' || $type === 'gate_in') {
    try {
        $gin_total_pages = ceil($total_gin_records / $per_page);
        $gin_offset = ($gin_page - 1) * $per_page;
        $stmt = $pdo->prepare("SELECT * FROM logistic_gate_ins WHERE entry_time >= ? AND entry_time <= ? ORDER BY entry_time DESC LIMIT $per_page OFFSET $gin_offset");
        $stmt->execute([$start_datetime, $end_datetime]);
        $gate_ins = $stmt->fetchAll();
    } catch (Exception $e) {}
}

// Fetch Gate Outs
$gate_outs = [];
$gout_total_pages = 0;
$gout_offset = 0;
if ($type === 'all' || $type === 'gate_out') {
    try {
        $gout_total_pages = ceil($total_gout_records / $per_page);
        $gout_offset = ($gout_page - 1) * $per_page;
        $stmt = $pdo->prepare("SELECT * FROM logistic_gate_outs WHERE exit_time >= ? AND exit_time <= ? ORDER BY exit_time DESC LIMIT $per_page OFFSET $gout_offset");
        $stmt->execute([$start_datetime, $end_datetime]);
        $gate_outs = $stmt->fetchAll();
    } catch (Exception $e) {}
}

// Fetch Export NEX/MOPOR
$exports = [];
$exp_total_pages = 0;
$exp_offset = 0;
if ($type === 'all' || $type === 'export_nex') {
    try {
        $exp_total_pages = ceil($total_exp_records / $per_page);
        $exp_offset = ($exp_page - 1) * $per_page;
        $stmt = $pdo->prepare("SELECT * FROM logistic_export_nex_mopors WHERE exit_time >= ? AND exit_time <= ? ORDER BY exit_time DESC LIMIT $per_page OFFSET $exp_offset");
        $stmt->execute([$start_datetime, $end_datetime]);
        $exports = $stmt->fetchAll();
    } catch (Exception $e) {}
}

include __DIR__ . '/includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <div>
            <h2 class="card-title">Filter Rekapitulasi Laporan GA</h2>
            <small class="no-print" style="color: var(--text-muted);">Pilih rentang tanggal dan modul laporan yang ingin diekspor</small>
        </div>
        <div style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
            <button type="button" onclick="window.print()" class="btn btn-outline" style="height: 38px;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                Cetak / Print
            </button>
            <a href="export_report.php?start_date=<?= urlencode($start_date) ?>&end_date=<?= urlencode($end_date) ?>&type=<?= urlencode($type) ?>" class="btn btn-primary" style="height: 38px;">
                <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                Unduh Excel Rekap (.xls)
            </a>
        </div>
    </div>

    <form method="GET" action="report.php" class="grid-3" style="align-items: flex-end;">
        <div class="form-group" style="margin-bottom: 0;">
            <label class="form-label">Tanggal Mulai</label>
            <input type="date" name="start_date" class="form-control" value="<?= htmlspecialchars($start_date) ?>">
        </div>

        <div class="form-group" style="margin-bottom: 0;">
            <label class="form-label">Tanggal Selesai</label>
            <input type="date" name="end_date" class="form-control" value="<?= htmlspecialchars($end_date) ?>">
        </div>

        <div class="form-group" style="margin-bottom: 0; display: flex; gap: 0.5rem;">
            <div style="flex: 1;">
                <label class="form-label">Kategori Laporan</label>
                <select name="type" class="form-select">
                    <option value="all" <?= $type === 'all' ? 'selected' : '' ?>>Semua Modul (Tamu, Pinjam & Pos 4)</option>
                    <option value="guest" <?= $type === 'guest' ? 'selected' : '' ?>>Buku Tamu Digital</option>
                    <option value="borrowing" <?= $type === 'borrowing' ? 'selected' : '' ?>>Peminjaman Barang & Kunci</option>
                    <option value="gate_in" <?= $type === 'gate_in' ? 'selected' : '' ?>>Pos 4 - Buku Masuk (Gate In)</option>
                    <option value="gate_out" <?= $type === 'gate_out' ? 'selected' : '' ?>>Pos 4 - Keluar EDC</option>
                    <option value="export_nex" <?= $type === 'export_nex' ? 'selected' : '' ?>>Pos 4 - Export NEX / NOPOR</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary" style="height: 38px;">Filter</button>
        </div>
    </form>
</div>

<!-- Summary Cards for Period -->
<div class="grid-4" style="margin-bottom: 1.5rem;">
    <div class="stat-card">
        <div class="stat-icon primary">
            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= number_format($total_guest_records) ?></div>
            <div class="stat-label">Total Tamu</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon info">
            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= number_format($total_borrow_records) ?></div>
            <div class="stat-label">Total Peminjaman</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon success">
            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= number_format($total_gin_records + $total_gout_records) ?></div>
            <div class="stat-label">Pos 4 Gate In/Out</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon warning">
            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= number_format($total_exp_records) ?></div>
            <div class="stat-label">Export NEX / NOPOR</div>
        </div>
    </div>
</div>

<?php if ($type === 'all' || $type === 'guest'): ?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Rekap Buku Tamu (<?= number_format($total_guest_records) ?> Records)</h3>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Tamu</th>
                    <th>Instansi</th>
                    <th>Kategori</th>
                    <th>Orang Ditemui</th>
                    <th>Waktu Masuk</th>
                    <th>Waktu Keluar</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($guests) === 0): ?>
                    <tr><td colspan="7" style="text-align: center; color: var(--text-muted); padding: 2rem;">Tidak ada data tamu di rentang tanggal ini.</td></tr>
                <?php else: ?>
                    <?php $no = $guest_offset + 1; foreach ($guests as $g): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td class="col-name"><strong><?= htmlspecialchars($g['name']) ?></strong></td>
                            <td class="col-nowrap"><?= htmlspecialchars($g['institution'] ?: '-') ?></td>
                            <td class="col-nowrap"><span class="badge badge-info"><?= htmlspecialchars(ucfirst($g['guest_category'])) ?></span></td>
                            <td class="col-nowrap"><?= htmlspecialchars($g['person_to_meet']) ?></td>
                            <td class="col-date"><?= date('d/m/Y H:i', strtotime($g['time_in'])) ?></td>
                            <td class="col-date"><?= $g['time_out'] ? date('d/m/Y H:i', strtotime($g['time_out'])) : '<span class="badge badge-success">Masih Masuk</span>' ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?= render_pagination($guest_page, $guest_total_pages, ['start_date' => $start_date, 'end_date' => $end_date, 'type' => $type], 'g_page') ?>
</div>
<?php endif; ?>

<?php if ($type === 'all' || $type === 'borrowing'): ?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Rekap Peminjaman Barang & Kunci (<?= number_format($total_borrow_records) ?> Records)</h3>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Peminjam</th>
                    <th>Dept</th>
                    <th>Nama & Kode / No. Kunci</th>
                    <th>Qty</th>
                    <th>Waktu Pinjam</th>
                    <th>Waktu Kembali</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($borrowings) === 0): ?>
                    <tr><td colspan="8" style="text-align: center; color: var(--text-muted); padding: 2rem;">Tidak ada data peminjaman di rentang tanggal ini.</td></tr>
                <?php else: ?>
                    <?php $no = $borrow_offset + 1; foreach ($borrowings as $b): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td class="col-name"><strong><?= htmlspecialchars($b['borrower_name']) ?></strong></td>
                            <td class="col-nowrap"><?= htmlspecialchars($b['department']) ?></td>
                            <td class="col-nowrap">
                                <strong><?= htmlspecialchars($b['item_name']) ?></strong>
                                <?php if (!empty($b['key_number'])): ?>
                                    <span class="badge badge-info">🔑 No Kunci: <?= htmlspecialchars($b['key_number']) ?></span>
                                <?php elseif (!empty($b['item_code'])): ?>
                                    <code>(<?= htmlspecialchars($b['item_code']) ?>)</code>
                                <?php endif; ?>
                            </td>
                            <td class="col-nowrap"><?= $b['quantity'] ?></td>
                            <td class="col-date"><?= date('d/m/Y H:i', strtotime($b['borrow_time'])) ?></td>
                            <td class="col-date"><?= $b['return_time'] ? date('d/m/Y H:i', strtotime($b['return_time'])) : '-' ?></td>
                            <td>
                                <?php if ($b['status'] === 'borrowed'): ?>
                                    <span class="badge badge-warning">Dipinjam</span>
                                <?php else: ?>
                                    <span class="badge badge-success">Dikembalikan</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?= render_pagination($borrow_page, $borrow_total_pages, ['start_date' => $start_date, 'end_date' => $end_date, 'type' => $type], 'b_page') ?>
</div>
<?php endif; ?>

<?php if ($type === 'all' || $type === 'gate_in'): ?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Rekap Pos 4 - Buku Masuk Gate In (<?= number_format($total_gin_records) ?> Records)</h3>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nopol</th>
                    <th>Sopir</th>
                    <th>Transportir</th>
                    <th>Tujuan</th>
                    <th>Waktu Masuk</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($gate_ins) === 0): ?>
                    <tr><td colspan="7" style="text-align: center; color: var(--text-muted); padding: 2rem;">Tidak ada data Gate In di rentang tanggal ini.</td></tr>
                <?php else: ?>
                    <?php $no = $gin_offset + 1; foreach ($gate_ins as $gi): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><code><strong><?= htmlspecialchars($gi['nopol']) ?></strong></code></td>
                            <td><?= htmlspecialchars($gi['driver_name']) ?></td>
                            <td><?= htmlspecialchars($gi['transportir']) ?></td>
                            <td><span class="badge badge-info"><?= htmlspecialchars($gi['destination']) ?></span></td>
                            <td><?= date('d/m/Y H:i', strtotime($gi['entry_time'])) ?></td>
                            <td><span class="badge badge-success"><?= htmlspecialchars($gi['status']) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?= render_pagination($gin_page, $gin_total_pages, ['start_date' => $start_date, 'end_date' => $end_date, 'type' => $type], 'gin_page') ?>
</div>
<?php endif; ?>

<?php if ($type === 'all' || $type === 'gate_out'): ?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Rekap Pos 4 - Keluar EDC (<?= number_format($total_gout_records) ?> Records)</h3>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nopol</th>
                    <th>Sopir</th>
                    <th>Total Nett Weight</th>
                    <th>Alamat Kirim / Tujuan</th>
                    <th>Transportir</th>
                    <th>Waktu Keluar</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($gate_outs) === 0): ?>
                    <tr><td colspan="7" style="text-align: center; color: var(--text-muted); padding: 2rem;">Tidak ada data Keluar EDC di rentang tanggal ini.</td></tr>
                <?php else: ?>
                    <?php $no = $gout_offset + 1; foreach ($gate_outs as $go): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td>
                                <code><strong><?= htmlspecialchars($go['nopol']) ?></strong></code>
                                <?php if (!empty($go['document_photo'])): ?>
                                    <br><button type="button" onclick="showDocumentPhoto('<?= htmlspecialchars($go['document_photo']) ?>')" class="badge badge-warning" style="border:none; cursor:pointer; margin-top:0.25rem;" title="Lihat Foto Dokumen">📷 Foto Dokumen</button>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($go['driver_name']) ?></td>
                            <td><?= is_numeric($go['tonnage']) ? number_format((float)$go['tonnage'], 2) : htmlspecialchars($go['tonnage']) ?></td>
                            <td><?= htmlspecialchars($go['destination']) ?></td>
                            <td><?= htmlspecialchars($go['transportir']) ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($go['exit_time'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?= render_pagination($gout_page, $gout_total_pages, ['start_date' => $start_date, 'end_date' => $end_date, 'type' => $type], 'gout_page') ?>
</div>
<?php endif; ?>

<?php if ($type === 'all' || $type === 'export_nex'): ?>
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Rekap Pos 4 - Export NEX / NOPOR (<?= number_format($total_exp_records) ?> Records)</h3>
    </div>
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
                    <th>Tonase</th>
                    <th>Waktu Keluar</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($exports) === 0): ?>
                    <tr><td colspan="8" style="text-align: center; color: var(--text-muted); padding: 2rem;">Tidak ada data Export NEX di rentang tanggal ini.</td></tr>
                <?php else: ?>
                    <?php $no = $exp_offset + 1; foreach ($exports as $ex): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td>
                                <code><strong><?= htmlspecialchars($ex['mopor_number']) ?></strong></code>
                                <?php if (!empty($ex['document_photo'])): ?>
                                    <br><button type="button" onclick="showDocumentPhoto('<?= htmlspecialchars($ex['document_photo']) ?>')" class="badge badge-warning" style="border:none; cursor:pointer; margin-top:0.25rem;" title="Lihat Foto Dokumen">📷 Foto Dokumen</button>
                                <?php endif; ?>
                            </td>
                            <td><code><?= htmlspecialchars($ex['do_number']) ?></code></td>
                            <td><code><?= htmlspecialchars($ex['container_number']) ?></code></td>
                            <td><code><?= htmlspecialchars($ex['seal_number']) ?></code></td>
                            <td><?= htmlspecialchars($ex['destination']) ?></td>
                            <td><?= is_numeric($ex['tonnage']) ? number_format((float)$ex['tonnage'], 2) . ' Ton' : htmlspecialchars($ex['tonnage']) ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($ex['exit_time'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?= render_pagination($exp_page, $exp_total_pages, ['start_date' => $start_date, 'end_date' => $end_date, 'type' => $type], 'exp_page') ?>
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
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
