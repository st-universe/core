<?php

declare(strict_types=1);

namespace Stu\Module\Station\Action\Scrapping;

use Override;
use request;
use RuntimeException;
use Stu\Component\Game\ModuleViewEnum;
use Stu\Component\Ship\ShipRumpEnum;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\ShipRemoverInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Entity\ModuleInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ConstructionProgressModuleRepositoryInterface;
use Stu\Orm\Repository\ConstructionProgressRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\ShipSystemRepositoryInterface;
use Stu\Orm\Repository\TradePostRepositoryInterface;

final class Scrapping implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_SCRAP';

    public function __construct(private ShipLoaderInterface $shipLoader, private ShipRepositoryInterface $shipRepository, private ShipSystemRepositoryInterface $shipSystemRepository, private ConstructionProgressRepositoryInterface $constructionProgressRepository, private ConstructionProgressModuleRepositoryInterface $constructionProgressModuleRepository, private ShipRemoverInterface $shipRemover, private TradePostRepositoryInterface $tradePostRepository)
    {
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
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

        $code = request::postString('scrapcode');
        if ($code === false) {
            return;
        }

        $trimmedCode = trim($code);
        if ($trimmedCode !== substr(md5($station->getName()), 0, 6)) {
            $game->addInformation(_('Der Bestätigungscode war fehlerhaft'));
            return;
        }

        if ($station->getRump()->getCategoryId() === ShipRumpEnum::SHIP_CATEGORY_CONSTRUCTION) {
            $game->setView(ModuleViewEnum::STATION);
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
        if ($progress === null) {
            throw new RuntimeException(sprintf('station with id %d does not have construction progess', $station->getId()));
        }
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
        $station->setMaxShield(0);

        //delete trade post stuff
        if ($station->getTradePost() !== null) {
            $this->tradePostRepository->delete($station->getTradePost());
            $station->setTradePost(null);
        }

        $this->shipRepository->save($station);
    }

    /**
     * @return array<int, array{0: ModuleInterface, 1: int}>
     */
    private function retrieveSomeIntactModules(ShipInterface $station): array
    {
        $intactModules = [];

        $plan = $station->getBuildplan();
        if ($plan === null) {
            return $intactModules;
        }

        $modules = $plan->getModules();

        foreach ($station->getSystems() as $system) {
            if (
                $system->getModule() !== null
                && $system->getStatus() === 100
            ) {
                $module = $system->getModule();

                if (!array_key_exists($module->getId(), $intactModules)) {
                    $buildplanModule = $modules->get($module->getId());

                    $count = $buildplanModule === null ? 1 : $buildplanModule->getModuleCount();

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

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
