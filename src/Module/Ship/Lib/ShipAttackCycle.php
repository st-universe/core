<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use Stu\Component\Ship\ShipAlertStateEnum;
use Stu\Component\Ship\ShipRoleEnum;
use Stu\Component\Ship\System\Exception\ShipSystemException;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Lib\DamageWrapper;
use Stu\Module\History\Lib\EntryCreatorInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\WeaponInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\WeaponRepositoryInterface;

final class ShipAttackCycle implements ShipAttackCycleInterface
{

    public const FIRINGMODE_FOCUS = 2;
    public const FIRINGMODE_RANDOM = 1;

    /**
     * @return ShipInterface[]
     */
    private $attacker = [];

    /**
     * @return ShipInterface[]
     */
    private $defender = [];
    private $firstStrike = true;
    private $attackShip = null;
    private $defendShip = null;
    private $messages = [];
    private $usedShips = ['attacker' => [], 'defender' => []];

    private $shipRemover;

    private $entryCreator;

    private $shipRepository;

    private $shipSystemManager;

    private $weaponRepository;

    private $singleMode = false;

    public function __construct(
        ShipRemoverInterface $shipRemover,
        EntryCreatorInterface $entryCreator,
        ShipRepositoryInterface $shipRepository,
        ShipSystemManagerInterface $shipSystemManager,
        WeaponRepositoryInterface $weaponRepository
    ) {
        $this->shipRemover = $shipRemover;
        $this->entryCreator = $entryCreator;
        $this->shipRepository = $shipRepository;
        $this->shipSystemManager = $shipSystemManager;
        $this->weaponRepository = $weaponRepository;
    }

    private function getAttacker(): array
    {
        return $this->attacker;
    }

    private function getDefender(): array
    {
        return $this->defender;
    }

    private function getAttackShip(): ?ShipInterface
    {
        return $this->attackShip;
    }

    private function getDefendShip(): ?ShipInterface
    {
        return $this->defendShip;
    }

    /**
     * @param ShipInterface[] $attackingShips indexed by ship id
     * @param ShipInterface $defendingShips indexed by ship id
     * @param bool $singleMode
     */
    public function init(
        array $attackingShips,
        array $defendingShips,
        bool $singleMode = false
    ): void {
        $this->attacker = $attackingShips;
        $this->defender = $defendingShips;
        $this->singleMode = $singleMode;
    }

