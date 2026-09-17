<?php
$file = __DIR__ . '/data/products.json';

echo '<h2>Diagnostic: products.json</h2>';
echo '<p><strong>Path:</strong> ' . $file . '</p>';
echo '<p><strong>Exists:</strong> ' . (file_exists($file) ? 'YES' : 'NO') . '</p>';

if (file_exists($file)) {
    $raw = file_get_contents($file);
    echo '<p><strong>Size:</strong> ' . strlen($raw) . ' bytes</p>';

    echo '<h3>Raw content:</h3>';
    echo '<pre style="background:#f0f0f0;padding:10px;max-height:300px;overflow:auto;">';
    echo htmlspecialchars($raw);
    echo '</pre>';

    $data = json_decode($raw, true);
    echo '<h3>Parsed structure:</h3>';
    echo '<pre style="background:#f0f0f0;padding:10px;max-height:300px;overflow:auto;">';
    print_r($data);
    echo '</pre>';

    if (is_array($data)) {
        echo '<p><strong>Items:</strong> ' . count($data) . '</p>';
        if (!empty($data[0]) && is_array($data[0])) {
            echo '<p><strong>Keys in first item:</strong> ' . implode(', ', array_keys($data[0])) . '</p>';
        }
    }
}