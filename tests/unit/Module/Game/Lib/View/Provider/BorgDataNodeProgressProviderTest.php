<?php

declare(strict_types=1);

namespace Stu\Module\Game\Lib\View\Provider;

use PHPUnit\Framework\Attributes\DataProvider;
use Stu\Module\Commodity\CommodityTypeConstants;
use Stu\Module\Control\Component\View\ViewControllerContext;
use Stu\Module\Template\StatusBarFactory;
use Stu\Module\Template\StatusBarInterface;
use Stu\Orm\Repository\StorageRepositoryInterface;
use Stu\StuTestCase;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

final class BorgDataNodeProgressProviderTest extends StuTestCase
{
    /** @return iterable<string, array{int, int}> */
    public static function amounts(): iterable
    {
        yield 'empty player storage' => [0, 0];
        yield 'first data node' => [1, 0];
        yield 'half complete' => [100, 50];
        yield 'not complete before the two hundredth node' => [199, 99];
        yield 'complete' => [200, 100];
        yield 'more than the target stays complete' => [400, 100];
    }

    #[DataProvider('amounts')]
    public function testTheGlobalPlayerStockRendersWithTheExistingStatusBar(int $amount, int $expected): void
    {
        $storage = $this->mock(StorageRepositoryInterface::class);
        $storage->shouldReceive('getCommodityAmountInPlayerStorage')->once()->with(CommodityTypeConstants::COMMODITY_BORG_DATA_NODE)->andReturn($amount);
        $game = $this->mock(ViewControllerContext::class);
        $variables = [];
        $game->shouldReceive('setTemplateVar')->times(3)->andReturnUsing(static function (string $name, mixed $value) use (&$variables): void {
            $variables[$name] = $value;
        });
        $provider = new BorgDataNodeProgressProvider($storage, new StatusBarFactory());

        $provider->setTemplateVariables($game);

        self::assertSame($expected, $variables['BORG_DATA_NODE_PROGRESS_PERCENT']);
        self::assertSame($amount > 0, $variables['BORG_DATA_NODE_PROGRESS_VISIBLE']);
        self::assertInstanceOf(StatusBarInterface::class, $variables['BORG_DATA_NODE_PROGRESS_BAR']);
        $twig = new Environment(new FilesystemLoader(dirname(__DIR__, 7) . '/src'), ['strict_variables' => true]);
        $html = $twig->render('html/maindesk/borgDataNodeProgress.twig', $variables);
        if ($amount === 0) {
            self::assertSame('', trim($html));
            return;
        }
        self::assertStringContainsString('Globaler Borg-Datenknoten-Fortschritt', $html);
        self::assertStringContainsString('/assets/bars/balken.png', $html);
        self::assertStringContainsString('<div>' . $expected . '%</div>', $html);
        self::assertStringNotContainsString('&lt;img', $html);
        if ($expected === 0) {
            self::assertStringContainsString('background-color: #00aa00;height: 12px; width:0px;', $html);
        }
    }
}
