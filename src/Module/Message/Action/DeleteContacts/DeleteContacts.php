<?php

declare(strict_types=1);

namespace Stu\Module\Message\Action\DeleteContacts;

use Override;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Orm\Repository\ContactRepositoryInterface;

final class DeleteContacts implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_DELETE_CONTACTS';

    public function __construct(
        private DeleteContactsRequestInterface $deleteContactsRequest,
        private ContactRepositoryInterface $contactRepository,
        private PrivateMessageSenderInterface $privateMessageSender
    ) {
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        foreach ($this->deleteContactsRequest->getContactIds() as $contactId) {
            $contact = $this->contactRepository->find((int) $contactId);
            if ($contact === null || $contact->getUserId() !== $game->getUser()->getId()) {
                continue;
            }

            //send info PM to contact
            $this->privateMessageSender->send(
                $game->getUser()->getId(),
                $contact->getRecipientId(),
                sprintf(
                    'Der Siedler betrachtet Dich nun nicht mehr als %s',
                    $contact->getMode()->getTitle()
                )
            );

            $this->contactRepository->delete($contact);
        }
        $game->addInformation(_('Die Kontakte wurden gelöscht'));
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
