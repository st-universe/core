<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\ChangeName;

use JBBCode\Parser;
use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;

final class ChangeName implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_CHANGE_NAME';

    private $colonyLoader;

    private $bbCodeParser;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        Parser $bbCodeParser
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->bbCodeParser = $bbCodeParser;
    }

    public function handle(GameControllerInterface $game): void
    {
        $colony = $this->colonyLoader->byIdAndUser(
            request::indInt('id'),
            $game->getUser()->getId()
        );

        $game->setView(ShowColony::VIEW_IDENTIFIER, ['COLONY_MENU', MENU_OPTION]);

        $value = request::postStringFatal('colname');
        $value = tidyString(strip_tags($value));

        if (mb_strlen($this->bbCodeParser->parse($value)->getAsText()) < 3) {
            $game->addInformation(_('Der Name ist zu kurz (Minium: 3 Zeichen)'));
            return;
        }
        $colony->setName($value);
        $colony->save();

        $game->addInformation(_('Der Koloniename wurde geändert'));
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
