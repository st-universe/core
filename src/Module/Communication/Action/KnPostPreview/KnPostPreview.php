<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\KnPostPreview;

use Override;
use request;
use Stu\Component\Communication\Kn\KnBbCodeParser;
use Stu\Module\Communication\Action\AddKnPost\AddKnPostRequestInterface;
use Stu\Module\Communication\View\ShowWriteKn\ShowWriteKn;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;

final class KnPostPreview implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_PREVIEW_KN';

    public function __construct(
        private AddKnPostRequestInterface $request,
        private KnBbCodeParser $bbcodeParser
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $title = $this->request->getTitle();
        $text = $this->request->getText();
        $plotId = $this->request->getPlotId();
        $mark = $this->request->getPostMark();

        $game->setTemplateVar('TITLE', $title);
        $game->setTemplateVar('TEXT', request::indString('text') ?: '');
        $game->setTemplateVar('PLOT_ID', $plotId);
        $game->setTemplateVar('MARK', $mark);
        $game->setTemplateVar('CHARACTER_IDS_STRING', request::indString('characterids'));
        $game->setTemplateVar('PREVIEW', $this->bbcodeParser->parse($text)->getAsHTML());

        $game->addInformation(_('Vorschau wurde erstellt'));

        $game->setView(ShowWriteKn::VIEW_IDENTIFIER);
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
