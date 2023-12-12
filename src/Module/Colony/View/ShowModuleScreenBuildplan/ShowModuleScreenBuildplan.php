<?php

declare(strict_types=1);

namespace Stu\Module\Colony\View\ShowModuleScreenBuildplan;

use request;
use Stu\Component\Ship\Crew\ShipCrewCalculatorInterface;
use Stu\Component\Ship\ShipModuleTypeEnum;
use Stu\Exception\SanityCheckException;
use Stu\Lib\ColonyStorageCommodityWrapper\ColonyStorageCommodityWrapper;
use Stu\Lib\ModuleScreen\ModuleScreenTab;
use Stu\Lib\ModuleScreen\ModuleScreenTabWrapper;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\ShipBuildplanRepositoryInterface;
use Stu\Orm\Repository\ShipRumpModuleLevelRepositoryInterface;

final class ShowModuleScreenBuildplan implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_MODULE_SCREEN_BUILDPLAN';

    private ColonyLoaderInterface $colonyLoader;

    private ShipBuildplanRepositoryInterface $shipBuildplanRepository;

    private ColonyLibFactoryInterface $colonyLibFactory;

    private ShipCrewCalculatorInterface $shipCrewCalculator;

    private ShipRumpModuleLevelRepositoryInterface $shipRumpModuleLevelRepository;

    public function __construct(
        ShipRumpModuleLevelRepositoryInterface $shipRumpModuleLevelRepository,
        ColonyLoaderInterface $colonyLoader,
        ColonyLibFactoryInterface $colonyLibFactory,
        ShipCrewCalculatorInterface $shipCrewCalculator,
        ShipBuildplanRepositoryInterface $shipBuildplanRepository
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->shipBuildplanRepository = $shipBuildplanRepository;
        $this->colonyLibFactory = $colonyLibFactory;
        $this->shipCrewCalculator = $shipCrewCalculator;
        $this->shipRumpModuleLevelRepository = $shipRumpModuleLevelRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $userId = $user->getId();

        $colony = $this->colonyLoader->byIdAndUser(
            request::indInt('id'),
            $userId,
            false
        );

        $plan = $this->shipBuildplanRepository->find(request::indInt('planid'));
        if ($plan === null || $plan->getUserId() !== $userId) {
            throw new SanityCheckException('This buildplan belongs to someone else', null, self::VIEW_IDENTIFIER);
        }
        $rump = $plan->getRump();

        $moduleScreenTabs = new ModuleScreenTabWrapper();
        for ($i = 1; $i <= ShipModuleTypeEnum::STANDARD_MODULE_TYPE_COUNT; $i++) {

            $moduleScreenTabs->register(new ModuleScreenTab($this->shipRumpModuleLevelRepository, $i, $colony, $rump, $plan));
        }


        $moduleSelectors = [];
        for ($i = 1; $i <= ShipModuleTypeEnum::STANDARD_MODULE_TYPE_COUNT; $i++) {

            if ($i == ShipModuleTypeEnum::MODULE_TYPE_SPECIAL) {
                $moduleSelectors[$i] = $this->colonyLibFactory->createModuleSelectorSpecial(
                    $i,
                    $colony,
                    null,
                    $rump,
                    $user,
                    $plan
                );
            } else {
                $moduleSelectors[$i] = $this->colonyLibFactory->createModuleSelector(
                    $i,
                    $colony,
                    null,
                    $rump,
                    $user,
                    $plan,
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
                '?id=%d&SHOW_MODULE_SCREEN_BUILDPLAN=1&planid=%d',
                $colony->getId(),
                $plan->getId()
            ),
            _('Schiffbau')
        );
        $moduleSlots = range(1, ShipModuleTypeEnum::STANDARD_MODULE_TYPE_COUNT);
        $game->setTemplateVar('MODULE_SLOTS', $moduleSlots);
        $game->setPagetitle(_('Schiffbau'));
        $game->setTemplateFile('html/modulescreen.xhtml');
        $game->setTemplateVar('COLONY', $colony);
        $game->setTemplateVar('RUMP', $rump);
        $game->setTemplateVar('PLAN', $plan);
        $game->setTemplateVar('MODULE_SCREEN_TABS', $moduleScreenTabs);
        $game->setTemplateVar('MODULE_SELECTORS', $moduleSelectors);
        $game->setTemplateVar('HAS_STORAGE', new ColonyStorageCommodityWrapper($colony->getStorage()));
        $game->setTemplateVar(
            'MAX_CREW_COUNT',
            $this->shipCrewCalculator->getMaxCrewCountByRump($rump)
        );
    }
}
