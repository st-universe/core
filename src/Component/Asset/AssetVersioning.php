<?php

declare(strict_types=1);

namespace Stu\Component\Asset;

use RuntimeException;
use stdClass;

final class AssetVersioning implements AssetVersioningInterface
{
    private stdClass $shards;
    private stdClass $loadedShards;

    private readonly string $assetsPath;

    public function __construct(private readonly string $webroot, ?string $assetsPath = null)
    {
        $this->assetsPath = $assetsPath ?? $webroot . '/assets';
    }

    #[\Override]
    public function rewrite(string $html): string
    {
        $manifestPath = $this->assetsPath . '/asset-manifest.json';
        $manifest = is_file($manifestPath) ? json_decode((string) file_get_contents($manifestPath)) : null;
        $this->shards = $manifest instanceof stdClass && ($manifest->shards ?? null) instanceof stdClass
            ? $manifest->shards
            : new stdClass();
        $this->loadedShards = new stdClass();

        $html = preg_replace_callback(
            '~<script\b[^>]*>.*?</script\s*>|<style\b[^>]*>.*?</style\s*>|<[^>]+>~is',
            fn (array $match): string => $this->rewriteUrls($match[0]),
            $html
        ) ?? $html;

        $html = preg_replace_callback(
            '~(<(?:script|link)\b[^>]*\b(?:src|href)=["\'])(/(?:version_[^/]+/)?static/[^"\'?#]+\.(?:js|css))([^"\']*)(["\'])~i',
            fn (array $match): string => $match[1] . $this->staticUrl($match[2], $match[3]) . $match[4],
            $html
        ) ?? $html;

        if (preg_match('~<head\b[^>]*>~i', $html) !== 1) {
            return $html;
        }

        $scripts = '';
        $script = $manifest instanceof stdClass ? ($manifest->script ?? null) : null;
        if (is_string($script) && preg_match('~^generated/asset-versions/[a-f0-9]{20}\.js$~D', $script)) {
            $scripts .= '<script src="/assets/' . $script . '"></script>' . "\n";
        }
        $scripts .= '<script src="' . $this->staticUrl('/static/js/assetUrl.js', '') . '"></script>' . "\n";

        return preg_replace_callback('~<head\b[^>]*>~i', static fn (array $match): string => $match[0] . "\n" . $scripts, $html, 1) ?? $html;
    }

    private function rewriteUrls(string $content): string
    {
        return preg_replace_callback(
            '~(?<![a-zA-Z0-9_./=?:\\\\-])/?assets/[a-zA-Z0-9_./%+\~\x80-\xff -]+\.(?:png|jpe?g|gif|svg|webp|avif|ico)(?:\?[^\s"\'<>`)]*)?(?:\#[^\s"\'<>`)]*)?(?![a-zA-Z0-9_./-])~i',
            function (array $match): string {
                $path = substr($match[0], 0, strcspn($match[0], '?#'));
                $key = preg_replace('~^/?assets/~', '', $path) ?? $path;
                $hash = $this->getHash(rawurldecode($key));
                if (!is_string($hash) || preg_match('/^[a-f0-9]{20}$/D', $hash) !== 1) {
                    return $match[0];
                }
                return $this->withVersion($match[0], $hash);
            },
            $content
        ) ?? $content;
    }

    private function getHash(string $path): ?string
    {
        $bucket = substr(hash('sha256', dirname($path)), 0, 2);
        if (!isset($this->loadedShards->{$bucket})) {
            $filename = $this->shards->{$bucket} ?? null;
            $entries = null;
            if (is_string($filename) && preg_match('~^generated/asset-versions/[a-f0-9]{20}\.json$~D', $filename)) {
                $file = $this->assetsPath . '/' . $filename;
                $entries = is_file($file) ? json_decode((string) file_get_contents($file)) : null;
            }
            $this->loadedShards->{$bucket} = $entries instanceof stdClass ? $entries : new stdClass();
        }
        $entries = $this->loadedShards->{$bucket};
        $hash = $entries instanceof stdClass ? ($entries->{$path} ?? null) : null;
        return is_string($hash) ? $hash : null;
    }

    private function withVersion(string $url, string $hash): string
    {
        $fragmentIndex = strpos($url, '#');
        $fragment = $fragmentIndex === false ? '' : substr($url, $fragmentIndex);
        $url = $fragmentIndex === false ? $url : substr($url, 0, $fragmentIndex);
        $parts = explode('?', $url, 2);
        $separator = str_contains($parts[1] ?? '', '&amp;') ? '&amp;' : '&';
        $parameters = array_filter(
            explode($separator, $parts[1] ?? ''),
            static fn (string $parameter): bool => $parameter !== '' && explode('=', $parameter, 2)[0] !== 'v'
        );
        $parameters[] = 'v=' . $hash;

        return $parts[0] . '?' . implode($separator, $parameters) . $fragment;
    }

    private function staticUrl(string $url, string $suffix): string
    {
        $path = preg_replace('~^/version_[^/]+/~', '/', $url) ?? $url;
        $file = realpath($this->webroot . $path);
        $staticRoot = realpath($this->webroot . '/static');
        if ($file === false || $staticRoot === false || !str_starts_with($file, $staticRoot . DIRECTORY_SEPARATOR) || !is_file($file)) {
            return $url . $suffix;
        }
        $content = file_get_contents($file);
        if ($content === false) {
            return $url . $suffix;
        }

        if (str_ends_with($path, '.css')) {
            $rewritten = $this->rewriteUrls($content);
            if ($rewritten !== $content) {
                $base = dirname($path) . '/';
                $rewritten = preg_replace_callback(
                    '~url\(\s*(["\']?)([^)"\']+)\1\s*\)|(@import\s+)(["\'])([^"\']+)\4~i',
                    static function (array $match) use ($base): string {
                        $value = trim($match[5] ?? $match[2]);
                        if (!preg_match('~^(?:/|\#|[a-z][a-z0-9+.-]*:)~i', $value)) {
                            $value = $base . $value;
                        }
                        return isset($match[5]) ? $match[3] . $match[4] . $value . $match[4] : 'url("' . $value . '")';
                    },
                    $rewritten
                ) ?? $rewritten;
                return $this->publishStylesheet($rewritten);
            }
        }

        return $this->withVersion($url . $suffix, substr(hash('sha256', $content), 0, 20));
    }

    private function publishStylesheet(string $content): string
    {
        $url = '/generated-assets/' . substr(hash('sha256', $content), 0, 20) . '.css';
        $path = $this->webroot . $url;
        if (is_file($path)) {
            return $url;
        }
        $directory = dirname($path);
        if (!is_dir($directory) && !mkdir($directory, 0o775, true) && !is_dir($directory)) {
            throw new RuntimeException('Verzeichnis für versionierte Stylesheets konnte nicht erstellt werden');
        }
        $temporary = tempnam($directory, '.asset-');
        if ($temporary === false) {
            throw new RuntimeException('Versioniertes Stylesheet konnte nicht erstellt werden');
        }
        try {
            if (file_put_contents($temporary, $content) !== strlen($content) || !chmod($temporary, 0o644) || !rename($temporary, $path)) {
                throw new RuntimeException('Versioniertes Stylesheet konnte nicht veröffentlicht werden');
            }
        } finally {
            if (is_file($temporary)) {
                unlink($temporary);
            }
        }
        return $url;
    }
}
