<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action;

use Stu\Orm\Entity\ModuleInterface;
use Stu\Component\Ship\Crew\ShipCrewCalculatorInterface;
use Stu\Module\Admin\View\Scripts\ShowScripts;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Logging\LoggerEnum;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Orm\Entity\BuildplanModuleInterface;
use Stu\Orm\Entity\ShipBuildplanInterface;
use Stu\Orm\Repository\ShipBuildplanRepositoryInterface;

final class RecalculateBuildplanCrewUsage implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_FIX_BUILDPLAN_CREW';

    private ShipBuildplanRepositoryInterface $shipBuildplanRepository;

    private ShipCrewCalculatorInterface $shipCrewCalculator;

    private LoggerUtilInterface $logger;

    public function __construct(
        ShipBuildplanRepositoryInterface $shipBuildplanRepository,
        ShipCrewCalculatorInterface $shipCrewCalculator,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->shipBuildplanRepository = $shipBuildplanRepository;
        $this->shipCrewCalculator = $shipCrewCalculator;
        $this->logger = $loggerUtilFactory->getLoggerUtil();

        $this->logger->init('CREW', LoggerEnum::LEVEL_ERROR);
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowScripts::VIEW_IDENTIFIER);

        // only Admins can trigger ticks
        if (!$game->isAdmin()) {
            $game->addInformation(_('[b][color=#ff2626]Aktion nicht möglich, Spieler ist kein Admin![/color][/b]'));
            return;
        }

        $updatedBuildplans = 0;

        foreach ($this->shipBuildplanRepository->findAll() as $buildplan) {
            if ($buildplan->getUser()->isNpc()) {
                continue;
            }

            $actualCrewUsage = $this->shipCrewCalculator->getCrewUsage(
                array_map(
                    fn (BuildplanModuleInterface $buildplanModule): ModuleInterface => $buildplanModule->getModule(),
                    $buildplan->getModules()->toArray()
                ),
                $buildplan->getRump(),
                $buildplan->getUser()
            );

            if ($actualCrewUsage !== $buildplan->getCrew()) {
                $this->updateBuildplanCrew($buildplan, $actualCrewUsage);
                $updatedBuildplans++;
            }
        }

        $game->addInformationf("Es wurden %d Baupläne aktualisiert", $updatedBuildplans);
    }

    private function updateBuildplanCrew(ShipBuildplanInterface $buildplan, int $crewUsage): void
    {
        $oldCrewUsage = $buildplan->getCrew();

        $buildplan->setCrew($crewUsage);
        $this->shipBuildplanRepository->save($buildplan);

        $this->logger->log(sprintf(
            'updated buildplan "%s" (userId %d) from %d to %d crew',
            $buildplan->getName(),
            $buildplan->getUser()->getId(),
            $oldCrewUsage,
            $crewUsage
        ));
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
