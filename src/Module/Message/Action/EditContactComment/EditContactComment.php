<?php

declare(strict_types=1);

namespace Stu\Module\Message\Action\EditContactComment;

use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\ContactRepositoryInterface;

final class EditContactComment implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_EDIT_CONTACT_COMMENT';
    public const int CHARACTER_LIMIT = 50;

    public function __construct(private EditContactCommentRequestInterface $editContactCommentRequest, private ContactRepositoryInterface $contactRepository) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $contact = $this->contactRepository->find($this->editContactCommentRequest->getContactId());
        if ($contact == null || $contact->getUserId() !== $game->getUser()->getId()) {
            return;
        }

        $text = $this->editContactCommentRequest->getText();

        if (mb_strlen($text) > self::CHARACTER_LIMIT) {
            $game->getInfo()->addInformation(sprintf(_('Es sind maximal %d Zeichen erlaubt'), self::CHARACTER_LIMIT));
            return;
        }

        $contact->setComment($text);

        $this->contactRepository->save($contact);

        $game->getInfo()->addInformation(_('Kommentar wurde editiert'));
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
