<?php

declare(strict_types=1);

namespace Stu\Module\Station\Action\Scrapping;

use request;
use Stu\Component\Ship\ShipRumpEnum;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
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

    public function __construct(
        ShipLoaderInterface $shipLoader,
        ShipRepositoryInterface $shipRepository,
        ShipSystemRepositoryInterface $shipSystemRepository,
        ConstructionProgressRepositoryInterface $constructionProgressRepository,
        ConstructionProgressModuleRepositoryInterface $constructionProgressModuleRepository
    ) {
        $this->shipLoader = $shipLoader;
        $this->shipRepository = $shipRepository;
        $this->shipSystemRepository = $shipSystemRepository;
        $this->constructionProgressRepository = $constructionProgressRepository;
        $this->constructionProgressModuleRepository = $constructionProgressModuleRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );

        if (!$ship->isBase()) {
            return;
        }

        if ($ship->getState() === ShipStateEnum::SHIP_STATE_UNDER_CONSTRUCTION) {
            return;
        }

        if ($ship->getState() === ShipStateEnum::SHIP_STATE_UNDER_SCRAPPING) {
            return;
        }

        if ($ship->getRump()->getRoleId() === ShipRumpEnum::SHIP_ROLE_CONSTRUCTION) {
            $game->addInformation(_('Konstrukte können nicht abgewrackt werden'));
            return;
        }

        if ($ship->getCrewCount() > 0) {
            $game->addInformation(_('Zum Abwracken muss die Station unbemannt sein'));
            return;
        }

        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $code = trim(request::postString('scrapcode'));

        if ($code !== substr(md5($ship->getName()), 0, 6)) {
            $game->addInformation(_('Der Abwrackcode war fehlerhaft'));
            return;
        }

        $this->startScrapping($ship);

        $game->addInformation(_('Das Abwracken hat begonnen'));
    }

    private function startScrapping(ShipInterface $station): void
    {
        $station->setState(ShipStateEnum::SHIP_STATE_UNDER_SCRAPPING);

        //remove ship systems
        $this->shipSystemRepository->truncateByShip($station->getId());
        $station->getSystems()->clear();

        //setup scrapping progress
        $progress = $this->constructionProgressRepository->getByShip($station->getId());
        $progress->setRemainingTicks($station->getRump()->getBuildtime());

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
                    $count = $buildplanModule->getModuleCount();

                    $intactModules[$module->getId()] = [$module, $count];
                }
            }
        }

        //retrieve 50% of all intact modules
        $recycleCount = (int) ceil(count($intactModules) / 2);
        for ($i = 1; $i <= $recycleCount; $i++) {
            $module = $intactModules[array_rand($intactModules)];
            unset($intactModules[$module->getId()]);
        }

        return $intactModules;
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
