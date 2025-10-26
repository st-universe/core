<?php

namespace Stu\Module\Spacecraft\Lib\Destruction\Handler;

use Stu\Lib\Information\InformationInterface;
use Stu\Module\PlayerSetting\Lib\UserStateEnum;
use Stu\Module\Spacecraft\Lib\Destruction\SpacecraftDestroyerInterface;
use Stu\Module\Spacecraft\Lib\Destruction\SpacecraftDestructionCauseEnum;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

class ColonizationShipCheck implements SpacecraftDestructionHandlerInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    #[\Override]
    public function handleSpacecraftDestruction(
        ?SpacecraftDestroyerInterface $destroyer,
        SpacecraftWrapperInterface $destroyedSpacecraftWrapper,
        SpacecraftDestructionCauseEnum $cause,
        InformationInterface $informations
    ): void {

        $user = $destroyedSpacecraftWrapper->get()->getUser();

        if ($user->getState() === UserStateEnum::COLONIZATION_SHIP) {
            $user->setState(UserStateEnum::UNCOLONIZED);
            $this->userRepository->save($user);
        }
    }
}
