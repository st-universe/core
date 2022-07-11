<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\GiveUp;

use request;
use Stu\Exception\AccessViolation;
use Stu\Module\Colony\Lib\ColonyResetterInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class GiveUp implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_GIVEUP_COLONY';

    private GiveUpRequestInterface $giveupRequest;

    private ColonyRepositoryInterface $colonyRepository;

    private ColonyResetterInterface $colonyResetter;

    private UserRepositoryInterface $userRepository;

    public function __construct(
        GiveUpRequestInterface $giveupRequest,
        ColonyRepositoryInterface $colonyRepository,
        ColonyResetterInterface $colonyResetter,
        UserRepositoryInterface $userRepository
    ) {
        $this->giveupRequest = $giveupRequest;
        $this->colonyRepository = $colonyRepository;
        $this->colonyResetter = $colonyResetter;
        $this->userRepository = $userRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();
        $userId = $user->getId();

        $colonyAmount = $this->colonyRepository->getAmountByUser($user);
        $colony = $this->colonyRepository->find($this->giveupRequest->getColonyId());

        if ($colony === null || $colony->getUserId() !== $userId) {
            throw new AccessViolation();
        }

        $code = trim(request::postString('giveupcode'));

        if ($code !== substr(md5($colony->getName()), 0, 6)) {
            $game->addInformation(_('Der Bestätigungscode war fehlerhaft'));
            return;
        }

        $this->colonyResetter->reset($colony);
        $colonyAmount--;

        $isMoon = $colony->getPlanetType()->getIsMoon();
        if (!$isMoon && $colonyAmount === 0) {
            $user->setState(UserEnum::USER_STATE_UNCOLONIZED);

            $this->userRepository->save($user);
        }

        $game->addInformation(_('Die Kolonie wurde aufgegeben'));
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
