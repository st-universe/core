<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\DeactivateTractorBeam;

use request;

use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Component\Spacecraft\System\Control\ActivatorDeactivatorHelperInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;

final class DeactivateTractorBeam implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_DEACTIVATE_TRACTOR';

    /** @param SpacecraftLoaderInterface<SpacecraftWrapperInterface> $spacecraftLoader */
    public function __construct(
        private SpacecraftLoaderInterface $spacecraftLoader,
        private ActivatorDeactivatorHelperInterface $helper
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowSpacecraft::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $ship = $this->spacecraftLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );

        if (!$ship->isTractoring()) {
            return;
        }
        $this->helper->deactivate(request::indInt('id'), SpacecraftSystemTypeEnum::TRACTOR_BEAM, $game->getInfo());
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
