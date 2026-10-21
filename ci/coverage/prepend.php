<?php

declare(strict_types=1);

// Mounted into test containers only; it is never shipped in the application image.
if (extension_loaded('pcov') && getenv('JOURNAL_COVERAGE_DIR')) {
    \pcov\start();
    register_shutdown_function(static function () : void {
        \pcov\stop();
        $coverage = \pcov\collect();
        $hashes = [];

        foreach ($coverage as $file => $lines) {
            $hashes[$file] = hash_file('sha256', $file);
        }
        $path = getenv('JOURNAL_COVERAGE_DIR') . '/' . bin2hex(random_bytes(16)) . '.json';
        file_put_contents($path, json_encode(['lines' => $coverage, 'hashes' => $hashes], JSON_THROW_ON_ERROR), LOCK_EX);
    });
}
