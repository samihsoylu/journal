<?php

declare(strict_types=1);

use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Data\RawCodeCoverageData;
use SebastianBergmann\CodeCoverage\Driver\Selector;
use SebastianBergmann\CodeCoverage\Filter;
use SebastianBergmann\CodeCoverage\Report\Facade;
use SebastianBergmann\CodeCoverage\Serialization\Unserializer;

// Combine PHPUnit and real HTTP/CLI execution without omitting uncovered source.
$root = dirname(__DIR__, 2);
require $root . '/vendor/autoload.php';
$directory = $argv[1] ?? '';
$threshold = (float) ($argv[2] ?? 95);

if ( ! is_dir($directory) || $threshold < 0 || $threshold > 100) {
    fwrite(STDERR, "Usage: php ci/coverage/report.php <coverage-directory> [minimum-percentage=95]\n");
    exit(1);
}
$filter = new Filter();
$xml = simplexml_load_file($root . '/phpunit.xml.dist');

foreach ($xml->xpath('/phpunit/source/include/directory') as $path) {
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root . '/' . (string) $path, FilesystemIterator::SKIP_DOTS)) as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $filter->includeFile($file->getPathname());
        }
    }
}

foreach ($xml->xpath('/phpunit/source/include/file') as $path) {
    $filter->includeFile($root . '/' . (string) $path);
}
$coverage = new CodeCoverage(new Selector()->select($filter), $filter);
$unit = new Unserializer()->unserialize($directory . '/phpunit.cov');

foreach ($unit['codeCoverage']->coveredFiles() as $file) {
    $unit['codeCoverage']->renameFile($file, $root . '/' . $file);
}
$coverage->setData($unit['codeCoverage']);
$coverage->setTests($unit['testResults']);
$captures = glob($directory . '/requests/*.json');

if ($captures === []) {
    throw new RuntimeException('No HTTP/CLI coverage was captured.');
}

foreach ($captures as $index => $capture) {
    $data = json_decode(file_get_contents($capture), true, 512, JSON_THROW_ON_ERROR);
    $lines = [];

    foreach ($data['lines'] as $containerFile => $executed) {
        $file = $root . substr($containerFile, strlen('/var/www/html'));

        if ($filter->isExcluded($file)) {
            continue;
        }

        if (hash_file('sha256', $file) !== ($data['hashes'][$containerFile] ?? null)) {
            throw new RuntimeException('Coverage is stale for ' . $file . '. Rebuild and rerun the tests.');
        }
        $lines[$file] = $executed;
    }
    $coverage->append(RawCodeCoverageData::fromLineCoverage($lines), 'docker-http-cli-' . $index);
}
$report = Facade::fromObject($coverage);
$report->renderClover($directory . '/clover.xml');
$report->renderText($directory . '/summary.txt', showUncoveredFiles: true);
$report->renderHtml($directory . '/html');
$summary = $report->summary();
printf("Application line coverage: %d/%d = %.2f%% (required %.2f%%)\n", $summary->numberOfExecutedLines(), $summary->numberOfExecutableLines(), $summary->lineCoverageAsPercentage(), $threshold);
exit($summary->lineCoverageAsPercentage() >= $threshold ? 0 : 1);
