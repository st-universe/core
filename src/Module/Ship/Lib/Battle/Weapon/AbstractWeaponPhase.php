<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Battle\Weapon;

use Stu\Component\Building\BuildingManagerInterface;
use Stu\Component\Ship\ShipModuleTypeEnum;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Lib\Pirate\Component\PirateWrathManagerInterface;
use Stu\Module\Control\StuRandom;
use Stu\Module\History\Lib\EntryCreatorInterface;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Prestige\Lib\CreatePrestigeLogInterface;
use Stu\Module\Ship\Lib\Battle\Provider\AttackerInterface;
use Stu\Module\Ship\Lib\Damage\ApplyDamageInterface;
use Stu\Module\Ship\Lib\ModuleValueCalculatorInterface;
use Stu\Module\Ship\Lib\ShipRemoverInterface;
use Stu\Orm\Entity\ModuleInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\WeaponRepositoryInterface;

abstract class AbstractWeaponPhase
{
    protected LoggerUtilInterface $loggerUtil;

    public function __construct(
        protected ShipSystemManagerInterface $shipSystemManager,
        protected WeaponRepositoryInterface $weaponRepository,
        protected EntryCreatorInterface $entryCreator,
        protected ShipRemoverInterface $shipRemover,
        protected ApplyDamageInterface $applyDamage,
        protected ModuleValueCalculatorInterface $moduleValueCalculator,
        protected BuildingManagerInterface $buildingManager,
        protected CreatePrestigeLogInterface $createPrestigeLog,
        protected PrivateMessageSenderInterface $privateMessageSender,
        protected StuRandom $stuRandom,
        private PirateWrathManagerInterface $pirateWrathManager,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->shipSystemManager = $shipSystemManager;
        $this->weaponRepository = $weaponRepository;
        $this->entryCreator = $entryCreator;
        $this->shipRemover = $shipRemover;
        $this->applyDamage = $applyDamage;
        $this->moduleValueCalculator = $moduleValueCalculator;
        $this->buildingManager = $buildingManager;
        $this->createPrestigeLog = $createPrestigeLog;
        $this->privateMessageSender = $privateMessageSender;
        $this->stuRandom = $stuRandom;
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

    public function handleDestruction(
        AttackerInterface $attacker,
        ShipInterface $target,
        bool $isAlertRed
    ): void {
        if ($isAlertRed) {
            $this->entryCreator->addEntry(
                '[b][color=red]Alarm-Rot:[/color][/b] Die ' . $target->getName() . ' (' . $target->getRump()->getName() . ') wurde in Sektor ' . $target->getSectorString() . ' von der ' . $attacker->getName() . ' zerstört',
                $attacker->getUser()->getId(),
                $target
            );
        } else {
            $entryMsg = sprintf(
                'Die %s (%s) wurde in Sektor %s von der %s zerstört',
                $target->getName(),
                $target->getRump()->getName(),
                $target->getSectorString(),
                $attacker->getName()
            );
            $this->entryCreator->addEntry(
                $entryMsg,
                $attacker->getUser()->getId(),
                $target
            );
        }

        $this->checkForPrestige($attacker->getUser(), $target);
        $this->descreasePirateWrath($attacker->getUser(), $target);
    }

    public function checkForPrestige(UserInterface $destroyer, ShipInterface $target): void
    {
        $rump = $target->getRump();
        $amount = $rump->getPrestige();

        // nothing to do
        if ($amount === 0) {
            return;
        }

        // empty escape pods to five times negative prestige
        if ($rump->isEscapePods() && $target->getCrewCount() === 0) {
            $amount *= 5;
        }

        $description = sprintf(
            '%s%d%s Prestige erhalten für die Zerstörung von: %s',
            $amount < 0 ? '[b][color=red]' : '',
            $amount,
            $amount < 0 ? '[/color][/b]' : '',
            $rump->getName()
        );

        $this->createPrestigeLog->createLog($amount, $description, $destroyer, time());

        // system pm only for negative prestige
        if ($amount < 0) {
            $this->sendSystemMessage($description, $destroyer->getId());
        }
    }

    private function descreasePirateWrath(UserInterface $attacker, ShipInterface $target): void
    {
        if ($attacker->getId() !== UserEnum::USER_NPC_KAZON) {
            return;
        }

        $targetPrestige = $target->getRump()->getPrestige();
        if ($targetPrestige < 1) {
            return;
        }

        $this->pirateWrathManager->decreaseWrath($target->getUser(), $targetPrestige);
    }

    private function sendSystemMessage(string $description, int $userId): void
    {
        $this->privateMessageSender->send(
            UserEnum::USER_NOONE,
            $userId,
            $description
        );
    }

    protected function getModule(ShipInterface $ship, ShipModuleTypeEnum $moduleType): ?ModuleInterface
    {
        $buildplan = $ship->getBuildplan();
        if ($buildplan === null) {
            return null;
        }

        $buildplanModule = current($buildplan->getModulesByType($moduleType));
        if (!$buildplanModule) {
            return null;
        }

        return $buildplanModule->getModule();
    }
}
