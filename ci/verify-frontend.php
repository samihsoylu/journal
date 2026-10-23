<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$manifest = json_decode(file_get_contents(__DIR__ . '/frontend-dependencies.json'), true, 512, JSON_THROW_ON_ERROR);

foreach ($manifest['files'] as $file => $expected) {
    if ( ! is_file($root . '/' . $file) || hash_file('sha256', $root . '/' . $file) !== $expected) {
        fwrite(STDERR, 'Frontend checksum mismatch: ' . $file . PHP_EOL);
        exit(1);
    }
}
echo "Vendored frontend dependency checksums match the pinned inventory.\n";
