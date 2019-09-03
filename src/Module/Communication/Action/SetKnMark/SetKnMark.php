<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\SetKnMark;

use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\KnPostRepositoryInterface;

final class SetKnMark implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_SET_KNMARK';

    private $setKnMarkRequest;

    private $knPostRepository;

    public function __construct(
        SetKnMarkRequestInterface $setKnMarkRequest,
        KnPostRepositoryInterface $knPostRepository
    ) {
        $this->setKnMarkRequest = $setKnMarkRequest;
        $this->knPostRepository = $knPostRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $posting = $this->knPostRepository->find($this->setKnMarkRequest->getKnOffset());

        if ($posting === null) {
            return;
        }

        $user = $game->getUser();

        $user->setKNMark($posting->getId());
        $user->save();

        $game->addInformation(_('Das Lesezeichen wurde gesetzt'));
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
