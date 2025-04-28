<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Damage;

use Override;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemModeEnum;
use Stu\Lib\Damage\DamageWrapper;
use Stu\Lib\Information\InformationInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\SpacecraftSystemInterface;

//TODO unit tests and move to Lib/Damage
final class SystemDamage implements SystemDamageInterface
{
    public function __construct(
        private SpacecraftSystemManagerInterface $spacecraftSystemManager
    ) {}

    #[Override]
    public function checkForDamagedShipSystems(
        SpacecraftWrapperInterface $wrapper,
        DamageWrapper $damageWrapper,
        int $huelleVorher,
        InformationInterface $informations
    ): bool {
        $ship = $wrapper->get();
        $systemsToDamage = ceil($huelleVorher * 6 / $ship->getMaxHull()) -
            ceil($ship->getHull() * 6 / $ship->getMaxHull());

        if ($systemsToDamage == 0) {
            return false;
        }

        for ($i = 1; $i <= $systemsToDamage; $i++) {
            $this->damageRandomShipSystem($wrapper, $damageWrapper, $informations);
        }

        return true;
    }

    #[Override]
    public function destroyRandomShipSystem(SpacecraftWrapperInterface $wrapper, DamageWrapper $damageWrapper): ?string
    {
        $healthySystems = $this->getHealthySystems($wrapper, $damageWrapper);
        shuffle($healthySystems);

        if ($healthySystems === []) {
            return null;
        }
        $system = $healthySystems[0];
        $system->setStatus(0);
        $system->setMode(SpacecraftSystemModeEnum::MODE_OFF);
        $this->spacecraftSystemManager->handleDestroyedSystem($wrapper, $healthySystems[0]->getSystemType());

        return $healthySystems[0]->getSystemType()->getDescription();
    }

    #[Override]
    public function damageRandomShipSystem(
        SpacecraftWrapperInterface $wrapper,
        DamageWrapper $damageWrapper,
        InformationInterface $informations,
        ?int $percent = null
    ): void {
        $healthySystems = $this->getHealthySystems($wrapper, $damageWrapper);
        shuffle($healthySystems);

        if ($healthySystems !== []) {
            $system = $healthySystems[0];

            $this->damageShipSystem($wrapper, $system, $percent ?? random_int(1, 70), $informations);
        }
    }

    /** @return array<SpacecraftSystemInterface>  */
    private function getHealthySystems(SpacecraftWrapperInterface $wrapper, DamageWrapper $damageWrapper): array
    {
        return $wrapper->get()->getSystems()
            ->filter(fn(SpacecraftSystemInterface $system): bool => $damageWrapper->canDamageSystem($system->getSystemType()))
            ->filter(fn(SpacecraftSystemInterface $system): bool => $system->getStatus() > 0)
            ->filter(fn(SpacecraftSystemInterface $system): bool => $system->getSystemType()->canBeDamaged())
            ->toArray();
    }

    #[Override]
    public function damageShipSystem(
        SpacecraftWrapperInterface $wrapper,
        SpacecraftSystemInterface $system,
        int $dmg,
        InformationInterface $informations
    ): bool {
        $status = $system->getStatus();
        $systemName = $system->getSystemType()->getDescription();

        if ($status > $dmg) {
            $system->setStatus($status - $dmg);
            $this->spacecraftSystemManager->handleDamagedSystem($wrapper, $system->getSystemType());
            $informations->addInformation("- Folgendes System wurde beschädigt: " . $systemName);

            return false;
        } else {
            $system->setStatus(0);
            $system->setMode(SpacecraftSystemModeEnum::MODE_OFF);
            $this->spacecraftSystemManager->handleDestroyedSystem($wrapper, $system->getSystemType());
            $informations->addInformation("- Der Schaden zerstört folgendes System: " . $systemName);

            return true;
        }
    }
}
