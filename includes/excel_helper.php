<?php
/**
 * GA Management System - Formatted Excel Export & Archiving Helper
 */

// Format Tanggal (DD/MM/YYYY)
function format_excel_date($datetime_str) {
    if (empty($datetime_str) || $datetime_str === '-' || $datetime_str === '0000-00-00 00:00:00') {
        return '-';
    }
    return date('d/m/Y', strtotime($datetime_str));
}

// Format Waktu (HH:MM)
function format_excel_time($datetime_str) {
    if (empty($datetime_str) || $datetime_str === '-' || $datetime_str === '0000-00-00 00:00:00') {
        return '-';
    }
    return date('H:i', strtotime($datetime_str));
}

// Generates common XML Styles header for Excel Spreadsheet
function get_excel_xml_header() {
    return '<?xml version="1.0" encoding="utf-8"?>' . "\n" .
    '<?mso-application progid="Excel.Sheet"?>' . "\n" .
    '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"' . "\n" .
    ' xmlns:o="urn:schemas-microsoft-com:office:office"' . "\n" .
    ' xmlns:x="urn:schemas-microsoft-com:office:excel"' . "\n" .
    ' xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"' . "\n" .
    ' xmlns:html="http://www.w3.org/TR/REC-html40">' . "\n" .
    ' <Styles>' . "\n" .
    '  <Style ss:ID="Default" ss:Name="Normal">' . "\n" .
    '   <Alignment ss:Vertical="Center"/>' . "\n" .
    '   <Font ss:FontName="Calibri" ss:Size="10" ss:Color="#1E293B"/>' . "\n" .
    '  </Style>' . "\n" .
    '  <Style ss:ID="Title">' . "\n" .
    '   <Font ss:FontName="Calibri" ss:Size="14" ss:Bold="1" ss:Color="#0F172A"/>' . "\n" .
    '  </Style>' . "\n" .
    '  <Style ss:ID="Header">' . "\n" .
    '   <Alignment ss:Horizontal="Center" ss:Vertical="Center" ss:WrapText="1"/>' . "\n" .
    '   <Borders>' . "\n" .
    '    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#64748B"/>' . "\n" .
    '    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#64748B"/>' . "\n" .
    '    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#64748B"/>' . "\n" .
    '    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#64748B"/>' . "\n" .
    '   </Borders>' . "\n" .
    '   <Font ss:FontName="Calibri" ss:Size="10" ss:Bold="1" ss:Color="#FFFFFF"/>' . "\n" .
    '   <Interior ss:Color="#1E293B" ss:Pattern="Solid"/>' . "\n" .
    '  </Style>' . "\n" .
    '  <Style ss:ID="CellData">' . "\n" .
    '   <Alignment ss:Vertical="Center"/>' . "\n" .
    '   <Borders>' . "\n" .
    '    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>' . "\n" .
    '    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>' . "\n" .
    '    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>' . "\n" .
    '    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>' . "\n" .
    '   </Borders>' . "\n" .
    '   <Font ss:FontName="Calibri" ss:Size="10" ss:Color="#334155"/>' . "\n" .
    '  </Style>' . "\n" .
    '  <Style ss:ID="CellCenter">' . "\n" .
    '   <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>' . "\n" .
    '   <Borders>' . "\n" .
    '    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>' . "\n" .
    '    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>' . "\n" .
    '    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>' . "\n" .
    '    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>' . "\n" .
    '   </Borders>' . "\n" .
    '   <Font ss:FontName="Calibri" ss:Size="10" ss:Color="#334155"/>' . "\n" .
    '  </Style>' . "\n" .
    '  <Style ss:ID="BadgeSuccess">' . "\n" .
    '   <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>' . "\n" .
    '   <Borders>' . "\n" .
    '    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#A7F3D0"/>' . "\n" .
    '    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#A7F3D0"/>' . "\n" .
    '    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#A7F3D0"/>' . "\n" .
    '    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#A7F3D0"/>' . "\n" .
    '   </Borders>' . "\n" .
    '   <Font ss:FontName="Calibri" ss:Size="10" ss:Bold="1" ss:Color="#065F46"/>' . "\n" .
    '   <Interior ss:Color="#D1FAE5" ss:Pattern="Solid"/>' . "\n" .
    '  </Style>' . "\n" .
    '  <Style ss:ID="BadgeSecondary">' . "\n" .
    '   <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>' . "\n" .
    '   <Borders>' . "\n" .
    '    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#CBD5E1"/>' . "\n" .
    '    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#CBD5E1"/>' . "\n" .
    '    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#CBD5E1"/>' . "\n" .
    '    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#CBD5E1"/>' . "\n" .
    '   </Borders>' . "\n" .
    '   <Font ss:FontName="Calibri" ss:Size="10" ss:Bold="1" ss:Color="#475569"/>' . "\n" .
    '   <Interior ss:Color="#F1F5F9" ss:Pattern="Solid"/>' . "\n" .
    '  </Style>' . "\n" .
    '  <Style ss:ID="BadgeWarning">' . "\n" .
    '   <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>' . "\n" .
    '   <Borders>' . "\n" .
    '    <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#FDE68A"/>' . "\n" .
    '    <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#FDE68A"/>' . "\n" .
    '    <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#FDE68A"/>' . "\n" .
    '    <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#FDE68A"/>' . "\n" .
    '   </Borders>' . "\n" .
    '   <Font ss:FontName="Calibri" ss:Size="10" ss:Bold="1" ss:Color="#92400E"/>' . "\n" .
    '   <Interior ss:Color="#FEF3C7" ss:Pattern="Solid"/>' . "\n" .
    '  </Style>' . "\n" .
    ' </Styles>' . "\n";
}

