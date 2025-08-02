<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Battle\Weapon;

use Override;
use Stu\Lib\Damage\DamageWrapper;
use Stu\Lib\Information\InformationInterface;
use Stu\Module\Spacecraft\Lib\Destruction\SpacecraftDestructionCauseEnum;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Ship;

//TODO unit tests
final class TholianWebWeaponPhase extends AbstractWeaponPhase implements TholianWebWeaponPhaseInterface
{
    #[Override]
    public function damageCapturedShip(
        Ship $ship,
        SpacecraftWrapperInterface $targetWrapper,
        InformationInterface $informations
    ): void {

        $capturedSpacecraft = $targetWrapper->get();

        $informations->addInformation(sprintf(
            "Das Energienetz um die %s in Sektor %s ist implodiert",
            $ship->getName(),
            $ship->getSectorString()
        ));

        $damage_wrapper = new DamageWrapper(
            (int)ceil(random_int(65, 85) * $capturedSpacecraft->getMaxHull() / 100)
        );
        $damage_wrapper->setCrit(random_int(0, 3) === 0);
        $damage_wrapper->setIsPhaserDamage(true);

        $this->applyDamage->damage($damage_wrapper, $targetWrapper, $informations);

        $this->checkForSpacecraftDestruction(
            $ship,
            $targetWrapper,
            SpacecraftDestructionCauseEnum::THOLIAN_WEB_IMPLOSION,
            $informations
        );
    }
}
