<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth_check.php';

check_role(['manager', 'secom']);

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    http_response_code(400);
    die('ID arsip tidak valid.');
}

$stmt = $pdo->prepare("SELECT filename, file_content FROM archives WHERE id = ?");
$stmt->execute([$id]);
$archive = $stmt->fetch();

if (!$archive) {
    http_response_code(404);
    die('Arsip tidak ditemukan.');
}

if (empty($archive['file_content'])) {
    http_response_code(404);
    die('Konten file arsip tidak tersedia. File arsip lama (sebelum 31 Juli 2026) tidak menyimpan konten di database.');
}

$filename = $archive['filename'];
header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');
echo $archive['file_content'];
exit();
