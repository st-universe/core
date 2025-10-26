<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Damage;

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

    #[\Override]
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
        $hull = $spacecraft->getCondition()->getHull();
        if ($spacecraft->getSystemState(SpacecraftSystemTypeEnum::RPG_MODULE) && $hull - $damage < round($spacecraft->getMaxHull() / 100 * 10)) {
            $damage = (int) round($hull - $spacecraft->getMaxHull() / 100 * 10);
            $disablemessage = _('-- Das Schiff wurde kampfunfähig gemacht');
            $spacecraft->getCondition()->setDisabled(true);
        }
        if ($hull > $damage) {
            $this->damageHull($wrapper, $damageWrapper, $damage, $informations);

            if ($disablemessage) {
                $informations->addInformation($disablemessage);
            }
        } else {
            $informations->addInformation("- Hüllenschaden: " . $damage);
            $informations->addInformation("-- Das Schiff wurde zerstört!");
            $spacecraft->getCondition()->setIsDestroyed(true);
        }
    }

    private function damageShields(SpacecraftWrapperInterface $wrapper, DamageWrapper $damageWrapper, InformationInterface $informations): void
    {
        $spacecraft = $wrapper->get();
        $condition = $spacecraft->getCondition();

        $damage = (int) $damageWrapper->getDamageRelative($spacecraft, DamageModeEnum::SHIELDS);
        if ($damage >= $condition->getShield()) {
            $informations->addInformation("- Schildschaden: " . $condition->getShield());
            $informations->addInformation("-- Schilde brechen zusammen!");

            $this->spacecraftSystemManager->deactivate($wrapper, SpacecraftSystemTypeEnum::SHIELDS);

            $condition->setShield(0);
        } else {
            $condition->setShield($condition->getShield() - $damage);
            $informations->addInformation("- Schildschaden: " . $damage . " - Status: " . $condition->getShield());
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
        $condition = $spacecraft->getCondition();

        if ($damageWrapper->isCrit()) {
            $this->handleCriticalHit($wrapper, $damageWrapper, $informations);
        }

        $huelleVorher = $condition->getHull();
        $condition->changeHull(-$damage);
        $informations->addInformationf("- Hüllenschaden: %d - Status: %d", $damage, $condition->getHull());

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

    private function handleCriticalHit(SpacecraftWrapperInterface $wrapper, DamageWrapper $damageWrapper, InformationInterface $informations): void
    {
        $spacecraft = $wrapper->get();
        $currentHull = $spacecraft->getCondition()->getHull();
        $maxHull = $spacecraft->getMaxHull();
        $hullPercentage = ($currentHull / $maxHull) * 100;

        if ($hullPercentage > 50) {
            $criticalDamage = random_int(30, 60);
            $this->systemDamage->damageRandomShipSystem($wrapper, $damageWrapper, $informations, $criticalDamage);
            $informations->addInformationf("- Kritischer Hüllen-Treffer verursacht %d%% Systemschaden", $criticalDamage);
        } else {
            $destructionChance = (50 - $hullPercentage) / 50;

            if (random_int(1, 100) <= ($destructionChance * 100)) {
                $systemName = $this->systemDamage->destroyRandomShipSystem($wrapper, $damageWrapper);
                if ($systemName !== null) {
                    $informations->addInformationf("- Kritischer Hüllen-Treffer zerstört System: %s", $systemName);
                }
            } else {
                $criticalDamage = random_int(50, 90);
                $this->systemDamage->damageRandomShipSystem($wrapper, $damageWrapper, $informations, $criticalDamage);
                $informations->addInformationf("- Kritischer Hüllen-Treffer verursacht %d%% Systemschaden", $criticalDamage);
            }
        }
    }
}