    public function cycle(): void
    {
        while ($this->hasReadyAttacker() || $this->hasReadyDefender()) {
            $this->defineContrabants();

            $attackShip = $this->getAttackShip();
            $defendShip = $this->getDefendShip();

            if (!$attackShip || !$defendShip) {
                return;
            }
            if ($attackShip->getIsDestroyed() || $defendShip->getIsDestroyed()) {
                continue;
            }
            if ($this->firstStrike === true) {
                $this->firstStrike = false;
            }

            $msg = $this->alertLevelBasedReaction($attackShip);

            if ($msg) {
                $this->addMessage("Aktionen der " . $attackShip->getName());
                $this->addMessageMerge($msg);
                $msg = [];
            }
            if (!$this->canFire($attackShip)) {
                $this->shipRepository->save($attackShip);
                continue;
            }
            if ($defendShip->getWarpState()) {
                $this->shipSystemManager->deactivate($defendShip, ShipSystemTypeEnum::SYSTEM_WARPDRIVE);
            }
            $defendShip->cancelRepair();
            $attackShip->cancelRepair();

            //--------------------------------------

            // Phaser
            if ($attackShip->getPhaser()) {
                for ($i = 1; $i <= $attackShip->getRump()->getPhaserVolleys(); $i++) {
                    if (!$attackShip->getPhaser() || $attackShip->getEps() < $this->getEnergyWeaponEnergyCosts()) {
                        break;
                    }
                    $attackShip->setEps($attackShip->getEps() - $this->getEnergyWeaponEnergyCosts());
                    if ($this->getEnergyWeapon($attackShip)->getFiringMode() === self::FIRINGMODE_RANDOM) {

                        $this->redefineDefender();

                        $defendShip = $this->getDefendShip();

                        if (!$defendShip) {
                            $this->endCycle();
                            break;
                        }
                    }

                    $this->addMessage("Die " . $attackShip->getName() . " feuert mit einem " . $this->getEnergyWeapon($attackShip)->getName() . " auf die " . $defendShip->getName());

                    if (
                        $attackShip->getHitChance() * (100 - $defendShip->getEvadeChance()) < rand(1, 10000)
                    ) {
                        $this->addMessage("Die " . $defendShip->getName() . " wurde verfehlt");
                        $this->endCycle();
                        continue;
                    }
                    $damage_wrapper = new DamageWrapper(
                        $this->getEnergyWeaponDamage($attackShip),
                        $attackShip
                    );
                    $damage_wrapper->setShieldDamageFactor($attackShip->getRump()->getPhaserShieldDamageFactor());
                    $damage_wrapper->setHullDamageFactor($attackShip->getRump()->getPhaserHullDamageFactor());
                    $damage_wrapper->setIsPhaserDamage(true);

                    $this->addMessageMerge($defendShip->damage($damage_wrapper));

                    if ($defendShip->getIsDestroyed()) {
                        $this->entryCreator->addShipEntry(
                            'Die ' . $defendShip->getName() . ' wurde in Sektor ' . $defendShip->getSectorString() . ' von der ' . $attackShip->getName() . ' zerstört',
                            $attackShip->getUser()->getId()
                        );

                        $this->shipRemover->destroy($defendShip);

                        $this->unsetDefender();
                        $this->redefineDefender();

                        if (!$this->getDefendShip()) {
                            $this->endCycle();
                            break;
                        }
                        if ($this->getEnergyWeapon($attackShip)->getFiringMode() === self::FIRINGMODE_FOCUS) {
                            $this->endCycle();
                            break;
                        }
                    }
                }
            }
            if (!$this->getDefendShip()) {
                $this->endCycle();
                break;
            }
            // Torpedo
            if (!$attackShip->getTorpedos()) {
                $this->endCycle($msg);
                continue;
            }
            if ($this->getDefendShip()->getIsDestroyed()) {

                $this->redefineDefender();

                if (!$this->getDefendShip()) {
                    $this->endCycle();
                    break;
                }
            }

            for ($i = 1; $i <= $attackShip->getRump()->getTorpedoVolleys(); $i++) {
                if (!$attackShip->getTorpedos() || $attackShip->getEps() < $this->getProjectileWeaponEnergyCosts()) {
                    break;
                }
                $attackShip->setTorpedoCount($attackShip->getTorpedoCount() - 1);

                if ($attackShip->getTorpedoCount() === 0) {
                    $this->shipSystemManager->deactivate($attackShip, ShipSystemTypeEnum::SYSTEM_TORPEDO);
                }

                $attackShip->setEps($attackShip->getEps() - $this->getProjectileWeaponEnergyCosts());

                $this->redefineDefender();

                $defendShip = $this->getDefendShip();

                $this->addMessage("Die " . $attackShip->getName() . " feuert einen " . $attackShip->getTorpedo()->getName() . " auf die " . $defendShip->getName());
                // higher evade chance for pulseships against
                // torpedo ships
                if ($attackShip->getRump()->getRoleId() === ShipRoleEnum::ROLE_TORPEDOSHIP && $defendShip->getRump()->getRoleId() === ShipRoleEnum::ROLE_PULSESHIP) {
                    $hitchance = round($attackShip->getHitChance() * 0.65);
                } else {
                    $hitchance = $attackShip->getHitChance();
                }
                if ($hitchance * (100 - $this->getDefendShip()->getEvadeChance()) < rand(1, 10000)) {
                    $this->addMessage("Die " . $this->getDefendShip()->getName() . " wurde verfehlt");
                    continue;
                }
                $damage_wrapper = new DamageWrapper(
                    $this->getProjectileWeaponDamage($attackShip),
                    $attackShip
                );
                $damage_wrapper->setShieldDamageFactor($attackShip->getTorpedo()->getShieldDamageFactor());
                $damage_wrapper->setHullDamageFactor($attackShip->getTorpedo()->getHullDamageFactor());
                $damage_wrapper->setIsTorpedoDamage(true);

                $this->addMessageMerge($defendShip->damage($damage_wrapper));

                if ($defendShip->getIsDestroyed()) {
                    $this->unsetDefender();

                    $this->entryCreator->addShipEntry(
                        'Die ' . $defendShip->getName() . ' wurde in Sektor ' . $defendShip->getSectorString() . ' von der ' . $attackShip->getName() . ' zerstört',
                        $attackShip->getUser()->getId()
                    );
                    $this->shipRemover->destroy($defendShip);
                    break;
                }
            }
            $this->endCycle();
        }
    }

    private function endCycle(&$msg = []): void
    {
        $this->addMessageMerge($msg);

        $this->shipRepository->save($this->getAttackShip());
        if ($this->getDefendShip()) {
            $this->shipRepository->save($this->getDefendShip());
        }
    }

