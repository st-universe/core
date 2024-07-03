<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Interaction;

use Override;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\ShipInterface;

final class InteractionChecker implements InteractionCheckerInterface
{
    #[Override]
    public function checkPosition(ShipInterface $shipa, ShipInterface $shipb): bool
    {
        return $shipa->getMap() === $shipb->getMap() && $shipa->getStarsystemMap() === $shipb->getStarsystemMap();
    }

    #[Override]
    public function checkColonyPosition(ColonyInterface $col, ShipInterface $ship): bool
    {
        return $col->getStarsystemMap() === $ship->getStarsystemMap();
    }

    //TODO intercept script attacks, e.g. beam from cloaked or warped ship
    #[Override]
    public static function canInteractWith(
        ShipInterface $ship,
        ShipInterface|ColonyInterface $target,
        GameControllerInterface $game,
        bool $doCloakCheck = false
    ): bool {
        if ($target->getUser()->isVacationRequestOldEnough()) {
            $game->addInformation(_('Aktion nicht möglich, der Spieler befindet sich im Urlaubsmodus!'));

            return false;
        }

        if ($ship->getCloakState()) {
            return false;
        }

        $interactionChecker = new InteractionChecker();
        if ($target instanceof ColonyInterface) {
            return $interactionChecker->checkColonyPosition($target, $ship) && $target !== $ship;
        } elseif (!$interactionChecker->checkPosition($ship, $target)) {
            return false;
        }
        if ($target->getShieldState() && $target->getUser() !== $ship->getUser()) {
            return false;
        }
        return !($doCloakCheck && $target->getCloakState());
    }
}
