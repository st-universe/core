<?php

declare(strict_types=1);

namespace Stu\Module\Ship\View\ShowBeamToColony;

use request;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Ship\Lib\Interaction\InteractionChecker;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;

final class ShowBeamToColony implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_COLONY_BEAMTO';

    private ShipLoaderInterface $shipLoader;

    private ColonyRepositoryInterface $colonyRepository;

    private ColonyLibFactoryInterface $colonyLibFactory;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        ColonyLibFactoryInterface $colonyLibFactory,
        ColonyRepositoryInterface $colonyRepository
    ) {
        $this->shipLoader = $shipLoader;
        $this->colonyRepository = $colonyRepository;
        $this->colonyLibFactory = $colonyLibFactory;
    }

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $user->getId(),
            true,
            false
        );
        $game->setPageTitle('Zu Kolonie beamen');
        $game->setMacroInAjaxWindow('html/shipmacros.xhtml/entity_not_available');

        $target = $this->colonyRepository->find(request::getIntFatal('target'));
        if ($target === null || !InteractionChecker::canInteractWith($ship, $target, $game)) {
            return;
        }

        $game->setMacroInAjaxWindow('html/shipmacros.xhtml/show_ship_beamto_colony');

        $game->setTemplateVar('targetColony', $target);
        $game->setTemplateVar('SHIP', $ship);
        $game->setTemplateVar('OWNS_TARGET', $target->getUser() === $user);
        $game->setTemplateVar(
            'SHOW_SHIELD_FREQUENCY',
            $this->colonyLibFactory->createColonyShieldingManager($target)->isShieldingEnabled() && $target->getUser() !== $user
        );
    }
}
