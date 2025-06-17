<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib\Damage;

use Override;
use RuntimeException;
use Stu\Lib\Damage\DamageModeEnum;
use Stu\Lib\Damage\DamageWrapper;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\PlanetFieldInterface;

final class ApplyBuildingDamage implements ApplyBuildingDamageInterface
{
    public function __construct(
        private ColonyLibFactoryInterface $colonyLibFactory
    ) {}

    #[Override]
    public function damageBuilding(
        DamageWrapper $damageWrapper,
        PlanetFieldInterface $target,
        bool $isOrbitField
    ): InformationWrapper {
        $informations = new InformationWrapper();

        $colony = $target->getHost();
        if (!$colony instanceof ColonyInterface) {
            throw new RuntimeException('this should not happen');
        }

        if (!$isOrbitField && $this->colonyLibFactory->createColonyShieldingManager($colony)->isShieldingEnabled()) {
            $damage = (int) $damageWrapper->getDamageRelative($colony, DamageModeEnum::SHIELDS);
            if ($damage > $colony->getShields()) {
                $informations->addInformation("- Schildschaden: " . $colony->getShields());
                $informations->addInformation("-- Schilde brechen zusammen!");

                $colony->setShields(0);
            } else {
                $colony->setShields($colony->getShields() - $damage);
                $informations->addInformation("- Schildschaden: " . $damage . " - Status: " . $colony->getShields());
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
