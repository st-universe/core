<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\EditContactComment;

use Contactlist;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;

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
        if (!$contact || $contact->getUserId() != $game->getUser()->getId()) {
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
