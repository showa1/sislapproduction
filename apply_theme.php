<?php
$directory = __DIR__ . '/modules/Eksekutif/views/';

function processDirectory($dir) {
    if (!is_dir($dir)) return;
    
    $items = scandir($dir);
    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        
        $path = $dir . '/' . $item;
        
        if (is_dir($path)) {
            processDirectory($path);
        } elseif (is_file($path) && substr($item, -4) === '.php') {
            applyTheme($path);
        }
    }
}

function applyTheme($filePath) {
    $content = file_get_contents($filePath);
    $changed = false;

    // 1. Ganti Warna Header Tabel (Oranye ke Navy Blue)
    if (strpos($content, '#f5981b') !== false) {
        $content = str_replace('#f5981b', '#002D72', $content);
        $changed = true;
    }

    // 2. Ganti warna text header jika perlu (Pastikan text row header putih)
    if (strpos($content, 'background-color: #002D72;') !== false && strpos($content, 'color: white;') === false) {
        $content = str_replace('background-color: #002D72;', "background-color: #002D72;\n            color: white;", $content);
        // Clean up double color white just in case
        $content = preg_replace('/(color:\s*white;[\s\S]*?)color:\s*white;/', '$1', $content);
        $changed = true;
    }

    // 3. Tombol Cari
    if (preg_match('/Html::submitButton\(([\'"])Cari\1,\s*\[\'class\'\s*=>\s*\'btn\s+btn-dark\b/ims', $content)) {
        // Ganti 'btn btn-dark' dengan 'btn btn-primary' dan tambahkan icon dengan warna biru pekat (navy feel)
        $content = preg_replace('/Html::submitButton\(([\'"])Cari\1,\s*\[\'class\'\s*=>\s*\'btn\s+btn-dark([^\']*)\'\]\)/ims', 
            "Html::submitButton('<i class=\"bi bi-search\"></i> Cari', ['class' => 'btn btn-primary$2', 'style' => 'background-color: #002D72; border-color: #002D72;'])", 
            $content);
        $changed = true;
    }
    
    // 4. Tombol Ulang
    if (preg_match('/Html::a\(([\'"])Ulang\1,/ims', $content)) {
        $content = preg_replace('/Html::a\(([\'"])Ulang\1,\s*(.+?),\s*\[\'id\'\s*=>\s*\'ulang-button\',\s*\'class\'\s*=>\s*\'btn\s+btn-danger([^\']*)\'\]\)/ims', 
            "Html::a('<i class=\"bi bi-arrow-clockwise\"></i> Ulang', $2, ['id' => 'ulang-button', 'class' => 'btn btn-outline-secondary$3'])", 
            $content);
        $changed = true;
    }

    // 5. Tombol Export
    if (preg_match('/Html::button\(([\'"])Export\1,/ims', $content)) {
        $content = preg_replace('/Html::button\(([\'"])Export\1,\s*\[\'id\'\s*=>\s*\'export-button\',\s*\'class\'\s*=>\s*\'btn\s+btn-primary([^\']*)\'\]\)/ims', 
            "Html::button('<i class=\"bi bi-file-earmark-spreadsheet\"></i> Export Excel', ['id' => 'export-button', 'class' => 'btn btn-success$2', 'style' => 'background-color: #6DC536; border-color: #6DC536;'])", 
            $content);
        $changed = true;
    }

    if ($changed) {
        file_put_contents($filePath, $content);
        echo "Updated: $filePath\n";
    }
}

processDirectory($directory);
echo "Done filtering files.\n";
