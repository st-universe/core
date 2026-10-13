<?php

declare(strict_types=1);

namespace Stu\Component\Asset;

use FilesystemIterator;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Stu\Module\Twig\TwigPage;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

final class AssetVersioningTest extends TestCase
{
    private string $directory;
    private AssetVersioning $subject;

    protected function setUp(): void
    {
        $this->directory = sys_get_temp_dir() . '/stu-assets-' . bin2hex(random_bytes(8));
        mkdir($this->directory . '/assets/generated/asset-versions', 0o775, true);
        mkdir($this->directory . '/static/js', 0o775, true);
        mkdir($this->directory . '/static/css', 0o775, true);
        file_put_contents($this->directory . '/static/js/assetUrl.js', 'function assetUrl(url) { return url; }');
        $this->writeManifest('1234567890abcdef1234');
        $this->subject = new AssetVersioning($this->directory);
    }

    protected function tearDown(): void
    {
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->directory, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($files as $file) {
            $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
        }
        rmdir($this->directory);
    }

    public function testRewritesRenderedTwigAndAjaxWithoutChangingExternalUrlsOrText(): void
    {
        $template = <<<'HTML'
            <img src="/assets/buttons/test.png?size=2&amp;v=old#icon" srcset="/assets/buttons/test.png 1x, /assets/buttons/test.png 2x">
            <div style="background:url(assets/buttons/test.png)"></div>
            <img onmouseover="this.src='/assets/buttons/test.png'" data-src="/assets/buttons/test.png">
            <img src="https://example.org/assets/buttons/test.png"><img src="//example.org/assets/buttons/test.png"><img src="https://example.org/image?url=/assets/buttons/test.png">
            <img src="/assets/missing.png">Text /assets/buttons/test.png
            HTML;
        $page = new TwigPage(new Environment(new ArrayLoader(['test' => $template])), $this->subject);
        $page->setTemplate('test');
        $result = $page->render();
        self::assertStringContainsString('?size=2&amp;v=1234567890abcdef1234#icon', $result);
        self::assertStringContainsString('srcset="/assets/buttons/test.png?v=1234567890abcdef1234 1x, /assets/buttons/test.png?v=1234567890abcdef1234 2x"', $result);
        self::assertStringContainsString('url(assets/buttons/test.png?v=1234567890abcdef1234)', $result);
        self::assertStringContainsString("this.src='/assets/buttons/test.png?v=1234567890abcdef1234'", $result);
        self::assertStringContainsString('data-src="/assets/buttons/test.png?v=1234567890abcdef1234"', $result);
        self::assertStringContainsString('src="https://example.org/assets/buttons/test.png"', $result);
        self::assertStringContainsString('src="//example.org/assets/buttons/test.png"', $result);
        self::assertStringContainsString('src="https://example.org/image?url=/assets/buttons/test.png"', $result);
        self::assertStringContainsString('src="/assets/missing.png"', $result);
        self::assertStringContainsString('Text /assets/buttons/test.png', $result);
        self::assertStringNotContainsString('<script src=', $result);
    }

    public function testManifestIsRefreshedBetweenRenders(): void
    {
        $html = '<img src="/assets/buttons/test.png">';
        $first = $this->subject->rewrite($html);
        $this->writeManifest('abcdef1234567890abcd');
        $second = $this->subject->rewrite($html);
        self::assertNotSame($first, $second);
        self::assertStringContainsString('v=abcdef1234567890abcd', $second);
        self::assertSame($second, $this->subject->rewrite($second));
    }

    public function testMissingOrInvalidManifestLeavesImagesUsableAndLoadsHelper(): void
    {
        unlink($this->directory . '/assets/asset-manifest.json');
        $html = '<head></head><img src="/assets/buttons/test.png">';
        $result = $this->subject->rewrite($html);
        self::assertStringContainsString('<img src="/assets/buttons/test.png">', $result);
        self::assertStringContainsString('/static/js/assetUrl.js?v=', $result);
        file_put_contents($this->directory . '/assets/asset-manifest.json', '{invalid');
        self::assertSame($result, $this->subject->rewrite($html));
    }

    public function testRuntimeManifestLoadsBeforeGameScripts(): void
    {
        $result = $this->subject->rewrite('<head><script src="/static/js/default.js"></script></head>');
        self::assertLessThan(strpos($result, '/static/js/assetUrl.js'), strpos($result, '/assets/generated/asset-versions/1234567890abcdef1234.js'));
        self::assertLessThan(strpos($result, '/static/js/default.js'), strpos($result, '/static/js/assetUrl.js'));
    }

    public function testCssGetsNewUrlOnlyWhenItsContentOrReferencedGraphicsChange(): void
    {
        file_put_contents($this->directory . '/static/css/test.css', '.test{background:url(/assets/buttons/test.png)} .other{background:url(../image.png)} @import "theme.css";');
        $html = '<link rel="stylesheet" href="/version_42/static/css/test.css">';
        $first = $this->subject->rewrite($html);
        preg_match('~href="([^"]+)"~', $first, $matches);
        self::assertStringStartsWith('/generated-assets/', $matches[1]);
        $css = file_get_contents($this->directory . $matches[1]);
        self::assertStringContainsString('/assets/buttons/test.png?v=1234567890abcdef1234', $css);
        self::assertStringContainsString('/static/css/../image.png', $css);
        self::assertStringContainsString('@import "/static/css/theme.css"', $css);
        self::assertSame($first, $this->subject->rewrite($html));
        $this->writeManifest('abcdef1234567890abcd');
        self::assertNotSame($first, $this->subject->rewrite($html));
        self::assertFileExists($this->directory . $matches[1]);
    }

    public function testStaticScriptsAreVersionedWithoutAllowingDirectoryTraversal(): void
    {
        file_put_contents($this->directory . '/static/js/test.js', 'test');
        $result = $this->subject->rewrite('<script src="/version_42/static/js/test.js?v=old"></script>');
        self::assertStringContainsString('/version_42/static/js/test.js?v=' . substr(hash('sha256', 'test'), 0, 20), $result);
        file_put_contents($this->directory . '/private.js', 'secret');
        $html = '<script src="/static/../private.js"></script>';
        self::assertSame($html, $this->subject->rewrite($html));
    }

    public function testAssetsCanBeReadFromSeparateRepository(): void
    {
        rename($this->directory . '/assets', $this->directory . '/external-assets');
        $subject = new AssetVersioning($this->directory, $this->directory . '/external-assets');
        self::assertStringContainsString('v=1234567890abcdef1234', $subject->rewrite('<img src="/assets/buttons/test.png">'));
    }

    private function writeManifest(string $hash): void
    {
        file_put_contents($this->directory . '/assets/generated/asset-versions/' . $hash . '.json', json_encode(['buttons/test.png' => $hash], JSON_THROW_ON_ERROR));
        file_put_contents($this->directory . '/assets/asset-manifest.json', json_encode([
            'shards' => [substr(hash('sha256', 'buttons'), 0, 2) => 'generated/asset-versions/' . $hash . '.json'],
            'script' => 'generated/asset-versions/1234567890abcdef1234.js',
        ], JSON_THROW_ON_ERROR));
    }
}
