<?php

declare(strict_types=1);

namespace Stu\Module\NPC\View\ShowShipCreator;

use Override;
use request;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\ShipBuildplanRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;
use Stu\Orm\Repository\LayerRepositoryInterface;
use Stu\Orm\Repository\TorpedoTypeRepositoryInterface;

final class ShowShipCreator implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_SHIP_CREATOR';

    public function __construct(
        private ShipBuildplanRepositoryInterface $shipBuildplanRepository,
        private UserRepositoryInterface $userRepository,
        private LayerRepositoryInterface $layerRepository,
        private TorpedoTypeRepositoryInterface $torpedoTypeRepository
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $userId = request::getInt('userId');
        $buildplanId = request::getInt('buildplanId');

        $game->setTemplateFile('html/npc/shipCreator.twig');
        $game->appendNavigationPart('/npc/index.php?SHOW_SHIP_CREATOR=1', 'Schiff erstellen');
        $game->setPageTitle('Schiff erstellen');

        if ($userId > 0) {
            $selectedUser = $this->userRepository->find($userId);
            $game->setTemplateVar('USER_ID', $userId);
            $game->setTemplateVar('SELECTED_USER', $selectedUser);

            if ($buildplanId > 0) {
                $buildplan = $this->shipBuildplanRepository->find($buildplanId);
                if ($buildplan !== null) {
                    $possibleTorpedoTypes = $this->torpedoTypeRepository->getByLevel($buildplan->getRump()->getTorpedoLevel());
                    $game->setTemplateVar('TORPEDO_TYPES', $possibleTorpedoTypes);
                    $game->setTemplateVar('SELECTED_BUILDPLAN', $buildplan);
                    $game->setTemplateVar('LAYERS', $this->layerRepository->findAll());
                }
            } else {
                $game->setTemplateVar('BUILDPLANS', $this->shipBuildplanRepository->getByUser($userId));
            }
        } else {
            $npcList = iterator_to_array($this->userRepository->getNpcList());
            $nonNpcList = iterator_to_array($this->userRepository->getNonNpcList());
            $allUsers = array_merge($npcList, $nonNpcList);
            $game->setTemplateVar('ALL_USERS', $allUsers);
        }
    }
}
