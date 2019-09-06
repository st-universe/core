<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\RenameCrew;

use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowRenameCrew\ShowRenameCrew;
use Stu\Orm\Repository\CrewRepositoryInterface;

final class RenameCrew implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_RENAME_CREW';

    private $shipLoader;

    private $crewRepository;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        CrewRepositoryInterface $crewRepository
    ) {
        $this->shipLoader = $shipLoader;
        $this->crewRepository = $crewRepository;
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

        $crew = $this->crewRepository->find((int) $crew_id);

        if ($crew === null || $crew->getUserId() != $userId) {
            throw new \AccessViolation();
        }

        $name = request::getString('rn_crew_' . $crew->getId() . '_value');
        if (mb_strlen(trim($name)) > 0) {
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
