<?php

declare(strict_types=1);

namespace Stu\Module\Twig;

use Noodlehaus\Config;
use Noodlehaus\Parser\Json;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Container\ContainerInterface;
use Stu\Extension\ExtensionRegistry;
use Stu\Module\Config\StuConfigInterface;
use Stu\StuTestCase;
use Twig\Environment;
use Twig\Error\LoaderError;

final class TrustedTemplateLoaderTest extends StuTestCase
{
    private string $directory;
    private Environment $twig;

    #[\Override]
    protected function setUp(): void
    {
        $this->directory = sys_get_temp_dir() . '/stu-template-security-' . bin2hex(random_bytes(8));
        mkdir($this->directory . '/src/html', 0o700, true);
        mkdir($this->directory . '/src/Public/avatare/user/crew/LOCAL/m', 0o700, true);
        mkdir($this->directory . '/extension/templates', 0o700, true);
        file_put_contents($this->directory . '/src/html/layout.twig', 'HEADER{% block content %}{% endblock %}FOOTER');
        file_put_contents($this->directory . '/src/html/page.twig', '{% extends "html/layout.twig" %}{% block content %}{{ value }}{% endblock %}');
        copy(__DIR__ . '/../../../../src/html/ajaxempty.twig', $this->directory . '/src/html/ajaxempty.twig');
        file_put_contents($this->directory . '/extension/templates/base.twig', 'EXTENSION:{% block content %}{% endblock %}');
        file_put_contents($this->directory . '/extension/templates/widget.twig', '{% extends "@test/base.twig" %}{% block content %}{{ value }}{% endblock %}');
        file_put_contents($this->directory . '/extension/module.php', '<?php return ["id" => "template-test", "apiVersion" => 1, "templates" => ["test" => "templates"]];');
        $image = imagecreatetruecolor(51, 52);
        $upload = $this->directory . '/src/Public/avatare/user/crew/LOCAL/m/1_1.png';
        imagepng($image, $upload);
        file_put_contents($upload, 'LOCAL_TEMPLATE_{{ 7 * 7 }}', FILE_APPEND);
        file_put_contents($this->directory . '/src/config.twig', 'UNTRUSTED');
        mkdir($this->directory . '/src/html-untrusted');
        file_put_contents($this->directory . '/src/html-untrusted/template.twig', 'UNTRUSTED');
        symlink($upload, $this->directory . '/src/html/upload.twig');
        symlink($upload, $this->directory . '/extension/templates/upload.twig');
        $settings = $this->mock(StuConfigInterface::class);
        $settings->shouldReceive('getGameSettings->getWebroot')->andReturn($this->directory . '/src/Public');
        $settings->shouldReceive('getDebugSettings->isDebugMode')->andReturn(true);
        $config = new Config(json_encode(['extensions' => ['template-test' => ['enabled' => true, 'path' => $this->directory . '/extension']]]), new Json(), true);
        $registry = new ExtensionRegistry($config, $this->directory);
        $container = $this->mock(ContainerInterface::class);
        $container->shouldReceive('get')->with(StuConfigInterface::class)->andReturn($settings);
        $container->shouldReceive('get')->with(ExtensionRegistry::class)->andReturn($registry);
        $services = require __DIR__ . '/../../../../src/Module/Twig/services.php';
        $this->twig = $services[Environment::class]($container);
    }

    #[\Override]
    protected function tearDown(): void
    {
        $entries = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->directory, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($entries as $entry) {
            $entry->isDir() && !$entry->isLink() ? rmdir($entry->getPathname()) : unlink($entry->getPathname());
        }
        rmdir($this->directory);
        parent::tearDown();
    }

    public static function untrustedNames(): iterable
    {
        yield 'public upload' => ['Public/avatare/user/crew/LOCAL/m/1_1.png'];
        yield 'old main namespace' => ['@__main__/Public/avatare/user/crew/LOCAL/m/1_1.png'];
        yield 'traversal under html' => ['html/../Public/avatare/user/crew/LOCAL/m/1_1.png'];
        yield 'relative traversal' => ['../Public/avatare/user/crew/LOCAL/m/1_1.png'];
        yield 'backslash traversal' => ['html\\..\\Public\\avatare\\user\\crew\\LOCAL\\m\\1_1.png'];
        yield 'non-template source file' => ['config.twig'];
        yield 'similar directory prefix' => ['../html-untrusted/template.twig'];
        yield 'core symlink' => ['html/upload.twig'];
        yield 'extension symlink' => ['@test/upload.twig'];
        yield 'extension traversal' => ['@test/../../src/Public/avatare/user/crew/LOCAL/m/1_1.png'];
        yield 'NUL byte' => ["html/page.twig\0"];
        yield 'unknown namespace' => ['@unknown/page.twig'];
    }

    #[DataProvider('untrustedNames')]
    public function testUntrustedFilesCannotBeLoadedEvenAfterRepeatedLookups(string $name): void
    {
        $loader = $this->twig->getLoader();
        for ($attempt = 0; $attempt < 2; $attempt++) {
            self::assertFalse($loader->exists($name));
            foreach (['getSourceContext', 'getCacheKey', 'isFresh'] as $method) {
                try {
                    $method === 'isFresh' ? $loader->$method($name, time()) : $loader->$method($name);
                    self::fail('Eine nicht vertrauenswürdige Datei darf nicht als Vorlage geladen werden');
                } catch (LoaderError) {
                    self::assertFalse($loader->exists($name));
                }
            }
        }
        $this->expectException(LoaderError::class);
        $this->twig->render('html/ajaxempty.twig', ['MACRO' => $name]);
    }

    public static function trustedNames(): iterable
    {
        yield ['html/page.twig'];
        yield ['page.twig'];
        yield ['@__main__/html/page.twig'];
        yield ['html\\page.twig'];
    }

    #[DataProvider('trustedNames')]
    public function testCoreTemplatesKeepNamesInheritanceAndEscaping(string $name): void
    {
        self::assertTrue($this->twig->getLoader()->exists($name));
        self::assertSame('HEADER&lt;Spieler&gt;FOOTER', $this->twig->render($name, ['value' => '<Spieler>']));
        self::assertSame('HEADERZweiter AufrufFOOTER', $this->twig->render($name, ['value' => 'Zweiter Aufruf']));
    }

    public function testRegisteredExtensionTemplatesAndCoreIncludesRemainAvailable(): void
    {
        self::assertSame('EXTENSION:&lt;Erweiterung&gt;', $this->twig->render('@test/widget.twig', ['value' => '<Erweiterung>']));
        $html = $this->twig->render('html/ajaxempty.twig', ['MACRO' => '@test/widget.twig', 'value' => 'Test']);
        self::assertStringContainsString('EXTENSION:Test', $html);
    }

    public function testTemplateCacheDoesNotPermitUploadedSources(): void
    {
        $twig = new Environment($this->twig->getLoader(), ['cache' => $this->directory . '/cache']);
        self::assertSame('HEADERTestFOOTER', $twig->render('html/page.twig', ['value' => 'Test']));
        self::assertSame('HEADERErneutFOOTER', $twig->render('html/page.twig', ['value' => 'Erneut']));
        $this->expectException(LoaderError::class);
        $twig->render('html/ajaxempty.twig', ['MACRO' => 'Public/avatare/user/crew/LOCAL/m/1_1.png']);
    }
}
