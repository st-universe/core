<?php

declare(strict_types=1);

namespace Stu\Module\Message\Action\EditContactComment;

use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\ContactRepositoryInterface;

final class EditContactComment implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_EDIT_CONTACT_COMMENT';
    public const CHARACTER_LIMIT = 50;

    private EditContactCommentRequestInterface $editContactCommentRequest;

    private ContactRepositoryInterface $contactRepository;

    public function __construct(
        EditContactCommentRequestInterface $editContactCommentRequest,
        ContactRepositoryInterface $contactRepository
    ) {
        $this->editContactCommentRequest = $editContactCommentRequest;
        $this->contactRepository = $contactRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $contact = $this->contactRepository->find($this->editContactCommentRequest->getContactId());
        if ($contact == null || $contact->getUserId() != $game->getUser()->getId()) {
            return;
        }

        $text = $this->editContactCommentRequest->getText();

        if (mb_strlen($text) > static::CHARACTER_LIMIT) {
            $game->addInformation(sprintf(_('Es sind maximal %d Zeichen erlaubt'), static::CHARACTER_LIMIT));
            return;
        }

        $contact->setComment($text);

        $this->contactRepository->save($contact);

        $game->addInformation(_('Kommentar wurde editiert'));
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
