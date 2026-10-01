<?php

declare(strict_types=1);

namespace Stu\Extension;

use RuntimeException;

final class ExtensionProcess
{
    public function __invoke(array $command, string $directory): string
    {
        $environment = getenv();
        $environment['GIT_TERMINAL_PROMPT'] = '0';
        $environment['GIT_SSH_COMMAND'] = ($environment['GIT_SSH_COMMAND'] ?? 'ssh')
            . ' -o BatchMode=yes -o ConnectTimeout=10 -o StrictHostKeyChecking=yes';
        $environment['GCM_INTERACTIVE'] = 'never';
        $environment['CI'] = 'true';
        $process = proc_open($command, [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes, $directory, $environment);
        if (!is_resource($process)) {
            throw new RuntimeException('Could not start deployment command');
        }
        fclose($pipes[0]);
        stream_set_blocking($pipes[1], false);
        stream_set_blocking($pipes[2], false);
        $output = '';
        $startedAt = microtime(true);
        try {
            do {
                $output .= stream_get_contents($pipes[1]);
                stream_get_contents($pipes[2]);
                if (strlen($output) > 1048576) {
                    throw new RuntimeException('Deployment command exceeded output limit');
                }
                $status = proc_get_status($process);
                if (!$status['running']) {
                    $output .= stream_get_contents($pipes[1]);
                    if ($status['exitcode'] !== 0) {
                        throw new RuntimeException('Deployment command failed; check access, branch and dependencies');
                    }
                    return trim($output);
                }
                if (microtime(true) - $startedAt > 180) {
                    throw new RuntimeException('Deployment command timed out');
                }
                usleep(20000);
            } while (true);
        } finally {
            if (proc_get_status($process)['running']) {
                proc_terminate($process, 9);
            }
            fclose($pipes[1]);
            fclose($pipes[2]);
            proc_close($process);
        }
    }
}