    private function redefineDefender(): void
    {
        $this->shipRepository->save($this->getDefendShip());

        if (array_key_exists($this->getDefendShip()->getId(), $this->getAttacker())) {
            $this->defendShip = $this->getRandomAttacker();
            return;
        }
        if (!array_key_exists($this->getAttackShip()->getId(), $this->getDefender())) {
            $this->defendShip = $this->getRandomDefender();
            return;
        }
        $this->defendShip = null;
    }

    private function defineContrabants(): void
    {
        if ($this->firstStrike || $this->singleMode) {
            $this->attackShip = $this->getRandomReadyAttacker();
            $this->defendShip = $this->getRandomDefender();
            return;
        }
        $attReady = $this->hasReadyAttacker();
        $defReady = $this->hasReadyDefender();
        if ($attReady && !$defReady) {
            $this->attackShip = $this->getRandomReadyAttacker();
            $this->defendShip = $this->getRandomDefender();
            return;
        }
        if (!$attReady && $defReady) {
            $this->attackShip = $this->getRandomReadyDefender();
            $this->defendShip = $this->getRandomAttacker();
            return;
        }
        // @todo
        if (rand(1, 2) == 1) {
            $this->attackShip = $this->getRandomReadyAttacker();
            $this->defendShip = $this->getRandomDefender();
        } else {
            $this->attackShip = $this->getRandomReadyDefender();
            $this->defendShip = $this->getRandomAttacker();
        }
        return;
    }

    private function getRandomDefender(): ?ShipInterface
    {
        $count = count($this->getDefender());
        if ($count == 0) {
            return null;
        }
        if ($count == 1) {
            $arr = current($this->getDefender());
            if ($arr->getIsDestroyed()) {
                return null;
            }
            if ($arr->getDisabled()) {
                $this->addMessage(_("Die " . $arr->getName() . " ist kampfunfähig"));
                return null;
            }
            return $arr;
        }
        $key = array_rand($this->getDefender());
        $defender = $this->getDefender();
        return $defender[$key];
    }

    private function getRandomReadyDefender(): ?ShipInterface
    {
        $arr = $this->getDefender();
        shuffle($arr);
        foreach ($arr as $key => $obj) {
            if ($obj->getIsDestroyed()) {
                unset($arr[$key]);
                continue;
            }
            if ($obj->getDisabled()) {
                $this->addMessage(_("Die " . $obj->getName() . " ist kampfunfähig"));
                return null;
            }
            if (!$this->hasShot('defender', $obj->getId())) {
                $this->setHasShot('defender', $obj->getId());
                return $obj;
            }
        }
        return null;
    }

    private function unsetDefender(): void
    {
        if (array_key_exists($this->getDefendShip()->getId(), $this->getAttacker())) {
            $arr = $this->getAttacker();
            unset($arr[$this->getDefendShip()->getId()]);
            $this->attacker = $arr;
            return;
        }
        $arr = $this->getDefender();
        unset($arr[$this->getDefendShip()->getId()]);
        $this->defender = $arr;
    }

    private function hasReadyAttacker()
    {
        return $this->getUsedShipCount('attacker') < count($this->getAttacker());
    }

    private function hasReadyDefender()
    {
        return $this->getUsedShipCount('defender') < count($this->getDefender());
    }

    private function getRandomAttacker(): ?ShipInterface
    {
        $count = count($this->getAttacker());
        if ($count == 0) {
            return null;
        }
        if ($count == 1) {
            $arr = &current($this->getAttacker());
            if ($arr->getIsDestroyed()) {
                return null;
            }
            if ($arr->getDisabled()) {
                $this->addMessage(_("Die " . $arr->getName() . " ist kampfunfähig"));
                return null;
            }
            return $arr;
        }
        $attacker = $this->getAttacker();
        $key = array_rand($attacker);
        return $attacker[$key];
    }

    private function getRandomReadyAttacker(): ?ShipInterface
    {
        $arr = &$this->getAttacker();
        shuffle($arr);
        foreach ($arr as $key => $obj) {
            if ($obj->getIsDestroyed() || $obj->getDisabled()) {
                unset($arr[$key]);
                continue;
            }
            if ($obj->getDisabled()) {
                $this->addMessage(_("Die " . $obj->getName() . " ist kampfunfähig"));
                unset($arr[$key]);
                return null;
            }
            if (!$this->hasShot('attacker', $obj->getId())) {
                $this->setHasShot('attacker', $obj->getId());
                return $obj;
            }
        }
        return null;
    }

    private function hasShot($key, $value): bool
    {
        return array_key_exists($value, $this->getUsedShips($key));
    }

    private function setHasShot($key, $value): void
    {
        $this->usedShips[$key][$value] = true;
    }

