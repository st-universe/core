<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Battle\Weapon;

use Stu\Component\Building\BuildingManagerInterface;
use Stu\Component\Ship\ShipModuleTypeEnum;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Lib\Information\InformationInterface;
use Stu\Module\Control\StuRandom;
use Stu\Module\History\Lib\EntryCreatorInterface;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Prestige\Lib\CreatePrestigeLogInterface;
use Stu\Module\Ship\Lib\Damage\ApplyDamageInterface;
use Stu\Module\Ship\Lib\Destruction\ShipDestroyerInterface;
use Stu\Module\Ship\Lib\Destruction\ShipDestructionCauseEnum;
use Stu\Module\Ship\Lib\Destruction\ShipDestructionInterface;
use Stu\Module\Ship\Lib\ModuleValueCalculatorInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ModuleInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\WeaponRepositoryInterface;

abstract class AbstractWeaponPhase
{
    protected LoggerUtilInterface $loggerUtil;

    public function __construct(
        protected ShipSystemManagerInterface $shipSystemManager,
        protected WeaponRepositoryInterface $weaponRepository,
        protected EntryCreatorInterface $entryCreator,
        protected ApplyDamageInterface $applyDamage,
        protected ModuleValueCalculatorInterface $moduleValueCalculator,
        protected BuildingManagerInterface $buildingManager,
        protected CreatePrestigeLogInterface $createPrestigeLog,
        protected PrivateMessageSenderInterface $privateMessageSender,
        protected StuRandom $stuRandom,
        private ShipDestructionInterface $shipDestruction,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

    protected function checkForShipDestruction(
        ShipDestroyerInterface $attacker,
        ShipWrapperInterface $targetWrapper,
        bool $isAlertRed,
        InformationInterface $message
    ): void {

        if (!$targetWrapper->get()->isDestroyed()) {
            return;
        }

        $this->shipDestruction->destroy(
            $attacker,
            $targetWrapper,
            $isAlertRed ? ShipDestructionCauseEnum::ALERT_RED : ShipDestructionCauseEnum::SHIP_FIGHT,
            $message
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
