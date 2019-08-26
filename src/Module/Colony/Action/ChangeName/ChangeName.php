<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\ChangeName;

use request;
use Stu\Control\ActionControllerInterface;
use Stu\Control\GameControllerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;

final class ChangeName implements ActionControllerInterface
{

    public const ACTION_IDENTIFIER = 'B_CHANGE_NAME';

    private $colonyLoader;

    public function __construct(
        ColonyLoaderInterface $colonyLoader
    ) {
        $this->colonyLoader = $colonyLoader;
    }

    public function handle(GameControllerInterface $game): void
    {
        $colony = $this->colonyLoader->byIdAndUser(
            request::indInt('id'),
            $game->getUser()->getId()
        );

        $game->setView(ShowColony::VIEW_IDENTIFIER, ['COLONY_MENU', MENU_OPTION]);

        $value = request::postStringFatal('colname');
        $colony->setName(tidyString($value));
        $colony->save();
        $game->addInformation(_('Der Koloniename wurde geändert'));
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