    private function getUsedShips($key): array
    {
        return $this->usedShips[$key];
    }

    private function getUsedShipCount($key): int
    {
        return count($this->getUsedShips($key));
    }

    private function addMessageMerge($msg): void
    {
        $this->messages = array_merge($this->getMessages(), $msg);
    }

    private function addMessage($msg): void
    {
        $this->messages[] = $msg;
    }

    public function getMessages(): array
    {
        return $this->messages;
    }

    private function canFire(ShipInterface $ship): bool
    {
        if ($ship->getEps() == 0) {
            return false;
        }
        if (!$ship->getNbs()) {
            return false;
        }
        if (!$ship->hasActiveWeapons()) {
            return false;
        }
        return true;
    }

    private function alertLevelBasedReaction(ShipInterface $ship): array
    {
        $msg = [];
        if ($ship->getCrewCount() == 0 || $ship->getRump()->isTrumfield()) {
            return $msg;
        }
        if ($ship->getAlertState() == ShipAlertStateEnum::ALERT_GREEN) {
            $ship->setAlertState(ShipAlertStateEnum::ALERT_YELLOW);
            $msg[] = "- Erhöhung der Alarmstufe wurde durchgeführt";
        }
        if ($ship->getDockedTo()) {
            $ship->setDockedTo(null);
            $msg[] = "- Das Schiff hat abgedockt";
        }
        if ($ship->getWarpState() == 1) {
            $this->shipSystemManager->deactivate($ship, ShipSystemTypeEnum::SYSTEM_WARPDRIVE);
            $msg[] = "- Der Warpantrieb wurde deaktiviert";
        }
        if ($ship->getCloakState()) {
            $this->shipSystemManager->deactivate($ship, ShipSystemTypeEnum::SYSTEM_CLOAK);
            $msg[] = "- Die Tarnung wurde deaktiviert";
        }
        try {
            $this->shipSystemManager->activate($ship, ShipSystemTypeEnum::SYSTEM_SHIELDS);

            $msg[] = "- Die Schilde wurden aktiviert";
        } catch (ShipSystemException $e) {
        }
        try {
            $this->shipSystemManager->activate($ship, ShipSystemTypeEnum::SYSTEM_NBS);

            $msg[] = "- Die Nahbereichssensoren wurden aktiviert";
        } catch (ShipSystemException $e) {
        }
        if ($ship->getAlertState() >= ShipAlertStateEnum::ALERT_YELLOW) {
            try {
                $this->shipSystemManager->activate($ship, ShipSystemTypeEnum::SYSTEM_PHASER);

                $msg[] = "- Die Energiewaffe wurde aktiviert";
            } catch (ShipSystemException $e) {
            }
        }
        return $msg;
    }

    private function getEnergyWeaponDamage(ShipInterface $ship): float
    {
        if (!$ship->hasShipSystem(ShipSystemTypeEnum::SYSTEM_PHASER)) {
            return 0;
        }
        $basedamage = calculateModuleValue(
            $ship->getRump(),
            $ship->getShipSystem(ShipSystemTypeEnum::SYSTEM_PHASER)->getModule(),
            'getBaseDamage'
        );
        $variance = (int) round($basedamage / 100 * $this->getEnergyWeapon($ship)->getVariance());
        $damage = rand($basedamage - $variance, $basedamage + $variance);
        if (rand(1, 100) <= $this->getEnergyWeapon($ship)->getCriticalChance()) {
            return $damage * 2;
        }
        return $damage;
    }

    private function getProjectileWeaponDamage(ShipInterface $ship): float
    {
        $variance = (int) round($ship->getTorpedo()->getBaseDamage() / 100 * $ship->getTorpedo()->getVariance());
        $basedamage = calculateModuleValue(
            $ship->getRump(),
            $ship->getShipSystem(ShipSystemTypeEnum::SYSTEM_TORPEDO)->getModule(),
            false,
            $ship->getTorpedo()->getBaseDamage()
        );
        $damage = rand($basedamage - $variance, $basedamage + $variance);
        if (rand(1, 100) <= $ship->getTorpedo()->getCriticalChance()) {
            return $damage * 2;
        }
        return $damage;
    }

    private function getEnergyWeapon(ShipInterface $ship): ?WeaponInterface
    {
        return $this->weaponRepository->findByModule(
            (int)$ship->getShipSystem(ShipSystemTypeEnum::SYSTEM_PHASER)->getModuleId()
        );
    }

    public function getProjectileWeaponEnergyCosts(): int
    {
        // @todo
        return 1;
    }

    public function getEnergyWeaponEnergyCosts(): int
    {
        // @todo
        return 1;
    }
}
