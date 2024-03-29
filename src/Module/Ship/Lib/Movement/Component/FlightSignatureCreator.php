<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Movement\Component;

use InvalidArgumentException;
use Stu\Component\Ship\ShipEnum;
use Stu\Orm\Entity\FlightSignatureInterface;
use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\StarSystemMapInterface;
use Stu\Orm\Repository\FlightSignatureRepositoryInterface;

/**
 * Creates flight signatures for ship movements
 */
final class FlightSignatureCreator implements FlightSignatureCreatorInterface
{
    /** @var array<int, int> */
    private const FLIGHT_DIRECTIONS = [
        ShipEnum::DIRECTION_RIGHT => ShipEnum::DIRECTION_LEFT,
        ShipEnum::DIRECTION_LEFT => ShipEnum::DIRECTION_RIGHT,
        ShipEnum::DIRECTION_TOP => ShipEnum::DIRECTION_BOTTOM,
        ShipEnum::DIRECTION_BOTTOM => ShipEnum::DIRECTION_TOP,
    ];

    private FlightSignatureRepositoryInterface $flightSignatureRepository;

    public function __construct(
        FlightSignatureRepositoryInterface $flightSignatureRepository
    ) {
        $this->flightSignatureRepository = $flightSignatureRepository;
    }

    public function createSignatures(
        ShipInterface $ship,
        int $flightDirection,
        MapInterface|StarSystemMapInterface $currentField,
        MapInterface|StarSystemMapInterface $nextField
    ): void {
        if ($currentField instanceof MapInterface !== $nextField instanceof MapInterface) {
            throw new InvalidArgumentException('wayopints have different type');
        }

        $fromSignature = $this->createSignature($ship);
        if ($currentField instanceof MapInterface) {
            $fromSignature->setMap($currentField);
        } else {
            $fromSignature->setStarsystemMap($currentField);
        }

        $toSignature = $this->createSignature($ship);
        if ($nextField instanceof MapInterface) {
            $toSignature->setMap($nextField);
        } else {
            $toSignature->setStarsystemMap($nextField);
        }

        $this->create(
            $flightDirection,
            $fromSignature,
            $toSignature
        );
    }

    private function create(
        int $flightMethod,
        FlightSignatureInterface $fromSignature,
        FlightSignatureInterface $toSignature
    ): void {
        $directionFrom = self::FLIGHT_DIRECTIONS[$flightMethod] ?? ShipEnum::DIRECTION_RIGHT;

        $fromSignature->setToDirection($flightMethod);
        $toSignature->setFromDirection($directionFrom);

        $this->flightSignatureRepository->save($fromSignature);
        $this->flightSignatureRepository->save($toSignature);
    }

    private function createSignature(ShipInterface $ship): FlightSignatureInterface
    {
        $signature = $this->flightSignatureRepository->prototype();
        $signature->setUserId($ship->getUser()->getId());
        $signature->setShipId($ship->getId());
        $signature->setShipName($ship->getName());
        $signature->setRump($ship->getRump());
        $signature->setIsCloaked($ship->getCloakState());
        $signature->setTime(time());

        return $signature;
    }
}
