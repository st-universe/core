<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Movement\Component;

use InvalidArgumentException;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Component\Map\DirectionEnum;
use Stu\Module\Control\StuTime;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\FlightSignature;
use Stu\Orm\Entity\Location;
use Stu\Orm\Entity\Map;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\SpacecraftRump;
use Stu\Orm\Repository\FlightSignatureRepositoryInterface;
use Stu\Orm\Repository\SpacecraftRumpRepositoryInterface;

/**
 * Creates flight signatures for ship movements
 */
final class FlightSignatureCreator implements FlightSignatureCreatorInterface
{
    /** @param SpacecraftLoaderInterface<SpacecraftWrapperInterface> $spacecraftLoader */
    public function __construct(
        private FlightSignatureRepositoryInterface $flightSignatureRepository,
        private StuTime $stuTime,
        private SpacecraftLoaderInterface $spacecraftLoader,
        private SpacecraftRumpRepositoryInterface $spacecraftRumpRepository
    ) {}

    #[\Override]
    public function createSignatures(
        Spacecraft $spacecraft,
        DirectionEnum $direction,
        Location $currentLocation,
        Location $nextLocation
    ): void {
        if ($currentLocation instanceof Map !== $nextLocation instanceof Map) {
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
        FlightSignature $fromSignature,
        FlightSignature $toSignature
    ): void {

        $fromSignature->setToDirection($direction);
        $toSignature->setFromDirection($direction->getOpposite());

        $this->flightSignatureRepository->save($fromSignature);
        $this->flightSignatureRepository->save($toSignature);
    }

    private function createSignature(Spacecraft $spacecraft): FlightSignature
    {
        $signature = $this->flightSignatureRepository->prototype();
        $signature->setUserId($spacecraft->getUser()->getId());
        $signature->setShipId($spacecraft->getId());
        $signature->setSpacecraftName($spacecraft->getName());
        $signature->setRump($this->getWarpSignature($spacecraft));
        $signature->setIsCloaked($spacecraft->isCloaked());
        $signature->setTime(time());

        return $signature;
    }

    private function getWarpSignature(Spacecraft $spacecraft): SpacecraftRump
    {
        $rump = $spacecraft->getRump();
        $wrapper = $this->spacecraftLoader->getWrapperByIdAndUser(
            $spacecraft->getId(),
            $spacecraft->getUser()->getId()
        );
        $warpsystem = $wrapper->getWarpDriveSystemData();
        if ($warpsystem !== null && $spacecraft->getSystemState(SpacecraftSystemTypeEnum::WARPDRIVE)) {
            $signaturetime = $warpsystem->getWarpSignatureTimer();
            if ($signaturetime + 300 >= $this->stuTime->time() && $this->spacecraftRumpRepository->find($warpsystem->getWarpSignature())) {
                $rump = $this->spacecraftRumpRepository->find($warpsystem->getWarpSignature());
            }
        }
        return $rump;
    }
}