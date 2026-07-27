<?php
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/excel_helper.php';

check_role(['manager', 'secom']);

$start_date = $_GET['start_date'] ?? date('Y-m-01');
$end_date   = $_GET['end_date'] ?? date('Y-m-d');
$type       = $_GET['type'] ?? 'all';

$filename = "Rekap_Laporan_GA_" . $start_date . "_sd_" . $end_date . ".xls";

header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");

echo get_excel_xml_header();

if ($type === 'all' || $type === 'guest') {
    $stmt = $pdo->prepare("SELECT * FROM guests WHERE DATE(time_in) BETWEEN ? AND ? ORDER BY time_in DESC");
    $stmt->execute([$start_date, $end_date]);
    $guests = $stmt->fetchAll();
    echo generate_guests_xml_worksheet($guests, "Rekap Buku Tamu");
}

if ($type === 'all' || $type === 'borrowing') {
    $stmt = $pdo->prepare("SELECT * FROM item_borrowings WHERE DATE(borrow_time) BETWEEN ? AND ? ORDER BY borrow_time DESC");
    $stmt->execute([$start_date, $end_date]);
    $borrowings = $stmt->fetchAll();
    echo generate_borrowings_xml_worksheet($borrowings, "Rekap Peminjaman");
}

if ($type === 'all' || $type === 'gate_in') {
    try {
        $stmt = $pdo->prepare("SELECT * FROM logistic_gate_ins WHERE DATE(entry_time) BETWEEN ? AND ? ORDER BY entry_time DESC");
        $stmt->execute([$start_date, $end_date]);
        $gate_ins = $stmt->fetchAll();
        echo generate_gate_ins_xml_worksheet($gate_ins, "Logistik Gate In");
    } catch (Exception $e) {}
}

if ($type === 'all' || $type === 'gate_out') {
    try {
        $stmt = $pdo->prepare("SELECT * FROM logistic_gate_outs WHERE DATE(exit_time) BETWEEN ? AND ? ORDER BY exit_time DESC");
        $stmt->execute([$start_date, $end_date]);
        $gate_outs = $stmt->fetchAll();
        echo generate_gate_outs_xml_worksheet($gate_outs, "Logistik Gate Out");
    } catch (Exception $e) {}
}

if ($type === 'all' || $type === 'export_nex') {
    try {
        $stmt = $pdo->prepare("SELECT * FROM logistic_export_nex_mopors WHERE DATE(exit_time) BETWEEN ? AND ? ORDER BY exit_time DESC");
        $stmt->execute([$start_date, $end_date]);
        $exports = $stmt->fetchAll();
        echo generate_export_nex_xml_worksheet($exports, "Logistik Export NEX");
    } catch (Exception $e) {}
}

echo '</Workbook>' . "\n";
