<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Crew;

use Override;
use RuntimeException;
use Stu\Component\Map\DirectionEnum;
use Stu\Component\Spacecraft\SpacecraftRumpEnum;
use Stu\Module\Spacecraft\Lib\Creation\SpacecraftFactoryInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Repository\SpacecraftRumpRepositoryInterface;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;
use Stu\Orm\Repository\StarSystemMapRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

//TODO unit tests
final class LaunchEscapePods implements LaunchEscapePodsInterface
{
    public function __construct(
        private readonly SpacecraftRepositoryInterface $spacecraftRepository,
        private readonly UserRepositoryInterface $userRepository,
        private readonly SpacecraftRumpRepositoryInterface $spacecraftRumpRepository,
        private readonly StarSystemMapRepositoryInterface $starSystemMapRepository,
        private readonly MapRepositoryInterface $mapRepository,
        private readonly SpacecraftFactoryInterface $spacecraftFactory,
        private readonly SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory
    ) {}

    #[Override]
    public function launch(Spacecraft $spacecraft): ?Spacecraft
    {
        $shipRump = $this->spacecraftRumpRepository->find($spacecraft->getUser()->getFactionId() + SpacecraftRumpEnum::SHIP_RUMP_BASE_ID_ESCAPE_PODS);

        // faction does not have escape pods
        if ($shipRump == null) {
            return null;
        }

        $pods = $this->spacecraftFactory->create($shipRump);
        $pods->setUser($this->userRepository->getFallbackUser());
        $pods->setRump($shipRump);
        $pods->setName(sprintf(_('Rettungskapseln von (%d)'), $spacecraft->getId()));
        $pods->setMaxHuell(1);
        $pods->getCondition()->setHull(1);

        $pods->setLocation($spacecraft->getLocation());

        //return to save place
        $this->returnToSafety($pods, $spacecraft);

        $this->spacecraftRepository->save($pods);

        return $pods;
    }

    private function returnToSafety(Spacecraft $pods, Spacecraft $spacecraft): void
    {
        $field = $pods->getLocation();

        if ($field->getFieldType()->getSpecialDamage() !== 0) {
            $flightDirection = $this->spacecraftWrapperFactory->wrapSpacecraft($spacecraft)->getComputerSystemDataMandatory()->getFlightDirection();
            while ($flightDirection === DirectionEnum::NON) {
                $flightDirection = DirectionEnum::from(random_int(1, 4));
            }

            $newXY = match ($flightDirection) {
                DirectionEnum::LEFT => $this->fly1($pods),
                DirectionEnum::BOTTOM => $this->fly2($pods),
                DirectionEnum::RIGHT => $this->fly3($pods),
                DirectionEnum::TOP => $this->fly4($pods),
            };

            if ($pods->getSystem() !== null) {
                $field = $this->starSystemMapRepository->getByCoordinates(
                    $pods->getSystem()->getId(),
                    $newXY[0],
                    $newXY[1]
                );
            } else {
                $layer = $pods->getLayer();
                if ($layer === null) {
                    throw new RuntimeException('this should not happen');
                }

                $field = $this->mapRepository->getByCoordinates(
                    $layer,
                    $newXY[0],
                    $newXY[1]
                );
            }
            if ($field === null) {
                throw new RuntimeException('this should not happen');
            }
            $pods->setLocation($field);
        }
    }

    //flee upwards
    /**
     * @return array<int>
     */
    private function fly2(Spacecraft $pods): array
    {
        return [$pods->getPosX(), $pods->getPosY() - 1];
    }

    //flee downwards
    /**
     * @return array<int>
     */
    private function fly4(Spacecraft $pods): array
    {
        return [$pods->getPosX(), $pods->getPosY() + 1];
    }

    //flee right
    /**
     * @return array<int>
     */
    private function fly3(Spacecraft $pods): array
    {
        return [$pods->getPosX() - 1, $pods->getPosY()];
    }

    //flee left
    /**
     * @return array<int>
     */
    private function fly1(Spacecraft $pods): array
    {
        return [$pods->getPosX() + 1, $pods->getPosY()];
    }
}
