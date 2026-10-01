<?php

declare(strict_types=1);

namespace Stu\Extension;

use Closure;
use RuntimeException;
use Throwable;

final class ExtensionDeployer
{
    private Closure $run;

    public function __construct(private string $root, ?Closure $run = null)
    {
        $this->run = $run ?? Closure::fromCallable(new ExtensionProcess());
    }

    public function deploy(array $extensions, bool $force = false): array
    {
        $result = ['changed' => false, 'messages' => []];
        foreach ($extensions as $id => $settings) {
            if (($settings['enabled'] ?? false) !== true || !isset($settings['deployment'])) {
                continue;
            }
            try {
                $changed = $this->install($id, $settings, $force);
                $result['changed'] = $result['changed'] || $changed;
                if ($changed) {
                    $result['messages'][] = '[extensions] ' . $id . ' updated';
                }
            } catch (Throwable $error) {
                $result['messages'][] = '[extensions] ' . $id . ' skipped: ' . $error->getMessage();
            }
        }
        return $result;
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

    private function install(string $id, array $settings, bool $force): bool
    {
        $this->validateId($id);
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
        $base = $this->root . '/var/extensions/' . $id;
        if (!is_dir($base) && !mkdir($base, 0750, true) && !is_dir($base)) {
            throw new RuntimeException('Cannot create module installation directory');
        }
        $lock = fopen($base . '/deploy.lock', 'c');
        if ($lock === false) {
            throw new RuntimeException('Cannot open module deployment lock');
        }
        $release = null;
        $link = $base . '/next';
        try {
            if (!flock($lock, LOCK_EX | LOCK_NB)) {
                return false;
            }
            $interval = max(60, (int) ($deployment['intervalSeconds'] ?? 300));
            $lastCheck = is_file($base . '/last-check') ? (int) file_get_contents($base . '/last-check') : 0;
            if (!$force && time() - $lastCheck < $interval) {
                return false;
            }
            file_put_contents($base . '/last-check', (string) time());
            $ref = 'refs/heads/' . $branch;
            $remote = ($this->run)(['git', 'ls-remote', '--exit-code', $repository, $ref], $this->root);
            if (!preg_match('/^([a-f0-9]{40})\s+' . preg_quote($ref, '/') . '$/D', trim($remote), $match)) {
                throw new RuntimeException('Could not resolve exact module branch');
            }
            $revision = $match[1];
            $identity = hash('sha256', $repository . "\n" . $revision);
            $current = $base . '/current';
            if (is_file($current . '/.stu-deployment') && trim(file_get_contents($current . '/.stu-deployment')) === $identity) {
                return false;
            }
            if (file_exists($current) && !is_link($current)) {
                throw new RuntimeException('Managed current path must be a symlink');
            }
            $release = $base . '/release-' . bin2hex(random_bytes(8));
            ($this->run)(['git', '-c', 'core.hooksPath=/dev/null', 'init', $release], $this->root);
            ($this->run)(['git', '-c', 'core.hooksPath=/dev/null', 'fetch', '--depth=1', '--no-tags', '--', $repository, $revision], $release);
            ($this->run)(['git', '-c', 'core.hooksPath=/dev/null', 'checkout', '--detach', 'FETCH_HEAD'], $release);
            $downloadedRevision = ($this->run)(['git', 'rev-parse', 'HEAD'], $release);
            if ($downloadedRevision !== $revision) {
                throw new RuntimeException('Downloaded revision does not match resolved module branch');
            }
            if (!is_file($release . '/module.php')) {
                throw new RuntimeException('Downloaded repository is not an extracted module');
            }
            $manifest = require $release . '/module.php';
            if (!is_array($manifest) || ($manifest['id'] ?? null) !== $id || ($manifest['apiVersion'] ?? null) !== ExtensionRegistry::API_VERSION) {
                throw new RuntimeException('Downloaded module has an incompatible manifest');
            }
            if (isset($manifest['realtime']['entry'])) {
                $entry = realpath($release . '/' . $manifest['realtime']['entry']);
                if ($entry === false || !str_starts_with($entry, $release . '/')) {
                    throw new RuntimeException('Invalid module realtime entry');
                }
                if (is_file(dirname($entry) . '/package-lock.json')) {
                    ($this->run)(['npm', 'ci', '--omit=dev', '--ignore-scripts', '--no-audit', '--no-fund'], dirname($entry));
                }
            }
            if (file_put_contents($release . '/.stu-deployment', $identity . "\n") === false) {
                throw new RuntimeException('Cannot save module revision');
            }
            if (is_link($link)) {
                unlink($link);
            }
            if (!symlink(basename($release), $link) || !rename($link, $current)) {
                throw new RuntimeException('Cannot activate module release');
            }
            $release = null;
            return true;
        } finally {
            if ($release !== null && is_dir($release)) {
                $this->removeDirectory($release);
            }
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
