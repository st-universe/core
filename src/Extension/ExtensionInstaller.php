<?php

declare(strict_types=1);

namespace Stu\Extension;

use Closure;
use RuntimeException;

final class ExtensionInstaller
{
    private Closure $run;
    private ExtensionReleaseManager $releaseManager;

    public function __construct(private string $root, ?Closure $run = null)
    {
        $this->run = $run ?? Closure::fromCallable(new ExtensionProcess());
        $this->releaseManager = new ExtensionReleaseManager($root, $this->run);
    }

    public function install(string $id, array $settings, bool $force): bool
    {
        $this->validateId($id);
        $deployment = $this->resolveDeploymentSettings($settings);
        $base = $this->ensureInstallationDirectory($id);
        $lock = $this->openDeploymentLock($base);

        try {
            if (!flock($lock, LOCK_EX | LOCK_NB) || $this->shouldSkipInstall($base, $deployment, $force)) {
                return false;
            }

            file_put_contents($base . '/last-check', (string) time());
            return $this->releaseManager->deploy($id, $base, $deployment);
        } finally {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }

    private function validateId(string $id): void
    {
        if (!preg_match('/^[a-zA-Z0-9_-]+$/D', $id)) {
            throw new RuntimeException('Invalid extension identifier');
        }
    }

    private function resolveDeploymentSettings(array $settings): array
    {
        if (isset($settings['path'])) {
            throw new RuntimeException('Use either a local path or managed deployment, not both');
        }

        $deployment = $settings['deployment'];
        $repository = $deployment['repository'] ?? '';
        $branch = $deployment['branch'] ?? '';

        if (!is_string($repository) || !preg_match('~^(git@[a-zA-Z0-9.-]+:[a-zA-Z0-9_./-]+|https://[a-zA-Z0-9.-]+/[a-zA-Z0-9_./-]+)$~D', $repository)) {
            throw new RuntimeException('Use a Git SSH or HTTPS repository without embedded credentials');
        }

        if (!is_string($branch) || !preg_match('~^[a-zA-Z0-9][a-zA-Z0-9_./-]*$~D', $branch)) {
            throw new RuntimeException('Invalid deployment branch');
        }

        return [
            'repository' => $repository,
            'branch' => $branch,
            'intervalSeconds' => max(60, (int) ($deployment['intervalSeconds'] ?? 300)),
        ];
    }

    private function ensureInstallationDirectory(string $id): string
    {
        $base = $this->root . '/var/extensions/' . $id;
        if (!is_dir($base) && !mkdir($base, 0750, true) && !is_dir($base)) {
            throw new RuntimeException('Cannot create module installation directory');
        }

        return $base;
    }

    private function openDeploymentLock(string $base): mixed
    {
        $lock = fopen($base . '/deploy.lock', 'c');
        if ($lock === false) {
            throw new RuntimeException('Cannot open module deployment lock');
        }

        return $lock;
    }

    private function shouldSkipInstall(string $base, array $deployment, bool $force): bool
    {
        $lastCheck = is_file($base . '/last-check') ? (int) file_get_contents($base . '/last-check') : 0;
        return !$force && time() - $lastCheck < $deployment['intervalSeconds'];
    }
}
