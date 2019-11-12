<?php

declare(strict_types=1);

namespace Stu\Module\Message\Action\AddContact;

use Stu\Module\Message\Lib\ContactListModeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Repository\ContactRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class AddContact implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_ADD_CONTACT';

    private $addContactRequest;

    private $contactRepository;

    private $privateMessageSender;

    private $userRepository;

    public function __construct(
        AddContactRequestInterface $addContactRequest,
        ContactRepositoryInterface $contactRepository,
        PrivateMessageSenderInterface $privateMessageSender,
        UserRepositoryInterface $userRepository
    ) {
        $this->addContactRequest = $addContactRequest;
        $this->contactRepository = $contactRepository;
        $this->privateMessageSender = $privateMessageSender;
        $this->userRepository = $userRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setTemplateVar('div', $this->addContactRequest->getContactDiv());
        $game->setTemplateVar('contact', null);

        $userId = $game->getUser()->getId();

        $recipient = $this->userRepository->find($this->addContactRequest->getRecipientId());
        if ($recipient === null) {
            $game->addInformation(_('Dieser Spieler existiert nicht'));
            return;
        }
        if ($recipient->isContactable() === false) {
            $game->addInformation(_('Dieser Spieler kann nicht hinzugefügt werden'));
            return;
        }
        if ($recipient->getId() == $userId) {
            $game->addInformation(_('Du kannst Dich nicht selbst auf die Kontaktliste setzen'));
            return;
        }
        if ($this->contactRepository->getByUserAndOpponent($userId, $recipient->getId()) !== null) {
            $game->addInformation(_('Dieser Spieler befindet sich bereits auf Deiner Kontaktliste'));
            return;
        }

        $mode = $this->addContactRequest->getModeId();

        if (!array_key_exists($mode, $game->getContactlistModes())) {
            return;
        }
        $contact = $this->contactRepository->prototype();
        $contact->setUser($game->getUser());
        $contact->setMode($mode);
        $contact->setRecipient($recipient);
        $contact->setDate(time());

        $this->contactRepository->save($contact);

        if ($mode == ContactListModeEnum::CONTACT_ENEMY) {
            $this->privateMessageSender->send(
                $userId,
                $recipient->getId(),
                _('Der Spieler betrachtet Dich von nun an als Feind')
            );
        }
        $game->addInformation(_('Der Spieler wurde hinzugefügt'));

        $game->setTemplateVar('contact', $contact);
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
