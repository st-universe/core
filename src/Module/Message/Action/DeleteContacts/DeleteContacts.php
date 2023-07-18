<?php

declare(strict_types=1);

namespace Stu\Module\Message\Action\DeleteContacts;

use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Tal\TalHelper;
use Stu\Orm\Repository\ContactRepositoryInterface;

final class DeleteContacts implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_DELETE_CONTACTS';

    private DeleteContactsRequestInterface $deleteContactsRequest;

    private ContactRepositoryInterface $contactRepository;

    private PrivateMessageSenderInterface $privateMessageSender;

    public function __construct(
        DeleteContactsRequestInterface $deleteContactsRequest,
        ContactRepositoryInterface $contactRepository,
        PrivateMessageSenderInterface $privateMessageSender
    ) {
        $this->deleteContactsRequest = $deleteContactsRequest;
        $this->contactRepository = $contactRepository;
        $this->privateMessageSender = $privateMessageSender;
    }

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
                    TalHelper::getContactListModeDescription($contact->getMode())
                )
            );

            $this->contactRepository->delete($contact);
        }
        $game->addInformation(_('Die Kontakte wurden gelöscht'));
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
