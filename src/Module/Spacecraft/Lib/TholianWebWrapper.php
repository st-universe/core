<?php

namespace Stu\Module\Spacecraft\Lib;

use Override;
use Stu\Component\Spacecraft\Repair\RepairUtilInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SystemDataDeserializerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\FleetWrapperInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftStateChangerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Module\Spacecraft\Lib\Ui\StateIconAndTitle;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapper;
use Stu\Orm\Entity\TholianWebInterface;
use Stu\Orm\Repository\TorpedoTypeRepositoryInterface;

/**
 * @extends SpacecraftWrapper<TholianWebInterface>
 */
class TholianWebWrapper extends SpacecraftWrapper
{
    public function __construct(
        TholianWebInterface $tholianWeb,
        SpacecraftSystemManagerInterface $spacecraftSystemManager,
        SystemDataDeserializerInterface $systemDataDeserializer,
        TorpedoTypeRepositoryInterface $torpedoTypeRepository,
        GameControllerInterface $game,
        SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory,
        SpacecraftStateChangerInterface $spacecraftStateChanger,
        RepairUtilInterface $repairUtil,
        StateIconAndTitle $stateIconAndTitle
    ) {
        parent::__construct(
            $tholianWeb,
            $spacecraftSystemManager,
            $systemDataDeserializer,
            $torpedoTypeRepository,
            $game,
            $spacecraftWrapperFactory,
            $spacecraftStateChanger,
            $repairUtil,
            $stateIconAndTitle
        );
    }

    #[Override]
    public function get(): TholianWebInterface
    {
        return $this->spacecraft;
    }

    #[Override]
    public function getFleetWrapper(): ?FleetWrapperInterface
    {
        return null;
    }
}
