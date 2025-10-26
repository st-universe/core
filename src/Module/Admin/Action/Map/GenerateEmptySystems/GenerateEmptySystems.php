<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action\Map\GenerateEmptySystems;

use request;
use Stu\Component\StarSystem\GenerateEmptySystemsInterface;
use Stu\Module\Admin\View\Map\ShowMapEditor;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;

final class GenerateEmptySystems implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'GENERATE_EMPTY_SYSTEMS';

    public function __construct(private GenerateEmptySystemsInterface $generateEmptySystems) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowMapEditor::VIEW_IDENTIFIER);

        $count = $this->generateEmptySystems->generate(request::getInt('layerid'), $game);

        $game->getInfo()->addInformation(sprintf('Es wurden %d Systeme generiert.', $count));
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return false;
    }
}
