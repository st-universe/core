<?php

declare(strict_types=1);

namespace Stu\Module\Communication\Action\KnPostPreview;

use request;
use Stu\Component\Communication\Kn\KnBbCodeParser;
use Stu\Module\Communication\Action\AddKnPost\AddKnPostRequestInterface;
use Stu\Module\Communication\View\ShowWriteKn\ShowWriteKn;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\Component\Action\ActionControllerContext;

final class KnPostPreview implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_PREVIEW_KN';

    public function __construct(
        private AddKnPostRequestInterface $request,
        private KnBbCodeParser $bbcodeParser
    ) {}

    #[\Override]
    public function handle(ActionControllerContext $context): void
    {
        $title = $this->request->getTitle();
        $text = request::indString('text') ?: '';
        $plotId = $this->request->getPlotId();
        $mark = $this->request->getPostMark();

        $context->setTemplateVar('TITLE', $title);
        $context->setTemplateVar('TEXT', $text);
        $context->setTemplateVar('PLOT_ID', $plotId);
        $context->setTemplateVar('MARK', $mark);
        $context->setTemplateVar('CHARACTER_IDS_STRING', request::indString('characterids'));

        $context->setTemplateVar('PREVIEW', $this->bbcodeParser->parseToHtml($text));

        $context->getInfo()->addInformation(_('Vorschau wurde erstellt'));

        $context->setView(ShowWriteKn::VIEW_IDENTIFIER);
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
