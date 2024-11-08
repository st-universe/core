<?php

declare(strict_types=1);

namespace Stu\Module\NPC\View\ShowShipCreator;

use request;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\ShipBuildplanRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;
use Stu\Orm\Repository\LayerRepositoryInterface;

final class ShowShipCreator implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_SHIP_CREATOR';

    private ShipBuildplanRepositoryInterface $shipBuildplanRepository;
    private UserRepositoryInterface $userRepository;
    private LayerRepositoryInterface $layerRepository;

    public function __construct(
        ShipBuildplanRepositoryInterface $shipBuildplanRepository,
        UserRepositoryInterface $userRepository,
        LayerRepositoryInterface $layerRepository
    ) {
        $this->shipBuildplanRepository = $shipBuildplanRepository;
        $this->userRepository = $userRepository;
        $this->layerRepository = $layerRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = request::getInt('userId');

        $game->setTemplateFile('html/npc/shipCreator.twig');
        $game->appendNavigationPart('/npc/index.php?SHOW_SHIP_CREATOR=1', 'Schiff erstellen');
        $game->setPageTitle('Schiff erstellen');

        if ($userId > 0) {
            $selectedUser = $this->userRepository->find($userId);
            $game->setTemplateVar('USER_ID', $userId);
            $game->setTemplateVar('SELECTED_USER', $selectedUser);
            $game->setTemplateVar('BUILDPLANS', $this->shipBuildplanRepository->getByUser($userId));
            $game->setTemplateVar('LAYERS', $this->layerRepository->findAll());
        } else {
            $allUsers = array_merge(
                $this->userRepository->getNpcList(),
                $this->userRepository->getNonNpcList()
            );
            $game->setTemplateVar('ALL_USERS', $allUsers);
        }
    }
}