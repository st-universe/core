<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Crew;

use Doctrine\ORM\EntityManagerInterface;
use Override;
use RuntimeException;
use Stu\Component\Map\DirectionEnum;
use Stu\Component\Spacecraft\SpacecraftRumpEnum;
use Stu\Module\Spacecraft\Lib\Creation\SpacecraftFactoryInterface;
use Stu\Orm\Entity\SpacecraftInterface;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Repository\SpacecraftRumpRepositoryInterface;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;
use Stu\Orm\Repository\StarSystemMapRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

//TODO unit tests
final class LaunchEscapePods implements LaunchEscapePodsInterface
{
    public function __construct(
        private SpacecraftRepositoryInterface $spacecraftRepository,
        private UserRepositoryInterface $userRepository,
        private SpacecraftRumpRepositoryInterface $spacecraftRumpRepository,
        private StarSystemMapRepositoryInterface $starSystemMapRepository,
        private MapRepositoryInterface $mapRepository,
        private SpacecraftFactoryInterface $spacecraftFactory,
        private EntityManagerInterface $entityManager
    ) {}

    #[Override]
    public function launch(SpacecraftInterface $spacecraft): ?SpacecraftInterface
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
        $pods->setHuell(1);
        $pods->setMaxHuell(1);
        $pods->setAlertStateGreen();

        $pods->setLocation($spacecraft->getLocation());

        //return to save place
        $this->returnToSafety($pods, $spacecraft);

        $this->spacecraftRepository->save($pods);
        $this->entityManager->flush(); //TODO really neccessary?

        return $pods;
    }

    private function returnToSafety(SpacecraftInterface $pods, SpacecraftInterface $spacecraft): void
    {
        $field = $pods->getLocation();

        if ($field->getFieldType()->getSpecialDamage() !== 0) {
            $flightDirection = $spacecraft->getFlightDirection();
            if ($flightDirection === null) {
                $flightDirection = DirectionEnum::from(random_int(1, 4));
            }
            $met = 'fly' . $flightDirection->value;
            $newXY = $this->$met($pods);

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
    private function fly2(SpacecraftInterface $pods): array
    {
        return [$pods->getPosX(), $pods->getPosY() - 1];
    }

    //flee downwards
    /**
     * @return array<int>
     */
    private function fly4(SpacecraftInterface $pods): array
    {
        return [$pods->getPosX(), $pods->getPosY() + 1];
    }

    //flee right
    /**
     * @return array<int>
     */
    private function fly3(SpacecraftInterface $pods): array
    {
        return [$pods->getPosX() - 1, $pods->getPosY()];
    }

    //flee left
    /**
     * @return array<int>
     */
    private function fly1(SpacecraftInterface $pods): array
    {
        return [$pods->getPosX() + 1, $pods->getPosY()];
    }
}
