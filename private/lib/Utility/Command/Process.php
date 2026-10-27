<?php

declare(strict_types=1);

namespace App\Utility\Command;

use App\Utility\Command\Interfaces\ProcessInterface;
use RuntimeException;

final readonly class Process implements ProcessInterface
{
    private function __construct(private int $id) {}

    public function getId() : int
    {
        return $this->id;
    }

    /**
     * Start a system process and don't wait for it to finish.
     */
    public static function start(Command $command, string $logPath = '/dev/null', string $input = '') : self
    {
        $setsidPath = $_ENV['SETSID_PATH'] ?? '/usr/bin/setsid';

        // Explicitly inherit the pipe: asynchronous shells otherwise replace stdin
        // with /dev/null. Secrets stay out of argv, environment and temporary files.
        $process = proc_open(sprintf('exec 3<&0; %s %s <&3 3<&- > %s 2>&1 & echo $!', escapeshellarg($setsidPath), $command, escapeshellarg($logPath)), [
            0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['file', '/dev/null', 'a'],
        ], $pipes);

        if ( ! is_resource($process)) {
            throw new RuntimeException('Could not start background process');
        }
        $written = fwrite($pipes[0], $input);
        fclose($pipes[0]);
        $id = filter_var(trim(stream_get_contents($pipes[1])), FILTER_VALIDATE_INT);
        fclose($pipes[1]);
        $status = proc_close($process);

        if ($status !== 0 || $id === false || $id <= 0 || $written !== strlen($input)) {
            throw new RuntimeException('Could not start background process');
        }

        return new self($id);
    }

    /**
     * Check if a process is running in the system.
     *
     * @return bool true if process exists
     */
    public function isRunning() : bool
    {
        $command = new Command(['ps', '-p', $this->id]);
        exec((string) $command, $output);

        return isset($output[1]);
    }

    /**
     * Kill an existing system process.
     */
    public function stop() : void
    {
        $command = new Command(['kill', $this->id]);
        $command->execute();
    }

    public static function getById(int $id) : ?self
    {
        $self = new self($id);

        if ( ! $self->isRunning()) {
            return null;
        }

        return $self;
    }
}
