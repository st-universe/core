<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib\Damage;

use RuntimeException;
use Stu\Lib\Damage\DamageModeEnum;
use Stu\Lib\Damage\DamageWrapper;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\PlanetField;

final class ApplyBuildingDamage implements ApplyBuildingDamageInterface
{
    public function __construct(
        private ColonyLibFactoryInterface $colonyLibFactory
    ) {}

    #[\Override]
    public function damageBuilding(
        DamageWrapper $damageWrapper,
        PlanetField $target,
        bool $isOrbitField
    ): InformationWrapper {
        $informations = new InformationWrapper();

        $colony = $target->getHost();
        if (!$colony instanceof Colony) {
            throw new RuntimeException('this should not happen');
        }

        $changeable = $colony->getChangeable();

        if (!$isOrbitField && $this->colonyLibFactory->createColonyShieldingManager($colony)->isShieldingEnabled()) {
            $damage = (int) $damageWrapper->getDamageRelative($colony, DamageModeEnum::SHIELDS);
            if ($damage > $changeable->getShields()) {
                $informations->addInformation("- Schildschaden: " . $changeable->getShields());
                $informations->addInformation("-- Schilde brechen zusammen!");

                $changeable->setShields(0);
            } else {
                $changeable->setShields($changeable->getShields() - $damage);
                $informations->addInformation("- Schildschaden: " . $damage . " - Status: " . $changeable->getShields());
            }
        }
        if ($damageWrapper->getNetDamage() <= 0) {
            return $informations;
        }
        $damage = (int) $damageWrapper->getDamageRelative($colony, DamageModeEnum::HULL);
        if ($target->getIntegrity() > $damage) {
            $target->setIntegrity($target->getIntegrity() - $damage);
            $informations->addInformation("- Gebäudeschaden: " . $damage . " - Status: " . $target->getIntegrity());

            return $informations;
        }
        $informations->addInformation("- Gebäudeschaden: " . $damage);
        $informations->addInformation("-- Das Gebäude wurde zerstört!");
        $target->setIntegrity(0);

        return $informations;
    }
}
