<?php
$page_title = 'Dashboard Monitoring GA';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth_check.php';

check_role(['manager', 'secom']);

// Statistics Query Today (Index Optimized)
$today_start = date('Y-m-d 00:00:00');
$today_end   = date('Y-m-d 23:59:59');

// 1. Total Tamu Hari Ini
$stmt = $pdo->prepare("SELECT COUNT(*) FROM guests WHERE time_in >= ? AND time_in <= ?");
$stmt->execute([$today_start, $today_end]);
$count_guests_today = $stmt->fetchColumn();

// 2. Tamu Aktif Masih di Lokasi
$stmt = $pdo->query("SELECT COUNT(*) FROM guests WHERE time_out IS NULL");
$count_guests_active = $stmt->fetchColumn();

// 3. Total Peminjaman Hari Ini
$stmt = $pdo->prepare("SELECT COUNT(*) FROM item_borrowings WHERE borrow_time >= ? AND borrow_time <= ?");
$stmt->execute([$today_start, $today_end]);
$count_borrow_today = $stmt->fetchColumn();

// 4. Barang Belum Dikembalikan
$stmt = $pdo->query("SELECT COUNT(*) FROM item_borrowings WHERE status = 'borrowed'");
$count_borrow_pending = $stmt->fetchColumn();

// 5. Total Gate In Logistik Hari Ini
$count_gate_in_today = 0;
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM logistic_gate_ins WHERE entry_time >= ? AND entry_time <= ?");
    $stmt->execute([$today_start, $today_end]);
    $count_gate_in_today = $stmt->fetchColumn();
} catch (Exception $e) {}

// 6. Total Gate Out Logistik Hari Ini
$count_gate_out_today = 0;
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM logistic_gate_outs WHERE exit_time >= ? AND exit_time <= ?");
    $stmt->execute([$today_start, $today_end]);
    $count_gate_out_today = $stmt->fetchColumn();
} catch (Exception $e) {}

// 7. Total Export NEX/MOPOR Hari Ini
$count_export_today = 0;
try {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM logistic_export_nex_mopors WHERE exit_time >= ? AND exit_time <= ?");
    $stmt->execute([$today_start, $today_end]);
    $count_export_today = $stmt->fetchColumn();
} catch (Exception $e) {}

// Recent Guests Today (Limit 5)
$stmt = $pdo->prepare("SELECT name, institution, time_in, time_out FROM guests ORDER BY time_in DESC LIMIT 5");
$stmt->execute();
$recent_guests = $stmt->fetchAll();

// Recent Item Borrowings (Limit 5)
$stmt = $pdo->prepare("SELECT borrower_name, department, item_name, borrow_time, status FROM item_borrowings ORDER BY borrow_time DESC LIMIT 5");
$stmt->execute();
$recent_borrowings = $stmt->fetchAll();

// Recent Logistic Gate Ins (Limit 5)
$recent_gate_ins = [];
try {
    $stmt = $pdo->prepare("SELECT nopol, driver_name, transportir, destination, entry_time FROM logistic_gate_ins ORDER BY entry_time DESC LIMIT 5");
    $stmt->execute();
    $recent_gate_ins = $stmt->fetchAll();
} catch (Exception $e) {}

include __DIR__ . '/includes/header.php';
?>

<!-- Stat Cards Row 1: Buku Tamu & Peminjaman -->
<div class="grid-4" style="margin-bottom: 1.5rem;">
    <div class="stat-card">
        <div class="stat-icon primary">
            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= number_format($count_guests_today) ?></div>
            <div class="stat-label">Tamu Hari Ini</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon success">
            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= number_format($count_guests_active) ?></div>
            <div class="stat-label">Tamu Masih di Lokasi</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon info">
            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path></svg>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= number_format($count_borrow_today) ?></div>
            <div class="stat-label">Peminjaman Hari Ini</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon warning">
            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= number_format($count_borrow_pending) ?></div>
            <div class="stat-label">Barang Belum Kembali</div>
        </div>
    </div>
</div>

<!-- Stat Cards Row 2: Monitoring Logistik Pos -->
<div class="grid-3" style="margin-bottom: 2rem;">
    <div class="stat-card">
        <div class="stat-icon primary">
            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= number_format($count_gate_in_today) ?></div>
            <div class="stat-label">Logistik Gate In Hari Ini</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon info">
            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= number_format($count_gate_out_today) ?></div>
            <div class="stat-label">Logistik Gate Out Hari Ini</div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon success">
            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"></path></svg>
        </div>
        <div class="stat-content">
            <div class="stat-value"><?= number_format($count_export_today) ?></div>
            <div class="stat-label">Export NEX/MOPOR Hari Ini</div>
        </div>
    </div>
</div>

<!-- Grid 3 Activity Tables -->
<div class="grid-3">
    <!-- Recent Guests -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Tamu Terbaru</h3>
            <a href="guest.php" class="btn btn-sm btn-outline">Lihat →</a>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Waktu</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($recent_guests) === 0): ?>
                        <tr>
                            <td colspan="3" style="text-align: center; color: var(--text-muted);">Belum ada tamu.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recent_guests as $g): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($g['name']) ?></strong></td>
                                <td><?= date('H:i', strtotime($g['time_in'])) ?></td>
                                <td>
                                    <?php if ($g['time_out']): ?>
                                        <span class="badge badge-secondary">Selesai</span>
                                    <?php else: ?>
                                        <span class="badge badge-success">Aktif</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Borrowings -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Peminjaman Terbaru</h3>
            <a href="borrowing.php" class="btn btn-sm btn-outline">Lihat →</a>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Peminjam</th>
                        <th>Barang</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($recent_borrowings) === 0): ?>
                        <tr>
                            <td colspan="3" style="text-align: center; color: var(--text-muted);">Belum ada peminjaman.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recent_borrowings as $b): ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($b['borrower_name']) ?></strong></td>
                                <td><?= htmlspecialchars($b['item_name']) ?></td>
                                <td>
                                    <?php if ($b['status'] === 'borrowed'): ?>
                                        <span class="badge badge-warning">Dipinjam</span>
                                    <?php else: ?>
                                        <span class="badge badge-success">Kembali</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Recent Logistic Gate Ins -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Logistik Masuk (Gate In)</h3>
            <a href="logistic.php" class="btn btn-sm btn-outline">Lihat →</a>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nopol</th>
                        <th>Sopir</th>
                        <th>Waktu</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($recent_gate_ins) === 0): ?>
                        <tr>
                            <td colspan="3" style="text-align: center; color: var(--text-muted);">Belum ada armada masuk.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recent_gate_ins as $gi): ?>
                            <tr>
                                <td><code><strong><?= htmlspecialchars($gi['nopol']) ?></strong></code></td>
                                <td><?= htmlspecialchars($gi['driver_name']) ?></td>
                                <td><?= date('H:i', strtotime($gi['entry_time'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
