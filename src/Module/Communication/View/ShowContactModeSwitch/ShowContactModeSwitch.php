<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowContactModeSwitch;

use Contactlist;
use Stu\Control\GameControllerInterface;
use Stu\Control\ViewControllerInterface;

final class ShowContactModeSwitch implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_CONTACT_MODESWITCH';

    private $showContactModeSwitchRequest;

    public function __construct(
        ShowContactModeSwitchRequestInterface $showContactModeSwitchRequest
    ) {
        $this->showContactModeSwitchRequest = $showContactModeSwitchRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $contact = new Contactlist($this->showContactModeSwitchRequest->getContactId());

        if ($contact->getUserId() != $game->getUser()->getId()) {
            return;
        }
        $game->setPageTitle(_('Status'));
        $game->setTemplateFile('html/ajaxwindow.xhtml');
        $game->setMacro('html/commmacros.xhtml/clmodeswitch');
        $game->setTemplateVar('contact', $contact);
    }
}
