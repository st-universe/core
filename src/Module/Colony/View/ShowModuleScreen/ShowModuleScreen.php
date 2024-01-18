<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowModuleScreen;

use Stu\Component\Ship\Crew\ShipCrewCalculatorInterface;
use Stu\Component\Ship\ShipModuleTypeEnum;
use Stu\Exception\AccessViolation;
use Stu\Lib\ColonyStorageCommodityWrapper\ColonyStorageCommodityWrapper;
use Stu\Lib\ModuleScreen\ModuleScreenTab;
use Stu\Lib\ModuleScreen\ModuleScreenTabWrapper;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\ShipRumpModuleLevelRepositoryInterface;
use Stu\Orm\Repository\ShipRumpRepositoryInterface;

final class ShowModuleScreen implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_MODULE_SCREEN';

    private ColonyLoaderInterface $colonyLoader;

    private ShowModuleScreenRequestInterface $showModuleScreenRequest;

    private ShipRumpRepositoryInterface $shipRumpRepository;

    private ColonyLibFactoryInterface $colonyLibFactory;

    private ShipCrewCalculatorInterface $shipCrewCalculator;

    private ShipRumpModuleLevelRepositoryInterface $shipRumpModuleLevelRepository;

    public function __construct(
        ShipRumpModuleLevelRepositoryInterface $shipRumpModuleLevelRepository,
        ColonyLoaderInterface $colonyLoader,
        ShowModuleScreenRequestInterface $showModuleScreenRequest,
        ShipRumpRepositoryInterface $shipRumpRepository,
        ShipCrewCalculatorInterface $shipCrewCalculator,
        ColonyLibFactoryInterface $colonyLibFactory
    ) {
        $this->shipRumpModuleLevelRepository = $shipRumpModuleLevelRepository;
        $this->colonyLoader = $colonyLoader;
        $this->showModuleScreenRequest = $showModuleScreenRequest;
        $this->shipRumpRepository = $shipRumpRepository;
        $this->colonyLibFactory = $colonyLibFactory;
        $this->shipCrewCalculator = $shipCrewCalculator;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $colony = $this->colonyLoader->loadWithOwnerValidation(
            $this->showModuleScreenRequest->getColonyId(),
            $userId,
            false
        );

        $rump = $this->shipRumpRepository->find($this->showModuleScreenRequest->getRumpId());

        if ($rump === null || !array_key_exists($rump->getId(), $this->shipRumpRepository->getBuildableByUser($userId))) {
            throw new AccessViolation();
        }

        $moduleScreenTabs = new ModuleScreenTabWrapper();
        for ($i = 1; $i <= ShipModuleTypeEnum::STANDARD_MODULE_TYPE_COUNT; $i++) {
            $moduleScreenTabs->register(new ModuleScreenTab($this->shipRumpModuleLevelRepository, $i, $colony, $rump));
        }


        $moduleSelectors = [];
        for ($i = 1; $i <= ShipModuleTypeEnum::STANDARD_MODULE_TYPE_COUNT; $i++) {

            if ($i == ShipModuleTypeEnum::MODULE_TYPE_SPECIAL) {
                $moduleSelectors[] = $this->colonyLibFactory->createModuleSelectorSpecial(
                    $i,
                    $colony,
                    null,
                    $rump,
                    $game->getUser()
                );
            } else {
                $moduleSelectors[] = $this->colonyLibFactory->createModuleSelector(
                    $i,
                    $colony,
                    null,
                    $rump,
                    $game->getUser()
                );
            }
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
                '?id=%d&SHOW_MODULE_SCREEN=1&rump=%d',
                $colony->getId(),
                $rump->getId()
            ),
            _('Schiffbau')
        );
        $moduleSlots = range(1, ShipModuleTypeEnum::STANDARD_MODULE_TYPE_COUNT);
        $game->setTemplateVar('MODULE_SLOTS', $moduleSlots);
        $game->setPagetitle(_('Schiffbau'));
        $game->setTemplateFile('html/modulescreen.xhtml');
        $game->setTemplateVar('COLONY', $colony);
        $game->setTemplateVar('RUMP', $rump);
        $game->setTemplateVar('MODULE_SCREEN_TABS', $moduleScreenTabs);
        $game->setTemplateVar('MODULE_SELECTORS', $moduleSelectors);
        $game->setTemplateVar('PLAN', false);
        $game->setTemplateVar('HAS_STORAGE', new ColonyStorageCommodityWrapper($colony->getStorage()));
        $game->setTemplateVar(
            'MAX_CREW_COUNT',
            $this->shipCrewCalculator->getMaxCrewCountByRump($rump)
        );
    }
}
