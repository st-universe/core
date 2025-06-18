<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Damage;

use Override;
use Stu\Lib\Damage\DamageWrapper;
use Stu\Module\Spacecraft\Lib\Destruction\SpacecraftDestructionCauseEnum;
use Stu\Module\Spacecraft\Lib\Destruction\SpacecraftDestructionInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageCollectionInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageFactoryInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

final class ApplyFieldDamage implements ApplyFieldDamageInterface
{
    public function __construct(
        private ApplyDamageInterface $applyDamage,
        private SpacecraftDestructionInterface $spacecraftDestruction,
        private MessageFactoryInterface $messageFactory
    ) {}

    #[Override]
    public function damage(
        SpacecraftWrapperInterface $wrapper,
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
        SpacecraftWrapperInterface $wrapper,
        int $damage,
        bool $isAbsolutDmg,
        string $cause,
        MessageCollectionInterface $messages
    ): void {
        $ship = $wrapper->get();

        $message = $this->messageFactory->createMessage(null, $ship->getUser()->getId());
        $messages->add($message);

        $shipName = $ship->getName();

        $dmg = $isAbsolutDmg ? $damage : $ship->getMaxHull() * $damage / 100;

        $message->add(sprintf(
            _('%s: Die %s wurde in Sektor %d|%d beschädigt'),
            $cause,
            $shipName,
            $ship->getPosX(),
            $ship->getPosY()
        ));

        $this->applyDamage->damage(
            new DamageWrapper((int) ceil($dmg)),
            $wrapper,
            $message
        );

        if ($ship->isDestroyed()) {

            $this->spacecraftDestruction->destroy(
                null,
                $wrapper,
                SpacecraftDestructionCauseEnum::FIELD_DAMAGE,
                $message
            );
        }
    }
}
