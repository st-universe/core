<?php

declare(strict_types=1);

namespace Stu\Module\Message\Action\AddContact;

use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;
use Stu\Module\Message\Lib\ContactListModeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Message\View\ShowContactList\ShowContactList;
use Stu\Module\Message\View\ShowContactMode\ShowContactMode;
use Stu\Orm\Repository\ContactRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

final class AddContact implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_ADD_CONTACT';

    public function __construct(private AddContactRequestInterface $addContactRequest, private ContactRepositoryInterface $contactRepository, private PrivateMessageSenderInterface $privateMessageSender, private UserRepositoryInterface $userRepository) {}

    #[\Override]
    public function handle(ActionControllerContext $context): void
    {
        $contactDiv = $this->addContactRequest->getContactDiv();
        $isAjaxRequest = $contactDiv !== '';

        $context->setView($isAjaxRequest ? ShowContactMode::VIEW_IDENTIFIER : ShowContactList::VIEW_IDENTIFIER);

        if ($isAjaxRequest) {
            $context->setTemplateVar('div', $contactDiv);
            $context->setTemplateVar('contact', null);
        }

        $userId = $context->getUser()->getId();

        $recipiendIdString = trim($this->addContactRequest->getRecipientId());

        if (!is_numeric($recipiendIdString) || ((int)$recipiendIdString) < 1) {
            $context->getInfo()->addInformation(_("Ungültiger Wert angegeben. Muss positive Zahl sein!"));
            return;
        }

        $recipient = $this->userRepository->find((int)$recipiendIdString);
        if ($recipient === null) {
            $context->getInfo()->addInformation(_('Dieser Spieler existiert nicht'));
            return;
        }
        if ($recipient->isContactable() === false) {
            $context->getInfo()->addInformation(_('Dieser Spieler kann nicht hinzugefügt werden'));
            return;
        }
        if ($recipient->getId() === $userId) {
            $context->getInfo()->addInformation(_('Du kannst Dich nicht selbst auf die Kontaktliste setzen'));
            return;
        }
        if ($this->contactRepository->getByUserAndOpponent($userId, $recipient->getId()) !== null) {
            $context->getInfo()->addInformation(_('Dieser Spieler befindet sich bereits auf Deiner Kontaktliste'));
            return;
        }

        $mode = ContactListModeEnum::tryFrom($this->addContactRequest->getModeId());

        if ($mode === null) {
            return;
        }
        $contact = $this->contactRepository->prototype();
        $contact->setUser($context->getUser());
        $contact->setMode($mode);
        $contact->setRecipient($recipient);
        $contact->setDate(time());

        $this->contactRepository->save($contact);

        if ($mode == ContactListModeEnum::ENEMY) {
            $this->privateMessageSender->send(
                $userId,
                $recipient->getId(),
                _('Der Siedler betrachtet Dich von nun an als Feind')
            );
        }
        if ($mode == ContactListModeEnum::FRIEND) {
            $this->privateMessageSender->send(
                $userId,
                $recipient->getId(),
                _('Der Siedler betrachtet Dich von nun an als Freund')
            );
        }
        if ($mode == ContactListModeEnum::NEUTRAL) {
            $this->privateMessageSender->send(
                $userId,
                $recipient->getId(),
                _('Der Siedler betrachtet Dich von nun an als neutral')
            );
        }
        $context->getInfo()->addInformation(_('Der Spieler wurde hinzugefügt'));

        if ($isAjaxRequest) {
            $context->setTemplateVar('contact', $contact);
        }
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
