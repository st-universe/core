<?php

namespace Stu\Module\Ship\Lib\Destruction\Handler;

use Stu\Lib\Information\InformationInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Ship\Lib\Destruction\ShipDestroyerInterface;
use Stu\Module\Ship\Lib\Destruction\ShipDestructionCauseEnum;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

class ColonizationShipCheck implements ShipDestructionHandlerInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {
    }

    public function handleShipDestruction(
        ?ShipDestroyerInterface $destroyer,
        ShipWrapperInterface $destroyedShipWrapper,
        ShipDestructionCauseEnum $cause,
        InformationInterface $informations
    ): void {

        $user = $destroyedShipWrapper->get()->getUser();

        if ($user->getState() === UserEnum::USER_STATE_COLONIZATION_SHIP) {
            $user->setState(UserEnum::USER_STATE_UNCOLONIZED);
            $this->userRepository->save($user);
        }
    }
}
