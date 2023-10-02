<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Movement\Component\Consequence\Flight;

use Stu\Component\Ship\ShipStateEnum;
use Stu\Module\Ship\Lib\Battle\Message\FightMessage;
use Stu\Module\Ship\Lib\Battle\Message\FightMessageCollectionInterface;
use Stu\Module\Ship\Lib\Movement\Component\Consequence\AbstractFlightConsequence;
use Stu\Module\Ship\Lib\Movement\Route\FlightRouteInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Ship\Lib\TholianWebUtilInterface;

class TholianWebConsequence extends AbstractFlightConsequence
{
    private TholianWebUtilInterface $tholianWebUtil;

    public function __construct(TholianWebUtilInterface $tholianWebUtil)
    {
        $this->tholianWebUtil = $tholianWebUtil;
    }

    protected function triggerSpecific(
        ShipWrapperInterface $wrapper,
        FlightRouteInterface $flightRoute,
        FightMessageCollectionInterface $messages
    ): void {

        $ship = $wrapper->get();

        $message = new FightMessage(null, $ship->getUser()->getId());
        $messages->add($message);

        //web spinning
        if ($ship->getState() === ShipStateEnum::SHIP_STATE_WEB_SPINNING) {
            $this->tholianWebUtil->releaseWebHelper($wrapper);

            $message->add(sprintf('Die %s hat die Unterstützung des Energienetzes abgebrochen', $ship->getName()));
        }

        // release from unfinished web
        $holdingWeb = $ship->getHoldingWeb();
        if ($holdingWeb !== null && !$holdingWeb->isFinished()) {
            $this->tholianWebUtil->releaseShipFromWeb($wrapper);

            $message->add(sprintf('Die %s ist einem unfertigen Energienetz entkommen', $ship->getName()));
        }
    }
}
