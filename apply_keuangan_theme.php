<?php
/**
 * Script to apply PMC Theme to all Keuangan module reports
 */

$baseDir = __DIR__ . '/modules/Keuangan/views';
$directories = glob($baseDir . '/*', GLOB_ONLYDIR);

$themeCss = "
        .custom-gridview thead th {
            background-color: #002D72; /* PMC Navy Blue */
            color: #ffffff;
            font-size: 15px;
            text-align: center;
            font-weight: 600;
            border-bottom: 2px solid #dee2e6;
            vertical-align: middle;
            white-space: nowrap;
        }
        .card-header {
            background-color: transparent !important;
            border-bottom: 2px solid #002D72 !important;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .card-header h2 {
            color: #002D72;
            font-weight: 700;
            font-size: 1.5rem;
        }
        .pagination .page-item.active .page-link {
            background-color: #002D72;
            border-color: #002D72;
        }
    ";

$buttonSearchOld = "'class' => 'btn btn-dark m-3'";
$buttonSearchNew = "'class' => 'btn btn-outline-primary m-3', 'style' => 'border-color: #002D72; color: #002D72;'";
$buttonUlangOld = "'class' => 'btn btn-danger m-3'";
$buttonUlangNew = "'class' => 'btn btn-outline-danger m-3'";
$buttonExportOld = "'class' => 'btn btn-primary m-3'";
$buttonExportNew = "'class' => 'btn btn-success m-3', 'style' => 'background-color: #6DC536; border-color: #6DC536;'";

$buttonSearchOldHtml = "Cari', ['class'";
$buttonSearchNewHtml = "<i class=\"bi bi-search\"></i> Cari', ['class'";
$buttonUlangOldHtml = "Ulang', \$resetUrl";
$buttonUlangNewHtml = "<i class=\"bi bi-arrow-clockwise\"></i> Ulang', \$resetUrl";
$buttonExportOldHtml = "Export', ['id'";
$buttonExportNewHtml = "<i class=\"bi bi-file-earmark-excel\"></i> Export Excel', ['id'";

$count = 0;

foreach ($directories as $dir) {
    // Skip default which is the dashboard
    if (basename($dir) === 'default') continue;
    
    $indexFile = $dir . '/index.php';
    if (file_exists($indexFile)) {
        $content = file_get_contents($indexFile);
        $originalContent = $content;
        
        // 1. Replace the CSS block
        // Find existing custom-gridview thead th block and replace it
        $patternCss = '/\.custom-gridview\s+thead\s+th\s*\{[^}]+\}/s';
        if (preg_match($patternCss, $content)) {
             $content = preg_replace($patternCss, trim($themeCss), $content);
        }

        // 2. Add Bootstrap Icons to Buttons if not already there
        if (strpos($content, '<i class="bi bi-search"></i>') === false) {
            $content = str_replace($buttonSearchOldHtml, $buttonSearchNewHtml, $content);
        }
        if (strpos($content, '<i class="bi bi-arrow-clockwise"></i>') === false) {
            $content = str_replace($buttonUlangOldHtml, $buttonUlangNewHtml, $content);
        }
        if (strpos($content, '<i class="bi bi-file-earmark-excel"></i>') === false) {
            $content = str_replace($buttonExportOldHtml, $buttonExportNewHtml, $content);
        }

        // 3. Replace button classes
        $content = str_replace($buttonSearchOld, $buttonSearchNew, $content);
        $content = str_replace($buttonUlangOld, $buttonUlangNew, $content);
        $content = str_replace($buttonExportOld, $buttonExportNew, $content);

        if ($content !== $originalContent) {
            file_put_contents($indexFile, $content);
            echo "Updated: " . basename($dir) . "/index.php\n";
            $count++;
        } else {
            echo "No changes needed for: " . basename($dir) . "/index.php\n";
        }
    }
}

echo "\nTotal files updated: $count\n";
