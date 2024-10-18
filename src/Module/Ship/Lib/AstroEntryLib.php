<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use Override;
use RuntimeException;
use Stu\Component\Ship\AstronomicalMappingEnum;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Orm\Entity\AstronomicalEntryInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\AstroEntryRepositoryInterface;

final class AstroEntryLib implements AstroEntryLibInterface
{
    public function __construct(private AstroEntryRepositoryInterface $astroEntryRepository) {}

    #[Override]
    public function cancelAstroFinalizing(ShipWrapperInterface $wrapper): void
    {
        $ship = $wrapper->get();

        $ship->setState(ShipStateEnum::SHIP_STATE_NONE);
        $astroLab = $wrapper->getAstroLaboratorySystemData();
        if ($astroLab === null) {
            throw new RuntimeException('this should not happen');
        }
        $astroLab->setAstroStartTurn(null)->update();

        $entry = $this->getAstroEntryByShipLocation($ship, false);
        if ($entry === null) {
            throw new RuntimeException('this should not happen');
        }

        $entry->setState(AstronomicalMappingEnum::MEASURED);
        $entry->setAstroStartTurn(null);
        $this->astroEntryRepository->save($entry);
    }

    #[Override]
    public function finish(ShipWrapperInterface $wrapper): void
    {
        $ship = $wrapper->get();

        $ship->setState(ShipStateEnum::SHIP_STATE_NONE);

        $astroLab = $wrapper->getAstroLaboratorySystemData();
        if ($astroLab === null) {
            throw new RuntimeException('this should not happen');
        }
        $astroLab->setAstroStartTurn(null)->update();

        $entry = $this->getAstroEntryByShipLocation($ship, false);
        if ($entry === null) {
            throw new RuntimeException('this should not happen');
        }

        $entry->setState(AstronomicalMappingEnum::DONE);
        $entry->setAstroStartTurn(null);
        $this->astroEntryRepository->save($entry);
    }

    #[Override]
    public function getAstroEntryByShipLocation(ShipInterface $ship, bool $showOverSystem = true): ?AstronomicalEntryInterface
    {
        $user = $ship->getUser();
        $system = $ship->getSystem();

        if ($system !== null) {
            return $this->getAstroEntryForUser($system, $user);
        }

        $overSystem = $ship->isOverSystem();
        if ($overSystem !== null && $showOverSystem) {
            return $this->getAstroEntryForUser($overSystem, $user);
        }

        $mapRegion = $ship->getMapRegion();
        if ($mapRegion !== null) {
            return $this->getAstroEntryForUser($mapRegion, $user);
        }

        return null;
    }

    private function getAstroEntryForUser(EntityWithAstroEntryInterface $entity, UserInterface $user): ?AstronomicalEntryInterface
    {
        return $entity->getAstronomicalEntries()->get($user->getId());
    }
}