// Generate Worksheet Tamu dengan Kolom Tanggal dan Waktu Terpisah
function generate_guests_xml_worksheet($guests, $sheet_title = "Data Buku Tamu") {
    $xml = ' <Worksheet ss:Name="' . htmlspecialchars($sheet_title) . '">' . "\n";
    $xml .= '  <Table>' . "\n";
    $xml .= '   <Column ss:Width="40"/>' . "\n";  // No
    $xml .= '   <Column ss:Width="160"/>' . "\n"; // Nama Tamu
    $xml .= '   <Column ss:Width="150"/>' . "\n"; // Kategori
    $xml .= '   <Column ss:Width="150"/>' . "\n"; // Instansi
    $xml .= '   <Column ss:Width="150"/>' . "\n"; // Orang Ditemui
    $xml .= '   <Column ss:Width="110"/>' . "\n"; // No Kartu
    $xml .= '   <Column ss:Width="180"/>' . "\n"; // Tujuan
    $xml .= '   <Column ss:Width="100"/>' . "\n"; // Tanggal Masuk
    $xml .= '   <Column ss:Width="90"/>' . "\n";  // Waktu Masuk
    $xml .= '   <Column ss:Width="100"/>' . "\n"; // Tanggal Keluar
    $xml .= '   <Column ss:Width="90"/>' . "\n";  // Waktu Keluar
    $xml .= '   <Column ss:Width="110"/>' . "\n"; // Status

    // Header Row
    $xml .= '   <Row ss:Height="25">' . "\n";
    $xml .= '    <Cell ss:StyleID="Header"><Data ss:Type="String">No</Data></Cell>' . "\n";
    $xml .= '    <Cell ss:StyleID="Header"><Data ss:Type="String">Nama Tamu</Data></Cell>' . "\n";
    $xml .= '    <Cell ss:StyleID="Header"><Data ss:Type="String">Kategori Tamu</Data></Cell>' . "\n";
    $xml .= '    <Cell ss:StyleID="Header"><Data ss:Type="String">Instansi / Perusahaan</Data></Cell>' . "\n";
    $xml .= '    <Cell ss:StyleID="Header"><Data ss:Type="String">Orang Ditemui</Data></Cell>' . "\n";
    $xml .= '    <Cell ss:StyleID="Header"><Data ss:Type="String">No. Kartu Visitor</Data></Cell>' . "\n";
    $xml .= '    <Cell ss:StyleID="Header"><Data ss:Type="String">Tujuan Kunjungan</Data></Cell>' . "\n";
    $xml .= '    <Cell ss:StyleID="Header"><Data ss:Type="String">Tanggal Masuk</Data></Cell>' . "\n";
    $xml .= '    <Cell ss:StyleID="Header"><Data ss:Type="String">Waktu Masuk</Data></Cell>' . "\n";
    $xml .= '    <Cell ss:StyleID="Header"><Data ss:Type="String">Tanggal Keluar</Data></Cell>' . "\n";
    $xml .= '    <Cell ss:StyleID="Header"><Data ss:Type="String">Waktu Keluar</Data></Cell>' . "\n";
    $xml .= '    <Cell ss:StyleID="Header"><Data ss:Type="String">Status</Data></Cell>' . "\n";
    $xml .= '   </Row>' . "\n";

    $no = 1;
    foreach ($guests as $g) {
        $status = $g['time_out'] ? 'Sudah Keluar' : 'Masih di Lokasi';
        $statusStyle = $g['time_out'] ? 'BadgeSecondary' : 'BadgeSuccess';

        $xml .= '   <Row ss:Height="20">' . "\n";
        $xml .= '    <Cell ss:StyleID="CellCenter"><Data ss:Type="Number">' . $no++ . '</Data></Cell>' . "\n";
        $xml .= '    <Cell ss:StyleID="CellData"><Data ss:Type="String">' . htmlspecialchars($g['name']) . '</Data></Cell>' . "\n";
        $xml .= '    <Cell ss:StyleID="CellData"><Data ss:Type="String">' . htmlspecialchars(ucfirst($g['guest_category'])) . '</Data></Cell>' . "\n";
        $xml .= '    <Cell ss:StyleID="CellData"><Data ss:Type="String">' . htmlspecialchars($g['institution'] ?: '-') . '</Data></Cell>' . "\n";
        $xml .= '    <Cell ss:StyleID="CellData"><Data ss:Type="String">' . htmlspecialchars($g['person_to_meet']) . '</Data></Cell>' . "\n";
        $xml .= '    <Cell ss:StyleID="CellCenter"><Data ss:Type="String">' . htmlspecialchars($g['visitor_card_number'] ?: '-') . '</Data></Cell>' . "\n";
        $xml .= '    <Cell ss:StyleID="CellData"><Data ss:Type="String">' . htmlspecialchars($g['purpose'] ?: '-') . '</Data></Cell>' . "\n";
        $xml .= '    <Cell ss:StyleID="CellCenter"><Data ss:Type="String">' . format_excel_date($g['time_in']) . '</Data></Cell>' . "\n";
        $xml .= '    <Cell ss:StyleID="CellCenter"><Data ss:Type="String">' . format_excel_time($g['time_in']) . '</Data></Cell>' . "\n";
        $xml .= '    <Cell ss:StyleID="CellCenter"><Data ss:Type="String">' . format_excel_date($g['time_out']) . '</Data></Cell>' . "\n";
        $xml .= '    <Cell ss:StyleID="CellCenter"><Data ss:Type="String">' . format_excel_time($g['time_out']) . '</Data></Cell>' . "\n";
        $xml .= '    <Cell ss:StyleID="' . $statusStyle . '"><Data ss:Type="String">' . $status . '</Data></Cell>' . "\n";
        $xml .= '   </Row>' . "\n";
    }

    $xml .= '  </Table>' . "\n";
    $xml .= ' </Worksheet>' . "\n";
    return $xml;
}

