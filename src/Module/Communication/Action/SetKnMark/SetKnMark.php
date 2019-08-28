<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\SetKnMark;

use KNPosting;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;

final class SetKnMark implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_SET_KNMARK';

    private $setKnMarkRequest;

    public function __construct(
        SetKnMarkRequestInterface $setKnMarkRequest
    ) {
        $this->setKnMarkRequest = $setKnMarkRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $posting = new KNPosting($this->setKnMarkRequest->getKnOffset());
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
