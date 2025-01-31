<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Battle\Weapon;

use Override;
use Stu\Lib\Damage\DamageWrapper;
use Stu\Lib\Information\InformationInterface;
use Stu\Module\Spacecraft\Lib\Destruction\SpacecraftDestructionCauseEnum;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\ShipInterface;

//TODO unit tests
final class TholianWebWeaponPhase extends AbstractWeaponPhase implements TholianWebWeaponPhaseInterface
{
    #[Override]
    public function damageCapturedShip(
        ShipInterface $ship,
        SpacecraftWrapperInterface $wrapper,
        InformationInterface $informations
    ): void {

        $ship = $wrapper->get();

        $informations->addInformation(sprintf(
            "Das Energienetz um die %s in Sektor %s ist implodiert",
            $ship->getName(),
            $ship->getSectorString()
        ));

        $damage_wrapper = new DamageWrapper(
            (int)ceil(random_int(65, 85) * $wrapper->get()->getMaxHull() / 100)
        );
        $damage_wrapper->setCrit(random_int(0, 3) === 0);
        $damage_wrapper->setIsPhaserDamage(true);

        $this->applyDamage->damage($damage_wrapper, $wrapper, $informations);

        $this->checkForSpacecraftDestruction(
            $ship,
            $wrapper,
            SpacecraftDestructionCauseEnum::THOLIAN_WEB_IMPLOSION,
            $informations
        );
    }
}
