<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\EditContactComment;

use Contactlist;
use Stu\Control\ActionControllerInterface;
use Stu\Control\GameControllerInterface;

final class EditContactComment implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_EDIT_CONTACT_COMMENT';

    private $editContactCommentRequest;

    public function __construct(
        EditContactCommentRequestInterface $editContactCommentRequest
    ) {
        $this->editContactCommentRequest = $editContactCommentRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $contact = Contactlist::getById($this->editContactCommentRequest->getContactId());
        if (!$contact || !$contact->isOwnContact()) {
            return;
        }
        $contact->setComment($this->editContactCommentRequest->getText());
        $contact->save();

        $game->addInformation(_('Kommentar wurde editiert'));
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
