<?php

$manifest = __DIR__ . '/../public/build/manifest.json';

if (!file_exists($manifest)) {
    $rootPath = __DIR__ . '/../';
    echo "⚙️  Vite manifest not found. Running build automatically...\n";
    exec('cd ' . escapeshellarg($rootPath) . ' && npm run build');
    echo "✅  Vite build completed.\n";
}
