<?php
/**
 * API Engagement - Menangani permintaan pembuatan laporan engagement
 */

require 'vendor/autoload.php';
require 'fungsi_proses.php';
require 'fungsi_konversi.php';

use PhpOffice\PhpWord\TemplateProcessor;

header('Content-Type: application/json');

// Pastikan direktori hasil ada
$hasilDir = __DIR__ . '/hasil';
if (!file_exists($hasilDir)) {
    mkdir($hasilDir, 0777, true);
}

// Pastikan direktori untuk evidence images ada
$evidenceDir = __DIR__ . '/hasil/evidence';
if (!file_exists($evidenceDir)) {
    mkdir($evidenceDir, 0777, true);
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Metode tidak diizinkan');
    }

    $action = $_POST['action'] ?? '';
    
    if ($action !== 'generate_engagement_report') {
        throw new Exception('Action tidak valid');
    }

    // Validasi input
    $tanggal = $_POST['tanggal'] ?? date('Y-m-d');
    $judul = $_POST['judul'] ?? '';
    $links = $_POST['link'] ?? [];
    $platforms = $_POST['platform'] ?? [];
    $namaAkun = $_POST['nama_akun'] ?? [];
    $narasi = $_POST['narasi'] ?? [];
    $linkIndexes = $_POST['link_index'] ?? [];

    if (empty($judul)) {
        throw new Exception('Judul laporan harus diisi');
    }

    if (empty($links) || count($links) === 0) {
        throw new Exception('Minimal harus ada satu link');
    }

    if (empty($namaAkun) || count($namaAkun) === 0) {
        throw new Exception('Minimal harus ada satu nama akun');
    }

    if (count($namaAkun) !== count($narasi)) {
        throw new Exception('Jumlah nama akun harus sesuai dengan jumlah narasi');
    }
    
    // Jika link_index tidak ada atau tidak sesuai, assign secara round-robin
    if (empty($linkIndexes) || count($linkIndexes) !== count($namaAkun)) {
        $linkIndexes = [];
        for ($i = 0; $i < count($namaAkun); $i++) {
            $linkIndexes[] = $i % count($links); // Round-robin assignment
        }
    }

    // Format tanggal
    $tanggalFormatted = strtoupper(formatTanggalIndonesia($tanggal));
    $tanggalFormattedWA = formatTanggalIndonesia($tanggal); // Format untuk WA (tanpa uppercase)
    $tanggalNamaFile = date('dmY', strtotime($tanggal));

    // Process evidence images per link
    // Evidence diupload per link, kemudian di-assign ke semua akun di link tersebut
    $evidenceByLink = [];
    $totalLinks = count($links);
    
    // Initialize evidence array for all links
    for ($i = 0; $i < $totalLinks; $i++) {
        $evidenceByLink[$i] = [];
    }
    
    // Process evidence files per link (format: evidence_link_{linkIndex}[])
    foreach ($_FILES as $key => $fileData) {
        if (preg_match('/^evidence_link_(\d+)$/', $key, $matches)) {
            $linkIndex = (int)$matches[1];
            if ($linkIndex >= 0 && $linkIndex < $totalLinks) {
                if (is_array($fileData['name'])) {
                    // Multiple files
                    foreach ($fileData['name'] as $fileIndex => $fileName) {
                        if (!empty($fileName) && isset($fileData['error'][$fileIndex]) && 
                            $fileData['error'][$fileIndex] === UPLOAD_ERR_OK) {
                            $tmpName = $fileData['tmp_name'][$fileIndex];
                            $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
                            $newFileName = 'evidence_link' . $linkIndex . '_' . $fileIndex . '_' . time() . '_' . uniqid() . '.' . $fileExtension;
                            $destination = $evidenceDir . '/' . $newFileName;
                            
                            if (move_uploaded_file($tmpName, $destination)) {
                                $evidenceByLink[$linkIndex][] = $destination;
                            }
                        }
                    }
                } else {
                    // Single file
                    if (!empty($fileData['name']) && isset($fileData['error']) && 
                        $fileData['error'] === UPLOAD_ERR_OK) {
                        $tmpName = $fileData['tmp_name'];
                        $fileExtension = pathinfo($fileData['name'], PATHINFO_EXTENSION);
                        $newFileName = 'evidence_link' . $linkIndex . '_' . time() . '_' . uniqid() . '.' . $fileExtension;
                        $destination = $evidenceDir . '/' . $newFileName;
                        
                        if (move_uploaded_file($tmpName, $destination)) {
                            $evidenceByLink[$linkIndex][] = $destination;
                        }
                    }
                }
            }
        }
    }
    
    // Also support old format (evidence[$index][]) for backward compatibility
    if (isset($_FILES['evidence']) && is_array($_FILES['evidence']['name'])) {
        $totalAccounts = count($namaAkun);
        foreach ($_FILES['evidence']['name'] as $index => $files) {
            if ($index < $totalAccounts) {
                $linkIdx = isset($linkIndexes[$index]) ? (int)$linkIndexes[$index] : ($index % count($links));
                $linkIdx = max(0, min($linkIdx, count($links) - 1));
                
                if (is_array($files)) {
                    foreach ($files as $fileIndex => $fileName) {
                        if (!empty($fileName) && isset($_FILES['evidence']['error'][$index][$fileIndex]) && 
                            $_FILES['evidence']['error'][$index][$fileIndex] === UPLOAD_ERR_OK) {
                            $tmpName = $_FILES['evidence']['tmp_name'][$index][$fileIndex];
                            $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
                            $newFileName = 'evidence_' . $index . '_' . $fileIndex . '_' . time() . '_' . uniqid() . '.' . $fileExtension;
                            $destination = $evidenceDir . '/' . $newFileName;
                            
                            if (move_uploaded_file($tmpName, $destination)) {
                                $evidenceByLink[$linkIdx][] = $destination;
                            }
                        }
                    }
                } else {
                    if (!empty($files) && isset($_FILES['evidence']['error'][$index]) && 
                        $_FILES['evidence']['error'][$index] === UPLOAD_ERR_OK) {
                        $tmpName = $_FILES['evidence']['tmp_name'][$index];
                        $fileExtension = pathinfo($files, PATHINFO_EXTENSION);
                        $newFileName = 'evidence_' . $index . '_' . time() . '_' . uniqid() . '.' . $fileExtension;
                        $destination = $evidenceDir . '/' . $newFileName;
                        
                        if (move_uploaded_file($tmpName, $destination)) {
                            $evidenceByLink[$linkIdx][] = $destination;
                        }
                    }
                }
            }
        }
    }
    
    // Assign evidence from link to accounts - each account gets different evidence
    // Group accounts by link index to distribute evidence properly
    $accountsByLink = [];
    $totalAccounts = count($namaAkun);
    for ($i = 0; $i < $totalAccounts; $i++) {
        $linkIdx = isset($linkIndexes[$i]) ? (int)$linkIndexes[$i] : ($i % count($links));
        $linkIdx = max(0, min($linkIdx, count($links) - 1));
        if (!isset($accountsByLink[$linkIdx])) {
            $accountsByLink[$linkIdx] = [];
        }
        $accountsByLink[$linkIdx][] = $i; // Store account index with original order
    }
    
    // Assign evidence to each account based on their position in the link
    // Each account gets ONE evidence image based on their order (first account = first evidence, etc.)
    $evidenceImages = [];
    foreach ($accountsByLink as $linkIdx => $accountIndices) {
        $linkEvidence = $evidenceByLink[$linkIdx] ?? [];
        $evidenceCount = count($linkEvidence);
        $accountCount = count($accountIndices);
        
        if ($evidenceCount > 0) {
            // Distribute evidence: each account gets one evidence based on their position
            // Account at position 0 gets evidence[0], position 1 gets evidence[1], etc.
            // If more accounts than evidence, cycle through evidence (account gets evidence[position % evidenceCount])
            foreach ($accountIndices as $position => $accountIndex) {
                // Each account gets ONE evidence image based on their position
                $evidenceIndex = $position % $evidenceCount; // Cycle if more accounts than evidence
                $evidenceImages[$accountIndex] = [$linkEvidence[$evidenceIndex]];
            }
        } else {
            // No evidence for this link
            foreach ($accountIndices as $accountIndex) {
                $evidenceImages[$accountIndex] = [];
            }
        }
    }
    
    // Ensure all accounts have evidence array (even if empty)
    for ($i = 0; $i < $totalAccounts; $i++) {
        if (!isset($evidenceImages[$i])) {
            $evidenceImages[$i] = [];
        }
    }

    // Prepare data for report
    // Setiap akun bisa punya link yang berbeda (atau sama)
    $reportData = [];
    $totalAccounts = count($namaAkun);
    
    for ($i = 0; $i < $totalAccounts; $i++) {
        $linkIdx = isset($linkIndexes[$i]) ? (int)$linkIndexes[$i] : ($i % count($links));
        $linkIdx = max(0, min($linkIdx, count($links) - 1)); // Ensure valid index
        
        $reportData[] = [
            'no' => $i + 1,
            'link' => $links[$linkIdx],
            'nama_akun' => $namaAkun[$i],
            'narasi' => $narasi[$i],
            'platform' => isset($platforms[$linkIdx]) ? $platforms[$linkIdx] : 'Unknown',
            'evidence' => $evidenceImages[$i] ?? []
        ];
    }

    // Generate WhatsApp format text
    // Pass original links array to maintain order
    $waFormat = generateWhatsAppFormat($judul, $tanggalFormattedWA, $reportData, $links);

    // Generate Word document using template
    $templatePath = __DIR__ . '/template_word/template_engagement.docx';
    
    if (!file_exists($templatePath)) {
        error_log("Template not found: " . $templatePath);
        error_log("Current directory: " . __DIR__);
        error_log("Template directory exists: " . (file_exists(__DIR__ . '/template_word') ? 'yes' : 'no'));
        
        // List files in template_word directory for debugging
        if (file_exists(__DIR__ . '/template_word')) {
            $files = scandir(__DIR__ . '/template_word');
            error_log("Files in template_word: " . implode(', ', $files));
        }
        
        throw new Exception('Template Word tidak ditemukan di: ' . $templatePath . '. Pastikan file template_engagement.docx ada di folder template_word/');
    }

    try {
        $templateProcessor = new TemplateProcessor($templatePath);

        // Set basic values
        $templateProcessor->setValue('judul', cleanTextForWord($judul));
        $templateProcessor->setValue('tanggal', $tanggalFormatted);
        $templateProcessor->setValue('jumlah_akun', $totalAccounts);

        // Clone rows for table data
        $templateProcessor->cloneRow('no', $totalAccounts);
        
        foreach ($reportData as $index => $data) {
            $rowIndex = $index + 1;
            
            // Set values for each row
            $templateProcessor->setValue("no#{$rowIndex}", $data['no']);
            $templateProcessor->setValue("tautan_konten#{$rowIndex}", cleanTextForWord($data['link']));
            $templateProcessor->setValue("nama_akun#{$rowIndex}", cleanTextForWord($data['nama_akun']));
            $templateProcessor->setValue("narasi#{$rowIndex}", cleanTextForWord($data['narasi']));
            
            // Handle evidence images
            if (!empty($data['evidence']) && count($data['evidence']) > 0) {
                $firstImage = null;
                foreach ($data['evidence'] as $imgPath) {
                    if (file_exists($imgPath) && is_file($imgPath)) {
                        $imageInfo = @getimagesize($imgPath);
                        if ($imageInfo !== false) {
                            if ($firstImage === null) {
                                $firstImage = $imgPath;
                            }
                            // Note: TemplateProcessor can only set one image per placeholder
                            // If multiple images needed, we'll use the first one
                        }
                    }
                }
                
                if ($firstImage !== null) {
                    try {
                        // Ukuran gambar persegi (square) yang rapi dan pas dalam kolom tabel
                        // Width dan Height sama untuk membuat gambar persegi
                        // Ukuran 2 inch x 2 inch = 144 points x 144 points (pas dalam kolom, tidak keluar)
                        $imageSize = 144;   // 2 inch = 144 points (persegi)
                        
                        $templateProcessor->setImageValue("eviden#{$rowIndex}", [
                            'path' => $firstImage,
                            'width' => $imageSize,
                            'height' => $imageSize,
                            'ratio' => false  // Memaksa ukuran persegi agar rapi dan tidak keluar kolom
                        ]);
                    } catch (Exception $e) {
                        error_log("Error setting image for row {$rowIndex}: " . $e->getMessage());
                        // Fallback: ukuran sedikit lebih kecil tapi tetap persegi
                        try {
                            $imageSize = 130;   // ~1.8 inch = 130 points (persegi)
                            
                            $templateProcessor->setImageValue("eviden#{$rowIndex}", [
                                'path' => $firstImage,
                                'width' => $imageSize,
                                'height' => $imageSize,
                                'ratio' => false
                            ]);
                        } catch (Exception $e2) {
                            error_log("Error setting image with smaller size for row {$rowIndex}: " . $e2->getMessage());
                            // Fallback ketiga: ukuran lebih kecil lagi tapi tetap persegi
                            try {
                                $imageSize = 115;   // ~1.6 inch = 115 points (persegi)
                                
                                $templateProcessor->setImageValue("eviden#{$rowIndex}", [
                                    'path' => $firstImage,
                                    'width' => $imageSize,
                                    'height' => $imageSize,
                                    'ratio' => false
                                ]);
                            } catch (Exception $e3) {
                                error_log("Error setting image with medium size for row {$rowIndex}: " . $e3->getMessage());
                                $templateProcessor->setValue("eviden#{$rowIndex}", 'Gambar tidak dapat dimuat');
                            }
                        }
                    }
                } else {
                    $templateProcessor->setValue("eviden#{$rowIndex}", '-');
                }
            } else {
                $templateProcessor->setValue("eviden#{$rowIndex}", '-');
            }
        }

        // Save document
        // Clean judul: keep alphanumeric, spaces, and dash (-)
        // Hanya hapus karakter khusus, pertahankan semua huruf, angka, spasi, dan dash
        // PASTIKAN TIDAK ADA KARAKTER YANG HILANG - hanya hapus karakter yang benar-benar tidak diinginkan
        $judulClean = preg_replace('/[^a-zA-Z0-9\s\-]/u', '', $judul);
        
        // Convert to uppercase - PASTIKAN SEMUA KARAKTER TETAP ADA
        $judulClean = mb_strtoupper($judulClean, 'UTF-8');
        
        // Normalize spaces (multiple spaces to single space)
        $judulClean = preg_replace('/\s+/u', ' ', $judulClean);
        $judulClean = trim($judulClean);
        
        // Remove duplicate "ENGAGEMENT" di awal judul jika ada (setelah di-uppercase)
        // PASTIKAN TIDAK MENGHAPUS KARAKTER LAIN - hanya hapus jika benar-benar dimulai dengan "ENGAGEMENT "
        // Gunakan cara yang lebih eksplisit dan aman
        $prefixToRemove = 'ENGAGEMENT ';
        $prefixLength = mb_strlen($prefixToRemove, 'UTF-8');
        if (mb_substr($judulClean, 0, $prefixLength, 'UTF-8') === $prefixToRemove) {
            $judulClean = mb_substr($judulClean, $prefixLength, null, 'UTF-8');
            $judulClean = trim($judulClean);
        }
        
        // Limit length - pastikan tidak memotong di tengah kata jika mungkin
        if (mb_strlen($judulClean, 'UTF-8') > 100) {
            $judulClean = mb_substr($judulClean, 0, 100, 'UTF-8');
            $judulClean = trim($judulClean);
        }
        
        // Format tanggal untuk UPDATE (format: "23 DESEMBER 2025" - semua kapital)
        $tanggalUpdate = strtoupper(formatTanggalIndonesia($tanggal));
        
        // Nama file: LAPORAN ENGAGEMENT [JUDUL] UPDATE [TANGGAL INDONESIA KAPITAL].docx
        // Tanpa underscore, tanpa tanggal format 23122025, semua uppercase, dengan UPDATE di akhir
        $outputFileName = 'LAPORAN ENGAGEMENT ' . $judulClean . ' UPDATE ' . $tanggalUpdate . '.docx';
        $outputPath = $hasilDir . '/' . $outputFileName;
        
        $templateProcessor->saveAs($outputPath);
        
        // Verify file was created
        if (!file_exists($outputPath)) {
            throw new Exception('File Word gagal dibuat. Path: ' . $outputPath);
        }
        
        error_log("Word document created successfully: " . $outputPath);
        
    } catch (Exception $e) {
        error_log("Error creating Word document: " . $e->getMessage());
        error_log("Stack trace: " . $e->getTraceAsString());
        throw new Exception('Gagal membuat dokumen Word: ' . $e->getMessage());
    }

    // Return success response with proper URL encoding
    $downloadUrl = 'hasil/' . rawurlencode($outputFileName);
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Laporan berhasil dibuat',
        'file_name' => $outputFileName,
        'download_url' => $downloadUrl,
        'file_path' => $outputPath,
        'file_exists' => file_exists($outputPath),
        'total_accounts' => $totalAccounts,
        'tanggal' => $tanggalFormatted,
        'wa_format' => $waFormat
    ]);

} catch (Exception $e) {
    http_response_code(400);
    $errorMessage = $e->getMessage();
    error_log("API Engagement Error: " . $errorMessage);
    error_log("Stack trace: " . $e->getTraceAsString());
    
    $response = [
        'success' => false,
        'message' => $errorMessage,
        'error_type' => get_class($e)
    ];
    
    // Add template info if available
    if (isset($templatePath)) {
        $response['template_path'] = $templatePath;
        $response['template_exists'] = file_exists($templatePath);
    }
    
    echo json_encode($response);
}

