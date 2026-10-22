<?php

declare(strict_types=1);

namespace Stu\Html\Security;

use Dom\HTMLDocument;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use request;
use Stu\Component\Game\ModuleEnum;
use Stu\Exception\SanityCheckException;
use Stu\Module\Control\Component\View\ViewContextFactoryInterface;
use Stu\Module\Control\ComponentSetupInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\Render\GameTwigRendererInterface;
use Stu\Module\Starmap\View\RefreshSection\RefreshSection;
use Stu\Module\Twig\TrustedTemplateLoader;
use Stu\StubGameComponentsTrait;
use Stu\TwigTestCase;
use Twig\Environment;

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
final class StarmapTemplateSecurityTest extends TwigTestCase
{
    use StubGameComponentsTrait;

    public function testRefreshRendersBothAllowedMapTemplates(): void
    {
        $this->stubGameComponents();
        self::$testSession->setUserById(101);
        $dic = $this->getContainer();
        self::assertInstanceOf(TrustedTemplateLoader::class, $dic->get(Environment::class)->getLoader());
        foreach ([
            null,
            'html/map/starmapSectionTable.twig',
            'html/colony/telescopeSectionTable.twig'
        ] as $macro) {
            $parameters = ['layerid' => 2, 'section' => 1];
            if ($macro !== null) {
                $parameters['macro'] = $macro;
            }
            request::setMockVars($parameters);
            $game = $dic->get(GameControllerInterface::class);
            $context = $dic->get(ViewContextFactoryInterface::class)->createViewContext($game, ModuleEnum::STARMAP);
            $dic->get(RefreshSection::class)->handle($context);
            self::assertSame($macro ?? 'html/map/starmapSectionTable.twig', $game->getGameData()->macro);
            $dic->get(ComponentSetupInterface::class)->setup();
            $html = $dic->get(GameTwigRendererInterface::class)->render($game, $game->getUser());
            $document = HTMLDocument::createFromString($html, LIBXML_NOERROR);
            self::assertNotNull($document->querySelector('#starmapsectiontable'));
            self::assertNull($document->querySelector('form[action="options.php"]'));
        }
    }

    public function testArbitraryTemplatesAreRejectedBeforeSelection(): void
    {
        self::$testSession->setUserById(101);
        $dic = $this->getContainer();
        $game = $dic->get(GameControllerInterface::class);
        foreach ([
            'Public/avatare/user/crew/LOCAL/m/1_1.png',
            'html/../Public/avatare/user/crew/LOCAL/m/1_1.png',
            '@__main__/Public/avatare/user/crew/LOCAL/m/1_1.png',
            'html/view/options.twig',
            '/etc/passwd'
        ] as $macro) {
            request::setMockVars(['layerid' => 2, 'section' => 1, 'macro' => $macro]);
            $context = $dic->get(ViewContextFactoryInterface::class)->createViewContext($game, ModuleEnum::STARMAP);
            try {
                $dic->get(RefreshSection::class)->handle($context);
                self::fail('Eine beliebige Vorlage darf nicht ausgewählt werden');
            } catch (SanityCheckException $e) {
                self::assertSame('Invalid map section template', $e->getMessage());
                self::assertSame('', $game->getGameData()->macro);
            }
        }
    }

    public function testEveryCoreTemplateRemainsLoadable(): void
    {
        $loader = $this->getContainer()->get(Environment::class)->getLoader();
        $root = realpath(__DIR__ . '/../../../../src');
        $files = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root . '/html', \FilesystemIterator::SKIP_DOTS));
        $count = 0;
        foreach ($files as $file) {
            if ($file->getExtension() !== 'twig') {
                continue;
            }
            $name = substr($file->getPathname(), strlen($root) + 1);
            self::assertTrue($loader->exists($name), $name);
            self::assertSame(file_get_contents($file->getPathname()), $loader->getSourceContext($name)->getCode(), $name);
            $count++;
        }
        self::assertGreaterThan(0, $count);
    }

    public function testUnseenLayerRemainsInaccessible(): void
    {
        self::$testSession->setUserById(101);
        request::setMockVars(['layerid' => 1, 'section' => 1]);
        $dic = $this->getContainer();
        $context = $dic->get(ViewContextFactoryInterface::class)->createViewContext($dic->get(GameControllerInterface::class), ModuleEnum::STARMAP);
        $this->expectException(SanityCheckException::class);
        $this->expectExceptionMessage('user tried to access unseen layer');
        $dic->get(RefreshSection::class)->handle($context);
    }
}