// Generate Worksheet Peminjaman Barang dengan Kolom Tanggal dan Waktu Terpisah
function generate_borrowings_xml_worksheet($borrowings, $sheet_title = "Data Peminjaman") {
    $xml = ' <Worksheet ss:Name="' . htmlspecialchars($sheet_title) . '">' . "\n";
    $xml .= '  <Table>' . "\n";
    $xml .= '   <Column ss:Width="40"/>' . "\n";  // No
    $xml .= '   <Column ss:Width="160"/>' . "\n"; // Peminjam
    $xml .= '   <Column ss:Width="130"/>' . "\n"; // Dept
    $xml .= '   <Column ss:Width="160"/>' . "\n"; // Nama Barang
    $xml .= '   <Column ss:Width="100"/>' . "\n"; // Kode Barang
    $xml .= '   <Column ss:Width="50"/>' . "\n";  // Qty
    $xml .= '   <Column ss:Width="100"/>' . "\n"; // Tanggal Pinjam
    $xml .= '   <Column ss:Width="90"/>' . "\n";  // Waktu Pinjam
    $xml .= '   <Column ss:Width="100"/>' . "\n"; // Tanggal Kembali
    $xml .= '   <Column ss:Width="90"/>' . "\n";  // Waktu Kembali
    $xml .= '   <Column ss:Width="140"/>' . "\n"; // Kondisi Awal
    $xml .= '   <Column ss:Width="140"/>' . "\n"; // Kondisi Kembali
    $xml .= '   <Column ss:Width="110"/>' . "\n"; // Status

    // Header Row
    $xml .= '   <Row ss:Height="25">' . "\n";
    $xml .= '    <Cell ss:StyleID="Header"><Data ss:Type="String">No</Data></Cell>' . "\n";
    $xml .= '    <Cell ss:StyleID="Header"><Data ss:Type="String">Nama Peminjam</Data></Cell>' . "\n";
    $xml .= '    <Cell ss:StyleID="Header"><Data ss:Type="String">Departemen</Data></Cell>' . "\n";
    $xml .= '    <Cell ss:StyleID="Header"><Data ss:Type="String">Nama Barang</Data></Cell>' . "\n";
    $xml .= '    <Cell ss:StyleID="Header"><Data ss:Type="String">Kode Barang</Data></Cell>' . "\n";
    $xml .= '    <Cell ss:StyleID="Header"><Data ss:Type="String">Jumlah (Qty)</Data></Cell>' . "\n";
    $xml .= '    <Cell ss:StyleID="Header"><Data ss:Type="String">Tanggal Pinjam</Data></Cell>' . "\n";
    $xml .= '    <Cell ss:StyleID="Header"><Data ss:Type="String">Waktu Pinjam</Data></Cell>' . "\n";
    $xml .= '    <Cell ss:StyleID="Header"><Data ss:Type="String">Tanggal Kembali</Data></Cell>' . "\n";
    $xml .= '    <Cell ss:StyleID="Header"><Data ss:Type="String">Waktu Kembali</Data></Cell>' . "\n";
    $xml .= '    <Cell ss:StyleID="Header"><Data ss:Type="String">Kondisi Awal</Data></Cell>' . "\n";
    $xml .= '    <Cell ss:StyleID="Header"><Data ss:Type="String">Kondisi Kembali</Data></Cell>' . "\n";
    $xml .= '    <Cell ss:StyleID="Header"><Data ss:Type="String">Status</Data></Cell>' . "\n";
    $xml .= '   </Row>' . "\n";

    $no = 1;
    foreach ($borrowings as $b) {
        $status = $b['status'] === 'borrowed' ? 'Sedang Dipinjam' : 'Dikembalikan';
        $statusStyle = $b['status'] === 'borrowed' ? 'BadgeWarning' : 'BadgeSuccess';

        $xml .= '   <Row ss:Height="20">' . "\n";
        $xml .= '    <Cell ss:StyleID="CellCenter"><Data ss:Type="Number">' . $no++ . '</Data></Cell>' . "\n";
        $xml .= '    <Cell ss:StyleID="CellData"><Data ss:Type="String">' . htmlspecialchars($b['borrower_name']) . '</Data></Cell>' . "\n";
        $xml .= '    <Cell ss:StyleID="CellData"><Data ss:Type="String">' . htmlspecialchars($b['department']) . '</Data></Cell>' . "\n";
        $xml .= '    <Cell ss:StyleID="CellData"><Data ss:Type="String">' . htmlspecialchars($b['item_name']) . '</Data></Cell>' . "\n";
        $xml .= '    <Cell ss:StyleID="CellCenter"><Data ss:Type="String">' . htmlspecialchars($b['item_code'] ?: '-') . '</Data></Cell>' . "\n";
        $xml .= '    <Cell ss:StyleID="CellCenter"><Data ss:Type="Number">' . $b['quantity'] . '</Data></Cell>' . "\n";
        $xml .= '    <Cell ss:StyleID="CellCenter"><Data ss:Type="String">' . format_excel_date($b['borrow_time']) . '</Data></Cell>' . "\n";
        $xml .= '    <Cell ss:StyleID="CellCenter"><Data ss:Type="String">' . format_excel_time($b['borrow_time']) . '</Data></Cell>' . "\n";
        $xml .= '    <Cell ss:StyleID="CellCenter"><Data ss:Type="String">' . format_excel_date($b['return_time']) . '</Data></Cell>' . "\n";
        $xml .= '    <Cell ss:StyleID="CellCenter"><Data ss:Type="String">' . format_excel_time($b['return_time']) . '</Data></Cell>' . "\n";
        $xml .= '    <Cell ss:StyleID="CellData"><Data ss:Type="String">' . htmlspecialchars($b['initial_condition']) . '</Data></Cell>' . "\n";
        $xml .= '    <Cell ss:StyleID="CellData"><Data ss:Type="String">' . htmlspecialchars($b['return_condition'] ?: '-') . '</Data></Cell>' . "\n";
        $xml .= '    <Cell ss:StyleID="' . $statusStyle . '"><Data ss:Type="String">' . $status . '</Data></Cell>' . "\n";
        $xml .= '   </Row>' . "\n";
    }

    $xml .= '  </Table>' . "\n";
    $xml .= ' </Worksheet>' . "\n";
    return $xml;
}

