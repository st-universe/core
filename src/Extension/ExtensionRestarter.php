<?php

declare(strict_types=1);

namespace Stu\Extension;

use Closure;
use RuntimeException;
use Throwable;

final class ExtensionRestarter
{
    private Closure $run;

    public function __construct(private string $root, ?Closure $run = null)
    {
        $this->run = $run ?? Closure::fromCallable(new ExtensionProcess());
    }

    public function restart(array $extensions): array
    {
        $result = ['failed' => false, 'messages' => []];
        foreach ($extensions as $id => $settings) {
            $command = $settings['deployment']['restartCommand'] ?? null;
            if (($settings['enabled'] ?? false) !== true || $command === null) {
                continue;
            }
            try {
                $this->validateId($id);
                if (!is_file($this->root . '/var/extensions/' . $id . '/current/module.php')) {
                    continue;
                }
                if (!is_array($command) || !array_is_list($command) || $command === []
                    || count(array_filter($command, 'is_string')) !== count($command)) {
                    throw new RuntimeException('restartCommand must be a nonempty argument list');
                }
                ($this->run)($command, $this->root);
                $result['messages'][] = '[extensions] ' . $id . ' restarted';
            } catch (Throwable $error) {
                $result['failed'] = true;
                $result['messages'][] = '[extensions] ' . $id . ' restart failed: ' . $error->getMessage();
            }
        }
        return $result;
    }

    private function validateId(string $id): void
    {
        if (!preg_match('/^[a-zA-Z0-9_-]+$/D', $id)) {
            throw new RuntimeException('Invalid extension identifier');
        }
    }
}