/**
 * Generate WhatsApp format text for engagement report
 */
function generateWhatsAppFormat($judul, $tanggal, $reportData, $originalLinks = []) {
    // Group data by link
    $groupedByLink = [];
    
    foreach ($reportData as $data) {
        $link = $data['link'];
        if (!isset($groupedByLink[$link])) {
            $groupedByLink[$link] = [];
        }
        $groupedByLink[$link][] = $data;
    }
    
    // Use original links order if provided, otherwise use order from reportData
    $linkOrder = [];
    if (!empty($originalLinks)) {
        // Use original links order, but only include links that have accounts
        foreach ($originalLinks as $link) {
            if (isset($groupedByLink[$link]) && count($groupedByLink[$link]) > 0) {
                $linkOrder[] = $link;
            }
        }
    } else {
        // Fallback: use order from reportData
        foreach ($reportData as $data) {
            $link = $data['link'];
            if (!in_array($link, $linkOrder) && isset($groupedByLink[$link]) && count($groupedByLink[$link]) > 0) {
                $linkOrder[] = $link;
            }
        }
    }
    
    // Count accounts per platform (only from links that have accounts)
    $platformCounts = [];
    foreach ($groupedByLink as $link => $accounts) {
        if (count($accounts) > 0) {
            // Get platform from first account of this link
            $platform = $accounts[0]['platform'];
            if (!isset($platformCounts[$platform])) {
                $platformCounts[$platform] = 0;
            }
            $platformCounts[$platform] += count($accounts);
        }
    }
    
    // Build platform summary text
    $platformSummary = [];
    foreach ($platformCounts as $platform => $count) {
        $platformSummary[] = $count . " akun " . $platform;
    }
    $platformSummaryText = implode(" dan ", $platformSummary);
    
    // Build WhatsApp format
    $text = "Kepada  Yth.: Kasuari-2\n\n";
    $text .= "Dari : Merpati-14\n\n";
    $text .= "Tembusan :\n";
    $text .= "1. Kasuari-21\n";
    $text .= "2. Kasuari-22\n";
    $text .= "3. Kasuari-23\n";
    $text .= "4. Kasuari-24\n";
    $text .= "5. Kasuari-25\n";
    $text .= "6. Kasuari-63\n";
    $text .= "7. Kasuari-75\n\n";
    $text .= "Perihal : " . $judul . "\n\n";
    $text .= "A. EXECUTIVE SUMMARY\n\n";
    $text .= "Pada " . $tanggal . ", telah dilakukan upaya " . $judul . " dengan total " . $platformSummaryText . ".\n\n";
    $text .= "B. HASIL ENGAGEMENT\n";
    
    // List only links that have accounts (maintain order from original links)
    $linkNumber = 1;
    foreach ($linkOrder as $link) {
        $accounts = $groupedByLink[$link];
        
        // Only include links that have at least one account
        if (count($accounts) > 0) {
            $text .= $linkNumber . ". " . $link . "\n";
            
            $accountLetter = 'a';
            foreach ($accounts as $account) {
                $text .= $accountLetter . ". Melakukan like dan komen menggunakan akun " . $account['nama_akun'] . " dengan narasi \"" . $account['narasi'] . "\"\n";
                $accountLetter++;
            }
            
            $text .= "\n";
            $linkNumber++;
        }
    }
    
    // If no links with accounts found, add a note
    if (empty($linkOrder)) {
        $text .= "Tidak ada data engagement yang ditemukan.\n\n";
    }
    
    $text .= "C. CATATAN DAN KENDALA\n";
    $text .= "Pelaksanaan engangement berjalan aman dan lancar. Jajaran Merpati  - 14 terus melakukan viralisasi serta mengamplifikasi konten ke platform media sosial yang ada di wilayah Merpati  - 14.\n\n";
    $text .= "D. DOKUMENTASI TERLAMPIR\n\n";
    $text .= "Nilai : A-1\n";
    $text .= "DMMP.";
    
    return $text;
}
?>

