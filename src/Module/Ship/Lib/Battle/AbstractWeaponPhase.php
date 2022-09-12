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
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

    public function checkForNegativePrestige(UserInterface $destroyer, ShipInterface $target): void
    {
        if ($this->isWorkbee($target)) {
            $amount = -10;
            $description = sprintf('[b][color=red]%d[/color][/b] Prestige erhalten für die Zerstörung eines Workbees', $amount);

            $this->createPrestigeLog->createLog($amount, $description, $destroyer, time());
            $this->sendSystemMessage($description, $destroyer->getId());
        } else if ($target->getRump()->isEscapePods()) {
            $amount = $target->getCrewCount() === 0 ? -20 : -100;
            $description = sprintf('[b][color=red]%d[/color][/b] Prestige erhalten für die Zerstörung einer Rettungskapsel', $amount);

            $this->createPrestigeLog->createLog($amount, $description, $destroyer, time());
            $this->sendSystemMessage($description, $destroyer->getId());
        }
    }

    private function isWorkbee(ShipInterface $ship): bool
    {
        $commodity = $ship->getRump()->getCommodity();
        if ($commodity === null) {
            return false;
        }

        return $commodity->isWorkbee();
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
