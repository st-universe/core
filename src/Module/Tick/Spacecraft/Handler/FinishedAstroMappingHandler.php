<?php

namespace Stu\Module\Tick\Spacecraft\Handler;

use Override;
use Stu\Component\Ship\AstronomicalMappingEnum;
use Stu\Component\Spacecraft\SpacecraftStateEnum;
use Stu\Lib\Information\InformationInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Database\Lib\CreateDatabaseEntryInterface;
use Stu\Module\Ship\Lib\AstroEntryLibInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\DatabaseEntryInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\DatabaseUserRepositoryInterface;

class FinishedAstroMappingHandler implements SpacecraftTickHandlerInterface
{
    public function __construct(
        private AstroEntryLibInterface $astroEntryLib,
        private DatabaseUserRepositoryInterface $databaseUserRepository,
        private CreateDatabaseEntryInterface $createDatabaseEntry,
        private GameControllerInterface $game
    ) {}

    #[Override]
    public function handleSpacecraftTick(
        SpacecraftWrapperInterface $wrapper,
        InformationInterface $information
    ): void {

        if (!$wrapper instanceof ShipWrapperInterface) {
            return;
        }

        $ship = $wrapper->get();

        /** @var null|DatabaseEntryInterface $databaseEntry */
        /** @var string $message */
        [$message, $databaseEntry] = $this->getDatabaseEntryForShipLocation($ship);

        $astroLab = $wrapper->getAstroLaboratorySystemData();

        if (
            $ship->getState() === SpacecraftStateEnum::ASTRO_FINALIZING
            && $databaseEntry !== null
            && $astroLab !== null
            && $this->game->getCurrentRound()->getTurn() >= ($astroLab->getAstroStartTurn() + AstronomicalMappingEnum::TURNS_TO_FINISH)
        ) {

            $this->astroEntryLib->finish($wrapper);

            $information->addInformationf(
                'Die Kartographierung %s wurde vollendet',
                $message
            );

            $userId = $ship->getUser()->getId();
            $databaseEntryId = $databaseEntry->getId();

            if (!$this->databaseUserRepository->exists($userId, $databaseEntryId)) {

                $entry = $this->createDatabaseEntry->createDatabaseEntryForUser($ship->getUser(), $databaseEntryId);

                if ($entry !== null) {
                    $information->addInformationf(
                        'Neuer Datenbankeintrag: %s (+%d Punkte)',
                        $entry->getDescription(),
                        $entry->getCategory()->getPoints()
                    );
                }
            }
        }
    }

    /**
     * @return array{0: string|null, 1: DatabaseEntryInterface|null}
     */
    private function getDatabaseEntryForShipLocation(ShipInterface $ship): array
    {
        $system = $ship->getSystem();
        if ($system !== null) {
            return [
                'des Systems ' . $system->getName(),
                $system->getDatabaseEntry()
            ];
        }

        $mapRegion = $ship->getMapRegion();
        if ($mapRegion !== null) {
            return [
                'der Region ' . $mapRegion->getDescription(),
                $mapRegion->getDatabaseEntry()
            ];
        }

        return [null, null];
    }
}
