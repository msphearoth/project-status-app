<?php

/**
 * Cross-platform equivalent of `rm -rf vendor node_modules public/build`
 * plus clearing compiled framework caches. Run via `make clean`.
 */
function removeDirectory(string $path): void
{
    if (! is_dir($path)) {
        return;
    }

    foreach (scandir($path) as $item) {
        if ($item === '.' || $item === '..') {
            continue;
        }

        $itemPath = $path.DIRECTORY_SEPARATOR.$item;
        is_dir($itemPath) ? removeDirectory($itemPath) : unlink($itemPath);
    }

    rmdir($path);
}

$root = __DIR__.'/..';

foreach (['vendor', 'node_modules', 'public/build'] as $dir) {
    removeDirectory($root.'/'.$dir);
    echo "Removed {$dir}\n";
}

foreach (glob($root.'/bootstrap/cache/*.php') as $file) {
    unlink($file);
}

echo "Cleared bootstrap/cache\n";
