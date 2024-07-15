<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Movement\Component;

use InvalidArgumentException;
use Override;
use Stu\Component\Ship\ShipEnum;
use Stu\Orm\Entity\FlightSignatureInterface;
use Stu\Orm\Entity\LocationInterface;
use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\FlightSignatureRepositoryInterface;

/**
 * Creates flight signatures for ship movements
 */
final class FlightSignatureCreator implements FlightSignatureCreatorInterface
{
    /** @var array<int, int> */
    private const array FLIGHT_DIRECTIONS = [
        ShipEnum::DIRECTION_RIGHT => ShipEnum::DIRECTION_LEFT,
        ShipEnum::DIRECTION_LEFT => ShipEnum::DIRECTION_RIGHT,
        ShipEnum::DIRECTION_TOP => ShipEnum::DIRECTION_BOTTOM,
        ShipEnum::DIRECTION_BOTTOM => ShipEnum::DIRECTION_TOP,
    ];

    public function __construct(private FlightSignatureRepositoryInterface $flightSignatureRepository)
    {
    }

    #[Override]
    public function createSignatures(
        ShipInterface $ship,
        int $flightDirection,
        LocationInterface $currentLocation,
        LocationInterface $nextLocation
    ): void {
        if ($currentLocation instanceof MapInterface !== $nextLocation instanceof MapInterface) {
            throw new InvalidArgumentException('wayopints have different type');
        }

        $fromSignature = $this->createSignature($ship);
        $fromSignature->setLocation($currentLocation);

        $toSignature = $this->createSignature($ship);
        $toSignature->setLocation($nextLocation);

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
