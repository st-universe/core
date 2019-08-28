<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\DeleteContacts;

use Contactlist;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;

final class DeleteContacts implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_DELETE_CONTACTS';

    private $deleteContactsRequest;

    public function __construct(
        DeleteContactsRequestInterface $deleteContactsRequest
    ) {
        $this->deleteContactsRequest = $deleteContactsRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        foreach ($this->deleteContactsRequest->getContactIds() as $key => $val) {
            $contact = Contactlist::getById($val);
            if (!$contact || $contact->getUserId() != $game->getUser()->getId()) {
                continue;
            }
            $contact->deleteFromDatabase();
        }
        $game->addInformation(_('Die Kontakte wurden gelöscht'));
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
