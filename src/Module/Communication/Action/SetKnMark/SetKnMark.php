<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\SetKnMark;

use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\KnPostRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class SetKnMark implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_SET_KNMARK';

    private SetKnMarkRequestInterface $setKnMarkRequest;

    private KnPostRepositoryInterface $knPostRepository;

    private UserRepositoryInterface $userRepository;

    public function __construct(
        SetKnMarkRequestInterface $setKnMarkRequest,
        KnPostRepositoryInterface $knPostRepository,
        UserRepositoryInterface $userRepository
    ) {
        $this->setKnMarkRequest = $setKnMarkRequest;
        $this->knPostRepository = $knPostRepository;
        $this->userRepository = $userRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $posting = $this->knPostRepository->find($this->setKnMarkRequest->getKnOffset());

        if ($posting === null) {
            return;
        }

        $user = $game->getUser();

        $user->setKnMark($posting->getId());

        $this->userRepository->save($user);

        $game->addInformation(_('Das Lesezeichen wurde gesetzt'));
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
