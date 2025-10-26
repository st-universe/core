<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use RuntimeException;
use Stu\Component\Ship\AstronomicalMappingStateEnum;
use Stu\Component\Spacecraft\SpacecraftStateEnum;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\AstronomicalEntry;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\AstroEntryRepositoryInterface;

final class AstroEntryLib implements AstroEntryLibInterface
{
    public function __construct(private AstroEntryRepositoryInterface $astroEntryRepository) {}

    #[\Override]
    public function cancelAstroFinalizing(SpacecraftWrapperInterface $wrapper): void
    {
        if (!$wrapper instanceof ShipWrapperInterface) {
            return;
        }

        $ship = $wrapper->get();

        $ship->getCondition()->setState(SpacecraftStateEnum::NONE);
        $astroLab = $wrapper->getAstroLaboratorySystemData();
        if ($astroLab === null) {
            throw new RuntimeException('this should not happen');
        }
        $astroLab->setAstroStartTurn(null)->update();

        $entry = $this->getAstroEntryByShipLocation($ship, false);
        if ($entry === null) {
            throw new RuntimeException('this should not happen');
        }

        $entry->setState(AstronomicalMappingStateEnum::MEASURED);
        $entry->setAstroStartTurn(null);
        $this->astroEntryRepository->save($entry);
    }

    #[\Override]
    public function finish(ShipWrapperInterface $wrapper): void
    {
        $ship = $wrapper->get();

        $ship->getCondition()->setState(SpacecraftStateEnum::NONE);

        $astroLab = $wrapper->getAstroLaboratorySystemData();
        if ($astroLab === null) {
            throw new RuntimeException('this should not happen');
        }
        $astroLab->setAstroStartTurn(null)->update();

        $entry = $this->getAstroEntryByShipLocation($ship, false);
        if ($entry === null) {
            throw new RuntimeException('this should not happen');
        }

        $entry->setState(AstronomicalMappingStateEnum::DONE);
        $entry->setAstroStartTurn(null);
        $this->astroEntryRepository->save($entry);
    }

    #[\Override]
    public function getAstroEntryByShipLocation(Spacecraft $spacecraft, bool $showOverSystem = true): ?AstronomicalEntry
    {
        $user = $spacecraft->getUser();
        $system = $spacecraft->getSystem();

        if ($system !== null) {
            return $this->getAstroEntryForUser($system, $user);
        }

        $overSystem = $spacecraft->isOverSystem();
        if ($overSystem !== null && $showOverSystem) {
            return $this->getAstroEntryForUser($overSystem, $user);
        }

        $mapRegion = $spacecraft->getMapRegion();
        if ($mapRegion !== null) {
            return $this->getAstroEntryForUser($mapRegion, $user);
        }

        return null;
    }

    private function getAstroEntryForUser(EntityWithAstroEntryInterface $entity, User $user): ?AstronomicalEntry
    {
        return $entity->getAstronomicalEntries()->get($user->getId());
    }
}
