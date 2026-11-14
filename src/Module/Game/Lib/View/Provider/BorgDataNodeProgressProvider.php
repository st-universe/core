<?php

declare(strict_types=1);

namespace Stu\Module\Game\Lib\View\Provider;

use Override;
use Stu\Module\Commodity\CommodityTypeConstants;
use Stu\Module\Control\Component\View\ViewControllerContext;
use Stu\Module\Template\StatusBarColorEnum;
use Stu\Module\Template\StatusBarFactoryInterface;
use Stu\Orm\Repository\StorageRepositoryInterface;

final class BorgDataNodeProgressProvider implements ViewComponentProviderInterface
{
    private const int TARGET_AMOUNT = 200;

    public function __construct(private StorageRepositoryInterface $storage, private StatusBarFactoryInterface $bars) {}

    #[Override]
    public function setTemplateVariables(ViewControllerContext $game): void
    {
        $amount = $this->storage->getCommodityAmountInPlayerStorage(CommodityTypeConstants::COMMODITY_BORG_DATA_NODE);
        $percent = max(0, min(100, (int) floor($amount * 100 / self::TARGET_AMOUNT)));
        $bar = $this->bars->createStatusBar()->setColor(StatusBarColorEnum::GREEN)
            ->setLabel('Globaler Borg-Datenknoten-Fortschritt')->setMaxValue(100)->setValue($percent)->setSizeModifier(6);
        $game->setTemplateVar('BORG_DATA_NODE_PROGRESS_VISIBLE', $amount > 0);
        $game->setTemplateVar('BORG_DATA_NODE_PROGRESS_BAR', $bar);
        $game->setTemplateVar('BORG_DATA_NODE_PROGRESS_PERCENT', $percent);
    }
}
