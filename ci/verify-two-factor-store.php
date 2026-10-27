<?php

declare(strict_types=1);

// Runs only inside the disposable E2E web container against its synthetic user.
use App\Service\Security\RecoveryCodes;
use App\Service\Security\TwoFactorStore;

require getcwd() . '/private/init.php';
$store = $kernel->getContainer()->get(TwoFactorStore::class);
$userId = (int) ($argv[1] ?? 0);
$state = $store->find($userId);

if ($state === null || ! $state->enabled()) {
    throw new RuntimeException('Synthetic factor account is not enabled');
}

if (($argv[2] ?? '') === 'worker') {
    $payload = json_decode(stream_get_contents(STDIN), true, flags: JSON_THROW_ON_ERROR);
    touch($payload['ready']);

    while ( ! is_file($payload['gate'])) {
        usleep(1000);
    }
    $accepted = $payload['purpose'] === 'counter'
        ? $store->consumeCounter($userId, $payload['revision'], $payload['value'])
        : $store->consumeRecovery($userId, $payload['revision'], $payload['value']);
    echo $accepted ? 'accepted' : 'rejected';
    exit;
}
$code = stream_get_contents(STDIN);
$hash = new RecoveryCodes()->hash($code);

if ($hash === null) {
    throw new RuntimeException('Missing synthetic recovery proof');
}

foreach (['counter' => ($state->lastCounter ?? 0) + 1, 'recovery' => $hash] as $purpose => $value) {
    $gate = sys_get_temp_dir() . '/journal-factor-gate-' . bin2hex(random_bytes(12));
    $workers = [];

    try {
        for ($index = 0; $index < 12; ++$index) {
            $process = proc_open([PHP_BINARY, __FILE__, (string) $userId, 'worker'], [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);

            if ( ! is_resource($process)) {
                throw new RuntimeException('Could not start a concurrent proof worker');
            }
            $ready = $gate . '-ready-' . $index;
            fwrite($pipes[0], json_encode(['gate' => $gate, 'ready' => $ready, 'purpose' => $purpose, 'revision' => $state->revision, 'value' => $value], JSON_THROW_ON_ERROR));
            fclose($pipes[0]);
            $workers[] = [$process, $pipes, $ready];
        }
        $deadline = microtime(true) + 20;

        while ( ! array_all($workers, static fn ($worker) => is_file($worker[2]))) {
            if (microtime(true) >= $deadline) {
                throw new RuntimeException('Concurrent proof workers did not all reach the gate');
            }
            usleep(1000);
        }
        touch($gate);
        $accepted = $rejected = 0;

        foreach ($workers as [$process, $pipes]) {
            $result = stream_get_contents($pipes[1]);
            $errors = stream_get_contents($pipes[2]);
            fclose($pipes[1]);
            fclose($pipes[2]);

            if (proc_close($process) !== 0) {
                throw new RuntimeException('Concurrent proof worker failed: ' . $errors);
            }
            $accepted += $result === 'accepted' ? 1 : 0;
            $rejected += $result === 'rejected' ? 1 : 0;
        }

        if ($accepted !== 1 || $rejected !== 11) {
            throw new RuntimeException('Concurrent proof was not consumed exactly once: ' . $purpose);
        }
    } finally {
        foreach ($workers as [$process, $pipes, $ready]) {
            if (is_resource($process)) {
                proc_terminate($process);
                proc_close($process);
            }

            foreach ([$pipes[1], $pipes[2]] as $pipe) {
                if (is_resource($pipe)) {
                    fclose($pipe);
                }
            }

            if (is_file($ready)) {
                unlink($ready);
            }
        }

        if (is_file($gate)) {
            unlink($gate);
        }
    }
}
echo "Concurrent factor consumption: passed (12 separate DB connections each; exactly one counter and one recovery winner)\n";
