<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowModuleScreenBuildplan;

use request;
use Stu\Component\Ship\ShipModuleTypeEnum;
use Stu\Exception\SanityCheckException;
use Stu\Lib\ColonyStorageCommodityWrapper\ColonyStorageCommodityWrapper;
use Stu\Lib\ModuleScreen\ModuleScreenTab;
use Stu\Lib\ModuleScreen\ModuleScreenTabWrapper;
use Stu\Lib\ModuleScreen\ModuleSelector;
use Stu\Lib\ModuleScreen\ModuleSelectorSpecial;
use Stu\Lib\ModuleScreen\MyWrapper;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Orm\Repository\ShipBuildplanRepositoryInterface;
use Stu\Orm\Repository\ShipRumpRepositoryInterface;

final class ShowModuleScreenBuildplan implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_MODULE_SCREEN_BUILDPLAN';

    private ColonyLoaderInterface $colonyLoader;

    private ShipBuildplanRepositoryInterface $shipBuildplanRepository;

    private ShipRumpRepositoryInterface $shipRumpRepository;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ShipBuildplanRepositoryInterface $shipBuildplanRepository,
        ShipRumpRepositoryInterface $shipRumpRepository
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->shipBuildplanRepository = $shipBuildplanRepository;
        $this->shipRumpRepository = $shipRumpRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->byIdAndUser(
            request::indInt('id'),
            $userId
        );

        $plan = $this->shipBuildplanRepository->find(request::indInt('planid'),);
        if ($plan === null || $plan->getUserId() !== $userId) {
            throw new SanityCheckException(sprintf('This buildplan belongs to someone else'));
        }
        $rump = $plan->getRump();

        $moduleScreenTabs = new ModuleScreenTabWrapper;
        for ($i = 1; $i <= ShipModuleTypeEnum::STANDARD_MODULE_TYPE_COUNT; $i++) {
            $moduleScreenTabs->register(new ModuleScreenTab($i, $colony, $rump, $plan));
        }

        $myWrapper = new MyWrapper();
        $moduleSelectors = [];
        for ($i = 1; $i <= ShipModuleTypeEnum::STANDARD_MODULE_TYPE_COUNT; $i++) {
            if ($i == ShipModuleTypeEnum::MODULE_TYPE_SPECIAL) {
                $moduleSelectors[$i] = new ModuleSelectorSpecial(
                    $i,
                    $colony,
                    null,
                    $rump,
                    $userId,
                    $plan
                );
            } else {
                $moduleSelectors[$i] = new ModuleSelector(
                    $i,
                    $colony,
                    null,
                    $rump,
                    $userId,
                    $plan,
                );
            }
            $myWrapper->register($moduleSelectors[$i]);
        }

        $game->appendNavigationPart(
            'colony.php',
            _('Kolonien')
        );
        $game->appendNavigationPart(
            sprintf(
                '?%s=1&id=%s',
                ShowColony::VIEW_IDENTIFIER,
                $colony->getId()
            ),
            $colony->getName()
        );

        $game->appendNavigationPart(
            sprintf(
                '?id=%d&SHOW_MODULE_SCREEN_BUILDPLAN=1&planid=%d',
                $colony->getId(),
                $plan->getId()
            ),
            _('Schiffbau')
        );
        $game->setPagetitle(_('Schiffbau'));
        $game->setTemplateFile('html/modulescreen.xhtml');
        $game->setTemplateVar('COLONY', $colony);
        $game->setTemplateVar('RUMP', $rump);
        $game->setTemplateVar('PLAN', $plan);
        $game->setTemplateVar('MODULE_SCREEN_TABS', $moduleScreenTabs);
        $game->setTemplateVar('MODULE_SELECTORS', $moduleSelectors);
        $game->setTemplateVar('MY_WRAPPER', $myWrapper);
        $game->setTemplateVar('MODULE_SLOTS', range(1, ShipModuleTypeEnum::STANDARD_MODULE_TYPE_COUNT));
        $game->setTemplateVar('HAS_STORAGE', new ColonyStorageCommodityWrapper($colony->getStorage()));
    }
}
