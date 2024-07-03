<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Crew;

use Doctrine\ORM\EntityManagerInterface;
use Override;
use RuntimeException;
use Stu\Component\Ship\ShipRumpEnum;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\ShipRumpRepositoryInterface;
use Stu\Orm\Repository\StarSystemMapRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

//TODO unit tests
final class LaunchEscapePods implements LaunchEscapePodsInterface
{
    public function __construct(private ShipRepositoryInterface $shipRepository, private UserRepositoryInterface $userRepository, private ShipRumpRepositoryInterface $shipRumpRepository, private StarSystemMapRepositoryInterface $starSystemMapRepository, private MapRepositoryInterface $mapRepository, private EntityManagerInterface $entityManager)
    {
    }

    #[Override]
    public function launch(ShipInterface $ship): ?ShipInterface
    {
        $shipRump = $this->shipRumpRepository->find($ship->getUser()->getFactionId() + ShipRumpEnum::SHIP_RUMP_BASE_ID_ESCAPE_PODS);

        // faction does not have escape pods
        if ($shipRump == null) {
            return null;
        }

        $pods = $this->shipRepository->prototype();
        $pods->setUser($this->userRepository->getFallbackUser());
        $pods->setRump($shipRump);
        $pods->setName(sprintf(_('Rettungskapseln von (%d)'), $ship->getId()));
        $pods->setHuell(1);
        $pods->setMaxHuell(1);
        $pods->setAlertStateGreen();

        $pods->setLocation($ship->getLocation());

        //return to save place
        $this->returnToSafety($pods, $ship);

        $this->shipRepository->save($pods);
        $this->entityManager->flush();
        return $pods;
    }

    private function returnToSafety(ShipInterface $pods, ShipInterface $ship): void
    {
        $field = $pods->getCurrentMapField();

        if ($field->getFieldType()->getSpecialDamage() !== 0) {
            $met = 'fly' . $ship->getFlightDirection();
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
    private function fly2(ShipInterface $pods): array
    {
        return [$pods->getPosX(), $pods->getPosY() - 1];
    }

    //flee downwards
    /**
     * @return array<int>
     */
    private function fly4(ShipInterface $pods): array
    {
        return [$pods->getPosX(), $pods->getPosY() + 1];
    }

    //flee right
    /**
     * @return array<int>
     */
    private function fly3(ShipInterface $pods): array
    {
        return [$pods->getPosX() - 1, $pods->getPosY()];
    }

    //flee left
    /**
     * @return array<int>
     */
    private function fly1(ShipInterface $pods): array
    {
        return [$pods->getPosX() + 1, $pods->getPosY()];
    }
}
