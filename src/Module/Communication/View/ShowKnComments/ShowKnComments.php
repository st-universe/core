<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowKnComments;

use KNPosting;
use Stu\Control\GameControllerInterface;
use Stu\Control\ViewControllerInterface;

final class ShowKnComments implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_KN_COMMENTS';

    private $showKnCommentsRequest;

    public function __construct(
        ShowKnCommentsRequestInterface $showKnCommentsRequest
    ) {
        $this->showKnCommentsRequest = $showKnCommentsRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $post = new KNPosting($this->showKnCommentsRequest->getKnPostId());

        $game->setPageTitle(sprintf(_('Kommentare für Beitrag %d'), $post->getId()));
        $game->setTemplateFile('html/ajaxwindow.xhtml');
        $game->setAjaxMacro('html/commmacros.xhtml/kncomments');
        $game->setTemplateVar('POST', $post);
    }
}
