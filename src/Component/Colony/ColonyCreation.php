<?php

declare(strict_types=1);

namespace Stu\Component\Colony;

use Override;
use RuntimeException;
use Stu\Module\Control\StuRandom;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\StarSystemMap;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class ColonyCreation implements ColonyCreationInterface
{
    public function __construct(private ColonyRepositoryInterface $colonyRepository, private UserRepositoryInterface $userRepository, private StuRandom $stuRandom) {}

    #[Override]
    public function create(StarSystemMap $systemMap, string $name): Colony
    {
        $colonyClass = $systemMap->getFieldType()->getColonyClass();
        if ($colonyClass === null) {
            throw new RuntimeException('colony class can not be null');
        }

        // TODO colonyConstants
        $colony = $this->colonyRepository->prototype();

        $colony->setColonyClass($colonyClass);
        $colony->setUser($this->userRepository->getFallbackUser());
        $colony->setStarsystemMap($systemMap);
        $colony->setPlanetName($name);
        $colony->setRotationFactor($this->stuRandom->rand(
            $colonyClass->getMinRotation(),
            $colonyClass->getMaxRotation(),
            true
        ));

        $this->colonyRepository->save($colony);

        return $colony;
    }
}
