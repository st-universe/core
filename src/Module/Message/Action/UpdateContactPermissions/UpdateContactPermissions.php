<?php

declare(strict_types=1);

namespace Stu\Module\Message\Action\UpdateContactPermissions;

use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Message\View\ShowContactList\ShowContactList;
use Stu\Orm\Repository\ContactRepositoryInterface;
use Stu\Orm\Repository\RelationPermissionRepositoryInterface;

final class UpdateContactPermissions implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_UPDATE_CONTACT_PERMISSIONS';

    public function __construct(
        private readonly UpdateContactPermissionsRequestInterface $updateContactPermissionsRequest,
        private readonly ContactRepositoryInterface $contactRepository,
        private readonly RelationPermissionRepositoryInterface $relationPermissionRepository
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowContactList::VIEW_IDENTIFIER);

        $contact = $this->contactRepository->find($this->updateContactPermissionsRequest->getContactId());
        if ($contact === null || $contact->getUserId() !== $game->getUser()->getId()) {
            return;
        }

        $this->relationPermissionRepository->replaceForContact(
            $contact,
            $this->updateContactPermissionsRequest->getPermissions()
        );
        $game->getInfo()->addInformation('Die Kontaktrechte wurden gespeichert');
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
