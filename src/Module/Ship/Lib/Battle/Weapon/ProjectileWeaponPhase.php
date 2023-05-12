<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Battle\Weapon;

use Stu\Lib\DamageWrapper;
use Stu\Module\Ship\Lib\Battle\Message\FightMessage;
use Stu\Module\Ship\Lib\Battle\Provider\ProjectileAttackerInterface;
use Stu\Orm\Entity\PlanetFieldInterface;
use Stu\Orm\Entity\TorpedoTypeInterface;
use Stu\Orm\Repository\TorpedoHullRepositoryInterface;
use Stu\Module\Ship\Lib\Battle\ApplyDamageInterface;
use Stu\Module\History\Lib\EntryCreatorInterface;
use Stu\Module\Prestige\Lib\CreatePrestigeLogInterface;
use Stu\Module\Ship\Lib\ShipRemoverInterface;

//TODO unit tests
final class ProjectileWeaponPhase extends AbstractWeaponPhase implements ProjectileWeaponPhaseInterface
{
    private TorpedoHullRepositoryInterface $torpedoHullRepository;

    protected ApplyDamageInterface $applyDamage;

    protected EntryCreatorInterface $entryCreator;

    protected ShipRemoverInterface $shipRemover;

    protected CreatePrestigeLogInterface $createPrestigeLog;


    public function __construct(
        ApplyDamageInterface $applyDamage,
        EntryCreatorInterface $entryCreator,
        CreatePrestigeLogInterface $createPrestigeLog,
        ShipRemoverInterface $shipRemover,
        TorpedoHullRepositoryInterface $torpedoHullRepository
    ) {
        $this->applyDamage = $applyDamage;
        $this->entryCreator = $entryCreator;
        $this->torpedoHullRepository = $torpedoHullRepository;
        $this->shipRemover = $shipRemover;
        $this->createPrestigeLog = $createPrestigeLog;
    }

    public function fire(
        ProjectileAttackerInterface $attacker,
        array $targetPool,
        bool $isAlertRed = false
    ): array {
        $fightMessages = [];

        for ($i = 1; $i <= $attacker->getTorpedoVolleys(); $i++) {
            if (count($targetPool) === 0) {
                break;
            }

            $torpedo = $attacker->getTorpedo();
            $targetWrapper = $targetPool[array_rand($targetPool)];
            $target = $targetWrapper->get();

            if (
                $torpedo === null
                || !$attacker->getTorpedoState()
                || !$attacker->hasSufficientEnergy($this->getProjectileWeaponEnergyCosts())
                || $attacker->getTorpedoCount() === 0
            ) {
                break;
            }

            $torpedoName =  $torpedo->getName();

            $attacker->lowerTorpedoCount(1);
            $attacker->reduceEps($this->getProjectileWeaponEnergyCosts());

            $fightMessage = new FightMessage($attacker->getUser()->getId(), $target->getUser()->getId());
            $fightMessages[] = $fightMessage;

            $fightMessage->add("Die " . $attacker->getName() . " feuert einen " . $torpedoName . " auf die " . $target->getName());

            $hitchance = $attacker->getHitChance();
            if ($hitchance * (100 - $target->getEvadeChance()) < rand(1, 10000)) {
                $fightMessage->add("Die " . $target->getName() . " wurde verfehlt");
                continue;
            }

            $isCritical = $this->isCritical($torpedo, $target->getCloakState());

            $damage_wrapper = new DamageWrapper(
                $attacker->getProjectileWeaponDamage($isCritical),
                $attacker
            );
            $damage_wrapper->setCrit($isCritical);
            $damage_wrapper->setShieldDamageFactor($torpedo->getShieldDamageFactor());
            $damage_wrapper->setHullDamageFactor($torpedo->getHullDamageFactor());
            $damage_wrapper->setIsTorpedoDamage(true);
            if ($target->getBuildplan() !== null && $torpedo !== null && $this->torpedoHullRepository !== null) {
                $damage_wrapper->setModificator($this->torpedoHullRepository->getByModuleAndTorpedo(current($target->getBuildplan()->getModulesByType(1))->getModule()->getId(), $torpedo->getId())->getModificator());
            }

            $fightMessage->addMessageMerge($this->applyDamage->damage($damage_wrapper, $targetWrapper));

            if ($target->isDestroyed()) {
                unset($targetPool[$target->getId()]);

                if ($isAlertRed) {
                    $this->entryCreator->addShipEntry(
                        '[b][color=red]Alarm-Rot:[/color][/b] Die ' . $target->getName() . ' (' . $target->getRump()->getName() . ') wurde in Sektor ' . $target->getSectorString() . ' von der ' . $attacker->getName() . ' zerstört',
                        $attacker->getUser()->getId()
                    );
                } else {
                    $entryMsg = sprintf(
                        'Die %s (%s) wurde in Sektor %s von der %s zerstört',
                        $target->getName(),
                        $target->getRump()->getName(),
                        $target->getSectorString(),
                        $attacker->getName()
                    );
                    if ($target->isBase()) {
                        $this->entryCreator->addStationEntry(
                            $entryMsg,
                            $attacker->getUser()->getId()
                        );
                    } else {
                        $this->entryCreator->addShipEntry(
                            $entryMsg,
                            $attacker->getUser()->getId()
                        );
                    }
                }
                $this->checkForPrestige($attacker->getUser(), $target);
                $fightMessage->add($this->shipRemover->destroy($targetWrapper));
            }
        }

        return $fightMessages;
    }

