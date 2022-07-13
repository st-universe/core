<?php

declare(strict_types=1);

namespace Stu\Module\Station\Action\Scrapping;

use request;
use Stu\Component\Ship\ShipRumpEnum;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Logging\LoggerEnum;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\ShipRemoverInterface;
use Stu\Module\Ship\View\Overview\Overview;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ConstructionProgressModuleRepositoryInterface;
use Stu\Orm\Repository\ConstructionProgressRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\ShipSystemRepositoryInterface;

final class Scrapping implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_SCRAP';

    private ShipLoaderInterface $shipLoader;

    private ShipRepositoryInterface $shipRepository;

    private ShipSystemRepositoryInterface $shipSystemRepository;

    private ConstructionProgressRepositoryInterface $constructionProgressRepository;

    private ConstructionProgressModuleRepositoryInterface $constructionProgressModuleRepository;

    private ShipRemoverInterface $shipRemover;

    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        ShipRepositoryInterface $shipRepository,
        ShipSystemRepositoryInterface $shipSystemRepository,
        ConstructionProgressRepositoryInterface $constructionProgressRepository,
        ConstructionProgressModuleRepositoryInterface $constructionProgressModuleRepository,
        ShipRemoverInterface $shipRemover,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->shipLoader = $shipLoader;
        $this->shipRepository = $shipRepository;
        $this->shipSystemRepository = $shipSystemRepository;
        $this->constructionProgressRepository = $constructionProgressRepository;
        $this->constructionProgressModuleRepository = $constructionProgressModuleRepository;
        $this->shipRemover = $shipRemover;
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

    public function handle(GameControllerInterface $game): void
    {
        $this->loggerUtil->init('stu', LoggerEnum::LEVEL_ERROR);

        $userId = $game->getUser()->getId();

        $station = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );

        if (!$station->isBase()) {
            return;
        }

        if ($station->getState() === ShipStateEnum::SHIP_STATE_UNDER_SCRAPPING) {
            return;
        }

        $code = trim(request::postString('scrapcode'));

        if ($code !== substr(md5($station->getName()), 0, 6)) {
            $game->addInformation(_('Der Bestätigungscode war fehlerhaft'));
            return;
        }

        if ($station->getRump()->getRoleId() === ShipRumpEnum::SHIP_ROLE_CONSTRUCTION) {
            $game->setView(Overview::VIEW_IDENTIFIER);
            $this->shipRemover->remove($station);
            $game->addInformation(_('Konstrukt wurde entfernt'));
            return;
        }

        if ($station->getCrewCount() > 0) {
            $game->addInformation(_('Zum Demontieren muss die Station unbemannt sein'));
            return;
        }

        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $this->startScrapping($station);

        $game->addInformation(_('Das Demontieren hat begonnen'));
    }

    private function startScrapping(ShipInterface $station): void
    {
        $station->setState(ShipStateEnum::SHIP_STATE_UNDER_SCRAPPING);

        //setup scrapping progress
        $progress = $this->constructionProgressRepository->getByShip($station->getId());
        $progress->setRemainingTicks((int)ceil($station->getRump()->getBuildtime() / 2));

        $this->constructionProgressRepository->save($progress);

        $this->constructionProgressModuleRepository->truncateByProgress($progress->getId());

        $intactModules = $this->retrieveSomeIntactModules($station);

        foreach ($intactModules as $mod) {
            [$module, $count] = $mod;

            for ($i = 0; $i < $count; $i++) {
                $progressModule = $this->constructionProgressModuleRepository->prototype();
                $progressModule->setConstructionProgress($progress);
                $progressModule->setModule($module);

                $this->constructionProgressModuleRepository->save($progressModule);
            }
        }

        //remove ship systems
        $this->shipSystemRepository->truncateByShip($station->getId());
        $station->getSystems()->clear();

        //clear system values
        $station->setShield(0);
        $station->setEps(0);
        $station->setMaxEBatt(0);
        $station->setMaxShield(0);

        $this->shipRepository->save($station);
    }

    private function retrieveSomeIntactModules(ShipInterface $station): array
    {
        $intactModules = [];

        $plan = $station->getBuildplan();
        $modules = $plan->getModules();

        foreach ($station->getSystems() as $system) {
            if (
                $system->getModule() !== null
                && $system->getStatus() == 100
            ) {
                $module = $system->getModule();

                if (!array_key_exists($module->getId(), $intactModules)) {
                    $buildplanModule = $modules->get($module->getId());

                    if ($buildplanModule === null) {
                        $this->loggerUtil->log(sprintf(
                            'systemName: %s, moduleId: %d, moduleName: %s',
                            $system->getName(),
                            $module->getId(),
                            $module->getName()
                        ));
                    }

                    $count = $buildplanModule->getModuleCount();

                    $intactModules[$module->getId()] = [$module, $count];
                }
            }
        }

        //retrieve 50% of all intact modules
        $recycleCount = (int) ceil(count($intactModules) / 2);
        for ($i = 1; $i <= $recycleCount; $i++) {
            [$module, $count] = $intactModules[array_rand($intactModules)];
            unset($intactModules[$module->getId()]);
        }

        return $intactModules;
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
