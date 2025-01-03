<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Movement\Component;

use InvalidArgumentException;
use Override;
use Stu\Component\Map\DirectionEnum;
use Stu\Orm\Entity\FlightSignatureInterface;
use Stu\Orm\Entity\LocationInterface;
use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\SpacecraftInterface;
use Stu\Orm\Repository\FlightSignatureRepositoryInterface;

/**
 * Creates flight signatures for ship movements
 */
final class FlightSignatureCreator implements FlightSignatureCreatorInterface
{
    public function __construct(private FlightSignatureRepositoryInterface $flightSignatureRepository) {}

    #[Override]
    public function createSignatures(
        SpacecraftInterface $spacecraft,
        DirectionEnum $direction,
        LocationInterface $currentLocation,
        LocationInterface $nextLocation
    ): void {
        if ($currentLocation instanceof MapInterface !== $nextLocation instanceof MapInterface) {
            throw new InvalidArgumentException('wayopints have different type');
        }

        $fromSignature = $this->createSignature($spacecraft);
        $fromSignature->setLocation($currentLocation);

        $toSignature = $this->createSignature($spacecraft);
        $toSignature->setLocation($nextLocation);

        $this->create(
            $direction,
            $fromSignature,
            $toSignature
        );
    }

    private function create(
        DirectionEnum $direction,
        FlightSignatureInterface $fromSignature,
        FlightSignatureInterface $toSignature
    ): void {

        $fromSignature->setToDirection($direction);
        $toSignature->setFromDirection($direction->getOpposite());

        $this->flightSignatureRepository->save($fromSignature);
        $this->flightSignatureRepository->save($toSignature);
    }

    private function createSignature(SpacecraftInterface $spacecraft): FlightSignatureInterface
    {
        $signature = $this->flightSignatureRepository->prototype();
        $signature->setUserId($spacecraft->getUser()->getId());
        $signature->setShipId($spacecraft->getId());
        $signature->setSpacecraftName($spacecraft->getName());
        $signature->setRump($spacecraft->getRump());
        $signature->setIsCloaked($spacecraft->isCloaked());
        $signature->setTime(time());

        return $signature;
    }
}
