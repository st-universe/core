<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Movement\Component;

use InvalidArgumentException;
use Stu\Component\Map\DirectionEnum;
use Stu\Component\Realtime\SpacecraftMovementPublisherInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Control\StuTime;
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
    public function __construct(
        private FlightSignatureRepositoryInterface $flightSignatureRepository,
        private StuTime $stuTime,
        private SpacecraftRumpRepositoryInterface $spacecraftRumpRepository,
        private SpacecraftMovementPublisherInterface $spacecraftMovementPublisher
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

        if ($spacecraft->isRpgModuleInvisible()) {
            return;
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

        $this->spacecraftMovementPublisher->publishMovement(
            $spacecraft,
            $direction,
            $currentLocation,
            $nextLocation
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

        $warpdriveSystem = $spacecraft->getSystems()->get(SpacecraftSystemTypeEnum::WARPDRIVE->value);
        if ($warpdriveSystem !== null && $spacecraft->getSystemState(SpacecraftSystemTypeEnum::WARPDRIVE)) {
            $data = json_decode((string) $warpdriveSystem->getData(), true);
            if (is_array($data)) {
                $signatureTime = (int) ($data['wstimer'] ?? 0);
                $signatureRumpId = (int) ($data['warpsignature'] ?? 0);

                if ($signatureRumpId > 0 && $signatureTime + 300 >= $this->stuTime->time()) {
                    $signatureRump = $this->spacecraftRumpRepository->find($signatureRumpId);
                    if ($signatureRump !== null) {
                        $rump = $signatureRump;
                    }
                }
            }
        }

        return $rump;
    }
}
