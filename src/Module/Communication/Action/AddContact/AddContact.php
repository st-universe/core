<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\AddContact;

use Contactlist;
use ContactlistData;
use PM;
use Stu\Control\ActionControllerInterface;
use Stu\Control\GameControllerInterface;
use User;

final class AddContact implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_ADD_CONTACT';

    private $addContactRequest;

    public function __construct(
        AddContactRequestInterface $addContactRequest
    ) {
        $this->addContactRequest = $addContactRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setTemplateVar('div', $this->addContactRequest->getContactDiv());

        $userId = $game->getUser()->getId();

        $recipient = User::getUserById($this->addContactRequest->getRecipientId());
        if (!$recipient) {
            $game->addInformation(_('Dieser Spieler existiert nicht'));
            return;
        }
        if (isSystemUser($recipient->getId())) {
            $game->addInformation(_('Dieser Spieler kann nicht hinzugefügt werden'));
            return;
        }
        if ($recipient->getId() == $userId) {
            $game->addInformation(_('Du kannst Dich nicht selbst auf die Kontaktliste setzen'));
            return;
        }
        if (Contactlist::isOnList($userId, $recipient->getId()) == 1) {
            $game->addInformation(_('Dieser Spieler befindet sich bereits auf Deiner Kontaktliste'));
            return;
        }

        $mode = $this->addContactRequest->getModeId();

        if (!array_key_exists($mode, getContactlistModes())) {
            return;
        }
        $contact = new ContactlistData();
        $contact->setUserId($userId);
        $contact->setMode($mode);
        $contact->setRecipientId($recipient->getId());
        $contact->setDate(time());
        $contact->save();
        if ($mode == Contactlist::CONTACT_ENEMY) {
            PM::sendPM($userId, $recipient->getId(), _('Der Spieler betrachtet Dich von nun an als Feind'));
        }
        $game->addInformation(_('Der Spieler wurde hinzugefügt'));

        $game->setTemplateVar('contact', $contact);
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
