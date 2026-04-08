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
            applyHeaderTheme($path);
        }
    }
}

function applyHeaderTheme($filePath) {
    $content = file_get_contents($filePath);
    $changed = false;

    // We want to match:
    // <div class="card">
    //     <div class="card-header d-block d-md-flex">
    //         <h2 class="mb-0">Laporan ...</h2>
    //     </div>
    //
    // And replace it with:
    // <h2 style="color: #002D72; font-weight: bold; margin-bottom: 20px;">Laporan ...</h2>
    // <div class="card border-0 shadow-sm">
    //     <div class="card-body"> ... (or just leave the inner divs as is, but we'll add shadow to card)
    
    $pattern = '/<div\s+class="card">\s*<div\s+class="card-header\s+[^"]*">\s*<h2\s+class="mb-0">([^<]+)<\/h2>\s*<\/div>/is';
    
    if (preg_match($pattern, $content)) {
        $replacement = "<h2 style=\"color: #002D72; font-weight: bold; margin-bottom: 20px;\">$1</h2>\n        <div class=\"card border-0 shadow-sm p-3\">";
        $content = preg_replace($pattern, $replacement, $content);
        $changed = true;
    }

    if ($changed) {
        file_put_contents($filePath, $content);
        echo "Updated header: $filePath\n";
    }
}

processDirectory($directory);
echo "Done updating headers.\n";
