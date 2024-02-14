<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Damage;

use Stu\Lib\DamageWrapper;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\History\Lib\EntryCreatorInterface;
use Stu\Module\Ship\Lib\Damage\ApplyDamageInterface;
use Stu\Module\Ship\Lib\Message\Message;
use Stu\Module\Ship\Lib\Message\MessageCollectionInterface;
use Stu\Module\Ship\Lib\ShipRemoverInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;

final class ApplyFieldDamage implements ApplyFieldDamageInterface
{
    private ApplyDamageInterface $applyDamage;

    private EntryCreatorInterface $entryCreator;

    private ShipRemoverInterface $shipRemover;

    public function __construct(
        ApplyDamageInterface $applyDamage,
        EntryCreatorInterface $entryCreator,
        ShipRemoverInterface $shipRemover
    ) {
        $this->applyDamage = $applyDamage;
        $this->entryCreator = $entryCreator;
        $this->shipRemover = $shipRemover;
    }

    public function damage(
        ShipWrapperInterface $wrapper,
        int $damage,
        bool $isAbsolutDmg,
        string $cause,
        MessageCollectionInterface $messages
    ): void {

        //ship itself
        $this->damageShip(
            $wrapper,
            $damage,
            $isAbsolutDmg,
            $cause,
            $messages
        );

        //tractored ship
        $tractoredShipWrapper = $wrapper->getTractoredShipWrapper();
        if ($tractoredShipWrapper !== null) {
            $this->damageShip(
                $tractoredShipWrapper,
                $damage,
                $isAbsolutDmg,
                $cause,
                $messages
            );
        }
    }

    private function damageShip(
        ShipWrapperInterface $wrapper,
        int $damage,
        bool $isAbsolutDmg,
        string $cause,
        MessageCollectionInterface $messages
    ): void {
        $ship = $wrapper->get();

        $message = new Message(null, $ship->getUser()->getId());
        $messages->add($message);

        $shipName = $ship->getName();
        $rumpName = $ship->getRump()->getName();

        $dmg = $isAbsolutDmg ? $damage : $ship->getMaxHull() * $damage / 100;

        $message->add(sprintf(
            _('%s: Die %s wurde in Sektor %d|%d beschädigt'),
            $cause,
            $shipName,
            $ship->getPosX(),
            $ship->getPosY()
        ));
        $message->addMessageMerge($this->applyDamage->damage(
            new DamageWrapper((int) ceil($dmg)),
            $wrapper
        )->getInformations());

        if ($ship->isDestroyed()) {
            $this->entryCreator->addEntry(
                sprintf(
                    _('Die %s (%s) wurde beim Einflug in Sektor %s zerstört'),
                    $shipName,
                    $rumpName,
                    $ship->getSectorString()
                ),
                UserEnum::USER_NOONE,
                $ship
            );

            $this->shipRemover->destroy($wrapper);
        }
    }
}
