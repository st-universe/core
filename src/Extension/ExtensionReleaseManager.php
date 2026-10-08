<?php

declare(strict_types=1);

namespace Stu\Extension;

use Closure;
use RuntimeException;

final class ExtensionReleaseManager
{
    public function __construct(
        private string $root,
        private Closure $run
    ) {}

    public function deploy(string $id, string $base, array $deployment): bool
    {
        $revision = $this->resolveBranchRevision($deployment['repository'], $deployment['branch']);
        $identity = $this->buildDeploymentIdentity($deployment['repository'], $revision);

        if ($this->isCurrentDeploymentUpToDate($base, $identity)) {
            return false;
        }

        $this->assertCurrentPathIsSymlink($base);
        $release = $this->createReleaseDirectory($base);

        try {
            $this->prepareRelease($release, $deployment['repository'], $revision);
            $manifest = $this->validateManifest($release, $id);
            $this->installRealtimeDependenciesIfNeeded($release, $manifest);
            $this->writeDeploymentMarker($release, $identity);
            $this->activateRelease($base, $release);
            $release = null;

            return true;
        } finally {
            if ($release !== null && is_dir($release)) {
                $this->removeDirectory($release);
            }
        }
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

    private function validateManifest(string $release, string $id): array
    {
        if (!is_file($release . '/module.php')) {
            throw new RuntimeException('Downloaded repository is not an extracted module');
        }

        $manifest = require $release . '/module.php';
        if (!is_array($manifest) || ($manifest['id'] ?? null) !== $id || ($manifest['apiVersion'] ?? null) !== ExtensionRegistry::API_VERSION) {
            throw new RuntimeException('Downloaded module has an incompatible manifest');
        }

        return $manifest;
    }

    private function installRealtimeDependenciesIfNeeded(string $release, array $manifest): void
    {
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
