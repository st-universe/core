<?php

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
    private $firstStrike = 1;
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

    /**
     * @return ShipInterface[]
     */
    private function getAttacker(): array
    {
        return $this->attacker;
    }

    private function getDefender()
    {
        return $this->defender;
    }

    private function getFirstStrike()
    {
        return $this->firstStrike;
    }

    private function setFirstStrike($value)
    {
        $this->firstStrike = $value;
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

    public function cycle()
    {
        while ($this->hasReadyAttacker() || $this->hasReadyDefender()) {
            $this->defineContrabants();
            if (!$this->getAttackShip() || !$this->getDefendShip()) {
                return;
            }
            if ($this->getAttackShip()->getIsDestroyed() || $this->getDefendShip()->getIsDestroyed()) {
                continue;
            }
            if ($this->getFirstStrike()) {
                $this->setFirstStrike(0);
            }
            $msg = $this->alertLevelBasedReaction($this->getAttackShip());
            if ($msg) {
                $this->addMessage("Aktionen der " . $this->getAttackShip()->getName());
                $this->addMessageMerge($msg);
                $msg = [];
            }
            if (!$this->canFire($this->getAttackShip())) {
                $this->shipRepository->save($this->getAttackShip());
                continue;
            }
            if ($this->getDefendShip()->getWarpState()) {
                $this->shipSystemManager->deactivate($this->getDefendShip(), ShipSystemTypeEnum::SYSTEM_WARPDRIVE);
            }
            $this->getDefendShip()->cancelRepair();
            $this->getAttackShip()->cancelRepair();

            //--------------------------------------

            // Phaser
            if ($this->getAttackShip()->getPhaser()) {
                for ($i = 1; $i <= $this->getAttackShip()->getRump()->getPhaserVolleys(); $i++) {
                    if (!$this->getAttackShip()->getPhaser() || $this->getAttackShip()->getEps() < $this->getEnergyWeaponEnergyCosts()) {
                        break;
                    }
                    $this->getAttackShip()->setEps($this->getAttackShip()->getEps() - $this->getEnergyWeaponEnergyCosts());
                    if ($this->getEnergyWeapon($this->getAttackShip())->getFiringMode() == self::FIRINGMODE_RANDOM) {
                        $this->redefineDefender();
                        if (!$this->getDefendShip()) {
                            $this->endCycle();
                            break;
                        }
                    }
                    $this->addMessage("Die " . $this->getAttackShip()->getName() . " feuert mit einem " . $this->getEnergyWeapon($this->getAttackShip())->getName() . " auf die " . $this->getDefendShip()->getName());
                    if ($this->getAttackShip()->getHitChance() * (100 - $this->getDefendShip()->getEvadeChance()) < rand(1,
                            10000)) {
                        $this->addMessage("Die " . $this->getDefendShip()->getName() . " wurde verfehlt");
                        $this->endCycle();
                        continue;
                    }
                    $damage_wrapper = new DamageWrapper($this->getEnergyWeaponDamage($this->getAttackShip()),
                        $this->getAttackShip());
                    {
                        $damage_wrapper->setShieldDamageFactor($this->getAttackShip()->getRump()->getPhaserShieldDamageFactor());
                        $damage_wrapper->setHullDamageFactor($this->getAttackShip()->getRump()->getPhaserHullDamageFactor());
                        $damage_wrapper->setIsPhaserDamage(true);
                    }
                    $this->addMessageMerge($this->getDefendShip()->damage($damage_wrapper));
                    if ($this->getDefendShip()->getIsDestroyed()) {
                        $this->entryCreator->addShipEntry(
                            'Die ' . $this->getDefendShip()->getName() . ' wurde in Sektor ' . $this->getDefendShip()->getSectorString() . ' von der ' . $this->getAttackShip()->getName() . ' zerstört',
                            $this->getAttackShip()->getUser()->getId()
                        );
                        $this->shipRemover->destroy($this->getDefendShip());
                        $this->unsetDefender();
                        $this->redefineDefender();
                        if (!$this->getDefendShip()) {
                            $this->endCycle();
                            break;
                        }
                        if ($this->getEnergyWeapon($this->getAttackShip())->getFiringMode() == self::FIRINGMODE_FOCUS) {
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
            if (!$this->getAttackShip()->getTorpedos()) {
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
            for ($i = 1; $i <= $this->getAttackShip()->getRump()->getTorpedoVolleys(); $i++) {
                if (!$this->getAttackShip()->getTorpedos() || $this->getAttackShip()->getEps() < $this->getProjectileWeaponEnergyCosts()) {
                    break;
                }
                $this->getAttackShip()->setTorpedoCount($this->getAttackShip()->getTorpedoCount() - 1);
                if ($this->getAttackShip()->getTorpedoCount() == 0) {
                    $this->shipSystemManager->deactivate($this->getAttackShip(), ShipSystemTypeEnum::SYSTEM_TORPEDO);
                }
                $this->getAttackShip()->setEps($this->getAttackShip()->getEps() - $this->getProjectileWeaponEnergyCosts());
                $this->redefineDefender();
                $this->addMessage("Die " . $this->getAttackShip()->getName() . " feuert einen " . $this->getAttackShip()->getTorpedo()->getName() . " auf die " . $this->getDefendShip()->getName());
                // higher evade chance for pulseships against
                // torpedo ships
                if ($this->getAttackShip()->getRump()->getRoleId() == ShipRoleEnum::ROLE_TORPEDOSHIP && $this->getDefendShip()->getRump()->getRoleId() == ShipRoleEnum::ROLE_PULSESHIP) {
                    $hitchance = round($this->getAttackShip()->getHitChance() * 0.65);
                } else {
                    $hitchance = $this->getAttackShip()->getHitChance();
                }
                if ($hitchance * (100 - $this->getDefendShip()->getEvadeChance()) < rand(1, 10000)) {
                    $this->addMessage("Die " . $this->getDefendShip()->getName() . " wurde verfehlt");
                    continue;
                }
                $damage_wrapper = new DamageWrapper($this->getProjectileWeaponDamage($this->getAttackShip()),
                    $this->getAttackShip());
                {
                    $damage_wrapper->setShieldDamageFactor($this->getAttackShip()->getTorpedo()->getShieldDamageFactor());
                    $damage_wrapper->setHullDamageFactor($this->getAttackShip()->getTorpedo()->getHullDamageFactor());
                    $damage_wrapper->setIsTorpedoDamage(true);
                }
                $this->addMessageMerge($this->getDefendShip()->damage($damage_wrapper));
                if ($this->getDefendShip()->getIsDestroyed()) {
                    $this->unsetDefender();

                    $this->entryCreator->addShipEntry(
                        'Die ' . $this->getDefendShip()->getName() . ' wurde in Sektor ' . $this->getDefendShip()->getSectorString() . ' von der ' . $this->getAttackShip()->getName() . ' zerstört',
                        $this->getAttackShip()->getUser()->getId()
                    );
                    $this->shipRemover->destroy($this->getDefendShip());
                    break;
                }
            }
            $this->endCycle();
        }
    }

    /**
     */
    private function endCycle(&$msg = [])
    {
        $this->addMessageMerge($msg);

        $this->shipRepository->save($this->getAttackShip());
        if ($this->getDefendShip()) {
            $this->shipRepository->save($this->getDefendShip());
        }
    }

    /**
     */
    private function redefineDefender()
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

    private function defineContrabants()
    {
        if ($this->getFirstStrike() || $this->isSingleMode()) {
            $this->attackShip = $this->getRandomReadyAttacker();
            $this->defendShip = $this->getRandomDefender();
            return true;
        }
        $attReady = $this->hasReadyAttacker();
        $defReady = $this->hasReadyDefender();
        if ($attReady && !$defReady) {
            $this->attackShip = $this->getRandomReadyAttacker();
            $this->defendShip = $this->getRandomDefender();
            return true;
        }
        if (!$attReady && $defReady) {
            $this->attackShip = $this->getRandomReadyDefender();
            $this->defendShip = $this->getRandomAttacker();
            return true;
        }
        // XXX: TBD
        if (rand(1, 2) == 1) {
            $this->attackShip = $this->getRandomReadyAttacker();
            $this->defendShip = $this->getRandomDefender();
        } else {
            $this->attackShip = $this->getRandomReadyDefender();
            $this->defendShip = $this->getRandomAttacker();
        }
        return true;
    }

    private function getRandomDefender()
    {
        $count = count($this->getDefender());
        if ($count == 0) {
            return null;
        }
        if ($count == 1) {
            $arr = &current($this->getDefender());
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

    private function getRandomReadyDefender()
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

    /**
     */
    private function unsetDefender()
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

    private function getRandomAttacker()
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

    private function getRandomReadyAttacker()
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

    private function hasShot($key, $value)
    {
        return array_key_exists($value, $this->getUsedShips($key));
    }

    private function setHasShot($key, $value)
    {
        $this->usedShips[$key][$value] = true;
    }

    private function getUsedShips($key)
    {
        return $this->usedShips[$key];
    }

    private function getUsedShipCount($key)
    {
        return count($this->getUsedShips($key));
    }

    private function addMessageMerge($msg)
    {
        $this->messages = array_merge($this->getMessages(), $msg);
    }

    private function addMessage($msg)
    {
        $this->messages[] = $msg;
    }

    public function getMessages()
    {
        return $this->messages;
    }

    protected function isSingleMode()
    {
        return $this->singleMode;
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
        $basedamage = calculateModuleValue($ship->getRump(),
            $ship->getShipSystem(ShipSystemTypeEnum::SYSTEM_PHASER)->getModule(), 'getBaseDamage');
        $variance = round($basedamage / 100 * $this->getEnergyWeapon($ship)->getVariance());
        $damage = rand($basedamage - $variance, $basedamage + $variance);
        if (rand(1, 100) <= $this->getEnergyWeapon($ship)->getCriticalChance()) {
            return $damage * 2;
        }
        return $damage;
    }

    private function getProjectileWeaponDamage(ShipInterface $ship): float
    {
        $variance = round($ship->getTorpedo()->getBaseDamage() / 100 * $ship->getTorpedo()->getVariance());
        $basedamage = calculateModuleValue($ship->getRump(),
            $ship->getShipSystem(ShipSystemTypeEnum::SYSTEM_TORPEDO)->getModule(), false,
            $ship->getTorpedo()->getBaseDamage());
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
