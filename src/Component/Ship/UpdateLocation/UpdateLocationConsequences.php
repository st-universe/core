<?php

declare(strict_types=1);

namespace Stu\Component\Ship\UpdateLocation;

use Stu\Component\Ship\UpdateLocation\Handler\UpdateLocationHandlerInterface;
use Stu\Module\Logging\LoggerEnum;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\ShipInterface;

final class UpdateLocationConsequences implements UpdateLocationConsequencesInterface
{
    /** @var array<UpdateLocationHandlerInterface>  */
    private array $preMoveHandler;

    /** @var array<UpdateLocationHandlerInterface> */
    private array $postMoveHandler;

    private LoggerUtilInterface $loggerUtil;

    /**
     *  @param UpdateLocationHandlerInterface[] $preMoveHandler
     *  @param UpdateLocationHandlerInterface[] $postMoveHandler
     */
    public function __construct(
        array $preMoveHandler,
        array $postMoveHandler,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->preMoveHandler = $preMoveHandler;
        $this->postMoveHandler = $postMoveHandler;
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

    public function updateLocationWithConsequences(
        ShipWrapperInterface $wrapper,
        ?ShipInterface $tractoringShip,
        $nextField,
        array &$msgToPlayer = null
    ): void {
        $this->loggerUtil->init('mov2', LoggerEnum::LEVEL_ERROR);

        // preMove handler
        $this->walkHandler($this->preMoveHandler, $wrapper, $tractoringShip, $msgToPlayer);

        // update location
        $ship = $wrapper->get();
        if ($ship->getSystem() === null) {
            $ship->updateLocation($nextField, null);
        } else {
            $ship->updateLocation(null, $nextField);
        }

        // postMove handler
        $this->walkHandler($this->postMoveHandler, $wrapper, $tractoringShip, $msgToPlayer);
    }

    private function walkHandler(array $handler, ShipWrapperInterface $wrapper, ?ShipInterface $tractoringShip, array &$msgToPlayer): void
    {
        array_walk(
            $handler,
            function (UpdateLocationHandlerInterface $handler, string $key) use ($wrapper, $tractoringShip, &$msgToPlayer): void {
                $handler->clearMessages();
                $handler->handle($wrapper, $tractoringShip);

                if (!empty($handler->getInternalMsg())) {
                    $this->loggerUtil->log(sprintf('handler: %s', $key));
                    $this->scheduleMsgToOwnerOrPlayer($wrapper->get(), $tractoringShip, $handler->getInternalMsg(), $msgToPlayer);
                }
            }
        );
    }

    private function scheduleMsgToOwnerOrPlayer(ShipInterface $ship, ?ShipInterface $tractoringShip, array $msgToSchedule, ?array &$msgToPlayer = null): void
    {
        $this->loggerUtil->log(sprintf(' msgToScheduleSize: %d', count($msgToSchedule)));

        $scheduleToOwnerOfPassiveShip = $tractoringShip !== null && $tractoringShip->getUser() !== $ship->getUser();

        if ($scheduleToOwnerOfPassiveShip) {
            $this->loggerUtil->log('  scheduleToOwnerOfPassiveShip');
            $this->informOwnerOfTractoredShip($msgToSchedule);
        } else {
            $this->loggerUtil->log('  msgToPlayer');
            if ($msgToPlayer !== null) {
                $msgToPlayer = array_merge($msgToPlayer, $msgToSchedule);
                $this->loggerUtil->log(sprintf('    size: %d', count($msgToPlayer)));
            }
        }
    }

    private function informOwnerOfTractoredShip(array $msg): void
    {
        //TODO privateMessageSender
    }

    // $ship->setState(ShipStateEnum::SHIP_STATE_NONE);

    //TODO cancel colony block / defend, wenn passiv
    //CancelColonyBlockOrDefendInterface
    //$game->addInformationMergeDown($this->cancelColonyBlockOrDefend->work($ship, true));

    //TODO wenn Traktor in Flotte, dann freilassen

    //TODO notruf
    //TODO notruf canceln wenn bewegen

    //TODO andockschleuse
    //TODO andockschleuse deaktivieren, wenn aktiv
    //  $ship->setDockedTo(null);
    //TODO andockschleuse schrotten, wenn passiv


}
