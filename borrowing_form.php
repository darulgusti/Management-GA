<?php
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth_check.php';
$error_msg = '';

$logged_user = get_logged_user();
if ($logged_user) {
    set_flash_message('danger', 'Akun terautentikasi (Manager / SECOM) hanya untuk monitoring data. Pengisian form dilakukan melalui portal publik.');
    header("Location: borrowing.php");
    exit();
}

$category = trim($_GET['category'] ?? ($_POST['category'] ?? 'GA'));
if (!in_array($category, ['GA', 'SECOM'])) {
    $category = 'GA';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $borrower_name = trim($_POST['borrower_name'] ?? '');
    $department = trim($_POST['department'] ?? '');
    $item_name = trim($_POST['item_name'] ?? '');
    $item_code = trim($_POST['item_code'] ?? '');
    $key_number = trim($_POST['key_number'] ?? '');
    $quantity = intval($_POST['quantity'] ?? 1);
    $initial_condition = 'Baik';
    $signature = $_POST['signature'] ?? '';

    if ($category === 'SECOM' && empty($item_name)) {
        $item_name = 'Kunci';
    }

    if (empty($borrower_name) || empty($department) || empty($item_name) || empty($quantity)) {
        $error_msg = "Mohon lengkapi seluruh kolom wajib formulir peminjaman!";
    } elseif ($category === 'SECOM' && empty($key_number)) {
        $error_msg = "Mohon isi Nomor Kunci untuk peminjaman Kunci SECOM!";
    } else {
        try {
            $now = date('Y-m-d H:i:s');
            $stmt = $pdo->prepare("INSERT INTO item_borrowings (borrower_name, category, department, item_name, item_code, key_number, quantity, borrow_time, initial_condition, signature, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'borrowed')");
            $stmt->execute([$borrower_name, $category, $department, $item_name, $item_code, $key_number, $quantity, $now, $initial_condition, $signature]);
            
            header("Location: success.php?type=borrowing");
            exit();
        } catch (Exception $e) {
            $error_msg = "Gagal menyimpan data peminjaman: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Peminjaman <?= $category === 'SECOM' ? 'Kunci SECOM' : 'Barang GA' ?> - GA Management</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="portal-body">

    <header class="portal-navbar" style="background: #ffffff; padding: 0.9rem 1.5rem; display: flex; align-items: center; justify-content: space-between; border-bottom: 2px solid var(--primary); box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
        <div style="display: flex; align-items: center; gap: 0.75rem;">
            <div class="sidebar-logo-icon" style="background: var(--primary); color: #ffffff;">GA</div>
            <div>
                <strong style="font-size: 1.1rem; color: var(--primary);">Form Peminjaman <?= $category === 'SECOM' ? 'Kunci SECOM' : 'Barang GA' ?></strong>
                <div style="font-size: 0.75rem; color: var(--text-muted);">General Affairs Inventory Request</div>
            </div>
        </div>
        <div>
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

        <div class="card" style="border-top: 4px solid var(--primary);">
            
            <!-- TAB SWITCHER GA / SECOM -->
            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border); padding-bottom: 1rem;">
                <a href="borrowing_form.php?category=GA" class="btn btn-sm <?= $category === 'GA' ? 'btn-primary' : 'btn-outline' ?>" style="font-weight: 600;">
                    🏢 Peminjaman Barang GA
                </a>
                <a href="borrowing_form.php?category=SECOM" class="btn btn-sm <?= $category === 'SECOM' ? 'btn-primary' : 'btn-outline' ?>" style="font-weight: 600;">
                    🔑 Peminjaman Kunci SECOM
                </a>
            </div>

            <div class="card-header">
                <div>
                    <h2 class="card-title">Form Peminjaman <?= $category === 'SECOM' ? 'Kunci SECOM (Security)' : 'Barang GA (General Affairs)' ?></h2>
                </div>
            </div>

            <form action="borrowing_form.php" method="POST">
                <input type="hidden" name="category" value="<?= htmlspecialchars($category) ?>">
                <input type="hidden" name="signature" id="borrow_signature_input">

                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label">Nama Peminjam (Karyawan / Staf) <small style="color: var(--primary); font-weight: 600;">(wajib diisi)</small></label>
                        <input type="text" name="borrower_name" required class="form-control" placeholder="Nama Lengkap" value="<?= htmlspecialchars($_POST['borrower_name'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Departemen / Bagian <small style="color: var(--primary); font-weight: 600;">(wajib diisi)</small></label>
                        <input type="text" name="department" required class="form-control" placeholder="Contoh: IT / Production / HR" value="<?= htmlspecialchars($_POST['department'] ?? '') ?>">
                    </div>
                </div>

                <?php if ($category === 'SECOM'): ?>
                    <input type="hidden" name="item_name" value="Kunci">
                    <div class="grid-2">
                        <div class="form-group">
                            <label class="form-label">Nomor Kunci <small style="color: var(--primary); font-weight: 600;">(wajib diisi)</small></label>
                            <input type="text" name="key_number" required class="form-control" placeholder="Contoh: K-01 / KEY-12" value="<?= htmlspecialchars($_POST['key_number'] ?? '') ?>">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Jumlah (Qty) <small style="color: var(--primary); font-weight: 600;">(wajib diisi)</small></label>
                            <input type="number" name="quantity" required value="<?= intval($_POST['quantity'] ?? 1) ?>" min="1" class="form-control">
                        </div>
                    </div>
                <?php else: ?>
                    <div class="grid-3">
                        <div class="form-group">
                            <label class="form-label">Nama Barang GA <small style="color: var(--primary); font-weight: 600;">(wajib diisi)</small></label>
                            <input type="text" name="item_name" required class="form-control" placeholder="Contoh: Proyektor / Mobil GA" value="<?= htmlspecialchars($_POST['item_name'] ?? '') ?>">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Kode / Identitas Barang (Opsional)</label>
                            <input type="text" name="item_code" class="form-control" placeholder="Contoh: PRJ-01 (Opsional)" value="<?= htmlspecialchars($_POST['item_code'] ?? '') ?>">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Jumlah (Qty) <small style="color: var(--primary); font-weight: 600;">(wajib diisi)</small></label>
                            <input type="number" name="quantity" required value="<?= intval($_POST['quantity'] ?? 1) ?>" min="1" class="form-control">
                        </div>
                    </div>
                <?php endif; ?>

                <div class="form-group">
                    <label class="form-label">Tanda Tangan Digital Peminjam <small style="color: var(--primary); font-weight: 600;">(wajib diisi)</small></label>
                    <div class="signature-container">
                        <canvas id="borrow_signature_canvas" class="signature-canvas"></canvas>
                        <div class="signature-controls">
                            <small style="color: #64748b;">Gunakan mouse atau layar sentuh untuk menggambar tanda tangan</small>
                            <button type="button" id="clear_borrow_signature" class="btn btn-sm btn-secondary">Hapus Canvas</button>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn btn-success btn-lg btn-block" style="margin-top: 1rem;">
                    <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    Simpan Data Peminjaman
                </button>
            </form>
        </div>

    </div>

    <script src="js/signature_pad.js"></script>
</body>
</html>
