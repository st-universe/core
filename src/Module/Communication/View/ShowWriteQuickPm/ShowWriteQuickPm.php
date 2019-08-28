<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowWriteQuickPm;

use Stu\Control\GameControllerInterface;
use Stu\Control\ViewControllerInterface;

final class ShowWriteQuickPm implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_WRITE_QUICKPM';

    private $showWriteQuickPmRequest;

    public function __construct(
        ShowWriteQuickPmRequestInterface $showWriteQuickPmRequest
    ) {
        $this->showWriteQuickPmRequest = $showWriteQuickPmRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setTemplateFile('html/ajaxwindow.xhtml');
        $game->setMacro('html/commmacros.xhtml/write_quick_pm');
        $game->setPageTitle(_('Neue private Nachricht'));

        $game->setTemplateVar(
            'RECIPIENT',
            ResourceCache()->getObject("user", $this->showWriteQuickPmRequest->getRecipientId())
        );
    }
}
