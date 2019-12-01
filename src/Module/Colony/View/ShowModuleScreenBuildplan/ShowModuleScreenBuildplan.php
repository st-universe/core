<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowModuleScreenBuildplan;

use AccessViolation;
use Stu\Component\Ship\ShipModuleTypeEnum;
use Stu\Lib\ColonyStorageGoodWrapper\ColonyStorageGoodWrapper;
use Stu\Lib\ModuleScreen\ModuleScreenTab;
use Stu\Lib\ModuleScreen\ModuleScreenTabWrapper;
use Stu\Lib\ModuleScreen\ModuleSelector;
use Stu\Lib\ModuleScreen\ModuleSelectorSpecial;
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

    private ShowModuleScreenBuildplanRequestInterface $showModuleScreenBuildplanRequest;

    private ShipBuildplanRepositoryInterface $shipBuildplanRepository;

    private ShipRumpRepositoryInterface $shipRumpRepository;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ShowModuleScreenBuildplanRequestInterface $showModuleScreenBuildplanRequest,
        ShipBuildplanRepositoryInterface $shipBuildplanRepository,
        ShipRumpRepositoryInterface $shipRumpRepository
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->showModuleScreenBuildplanRequest = $showModuleScreenBuildplanRequest;
        $this->shipBuildplanRepository = $shipBuildplanRepository;
        $this->shipRumpRepository = $shipRumpRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->byIdAndUser(
            $this->showModuleScreenBuildplanRequest->getColonyId(),
            $userId
        );

        $plan = $this->shipBuildplanRepository->find($this->showModuleScreenBuildplanRequest->getBuildplanId());
        if ($plan === null || $plan->getUserId() !== $userId) {
            return;
        }
        $rump = $plan->getRump();

        if (!array_key_exists($rump->getId(), $this->shipRumpRepository->getBuildableByUser($userId))) {
            throw new AccessViolation();
        }

        $moduleScreenTabs = new ModuleScreenTabWrapper;
        for ($i = 1; $i <= ShipModuleTypeEnum::MODULE_TYPE_COUNT; $i++) {
            $moduleScreenTabs->register(new ModuleScreenTab($i, $colony, $rump, $plan));
        }

        $moduleSelectors = [];
        for ($i = 1; $i <= ShipModuleTypeEnum::MODULE_TYPE_COUNT; $i++) {
            if ($i == ShipModuleTypeEnum::MODULE_TYPE_SPECIAL) {
                $moduleSelectors[] = new ModuleSelectorSpecial(
                    $i,
                    $colony,
                    $rump,
                    $userId,
                    $plan
                );
            } else {
                $moduleSelectors[] = new ModuleSelector(
                    $i,
                    $colony,
                    $rump,
                    $userId,
                    $plan,
                );
            }
        }

        $game->appendNavigationPart(
            'colony.php',
            _('Kolonien')
        );
        $game->appendNavigationPart(
            sprintf('?%s=1&id=%s',
                ShowColony::VIEW_IDENTIFIER,
                $colony->getId()
            ),
            $colony->getName());

        $game->appendNavigationPart(
            sprintf(
                '?id=%d&SHOW_MODULE_SCREEN=1&planid=%d',
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
        $game->setTemplateVar('MODULE_SLOTS', range(1, ShipModuleTypeEnum::MODULE_TYPE_COUNT));
        $game->setTemplateVar('HAS_STORAGE', new ColonyStorageGoodWrapper($colony->getStorage()));
    }
}
