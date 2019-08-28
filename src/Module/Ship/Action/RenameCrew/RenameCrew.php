<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\RenameCrew;

use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowRenameCrew\ShowRenameCrew;

final class RenameCrew implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_RENAME_CREW';

    private $shipLoader;

    public function __construct(
        ShipLoaderInterface $shipLoader
    ) {
        $this->shipLoader = $shipLoader;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );

        $game->setView(ShowRenameCrew::VIEW_IDENTIFIER);
        $crew_id = request::getIntFatal('crewid');
        $crew = ResourceCache()->getObject('crew', $crew_id);
        $name = request::getString('rn_crew_' . $crew->getId() . '_value');
        if ($crew->ownedByCurrentUser() && strlen(trim($name)) > 0) {
            $crew->setName(tidyString($name));
            $crew->save();
        }
        $game->setTemplateVar('CREW', $crew);
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
