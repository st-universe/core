<?php

declare(strict_types=1);

namespace Stu\Extension;

use Closure;
use RuntimeException;

final class ExtensionInstaller
{
    private Closure $run;

    public function __construct(private string $root, ?Closure $run = null)
    {
        $this->run = $run ?? Closure::fromCallable(new ExtensionProcess());
    }

    public function install(string $id, array $settings, bool $force): bool
    {
        $this->validateId($id);
        $deployment = $this->resolveDeploymentSettings($settings);
        $base = $this->ensureInstallationDirectory($id);
        $lock = $this->openDeploymentLock($base);

        $release = null;
        try {
            if (!$this->acquireDeploymentLock($lock)) {
                return false;
            }

            if ($this->shouldSkipInstall($base, $deployment, $force)) {
                return false;
            }

            $this->rememberLastCheck($base);
            $revision = $this->resolveBranchRevision($deployment['repository'], $deployment['branch']);
            $identity = $this->buildDeploymentIdentity($deployment['repository'], $revision);

            if ($this->isCurrentDeploymentUpToDate($base, $identity)) {
                return false;
            }

            $this->assertCurrentPathIsSymlink($base);
            $release = $this->createReleaseDirectory($base);
            $this->prepareRelease($release, $deployment['repository'], $revision);
            $this->validateManifest($release, $id);
            $this->installRealtimeDependenciesIfNeeded($release);
            $this->writeDeploymentMarker($release, $identity);
            $this->activateRelease($base, $release);

            $release = null;
            return true;
        } finally {
            if ($release !== null && is_dir($release)) {
                $this->removeDirectory($release);
            }
            $this->releaseDeploymentLock($lock);
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

    private function acquireDeploymentLock(mixed $lock): bool
    {
        return flock($lock, LOCK_EX | LOCK_NB);
    }

    private function shouldSkipInstall(string $base, array $deployment, bool $force): bool
    {
        $lastCheck = is_file($base . '/last-check') ? (int) file_get_contents($base . '/last-check') : 0;
        return !$force && time() - $lastCheck < $deployment['intervalSeconds'];
    }

    private function rememberLastCheck(string $base): void
    {
        file_put_contents($base . '/last-check', (string) time());
    }

    private function resolveBranchRevision(string $repository, string $branch): string
    {
        $ref = 'refs/heads/' . $branch;
        $remote = ($this->run)(['git', 'ls-remote', '--exit-code', $repository, $ref], $this->root);

        if (!preg_match('/^([a-f0-9]{40})\s+' . preg_quote($ref, '/') . '$/D', trim($remote), $match)) {
            throw new RuntimeException('Could not resolve exact module branch');
        }

        return $match[1];
    }

    private function buildDeploymentIdentity(string $repository, string $revision): string
    {
        return hash('sha256', $repository . "\n" . $revision);
    }

    private function isCurrentDeploymentUpToDate(string $base, string $identity): bool
    {
        $current = $base . '/current';
        return is_file($current . '/.stu-deployment')
            && trim((string) file_get_contents($current . '/.stu-deployment')) === $identity;
    }

    private function assertCurrentPathIsSymlink(string $base): void
    {
        $current = $base . '/current';
        if (file_exists($current) && !is_link($current)) {
            throw new RuntimeException('Managed current path must be a symlink');
        }
    }

    private function createReleaseDirectory(string $base): string
    {
        return $base . '/release-' . bin2hex(random_bytes(8));
    }

    private function prepareRelease(string $release, string $repository, string $revision): void
    {
        ($this->run)(['git', '-c', 'core.hooksPath=/dev/null', 'init', $release], $this->root);
        ($this->run)(['git', '-c', 'core.hooksPath=/dev/null', 'fetch', '--depth=1', '--no-tags', '--', $repository, $revision], $release);
        ($this->run)(['git', '-c', 'core.hooksPath=/dev/null', 'checkout', '--detach', 'FETCH_HEAD'], $release);

        $downloadedRevision = ($this->run)(['git', 'rev-parse', 'HEAD'], $release);
        if ($downloadedRevision !== $revision) {
            throw new RuntimeException('Downloaded revision does not match resolved module branch');
        }
    }

    private function validateManifest(string $release, string $id): void
    {
        if (!is_file($release . '/module.php')) {
            throw new RuntimeException('Downloaded repository is not an extracted module');
        }

        $manifest = require_once $release . '/module.php';
        if (!is_array($manifest) || ($manifest['id'] ?? null) !== $id || ($manifest['apiVersion'] ?? null) !== ExtensionRegistry::API_VERSION) {
            throw new RuntimeException('Downloaded module has an incompatible manifest');
        }
    }

    private function installRealtimeDependenciesIfNeeded(string $release): void
    {
        $manifest = require_once $release . '/module.php';
        if (!isset($manifest['realtime']['entry'])) {
            return;
        }

        $entry = realpath($release . '/' . $manifest['realtime']['entry']);
        if ($entry === false || !str_starts_with($entry, $release . '/')) {
            throw new RuntimeException('Invalid module realtime entry');
        }

        if (is_file(dirname($entry) . '/package-lock.json')) {
            ($this->run)(['npm', 'ci', '--omit=dev', '--ignore-scripts', '--no-audit', '--no-fund'], dirname($entry));
        }
    }

    private function writeDeploymentMarker(string $release, string $identity): void
    {
        if (file_put_contents($release . '/.stu-deployment', $identity . "\n") === false) {
            throw new RuntimeException('Cannot save module revision');
        }
    }

    private function activateRelease(string $base, string $release): void
    {
        $link = $base . '/next';
        $current = $base . '/current';

        if (is_link($link)) {
            unlink($link);
        }

        if (!symlink(basename($release), $link) || !rename($link, $current)) {
            throw new RuntimeException('Cannot activate module release');
        }
    }

    private function releaseDeploymentLock(mixed $lock): void
    {
        if (is_resource($lock)) {
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }

    private function removeDirectory(string $directory): void
    {
        foreach (scandir($directory) ?: [] as $entry) {
            if ($entry === '.' || $entry === '..') {
                continue;
            }
            $path = $directory . '/' . $entry;
            if (is_dir($path) && !is_link($path)) {
                $this->removeDirectory($path);
            } else {
                unlink($path);
            }
        }
        rmdir($directory);
    }
}