    public function fireAtBuilding(
        ProjectileAttackerInterface $attacker,
        PlanetFieldInterface $target,
        bool $isOrbitField,
        int &$antiParticleCount
    ): array {
        $building = $target->getBuilding();
        if ($building === null) {
            return [];
        }

        $msg = [];
        for ($i = 1; $i <= $attacker->getTorpedoVolleys(); $i++) {
            $torpedo = $attacker->getTorpedo();

            if (
                $torpedo === null
                || !$attacker->getTorpedoState()
                || !$attacker->hasSufficientEnergy($this->getProjectileWeaponEnergyCosts())
            ) {
                break;
            }

            $attacker->lowerTorpedoCount(1);
            $attacker->reduceEps($this->getProjectileWeaponEnergyCosts());

            $msg[] = sprintf(
                _("Die %s feuert einen %s auf das Gebäude %s auf Feld %d"),
                $attacker->getName(),
                $torpedo->getName(),
                $building->getName(),
                $target->getFieldId()
            );

            if ($antiParticleCount > 0) {
                $antiParticleCount--;
                $msg[] = "Der Torpedo wurde vom orbitalem Torpedoabwehrsystem abgefangen";
                continue;
            }
            if ($attacker->getHitChance() < rand(1, 100)) {
                $msg[] = "Das Gebäude wurde verfehlt";
                continue;
            }
            $isCritical = rand(1, 100) <= $torpedo->getCriticalChance();
            $damage_wrapper = new DamageWrapper(
                $attacker->getProjectileWeaponDamage($isCritical),
                $attacker
            );
            $damage_wrapper->setCrit($isCritical);
            $damage_wrapper->setShieldDamageFactor($torpedo->getShieldDamageFactor());
            $damage_wrapper->setHullDamageFactor($torpedo->getHullDamageFactor());
            $damage_wrapper->setIsTorpedoDamage(true);

            $msg = array_merge($msg, $this->applyDamage->damageBuilding($damage_wrapper, $target, $isOrbitField));

            if ($target->getIntegrity() === 0) {
                $this->entryCreator->addColonyEntry(
                    sprintf(
                        _('Das Gebäude %s auf Kolonie %s wurde von der %s zerstört'),
                        $building->getName(),
                        $target->getColony()->getName(),
                        $attacker->getName()
                    )
                );

                $this->buildingManager->remove($target);
                break;
            }
            //deactivate if high damage
            elseif ($target->hasHighDamage()) {
                $this->buildingManager->deactivate($target);
            }
        }

        return $msg;
    }

    private function getProjectileWeaponEnergyCosts(): int
    {
        // @todo
        return 1;
    }

    private function isCritical(TorpedoTypeInterface $torpedo, bool $isTargetCloaked): bool
    {
        $critChance = $isTargetCloaked ? $torpedo->getCriticalChance() * 2 : $torpedo->getCriticalChance();
        if (rand(1, 100) <= $critChance) {
            return true;
        }
        return false;
    }
}