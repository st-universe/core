<?php

declare(strict_types=1);

namespace Stu\Extension;

use Composer\Autoload\ClassLoader;
use Noodlehaus\ConfigInterface;
use RuntimeException;

final class ExtensionRegistry
{
    public const int API_VERSION = 1;

    private array $extensions = [];

    public function __construct(private ConfigInterface $config, private string $root)
    {
        foreach ($config->get('extensions', []) as $id => $settings) {
            if (($settings['enabled'] ?? false) !== true) {
                continue;
            }
            $path = $settings['path'] ?? (isset($settings['deployment'])
                ? 'var/extensions/' . $id . '/current'
                : 'vendor/st-universe/' . $id);
            $directory = realpath(str_starts_with($path, '/') ? $path : $this->root . '/' . $path);
            if ($directory === false || !is_file($directory . '/module.php')) {
                continue;
            }
            $manifest = ExtensionManifestLoader::load($directory . '/module.php');
            if (!is_array($manifest) || ($manifest['id'] ?? null) !== $id || ($manifest['apiVersion'] ?? null) !== self::API_VERSION) {
                throw new RuntimeException(sprintf('Incompatible extension manifest: %s', $id));
            }
            $loader = new ClassLoader();
            foreach ($manifest['autoload'] ?? [] as $namespace => $relativePath) {
                $loader->addPsr4($namespace, $directory . '/' . $relativePath);
            }
            $loader->register();
            $this->extensions[$id] = $manifest + ['directory' => $directory, 'settings' => $settings];
        }
    }

    public function all(): array
    {
        return $this->extensions;
    }

    public function get(string $id): array
    {
        return $this->extensions[$id] ?? throw new RuntimeException(sprintf('Extension %s is disabled or missing', $id));
    }

    public function entityPaths(): array
    {
        $paths = [];
        foreach ($this->extensions as $extension) {
            foreach ($extension['entities'] ?? [] as $path) {
                $paths[] = $extension['directory'] . '/' . $path;
            }
        }
        return $paths;
    }

    public function templates(string $slot): array
    {
        $templates = [];
        foreach ($this->extensions as $extension) {
            foreach ($extension['slots'][$slot] ?? [] as $template) {
                $templates[] = $template;
            }
        }
        return $templates;
    }

    public function assetVersion(string $id, string $name): string
    {
        $extension = $this->get($id);
        $path = $extension['assets'][$name] ?? throw new RuntimeException('Unknown extension asset');
        $hash = hash_file('sha256', $extension['directory'] . '/' . $path);
        if ($hash === false) {
            throw new RuntimeException('Cannot read extension asset');
        }
        return substr($hash, 0, 16);
    }

    public function handlers(string $hook): array
    {
        $handlers = [];
        foreach ($this->extensions as $extension) {
            foreach ($extension['hooks'][$hook] ?? [] as $handler) {
                $handlers[] = $handler;
            }
        }
        return $handlers;
    }

    public function migrations(string $platform): array
    {
        $classes = [];
        foreach ($this->extensions as $extension) {
            foreach ($extension['migrations'][$platform] ?? [] as $namespace => $path) {
                foreach (glob($extension['directory'] . '/' . $path . '/Version*.php') ?: [] as $file) {
                    $classes[] = $namespace . '\\' . basename($file, '.php');
                }
            }
        }
        return $classes;
    }

    public function managesSchemaAsset(string $name): bool
    {
        foreach ($this->config->get('extensions', []) as $id => $settings) {
            foreach ($settings['tablePrefixes'] ?? [] as $prefix) {
                if (!isset($this->extensions[$id]) && $prefix !== '' && str_starts_with($name, $prefix)) {
                    return false;
                }
            }
        }
        return true;
    }
}
