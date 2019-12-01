<?php

declare(strict_types=1);

namespace Stu\Module\Message\Action\DeleteContacts;

use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\ContactRepositoryInterface;

final class DeleteContacts implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_DELETE_CONTACTS';

    private DeleteContactsRequestInterface $deleteContactsRequest;

    private ContactRepositoryInterface $contactRepository;

    public function __construct(
        DeleteContactsRequestInterface $deleteContactsRequest,
        ContactRepositoryInterface $contactRepository
    ) {
        $this->deleteContactsRequest = $deleteContactsRequest;
        $this->contactRepository = $contactRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        foreach ($this->deleteContactsRequest->getContactIds() as $contactId) {
            $contact = $this->contactRepository->find((int) $contactId);
            if ($contact === null || $contact->getUserId() != $game->getUser()->getId()) {
                continue;
            }

            $this->contactRepository->delete($contact);
        }
        $game->addInformation(_('Die Kontakte wurden gelöscht'));
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
