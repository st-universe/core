<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Damage;

use Override;
use RuntimeException;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Lib\Damage\DamageModeEnum;
use Stu\Lib\Damage\DamageWrapper;
use Stu\Lib\Information\InformationInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

//TODO unit tests
final class ApplyDamage implements ApplyDamageInterface
{
    public function __construct(
        private SpacecraftSystemManagerInterface $spacecraftSystemManager,
        private SystemDamageInterface $systemDamage
    ) {}

    #[Override]
    public function damage(
        DamageWrapper $damageWrapper,
        SpacecraftWrapperInterface $wrapper,
        InformationInterface $informations
    ): void {

        if ($damageWrapper->getNetDamage() <= 0) {
            throw new RuntimeException('this should not happen');
        }

        $spacecraft = $wrapper->get();

        if ($spacecraft->isShielded()) {

            if ($damageWrapper->isShieldPenetration()) {
                $informations->addInformationf('- Projektil hat Schilde durchdrungen!');
            } else {
                $this->damageShields($wrapper, $damageWrapper, $informations);
            }
        }
        if ($damageWrapper->getNetDamage() <= 0) {
            return;
        }

        $disablemessage = false;
        $damage = (int) $damageWrapper->getDamageRelative($spacecraft, DamageModeEnum::HULL);
        if ($spacecraft->getSystemState(SpacecraftSystemTypeEnum::RPG_MODULE) && $spacecraft->getHull() - $damage < round($spacecraft->getMaxHull() / 100 * 10)) {
            $damage = (int) round($spacecraft->getHull() - $spacecraft->getMaxHull() / 100 * 10);
            $disablemessage = _('-- Das Schiff wurde kampfunfähig gemacht');
            $spacecraft->setDisabled(true);
        }
        if ($spacecraft->getHull() > $damage) {
            $this->damageHull($wrapper, $damageWrapper, $damage, $informations);

            if ($disablemessage) {
                $informations->addInformation($disablemessage);
            }
        } else {
            $informations->addInformation("- Hüllenschaden: " . $damage);
            $informations->addInformation("-- Das Schiff wurde zerstört!");
            $spacecraft->setIsDestroyed(true);
        }
    }

    private function damageShields(SpacecraftWrapperInterface $wrapper, DamageWrapper $damageWrapper, InformationInterface $informations): void
    {
        $spacecraft = $wrapper->get();

        $damage = (int) $damageWrapper->getDamageRelative($spacecraft, DamageModeEnum::SHIELDS);
        if ($damage >= $spacecraft->getShield()) {
            $informations->addInformation("- Schildschaden: " . $spacecraft->getShield());
            $informations->addInformation("-- Schilde brechen zusammen!");

            $this->spacecraftSystemManager->deactivate($wrapper, SpacecraftSystemTypeEnum::SHIELDS);

            $spacecraft->setShield(0);
        } else {
            $spacecraft->setShield($spacecraft->getShield() - $damage);
            $informations->addInformation("- Schildschaden: " . $damage . " - Status: " . $spacecraft->getShield());
        }

        $shieldSystemData = $wrapper->getShieldSystemData();
        if ($shieldSystemData === null) {
            throw new RuntimeException('this should not happen');
        }

        $shieldSystemData->setShieldRegenerationTimer(time())->update();
    }

    private function damageHull(SpacecraftWrapperInterface $wrapper, DamageWrapper $damageWrapper, int $damage, InformationInterface $informations): void
    {
        $spacecraft = $wrapper->get();

        if ($damageWrapper->isCrit()) {
            $systemName = $this->systemDamage->destroyRandomShipSystem($wrapper, $damageWrapper);

            if ($systemName !== null) {
                $informations->addInformationf("- Kritischer Hüllen-Treffer zerstört System: %s", $systemName);
            }
        }
        $huelleVorher = $spacecraft->getHull();
        $spacecraft->setHuell($huelleVorher - $damage);
        $informations->addInformationf("- Hüllenschaden: %d - Status: %d", $damage, $spacecraft->getHull());

        if (!$this->systemDamage->checkForDamagedShipSystems(
            $wrapper,
            $damageWrapper,
            $huelleVorher,
            $informations
        )) {
            $this->systemDamage->damageRandomShipSystem(
                $wrapper,
                $damageWrapper,
                $informations,
                (int)ceil((100 * $damage * random_int(1, 5)) / $spacecraft->getMaxHull())
            );
        }
    }
}
