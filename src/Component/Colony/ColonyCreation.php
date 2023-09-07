<?php

declare(strict_types=1);

namespace Stu\Component\Colony;

use RuntimeException;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\StarSystemMapInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

//TODO unit tests
final class ColonyCreation implements ColonyCreationInterface
{
    private ColonyRepositoryInterface $colonyRepository;

    private UserRepositoryInterface $userRepository;

    public function __construct(
        ColonyRepositoryInterface $colonyRepository,
        UserRepositoryInterface $userRepository
    ) {
        $this->colonyRepository = $colonyRepository;
        $this->userRepository = $userRepository;
    }

    public function create(StarSystemMapInterface $systemMap, string $name): ColonyInterface
    {
        $colonyClass = $systemMap->getFieldType()->getColonyClass();
        if ($colonyClass === null) {
            throw new RuntimeException('colony class can not be null');
        }

        $colony = $this->colonyRepository->prototype();

        $colony->setColonyClass($colonyClass);
        $colony->setUser($this->userRepository->getFallbackUser());
        $colony->setStarsystemMap($systemMap);
        $colony->setPlanetName($name);
        $colony->setRotationFactor(0); //TODO

        $this->colonyRepository->save($colony);

        return $colony;
    }
}
