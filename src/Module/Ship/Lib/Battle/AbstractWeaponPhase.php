<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Battle;

use Stu\Component\Building\BuildingManagerInterface;
use Stu\Component\Game\GameEnum;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Module\History\Lib\EntryCreatorInterface;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Prestige\Lib\CreatePrestigeLogInterface;
use Stu\Module\Ship\Lib\ModuleValueCalculatorInterface;
use Stu\Module\Ship\Lib\ShipRemoverInterface;
use Stu\Module\Ship\Lib\ShipTorpedoManagerInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\WeaponRepositoryInterface;

abstract class AbstractWeaponPhase
{
    protected ShipSystemManagerInterface $shipSystemManager;

    protected WeaponRepositoryInterface $weaponRepository;

    protected EntryCreatorInterface $entryCreator;

    protected ShipRemoverInterface $shipRemover;

    protected ApplyDamageInterface $applyDamage;

    protected ModuleValueCalculatorInterface $moduleValueCalculator;

    protected BuildingManagerInterface $buildingManager;

    protected LoggerUtilInterface $loggerUtil;

    protected ShipTorpedoManagerInterface $shipTorpedoManager;

    private CreatePrestigeLogInterface $createPrestigeLog;

    private PrivateMessageSenderInterface $privateMessageSender;

    public function __construct(
        ShipSystemManagerInterface $shipSystemManager,
        WeaponRepositoryInterface $weaponRepository,
        EntryCreatorInterface $entryCreator,
        ShipRemoverInterface $shipRemover,
        ApplyDamageInterface $applyDamage,
        ModuleValueCalculatorInterface $moduleValueCalculator,
        BuildingManagerInterface $buildingManager,
        CreatePrestigeLogInterface $createPrestigeLog,
        PrivateMessageSenderInterface $privateMessageSender,
        ShipTorpedoManagerInterface $shipTorpedoManager,
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
        $this->shipTorpedoManager = $shipTorpedoManager;
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
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

    private function sendSystemMessage(string $description, int $userId): void
    {
        $this->privateMessageSender->send(
            GameEnum::USER_NOONE,
            $userId,
            $description
        );
    }
}
