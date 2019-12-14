<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\Abandon;

use Stu\Exception\AccessViolation;
use Stu\Module\Colony\Lib\ColonyResetterInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\PlayerSetting\Lib\PlayerEnum;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class Abandon implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_GIVEUP_COLONY';

    private AbandonRequestInterface $abandonRequest;

    private ColonyRepositoryInterface $colonyRepository;

    private ColonyResetterInterface $colonyResetter;

    private UserRepositoryInterface $userRepository;

    public function __construct(
        AbandonRequestInterface $abandonRequest,
        ColonyRepositoryInterface $colonyRepository,
        ColonyResetterInterface $colonyResetter,
        UserRepositoryInterface $userRepository
    ) {
        $this->abandonRequest = $abandonRequest;
        $this->colonyRepository = $colonyRepository;
        $this->colonyResetter = $colonyResetter;
        $this->userRepository = $userRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $userId = $user->getId();
        $colony = $this->colonyRepository->find($this->abandonRequest->getColonyId());

        if ($colony === null || $colony->getUserId() !== $userId) {
            throw new AccessViolation();
        }

        $this->colonyResetter->reset($colony);

        $colonyAmount = $this->colonyRepository->getAmountByUser($user);

        if ($colonyAmount === 0) {
            $user->setActive(PlayerEnum::USER_ACTIVE);

            $this->userRepository->save($user);
        }

        $game->addInformation(_('Die Kolonie wurde aufgegeben'));
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
