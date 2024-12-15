<?php

namespace Stu\Module\Spacecraft\Lib\Destruction\Handler;

use Override;
use Stu\Lib\Information\InformationInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Spacecraft\Lib\Destruction\SpacecraftDestroyerInterface;
use Stu\Module\Spacecraft\Lib\Destruction\SpacecraftDestructionCauseEnum;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

class ColonizationShipCheck implements SpacecraftDestructionHandlerInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    #[Override]
    public function handleSpacecraftDestruction(
        ?SpacecraftDestroyerInterface $destroyer,
        SpacecraftWrapperInterface $destroyedSpacecraftWrapper,
        SpacecraftDestructionCauseEnum $cause,
        InformationInterface $informations
    ): void {

        $user = $destroyedSpacecraftWrapper->get()->getUser();

        if ($user->getState() === UserEnum::USER_STATE_COLONIZATION_SHIP) {
            $user->setState(UserEnum::USER_STATE_UNCOLONIZED);
            $this->userRepository->save($user);
        }
    }
}