// Fungsi Pengarsipan Data (Manual / Otomatis)
function run_archive_process($pdo, $is_manual = false) {
    $archives_dir = __DIR__ . '/../archives';
    if (!is_dir($archives_dir)) {
        mkdir($archives_dir, 0777, true);
    }

    if ($is_manual) {
        // Manual Archiving: Arsip SEMUA data selesai (meski belum 3 bulan)
        $stmt = $pdo->query("SELECT * FROM guests WHERE time_out IS NOT NULL ORDER BY time_in DESC");
        $old_guests = $stmt->fetchAll();

        // Select returned borrowings
        $stmt = $pdo->query("SELECT * FROM item_borrowings WHERE status = 'returned' ORDER BY borrow_time DESC");
        $old_borrowings = $stmt->fetchAll();

        $archive_type_label = 'Manual';
        $file_prefix = 'Arsip_Manual_GA_';
    } else {
        // Automatic Archiving: Arsip data selesai yang sudah mencapai >= 3 bulan
        $cutoff_date = date('Y-m-d H:i:s', strtotime('-3 months'));

        $stmt = $pdo->prepare("SELECT * FROM guests WHERE time_out IS NOT NULL AND time_out <= ? ORDER BY time_in DESC");
        $stmt->execute([$cutoff_date]);
        $old_guests = $stmt->fetchAll();

        $stmt = $pdo->prepare("SELECT * FROM item_borrowings WHERE status = 'returned' AND return_time <= ? ORDER BY borrow_time DESC");
        $stmt->execute([$cutoff_date]);
        $old_borrowings = $stmt->fetchAll();

        $archive_type_label = 'Otomatis (> 3 Bulan)';
        $file_prefix = 'Arsip_Otomatis_GA_';
    }

    $total_records = count($old_guests) + count($old_borrowings);
    if ($total_records === 0) {
        return [
            'success' => false,
            'count' => 0,
            'message' => $is_manual ? 'Tidak ada data riwayat selesai untuk diarsip.' : 'Tidak ada data selesai yang mencapai 3 bulan untuk diarsip.'
        ];
    }

    $filename = $file_prefix . date('Ymd_His') . ".xls";
    $filepath = $archives_dir . '/' . $filename;

    // Build Excel XML File with formatting & separate date/time
    $excel_content = get_excel_xml_header();
    $excel_content .= generate_guests_xml_worksheet($old_guests, "Arsip Buku Tamu");
    $excel_content .= generate_borrowings_xml_worksheet($old_borrowings, "Arsip Peminjaman");
    $excel_content .= '</Workbook>';

    file_put_contents($filepath, $excel_content);

    // Save archive log record into archives table
    $stmt = $pdo->prepare("INSERT INTO archives (filename, archive_type, records_count) VALUES (?, ?, ?)");
    $stmt->execute([$filename, $archive_type_label, $total_records]);

    // Clean up / Delete archived history records from active database tables
    if ($is_manual) {
        $pdo->exec("DELETE FROM guests WHERE time_out IS NOT NULL");
        $pdo->exec("DELETE FROM item_borrowings WHERE status = 'returned'");
    } else {
        $cutoff_date = date('Y-m-d H:i:s', strtotime('-3 months'));
        $stmt = $pdo->prepare("DELETE FROM guests WHERE time_out IS NOT NULL AND time_out <= ?");
        $stmt->execute([$cutoff_date]);
        $stmt = $pdo->prepare("DELETE FROM item_borrowings WHERE status = 'returned' AND return_time <= ?");
        $stmt->execute([$cutoff_date]);
    }

    return [
        'success' => true,
        'count' => $total_records,
        'filename' => $filename,
        'message' => "Berhasil mengarsipkan $total_records data ke berkas $filename."
    ];
}

// Cek Pengarsipan Otomatis Harian (Otomatis memproses data >= 3 bulan, di-cache 24 jam per session)
function check_and_run_auto_archive($pdo) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    // Performance optimization: Cek maksimal 1x 24 jam per user session
    if (isset($_SESSION['last_auto_archive_check']) && (time() - $_SESSION['last_auto_archive_check']) < 86400) {
        return;
    }
    $_SESSION['last_auto_archive_check'] = time();

    try {
        $cutoff_date = date('Y-m-d H:i:s', strtotime('-3 months'));

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM guests WHERE time_out IS NOT NULL AND time_out <= ?");
        $stmt->execute([$cutoff_date]);
        $guest_count = $stmt->fetchColumn();

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM item_borrowings WHERE status = 'returned' AND return_time <= ?");
        $stmt->execute([$cutoff_date]);
        $borrow_count = $stmt->fetchColumn();

        if (($guest_count + $borrow_count) > 0) {
            run_archive_process($pdo, false);
        }
    } catch (Exception $e) {
        // Ignore if error
    }
}
