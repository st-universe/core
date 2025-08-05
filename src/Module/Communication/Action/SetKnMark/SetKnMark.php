<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\SetKnMark;

use Override;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\KnPostRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class SetKnMark implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_SET_KNMARK';

    public function __construct(private SetKnMarkRequestInterface $setKnMarkRequest, private KnPostRepositoryInterface $knPostRepository, private UserRepositoryInterface $userRepository) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $posting = $this->knPostRepository->find($this->setKnMarkRequest->getKnOffset());

        if ($posting === null) {
            return;
        }

        $user = $game->getUser();

        $user->setKnMark($posting->getId());

        $this->userRepository->save($user);

        $game->getInfo()->addInformation(_('Das Lesezeichen wurde gesetzt'));
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
