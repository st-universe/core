<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action;

use Override;
use Stu\Component\Spacecraft\Crew\SpacecraftCrewCalculatorInterface;
use Stu\Module\Admin\View\Scripts\ShowScripts;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Logging\LoggerEnum;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Orm\Entity\BuildplanModule;
use Stu\Orm\Entity\Module;
use Stu\Orm\Entity\SpacecraftBuildplan;
use Stu\Orm\Repository\SpacecraftBuildplanRepositoryInterface;

final class RecalculateBuildplanCrewUsage implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_FIX_BUILDPLAN_CREW';

    private LoggerUtilInterface $logger;

    public function __construct(
        private SpacecraftBuildplanRepositoryInterface $spacecraftBuildplanRepository,
        private SpacecraftCrewCalculatorInterface $shipCrewCalculator,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->logger = $loggerUtilFactory->getLoggerUtil();

        $this->logger->init('CREW', LoggerEnum::LEVEL_ERROR);
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowScripts::VIEW_IDENTIFIER);

        // only Admins can trigger ticks
        if (!$game->isAdmin()) {
            $game->addInformation(_('[b][color=#ff2626]Aktion nicht möglich, Spieler ist kein Admin![/color][/b]'));
            return;
        }

        $updatedBuildplans = 0;

        foreach ($this->spacecraftBuildplanRepository->findAll() as $buildplan) {
            if ($buildplan->getUser()->isNpc()) {
                continue;
            }

            $actualCrewUsage = $this->shipCrewCalculator->getCrewUsage(
                array_map(
                    fn(BuildplanModule $buildplanModule): Module => $buildplanModule->getModule(),
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

    private function updateBuildplanCrew(SpacecraftBuildplan $buildplan, int $crewUsage): void
    {
        $oldCrewUsage = $buildplan->getCrew();

        $buildplan->setCrew($crewUsage);
        $this->spacecraftBuildplanRepository->save($buildplan);

        $this->logger->log(sprintf(
            'updated buildplan "%s" (userId %d) from %d to %d crew',
            $buildplan->getName(),
            $buildplan->getUser()->getId(),
            $oldCrewUsage,
            $crewUsage
        ));
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
