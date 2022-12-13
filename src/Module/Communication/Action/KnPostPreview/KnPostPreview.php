<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\KnPostPreview;

use JBBCode\Parser;
use request;
use Stu\Module\Communication\Action\AddKnPost\AddKnPostRequestInterface;
use Stu\Module\Communication\View\ShowWriteKn\ShowWriteKn;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;

final class KnPostPreview implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_PREVIEW_KN';

    private AddKnPostRequestInterface $request;

    private Parser $bbcodeParser;

    public function __construct(
        AddKnPostRequestInterface $request,
        Parser $bbcodeParser
    ) {
        $this->request = $request;
        $this->bbcodeParser = $bbcodeParser;
    }

    public function handle(GameControllerInterface $game): void
    {
        $title = $this->request->getTitle();
        $text = $this->request->getText();
        $plotId = $this->request->getPlotId();
        $mark = $this->request->getPostMark();

        $game->setTemplateVar('TITLE', $title);
        $game->setTemplateVar('TEXT', request::indString('text'));
        $game->setTemplateVar('PLOT_ID', $plotId);
        $game->setTemplateVar('MARK', $mark);
        $game->setTemplateVar('PREVIEW', $this->bbcodeParser->parse($text)->getAsHTML());

        $game->addInformation(_('Vorschau wurde erstellt'));

        $game->setView(ShowWriteKn::VIEW_IDENTIFIER);
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
