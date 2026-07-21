<?php
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/config/database.php';

check_role(['manager', 'secom']);

$stmt = $pdo->query("SELECT * FROM guests ORDER BY time_in DESC");
$guests = $stmt->fetchAll();

$filename = "Buku_Tamu_Report_" . date('Y-m-d_H-i') . ".xls";

header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");

echo get_excel_xml_header();
echo generate_guests_xml_worksheet($guests, "Laporan Buku Tamu");
echo '</Workbook>' . "\n";
