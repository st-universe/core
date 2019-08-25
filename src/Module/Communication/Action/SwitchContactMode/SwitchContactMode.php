<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\SwitchContactMode;

use Contactlist;
use ContactlistData;
use PM;
use Stu\Control\ActionControllerInterface;
use Stu\Control\GameControllerInterface;
use Stu\Module\Communication\View\ShowContactMode\ShowContactMode;

final class SwitchContactMode implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_CHANGE_CONTACTMODE';

    private $switchContactModeRequest;

    public function __construct(
        SwitchContactModeRequestInterface $switchContactModeRequest
    ) {
        $this->switchContactModeRequest = $switchContactModeRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setTemplateVar('div', $this->switchContactModeRequest->getContactDiv());

        $contact = Contactlist::getById($this->switchContactModeRequest->getContactId());
        $mode = $this->switchContactModeRequest->getModeId();
        $userId = $game->getUser()->getId();

        if (!$contact || $contact->getUserId() != $userId) {
            return;
        }
        if (!array_key_exists($mode, getContactlistModes())) {
            return;
        }
        if ($mode != $contact->getMode() && $mode == Contactlist::CONTACT_ENEMY) {
            PM::sendPM(
                $userId,
                $contact->getRecipientId(),
                _('Der Siedler betrachtet Dich von nun an als Feind')
            );
            $obj = Contactlist::hasContact($contact->getRecipientId(), $userId);
            if ($obj) {
                if (!$obj->isEnemy()) {
                    $obj->setMode(Contactlist::CONTACT_ENEMY);
                    $obj->save();
                }
            } else {
                $obj = new ContactlistData();
                $obj->setUserId($contact->getRecipientId());
                $obj->setRecipientId(currentUser()->getId());
                $obj->setMode(Contactlist::CONTACT_ENEMY);
                $obj->setDate(time());
                $obj->save();
            }
        }
        $contact->setMode($mode);
        $contact->save();

        $game->setTemplateVar('contact', $contact);
        $game->setView(ShowContactMode::VIEW_IDENTIFIER);
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
